<?php

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * Page Type Builder Admin View.
 *
 * @package PageTypeBuilder
 * @version 1.0.0
 */

class PTB_Admin_View {

  /**
   * Path to view dir.
   */

  private $path = '';

  /**
   * Page Type Builder Admin View Constructor.
   *
   * @since 1.0
   */

  public function __construct ($path = '') {
    $this->path = !empty($path) ? $path : PTB_PLUGIN_DIR . 'includes/admin/views/';
  }

  /**
   * Check if file exists.
   *
   * @param string $file
   * @since 1.0
   *
   * @return bool
   */

  public function exists ($file) {
    return file_exists($this->file($file));
  }

  /**
   * Render file.
   *
   * @param string $file
   * @since 1.0
   *
   * @return string|null
   */

  public function render ($file) {
    if (!empty($file) && $this->exists($file)) {
      require_once($this->file($file));
    }

    return null;
  }

  /**
   * Get full path to file with php exstention.
   *
   * @param string $file
   * @since 1.0
   * @access private
   *
   * @return string
   */

  private function file ($file) {
    return $this->path . $file . '.php';
  }

}