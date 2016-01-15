<?php

/**
 * Plugin Name: Papi
 * Description: Page Type API with custom fields
 * Author: Fredrik Forsmo
 * Author URI: https://frozzare.com
 * Version: 3.0.0-beta1
 * Plugin URI: https://wp-papi.github.io
 * Textdomain: papi
 * Domain Path: /languages/
 */

// Load Composer autoload if it exists.
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

// Load Papi loader.
require_once __DIR__ . '/src/papi-loader.php';
