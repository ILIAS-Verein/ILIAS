<#1>
<?php

if(!$ilDB->tableColumnExists('loc_settings','it_type')) 
{
    $ilDB->addTableColumn(
        'loc_settings',
        'it_type',
        array(
            'type' => 'integer',
			'length' => 1,
            'notnull' => false,
            'default' => 5
        ));
}
?>
<#2>
<?php

if(!$ilDB->tableColumnExists('loc_settings','qt_type')) 
{
    $ilDB->addTableColumn(
        'loc_settings',
        'qt_type',
        array(
            'type' => 'integer',
			'length' => 1,
            'notnull' => false,
            'default' => 1
        ));
}

?>

<#3>
<?php

if(!$ilDB->tableColumnExists('loc_settings','it_start')) 
{
    $ilDB->addTableColumn(
        'loc_settings',
        'it_start',
        array(
            'type' => 'integer',
			'length' => 1,
            'notnull' => false,
            'default' => 1
        ));
}

?>

<#4>
<?php

if(!$ilDB->tableColumnExists('loc_settings','qt_start')) 
{
    $ilDB->addTableColumn(
        'loc_settings',
        'qt_start',
        array(
            'type' => 'integer',
			'length' => 1,
            'notnull' => false,
            'default' => 1
        ));
}
?>

<#5>
<?php


$query = 'UPDATE loc_settings SET it_type = '.$ilDB->quote(1,'integer').' WHERE type = '.$ilDB->quote(1,'integer');
$res = $ilDB->manipulate($query);

?>

<#6>
<?php


$query = 'UPDATE loc_settings SET qt_start = '.$ilDB->quote(0,'integer').' WHERE type = '.$ilDB->quote(4,'integer');
$res = $ilDB->manipulate($query);

?>

<#7>
<?php

if(!$ilDB->tableExists('loc_tst_assignments'))
{
	$ilDB->createTable('loc_tst_assignments', array(
		'assignment_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'container_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'assignment_type' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true,
			'default' => 0
		),
		'objective_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'tst_ref_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
	));

	$ilDB->addPrimaryKey('loc_tst_assignments', array('assignment_id'));
	$ilDB->createSequence('loc_tst_assignments');

}
?>

<#8>
<?php
if( !$ilDB->tableExists('tst_seq_qst_optional') )
{
	$ilDB->createTable('tst_seq_qst_optional', array(
		'active_fi' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'pass' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'question_fi' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
	));
}
?>

<#9>
<?php
if( !$ilDB->tableColumnExists('tst_sequence', 'ans_opt_confirmed') )
{
	$ilDB->addTableColumn('tst_sequence', 'ans_opt_confirmed', array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => true,
		'default' => 0
	));
}
?>

<#10>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#11>
<?php

if(!$ilDB->tableColumnExists('loc_settings','passed_obj_mode')) 
{
    $ilDB->addTableColumn(
        'loc_settings',
        'passed_obj_mode',
        array(
            'type' => 'integer',
			'length' => 1,
            'notnull' => false,
            'default' => 1
        ));
}
?>

<#12>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#13>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#14>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#15>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#16>
<?php
if( !$ilDB->tableColumnExists('qpl_questionpool', 'skill_service') )
{
	$ilDB->addTableColumn('qpl_questionpool', 'skill_service', array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => false,
		'default' => null
	));

	$ilDB->manipulateF(
		'UPDATE qpl_questionpool SET skill_service = %s',
		array('integer'), array(0)
	);
}
?>

