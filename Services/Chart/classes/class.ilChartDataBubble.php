<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Chart/classes/class.ilChartData.php");

/**
 * Bubble chart data
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesChart
 */
class ilChartDataBubble extends ilChartData
{
	protected function getTypeString()
	{
		return "bubbles";
	}

	/**
	 * Set data
	 *
	 * @param float $a_x x
	 * @param float $a_y y
	 * @param float $a_r radius
	 */
	public function addPoint($a_x, $a_y, $a_r)
	{
		$this->data[] = array($a_x, $a_y, $a_r);
	}

}

?>