<?php
// This is the hotfix file for ILIAS 5.3.x DB fixes
// This file should be used, if bugfixes need DB changes, but the
// main db update script cannot be used anymore, since it is
// impossible to merge the changes with the trunk.
//
// IMPORTANT: The fixes done here must ALSO BE reflected in the trunk.
// The trunk needs to work in both cases !!!
// 1. If the hotfixes have been applied.
// 2. If the hotfixes have not been applied.
?>
<#1>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#2>
<?php
$ilDB->query("
UPDATE il_dcl_stloc1_value 
SET value = NULL 
WHERE value = '[]' 
       AND record_field_id IN (
               SELECT rf.id 
               FROM il_dcl_record_field rf 
               INNER JOIN il_dcl_field f ON f.id = rf.field_id 
               WHERE f.datatype_id = 14
       )
");
?>
<#3>
<?php

$query = "
	SELECT	qpl.question_id qid,
			qpl.points qpl_points,
			answ.points answ_points
	
	FROM qpl_questions qpl
	
	INNER JOIN qpl_qst_essay qst
	ON qst.question_fi = qpl.question_id
	
	INNER JOIN qpl_a_essay answ
	ON answ.question_fi = qst.question_fi
	
	WHERE qpl.question_id IN(
	
		SELECT keywords.question_fi
	
		FROM qpl_a_essay keywords
	
		INNER JOIN qpl_qst_essay question
		ON question.question_fi = keywords.question_fi
		AND question.keyword_relation = {$ilDB->quote('', 'text')}
	
		WHERE keywords.answertext = {$ilDB->quote('', 'text')}
		GROUP BY keywords.question_fi
		HAVING COUNT(keywords.question_fi) = {$ilDB->quote(1, 'integer')}
		
	)
";

$res = $ilDB->query($query);

while( $row = $ilDB->fetchAssoc($res) )
{
	if( $row['answ_points'] > $row['qpl_points'] )
	{
		$ilDB->update('qpl_questions',
			array('points' => array('float', $row['answ_points'])),
			array('question_id' => array('integer', $row['qid']))
		);
	}
	
	$ilDB->manipulateF(
		"DELETE FROM qpl_a_essay WHERE question_fi = %s",
		array('integer'), array($row['qid'])
	);
	
	$ilDB->update('qpl_qst_essay',
		array('keyword_relation' => array('text', 'non')),
		array('question_fi' => array('integer', $row['qid']))
	);
}

?>
<#4>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5>
<?php
if (!$ilDB->tableColumnExists(ilOrgUnitPermission::TABLE_NAME, 'protected')) {
	$ilDB->addTableColumn(ilOrgUnitPermission::TABLE_NAME, 'protected', [
		"type"    => "integer",
		"length"  => 1,
		"default" => 0,
	]);
}
$ilDB->manipulate("UPDATE il_orgu_permissions SET protected = 1 WHERE parent_id = -1");
?>
<#6>
<?php
if( $ilDB->indexExistsByFields('cmi_objective', array('id')) )
{
	$ilDB->dropIndexByFields('cmi_objective',array('id'));
}
?>
<#7>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#8>
<?php
if (!$ilDB->indexExistsByFields('page_style_usage', array('page_id', 'page_type', 'page_lang', 'page_nr')) )
{
	$ilDB->addIndex('page_style_usage',array('page_id', 'page_type', 'page_lang', 'page_nr'),'i1');
}
?>
<#9>
<?php

include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

$rp_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId("read_learning_progress");
$ep_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('edit_learning_progress');
$w_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('write');
if($rp_ops_id && $ep_ops_id && $w_ops_id)
{			
	// see ilObjectLP
	$lp_types = array('mcst');

	foreach($lp_types as $lp_type)
	{
		$lp_type_id = ilDBUpdateNewObjectType::getObjectTypeId($lp_type);
		if($lp_type_id)
		{			
			ilDBUpdateNewObjectType::addRBACOperation($lp_type_id, $rp_ops_id);				
			ilDBUpdateNewObjectType::addRBACOperation($lp_type_id, $ep_ops_id);				
			ilDBUpdateNewObjectType::cloneOperation($lp_type, $w_ops_id, $rp_ops_id);
			ilDBUpdateNewObjectType::cloneOperation($lp_type, $w_ops_id, $ep_ops_id);
		}
	}
}
?>
<#10>
<?php
$set = $ilDB->query("
  SELECT obj_id, title, description, role_id, usr_id FROM object_data
  INNER JOIN role_data role ON role.role_id = object_data.obj_id
  INNER JOIN rbac_ua on role.role_id = rol_id
  WHERE title LIKE '%il_orgu_superior%' OR title LIKE '%il_orgu_employee%'
");
$assigns = [];
$superior_position_id = ilOrgUnitPosition::getCorePositionId(ilOrgUnitPosition::CORE_POSITION_SUPERIOR);
$employee_position_id = ilOrgUnitPosition::getCorePositionId(ilOrgUnitPosition::CORE_POSITION_EMPLOYEE);

while ($res = $ilDB->fetchAssoc($set)) {
	$user_id = $res['usr_id'];

	$tmp = explode("_", $res['title']);
	$orgu_ref_id = (int) $tmp[3];
	if ($orgu_ref_id == 0) {
		//$ilLog->write("User $user_id could not be assigned to position. Role description does not contain object id of orgu. Skipping.");
		continue;
	}

	$tmp = explode("_", $res['title']); //il_orgu_[superior|employee]_[$ref_id]
	$role_type = $tmp[2]; // [superior|employee]

	if ($role_type == 'superior')
		$position_id = $superior_position_id;
	elseif ($role_type == 'employee')
		$position_id = $employee_position_id;
	else {
		//$ilLog->write("User $user_id could not be assigned to position. Role type seems to be neither superior nor employee. Skipping.");
		continue;
	}
	if(!ilOrgUnitUserAssignment::findOrCreateAssignment(
		$user_id,
		$position_id,
		$orgu_ref_id)) {
		//$ilLog->write("User $user_id could not be assigned to position $position_id, in orgunit $orgu_ref_id . One of the ids might not actually exist in the db. Skipping.");
	}
}
?>
<#11>
<?php
	$ilDB->manipulate('UPDATE exc_mem_ass_status SET status='.$ilDB->quote('notgraded', 'text').' WHERE status = '.$ilDB->quote('', 'text'));
?>
<#12>
<?php

$query = 'SELECT MAX(meta_description_id) desc_id from il_meta_description ';
$res = $ilDB->query($query);
while($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT))
{
	$ilDB->dropSequence("il_meta_description");
	$ilDB->createSequence("il_meta_description", $row->desc_id + 100);
}
?>
<#13>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#14>
<?php

$client_id = basename(CLIENT_DATA_DIR);
$web_path = ILIAS_ABSOLUTE_PATH . "/" . ILIAS_WEB_DIR . "/" . $client_id;
$sec_path = $web_path."/sec";

if(!file_exists($sec_path))
{
	ilUtil::makeDir($sec_path);
}

$old_path = $web_path."/IASS";
$new_path = $sec_path."/ilIndividualAssessment";
if(file_exists($old_path))
{
	rename($old_path, $new_path);
}

?>
<#15>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#16>
<?php

$query = 'select id from adm_settings_template  '.
	'where title = '. $ilDB->quote('il_astpl_loc_initial','text').
	'or title = '. $ilDB->quote('il_astpl_loc_qualified','text');
$res = $ilDB->query($query);
while($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT))
{
	$ilDB->replace(
		'adm_set_templ_value', 
		[
           	'template_id' => ['integer', $row->id],
			 'setting' => ['text', 'pass_scoring']
		],
		[
			'value' => ['integer',0],
			'hide' => ['integer',1]
		]
	);
}
?>
<#17>
<?php
$ilDB->modifyTableColumn('il_dcl_tableview', 'roles',array('type' => 'clob'));
?>
<#18>
<?php
/*
* This hotfix removes org unit assignments of user who don't exist anymore
* select all user_ids from usr_data and remove all il_orgu_ua entries which have an user_id from an user who doesn't exist anymore
*/
global $ilDB;
$q = "DELETE FROM il_orgu_ua WHERE user_id NOT IN (SELECT usr_id FROM usr_data)";
$ilDB->manipulate($q);
?>
<#19>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#20>
<?php
$setting = new ilSetting();
$ilrqtix = $setting->get('iloscmsgidx1', 0);
if (!$ilrqtix) {
	$ilDB->addIndex('osc_messages', array('user_id'), 'i1');
	$setting->set('iloscmsgidx1', 1);
}
?>
<#21>
<?php
$setting = new ilSetting();
$ilrqtix = $setting->get('iloscmsgidx2', 0);
if (!$ilrqtix) {
	$ilDB->addIndex('osc_messages', array('conversation_id'), 'i2');
	$setting->set('iloscmsgidx2', 1);
}
?>
<#22>
<?php
$setting = new ilSetting();
$ilrqtix = $setting->get('iloscmsgidx3', 0);
if (!$ilrqtix) {
	$ilDB->addIndex('osc_messages', array('conversation_id', 'user_id', 'timestamp'), 'i3');
	$setting->set('iloscmsgidx3', 1);
}
?>