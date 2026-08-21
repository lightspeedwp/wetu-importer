<?php
/**
 * Minimal, hermetic bootstrap for unit-testing pure-logic methods on the WETU
 * importer classes without a full WordPress installation.
 *
 * This is intentionally NOT a WordPress test suite (no wp-phpunit, no DB). It
 * stubs only the handful of WordPress functions that
 * LSX_WETU_Importer_Tours::normalize_featured_image_value() calls, backed by
 * an in-memory fake attachment table the tests populate directly. That is
 * enough to exercise the real method under test with no mocking framework.
 *
 * @package LSX_WETU_Importer_Tours
 */

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/**
 * In-memory fake attachment table: attachment_id => url.
 * Tests populate this directly via $GLOBALS before calling the method under
 * test, then unset it in tearDown.
 *
 * @var array<int, string>
 */
$GLOBALS['test_fake_attachments'] = array();

if ( ! function_exists( 'wp_get_attachment_url' ) ) {
	function wp_get_attachment_url( $attachment_id ) {
		return $GLOBALS['test_fake_attachments'][ $attachment_id ] ?? false;
	}
}

if ( ! function_exists( 'attachment_url_to_postid' ) ) {
	function attachment_url_to_postid( $url ) {
		$id = array_search( $url, $GLOBALS['test_fake_attachments'], true );
		return false === $id ? 0 : $id;
	}
}

// LSX_WETU_Importer_Tours extends LSX_WETU_Importer, whose real file runs
// plugin bootstrap side effects at require-time (it recursively requires
// every other importer class and instantiates a singleton) -- appropriate for
// production, but far too heavy for a focused unit test and not something
// this test exercises. A no-op stand-in satisfies `extends` without any of
// that; the real class-lsx-wetu-importer-tours.php is loaded unmodified.
if ( ! class_exists( 'LSX_WETU_Importer' ) ) {
	class LSX_WETU_Importer {}
}

require_once dirname( __DIR__ ) . '/classes/class-lsx-wetu-importer-tours.php';
