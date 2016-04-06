<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* Class ilLOXmlWriter
*
* @author Stefan Meyer <smeyer.ilias@gmx.de> 
*
*
*/
class ilLOXmlParser
{
	const TYPE_TST_PO = 1;
	const TYPE_TST_ALL = 2;
	
	private $xml = '';
	private $course = null;
	private $mapping = null;
	
	/**
	 * Constructor
	 * @param ilObjCourse $course
	 * @param type $a_xml
	 */
	public function __construct(ilObjCourse $course, $a_xml)
	{
		$this->course = $course;
		$this->xml = $a_xml;
	}
	
	/**
	 * Set import mapping
	 * @param ilImportMapping $mapping
	 */
	public function setMapping(ilImportMapping $mapping)
	{
		$this->mapping = $mapping;
	}
	
	/**
	 * Get import mapping
	 * @return ilImportMapping
	 */
	public function getMapping()
	{
		return $this->mapping;
	}
	
	/**
	 * Get course
	 * @return ilObjCourse
	 */
	protected function getCourse()
	{
		return $this->course;
	}
	
	/**
	 * Parse xml
	 */
	public function parse()
	{
		libxml_use_internal_errors(true);

		
		$root = simplexml_load_string(trim($this->xml));
		if(!$root instanceof SimpleXMLElement)
		{
			$GLOBALS['ilLog']->write(__METHOD__.': XML is: '. $this->xml. (string) $root);
			$GLOBALS['ilLog']->write(__METHOD__.': Error parsing objective xml: '.$this->parseXmlErrors());
			return false;
		}
		$GLOBALS['ilLog']->write(__METHOD__.': Handling element: '. (string) $root->getName());
		$this->parseSettings($root);
		$this->parseObjectives($root);
	}
	
	/**
	 * 
	 * @param SimpleXMLElement $root
	 */
	protected function parseSettings(SimpleXMLElement $root)
	{
		include_once './Modules/Course/classes/Objectives/class.ilLOSettings.php';
		$settings = ilLOSettings::getInstanceByObjId($this->getCourse()->getId());
		$GLOBALS['ilLog']->write(__METHOD__.': Handling element: '. (string) $root->Settings->getName());
		foreach($root->Settings as $set)
		{
			$GLOBALS['ilLog']->write(__METHOD__.': Handling element: '. (string) $set->getName());
			$settings->setInitialTestType((int) (string) $set->attributes()->initialTestType);
			$settings->setInitialTestAsStart((bool) (string) $set->attributes()->initialTestStart);
			$settings->setQualifyingTestType((int) (string) $set->attributes()->qualifyingTestType);
			$settings->setQualifyingTestAsStart((bool) (string) $set->attributes()->qualifyingTestStart);
			$settings->resetResults((bool) (string) $set->attributes()->resetResults);
			$settings->setPassedObjectiveMode((int) (string) $set->attributes()->passedObjectivesMode);
			
			// itest
			$itest = (int) $this->getMappingInfoForItem((int) (string) $set->attributes()->iTest);
			$settings->setInitialTest($itest);
			
			// qtest
			$qtest = (int) $this->getMappingInfoForItem((int) (string) $set->attributes()->qTest);
			$settings->setQualifiedTest($qtest);
			
			$settings->update();
		}
	}
	
	/**
	 * Parse objective
	 * @param SimpleXMLElement $root
	 */
	protected function parseObjectives(SimpleXMLElement $root)
	{
		foreach($root->Objective as $obj)
		{
			include_once './Modules/Course/classes/class.ilCourseObjective.php';
			$new_obj = new ilCourseObjective($this->getCourse());
			$new_obj->setActive((bool) (string) $obj->attributes()->online);
			$new_obj->setTitle((string) $obj->Title);
			$new_obj->setDescription((string) $obj->Description);
			$new_obj->setPosition((int) (string) $obj->attributes()->position);
			$new_objective_id = $new_obj->add();
			
			$this->parseMaterials($obj,$new_objective_id);
			$this->parseTests($obj, $new_objective_id);
			
		}
	}
	
