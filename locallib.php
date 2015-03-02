<?php
/**
 * Reports implementation
 *
 * @package    report_rcmr
 * @subpackage stats
 * @copyright  2015 onwards Jat Macalalad
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;
require_once($CFG->dirroot.'/lib/statslib.php');

/**
 * Let's borrow the stats timeframe generator
 * @param unknown $course
 * @param unknown $mode
 * @param unknown $time
 * @param unknown $url
 * @return string
 */

function report_rcmr_mode_menu($course, $mode, $time, $url) 
{
	global $CFG, $OUTPUT;

	$options = array();
	$options[STATS_MODE_GENERAL] = get_string('statsmodegeneral');
	$options[STATS_MODE_DETAILED] = get_string('statsmodedetailed');
	if (has_capability('report/stats:view', context_system::instance())) 
	{
		$options[STATS_MODE_RANKED] = get_string('reports');
	}
	$popupurl = $url."?course=$course->id&time=$time";
	$select = new single_select(new moodle_url($popupurl), 'mode', $options, $mode, null);
	$select->set_label(get_string('reports'), array('class' => 'accesshide'));
	$select->formid = 'switchmode';
	
	return $OUTPUT->render($select);
}

function report_rcmr_timeoptions($mode) 
{
	global $CFG, $DB;

	if ($mode == STATS_MODE_DETAILED) {
		$earliestday = $DB->get_field_sql('SELECT MIN(timeend) FROM {stats_user_daily}');
		$earliestweek = $DB->get_field_sql('SELECT MIN(timeend) FROM {stats_user_weekly}');
		$earliestmonth = $DB->get_field_sql('SELECT MIN(timeend) FROM {stats_user_monthly}');
	} else {
		$earliestday = $DB->get_field_sql('SELECT MIN(timeend) FROM {stats_daily}');
		$earliestweek = $DB->get_field_sql('SELECT MIN(timeend) FROM {stats_weekly}');
		$earliestmonth = $DB->get_field_sql('SELECT MIN(timeend) FROM {stats_monthly}');
	}

	if (empty($earliestday)) $earliestday = time();
	if (empty($earliestweek)) $earliestweek = time();
	if (empty($earliestmonth)) $earliestmonth = time();

	$now = stats_get_base_daily();
	$lastweekend = stats_get_base_weekly();
	$lastmonthend = stats_get_base_monthly();
	
	$timeFrame = array(0 => 'All');
	$timeFrame = array_merge($timeFrame, stats_get_time_options($now,$lastweekend,$lastmonthend,$earliestday,$earliestweek,$earliestmonth) ); 

	return $timeFrame;
}

function report_rcmr_timeframe($aIntTime)
{
	$arrTimeFrame = array(
			'start'	=> 0,
			'end' 	=> time()
	);
	
	switch ( (int) $aIntTime )
	{
		case 1:
			$intOneWeek = time() - ( 60 * 60 * 24 * 7);
			$arrTimeFrame = array(
				'start'	=> $intOneWeek,
				'end' 	=> time()	
			);
			break;
			/* Weekly to 1 month */
		case ($aIntTime < 32):
			/* Monthly to a year */
		default:
			break;
	}

	return $arrTimeFrame;
}

function report_rcmr_new_users($aIntTimeFrame)
{
	global $DB;
	
	$arrTimeFrame = report_rcmr_timeframe($aIntTimeFrame);
	
	$intUsers = $DB->count_records_sql("SELECT COUNT(id) FROM {user} WHERE lastaccess = 0 AND (timecreated > ? AND timecreated < ?)", array($arrTimeFrame['start'], $arrTimeFrame['end']));
	
	return $intUsers;
}

function report_rcmr_returning_users($aIntTimeFrame)
{
	global $DB;
	
	$arrTimeFrame = report_rcmr_timeframe($aIntTimeFrame);
	
	$intUsers = $DB->count_records_sql("SELECT COUNT(id) FROM {user} WHERE (lastaccess > ? AND lastaccess < ?)", array($arrTimeFrame['start'], $arrTimeFrame['end']));
	
	return $intUsers;
}

function report_rcmr_webinars($aIntTimeFrame)
{
	global $DB;
	
	$arrTimeFrame = report_rcmr_timeframe($aIntTimeFrame);
	
	$intSessions = $DB->count_records_sql("SELECT COUNT(id) FROM {gototraining_session_times} WHERE startdate > ? AND startdate < ?", array($arrTimeFrame['start'], $arrTimeFrame['end']));
	
	return $intSessions;
}

function report_rcmr_face_to_face($aIntTimeFrame)
{
	global $DB;
	
	$arrTimeFrame = report_rcmr_timeframe($aIntTimeFrame);
	
	$intSessions = $DB->count_records_sql("SELECT COUNT(id) FROM {facetoface_sessions_dates} WHERE timestart > ? AND timestart < ?", array($arrTimeFrame['start'], $arrTimeFrame['end']));
	
	return $intSessions;
}

function report_rcmr_attendance($aIntTimeFrame)
{
	global $DB;
	$strBody = '';
	$intRegistrants = 0;
	$intAttendees = 0;
	$arrTimeFrame = report_rcmr_timeframe($aIntTimeFrame);
	
	$arrData = $DB->get_records_sql("SELECT GTS.id, GTS.name, COUNT(GTR.id) as 'registrants', COUNT(GTR.id) as 'attendees'
						FROM mdl_gototraining_registrants GTR, mdl_gototraining_sessions GTS, mdl_gototraining_session_times GST
						WHERE GTR.sessionid = GTS.id
						AND GST.sessionid = GTS.id
						AND GST.startdate > ?
						AND GST.startdate < ?
						GROUP BY GTR.sessionid", array($arrTimeFrame['start'], $arrTimeFrame['end'])
	);

	foreach ($arrData as $objWebinarData)
	{
		$intRegistrants += $objWebinarData->registrants;
		$intAttendees += $objWebinarData->attendees;
		
		if(empty($objWebinarData->name))
		{
			$objWebinarData->name = 'Not set';
		}
		
		$strBody .= html_writer::start_tag('tr');
		$strBody .= html_writer::start_tag('td');
		$strBody .= $objWebinarData->name;
		$strBody .= html_writer::end_tag('td');
		$strBody .= html_writer::start_tag('td');
		$strBody .= $objWebinarData->registrants;
		$strBody .= html_writer::end_tag('td');
		$strBody .= html_writer::start_tag('td');
		$strBody .= $objWebinarData->attendees;
		$strBody .= html_writer::end_tag('td');
		$strBody .= html_writer::end_tag('tr');
	}
	
	$strBody .= html_writer::start_tag('tr');
	$strBody .= html_writer::start_tag('td');
	$strBody .= html_writer::tag( 'b', get_string('total') );
	$strBody .= html_writer::end_tag('td');
	$strBody .= html_writer::start_tag('td');
	$strBody .= $intRegistrants;
	$strBody .= html_writer::end_tag('td');
	$strBody .= html_writer::start_tag('td');
	$strBody .= $intAttendees;
	$strBody .= html_writer::end_tag('td');
	$strBody .= html_writer::end_tag('tr');
	
	
	return $strBody;
}