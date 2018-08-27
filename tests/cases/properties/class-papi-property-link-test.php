<?php

/**
 * @group properties
 */
class Papi_Property_Link_Test extends Papi_Property_Test_Case {

	public $slug = 'link_test';

	public function assert_values( $expected, $actual, $slug ) {
		$this->assertSame( $expected->target, $actual->target );
		$this->assertSame( $expected->title, $actual->title );
		$this->assertSame( $expected->url, $actual->url );
		$this->assertSame( 0, $actual->post_id );
	}

	public function get_value() {
		return [
			'url'    => 'http://example.org',
			'title'  => 'Example site',
			'target' => '_blank'
		];
	}

	public function get_expected() {
		return (object) [
			'url'    => 'http://example.org',
			'title'  => 'Example site',
			'target' => '_blank'
		];
	}

	public function test_property_convert_type() {
		$this->assertSame( 'object', $this->property->convert_type );
	}

	public function test_property_default_value() {
		$this->assertSame( [], $this->property->default_value );
	}

	public function test_property_delete_value() {
		$this->save_properties_value( $this->property );
		$result = $this->property->delete_value( $this->property->slug, $this->post_id, 'post' );
		$this->assertTrue( $result );
	}

	public function test_property_format_value() {
		$post_id = $this->factory->post->create();
		$value   = [
			'_papi_link_target' => '_blank',
			'_papi_link_title'  => 'Example site',
			'_papi_link_url'    => 'http://example.org'
		];

		$output = $this->property->format_value( $value, '_papi_link', $post_id );

		$this->assertSame( '_blank', $output->target );
		$this->assertSame( 'Example site', $output->title );
		$this->assertSame( 'http://example.org', $output->url );

		$output = $this->property->format_value( (object) $value, '_papi_link', $post_id );

		$this->assertSame( '_blank', $output->target );
		$this->assertSame( 'Example site', $output->title );
		$this->assertSame( 'http://example.org', $output->url );
	}

	public function test_property_format_value_empty_target() {
		$post_id = $this->factory->post->create();
		$value   = [
			'_papi_link_title'  => 'Example site',
			'_papi_link_url'    => 'http://example.org'
		];

		$output = $this->property->format_value( $value, '_papi_link', $post_id );

		$this->assertSame( '_self', $output->target );
		$this->assertSame( 'Example site', $output->title );
		$this->assertSame( 'http://example.org', $output->url );
	}

	public function test_property_format_value_real_post() {
		$post_id   = $this->factory->post->create();
		$input     = [
			'url'    => rtrim( get_permalink( $post_id ), '/' ),
			'title'  => get_the_title( $post_id ),
			'target' => '_blank'
		];
		$expected  = (object) [
			'url'     => get_permalink( $post_id ),
			'title'   => get_the_title( $post_id ),
			'target'  => '_blank',
			'post_id' => $post_id
		];
		$this->assertEquals( $expected, $this->property->format_value( $input, $this->property->slug, 0 ) );
	}

	public function test_property_import_value() {
		$expected = [
			'papi_link_test_url'     => 'http://example.org',
			'papi_link_test_title'   => 'Example site',
			'papi_link_test_target'  => '_blank',
			'papi_link_test_post_id' => 0,
			'papi_link_test'         => 1
		];
		$this->assertEquals( $expected, $this->property->import_value( $this->get_value(), $this->property->slug, 0 ) );
		$this->assertEquals( $expected, $this->property->import_value( (object) $this->get_value(), $this->property->slug, 0 ) );

		$this->assertNull( $this->property->import_value( null, '', 0 ) );
		$this->assertNull( $this->property->import_value( true, '', 0 ) );
		$this->assertNull( $this->property->import_value( false, '', 0 ) );
		$this->assertNull( $this->property->import_value( 1, '', 0 ) );
		$this->assertNull( $this->property->import_value( 'test', '', 0 ) );
	}

