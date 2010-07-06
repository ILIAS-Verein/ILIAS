<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

include_once('Services/Calendar/classes/class.ilDate.php');
include_once('Services/Calendar/classes/class.ilCalendarHeaderNavigationGUI.php');
include_once('Services/Calendar/classes/class.ilCalendarUserSettings.php');
include_once('Services/Calendar/classes/class.ilCalendarAppointmentColors.php');


/** 
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
* @ilCtrl_Calls ilCalendarMonthGUI: ilCalendarAppointmentGUI
* 
* @ingroup ServicesCalendar 
*/


class ilCalendarMonthGUI
{
	protected $num_appointments = 1;
	
	protected $seed = null;
	protected $user_settings = null;

	protected $lng;
	protected $ctrl;
	protected $tabs_gui;
	protected $tpl;
	
	protected $timezone = 'UTC';

	/**
	 * Constructor
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function __construct(ilDate $seed_date)
	{
		global $ilCtrl, $lng, $ilUser,$ilTabs,$tpl;
		
		$this->seed = $seed_date;

		$this->tpl = $tpl;
		$this->lng = $lng;
		$this->ctrl = $ilCtrl;
		$this->tabs_gui = $ilTabs;
		$this->tabs_gui->setSubTabActive('app_month');
		
		$this->user_settings = ilCalendarUserSettings::_getInstanceByUserId($ilUser->getId());
		$this->app_colors = new ilCalendarAppointmentColors($ilUser->getId());
		
		$this->timezone = $ilUser->getTimeZone();
	}
	
	/**
	 * Execute command
	 *
	 * @access public
	 * 
	 */
	public function executeCommand()
	{
		global $ilCtrl,$tpl;

		$next_class = $ilCtrl->getNextClass();
		switch($next_class)
		{
			case 'ilcalendarappointmentgui':
				$this->ctrl->setReturn($this,'');
				$this->tabs_gui->setSubTabActive($_SESSION['cal_last_tab']);
				
				include_once('./Services/Calendar/classes/class.ilCalendarAppointmentGUI.php');
				$app = new ilCalendarAppointmentGUI($this->seed,(int) $_GET['app_id']);
				$this->ctrl->forwardCommand($app);
				break;
			
			default:
				$time = microtime(true);
				$cmd = $this->ctrl->getCmd("show");
				$this->$cmd();
				$tpl->setContent($this->tpl->get());
				
				#echo "Zeit: ".(microtime(true) - $time);
				break;
		}
		return true;
	}
	
