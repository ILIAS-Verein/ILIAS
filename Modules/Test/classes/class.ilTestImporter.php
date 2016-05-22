<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlImporter.php");

/**
 * Importer class for files
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * @ingroup ModulesLearningModule
 */
class ilTestImporter extends ilXmlImporter
{
	/**
	 * @var string
	 */
	private $xmlFile;
	
	/**
	 * @var ilImportMapping
	 */
	private $mappingRegistry;
	
	/**
	 * @var ilObjTest
	 */
	protected $testOBJ;
	
	/**
	 * @return string
	 */
	public function getXmlFile()
	{
		return $this->xmlFile;
	}
	
	/**
	 * @param string $xmlFile
	 */
	public function setXmlFile($xmlFile)
	{
		$this->xmlFile = $xmlFile;
	}
	
	/**
	 * @return ilImportMapping
	 */
	public function getMappingRegistry()
	{
		return $this->mappingRegistry;
	}
	
	/**
	 * @param ilImportMapping $mappingRegistry
	 */
	public function setMappingRegistry($mappingRegistry)
	{
		$this->mappingRegistry = $mappingRegistry;
	}
	
	/**
	 * @return ilObjTest
	 */
	public function getTestOBJ()
	{
		return $this->testOBJ;
	}
	
	/**
	 * @param ilObjTest $testOBJ
	 */
	public function setTestOBJ($testOBJ)
	{
		$this->testOBJ = $testOBJ;
	}
	
