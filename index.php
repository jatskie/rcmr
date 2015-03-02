<?php

require_once('../../config.php');
require_once('../../course/lib.php');
require_once($CFG->dirroot.'/report/rcmr/locallib.php');

$intId 	  		= optional_param('id', $SITE->id, PARAM_INT);
$strStartDate 	= optional_param('startdate', '', PARAM_RAW); 
$strEndDate 	= optional_param('enddate', '', PARAM_RAW);

$intMode  		= 1;

$course = $DB->get_record('course', array('id' => $intId), '*', MUST_EXIST);

require_login($course);

$context = context_course::instance($course->id);

require_capability('report/loglive:view', $context);

$strTitle = get_string('pluginname', 'report_rcmr');

$url = new moodle_url('/report/rcmr/index.php');

$PAGE->set_url($url);
$PAGE->set_title($strTitle);
$PAGE->set_pagelayout('standard');
$PAGE->navbar->add(get_string('report'));
$PAGE->navbar->add(get_string('pluginname', 'report_rcmr'));
$PAGE->navbar->add(get_string('reportsummary', 'report_rcmr'), new moodle_url('index.php'));
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');

echo $OUTPUT->header();

$strStyle = '.report-rcmr-session-name{ width: 35%;} .report-rcmr-session-count{ width: 25%; text-align: center !important; } .text-center{text-align: center !important;}';
$strScript = ' $(function() { $( "#startdate" ).datepicker(); $( "#enddate" ).datepicker(); });';

$strBody  = html_writer::tag('style', $strStyle); 
$strBody .= html_writer::tag('h1', get_string('pluginname', 'report_rcmr') );
$strBody .= html_writer::empty_tag('hr');
$strBody .= html_writer::start_div('row');
$strBody .= html_writer::start_tag('form', array('method' => 'post', 'action' => $CFG->wwwroot.'/report/rcmr/index.php'));
$strBody .= html_writer::start_tag('table', array('class' => 'table table-striped report-rcmr'));
$strBody .= html_writer::start_tag('tr', array('colspan' => 2));
$strBody .= html_writer::start_tag('td');
$strBody .= html_writer::start_div('container');
$strBody .= html_writer::tag('div', get_string('timeframe', 'report_rcmr'), array('class' => 'col-xs-3'));
$strBody .= html_writer::start_div('col-xs-9');
$strBody .= html_writer::empty_tag('input', array('name' => 'startdate', 'id' => 'startdate', 'placeholder' => 'Start Date', 'value' => $strStartDate));
$strBody .= '&nbsp; to &nbsp;';
$strBody .= html_writer::empty_tag('input', array('name' => 'enddate', 'id' => 'enddate', 'placeholder' => 'End Date', 'value' => $strEndDate));
$strBody .= '&nbsp;';
$strBody .= html_writer::checkbox('rcusersonly', '1', false, get_string('rcuseronly', 'report_rcmr'));
$strBody .= html_writer::tag('button', get_string('viewreport', 'report_rcmr'), array('class' => 'btn btn-primary', 'type' => 'submit'));
$strBody .= html_writer::end_div();
$strBody .= html_writer::end_tag('td');
$strBody .= html_writer::end_tag('tr');
// New users
$strBody .= html_writer::start_tag('tr');
$strBody .= html_writer::start_tag('td');
$strBody .= get_string('newusers', 'report_rcmr');
$strBody .= html_writer::end_tag('td');
$strBody .= html_writer::start_tag('td');
$strBody .= report_rcmr_new_users($strStartDate, $strEndDate);
$strBody .= html_writer::end_tag('td');
$strBody .= html_writer::end_tag('tr');
//Returning Users
$strBody .= html_writer::start_tag('tr');
$strBody .= html_writer::start_tag('td');
$strBody .= get_string('returningusers', 'report_rcmr');
$strBody .= html_writer::end_tag('td');
$strBody .= html_writer::start_tag('td');
$strBody .= report_rcmr_returning_users($strStartDate, $strEndDate);
$strBody .= html_writer::end_tag('td');
$strBody .= html_writer::end_tag('tr');
//Webinars hosted
$strBody .= html_writer::start_tag('tr');
$strBody .= html_writer::start_tag('td');
$strBody .= get_string('webinars', 'report_rcmr');
$strBody .= html_writer::end_tag('td');
$strBody .= html_writer::start_tag('td');
$strBody .= report_rcmr_webinars($strStartDate, $strEndDate);
$strBody .= html_writer::end_tag('td');
$strBody .= html_writer::end_tag('tr');
//Face to face
$strBody .= html_writer::start_tag('tr');
$strBody .= html_writer::start_tag('td');
$strBody .= get_string('facetoface', 'report_rcmr');
$strBody .= html_writer::end_tag('td');
$strBody .= html_writer::start_tag('td');
$strBody .= report_rcmr_face_to_face($strStartDate, $strEndDate);
$strBody .= html_writer::end_tag('td');
$strBody .= html_writer::end_tag('tr');
//Webinars + F2F (excluding eLearning recorded sessions)
$strBody .= html_writer::start_tag('tr');
$strBody .= html_writer::start_tag('td');
$strBody .= get_string('totalsessions', 'report_rcmr');
$strBody .= html_writer::end_tag('td');
$strBody .= html_writer::start_tag('td');
$strBody .= report_rcmr_webinars($strStartDate, $strEndDate) + report_rcmr_face_to_face($strStartDate, $strEndDate);
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
$strBody .= html_writer::start_tag('table', array('class' => 'table table-condensed table-bordered report-rcmr-attendance'));
$strBody .= html_writer::start_tag('thead');
$strBody .= html_writer::start_tag('tr');
$strBody .= html_writer::start_tag('th', array('class' => 'report-rcmr-session-name'));
$strBody .= get_string('sessionname', 'report_rcmr');
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
$strBody .= report_rcmr_attendance($strStartDate, $strEndDate);
$strBody .= html_writer::end_tag('tbody');
$strBody .= html_writer::end_tag('table');
$strBody .= html_writer::end_tag('td');
$strBody .= html_writer::end_tag('tr');

$strBody .= html_writer::end_tag('table');
$strBody .= html_writer::end_tag('form');
$strBody .= html_writer::start_div('col-xs-12');
$strBody .= html_writer::tag('button', get_string('exporttocsv', 'report_rcmr'), array('class' => 'btn btn-primary', 'id' => 'report-export'));
$strBody .= html_writer::end_div();
$strBody .= html_writer::end_div();

$strBody .= html_writer::tag('script', $strScript);

echo $strBody;
echo $OUTPUT->footer();
?>