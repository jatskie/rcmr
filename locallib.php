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
require_once($CFG->dirroot.'/lib/coursecatlib.php');

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

function report_rcmr_new_users($aStrStartDate, $aStrEndDate, $aBoolRedcrossOnly = false)
{
	global $DB;
	
	$arrTimeFrame = report_rcmr_timeframe($aStrStartDate, $aStrEndDate);
	
	$strWhere = report_rcmr_redcross_only($aBoolRedcrossOnly);
	
	$intUsers = $DB->count_records_sql("SELECT COUNT(id) FROM {user} user WHERE lastaccess = 0 AND (timecreated > ? AND timecreated < ?) $strWhere", $arrTimeFrame);
	
	return $intUsers;
}

function report_rcmr_returning_users($aStrStartDate, $aStrEndDate, $aBoolRedcrossOnly = false)
{
	global $DB;
	
	$arrTimeFrame = report_rcmr_timeframe($aStrStartDate, $aStrEndDate);
	
	$strWhere = report_rcmr_redcross_only($aBoolRedcrossOnly);
	
	$intUsers = $DB->count_records_sql("SELECT COUNT(id) FROM {user} user WHERE (lastaccess > ? AND lastaccess < ?) $strWhere", $arrTimeFrame);
	
	return $intUsers;
}

function report_rcmr_webinars($aStrStartDate, $aStrEndDate, $aBoolRedcrossOnly = false)
{
	global $DB;
	
	$arrTimeFrame = report_rcmr_timeframe($aStrStartDate, $aStrEndDate);
		
	$intSessions = $DB->count_records_sql("SELECT COUNT(GST.id) FROM {gototraining_session_times} GST WHERE (GST.startdate > ? AND GST.startdate < ?)", $arrTimeFrame);
	
	return $intSessions;
}

function report_rcmr_face_to_face($aStrStartDate, $aStrEndDate, $aBoolRedcrossOnly = false)
{
	global $DB;
	
	$arrTimeFrame = report_rcmr_timeframe($aStrStartDate, $aStrEndDate);
	
	$intSessions = $DB->count_records_sql("SELECT COUNT(FFS.id) FROM {facetoface_sessions_dates} FFS WHERE (FFS.timestart > ? AND FFS.timestart < ?)", $arrTimeFrame);
	
	return $intSessions;
}

function report_rcmr_elearning($aStrStartDate, $aStrEndDate, $aBoolRedcrossOnly = false)
{
	global $DB;
	
	$arrTimeFrame = report_rcmr_timeframe($aStrStartDate, $aStrEndDate);
	
	$intElearning = $DB->count_records_sql("SELECT COUNT(id) FROM {enrol} WHERE timecreated > ? AND timecreated < ?", $arrTimeFrame);
	
	return $intElearning;
}