	/**
	 * fill data section
	 *
	 * @access public
	 * 
	 */
	public function show()
	{
		global $tpl, $ilUser;

		$this->tpl = new ilTemplate('tpl.month_view.html',true,true,'Services/Calendar');
		
		include_once('./Services/YUI/classes/class.ilYuiUtil.php');
		ilYuiUtil::initDragDrop();
		ilYuiUtil::initPanel();
		
		$navigation = new ilCalendarHeaderNavigationGUI($this,$this->seed,ilDateTime::MONTH);
		$this->tpl->setVariable('NAVIGATION',$navigation->getHTML());
		
		for($i = (int) $this->user_settings->getWeekStart();$i < (7 + (int) $this->user_settings->getWeekStart());$i++)
		{
			$this->tpl->setCurrentBlock('month_header_col');
			$this->tpl->setVariable('TXT_WEEKDAY',ilCalendarUtil::_numericDayToString($i,true));
			$this->tpl->parseCurrentBlock();
		}

		if(isset($_GET["bkid"]))
		{
			$user_id = $_GET["bkid"];
			$disable_empty = true;
			$no_add = true;
		}
		else
		{
			$user_id = $ilUser->getId();
			$disable_empty = false;
			$no_add = false;
		}
		include_once('Services/Calendar/classes/class.ilCalendarSchedule.php');
		$this->scheduler = new ilCalendarSchedule($this->seed,ilCalendarSchedule::TYPE_MONTH,$user_id,$disable_empty);
		$this->scheduler->addSubitemCalendars(true);
		$this->scheduler->calculate();

		include_once('Services/Calendar/classes/class.ilCalendarSettings.php');
		$settings = ilCalendarSettings::_getInstance();

		$counter = 0;
		foreach(ilCalendarUtil::_buildMonthDayList($this->seed->get(IL_CAL_FKT_DATE,'m'),
			$this->seed->get(IL_CAL_FKT_DATE,'Y'),
			$this->user_settings->getWeekStart())->get() as $date)
		{
			$counter++;
			$has_events = (bool)$this->showEvents($date);

			if(!$no_add)
			{
				if ($settings->getEnableGroupMilestones())
				{
					$this->ctrl->clearParametersByClass('ilcalendarappointmentgui');
					$this->ctrl->setParameterByClass('ilcalendarappointmentgui','seed',$date->get(IL_CAL_DATE));
					$this->tpl->setCurrentBlock("new_ms");
					$this->tpl->setVariable('H_NEW_MS_SRC',ilUtil::getImagePath('ms_add.gif'));
					$this->tpl->setVariable('H_NEW_MS_ALT',$this->lng->txt('cal_new_ms'));
					$this->tpl->setVariable('NEW_MS_LINK',$this->ctrl->getLinkTargetByClass('ilcalendarappointmentgui','addMilestone'));
					$this->tpl->parseCurrentBlock();
				}
			
				$this->tpl->setCurrentBlock("new_app");
				#$this->tpl->setVariable('NEW_SRC',ilUtil::getImagePath('new.gif','calendar'));
				$this->tpl->setVariable('NEW_SRC',ilUtil::getImagePath('date_add.gif'));
				$this->tpl->setVariable('NEW_ALT',$this->lng->txt('cal_new_app'));
				$this->ctrl->clearParametersByClass('ilcalendarappointmentgui');
				$this->ctrl->setParameterByClass('ilcalendarappointmentgui','seed',$date->get(IL_CAL_DATE));
				$this->tpl->setVariable('ADD_LINK',$this->ctrl->getLinkTargetByClass('ilcalendarappointmentgui','add'));
				$this->tpl->parseCurrentBlock();
			}

			
			$day = $date->get(IL_CAL_FKT_DATE,'j');
			$month = $date->get(IL_CAL_FKT_DATE,'n');

			if($day == 1)
			{
				$month_day = '1 '.ilCalendarUtil::_numericMonthToString($month,false);
			}
			else
			{
				$month_day = $day;
			}

			if(!$disable_empty || $has_events)
			{
				$this->tpl->setCurrentBlock('month_day_link');
				$this->ctrl->clearParametersByClass('ilcalendardaygui');
				$this->ctrl->setParameterByClass('ilcalendardaygui','seed',$date->get(IL_CAL_DATE));
				$this->tpl->setVariable('OPEN_DAY_VIEW',$this->ctrl->getLinkTargetByClass('ilcalendardaygui',''));
				$this->ctrl->clearParametersByClass('ilcalendardaygui');
			}
			else
			{
				$this->tpl->setCurrentBlock('month_day_no_link');
			}

			$this->tpl->setVariable('MONTH_DAY',$month_day);

			$this->tpl->parseCurrentBlock();
			
			
			$this->tpl->setCurrentBlock('month_col');

			include_once('./Services/Calendar/classes/class.ilCalendarUtil.php');
			if(ilCalendarUtil::_isToday($date))
			{
				$this->tpl->setVariable('TD_CLASS','caltoday');
			}
			#elseif(ilDateTime::_equals($date,$this->seed,IL_CAL_DAY))
			#{
			#	$this->tpl->setVariable('TD_CLASS','calnow');
			#}
			elseif(ilDateTime::_equals($date,$this->seed,IL_CAL_MONTH))
			{
				$this->tpl->setVariable('TD_CLASS','calstd');
			}
			elseif(ilDateTime::_before($date,$this->seed,IL_CAL_MONTH))
			{
				$this->tpl->setVariable('TD_CLASS','calprev');
			}
			else
			{
				$this->tpl->setVariable('TD_CLASS','calnext');
			}

			$this->tpl->parseCurrentBlock();
			
			
			if($counter and !($counter % 7))
			{
				$this->tpl->setCurrentBlock('month_row');
				$this->tpl->parseCurrentBlock();
			}
		}
	}
	
