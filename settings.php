<?php

defined('MOODLE_INTERNAL') || die;
// Adds a folder to the nav tree
$ADMIN->add('reports', new admin_category('rcmrfolder', get_string('pluginname', 'report_rcmr')));
// Adds the summary page (index.php)
$ADMIN->add('rcmrfolder', new admin_externalpage('report_rcmr', get_string('reportsummary', 'report_rcmr'),	"/report/rcmr/index.php", 'report/stats:view',	empty($CFG->enablestats)));
// Adds completion reportin to the nav tree
$ADMIN->add('rcmrfolder', new admin_externalpage('report_rcmr_course', get_string('reportcompletion', 'report_rcmr'), "/report/rcmr/completion.php", 'report/stats:view',	empty($CFG->enablestats)));

// No report settings.
//$settings = null;