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
		global $ilObjDataCache;
		
		$this->tabs->setBackTarget(
			$this->lng->txt('tst_results_back_introduction'),
			$this->ctrl->getLinkTargetByClass('ilobjtestgui', 'participants')
		);

		$toolbar = $this->buildUserTestResultsToolbarGUI();
		
		$this->ctrl->setParameter($this, 'pdf', '1');
		$toolbar->setPdfExportLinkTarget( $this->ctrl->getLinkTarget($this, 'outParticipantsPassDetails') );
		$this->ctrl->setParameter($this, 'pdf', '');
		$toolbar->build();

		$tpl = new ilTemplate('tpl.il_as_tst_virtual_pass_details.html', false, false, 'Modules/Test');

		require_once 'Modules/Test/classes/class.ilTestResultHeaderLabelBuilder.php';
		$testResultHeaderLabelBuilder = new ilTestResultHeaderLabelBuilder($this->lng, $ilObjDataCache);
		$tpl->setVariable("TEXT_HEADING", $testResultHeaderLabelBuilder->getVirtualPassHeaderLabel());
		
		$this->populateContent($this->ctrl->getHTML($toolbar).$tpl->get());
	}
}