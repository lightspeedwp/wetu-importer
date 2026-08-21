<?php
/**
 * Regression test for the itinerary `featured_image` URL-corruption bug.
 *
 * Incident: a bare attachment ID stored in a tour's itinerary `featured_image`
 * field (e.g. "945") becomes the non-functional "https://945" the moment a
 * human re-saves the tour in wp-admin, because that field is a CMB2 `file`
 * type nested in a `group`, and CMB2's save-time URL sanitizer treats any
 * schemeless string as a bare domain missing its protocol. This importer's
 * get_current_itinerary_images() carried whatever was already stored forward
 * unchanged on every re-sync, so once a value was corrupted (or was never
 * more than a bare ID to begin with) it stayed that way, and the itinerary
 * renderer's actual read path -- the `featured_image_id` companion field --
 * never got populated at all.
 *
 * These tests exercise normalize_featured_image_value() directly (via
 * Reflection, since it is protected) against every shape that field has been
 * found holding in the wild, using ReflectionClass::newInstanceWithoutConstructor()
 * so the parent class's WordPress-hook-registering constructor never runs.
 *
 * @package LSX_WETU_Importer_Tours
 */

class Test_Itinerary_Featured_Image extends PHPUnit\Framework\TestCase {

	/**
	 * @var LSX_WETU_Importer_Tours
	 */
	private $tours;

	protected function setUp(): void {
		parent::setUp();

		$reflection  = new ReflectionClass( LSX_WETU_Importer_Tours::class );
		$this->tours = $reflection->newInstanceWithoutConstructor();

		$GLOBALS['test_fake_attachments'] = array();
	}

	protected function tearDown(): void {
		$GLOBALS['test_fake_attachments'] = array();
		parent::tearDown();
	}

	/**
	 * Calls the protected normalize_featured_image_value() under test.
	 *
	 * @param string $value
	 * @return array{url: string, id: string}|null
	 */
	private function normalize( string $value ): ?array {
		$method = new ReflectionMethod( LSX_WETU_Importer_Tours::class, 'normalize_featured_image_value' );
		$method->setAccessible( true );

		return $method->invoke( $this->tours, $value );
	}

	/**
	 * The exact corruption reported: "https://" followed by a bare attachment
	 * ID, with no path, produced by CMB2's save-time URL sanitizer treating the
	 * bare ID as a schemeless domain.
	 */
	public function test_corrupted_https_bare_id_resolves_to_real_url_and_id(): void {
		$GLOBALS['test_fake_attachments'][945] = 'https://example.com/wp-content/uploads/mkuze.jpg';

		$result = $this->normalize( 'https://945' );

		$this->assertSame( 'https://example.com/wp-content/uploads/mkuze.jpg', $result['url'] );
		$this->assertSame( '945', $result['id'] );
	}

	/**
	 * The pre-corruption shape: a bare attachment ID with no scheme at all.
	 * This is what the field held before anyone re-saved the tour in
	 * wp-admin, and is exactly as invalid a `featured_image` value as the
	 * corrupted form -- it must resolve the same way, not merely be left
	 * alone, or the very next save will corrupt it via the same CMB2 path.
	 */
	public function test_bare_id_resolves_to_real_url_and_id(): void {
		$GLOBALS['test_fake_attachments'][8076] = 'https://example.com/wp-content/uploads/kosi-bay.jpg';

		$result = $this->normalize( '8076' );

		$this->assertSame( 'https://example.com/wp-content/uploads/kosi-bay.jpg', $result['url'] );
		$this->assertSame( '8076', $result['id'] );
	}

	/**
	 * The already-correct shape (a real URL) must be passed through as-is,
	 * with its attachment ID resolved so `featured_image_id` -- the field the
	 * itinerary renderer actually reads -- gets populated too.
	 */
	public function test_real_url_is_preserved_and_id_is_resolved(): void {
		$url                                  = 'https://example.com/wp-content/uploads/dolphins.jpg';
		$GLOBALS['test_fake_attachments'][23250] = $url;

		$result = $this->normalize( $url );

		$this->assertSame( $url, $result['url'] );
		$this->assertSame( '23250', $result['id'] );
	}

