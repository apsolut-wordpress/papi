<?php

/**
 * @group core
 */
class Papi_Core_Conditional_Rules_Test extends WP_UnitTestCase {

	public function setUp() {
		parent::setUp();

		add_filter( 'papi/settings/directories', function () {
			return [1,  PAPI_FIXTURE_DIR . '/page-types'];
		} );

		$this->post_id = $this->factory->post->create();

		$_GET = [];
		$_GET['post'] = $this->post_id;

		update_post_meta( $this->post_id, papi_get_page_type_key(), 'rule-page-type' );
	}

	public function tearDown() {
		parent::tearDown();
		unset(
			$this->post_id,
			$_GET
		);
	}

	private function save_properties( $property ) {
		$handler = new Papi_Admin_Meta_Handler();

		$_POST = papi_test_create_property_post_data( [
			'slug'  => $property->slug,
			'type'  => $property,
			'value' => $property->value
		], $_POST );

		$handler->save_properties( $this->post_id );
	}

	public function test_rule_equal_option() {
		global $current_screen;

		$current_screen = WP_Screen::get( 'admin_init' );

		$_SERVER['REQUEST_URI'] = 'http://site.com/?page=papi/options/header-option-type';

		$result = papi_filter_conditional_rule_allowed( [
			'operator' => 'NOT EXISTS',
			'slug'     => 'name'
		] );

		$this->assertTrue( $result );

		$result = papi_filter_conditional_rule_allowed( [
			'operator' => '=',
			'slug'     => 'name',
			'value'    => ''
		] );

		$this->assertFalse( $result );

		update_option( 'name', 'Fredrik' );

		$result = papi_filter_conditional_rule_allowed( [
			'operator' => '=',
			'slug'     => 'name',
			'value'    => 'Fredrik'
		] );

		$this->assertTrue( $result );

		$_SERVER['REQUEST_URI'] = '';

		$current_screen = null;
	}

	public function test_rule_equal() {
		$property = papi_property( [
			'title' => 'Name',
			'type'  => 'string',
			'value' => 'Fredrik'
		] );

		$this->save_properties( $property );

		$result = papi_filter_conditional_rule_allowed( [
			'operator' => '=',
			'slug'     => 'name',
			'value'    => ''
		] );

		$this->assertFalse( $result );

		$result = papi_filter_conditional_rule_allowed( [
			'operator' => '=',
			'slug'     => 'name',
			'value'    => 'Fredrik'
		] );

		$this->assertTrue( $result );

		$property = papi_property( [
			'title' => 'Number',
			'type'  => 'number',
			'slug'  => 'number',
			'value' => 1.1
		] );

		$this->save_properties( $property );

		$result = papi_filter_conditional_rule_allowed( [
			'operator' => '=',
			'slug'     => 'number',
			'value'    => 1.1
		] );

		$this->assertTrue( $result );
	}

	public function test_rule_equal_bool_true() {
		$property = papi_property( [
			'title' => 'Name',
			'type'  => 'string',
			'value' => 'true'
		] );

		$this->save_properties( $property );

		$result = papi_filter_conditional_rule_allowed( [
			'operator' => '=',
			'slug'     => 'name',
			'value'    => true
		] );

		$this->assertTrue( $result );
	}

	public function test_rule_equal_bool_false() {
		$property = papi_property( [
			'title' => 'Name',
			'type'  => 'string',
			'value' => 'false'
		] );

		$this->save_properties( $property );

		$result = papi_filter_conditional_rule_allowed( [
			'operator' => '=',
			'slug'     => 'name',
			'value'    => false
		] );

		$this->assertTrue( $result );
	}

	public function test_rule_not_equal() {
		$property = papi_property( [
			'title' => 'Name',
			'type'  => 'string',
			'slug'  => 'name',
			'value' => 'Fredrik'
		] );

		$this->save_properties( $property );

		$result = papi_filter_conditional_rule_allowed( [
			'operator' => '!=',
			'slug'     => 'name',
			'value'    => 'Fredrik'
		] );

		$this->assertFalse( $result );

		$result = papi_filter_conditional_rule_allowed( [
			'operator' => '!=',
			'slug'     => 'name',
			'value'    => ''
		] );

		$this->assertTrue( $result );
	}

	public function test_rule_not_equal_bool_true() {
		$property = papi_property( [
			'title' => 'Name',
			'type'  => 'string',
			'value' => 'false'
		] );

		$this->save_properties( $property );

		$result = papi_filter_conditional_rule_allowed( [
			'operator' => '!=',
			'slug'     => 'name',
			'value'    => true
		] );

		$this->assertTrue( $result );
	}