	/**
	 * 
	 * Show events
	 *
	 * @access protected
	 */
	protected function showEvents(ilDate $date)
	{
		global $tree, $ilUser;

		$count = 0;
		foreach($this->scheduler->getByDay($date,$this->timezone) as $item)
		{
			// booking
			$booking_subtitle = false;
			if($item['category_type'] == ilCalendarCategory::TYPE_CH)
			{
				include_once 'Services/Booking/classes/class.ilBookingEntry.php';
				$entry = new ilBookingEntry($item['event']->getContextId());
				if($entry)
				{
					$current = (int)$entry->getCurrentNumberOfBookings($item['event']->getEntryId());
					$max = (int)$entry->getNumberOfBookings();
					$booking_subtitle = ' '.$item['event']->getTitle().' ('.$current.'/'.$max.')';
				}
			}

			$this->tpl->setCurrentBlock('panel_code');
			$this->tpl->setVariable('NUM',$this->num_appointments);
			$this->tpl->parseCurrentBlock();

			// milestone icon
			if ($item['event']->isMilestone())
			{
				$this->tpl->setCurrentBlock('fullday_ms_icon');
				$this->tpl->setVariable('ALT_FD_MS', $this->lng->txt("cal_milestone"));
				$this->tpl->setVariable('SRC_FD_MS', ilUtil::getImagePath("icon_ms_s.gif"));
				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setCurrentBlock('il_event');

			include_once('./Services/Calendar/classes/class.ilCalendarAppointmentPanelGUI.php');
			$this->tpl->setVariable('PANEL_DATA',ilCalendarAppointmentPanelGUI::_getInstance()->getHTML($item));
			$this->tpl->setVariable('PANEL_NUM',$this->num_appointments);


			$this->ctrl->clearParametersByClass('ilcalendarappointmentgui');
			$this->ctrl->setParameterByClass('ilcalendarappointmentgui','app_id',$item['event']->getEntryId());
			$this->tpl->setVariable('EVENT_EDIT_LINK',$this->ctrl->getLinkTargetByClass('ilcalendarappointmentgui','edit'));
			$this->tpl->setVariable('EVENT_NUM',$item['event']->getEntryId());
			
			$compl = ($item['event']->isMilestone() && $item['event']->getCompletion() > 0)
				? " (".$item['event']->getCompletion()."%)"
				: "";

			if($item['event']->isFullDay())
			{
				$title = $item['event']->getPresentationTitle().$compl;
			}
			else
			{
				switch($this->user_settings->getTimeFormat())
				{
					case ilCalendarSettings::TIME_FORMAT_24:
						$title = $item['event']->getStart()->get(IL_CAL_FKT_DATE,'H:i',$this->timezone);
						break;
						
					case ilCalendarSettings::TIME_FORMAT_12:
						$title = $item['event']->getStart()->get(IL_CAL_FKT_DATE,'h:ia',$this->timezone);
						break;
				}

				if(!$booking_subtitle)
				{
					$title .= (' '.$item['event']->getPresentationTitle());
				}
				else
				{
					$title .= $booking_subtitle;
				}
			}
			$this->tpl->setVariable('EVENT_TITLE',$title);
			$color = $this->app_colors->getColorByAppointment($item['event']->getEntryId());
			$this->tpl->setVariable('EVENT_BGCOLOR',$color);
			$this->tpl->setVariable('EVENT_FONTCOLOR',ilCalendarUtil::calculateFontColor($color));
			
			$this->tpl->parseCurrentBlock();
			
			$this->num_appointments++;
			$count++;
		}
		return $count;
	}
	
}


?>