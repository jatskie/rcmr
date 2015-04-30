<?php

require_once('../../config.php');
require_once('../../course/lib.php');
require_once($CFG->dirroot.'/report/rcmr/locallib.php');

$intId 	  		= optional_param('id', $SITE->id, PARAM_INT);
$strStartDate 	= optional_param('startdate', '', PARAM_RAW); 
$strEndDate 	= optional_param('enddate', '', PARAM_RAW);
$boolRedcrossOnly = optional_param('rcusersonly', false, PARAM_BOOL);

$course = $DB->get_record('course', array('id' => $intId), '*', MUST_EXIST);

require_login($course);

$context = context_course::instance($course->id);

require_capability('report/loglive:view', $context);

$strTitle = get_string('pluginname', 'report_rcmr');

$url = new moodle_url('/report/rcmr/index.php');

$PAGE->set_url($url);
$PAGE->set_title($strTitle);
$PAGE->set_pagelayout('report');

// Breadcrumbs
$PAGE->navbar->add(get_string('report'));
$PAGE->navbar->add(get_string('pluginname', 'report_rcmr'));
$PAGE->navbar->add(get_string('reportsummary', 'report_rcmr'), new moodle_url('index.php'));

// Additional javascripts
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');

echo $OUTPUT->header();

$strStyle = '.report-row{margin-left: 20px !important;} table.report-rcmr tbody tr td{ width: 400px; } .report-rcmr-session-name{ width: 35%;} .report-rcmr-session-count{ width: 25%; text-align: center !important; } .text-center{text-align: center !important;}';
$strScript = ' $(function() { $( "#startdate" ).datepicker(); $( "#enddate" ).datepicker(); });';

$strBody  = html_writer::tag('style', $strStyle); 
$strBody .= html_writer::tag('h1', get_string('pluginname', 'report_rcmr') );
$strBody .= html_writer::empty_tag('hr');
$strBody .= html_writer::start_div('row span12');
$strBody .= html_writer::start_tag('form', array('method' => 'post', 'action' => $CFG->wwwroot.'/report/rcmr/index.php'));
$strBody .= html_writer::start_tag('table', array('class' => 'table table-striped report-rcmr'));
$strBody .= html_writer::start_tag('tr');
$strBody .= html_writer::start_tag('td', array('colspan' => 2));
$strBody .= html_writer::start_div('row report-row');
$strBody .= html_writer::tag('div', get_string('timeframe', 'report_rcmr'), array('class' => 'span2'));
$strBody .= html_writer::start_div('span8');
$strBody .= html_writer::empty_tag('input', array('name' => 'startdate', 'id' => 'startdate', 'placeholder' => 'Start Date', 'value' => $strStartDate));
$strBody .= '&nbsp; to &nbsp;';
$strBody .= html_writer::empty_tag('input', array('name' => 'enddate', 'id' => 'enddate', 'placeholder' => 'End Date', 'value' => $strEndDate));
$strBody .= '&nbsp;';
$strBody .= html_writer::checkbox('rcusersonly', '1', $boolRedcrossOnly, get_string('rcusersonly', 'report_rcmr'));
$strBody .= html_writer::end_div();
$strBody .= html_writer::tag('div', html_writer::tag('button', get_string('viewreport', 'report_rcmr'), array('class' => 'btn btn-primary', 'type' => 'submit')), array('class' => 'span2'));
$strBody .= html_writer::end_tag('td');
$strBody .= html_writer::end_tag('tr');
// New users
$strBody .= html_writer::start_tag('tr');
$strBody .= html_writer::start_tag('td');
$strBody .= get_string('newusers', 'report_rcmr');
$strBody .= html_writer::end_tag('td');
$strBody .= html_writer::start_tag('td');
$strBody .= report_rcmr_new_users($strStartDate, $strEndDate, $boolRedcrossOnly);
$strBody .= html_writer::end_tag('td');
$strBody .= html_writer::end_tag('tr');
//Returning Users
$strBody .= html_writer::start_tag('tr');
$strBody .= html_writer::start_tag('td');
$strBody .= get_string('returningusers', 'report_rcmr');
$strBody .= html_writer::end_tag('td');
$strBody .= html_writer::start_tag('td');
$strBody .= report_rcmr_returning_users($strStartDate, $strEndDate, $boolRedcrossOnly);
$strBody .= html_writer::end_tag('td');
$strBody .= html_writer::end_tag('tr');
//Elearning
$strBody .= html_writer::start_tag('tr');
$strBody .= html_writer::start_tag('td');
$strBody .= get_string('elearning', 'report_rcmr');
$strBody .= html_writer::end_tag('td');
$strBody .= html_writer::start_tag('td');
$strBody .= report_rcmr_elearning($strStartDate, $strEndDate, $boolRedcrossOnly);
$strBody .= html_writer::end_tag('td');
$strBody .= html_writer::end_tag('tr');
//Elearning
$strBody .= html_writer::start_tag('tr');
$strBody .= html_writer::start_tag('td');
$strBody .= get_string('elearning_completion', 'report_rcmr');
$strBody .= html_writer::end_tag('td');
$strBody .= html_writer::start_tag('td');
$strBody .= html_writer::tag('a', get_string('reportcompletion', 'report_rcmr'), array('href' => "$CFG->wwwroot/report/rcmr/completion.php"));
$strBody .= html_writer::end_tag('td');
$strBody .= html_writer::end_tag('tr');
//Webinars hosted
$strBody .= html_writer::start_tag('tr');
$strBody .= html_writer::start_tag('td');
$strBody .= get_string('webinars', 'report_rcmr');
$strBody .= html_writer::end_tag('td');
$strBody .= html_writer::start_tag('td');
$strBody .= report_rcmr_webinars($strStartDate, $strEndDate, $boolRedcrossOnly);
$strBody .= html_writer::end_tag('td');
$strBody .= html_writer::end_tag('tr');
//Face to face
$strBody .= html_writer::start_tag('tr');
$strBody .= html_writer::start_tag('td');
$strBody .= get_string('facetoface', 'report_rcmr');
$strBody .= html_writer::end_tag('td');
$strBody .= html_writer::start_tag('td');
$strBody .= report_rcmr_face_to_face($strStartDate, $strEndDate, $boolRedcrossOnly);
$strBody .= html_writer::end_tag('td');
$strBody .= html_writer::end_tag('tr');
//Webinars + F2F (excluding eLearning recorded sessions)
$strBody .= html_writer::start_tag('tr');
$strBody .= html_writer::start_tag('td');
$strBody .= get_string('totalsessions', 'report_rcmr');
$strBody .= html_writer::end_tag('td');
$strBody .= html_writer::start_tag('td');
$strBody .= report_rcmr_webinars($strStartDate, $strEndDate, $boolRedcrossOnly) + report_rcmr_face_to_face($strStartDate, $strEndDate, $boolRedcrossOnly);
$strBody .= html_writer::end_tag('td');
$strBody .= html_writer::end_tag('tr');
// Videos