	public function test_rule_not_equal_bool_false() {
		$property = papi_property( [
			'title' => 'Name',
			'type'  => 'string',
			'value' => 'true'
		] );

		$this->save_properties( $property );

		$result = papi_filter_conditional_rule_allowed( [
			'operator' => '!=',
			'slug'     => 'name',
			'value'    => false
		] );

		$this->assertTrue( $result );
	}

	public function test_rule_greater_then() {
		$property = papi_property( [
			'title' => 'Number',
			'type'  => 'number',
			'slug'  => 'number',
			'value' => 1
		] );

		$this->save_properties( $property );

		$result = papi_filter_conditional_rule_allowed( [
			'operator' => '>',
			'slug'     => 'fake',
			'value'    => 1
		] );

		$this->assertFalse( $result );

		$result = papi_filter_conditional_rule_allowed( [
			'operator' => '>',
			'slug'     => 'number',
			'value'    => 1
		] );

		$this->assertFalse( $result );

		$result = papi_filter_conditional_rule_allowed( [
			'operator' => '>',
			'slug'     => 'number',
			'value'    => '1.1'
		] );

		$this->assertFalse( $result );

		$result = papi_filter_conditional_rule_allowed( [
			'operator' => '>',
			'slug'     => 'number',
			'value'    => 0
		] );

		$this->assertTrue( $result );

		$result = papi_filter_conditional_rule_allowed( [
			'operator' => '>',
			'slug'     => 'number',
			'value'    => '0.9'
		] );

		$this->assertTrue( $result );

		$result = papi_filter_conditional_rule_allowed( [
			'operator' => '>',
			'slug'     => 'number',
			'source'   => [1, 2],
			'value'    => 1
		] );

		$this->assertTrue( $result );
	}

	public function test_rule_greater_then_or_equal() {
		$property = papi_property( [
			'title' => 'Number',
			'type'  => 'number',
			'slug'  => 'number',
			'value' => 1
		] );

		$this->save_properties( $property );

		$result = papi_filter_conditional_rule_allowed( [
			'operator' => '>=',
			'slug'     => 'fake',
			'value'    => 1
		] );

		$this->assertFalse( $result );

		$result = papi_filter_conditional_rule_allowed( [
			'operator' => '>=',
			'slug'     => 'number',
			'value'    => 1
		] );

		$this->assertTrue( $result );

		$result = papi_filter_conditional_rule_allowed( [
			'operator' => '>=',
			'slug'     => 'number',
			'value'    => 0
		] );

		$this->assertTrue( $result );

		$result = papi_filter_conditional_rule_allowed( [
			'operator' => '>=',
			'slug'     => 'number',
			'value'    => '0'
		] );

		$this->assertTrue( $result );

		$result = papi_filter_conditional_rule_allowed( [
			'operator' => '>=',
			'slug'     => 'number',
			'source'   => [1, 2],
			'value'    => 2
		] );

		$this->assertTrue( $result );
	}

	public function test_rule_less_then() {
		$property = papi_property( [
			'title' => 'Number',
			'type'  => 'number',
			'slug'  => 'number',
			'value' => 1
		] );

		$this->save_properties( $property );

		$result = papi_filter_conditional_rule_allowed( [
			'operator' => '<',
			'slug'     => 'fake',
			'value'    => 1
		] );

		$this->assertFalse( $result );

		$result = papi_filter_conditional_rule_allowed( [
			'operator' => '<',
			'slug'     => 'number',
			'value'    => 1
		] );

		$this->assertFalse( $result );

		$result = papi_filter_conditional_rule_allowed( [
			'operator' => '<',
			'slug'     => 'number',
			'value'    => 2
		] );

		$this->assertTrue( $result );

		$result = papi_filter_conditional_rule_allowed( [
			'operator' => '<',
			'slug'     => 'number',
			'source'   => [1, 2],
			'value'    => 3
		] );

		$this->assertTrue( $result );
	}

	public function test_rule_less_then_or_equal() {
		$property = papi_property( [
			'title' => 'Number',
			'type'  => 'number',
			'slug'  => 'number',
			'value' => 1
		] );

		$this->save_properties( $property );

		$result = papi_filter_conditional_rule_allowed( [
			'operator' => '<=',
			'slug'     => 'fake',
			'value'    => 1
		] );

		$this->assertFalse( $result );

		$result = papi_filter_conditional_rule_allowed( [
			'operator' => '<=',
			'slug'     => 'number',
			'value'    => 1
		] );

		$this->assertTrue( $result );

		$result = papi_filter_conditional_rule_allowed( [
			'operator' => '<=',
			'slug'     => 'number',
			'value'    => 2
		] );

		$this->assertTrue( $result );

		$result = papi_filter_conditional_rule_allowed( [
			'operator' => '<=',
			'slug'     => 'number',
			'source'   => [1, 2],
			'value'    => 2
		] );

		$this->assertTrue( $result );
	}

