<?php

require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $DB, $PAGE;
$PAGE->set_url(new \moodle_url('/theme/stablez/test.php'));
$context = context_system::instance(); // System-level context.
$PAGE->set_context($context);

$userid = 0;

$courseid = 2;

die;