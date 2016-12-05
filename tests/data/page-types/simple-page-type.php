<?php

class Simple_Page_Type extends Papi_Page_Type {

	/**
	 * Define our Page Type meta data.
	 *
	 * @return array
	 */
	public function meta() {
		return [
			'name'                 => 'Simple page',
			'description'          => 'This is a simple page',
			'template'             => 'pages/simple-page.php',
			'post_type'            => [],
			'standard_type'        => true,
			'show_page_attributes' => false,
			'show_permalink'       => false
		];
	}

	/**
	 * Add custom body classes.
	 *
	 * @return array
	 */
	public function body_classes() {
		return ['simple-page-type'];
	}

	/**
	 * Remove post type support and `remove_meta_box`.
	 *
	 * @return array
	 */
	public function remove() {
		return ['editor', 'commentdiv'];
	}

	/**
	 * Define our properties.
	 */
	public function register() {
		// Test box property.
		$this->box( 'Hello', papi_property( [
			'type'  => 'string',
			'title' => 'Name'
		] ) );

		$this->box( 'Hello 2', papi_property( [
			'type'  => 'string',
			'title' => 'Namn'
		] ) );

		$this->box( 'Content', [
			'type'  => 'string',
			'title' => 'Name',
			'slug' => 'name2'
		] );

		$this->box( 'Siffror', [
			papi_property( [
				'type'         => 'number',
				'title'        => 'Siffran',
				'slug'         => 'siffran',
				'before_html'  => 'En siffra',
				'before_class' => 'en-siffra',
				'after_html'   => 'Två siffra',
				'after_class'  => 'tva-siffra'
			] )
		] );

		$this->box( 'Number', papi_property( [
			'type'  => 'number',
			'title' => 'Number',
			'slug'  => 'number'
		] ) );

		// Will not work.
		$this->box( 1 );

		// Box without a empty title.
		$this->box( ['title' => ''], [
			papi_property( [
				'type'  => 'string',
				'title' => 'Country'
			] )
		] );

		// Test properties from another method.
		$this->box( [
			'title'      => 'Content',
			'sort_order' => 100
		], [ $this, 'content_box' ] );

		// Load box from a template file.
		$this->box(
			$this->template(
				dirname( __DIR__ ) . '/page-types/boxes/simple.php'
			)
		);

		$this->box( 'Children', [
			papi_property( [
				'type'     => 'string',
				'title'    => 'Name',
				'slug'     => 'name_levels',
				'settings' => [
					'items' => [
						papi_property( [
							'type'     => 'string',
							'title'	   => 'Child name',
							'slug'     => 'child_name',
							'settings' => [
								'items' => [
									papi_property( [
										'type'  => 'string',
										'title' => 'Child child name',
										'slug'  => 'child_child_name'
									] ),
									null
								]
							]
						] )
					]
				]
			] )
		] );

		$this->box( 'Children 2', [
			papi_property( [
				'type'     => 'string',
				'title'    => 'Name',
				'slug'     => 'name_levels_2',
				'settings' => [
					'items' => [
						papi_property( [
							'type'  => 'string',
							'title'	=> 'Child name 2',
							'slug'  => 'child_name_2'
						] )
					]
				]
			] )
		] );

		$this->box( 'Content box broken', [ [ $this, 'content_box_broken' ] ] );

		// Test default value.
		$this->box( 'Hello', papi_property( [
			'slug'  => 'name_default',
			'type'  => 'string',
			'title' => 'Name',
			'value' => 'Fredrik'
		] ) );

		$this->box( 'Sections', [
			papi_property( [
				'type'     => 'repeater',
				'title'    => 'Sections',
				'slug'     => 'sections',
				'sidebar'  => false,
				'settings' => [
					'items' => papi_property( [
						'type'  => 'string',
						'title' => 'Title',
						'slug'  => 'title'
					] )
				]
			] )
		] );

		$this->box( 'String test', [
			papi_property( [
				'type'  => 'string',
				'title' => 'String test',
				'slug'  => 'string_test'
			] )
		] );

		$this->box( 'UPPERCASE TEST', [
			papi_property( [
				'type'  => 'string',
				'title' => 'UPPERCASE',
				'slug'  => 'UPPERCASE'
			] )
		] );
	}

	public function content_box() {
		return [ $this->property( [
			'type'  => 'string',
			'title' => 'Name'
		] ) ];
	}

	public function content_box_broken() {
		return;
	}
}
