<?php

require_once('../../config.php');
require_once('../../course/lib.php');
require_once($CFG->dirroot.'/report/rcmr/locallib.php');

$intId 	  		= optional_param('id', $SITE->id, PARAM_INT);
$intTs 	  		= optional_param('ts', 0, PARAM_INT);
$intTimeframe 	= optional_param('timeframe', 0, PARAM_INT); 
$intMode  		= 1;

$course = $DB->get_record('course', array('id' => $intId), '*', MUST_EXIST);

require_login($course);

$context = context_course::instance($course->id);

require_capability('report/loglive:view', $context);

$strTitle = get_string('pluginname', 'report_rcmr');

$url = new moodle_url('/report/rcmr/index.php', array('ts' => $intTs));

$PAGE->set_url($url);
$PAGE->set_title($strTitle);
$PAGE->navbar->add('Red Cross Monthly Report', new moodle_url('index.php'));

echo $OUTPUT->header();

if(0 != $intTimeframe)
{
	/**
	 * Generate a report
	 */
	
	
}
$strStyle = '.report-rcmr tr td{width: 50%;} .report-rcmr-session-name{ width: 85%;}';

$strBody  = html_writer::tag('style', $strStyle); 
$strBody .= html_writer::tag('h1', get_string('pluginname', 'report_rcmr') );
$strBody .= html_writer::empty_tag('hr');
$strBody .= html_writer::start_div('container');
$strBody .= html_writer::start_tag('form', array('method' => 'post', 'action' => $CFG->wwwroot.'/report/rcmr/index.php'));
$strBody .= html_writer::start_tag('table', array('class' => 'table table-striped report-rcmr'));
$strBody .= html_writer::start_tag('tr');
$strBody .= html_writer::tag('td', get_string('timeframe', 'report_rcmr'), array('class' => 'col-xs-3'));
$strBody .= html_writer::start_tag('td');
$strBody .= html_writer::select(report_rcmr_timeoptions($intMode), 'timeframe', $intTimeframe, false);
$strBody .= html_writer::tag('button', get_string('viewreport', 'report_rcmr'), array('class' => 'button', 'type' => 'submit'));
$strBody .= html_writer::end_tag('td');
$strBody .= html_writer::end_tag('tr');
// New users
$strBody .= html_writer::start_tag('tr');
$strBody .= html_writer::start_tag('td');
$strBody .= get_string('newusers', 'report_rcmr');
$strBody .= html_writer::end_tag('td');
$strBody .= html_writer::start_tag('td');
$strBody .= report_rcmr_new_users($intTimeframe);
$strBody .= html_writer::end_tag('td');
$strBody .= html_writer::end_tag('tr');
//Returning Users
$strBody .= html_writer::start_tag('tr');
$strBody .= html_writer::start_tag('td');
$strBody .= get_string('returningusers', 'report_rcmr');
$strBody .= html_writer::end_tag('td');
$strBody .= html_writer::start_tag('td');
$strBody .= report_rcmr_returning_users($intTimeframe);
$strBody .= html_writer::end_tag('td');
$strBody .= html_writer::end_tag('tr');
//Webinars hosted
$strBody .= html_writer::start_tag('tr');
$strBody .= html_writer::start_tag('td');
$strBody .= get_string('webinars', 'report_rcmr');
$strBody .= html_writer::end_tag('td');
$strBody .= html_writer::start_tag('td');
$strBody .= report_rcmr_webinars($intTimeframe);
$strBody .= html_writer::end_tag('td');
$strBody .= html_writer::end_tag('tr');
//Face to face
$strBody .= html_writer::start_tag('tr');
$strBody .= html_writer::start_tag('td');
$strBody .= get_string('facetoface', 'report_rcmr');
$strBody .= html_writer::end_tag('td');
$strBody .= html_writer::start_tag('td');
$strBody .= report_rcmr_face_to_face($intTimeframe);
$strBody .= html_writer::end_tag('td');
$strBody .= html_writer::end_tag('tr');
//Webinars + F2F (excluding eLearning recorded sessions)
$strBody .= html_writer::start_tag('tr');
$strBody .= html_writer::start_tag('td');
$strBody .= get_string('totalsessions', 'report_rcmr');
$strBody .= html_writer::end_tag('td');
$strBody .= html_writer::start_tag('td');
$strBody .= report_rcmr_webinars($intTimeframe) + report_rcmr_face_to_face($intTimeframe);
$strBody .= html_writer::end_tag('td');
$strBody .= html_writer::end_tag('tr');
// Attendance
$strBody .= html_writer::start_tag('tr');
$strBody .= html_writer::start_tag('td');
$strBody .= get_string('attendance', 'report_rcmr');
$strBody .= html_writer::end_tag('td');
$strBody .= html_writer::start_tag('td');
$strBody .= html_writer::end_tag('td');
$strBody .= html_writer::end_tag('tr');

$strBody .= html_writer::start_tag('tr');
$strBody .= html_writer::start_tag('td', array('colspan' => 3));
$strBody .= html_writer::start_tag('table', array('class' => 'table table-condensed report-rcmr-attendance'));
$strBody .= html_writer::start_tag('thead');
$strBody .= html_writer::start_tag('tr');
$strBody .= html_writer::start_tag('th', array('class' => 'report-rcmr-session-name'));
$strBody .= get_string('sessionname', 'report_rcmr');
$strBody .= html_writer::end_tag('th');
$strBody .= html_writer::start_tag('th');
$strBody .= get_string('registrants', 'report_rcmr');
$strBody .= html_writer::end_tag('th');
$strBody .= html_writer::start_tag('th');
$strBody .= get_string('attendees', 'report_rcmr');
$strBody .= html_writer::end_tag('th');
$strBody .= html_writer::end_tag('tr');
$strBody .= html_writer::end_tag('thead');
$strBody .= html_writer::start_tag('tbody');
$strBody .= report_rcmr_attendance($intTimeframe);
$strBody .= html_writer::end_tag('tbody');
$strBody .= html_writer::end_tag('table');
$strBody .= html_writer::end_tag('td');
$strBody .= html_writer::end_tag('tr');

$strBody .= html_writer::end_tag('table');
$strBody .= html_writer::end_tag('form');
$strBody .= html_writer::end_div();

echo $strBody;
echo $OUTPUT->footer();
?>