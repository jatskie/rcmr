<?php

defined('MOODLE_INTERNAL') || die;

$ADMIN->add(
		'reports', new admin_externalpage(
			'rcreport', 
			get_string('pluginname', 'report_rcmr'), 
			"$CFG->wwwroot/report/rcmr/index.php", 
			'report/stats:view', 
			empty($CFG->enablestats)
		)
);

// No report settings.
$settings = null;
