<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Modules/Test/classes/class.ilTestServiceGUI.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilTestEvalObjectiveOrientedGUI extends ilTestServiceGUI
{
	public function executeCommand()
	{
		$this->ctrl->saveParameter($this, "active_id");
		
		switch( $this->ctrl->getNextClass($this) )
		{
			default:
				$this->handleTabs('results_objective_oriented');
				$cmd = $this->ctrl->getCmd().'Cmd';
				$this->$cmd();
		}
	}
	
	private function showVirtualPassCmd()
	{
		$this->tabs->setBackTarget(
			$this->lng->txt('tst_results_back_introduction'), $this->ctrl->getLinkTargetByClass('ilobjtestgui', 'participants')
		);
		
		$this->tpl->setContent('blubb');
	}
}