<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Skill/classes/class.ilPersonalSkillsGUI.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilTestPersonalSkillsGUI
{
	/**
	 * @var ilLanguage
	 */
	private $lng;

	private $availableSkills;

	private $selectedSkillProfile;

	private $usrId;

	/**
	 * @var int
	 */
	private $testId;

	/**
	 * @param ilLanguage $lng
	 * @param int        $testId
	 */
	public function __construct(ilLanguage $lng, $testId)
	{
		$this->lng = $lng;
		$this->testId = $testId;
	}

	public function getHTML()
	{
		$gui = new ilPersonalSkillsGUI();

		$gui->setGapAnalysisActualStatusModePerObject($this->getTestId(), $this->lng->txt('tst_test_result'));
		$gui->setHistoryView(true);

		if( $this->getSelectedSkillProfile() )
		{
			$gui->setProfileId($this->getSelectedSkillProfile());

			$profile = new ilSkillProfile($this->getSelectedSkillProfile());
			$profileLevels = $profile->getSkillLevels();
			
			foreach($profileLevels as $skillsLevel)
			{
				foreach($this->getAvailableSkills() as $skill)
				{
					if( $this->isSameSkill($skillsLevel, $skill) )
					{
						continue(2);
					}
				}

				$gui->hideSkill($skillsLevel['base_skill_id'], $skillsLevel['tref_id']);
			}
		}

		$html = $gui->getGapAnalysisHTML($this->getUsrId(), $this->getAvailableSkills());

		return $html;
	}
	
	private function isSameSkill($skillsLevel, $skill)
	{
		if( $skillsLevel['base_skill_id'] != $skill['base_skill_id'] )
		{
			return false;
		}
		
		if( $skillsLevel['tref_id'] != $skill['tref_id'] )
		{
			return false;
		}
		
		return true;
	}

	public function setAvailableSkills($availableSkills)
	{
		$this->availableSkills = $availableSkills;
	}

	public function getAvailableSkills()
	{
		return $this->availableSkills;
	}

	public function setSelectedSkillProfile($selectedSkillProfile)
	{
		$this->selectedSkillProfile = $selectedSkillProfile;
	}

	public function getSelectedSkillProfile()
	{
		return $this->selectedSkillProfile;
	}

	public function setUsrId($usrId)
	{
		$this->usrId = $usrId;
	}

	public function getUsrId()
	{
		return $this->usrId;
	}

	/**
	 * @return int
	 */
	public function getTestId()
	{
		return $this->testId;
	}

} 