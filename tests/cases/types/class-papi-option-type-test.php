<?php

class Papi_Option_Type_Test extends WP_UnitTestCase {

	public function setUp() {
		parent::setUp();

		$_GET = [];

		tests_add_filter( 'papi/settings/directories', function () {
			return [1,  PAPI_FIXTURE_DIR . '/page-types'];
		} );

		$this->header_option_type = papi_get_page_type_by_id( 'options/header-option-type' );
		$this->empty_option_type = new Papi_Option_Type();
	}

	public function tearDown() {
		parent::tearDown();
		unset(
			$_GET,
			$this->header_option_type
		);
	}

	public function test_display() {
		$this->assertFalse( $this->header_option_type->display( $this->header_option_type->post_type[0] ) );
		$this->assertTrue( $this->empty_option_type->display( $this->empty_option_type->post_type[0] ) );
	}

	public function test_get_boxes() {
		$this->assertEmpty( $this->empty_option_type->get_boxes() );

		$this->assertTrue( is_array( $this->header_option_type->get_boxes() ) );

		$boxes = $this->header_option_type->get_boxes();

		$this->assertSame( 'Options', $boxes[0][0]['title'] );
	}

	/**
	 * @issue #153
	 */
	public function test_get_boxes_init_hook() {
		global $wp_current_filter;

		tests_add_filter( 'papi/settings/directories', function () {
			return [1,  PAPI_FIXTURE_DIR . '/page-types'];
		} );

		$admin = new Papi_Admin;
		$header_option_type = papi_get_page_type( PAPI_FIXTURE_DIR . '/page-types/options/header-option-type.php' );
		$_GET['page'] = 'papi/options/header-option-type';

		// Set to setup option type.
		$admin = new Papi_Admin;
		$this->assertTrue( $admin->setup_papi() );

		// Add a value to the option field.
		update_option( 'question', 'Hello, world' );

		// Get option value on init hook.
		$wp_current_filter[] = 'init';
		papi_get_option( 'question' );

		// Setup option type on admin init hook.
		$wp_current_filter[]='admin_init';
		$admin->admin_init();

		// Properties should be 3.
		$boxes = $header_option_type->get_boxes();
		$this->assertSame( 3, count( $boxes[0][1] ) );
	}

	public function test_get_property() {
		$this->assertNull( $this->header_option_type->get_property( 'fake' ) );

		$property = $this->header_option_type->get_property( 'image' );
		$this->assertSame( 'image', $property->get_option( 'type' ) );
		$this->assertSame( 'image', $property->type );
		$this->assertSame( 'papi_image', $property->slug );
		$this->assertSame( 'papi_image', $property->get_option( 'slug' ) );
		$this->assertSame( 'Image', $property->get_option( 'title' ) );
		$this->assertSame( 'Image', $property->title );

		$property = $this->header_option_type->get_property( 'name' );
		$this->assertSame( 'string', $property->get_option( 'type' ) );
		$this->assertSame( 'string', $property->type );
		$this->assertSame( 'papi_name', $property->slug );
		$this->assertSame( 'papi_name', $property->get_option( 'slug' ) );
		$this->assertSame( 'Name', $property->get_option( 'title' ) );
		$this->assertSame( 'Name', $property->title );

		$property = $this->header_option_type->get_property( 'name_levels_2', 'child_name_2' );
		$this->assertSame( 'Child name 2', $property->get_option( 'title' ) );
		$this->assertSame( 'Child name 2', $property->title );
		$this->assertSame( 'papi_child_name_2', $property->slug );
		$this->assertSame( 'papi_child_name_2', $property->get_option( 'slug' ) );
		$this->assertSame( 'string', $property->get_option( 'type' ) );
		$this->assertSame( 'string', $property->type );
	}

	public function test_get_child_properties() {
		$property = $this->header_option_type->get_property( 'name_levels' );
		$children1 = $property->get_child_properties();
		$children2 = $children1[0]->get_child_properties();
		$this->assertTrue( papi_is_property( $children2[0] ) );
		$this->assertSame( 'Child child name', $children2[0]->get_option( 'title' ) );
		$this->assertSame( 'Child child name', $children2[0]->title );
		$this->assertSame( 'string', $children2[0]->get_option( 'type' ) );
		$this->assertSame( 'string', $children2[0]->type );
	}

	public function test_has_post_type() {
		$this->assertTrue( $this->header_option_type->has_post_type( $this->header_option_type->post_type[0] ) );
		$this->assertTrue( $this->empty_option_type->has_post_type( $this->empty_option_type->post_type[0] ) );
	}

	public function test_meta_method() {
		$this->assertSame( 'option_type', $this->header_option_type->_meta_method );
		$this->assertSame( 'option_type', $this->empty_option_type->_meta_method );
	}

	public function test_meta_info() {
		$this->assertEmpty( $this->empty_option_type->name );
		$this->assertEmpty( $this->empty_option_type->menu );

		$this->assertSame( 'Header', $this->header_option_type->name );
		$this->assertSame( 'options-general.php', $this->header_option_type->menu );
	}

	public function test_post_type() {
		$this->assertTrue( is_array( $this->header_option_type->post_type ) );
		$this->assertSame( '_papi_option_type', $this->header_option_type->post_type[0] );
		$this->assertSame( '_papi_option_type', $this->header_option_type->get_post_type() );
		$this->assertTrue( is_array( $this->empty_option_type->post_type ) );
		$this->assertSame( '_papi_option_type', $this->empty_option_type->post_type[0] );
		$this->assertSame( '_papi_option_type', $this->empty_option_type->get_post_type() );
	}

	public function test_render() {
		$this->header_option_type->render();
		$this->expectOutputRegex( '/.*/' );
	}

	public function test_setup() {
		$this->assertNull( $this->header_option_type->setup() );
		$this->assertNull( $this->empty_option_type->setup() );
	}
}
