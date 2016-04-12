<?php

/**
 * Post page implementation of Papi page.
 */
class Papi_Post_Page extends Papi_Core_Page {

	/**
	 * The page type.
	 *
	 * @var string
	 */
	const TYPE = 'page';

	/**
	 * The WordPress post.
	 *
	 * @var object
	 */
	private $post;

	/**
	 * The Page type.
	 *
	 * @var Papi_Page_Type
	 */
	private $page_type;

	/**
	 * The constructor.
	 *
	 * Create a new instance of the class.
	 *
	 * @param int $post_id
	 */
	public function __construct( $post_id = 0 ) {
		if ( $post_id === 0 ) {
			$this->id = papi_get_post_id();
		} else {
			$this->id = intval( $post_id );
		}

		$this->post      = get_post( $this->id );
		$id              = papi_get_page_type_id( $this->id );
		$this->page_type = papi_get_page_type_by_id( $id );
	}

	/**
	 * Get the page type object of the page.
	 *
	 * @return Papi_Page_Type
	 */
	public function get_page_type() {
		return $this->page_type;
	}

	/**
	 * Get the permalink for the page.
	 *
	 * @return string
	 */
	public function get_permalink() {
		return get_permalink( $this->id );
	}

	/**
	 * Get the WordPress post object.
	 *
	 * @return WP_Post
	 */
	public function get_post() {
		return $this->post;
	}

	/**
	 * Get the post status of a page.
	 *
	 * @return string
	 */
	public function get_status() {
		return get_post_status( $this->id );
	}

	/**
	 * Load property from page type.
	 *
	 * @param  string $slug
	 * @param  string $child_slug
	 *
	 * @return object
	 */
	public function get_property( $slug, $child_slug = '' ) {
		$page_type_id = papi_get_page_type_id( $this->id );
		$page_type    = papi_get_page_type_by_id( $page_type_id );

		if ( $page_type instanceof Papi_Page_Type === false ) {
			return;
		}

		return $page_type->get_property( $slug, $child_slug );
	}

	/**
	 * Prepare convert value.
	 *
	 * @param  Papi_Core_Property $property
	 * @param  mixed              $value
	 *
	 * @retrun mixed
	 */
	protected function prepare_convert_value( Papi_Core_Property $property, $value ) {
		if ( $property->overwrite ) {
			// Clear post cache to solve issue with cached post objects
			// when selecting post field.
			clean_post_cache( $this->id );

			$slug    = $property->get_slug( true );
			$context = is_admin() ? 'edit' : 'display';
			$value   = get_post_field( $slug, $this->id, $context );
		}

		return $value;
	}

	/**
	 * Check if the page has the post object and that it's not null.
	 *
	 * @return bool
	 */
	public function valid() {
		return ! is_null( $this->post );
	}
}