	/**
	 * A real URL for an attachment this importer cannot resolve back to a
	 * post ID (e.g. an external image, or one WordPress's own url-to-postid
	 * lookup fails on) must still be kept -- losing a working URL because its
	 * ID can't be resolved would be a regression in itself.
	 */
	public function test_real_url_with_unresolvable_id_keeps_url_with_empty_id(): void {
		$result = $this->normalize( 'https://cdn.example.com/not-a-local-attachment.jpg' );

		$this->assertSame( 'https://cdn.example.com/not-a-local-attachment.jpg', $result['url'] );
		$this->assertSame( '', $result['id'] );
	}

	/**
	 * An attachment ID (bare or corrupted) that no longer exists must be
	 * dropped, not carried forward as a broken value. This is the case found
	 * on the affected site: 4-7 itinerary references pointed at attachments
	 * that had been deleted, orphaned rather than corrupted.
	 */
	public function test_bare_id_for_deleted_attachment_returns_null(): void {
		$this->assertNull( $this->normalize( '999999' ) );
	}

	/**
	 * Same as above for the corrupted "https://<id>" shape -- a corrupted
	 * reference to an attachment that no longer exists must not be "repaired"
	 * into a URL that 404s.
	 */
	public function test_corrupted_https_bare_id_for_deleted_attachment_returns_null(): void {
		$this->assertNull( $this->normalize( 'https://999999' ) );
	}

	/**
	 * http:// (not https://) is the same defect via the same mechanism and
	 * must be handled identically to the https:// case.
	 */
	public function test_corrupted_http_bare_id_resolves_to_real_url_and_id(): void {
		$GLOBALS['test_fake_attachments'][31940] = 'https://example.com/wp-content/uploads/makuwa.jpg';

		$result = $this->normalize( 'http://31940' );

		$this->assertSame( 'https://example.com/wp-content/uploads/makuwa.jpg', $result['url'] );
		$this->assertSame( '31940', $result['id'] );
	}

	/**
	 * get_current_itinerary_images() is the actual carry-forward path a sync
	 * uses. This is an end-to-end check that it normalizes every entry it
	 * reads rather than just proving the underlying helper works in
	 * isolation -- a future edit could normalize the value and then forget to
	 * use the normalized result.
	 */
	public function test_get_current_itinerary_images_normalizes_every_entry(): void {
		$GLOBALS['test_fake_attachments'][945]  = 'https://example.com/wp-content/uploads/mkuze.jpg';
		$GLOBALS['test_fake_attachments'][8076] = 'https://example.com/wp-content/uploads/kosi-bay.jpg';

		global $wpdb; // Unused by the fake below, but keeps get_post_meta's real signature familiar to a reader.
		unset( $wpdb );

		// get_current_itinerary_images() calls get_post_meta( $id, 'itinerary', false ),
		// so the fake must return an array of itinerary arrays (one per day),
		// each shaped like the real stored value.
		global $test_post_meta;
		$test_post_meta = array(
			31766 => array(
				array( 'featured_image' => 'https://945' ),   // corrupted.
				array( 'featured_image' => '8076' ),          // bare ID, never corrupted.
				array( 'featured_image' => '' ),              // never set -- must stay absent, not become an entry.
			),
		);

		if ( ! function_exists( 'get_post_meta' ) ) {
			function get_post_meta( $post_id, $key, $single ) {
				global $test_post_meta;
				return $test_post_meta[ $post_id ] ?? array();
			}
		}

		$result = $this->tours->get_current_itinerary_images( 31766 );

		$this->assertCount( 2, $result, 'the empty third day must not produce an entry' );
		$this->assertSame( 'https://example.com/wp-content/uploads/mkuze.jpg', $result[1]['url'] );
		$this->assertSame( '945', $result[1]['id'] );
		$this->assertSame( 'https://example.com/wp-content/uploads/kosi-bay.jpg', $result[2]['url'] );
		$this->assertSame( '8076', $result[2]['id'] );
	}
}