	public function test_rule_in() {
		$property = papi_property( [
			'title' => 'Number',
			'type'  => 'number',
			'slug'  => 'number',
			'value' => 1
		] );

		$this->save_properties( $property );

		$result = papi_filter_conditional_rule_allowed( [
			'operator' => 'IN',
			'slug'     => 'number',
			'value'    => 10
		] );

		$this->assertFalse( $result );

		$result = papi_filter_conditional_rule_allowed( [
			'operator' => 'IN',
			'slug'     => 'number',
			'value'    => [10, 20]
		] );

		$this->assertFalse( $result );

		$result = papi_filter_conditional_rule_allowed( [
			'operator' => 'IN',
			'slug'     => 'number',
			'value'    => [1, 2]
		] );

		$this->assertTrue( $result );
	}

	public function test_rule_not_in() {
		$property = papi_property( [
			'title' => 'Number',
			'type'  => 'number',
			'slug'  => 'number',
			'value' => 1
		] );

		$this->save_properties( $property );

		$result = papi_filter_conditional_rule_allowed( [
			'operator' => 'NOT IN',
			'slug'     => 'number',
			'value'    => 10
		] );

		$this->assertFalse( $result );

		$result = papi_filter_conditional_rule_allowed( [
			'operator' => 'NOT IN',
			'slug'     => 'number',
			'value'    => [1, 2]
		] );

		$this->assertFalse( $result );

		$result = papi_filter_conditional_rule_allowed( [
			'operator' => 'NOT IN',
			'slug'     => 'number',
			'value'    => [1, null]
		] );

		$this->assertFalse( $result );

		$result = papi_filter_conditional_rule_allowed( [
			'operator' => 'NOT IN',
			'slug'     => 'number',
			'value'    => [10, 20]
		] );

		$this->assertTrue( $result );

		$result = papi_filter_conditional_rule_allowed( [
			'operator' => 'NOT IN',
			'slug'     => 'number',
			'value'    => ['10', '20']
		] );

		$this->assertTrue( $result );
	}

	public function test_rule_like() {
		$property = papi_property( [
			'title' => 'Name',
			'type'  => 'string',
			'slug'  => 'name',
			'value' => 'Fredrik'
		] );

		$this->save_properties( $property );

		$result = papi_filter_conditional_rule_allowed( [
			'operator' => 'LIKE',
			'slug'     => 'name',
			'value'    => 'Elli'
		] );

		$this->assertFalse( $result );

		$result = papi_filter_conditional_rule_allowed( [
			'operator' => 'LIKE',
			'slug'     => 'number',
			'value'    => ''
		] );

		$this->assertFalse( $result );

		$result = papi_filter_conditional_rule_allowed( [
			'operator' => 'LIKE',
			'slug'     => 'name',
			'value'    => 'rik'
		] );

		$this->assertTrue( $result );

		$property = papi_property( [
			'title' => 'Name',
			'type'  => 'string',
			'slug'  => 'name2',
			'value' => 124
		] );

		$this->save_properties( $property );

		$result = papi_filter_conditional_rule_allowed( [
			'operator' => 'LIKE',
			'slug'     => 'name2',
			'value'    => 1
		] );

		$this->assertTrue( $result );
	}

	public function test_rule_between() {
		$property = papi_property( [
			'title' => 'Number',
			'type'  => 'number',
			'slug'  => 'number',
			'value' => 1
		] );

		$this->save_properties( $property );

		$result = papi_filter_conditional_rule_allowed( [
			'operator' => 'BETWEEN',
			'slug'     => 'number',
			'value'    => ''
		] );

		$this->assertFalse( $result );

		$result = papi_filter_conditional_rule_allowed( [
			'operator' => 'BETWEEN',
			'slug'     => 'number',
			'value'    => [10, 20]
		] );

		$this->assertFalse( $result );

		$result = papi_filter_conditional_rule_allowed( [
			'operator' => 'BETWEEN',
			'slug'     => 'number',
			'value'    => ['10', '20']
		] );

		$this->assertFalse( $result );

		$result = papi_filter_conditional_rule_allowed( [
			'operator' => 'BETWEEN',
			'slug'     => 'number',
			'value'    => [1, null]
		] );

		$this->assertFalse( $result );

		$result = papi_filter_conditional_rule_allowed( [
			'operator' => 'BETWEEN',
			'slug'     => 'number',
			'value'    => [0, 2]
		] );

		$this->assertTrue( $result );

		$result = papi_filter_conditional_rule_allowed( [
			'operator' => 'BETWEEN',
			'slug'     => 'number',
			'value'    => ['0', '2']
		] );

		$this->assertTrue( $result );
	}