function report_rcmr_attendance_html($aStrStartDate, $aStrEndDate, $aBoolRedcrossOnly = false)
{
	global $DB;
	$intRegistrants = 0;
	$intAttendees = 0;
	$strInnerJoin = '';
	$strWhere = '';
	
	$arrTimeFrame = report_rcmr_timeframe($aStrStartDate, $aStrEndDate);
	
	$arrGTR = array();
	
	$arrAttendanceData = array();
	
	// Get all sessions created
	$arrGTS = $DB->get_records_sql("SELECT * FROM {gototraining_sessions}");
	
	// Get all registrants grouped by session
	foreach ($arrGTS as $objGTS)
	{
		$arrGTR[$objGTS->id] = $DB->get_records_sql("
				SELECT * 
				FROM {gototraining_registrants} 
				WHERE sessionid = $objGTS->id 
				AND (registrationdate > ? AND registrationdate < ?)", 
				$arrTimeFrame
		);			
	}
	
	// Per session
	foreach ($arrGTR as $intGTSid => $arrGTRR)
	{
		$strGTSName = $arrGTS[$intGTSid]->name;
		$arrAttendanceData[$strGTSName] = array(
				'registrants' 	=> 0,
				'attendees'		=> 0
		);
		$objGTCourse = $DB->get_record('gototraining', array('id' => $arrGTS[$intGTSid]->gototraining));		
		$objCourseModules = $DB->get_records('course_modules', array('course' => $objGTCourse->id));
		
		$intCompletedModules = 0;
		
		// per user
		foreach ($arrGTRR as $objUser)
		{
			$objUserid = $DB->get_record('user', array('email' => $objUser->email),'id');
			// per modules in a course
			foreach ($objCourseModules as $objModule)
			{
				$completionCheck = $DB->get_record('course_modules_completion', array('coursemoduleid' => $objModule->id, 'userid' => $objUserid->id));

				if(false != $completionCheck && 1 == $completionCheck->completionstate)
				{
					++$intCompletedModules;
				}
			}
				
			if(0 < $intCompletedModules)
			{
				$arrAttendanceData[$strGTSName]['attendees'] += 1;
			}						
		}

		$arrAttendanceData[$strGTSName]['registrants'] = count($arrGTRR);		
	}
	
	$strBody  = html_writer::start_tag('table', array('class' => 'table table-condensed table-bordered report-rcmr-attendance'));
	$strBody .= html_writer::start_tag('thead');
	$strBody .= html_writer::start_tag('tr');
	$strBody .= html_writer::start_tag('th', array('class' => 'report-rcmr-session-name'));
	$strBody .= get_string('sessionname', 'report_rcmr') . " (" . count($arrAttendanceData) . ")";
	$strBody .= html_writer::end_tag('th');
	$strBody .= html_writer::start_tag('th', array('class' => 'report-rcmr-session-count'));
	$strBody .= get_string('registrants', 'report_rcmr');
	$strBody .= html_writer::end_tag('th');
	$strBody .= html_writer::start_tag('th', array('class' => 'report-rcmr-session-count'));
	$strBody .= get_string('attendees', 'report_rcmr');
	$strBody .= html_writer::end_tag('th');
	$strBody .= html_writer::end_tag('tr');
	$strBody .= html_writer::end_tag('thead');
	$strBody .= html_writer::start_tag('tbody');
	
	foreach ($arrAttendanceData as $strGTSName => $arrWebinarData)
	{
		// skip empty sessions
		if(0 == $arrWebinarData['registrants'] && 0 == $arrWebinarData['attendees'])
		{
			continue;
		}
		
		$intRegistrants += $arrWebinarData['registrants'];
		$intAttendees += $arrWebinarData['attendees'];
		
		if("" == $strGTSName)
		{
			$$strGTSName = 'Not set';
		}
		
		$strBody .= html_writer::start_tag('tr');
		$strBody .= html_writer::start_tag('td');
		$strBody .= $strGTSName;
		$strBody .= html_writer::end_tag('td');
		$strBody .= html_writer::start_tag('td', array('class' => 'text-center'));
		$strBody .= $arrWebinarData['registrants'];
		$strBody .= html_writer::end_tag('td');
		$strBody .= html_writer::start_tag('td', array('class' => 'text-center'));
		$strBody .= $arrWebinarData['attendees'];
		$strBody .= html_writer::end_tag('td');
		$strBody .= html_writer::end_tag('tr');
	}
	
	if(empty($arrAttendanceData))
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
	
	$strBody .= html_writer::end_tag('tbody');
	$strBody .= html_writer::end_tag('table');
	
	return $strBody;
}

function report_rcmr_lms_videos($aStrStartDate, $aStrEndDate)
{
	global $DB;
	$arrTimeFrame = report_rcmr_timeframe($aStrStartDate, $aStrEndDate);
	
	$intVideos = $DB->count_records_sql("SELECT COUNT(id) FROM {files} WHERE mimetype LIKE '%video%' AND (timecreated > ? AND timecreated < ?)", $arrTimeFrame);
		
	return $intVideos;
}

function report_rcmr_completion($aStrStartDate, $aStrEndDate, $aIntCourseid = 0, $aIntCategoryid = 0)
{
	global $DB;
	$arrTimeFrame = report_rcmr_timeframe($aStrStartDate, $aStrEndDate);
	
	$strWhere = "WHERE ((completion.timeenrolled > ? AND completion.timeenrolled < ?) OR (completion.timestarted > ? AND completion.timestarted < ?) OR (completion.timecompleted > ? AND completion.timecompleted < ?))";
	$strInnerJoin = '';
	
	$arrSQLArgs = array(
			$arrTimeFrame['start'], 
			$arrTimeFrame['end'], 
			$arrTimeFrame['start'], 
			$arrTimeFrame['end'], 
			$arrTimeFrame['start'], 
			$arrTimeFrame['end']			
	);
	
	if(0 != $aIntCourseid && 0 == $aIntCategoryid)
	{
		$strWhere .= " AND course = ?";
		array_push($arrSQLArgs, $aIntCourseid);
	}
	else if(0 != $aIntCategoryid)
	{
		$strWhere .= " AND completion.course IN ( SELECT course.id FROM {course} course INNER JOIN {course_categories} cat on course.category = cat.id WHERE cat.id = '$aIntCategoryid') "; 
	}
	
	$arrCompletions = $DB->get_records_sql("SELECT completion.id, completion.course, completion.timeenrolled, completion.timestarted, completion.timecompleted FROM {course_completions} completion $strInnerJoin $strWhere", $arrSQLArgs);
	
	$arrManualEnrolment = $DB->get_records_sql("SELECT * FROM {enrol}");
	
	$arrOverallCompletion = array();
	// Build completion data
	foreach ($arrCompletions as $objCompletion)
	{
		$intCourseid = $objCompletion->course;
		
		if(false == array_key_exists($intCourseid, $arrOverallCompletion))
		{
			$arrOverallCompletion[$intCourseid] = array(
					'enrolment'		=> 0,
					'not_started' 	=> 0,
					'in_progress' 	=> 0,
					'completed' 	=> 0
			);
		}
		
		if(0 == $objCompletion->timestarted)
		{
			$arrOverallCompletion[$intCourseid]['not_started'] += 1;
		}
		else if(null != $objCompletion->timecompleted)
		{
			$arrOverallCompletion[$intCourseid]['completed'] += 1;
		}
		else
		{
			$arrOverallCompletion[$intCourseid]['in_progress'] += 1;
		}
	}
	
	// Build enrolment data
	/*
	foreach ($arrManualEnrolment as $objUserEnrolment)
	{
		$intCourseid = $objUserEnrolment->courseid;
		if(false == array_key_exists($intCourseid, $arrOverallCompletion))
		{
			$arrOverallCompletion[$intCourseid] = array(
					'enrolment'		=> 0,
					'not_started' 	=> 0,
					'in_progress' 	=> 0,
					'completed' 	=> 0
			);
		}
		
		$arrOverallCompletion[$intCourseid]['enrolment'] += 1;
	}
	*/
	
	return $arrOverallCompletion;
}

