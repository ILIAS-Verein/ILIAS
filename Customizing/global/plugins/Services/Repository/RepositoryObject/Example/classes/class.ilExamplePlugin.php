<?php

include_once("./Services/Repository/classes/class.ilRepositoryObjectPlugin.php");
 
/**
* Example repository object plugin
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
*/
class ilExamplePlugin extends ilRepositoryObjectPlugin
{
	function getPluginName()
	{
		return "Example";
	}
	
	protected function uninstallCustom()
	{
		global $ilDB;
		
		if($ilDB->tableExists("rep_robj_xexo_data"))
		{
			$ilDB->dropTable("rep_robj_xexo_data");
		}
	}
}
?>