	public function test_rule_not_between() {
		$property = papi_property( [
			'title' => 'Number',
			'type'  => 'number',
			'slug'  => 'number',
			'value' => 1
		] );

		$this->save_properties( $property );

		$result = papi_filter_conditional_rule_allowed( [
			'operator' => 'NOT BETWEEN',
			'slug'     => 'number',
			'value'    => ''
		] );

		$this->assertFalse( $result );

		$result = papi_filter_conditional_rule_allowed( [
			'operator' => 'NOT BETWEEN',
			'slug'     => 'number',
			'value'    => [1, 2]
		] );

		$this->assertFalse( $result );

		$result = papi_filter_conditional_rule_allowed( [
			'operator' => 'NOT BETWEEN',
			'slug'     => 'number',
			'value'    => ['0', '2']
		] );

		$this->assertFalse( $result );

		$result = papi_filter_conditional_rule_allowed( [
			'operator' => 'NOT BETWEEN',
			'slug'     => 'number',
			'value'    => [1, null]
		] );

		$this->assertFalse( $result );

		$result = papi_filter_conditional_rule_allowed( [
			'operator' => 'NOT BETWEEN',
			'slug'     => 'number',
			'value'    => [0, 2]
		] );

		$this->assertFalse( $result );

		$result = papi_filter_conditional_rule_allowed( [
			'operator' => 'NOT BETWEEN',
			'slug'     => 'number',
			'value'    => ['10', '20']
		] );

		$this->assertTrue( $result );

		$result = papi_filter_conditional_rule_allowed( [
			'operator' => 'NOT BETWEEN',
			'slug'     => 'number',
			'value'    => [10, 20]
		] );

		$this->assertTrue( $result );
	}

	public function test_rule_exists() {
		$property = papi_property( [
			'title' => 'Number',
			'type'  => 'number',
			'slug'  => 'number',
			'value' => 1
		] );

		$this->save_properties( $property );

		$result = papi_filter_conditional_rule_allowed( [
			'operator' => 'EXISTS',
			'slug'     => 'fake'
		] );

		$this->assertFalse( $result );

		$result = papi_filter_conditional_rule_allowed( [
			'operator' => 'EXISTS',
			'slug'     => 'number'
		] );

		$this->assertTrue( $result );
	}

	public function test_rule_not_exists() {
		$property = papi_property( [
			'title' => 'Number',
			'type'  => 'number',
			'slug'  => 'number',
			'value' => 1
		] );

		$this->save_properties( $property );

		$result = papi_filter_conditional_rule_allowed( [
			'operator' => 'NOT EXISTS',
			'slug'     => 'number'
		] );

		$this->assertFalse( $result );

		$result = papi_filter_conditional_rule_allowed( [
			'operator' => 'NOT EXISTS',
			'slug'     => 'fake'
		] );

		$this->assertTrue( $result );
	}

	public function test_rule_empty() {
		$property = papi_property( [
			'title' => 'Number',
			'type'  => 'number',
			'slug'  => 'number',
			'value' => 1
		] );

		$result = papi_filter_conditional_rule_allowed( [
			'operator' => 'EMPTY',
			'slug'     => 'number'
		] );

		$this->assertTrue( $result );

		$this->save_properties( $property );

		$result = papi_filter_conditional_rule_allowed( [
			'operator' => 'EMPTY',
			'slug'     => 'number'
		] );

		$this->assertFalse( $result );
	}

	public function test_rule_not_empty() {
		$property = papi_property( [
			'title' => 'Number',
			'type'  => 'number',
			'slug'  => 'number',
			'value' => 1
		] );

		$result = papi_filter_conditional_rule_allowed( [
			'operator' => 'NOT EMPTY',
			'slug'     => 'number'
		] );

		$this->assertFalse( $result );

		$this->save_properties( $property );

		$result = papi_filter_conditional_rule_allowed( [
			'operator' => 'NOT EMPTY',
			'slug'     => 'number'
		] );

		$this->assertTrue( $result );
	}

	public function test_setup_filters() {
		$rules = new Papi_Core_Conditional_Rules();
		$this->assertNull( $rules->setup_filters() );
	}
}
