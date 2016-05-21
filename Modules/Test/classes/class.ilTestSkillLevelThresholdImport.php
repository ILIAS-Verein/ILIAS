<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package     Modules/Test(QuestionPool)
 */
class ilTestSkillLevelThresholdImport
{
	/**
	 * @var integer
	 */
	protected $importSkillBaseId = null;
	/**
	 * @var integer
	 */
	protected $importSkillTrefId = null;
	
	/**
	 * @var integer
	 */
	protected $importLevelId = null;
	/**
	 * @var integer
	 */
	protected $importOrderIndex = null;
	
	/**
	 * @var integer
	 */
	protected $threshold = null;
	
	/**
	 * @var string
	 */
	protected $originalLevelTitle = null;
	/**
	 * @var string
	 */
	protected $originalLevelDescription = null;
	
	/**
	 * @return int
	 */
	public function getImportSkillBaseId()
	{
		return $this->importSkillBaseId;
	}
	
	/**
	 * @param int $importSkillBaseId
	 */
	public function setImportSkillBaseId($importSkillBaseId)
	{
		$this->importSkillBaseId = $importSkillBaseId;
	}
	
	/**
	 * @return int
	 */
	public function getImportSkillTrefId()
	{
		return $this->importSkillTrefId;
	}
	
	/**
	 * @param int $importSkillTrefId
	 */
	public function setImportSkillTrefId($importSkillTrefId)
	{
		$this->importSkillTrefId = $importSkillTrefId;
	}
	
	/**
	 * @return int
	 */
	public function getImportLevelId()
	{
		return $this->importLevelId;
	}
	
	/**
	 * @param int $importLevelId
	 */
	public function setImportLevelId($importLevelId)
	{
		$this->importLevelId = $importLevelId;
	}
	
	/**
	 * @return int
	 */
	public function getImportOrderIndex()
	{
		return $this->importOrderIndex;
	}
	
	/**
	 * @param int $importOrderIndex
	 */
	public function setImportOrderIndex($importOrderIndex)
	{
		$this->importOrderIndex = $importOrderIndex;
	}
	
	/**
	 * @return int
	 */
	public function getThreshold()
	{
		return $this->threshold;
	}
	
	/**
	 * @param int $threshold
	 */
	public function setThreshold($threshold)
	{
		$this->threshold = $threshold;
	}
	
	/**
	 * @return string
	 */
	public function getOriginalLevelTitle()
	{
		return $this->originalLevelTitle;
	}
	
	/**
	 * @param string $originalLevelTitle
	 */
	public function setOriginalLevelTitle($originalLevelTitle)
	{
		$this->originalLevelTitle = $originalLevelTitle;
	}
	
	/**
	 * @return string
	 */
	public function getOriginalLevelDescription()
	{
		return $this->originalLevelDescription;
	}
	
	/**
	 * @param string $originalLevelDescription
	 */
	public function setOriginalLevelDescription($originalLevelDescription)
	{
		$this->originalLevelDescription = $originalLevelDescription;
	}
}