	public function test_property_import_value_real_post() {
		$post_id   = $this->factory->post->create();
		$permalink = get_permalink( $post_id );
		$input     = [
			'url'    => get_permalink( $post_id ),
			'title'  => get_the_title( $post_id ),
			'target' => '_blank'
		];
		$expected  = [
			'papi_link_test_url'     => get_permalink( $post_id ),
			'papi_link_test_title'   => get_the_title( $post_id ),
			'papi_link_test_target'  => '_blank',
			'papi_link_test_post_id' => $post_id,
			'papi_link_test'         => 1,
		];
		$this->assertEquals( $expected, $this->property->import_value( $input, $this->property->slug, 0 ) );
	}

	public function test_property_load_value() {
		$post_id = $this->factory->post->create();
		$value   = [
			'_papi_link_target' => '_blank',
			'_papi_link_title'  => 'Example site',
			'_papi_link_url'    => 'http://example.org'
		];

		foreach ( $value as $k => $v ) {
			update_post_meta( $post_id, unpapify( $k ) , $v );
		}

		$output = $this->property->load_value( null, '_papi_link', $post_id );

		$this->assertSame( '_blank', $output->target );
		$this->assertSame( 'Example site', $output->title );
		$this->assertSame( 'http://example.org', $output->url );
	}

	public function test_property_load_value_empty_target() {
		$post_id = $this->factory->post->create();
		$value   = [
			'_papi_link_title'  => 'Example site',
			'_papi_link_url'    => 'http://example.org'
		];

		foreach ( $value as $k => $v ) {
			update_post_meta( $post_id, unpapify( $k ) , $v );
		}

		$output = $this->property->load_value( $value, '_papi_link', $post_id );

		$this->assertSame( '_self', $output->target );
		$this->assertSame( 'Example site', $output->title );
		$this->assertSame( 'http://example.org', $output->url );
	}

	public function test_property_load_value_real_post() {
		$post_id   = $this->factory->post->create();
		$permalink = get_permalink( $post_id );
		$value     = [
			'papi_link_test_url'    => rtrim( get_permalink( $post_id ), '/' ),
			'papi_link_test_title'  => get_the_title( $post_id ),
			'papi_link_test_target' => '_blank'
		];

		foreach ( $value as $k => $v ) {
			update_post_meta( $post_id, unpapify( $k ) , $v );
		}

		$output = $this->property->load_value( null, $this->property->slug, $post_id );
		$expected = (object) [
			'url'     => get_permalink( $post_id ),
			'title'   => get_the_title( $post_id ),
			'target'  => '_blank',
			'post_id' => $post_id
		];

		$this->assertEquals( $expected, $output );
	}

	public function test_property_options() {
		$this->assertSame( 'link', $this->property->get_option( 'type' ) );
		$this->assertSame( 'Link test', $this->property->get_option( 'title' ) );
		$this->assertSame( 'papi_link_test', $this->property->get_option( 'slug' ) );
	}

	public function test_render_link_template() {
		$this->property->render_link_template();
		$this->expectOutputRegex( '/.*\S.*/' );
	}

	public function test_property_update_value_empty_value() {
		$post_id = $this->factory->post->create();
		$values = $this->property->update_value( [], $this->property->slug, $post_id );
		$this->assertEmpty( $values['papi_link_test_post_id'] );
		$this->assertEmpty( $values['papi_link_test_url'] );
		$this->assertEmpty( $values['papi_link_test_title'] );
		$this->assertEmpty( $values['papi_link_test_target'] );
	}

	public function test_property_update_value_real_post() {
		$post_id = $this->factory->post->create();
		$values = $this->property->update_value( [], $this->property->slug, $post_id );

		$post_id   = $this->factory->post->create();
		$permalink = get_permalink( $post_id );
		$input     = [
			'url'    => get_permalink( $post_id ),
			'title'  => get_the_title( $post_id ),
			'target' => '_blank'
		];
		$expected  = [
			'papi_link_test_url'     => get_permalink( $post_id ),
			'papi_link_test_title'   => get_the_title( $post_id ),
			'papi_link_test_target'  => '_blank',
			'papi_link_test_post_id' => $post_id,
			'papi_link_test'         => 1,
		];
		$this->assertEquals( $expected, $this->property->update_value( $input, $this->property->slug, 0 ) );
	}
}
