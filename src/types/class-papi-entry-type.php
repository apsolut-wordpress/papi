<?php

/**
 * Papi Entry Type is a base class that can
 * register boxes with properties.
 */
class Papi_Entry_Type extends Papi_Core_Type {

	/**
	 * The array of meta boxes to register.
	 *
	 * @var array
	 */
	protected $boxes = [];

	/**
	 * Load all boxes.
	 *
	 * @var bool
	 */
	protected $load_boxes = false;

	/**
	 * Show help tabs.
	 *
	 * @return bool
	 */
	public $show_help_tabs = true;

	/**
	 * Show screen options.
	 *
	 * @var bool
	 */
	public $show_screen_options = true;

	/**
	 * The type name.
	 *
	 * @var string
	 */
	public $type = 'entry';

	/**
	 * The constructor.
	 *
	 * Load a entry type by the file.
	 *
	 * @param string $file_path
	 */
	public function __construct( $file_path = '' ) {
		// Try to load the file if the file path is empty.
		if ( empty( $file_path ) ) {
			$page_type = papi_get_entry_type_id();
			$file_path = papi_get_file_path( $page_type );
		}

		parent::__construct( $file_path );
	}

	/**
	 * Add help tabs.
	 */
	public function add_help_tabs() {
		$help   = $this->help();
		$screen = get_current_screen();

		// No screen available.
		if ( $screen instanceof WP_Screen === false ) {
			return;
		}

		// Clean up all existing tabs.
		if ( ! $this->show_help_tabs || ! empty( $help ) ) {
			$screen->remove_help_tabs();
		}

		// No new help tabs available.
		if ( empty( $help ) ) {
			return;
		}

		// Add help sidebar content. By default it will be disabled
		// since `help_sidebar` method returns false.
		$help_sidebar = $this->help_sidebar();
		$help_sidebar = papi_maybe_get_callable_value( $help_sidebar );
		$screen->set_help_sidebar( wpautop( $help_sidebar ) );

		foreach ( $help as $key => $value ) {
			$args = [
				'id'    => papi_html_name( $key ),
				'title' => $key
			];

			if ( is_callable( $value ) ) {
				$args['callback'] = function () use ( $value ) {
					return wpautop( $value() );
				};
			} else {
				$args['content'] = wpautop( $value );
			}

			$screen->add_help_tab( $args );
		}
	}

	/**
	 * Get custom body classes.
	 *
	 * @return array
	 */
	public function body_classes() {
		return [];
	}

	/**
	 * Add new meta box with properties.
	 *
	 * @param mixed $file_or_options
	 * @param array $properties
	 */
	protected function box( $file_or_options = [], $properties = [] ) {
		if ( ! is_string( $file_or_options ) && ! is_array( $file_or_options ) && ! is_object( $file_or_options ) ) {
			return;
		}

		list( $options, $properties ) = papi_get_options_and_properties(
			$file_or_options,
			$properties,
			true
		);

		// Check so we have a post the to add the box to.
		// @codeCoverageIgnoreStart
		if ( ! $this->load_boxes ) {
			return;
		}
		// @codeCoverageIgnoreEnd

		if ( is_callable( $properties ) ) {
			$properties = call_user_func( $properties );
		}

		// Check and convert all non properties objects to properties objects.
		$properties = $this->convert_properties( $properties );

		// Create a core box instance and add it to the boxes array.
		array_push( $this->boxes, new Papi_Core_Box( $options, $properties ) );
	}

	/**
	 * Call parent register if it exists
	 * to collect boxes on the parent entry type.
	 */
	private function call_parent_register() {
		$parent_class = get_parent_class( $this );

		if ( ! method_exists( $parent_class, 'register' ) ) {
			return;
		}

		$rc = new ReflectionClass( $parent_class );

		// Bail if not instantiable.
		if ( ! $rc->isinstantiable() ) {
			return;
		}

		$parent = $rc->newInstance();
		$parent->register();
		$this->boxes = $parent->get_boxes();
	}

	/**
	 * Convert properties to properties objects.
	 *
	 * @param  array|object $properties
	 *
	 * @return array
	 */
	private function convert_properties( $properties ) {
		if ( is_array( $properties ) ) {
			if ( isset( $properties['type'] ) ) {
				$properties = [$properties];
			} else if ( isset( $properties[0] ) && $properties[0] instanceof Papi_Core_Tab ) {
				foreach ( $properties as $items ) {
					$items->properties = array_map( 'papi_get_property_type', $items->properties );
				}

				return $properties;
			}
		}

		if ( is_object( $properties ) ) {
			$properties = papi_get_property_type( $properties );
		}

		if ( papi_is_property( $properties ) ) {
			$properties = [$properties];
		}

		$properties = is_array( $properties ) ? $properties : [];
		$properties = array_map( 'papi_get_property_type', $properties );

		return array_filter( $properties, 'papi_is_property' );
	}

