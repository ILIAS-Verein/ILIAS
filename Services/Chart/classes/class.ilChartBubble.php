<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Chart/classes/class.ilChart.php");

/**
 *  Bubble chart class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup
 */
class ilChartBubble extends ilChart
{
	protected $x_min = false;
	protected $x_max = false;
	protected $y_min = false;
	protected $y_max = false;


	public function getDataInstance($a_type = null)
	{
		include_once "Services/Chart/classes/class.ilChartDataBubble.php";
		return new ilChartDataBubble();
	}

	protected function isValidDataType(ilChartData $a_series)
	{
		return ($a_series instanceof ilChartDataBubble);
	}

	protected function addCustomJS()
	{
		global $tpl;

		$tpl->addJavascript("Services/Chart/js/flot/jquery.flot.pie.js");
		$tpl->addJavascript("Services/Chart/js/flot/jquery.flot.highlighter.js");
		$tpl->addJavascript("Services/Chart/js/flot/jquery.flot.categories.js");
		$tpl->addJavascript("Services/Chart/js/flot/jquery.flot.spider.js");
		$tpl->addJavascript("Services/Chart/js/flot/jquery.flot.JUMlib.js");
		$tpl->addJavascript("Services/Chart/js/flot/jquery.flot.bubbles.ilias.js");
	}




	// from 4.4

	/**
	 * Set min max
	 *
	 * @param
	 * @return
	 */
	function setMinMax($a_x_min, $a_x_max, $a_y_min, $a_y_max)
	{
		$this->x_min = $a_x_min;
		$this->x_max = $a_x_max;
		$this->y_min = $a_y_min;
		$this->y_max = $a_y_max;
	}


	/**
	 * Render
	 */
	public function getHTML()
	{
		global $tpl;

		if(!$this->isValid())
		{
			return;
		}

		$this->initJS();

		$chart = new ilTemplate("tpl.grid2.html", true, true, "Services/Chart");
		$chart->setVariable("ID", $this->id);
		$chart->setVariable("WIDTH", $this->width);
		$chart->setVariable("HEIGHT", $this->height);

		$data = array();
		foreach($this->data as $idx => $series)
		{
			$data[] = $series->getData();

		}

		include_once("./Services/JSON/classes/class.ilJsonUtil.php");
		$cfg = array();
		$cfg["series"] = array("bubbles" => array(
			"active" => true,
			"show" => true,
			"bubblelabel" => array("show" => true),
			"linewidth" => 2
		));
		$cfg["grid"] = array(
			"hoverable" => true,
			"clickable" => true,
			"editable" => true
		);
		if ($ticks = $this->getTicks())
		{
			foreach($ticks as $axis => $def)
			{
				if (is_array($def))
				{
					foreach ($def as $k => $v)
					{
						$cfg[$axis."axis"]["ticks"][] = array($k, $v);
					}
				}
			}
		}

		if ($this->x_min !== false)
		{
			$cfg["xaxis"]["min"] = $this->x_min;
		}
		if ($this->x_max !== false)
		{
			$cfg["xaxis"]["max"] = $this->x_max;
		}
		if ($this->y_min !== false)
		{
			$cfg["yaxis"]["min"] = $this->y_min;
		}
		if ($this->y_max !== false)
		{
			$cfg["yaxis"]["max"] = $this->y_max;
		}

		$cfg["xaxis"]["font"] =
			array(
				"size" => 11,
				"family" => "sans-serif",
				"color" => "rgba(30,30,30,1)"
			);
		$cfg["yaxis"]["font"] =
			array(
				"size" => 11,
				"family" => "sans-serif",
				"color" => "rgba(30,30,30,1)"
			);

		//ticks: [[1, "m"], [2, "n"], [3, "o"], [4, "p"], [5, "q"], [6, "r"], [7, "s"]]
		$chart->setVariable("CFG", ilJsonUtil::encode($cfg));
		$chart->setVariable("DATA", ilJsonUtil::encode($data));

		$ret = $chart->get();
//echo htmlentities($ret);
		return $ret;
	}

}

?>