	/**
	 * Parse assigned materials
	 * @param SimpleXMLElement $obj
	 */
	protected function parseMaterials(SimpleXMLElement $obj, $a_objective_id)
	{
		foreach($obj->Material as $mat)
		{
			$mat_ref_id = (string) $mat->attributes()->refId;
			$mat_obj_id = (string) $mat->attributes()->objId;
			
			$mapping_ref_id = $this->getMappingInfoForItem($mat_ref_id);
			if($mapping_ref_id)
			{
				include_once './Modules/Course/classes/class.ilCourseObjectiveMaterials.php';
				$new_mat = new ilCourseObjectiveMaterials($a_objective_id);
				$new_mat->setLMRefId($mapping_ref_id);
				$new_mat->setLMObjId(ilObject::_lookupObjId($mapping_ref_id));
				$new_mat->setType(ilObject::_lookupType(ilObject::_lookupObjId($mapping_ref_id)));
				$new_mat->add();
			}
		}
	}
	
	/**
	 * Parse tests of objective
	 * @param SimpleXMLElement $obj
	 * @param type $a_objective_id
	 */
	protected function parseTests(SimpleXMLElement $obj, $a_objective_id)
	{
		$GLOBALS['ilLog']->write(__METHOD__.': Parsing ' . (string) $obj->getName());
		
		foreach($obj->Test as $tst)
		{
			$type = (int) (string) $tst->attributes()->type;
			$tst_ref_id = (string) $tst->attributes()->refId;
			$GLOBALS['ilLog']->write(__METHOD__.': Found test ref id ' . (string) $tst_ref_id);

			$mapping_ref_id = $this->getMappingInfoForItem($tst_ref_id);
			if(!$mapping_ref_id)
			{
				continue;
			}

			if($type == self::TYPE_TST_PO)
			{
				include_once './Modules/Course/classes/Objectives/class.ilLOTestAssignment.php';
				$assignment = new ilLOTestAssignment();
				$assignment->setContainerId($this->getCourse()->getId());
				$assignment->setTestRefId($mapping_ref_id);
				$assignment->setObjectiveId($a_objective_id);
				$assignment->setAssignmentType((int) (string) $tst->attributes()->testType);
				$assignment->save();
			}
			else
			{
				include_once './Modules/Course/classes/class.ilCourseObjectiveQuestion.php';
				$quest = new ilCourseObjectiveQuestion($a_objective_id);
				$quest->setTestRefId($mapping_ref_id);
				$quest->setTestObjId(ilObject::_lookupObjId($mapping_ref_id));
				$quest->setTestStatus((string) $tst->attributes()->testType);
				$quest->setTestSuggestedLimit((string) $tst->attributes()->limit);
				
				foreach($tst->Question as $qst)
				{
					$qid = (string) $qst->attributes()->id;
					$mapping_qid = $this->getMappingForQuestion($qid);
					if($mapping_qid)
					{
						$quest->setQuestionId($mapping_qid);
						$quest->add();
					}
				}
			}
		}
	}
	
	/**
	 * Get mapping info
	 * @param type $a_ref_id
	 * @param type $a_obj_id
	 * @return int ref id of mapped item
	 */
	protected function getMappingInfoForItem($a_ref_id)
	{
		$new_ref_id = $this->getMapping()->getMapping('Services/Container', 'refs', $a_ref_id);
		$GLOBALS['ilLog']->write(__METHOD__.': Found new ref_id: ' .$new_ref_id.' for '. $a_ref_id);
		return (int) $new_ref_id;
	}
	
	protected function getMappingForQuestion($qid)
	{
		$new_qid = $this->getMapping()->getMapping('Modules/Test', 'quest', $qid);
		$GLOBALS['ilLog']->write(__METHOD__.': Found new question_id: ' .$new_qid.' for '. $qid);
		return $new_qid;
	}
	
	
	
	
	/**
	 * Parse xml errors from libxml_get_errors
	 *
	 * @return string
	 */
	protected function parseXmlErrors()
	{
		$errors = '';
		foreach(libxml_get_errors() as $err)
		{
			$errors .= $err->code.'<br/>';
		}
		return $errors;
	}
}
?>