	/**
	 * Get body css classes.
	 *
	 * Use `body_classes` method to add custom body classes.
	 *
	 * @return array
	 */
	public function get_body_classes() {
		$arr = $this->body_classes();
		$arr = is_string( $arr ) ? [$arr] : $arr;
		$arr = is_array( $arr ) ? $arr : [];

		return array_merge( $arr, [] );
	}

	/**
	 * Get boxes from the page type.
	 *
	 * @return array
	 */
	public function get_boxes() {
		if ( empty( $this->boxes ) && $this->load_boxes === false ) {
			if ( ! method_exists( $this, 'register' ) ) {
				return [];
			}

			$this->load_boxes = true;

			$this->call_parent_register();
			$this->register();
		}

		$this->boxes = $this->merge_boxes( $this->boxes );
		$this->boxes = papi_sort_order( array_reverse( $this->boxes ) );

		return $this->boxes;
	}

	/**
	 * Get labels that should be changed
	 * when using `fill_labels` option.
	 *
	 * @return array
	 */
	public function get_labels() {
		return [];
	}

	/**
	 * Get property from page type.
	 *
	 * @param  string $slug
	 * @param  string $child_slug
	 *
	 * @return null|Papi_Property
	 */
	public function get_property( $slug, $child_slug = '' ) {
		$boxes = $this->get_boxes( true );
		$parts = preg_split( '/\[\d+\]/', $slug );
		$parts = array_map( function ( $part ) {
			return preg_replace( '/(\[|\])/', '', $part );
		}, $parts );

		if ( count( $parts ) > 1 ) {
			$property = null;

			for ( $i = 0, $l = count( $parts ); $i < $l; $i++ ) {
				$child    = isset( $parts[$i + 1] ) ? $parts[$i + 1] : '';
				$property = $this->get_property( $parts[$i], $child );

				if ( isset( $parts[$i + 1] ) ) {
					$i++;
				}
			}

			return $property;
		}

		if ( empty( $boxes ) ) {
			return;
		}

		foreach ( $boxes as $box ) {
			foreach ( $box->properties as $property ) {
				$property = papi_get_property_type( $property );

				if ( papi_is_property( $property ) && $property->match_slug( $slug ) ) {
					if ( empty( $child_slug ) ) {
						return $property;
					}

					$property = $property->get_child_property( $child_slug );

					if ( papi_is_property( $property ) ) {
						return $property;
					}
				}
			}
		}
	}

	/**
	 * Add admin help tabs.
	 *
	 * Example:
	 *   'My custom title' => 'My custom content'
	 *
	 * @return array
	 */
	public function help() {
		return [];
	}

	/**
	 * Add help sidebar content.
	 *
	 * By default we return false to disable
	 * the sidebar content.
	 *
	 * @return bool
	 */
	public function help_sidebar() {
		return false;
	}

	/**
	 * Merge boxes with same title.
	 *
	 * @param  array $boxes
	 *
	 * @return array
	 */
	protected function merge_boxes( array $boxes ) {
		$result = [];

		foreach ( $boxes as $box ) {
			if ( ! isset( $result[$box->id] ) ) {
				$result[$box->id] = $box;
				continue;
			}

			foreach ( $box->properties as $property ) {
				$result[$box->id]->properties[] = $property;
			}

			$result[$box->id]->properties = array_unique( $result[$box->id]->properties );
		}

		return array_values( $result );
	}

	/**
	 * Add new property to the page using array or rendering property template file.
	 *
	 * @param  array|string $file_or_options
	 * @param  array $values
	 *
	 * @return null|Papi_Property
	 */
	protected function property( $file_or_options = [], $values = [] ) {
		return papi_property( $file_or_options, $values );
	}

	/**
	 * Check if the entry type is a singleton.
	 *
	 * @return bool
	 */
	public function singleton() {
		return false;
	}

	/**
	 * Setup entry type.
	 */
	public function setup() {
		add_action( 'in_admin_header', [$this, 'add_help_tabs'] );
		add_filter( 'screen_options_show_screen', function () {
			return $this->show_screen_options;
		} );

		// @codeCoverageIgnoreStart
		if ( ! method_exists( $this, 'register' ) ) {
			return;
		}
		// @codeCoverageIgnoreEnd

		$this->register();

		foreach ( $this->get_boxes() as $box ) {
			new Papi_Admin_Meta_Box( $box );
		}
	}

	/**
	 * Add a new tab.
	 *
	 * @param  mixed $file_or_options
	 * @param  array $properties
	 *
	 * @return null|Papi_Core_Tab
	 */
	protected function tab( $file_or_options = [], $properties = [] ) {
		if ( ! is_string( $file_or_options ) && ! is_array( $file_or_options ) ) {
			return;
		}

		return papi_tab( $file_or_options, $properties );
	}

	/**
	 * Load template file.
	 *
	 * @param  string $file
	 * @param  array  $values
	 *
	 * @return array
	 */
	protected function template( $file, $values = [] ) {
		return papi_template( $file, $values );
	}
}
