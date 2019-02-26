import $ from 'jquery';

/**
 * Property Dropdown.
 *
 * Using Select2.
 */
class Dropdown {
  /**
   * Initialize Property Color.
   */
  static init () {
    new Dropdown().binds();
  }

  /**
   * Bind elements with functions.
   */
  binds () {
    $(document).on('papi/property/repeater/added', '[data-property="dropdown"]', this.update.bind(this));
  }

  /**
   * Update select if isn't a select2.
   */
  update (e) {
    e.preventDefault();

    const $select = $(e.currentTarget).parent().find('select');

    if ($select.hasClass('papi-component-select2') && 'select2' in $.fn) {
      $select.select2();
    }
  }
}

export default Dropdown;
