import $ from 'jquery';
import Repeater from 'properties/repeater';
import Utils from 'utils';

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
    const $repeater = $this.closest('.papi-property-repeater-top');
    const $tbody = $repeater.find('.repeater-tbody').first();
    const counter = $tbody.children().length;
    const jsonText = this.getJSON($this);
    const layout = $this.data().layout;
    const limit = $repeater.data().limit;
    const append = limit === undefined || limit === -1 || $tbody.find('> tr').length < limit;

    if (!jsonText.length || !append) {
      return;
    }

    let properties = $.parseJSON(jsonText);

    const self = this;
    this.fetch(properties, counter, layout, function (res) {
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
      start: function (e, ui) {
        let editorIds = $.map($(ui.item).find('.wp-editor-area').get(), function(elem) { return elem.id; });
        self.deactivateEditors(editorIds);
      },
      stop: function (e, ui) {
        self.updateRowNumber($(this).closest('.repeater-tbody'));

        let editorIds = $.map($(ui.item).find('.wp-editor-area').get(), function(elem) { return elem.id; });
        self.activateEditors(editorIds);
      }
    });

    $(document).on('click', '.papi-property-flexible > .bottom button[type="button"]', function (e) {
      e.preventDefault();

      const $this = $(this);
      const $prev = $this.prev();
      const offset = $this.closest('.flexible-layouts-btn-wrap').offset().top - $('#wpadminbar').offset().top;

      if ($prev.height() > offset) {
        $prev.removeClass('flexible-layouts-top').addClass('flexible-layouts-bottom');
      } else {
        $prev.removeClass('flexible-layouts-bottom').addClass('flexible-layouts-top');
      }

      $prev.removeClass('flexible-layouts-hidden');
    });

    $(document).on('click', '.papi-property-flexible .flexible-layouts li a', function (e) {
      e.preventDefault();
      $(this).closest('.flexible-layouts').addClass('flexible-layouts-hidden');
      self.add($(this));
    });

    $(document).on('mouseup', 'body', function (e) {
      const $layouts = $('.flexible-layouts:not(.flexible-layouts-hidden)');
      if (!$layouts.is(e.target) && $layouts.has(e.target).length === 0) {
        $layouts.addClass('flexible-layouts-hidden');
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
   * @param {string} flexibleLayout
   * @param {function} callback
   */
  fetch(properties, counter, flexibleLayout, callback) {
    const params = {
      'action': 'get_properties',
      'counter': counter,
      'flexible_layout': flexibleLayout,
      'meta_type': Utils.getMetaType()
    };

    params[Utils.getMetaTypeKey()] = Utils.getMetaTypeValue();

    $.ajax({
      type: 'POST',
      data: {
        properties: JSON.stringify(properties)
      },
      url: papi.ajaxUrl + '?' + $.param(params),
      dataType: 'json'
    }).success(callback);
  }

  /**
   * Remove item from the flexible repeater.
   *
   * @param {object} $this
   */
  remove($this) {
    let $tbody = $this.closest('.papi-property-repeater-top');

    if ($tbody.hasClass('papi-property-flexible')) {
      $tbody = $tbody.find('.repeater-tbody');
      $this.closest('tr').remove();
      this.updateRowNumber($tbody);
    }
  }

  /**
   * Update database row number.
   *
   * @param {object} $tbody
   */
  updateDatabaseRowNumber($tbody) {
    let counter = $tbody.find('tr tbody tr').length;

    $tbody
      .closest('.papi-property-repeater-top')
      .find('.papi-property-repeater-rows')
      .val($tbody.find('tr tbody tr').length);

    this.triggerRule($tbody, counter);
  }
}

export default Flexible;
