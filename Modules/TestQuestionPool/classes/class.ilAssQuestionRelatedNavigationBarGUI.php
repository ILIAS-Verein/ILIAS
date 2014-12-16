<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/TestQuestionPool
 */
class ilAssQuestionRelatedNavigationBarGUI
{
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var ilLanguage
	 */
	protected $lng;

	protected $instantResponseCmd;

	protected $instantResponseEnabled;

	protected $hintProvidingEnabled;

	protected $hintRequestsPossible;

	protected $hintRequestsExist;
	
	public function __construct(ilCtrl $ctrl, ilLanguage $lng)
	{
		$this->ctrl = $ctrl;
		$this->lng = $lng;
	}

	public function setHintRequestsExist($hintRequestsExist)
	{
		$this->hintRequestsExist = $hintRequestsExist;
	}

	public function doesHintRequestsExist()
	{
		return $this->hintRequestsExist;
	}

	public function setHintRequestsPossible($hintRequestsPossible)
	{
		$this->hintRequestsPossible = $hintRequestsPossible;
	}

	public function areHintRequestsPossible()
	{
		return $this->hintRequestsPossible;
	}

	public function setHintProvidingEnabled($hintProvidingEnabled)
	{
		$this->hintProvidingEnabled = $hintProvidingEnabled;
	}

	public function isHintProvidingEnabled()
	{
		return $this->hintProvidingEnabled;
	}

	public function setInstantResponseEnabled($instantFeedbackEnabled)
	{
		$this->instantResponseEnabled = $instantFeedbackEnabled;
	}

	public function isInstantResponseEnabled()
	{
		return $this->instantResponseEnabled;
	}

	public function setInstantResponseCmd($instantResponseCmd)
	{
		$this->instantResponseCmd = $instantResponseCmd;
	}

	public function getInstantResponseCmd()
	{
		return $this->instantResponseCmd;
	}

	public function getHTML()
	{
		$navTpl = new ilTemplate('tpl.qst_question_related_navigation.html', true, true, 'Modules/TestQuestionPool');

		$parseQuestionRelatedNavigation = false;

		if( $this->isInstantResponseEnabled() )
		{
			$navTpl->setCurrentBlock("direct_feedback");
			$navTpl->setVariable("CMD_SHOW_INSTANT_RESPONSE", $this->getInstantResponseCmd());
			$navTpl->setVariable("TEXT_SHOW_INSTANT_RESPONSE", $this->lng->txt("check"));
			$navTpl->parseCurrentBlock();

			$parseQuestionRelatedNavigation = true;
		}

		if( $this->isHintProvidingEnabled() )
		{
			require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionHintTracking.php';
			require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionHintRequestGUI.php';

			if( $this->areHintRequestsPossible() )
			{
				if( $this->doesHintRequestsExist() )
				{
					$buttonText = $this->lng->txt("button_request_next_question_hint");
				}
				else
				{
					$buttonText = $this->lng->txt("button_request_question_hint");
				}
				
				$href = $this->ctrl->getLinkTargetByClass('ilAssQuestionHintRequestGUI', ilAssQuestionHintRequestGUI::CMD_CONFIRM_REQUEST);

				$navTpl->setCurrentBlock("button_request_next_question_hint");
				$navTpl->setVariable("HREF_REQUEST_NEXT_QUESTION_HINT", $href);
				$navTpl->setVariable("TEXT_REQUEST_NEXT_QUESTION_HINT", $buttonText);
				$navTpl->parseCurrentBlock();

				$parseQuestionRelatedNavigation = true;
			}

			if( $this->doesHintRequestsExist() )
			{
				$href = $this->ctrl->getLinkTargetByClass('ilAssQuestionHintRequestGUI', ilAssQuestionHintRequestGUI::CMD_SHOW_LIST);

				$navTpl->setCurrentBlock("button_show_requested_question_hints");
				$navTpl->setVariable("HREF_SHOW_REQUESTED_QUESTION_HINTS", $href);
				$navTpl->setVariable("TEXT_SHOW_REQUESTED_QUESTION_HINTS", $this->lng->txt("button_show_requested_question_hints"));
				$navTpl->parseCurrentBlock();

				$parseQuestionRelatedNavigation = true;
			}
		}

		if( $parseQuestionRelatedNavigation )
		{
			$navTpl->setCurrentBlock("question_related_navigation");
			$navTpl->parseCurrentBlock();
		}
		
		return $navTpl->get();
	}
} 