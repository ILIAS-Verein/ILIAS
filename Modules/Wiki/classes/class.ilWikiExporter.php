<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlExporter.php");

/**
 * Exporter class for wikis
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id: $
 * @ingroup ModulesWiki
 */
class ilWikiExporter extends ilXmlExporter
{
	private $ds;

	/**
	 * Initialisation
	 */
	function init()
	{
		include_once("./Modules/Wiki/classes/class.ilWikiDataSet.php");
		$this->ds = new ilWikiDataSet();
		$this->ds->setExportDirectories($this->dir_relative, $this->dir_absolute);
		$this->ds->setDSPrefix("ds");
	}


	/**
	 * Get tail dependencies
	 *
	 * @param		string		entity
	 * @param		string		target release
	 * @param		array		ids
	 * @return		array		array of array with keys "component", entity", "ids"
	 */
	function getXmlExportTailDependencies($a_entity, $a_target_release, $a_ids)
	{
		include_once("./Modules/Wiki/classes/class.ilWikiPage.php");
		$pg_ids = array();
		foreach ($a_ids as $id)
		{
			$pages = ilWikiPage::getAllPages($id);
			foreach ($pages as $p)
			{
				if (ilWikiPage::_exists("wpg", $p["id"]))
				{
					$pg_ids[] = "wpg:".$p["id"];
				}
			}
		}

		$deps = array (
			array(
				"component" => "Services/COPage",
				"entity" => "pg",
				"ids" => $pg_ids),
			array(
				"component" => "Services/Rating",
				"entity" => "rating_category",
				"ids" => $a_ids
				)
			);
		
		$advmd_ids = array();
		foreach($a_ids as $id)
		{
			$rec_ids = $this->getActiveAdvMDRecords($id);
			if(sizeof($rec_ids))
			{
				foreach($rec_ids as $rec_id)
				{
					$advmd_ids[] = $id.":".$rec_id;
				}
			}				
		}
		if(sizeof($advmd_ids))
		{
			$deps[] = array(
				"component" => "Services/AdvancedMetaData",
				"entity" => "advmd",
				"ids" => $advmd_ids
			);	
		}
		
		return $deps;
	}
	
	protected function getActiveAdvMDRecords($a_id)
	{			
		include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecord.php');
		$active = array();		
		foreach(ilAdvancedMDRecord::_getActivatedRecordsByObjectType("wiki", "wpg") as $record_obj)
		{
			$active[] = $record_obj->getRecordId();
		}		
		return array_intersect($active, ilAdvancedMDRecord::getObjRecSelection($a_id, "wpg"));						
	}

	/**
	 * Get xml representation
	 *
	 * @param	string		entity
	 * @param	string		target release
	 * @param	string		id
	 * @return	string		xml string
	 */
	public function getXmlRepresentation($a_entity, $a_schema_version, $a_id)
	{
		return $this->ds->getXmlRepresentation($a_entity, $a_schema_version, $a_id, "", true, true);
	}

	/**
	 * Returns schema versions that the component can export to.
	 * ILIAS chooses the first one, that has min/max constraints which
	 * fit to the target release. Please put the newest on top.
	 *
	 * @return
	 */
	function getValidSchemaVersions($a_entity)
	{
		return array (
			"4.1.0" => array(
				"namespace" => "http://www.ilias.de/Modules/Wiki/wiki/4_1",
				"xsd_file" => "ilias_wiki_4_1.xsd",
				"uses_dataset" => true,
				"min" => "4.1.0",
				"max" => "4.2.99"),
			"4.3.0" => array(
				"namespace" => "http://www.ilias.de/Modules/Wiki/wiki/4_3",
				"xsd_file" => "ilias_wiki_4_3.xsd",
				"uses_dataset" => true,
				"min" => "4.3.0",
				"max" => "")
		);
	}

}

?>