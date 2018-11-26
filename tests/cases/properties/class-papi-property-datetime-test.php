<?php

/**
 * @group properties
 */
class Papi_Property_Datetime_Test extends Papi_Property_Test_Case {

	public $slug = 'datetime_test';

	public function get_value() {
		return '2014-11-23';
	}

	public function get_expected() {
		return '2014-11-23';
	}

	public function test_property_format_value() {
		$this->assertSame( '2014-11-23', $this->property->format_value( '2014-11-23', '', 0 ) );
		$this->assertSame( '2014-11-24', $this->property->format_value( '2014-11-24', '', 0 ) );
		$this->assertEmpty( $this->property->format_value( null, '', 0 ) );
	}

	public function test_property_options() {
		$this->assertSame( 'datetime', $this->property->get_option( 'type' ) );
		$this->assertSame( 'Datetime test', $this->property->get_option( 'title' ) );
		$this->assertSame( 'papi_datetime_test', $this->property->get_option( 'slug' ) );
	}
}
