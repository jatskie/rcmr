<?php
require_once('../../config.php');
require_once('../../course/lib.php');
require_once($CFG->dirroot.'/report/rcmr/locallib.php');

$intId 	  		= optional_param('id', $SITE->id, PARAM_INT);
$strStartDate 	= optional_param('startdate', '', PARAM_RAW);
$strEndDate 	= optional_param('enddate', '', PARAM_RAW);
$intCourseid	= optional_param('courseid', 0, PARAM_INT);
$intCategoryid	= optional_param('categoryid', 0, PARAM_INT);

$course = $DB->get_record('course', array('id' => $intId), '*', MUST_EXIST);

require_login($course);
$context = context_course::instance($course->id);
require_capability('report/loglive:view', $context);
$strTitle = get_string('reportcompletion', 'report_rcmr');

$url = new moodle_url('/report/rcmr/completion.php');
$PAGE->set_url($url);
$PAGE->set_title($strTitle);
$PAGE->set_pagelayout('standard');
$PAGE->navbar->add(get_string('report'));
$PAGE->navbar->add(get_string('pluginname', 'report_rcmr'));
$PAGE->navbar->add(get_string('reportcompletion', 'report_rcmr'), new moodle_url('completion.php'));
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');

echo $OUTPUT->header();

$arrCompletionData = report_rcmr_completion($strStartDate, $strEndDate, $intCourseid, $intCategoryid);
// Generate HTML output
$strScript = ' $(function() { $( "#startdate" ).datepicker(); $( "#enddate" ).datepicker(); });';

$strBody  = '<style>.nav-collapse.collapse{ visibility: hidden;}</style>';
$strBody .= html_writer::tag('h1', get_string('reportcompletion', 'report_rcmr') );
$strBody .= html_writer::empty_tag('hr');
$strBody .= html_writer::start_div('row');
$strBody .= html_writer::start_tag('form', array('method' => 'post', 'action' => $CFG->wwwroot.'/report/rcmr/completion.php'));
$strBody .= html_writer::start_tag('table', array('class' => 'table table-striped report-rcmr'));
$strBody .= html_writer::start_tag('tr', array('colspan' => 2));
$strBody .= html_writer::start_tag('td');
$strBody .= html_writer::start_div('container');
$strBody .= html_writer::start_div('span6');
$strBody .= get_string('timeframe', 'report_rcmr') . '&nbsp;' .html_writer::empty_tag('input', array('name' => 'startdate', 'id' => 'startdate', 'placeholder' => 'Start Date', 'value' => $strStartDate));
$strBody .= '&nbsp; to &nbsp;';
$strBody .= html_writer::empty_tag('input', array('name' => 'enddate', 'id' => 'enddate', 'placeholder' => 'End Date', 'value' => $strEndDate));
$strBody .= html_writer::end_div();
$strBody .= html_writer::start_div('span3');
$strBody .= 'Select Course <br/>' . report_rcmr_build_course_dropdown($intCourseid);
$strBody .= 'Select Category <br/>' . report_rcmr_build_course_category_dropdown($intCategoryid);
$strBody .= html_writer::end_div();
$strBody .= html_writer::start_div('span12');
$strBody .= html_writer::tag('button', get_string('viewreport', 'report_rcmr'), array('class' => 'btn btn-primary', 'type' => 'submit'));
$strBody .= html_writer::end_div();
$strBody .= html_writer::end_tag('td');
$strBody .= html_writer::end_tag('tr');

$strBody .= html_writer::end_tag('table');
$strBody .= html_writer::end_tag('form');
$strBody .= html_writer::end_div();

$strBody .= html_writer::start_tag('tr', array('colspan' => 2));
$strBody .= html_writer::start_tag('td');
$strBody .= report_rcmr_completion_html($arrCompletionData);
$strBody .= html_writer::end_tag('td');
$strBody .= html_writer::end_tag('tr');

$strBody .= html_writer::tag('script', $strScript);

echo $strBody;
echo $OUTPUT->footer();
?>