	/**
	 * Import XML
	 *
	 * @param
	 * @return
	 */
	function importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping)
	{
		$this->setXmlFile($a_xml);
		$this->setMappingRegistry($a_mapping);
		
		/* @var ilObjTest $newObj */
		
		// Container import => test object already created
		include_once "./Modules/Test/classes/class.ilObjTest.php";
		ilObjTest::_setImportDirectory($this->getImportDirectoryContainer());

		if($new_id = $a_mapping->getMapping('Services/Container','objs',$a_id))
		{
			// container content
			$newObj = ilObjectFactory::getInstanceByObjId($new_id,false);

			$_SESSION['tst_import_subdir'] = $this->getImportPackageName();
		}
		else
		{
			// single object
			$new_id = $a_mapping->getMapping('Modules/Test', 'tst', 'new_id');
			$newObj = ilObjectFactory::getInstanceByObjId($new_id, false);
			
			if( isset($_SESSION['tst_import_qst_parent']) )
			{
				$questionParentObjId = $_SESSION['tst_import_qst_parent'];
			}
			else
			{
				$questionParentObjId = $newObj->getId();
			}
		}

		$newObj->loadFromDb();

		list($xml_file,$qti_file) = $this->parseXmlFileNames();

		if(!@file_exists($xml_file))
		{
			$GLOBALS['ilLog']->write(__METHOD__.': Cannot find xml definition: '. $xml_file);
			return false;
		}
		if(!@file_exists($qti_file))
		{
			$GLOBALS['ilLog']->write(__METHOD__.': Cannot find xml definition: '. $qti_file);
			return false;
		}

		// FIXME: Copied from ilObjTestGUI::importVerifiedFileObject
		// TODO: move all logic to ilObjTest::importVerifiedFile and call 
		// this method from ilObjTestGUI and ilTestImporter 
		$newObj->mark_schema->flush();
		

		if( isset($_SESSION['tst_import_idents']) )
		{
			$idents = $_SESSION['tst_import_idents'];
		}
		else
		{
			$idents = null;
		}

		// start parsing of QTI files
		include_once "./Services/QTI/classes/class.ilQTIParser.php";
		$qtiParser = new ilQTIParser($qti_file, IL_MO_PARSE_QTI, $questionParentObjId , $idents);
		$qtiParser->setTestObject($newObj);
		$result = $qtiParser->startParsing();

		// import page data
		include_once ("./Modules/LearningModule/classes/class.ilContObjParser.php");
		$contParser = new ilContObjParser($newObj, $xml_file, basename($this->getImportDirectory()));
		$contParser->setQuestionMapping($qtiParser->getImportMapping());
		$contParser->startParsing();

		$GLOBALS['ilLog']->write(__METHOD__.': Begin added T&A mappings');
		
		foreach ($qtiParser->getImportMapping() as $k => $v)
		{
			$oldQuestionId = substr($k, strpos($k, 'qst_')+strlen('qst_'));
			$newQuestionId = $v['test']; // yes, this is the new question id ^^

			$a_mapping->addMapping(
				"Services/Taxonomy", "tax_item", "tst:quest:$oldQuestionId", $newQuestionId
			);

			$a_mapping->addMapping(
				"Services/Taxonomy", "tax_item_obj_id", "tst:quest:$oldQuestionId", $newObj->getId()
			);

			$a_mapping->addMapping(
				"Modules/Test", "quest", $oldQuestionId, $newQuestionId
			);
			
			$GLOBALS['ilLog']->write(__METHOD__.': Added T&A mappings');
		}
		
		if( $newObj->isRandomTest() )
		{
			$newObj->questions = array();
			$this->importRandomQuestionSetConfig($newObj, $xml_file, $a_mapping);
		}

		// import test results
		if(@file_exists($_SESSION["tst_import_results_file"]))
		{
			include_once("./Modules/Test/classes/class.ilTestResultsImportParser.php");
			$results = new ilTestResultsImportParser($_SESSION["tst_import_results_file"], $newObj);
			$results->setQuestionIdMapping($a_mapping->getMappingsOfEntity('Modules/Test', 'quest'));
			$results->setSrcPoolDefIdMapping($a_mapping->getMappingsOfEntity('Modules/Test', 'rnd_src_pool_def'));
			$results->startParsing();
		}
		
		// import skill assignments
		$importedAssignmentList = $this->importQuestionSkillAssignments($newObj->getId());
		$this->importSkillLevelThresholds($importedAssignmentList, $newObj->getTestId(), $newObj->getId());
			
		$a_mapping->addMapping("Modules/Test", "tst", $a_id, $newObj->getId());

		$newObj->saveToDb();

		ilObjTest::_setImportDirectory();
		
		$this->setTestOBJ($newObj);
	}

	/**
	 * Final processing
	 *
	 * @param ilImportMapping $a_mapping
	 * @return
	 */
	function finalProcessing($a_mapping)
	{
		$maps = $a_mapping->getMappingsOfEntity("Modules/Test", "tst");
		
		foreach ($maps as $old => $new)
		{
			if ($old == "new_id" || (int)$old <= 0)
			{
				continue;
			}

			if( $this->testOBJ->isRandomTest() )
			{
				$this->finalRandomTestTaxonomyProcessing($a_mapping, $old, $new);
			}
		}
	}
	
	protected function finalRandomTestTaxonomyProcessing(ilImportMapping $mapping, $oldTstObjId, $newTstObjId)
	{
		require_once 'Services/Taxonomy/classes/class.ilObjTaxonomy.php';

		// get all new taxonomies of this object and store usage for test object
		
		$new_tax_ids = $mapping->getMapping(
			'Services/Taxonomy', 'tax_usage_of_obj', $oldTstObjId
		);
		
		if($new_tax_ids !== false)
		{
			$tax_ids = explode(":", $new_tax_ids);
			
			foreach($tax_ids as $tid)
			{
				ilObjTaxonomy::saveUsage($tid, $newTstObjId);
			}
		}

		// update all source pool definition's tax/taxNode ids with new mapped id
		
		global $ilDB;

		require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetSourcePoolDefinitionFactory.php';
		$srcPoolDefFactory = new ilTestRandomQuestionSetSourcePoolDefinitionFactory(
			$ilDB, $this->testOBJ
		);

		require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetSourcePoolDefinitionList.php';
		$srcPoolDefList = new ilTestRandomQuestionSetSourcePoolDefinitionList(
			$ilDB, $this->testOBJ, $srcPoolDefFactory
		);

		$srcPoolDefList->loadDefinitions();

		foreach($srcPoolDefList as $definition)
		{
			if( !$definition->getMappedFilterTaxId() && !$definition->getMappedFilterTaxNodeId() )
			{
				continue;
			}
			
			$newTaxId = $mapping->getMapping(
				'Services/Taxonomy', 'tax', $definition->getMappedFilterTaxId()
			);
			
			$definition->setMappedFilterTaxId($newTaxId ? $newTaxId : null);

			$newTaxNodeId = $mapping->getMapping(
				'Services/Taxonomy', 'tax_tree', $definition->getMappedFilterTaxNodeId()
			);

			$definition->setMappedFilterTaxNodeId($newTaxNodeId ? $newTaxNodeId : null);

			$definition->saveToDb();
		}
	}

	/**
	 * Create qti and xml file name
	 * @return array 
	 */
	protected function parseXmlFileNames()
	{
		$GLOBALS['ilLog']->write(__METHOD__.': '.$this->getImportDirectory());
		
		$basename = basename($this->getImportDirectory());

		$xml = $this->getImportDirectory().'/'.$basename.'.xml';
		$qti = $this->getImportDirectory().'/'.preg_replace('/test|tst/', 'qti', $basename).'.xml';
		
		return array($xml,$qti);
	}
	
	protected function importRandomQuestionSetConfig(ilObjTest $testOBJ, $xmlFile, $a_mapping)
	{
		require_once 'Modules/Test/classes/class.ilObjTestXMLParser.php';
		$parser = new ilObjTestXMLParser($xmlFile);
		$parser->setTestOBJ($testOBJ);
		$parser->setImportMapping($a_mapping);
		$parser->startParsing();
	}

	private function getImportDirectoryContainer()
	{
		$dir = $this->getImportDirectory();
		$dir = dirname($dir);
		return $dir;
	}

	private function getImportPackageName()
	{
		$dir = $this->getImportDirectory();
		$name = basename($dir);
		return $name;
	}
	
	/**
	 * @param $xmlFile
	 * @param ilImportMapping $mappingRegistry
	 * @param $targetParentObjId
	 * @return ilAssQuestionSkillAssignmentList
	 */
	protected function importQuestionSkillAssignments($parentObjId)
	{
		require_once 'Modules/TestQuestionPool/classes/questions/class.ilAssQuestionSkillAssignmentXmlParser.php';
		$parser = new ilAssQuestionSkillAssignmentXmlParser($this->getXmlFile());
		$parser->startParsing();
		
		require_once 'Modules/TestQuestionPool/classes/questions/class.ilAssQuestionSkillAssignmentImporter.php';
		$importer = new ilAssQuestionSkillAssignmentImporter();
		$importer->setTargetParentObjId($parentObjId);
		$importer->setImportInstallationId($this->getInstallId());
		$importer->setImportMappingRegistry($this->getMappingRegistry());
		$importer->setImportMappingComponent('Modules/Test');
		$importer->setImportAssignmentList($parser->getAssignmentList());
		
		$importer->import();
		
		if( $importer->getFailedImportAssignmentList()->assignmentsExist() )
		{
			$qsaImportFails = new ilAssQuestionSkillAssignmentImportFails();
			$qsaImportFails->registerFailedImports($parentObjId, $importer->getFailedImportAssignmentList());
		}
		
		return $importer->getSuccessImportAssignmentList();
	}
	
	
	protected function importSkillLevelThresholds(ilAssQuestionSkillAssignmentList $assignmentList, $testId, $parentObjId)
	{
		require_once 'Modules/Test/classes/class.ilTestSkillLevelThresholdXmlParser.php';
		$parser = new ilTestSkillLevelThresholdXmlParser($this->getXmlFile());
		$parser->startParsing();
		
		require_once 'Modules/Test/classes/class.ilTestSkillLevelThresholdImporter.php';
		$importer = new ilTestSkillLevelThresholdImporter();
		$importer->setTargetTestId($testId);
		$importer->setImportInstallationId($this->getInstallId());
		$importer->setImportMappingRegistry($this->getMappingRegistry());
		$importer->setImportedQuestionSkillAssignmentList($assignmentList);
		$importer->setImportThresholdList($parser->getSkillLevelThresholdImportList());
		$importer->import();
		
		if( $importer->getFailedThresholdImportSkillList()->skillsExist() )
		{
			$sltImportFails = new ilTestSkillLevelThresholdImportFails();
			$sltImportFails->registerFailedImports($parentObjId, $importer->getFailedThresholdImportSkillList());
		}
	}
}

?>