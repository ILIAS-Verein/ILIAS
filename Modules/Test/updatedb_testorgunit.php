<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

chdir('../../');
include_once 'include/inc.header.php';

/* @var ilAccess $ilAccess */
if( !$ilAccess->checkAccess('read', '', SYSTEM_FOLDER_ID) )
{
	die('administrative privileges only!');
}

try
{
	
	ilOrgUnitOperationQueries::registerNewOperation(
		ilOrgUnitOperation::OP_READ_LEARNING_PROGRESS,
		'Read Test Participants Learning Progress',
		ilOrgUnitOperationContext::CONTEXT_TST
	);
	
	ilOrgUnitOperationQueries::registerNewOperation(
		ilOrgUnitOperation::OP_ACCESS_RESULTS,
		'Access Test Participants Results',
		ilOrgUnitOperationContext::CONTEXT_TST
	);
	
	ilOrgUnitOperationQueries::registerNewOperation(
		ilOrgUnitOperation::OP_MANAGE_PARTICIPANTS,
		'Manage Test Participants',
		ilOrgUnitOperationContext::CONTEXT_TST
	);
	
	ilOrgUnitOperationQueries::registerNewOperation(
		ilOrgUnitOperation::OP_SCORE_PARTICIPANTS,
		'Score Test Participants',
		ilOrgUnitOperationContext::CONTEXT_TST
	);
	
}
catch(ilException $e)
{
}
