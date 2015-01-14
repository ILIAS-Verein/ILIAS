<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Factory for test session
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 * 
 * @package		Modules/Test
 */
class ilTestSessionFactory
{
	/**
	 * singleton instance of test session
	 *
	 * @var ilTestSession|ilTestSessionDynamicQuestionSet
	 */
	private $testSession = array();
	
	/**
	 * object instance of current test
	 *
	 * @var ilObjTest
	 */
	private $testOBJ = null;
	
	/**
	 * constructor
	 * 
	 * @param ilObjTest $testOBJ
	 */
	public function __construct(ilObjTest $testOBJ)
	{
		$this->testOBJ = $testOBJ;
	}
	
	/**
	 * temporarily bugfix for resetting the state of this singleton
	 * smeyer
	 * --> BH: not required anymore
	 */
	public function reset()
	{
		$this->testSession = array();
	}
	
	
	
	
	/**
	 * creates and returns an instance of a test sequence
	 * that corresponds to the current test mode
	 * 
	 * @param integer $activeId
	 * @return ilTestSession|ilTestSessionDynamicQuestionSet
	 */
	public function getSession($activeId = null)
	{
		if( $activeId === null || $this->testSession[$activeId] === null)
		{
			switch( $this->testOBJ->getQuestionSetType() )
			{
				case ilObjTest::QUESTION_SET_TYPE_FIXED:
				case ilObjTest::QUESTION_SET_TYPE_RANDOM:

					require_once 'Modules/Test/classes/class.ilTestSession.php';
					$testSession = new ilTestSession();
					break;

				case ilObjTest::QUESTION_SET_TYPE_DYNAMIC:

					require_once 'Modules/Test/classes/class.ilTestSessionDynamicQuestionSet.php';
					$testSession = new ilTestSessionDynamicQuestionSet();
					break;
			}

			$testSession->setRefId($this->testOBJ->getRefId());
			$testSession->setTestId($this->testOBJ->getTestId());
			
			if($activeId)
			{
				$testSession->loadFromDb($activeId);
				$this->testSession[$activeId] = $testSession;
			}
			else
			{
				global $ilUser;

				$testSession->loadTestSession(
					$this->testOBJ->getTestId(), $ilUser->getId(), $_SESSION["tst_access_code"][$this->testOBJ->getTestId()]
				);
				
				return $testSession;
			}
		}

		return $this->testSession[$activeId];
	}
}
