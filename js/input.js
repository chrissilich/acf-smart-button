(function($) {

  function initialize_field($el) {

    // define vars
    var $checkbox = $el.find('.button-link-switch-checkbox'),
      $internal = $el.find('.internal'),
      $external = $el.find('.external'),
      $switcherLabels = $el.find('.switcher-container label'),
      $internalSelect = $internal.find('select');

    // listen to checkbox change
    $checkbox.change(function() {
      helperCheckboxChange($checkbox, $internal, $external);
    });

		// toggle on label clicks
    $switcherLabels.on('click', function() {
      helperCheckboxChange($checkbox, $internal, $external);
    });

    // trigger change function on init to respect current state (do not trigger change event as this provokes browser alert on window close)
    helperCheckboxChange($checkbox, $internal, $external);

    // Initialize select2 on the manually created select field for internal links
    intitializeInternalSelect($internalSelect);
  }

  function intitializeInternalSelect($internalSelect) {

    const queryString = $internalSelect.data('querystring')
    const API_URL = '/wp-json/acf-smart-button/v1/posts/?'
    const wp_nonce = $internalSelect.data('nonce')

    $internalSelect.select2({
      ajax: {
        url: API_URL + queryString,
        dataType: 'json',
        headers: {
          'X-WP-Nonce': wp_nonce
        },
        processResults: function (data) {
          return {
            results: data.map(function (item) {
              console.log(item.title);
              return {
                id: item.id,
                text: $('<span>'+item.title+'</span>').text(),
              }
            }
          )};
        }
      }
    });
  }

  function helperCheckboxChange($self, $internal, $external) {
    if ($self.prop('checked')) {
      $internal.hide();
      $external.show().find('input').show();
    } else {
      $internal.show();
      $external.hide();
    }
  }

  if (typeof acf.add_action !== 'undefined') {

    /*
     *  ready append (ACF5)
     *
     *  These are 2 events which are fired during the page load
     *  ready = on page load similar to $(document).ready()
     *  append = on new DOM elements appended via repeater field
     *
     *  @type	event
     *  @date	20/07/13
     *
     *  @param	$el (jQuery selection) the jQuery element which contains the ACF fields
     *  @return	n/a
     */
    acf.add_action('ready append', function($el) {

      // search $el for fields of type 'button'
      acf.get_fields({
        type: 'smart_button'
      }, $el).each(function() {
        initialize_field($(this));
      });

    });

  } else {

    /*
     *  acf/setup_fields (ACF4)
     *
     *  This event is triggered when ACF adds any new elements to the DOM.
     *
     *  @type	function
     *  @since	1.0.0
     *  @date	01/01/12
     *
     *  @param	event		e: an event object. This can be ignored
     *  @param	Element		postbox: An element which contains the new HTML
     *
     *  @return	n/a
     */
    $(document).on('acf/setup_fields', function(e, postbox) {
      $(postbox).find('.field[data-field_type="smart_button"]').each(function() {
        initialize_field($(this));
      });
    });

  }

})(jQuery);
