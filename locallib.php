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

function report_rcmr_timeframe($aStrStartDate, $aStrEndDate)
{
	$arrTimeFrame = array(
			'start'	=> 0,
			'end' 	=> time()
	);
	
	if(false == empty($aStrStartDate))
	{
		$arrTimeFrame['start'] = strtotime($aStrStartDate);
	}
	
	if(false == empty($aStrEndDate))
	{
		$arrTimeFrame['end'] = strtotime($aStrEndDate);
	}

	return $arrTimeFrame;
}

function report_rcmr_new_users($aStrStartDate, $aStrEndDate)
{
	global $DB;
	
	$arrTimeFrame = report_rcmr_timeframe($aStrStartDate, $aStrEndDate);
	
	$intUsers = $DB->count_records_sql("SELECT COUNT(id) FROM {user} WHERE lastaccess = 0 AND (timecreated > ? AND timecreated < ?)", array($arrTimeFrame['start'], $arrTimeFrame['end']));
	
	return $intUsers;
}

function report_rcmr_returning_users($aStrStartDate, $aStrEndDate)
{
	global $DB;
	
	$arrTimeFrame = report_rcmr_timeframe($aStrStartDate, $aStrEndDate);
	
	$intUsers = $DB->count_records_sql("SELECT COUNT(id) FROM {user} WHERE (lastaccess > ? AND lastaccess < ?)", array($arrTimeFrame['start'], $arrTimeFrame['end']));
	
	return $intUsers;
}

function report_rcmr_webinars($aStrStartDate, $aStrEndDate)
{
	global $DB;
	
	$arrTimeFrame = report_rcmr_timeframe($aStrStartDate, $aStrEndDate);
	
	$intSessions = $DB->count_records_sql("SELECT COUNT(id) FROM {gototraining_session_times} WHERE startdate > ? AND startdate < ?", array($arrTimeFrame['start'], $arrTimeFrame['end']));
	
	return $intSessions;
}

function report_rcmr_face_to_face($aStrStartDate, $aStrEndDate)
{
	global $DB;
	
	$arrTimeFrame = report_rcmr_timeframe($aStrStartDate, $aStrEndDate);
	
	$intSessions = $DB->count_records_sql("SELECT COUNT(id) FROM {facetoface_sessions_dates} WHERE timestart > ? AND timestart < ?", array($arrTimeFrame['start'], $arrTimeFrame['end']));
	
	return $intSessions;
}

function report_rcmr_attendance($aStrStartDate, $aStrEndDate)
{
	global $DB;
	$strBody = '';
	$intRegistrants = 0;
	$intAttendees = 0;
	$arrTimeFrame = report_rcmr_timeframe($aStrStartDate, $aStrEndDate);
	
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
		$strBody .= html_writer::start_tag('td', array('class' => 'text-center'));
		$strBody .= $objWebinarData->registrants;
		$strBody .= html_writer::end_tag('td');
		$strBody .= html_writer::start_tag('td', array('class' => 'text-center'));
		$strBody .= $objWebinarData->attendees;
		$strBody .= html_writer::end_tag('td');
		$strBody .= html_writer::end_tag('tr');
	}
	
	if(empty($arrData))
	{
		$strBody .= html_writer::start_tag('tr');
		$strBody .= html_writer::start_tag('td', array('colspan' => 3));
		$strBody .= html_writer::tag('div', get_string('nosession', 'report_rcmr'), array('class' => 'alert alert-info text-center'));
		$strBody .= html_writer::end_tag('td');
		$strBody .= html_writer::end_tag('tr');
	}
	
	$strBody .= html_writer::start_tag('tr');
	$strBody .= html_writer::start_tag('td');
	$strBody .= html_writer::tag( 'b', get_string('total') );
	$strBody .= html_writer::end_tag('td');
	$strBody .= html_writer::start_tag('td', array('class' => 'text-center'));
	$strBody .= html_writer::tag('b', $intRegistrants);
	$strBody .= html_writer::end_tag('td');
	$strBody .= html_writer::start_tag('td', array('class' => 'text-center'));
	$strBody .= html_writer::tag('b', $intAttendees);
	$strBody .= html_writer::end_tag('td');
	$strBody .= html_writer::end_tag('tr');
	
	
	return $strBody;
}