$arrVideos = report_rcmr_lms_videos($strStartDate, $strEndDate);
$arrTopVideos = report_rcmr_lms_videos_top($arrVideos);

$strBody .= html_writer::start_tag('tr');
$strBody .= html_writer::start_tag('td');
$strBody .= get_string('videosuploaded', 'report_rcmr');
$strBody .= html_writer::end_tag('td');
$strBody .= html_writer::start_tag('td');
$strBody .= count($arrVideos);
$strBody .= html_writer::end_tag('td');
$strBody .= html_writer::end_tag('tr');

$strBody .= html_writer::start_tag('tr');
$strBody .= html_writer::start_tag('td');
$strBody .= get_string('topvideosuploaded', 'report_rcmr');
$strBody .= html_writer::end_tag('td');
$strBody .= html_writer::start_tag('td');
foreach ($arrTopVideos as $strVideo)
{
	$strBody .= html_writer::tag('p', $strVideo);
}
$strBody .= html_writer::end_tag('td');
$strBody .= html_writer::end_tag('tr');

$strBody .= html_writer::start_tag('tr');
$strBody .= html_writer::start_tag('td');
$strBody .= get_string('videoscompleted', 'report_rcmr');
$strBody .= html_writer::end_tag('td');
$strBody .= html_writer::start_tag('td');
$strBody .= report_rcmr_lms_videos_completed($arrVideos);
$strBody .= html_writer::end_tag('td');
$strBody .= html_writer::end_tag('tr');
// Points
$strBody .= html_writer::start_tag('tr');
$strBody .= html_writer::start_tag('td');
$strBody .= get_string('cpdpoints_allocated', 'report_rcmr');
$strBody .= html_writer::end_tag('td');
$strBody .= html_writer::start_tag('td');
$strBody .= report_rcmr_get_points($strStartDate, $strEndDate);
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
$strBody .= report_rcmr_attendance_html($strStartDate, $strEndDate, $boolRedcrossOnly);
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