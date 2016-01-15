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
		// no need to do more
		$ex = new ilObjExample();
		$ex->create(true);		
	}
}

?>