<#17>
<?php
if( !$ilDB->tableExists('qpl_qst_skl_assigns') )
{
	$ilDB->createTable('qpl_qst_skl_assigns', array(
		'obj_fi' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'question_fi' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'skill_base_fi' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'skill_tref_fi' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'skill_points' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
	));

	$ilDB->addPrimaryKey('qpl_qst_skl_assigns', array('obj_fi', 'question_fi', 'skill_base_fi', 'skill_tref_fi'));

	if( $ilDB->tableExists('tst_skl_qst_assigns') )
	{
		$res = $ilDB->query("
			SELECT tst_skl_qst_assigns.*, tst_tests.obj_fi
			FROM tst_skl_qst_assigns
			INNER JOIN tst_tests ON test_id = test_fi
		");

		while( $row = $ilDB->fetchAssoc($res) )
		{
			$ilDB->replace('qpl_qst_skl_assigns',
				array(
					'obj_fi' => array('integer', $row['obj_fi']),
					'question_fi' => array('integer', $row['question_fi']),
					'skill_base_fi' => array('integer', $row['skill_base_fi']),
					'skill_tref_fi' => array('integer', $row['skill_tref_fi'])
				),
				array(
					'skill_points' => array('integer', $row['skill_points'])
				)
			);
		}

		$ilDB->dropTable('tst_skl_qst_assigns');
	}
}
?>

<#18>
<?php
$setting = new ilSetting();

if( !$setting->get('dbup_tst_skl_thres_mig_done', 0) )
{
	if( !$ilDB->tableExists('tst_threshold_tmp') )
	{
		$ilDB->createTable('tst_threshold_tmp', array(
			'test_id' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true,
				'default' => 0
			),
			'obj_id' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true,
				'default' => 0
			)
		));

		$ilDB->addPrimaryKey('tst_threshold_tmp', array('test_id'));
	}

	$res = $ilDB->query("
		SELECT DISTINCT tst_tests.test_id, obj_fi FROM tst_tests
		INNER JOIN tst_skl_thresholds ON test_fi = tst_tests.test_id
		LEFT JOIN tst_threshold_tmp ON tst_tests.test_id = tst_threshold_tmp.test_id
		WHERE tst_threshold_tmp.test_id IS NULL
	");

	while( $row = $ilDB->fetchAssoc($res) )
	{
		$ilDB->replace('tst_threshold_tmp',
			array('test_id' => array('integer', $row['test_id'])),
			array('obj_id' => array('integer', $row['obj_fi']))
		);
	}

	if( !$ilDB->tableColumnExists('tst_skl_thresholds', 'tmp') )
	{
		$ilDB->addTableColumn('tst_skl_thresholds', 'tmp', array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => false,
			'default' => null
		));
	}

	$setting->set('dbup_tst_skl_thres_mig_done', 1);
}
?>

<#19>
<?php
if( $ilDB->tableExists('tst_threshold_tmp') )
{
	$stmtSelectSklPointSum = $ilDB->prepare(
		"SELECT skill_base_fi, skill_tref_fi, SUM(skill_points) points_sum FROM qpl_qst_skl_assigns
			WHERE obj_fi = ? GROUP BY skill_base_fi, skill_tref_fi", array('integer')
	);

	$stmtUpdatePercentThresholds = $ilDB->prepareManip(
		"UPDATE tst_skl_thresholds SET tmp = ROUND( ((threshold * 100) / ?), 0 )
			WHERE test_fi = ? AND skill_base_fi = ? AND skill_tref_fi = ?",
		array('integer', 'integer', 'integer', 'integer')
	);

	$res1 = $ilDB->query("
		SELECT DISTINCT test_id, obj_id FROM tst_threshold_tmp
		INNER JOIN tst_skl_thresholds ON test_fi = test_id
		WHERE tmp IS NULL
	");

	while( $row1 = $ilDB->fetchAssoc($res1) )
	{
		$res2 = $ilDB->execute($stmtSelectSklPointSum, array($row1['obj_id']));

		while( $row2 = $ilDB->fetchAssoc($res2) )
		{
			$ilDB->execute($stmtUpdatePercentThresholds, array(
				$row2['points_sum'], $row1['test_id'], $row2['skill_base_fi'], $row2['skill_tref_fi']
			));
		}
	}
}
?>

<#20>
<?php
if( $ilDB->tableExists('tst_threshold_tmp') )
{
	$ilDB->dropTable('tst_threshold_tmp');
}
?>

<#21>
<?php
if( $ilDB->tableColumnExists('tst_skl_thresholds', 'tmp') )
{
	$ilDB->manipulate("UPDATE tst_skl_thresholds SET threshold = tmp");
	$ilDB->dropTableColumn('tst_skl_thresholds', 'tmp');
}
?>

<#22>
<?php
if( !$ilDB->tableColumnExists('qpl_qst_skl_assigns', 'eval_mode') )
{
	$ilDB->addTableColumn('qpl_qst_skl_assigns', 'eval_mode', array(
		'type' => 'text',
		'length' => 16,
		'notnull' => false,
		'default' => null
	));

	$ilDB->manipulateF(
		"UPDATE qpl_qst_skl_assigns SET eval_mode = %s", array('text'), array('result')
	);
}
?>

<#23>
<?php
if( !$ilDB->tableExists('qpl_qst_skl_sol_expr') )
{
	$ilDB->createTable('qpl_qst_skl_sol_expr', array(
		'question_fi' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'skill_base_fi' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'skill_tref_fi' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'order_index' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'expression' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true,
			'default' => ''
		),
		'points' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
	));

	$ilDB->addPrimaryKey('qpl_qst_skl_sol_expr', array(
		'question_fi', 'skill_base_fi', 'skill_tref_fi', 'order_index'
	));
}
?>