function report_rcmr_completion_html($aArrData)
{
	global $DB;
	
	$strBody  = html_writer::start_tag('table', array('class' => 'table'));
	$strBody .= html_writer::start_tag('thead');
	$strBody .= html_writer::start_tag('tr');
	$strBody .= html_writer::start_tag('th');
	$strBody .= 'Course (' . count($aArrData) . ')';
	$strBody .= html_writer::end_tag('th');
	//$strBody .= html_writer::start_tag('th');
	//$strBody .= 'Current Enrolment';
	//$strBody .= html_writer::end_tag('th');
	$strBody .= html_writer::start_tag('th');
	$strBody .= 'Not Started';
	$strBody .= html_writer::end_tag('th');
	$strBody .= html_writer::start_tag('th');
	$strBody .= 'In Progress';
	$strBody .= html_writer::end_tag('th');
	$strBody .= html_writer::start_tag('th');
	$strBody .= 'Completed';
	$strBody .= html_writer::end_tag('th');	
	$strBody .= html_writer::end_tag('tr');
	$strBody .= html_writer::end_tag('thead');
	$strBody .= html_writer::start_tag('tbody');
	foreach ($aArrData as $strCourseid => $arrCompletionData)
	{
		$objCourse = $DB->get_record('course', array('id' => $strCourseid));
		$strBody .= html_writer::start_tag('tr');
		$strBody .= html_writer::tag('td', $objCourse->fullname);
		//$strBody .= html_writer::tag('td', $arrCompletionData['enrolment']);
		$strBody .= html_writer::tag('td', $arrCompletionData['not_started']);
		$strBody .= html_writer::tag('td', $arrCompletionData['in_progress']);
		$strBody .= html_writer::tag('td', $arrCompletionData['completed']);
		$strBody .= html_writer::end_tag('tr');
	}
	$strBody .= html_writer::end_tag('tbody');
	$strBody .= html_writer::end_tag('table');
	
	return $strBody;
}

function report_rcmr_build_course_dropdown($aIntCourseid)
{
	$arrCourses = get_courses();
	$arrOptions = array();
	
	foreach ($arrCourses as $objCourse)
	{
		$arrOptions[$objCourse->id] = $objCourse->fullname;	
	}
	
	asort($arrOptions);
	
	return html_writer::select($arrOptions, 'courseid', $aIntCourseid);
}

function report_rcmr_build_course_category_dropdown($aIntCategoryid)
{
	$arrCategories = coursecat::make_categories_list();
	
	return html_writer::select($arrCategories, 'categoryid', $aIntCategoryid);
}

function report_rcmr_redcross_only($aBoolRedcrossOnly)
{
	$strWhere = '';
	
	if(true == $aBoolRedcrossOnly)
	{
		$strWhere = " AND user.email LIKE '%@redcrossblood.org.au%'";
	}
	
	return $strWhere;
}

function report_rcmr_get_points($aStrStartDate, $aStrEndDate)
{
	global $DB;
	$arrTimeFrame = report_rcmr_timeframe($aStrStartDate, $aStrEndDate);
	
	$arrCPDPoints = $DB->get_records("certificate");
	
	return 0;
}