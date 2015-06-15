import $ from 'jquery';
import Repeater from 'papi/properties/repeater';

class Flexible extends Repeater {

  /**
   * The template to use.
   *
   * @var {function}
   */

  get template() {
    return window.wp.template('papi-property-flexible-row');
  }

  /**
   * Initialize Property Flexible.
   */

  static init() {
    new Flexible().binds();
  }

  /**
   * Prepare to add a new row to the repeater
   * and then call fetch to fetch Papi ajax data.
   *
   * @param {object} $this
   */

  add($this) {
    const $repeater      = $this.closest('.papi-property-repeater-top');
    const $tbody         = $repeater.find('.repeater-tbody');
    const counter        = $tbody.children().length;
    const jsonText       = this.getJSON($this);
    const flexibleLayout = $this.data().flexibleLayout;
    const limit          = $repeater.data().flexibleLimit;
    const append         = limit === undefined || limit === -1 || $tbody.find('> tr').length < limit;

    if (!jsonText.length || !append) {
      return;
    }

    let properties = $.parseJSON(jsonText);

    const self = this;
    this.fetch(properties, counter, flexibleLayout, function (res) {
      self.addRow($tbody, counter, res);
    });
  }

  /**
   * Bind elements with functions.
   */

  binds() {
    const self = this;

    $('.repeater-tbody').sortable({
      revert: true,
      handle: '.handle',
      helper: function (e, ui) {
        ui.children().each(function() {
          $(this).width($(this).width());
        });
        return ui;
      },
      stop: function () {
        self.updateRowNumber($(this).closest('.repeater-tbody'));
      }
    });

    $(document).on('click', '.papi-property-flexible .bottom a.button', function (e) {
      e.preventDefault();
      $(this).prev().removeClass('papi-hide');
    });

    $(document).on('click', '.papi-property-flexible .flexible-layouts li', function (e) {
      e.preventDefault();
      $(this).closest('.flexible-layouts').addClass('papi-hide');
      self.add($(this));
    });

    $(document).on('mouseup', 'body', function (e) {
      const $layouts = $('.flexible-layouts:not(.papi-hide)');
      if (!$layouts.is(e.target) && $layouts.has(e.target).length === 0) {
        $layouts.addClass('papi-hide');
      }
    });

    $(document).on('click', '.papi-property-flexible .repeater-remove-item', function (e) {
      e.preventDefault();
      self.remove($(this));
    });
  }

  /**
   * Fetch properties from Papi ajax.
   *
   * @param {array} properties
   * @param {int} counter
   * @param {function} callback
   */

  fetch(properties, counter, flexibleLayout, callback) {
    $.ajax({
      type: 'POST',
      data: {
        properties: JSON.stringify(properties)
      },
      url: papi.ajaxUrl + '?action=get_properties&counter=' + counter + '&flexible_layout=' + flexibleLayout,
      dataType: 'json'
    }).success(callback);
  }

  /**
   * Remove item from the flexible repeater.
   *
   * @param {object} e
   */

  remove($this) {
    const $tbody = $this.closest('.papi-property-flexible').find('.repeater-tbody');
    $this.closest('tr').remove();
    this.updateRowNumber($tbody);
  }

  /**
   * Update database row number.
   *
   * @param {object} $el
   */

  updateDatabaseRowNumber($tbody) {
    $tbody
      .closest('.papi-property-repeater-top')
      .find('.papi-property-repeater-rows')
      .val($tbody.find('tr tbody tr').length);
  }

}

export default Flexible;
