<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlImporter.php");

class ilExampleImporter extends ilXmlImporter
{	
	public function init()
	{
	}

	function importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping)
	{
		global $objDefinition;
		
		$path = $objDefinition->getLocation($a_entity);
		include_once $path."/class.ilObjExample.php";		
		$ex = new ilObjExample();
		$ex->create(true);		
		
		// MUST be done for the import to work
		$a_mapping->addMapping('Plugins/xexo', 'xexo', $a_id, $ex->getId());
	}
}

?>