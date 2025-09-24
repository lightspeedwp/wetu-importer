<?php

/**
 * PHPUnit bootstrap file for WETU Importer tests.
 *
 * @package WetuImporter
 */

// Load WordPress test environment
if (! getenv('WP_TESTS_DIR')) {
	putenv('WP_TESTS_DIR=/tmp/wordpress-tests-lib');
}

$_tests_dir = getenv('WP_TESTS_DIR');

if (! file_exists($_tests_dir . '/includes/functions.php')) {
	echo "Could not find $_tests_dir/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit(1);
}

// Load WordPress test framework
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin()
{
	require dirname(dirname(__DIR__)) . '/lsx-importer-for-wetu.php';
}
tests_add_filter('muplugins_loaded', '_manually_load_plugin');

// Start up the WP testing environment
require $_tests_dir . '/includes/bootstrap.php';

// Load Brain Monkey for mocking
if (class_exists('Brain\Monkey\Functions')) {
	Brain\Monkey\setUp();
}
