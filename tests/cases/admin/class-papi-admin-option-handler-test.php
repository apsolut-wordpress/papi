<?php

/**
 * @group admin
 */
class Papi_Admin_Option_Handler_Test extends WP_UnitTestCase {

	public function setUp() {
		parent::setUp();

		add_filter( 'papi/settings/directories', function () {
			return [1,  PAPI_FIXTURE_DIR . '/page-types'];
		} );

		add_filter( 'papi/is_admin', '__return_true' );

		$this->page_type = papi_get_entry_type_by_id( 'options/header-option-type' );
		$this->property  = $this->page_type->get_property( 'name' );
	}

	public function tearDown() {
		parent::tearDown();

		remove_filter( 'papi/is_admin', '__return_true' );

		unset(
			$_POST,
			$_SERVER['REQUEST_METHOD'],
			$this->handler,
			$this->property,
			$this->page_type
		);
	}

	public function test_actions() {
		$handler = new Papi_Admin_Option_Handler;
		$this->assertGreaterThan( 0, has_action( 'admin_init', [$handler, 'save_properties'] ) );
	}

	public function test_save_options_without_nonce() {
		$_POST = papi_test_create_property_post_data( [
			'slug'  => $this->property->slug,
			'type'  => $this->property,
			'value' => 'Hello, world!'
		], $_POST );

		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_SERVER['REQUEST_URI'] = 'http://site.com/wp-admin/options-general.php?page=papi/options/header-option-type';

		( new Papi_Admin_Option_Handler )->save_properties();

		$this->assertNull( papi_get_option( $this->property->slug ) );
	}

	public function test_save_options_with_nonce() {
		$_POST = papi_test_create_property_post_data( [
			'slug'  => $this->property->slug,
			'type'  => $this->property,
			'value' => 'Hello, world!'
		], $_POST );

		$_POST['papi_meta_nonce'] = wp_create_nonce( 'papi_save_data' );

		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_SERVER['REQUEST_URI'] = 'http://site.com/wp-admin/options-general.php?page=papi/options/header-option-type';

		( new Papi_Admin_Option_Handler )->save_properties();

		$this->assertSame( 'Hello, world!', papi_get_option( $this->property->slug ) );
	}

	public function test_save_properties_fail() {
		$_POST = papi_test_create_property_post_data( [
			'slug'  => $this->property->slug,
			'type'  => $this->property,
			'value' => 'Hello, world!'
		], $_POST );

		$old_request_uri = $_SERVER['REQUEST_URI'];

		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_SERVER['REQUEST_URI'] = 'http://site.com/wp-admin/options-general.php?page=papi/options/header-option-type';

		( new Papi_Admin_Option_Handler )->save_properties();

		$_SERVER['REQUEST_URI'] = $old_request_uri;

		$this->assertNull( papi_get_option( $this->property->slug ) );
	}
}
