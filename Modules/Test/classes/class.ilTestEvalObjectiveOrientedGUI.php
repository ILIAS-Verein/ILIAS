<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Modules/Test/classes/class.ilTestServiceGUI.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 *
 * @ilCtrl_Calls ilTestEvalObjectiveOrientedGUI: ilTestResultsToolbarGUI
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
			$this->lng->txt('tst_results_back_introduction'),
			$this->ctrl->getLinkTargetByClass('ilobjtestgui', 'participants')
		);

		$toolbar = $this->buildUserTestResultsToolbarGUI();
		$this->ctrl->setParameter($this, 'pdf', '1');
		$toolbar->setPdfExportLinkTarget( $this->ctrl->getLinkTarget($this, 'showVirtualPass') );
		$this->ctrl->setParameter($this, 'pdf', '');
		$toolbar->build();

		$testSession = $this->testSessionFactory->getSession();
		
		$virtualSequence = $this->service->buildVirtualSequence($testSession);
		$userResults = $this->service->getVirtualSequenceUserResults($virtualSequence);
		
		require_once 'Modules/Course/classes/Objectives/class.ilLOTestQuestionAdapter.php';
		$objectivesAdapter = ilLOTestQuestionAdapter::getInstance($testSession);

		$objectivesList = $this->buildQuestionRelatedObjectivesList($objectivesAdapter, $virtualSequence);
		$objectivesList->loadObjectivesTitles();

		require_once 'Modules/Test/classes/class.ilTestResultHeaderLabelBuilder.php';
		$testResultHeaderLabelBuilder = new ilTestResultHeaderLabelBuilder($this->lng, $this->objCache);

		$testResultHeaderLabelBuilder->setObjectiveOrientedContainerId($testSession->getObjectiveOrientedContainerId());
		$testResultHeaderLabelBuilder->setUserId($testSession->getUserId());
		$testResultHeaderLabelBuilder->setTestObjId($this->object->getId());
		$testResultHeaderLabelBuilder->setTestRefId($this->object->getRefId());
		$testResultHeaderLabelBuilder->initObjectiveOrientedMode();

		$tpl = new ilTemplate('tpl.il_as_tst_virtual_pass_details.html', false, false, 'Modules/Test');
		
		$tpl->setVariable("TEXT_HEADING", $testResultHeaderLabelBuilder->getVirtualPassHeaderLabel(
			$objectivesList->getUniqueObjectivesString()
		));

		$overview = $this->getPassDetailsOverview(
			$userResults, $testSession->getActiveId(), null, $this, "showVirtualPass",
			$command_solution_details, $questionAnchorNav, $objectivesList
		);
		$tpl->setVariable("PASS_DETAILS", $overview);
		
		$this->populateContent($this->ctrl->getHTML($toolbar).$tpl->get());
	}
}