<#24>
<?php
$res = $ilDB->query("
	SELECT DISTINCT(question_fi) FROM qpl_qst_skl_assigns
	LEFT JOIN qpl_questions ON question_fi = question_id
	WHERE question_id IS NULL
");

$deletedQuestionIds = array();

while($row = $ilDB->fetchAssoc($res))
{
	$deletedQuestionIds[] = $row['question_fi'];
}

$inDeletedQuestionIds = $ilDB->in('question_fi', $deletedQuestionIds, false, 'integer');

$ilDB->query("
	DELETE FROM qpl_qst_skl_assigns WHERE $inDeletedQuestionIds
");
?>

<#25>
<?php
$row = $ilDB->fetchAssoc($ilDB->queryF(
	'SELECT COUNT(*) cnt FROM qpl_qst_skl_assigns LEFT JOIN skl_tree_node ON skill_base_fi = obj_id WHERE type = %s',
	array('text'), array('sktr')
));

if( $row['cnt'] )
{
	$res = $ilDB->queryF(
		'SELECT obj_fi, question_fi, skill_base_fi, skill_tref_fi FROM qpl_qst_skl_assigns LEFT JOIN skl_tree_node ON skill_base_fi = obj_id WHERE type = %s',
		array('text'), array('sktr')
	);

	while($row = $ilDB->fetchAssoc($res))
	{
		$ilDB->update('qpl_qst_skl_assigns',
			array(
				'skill_base_fi' => array('integer', $row['skill_tref_fi']),
				'skill_tref_fi' => array('integer', $row['skill_base_fi'])
			),
			array(
				'obj_fi' => array('integer', $row['obj_fi']),
				'question_fi' => array('integer', $row['question_fi']),
				'skill_base_fi' => array('integer', $row['skill_base_fi']),
				'skill_tref_fi' => array('integer', $row['skill_tref_fi'])
			)
		);
	}
}
?>

<#26>
<?php
$ilDB->manipulateF(
	"UPDATE qpl_qst_skl_assigns SET eval_mode = %s WHERE eval_mode IS NULL", array('text'), array('result')
);
?>
<#27>
<?php
if(!$ilDB->tableColumnExists('skl_user_skill_level', 'unique_identifier'))
{
	$ilDB->addTableColumn('skl_user_skill_level', 'unique_identifier', array(
		'type' => 'text',
		'length' => 80,
		'notnull' => false
	));
}
?>
<#28>
<?php
$ilDB->addTableColumn("il_wiki_data", "link_md_values",array (
	"type" => "integer",
	"length" => 1,
	"notnull" => false,
	"default" => 0,
));
?>
<#29>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#30>
<?php
	if (!$ilDB->tableColumnExists('skl_tree_node', 'creation_date'))
	{
		$ilDB->addTableColumn('skl_tree_node', 'creation_date', array(
				"type" => "timestamp",
				"notnull" => false,
		));
	}
?>
<#31>
<?php
if (!$ilDB->tableColumnExists('skl_tree_node', 'import_id'))
{
	$ilDB->addTableColumn('skl_tree_node', 'import_id', array(
			"type" => "text",
			"length" => 50,
			"notnull" => false
	));
}
?>
<#32>
<?php
if (!$ilDB->tableColumnExists('skl_level', 'creation_date'))
{
	$ilDB->addTableColumn('skl_level', 'creation_date', array(
			"type" => "timestamp",
			"notnull" => false,
	));
}
?>
<#33>
<?php
if (!$ilDB->tableColumnExists('skl_level', 'import_id'))
{
	$ilDB->addTableColumn('skl_level', 'import_id', array(
			"type" => "text",
			"length" => 50,
			"notnull" => false
	));
}
?>
<#34>
<?php
$ilCtrlStructureReader->getStructure();
?>

	