<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Export/classes/class.ilXmlExporter.php';

class ilExampleExporter extends ilXmlExporter
{	
	public function init()
	{
		
	}
	
	public function getXmlRepresentation($a_entity, $a_schema_version, $a_id)
	{
		return "<example><config>1</config></example>";
	}
	
	public function getValidSchemaVersions($a_entity)
	{
		return array (			
			"5.0.0" => array(
				"namespace" => "http://www.ilias.de/Modules/Course/crs/5_0",
				"xsd_file" => "ilias_crs_5_0.xsd",
				"uses_dataset" => false,
				"min" => "5.0.0",
				"max" => "")
		);
	}
}