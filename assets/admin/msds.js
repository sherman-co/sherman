(function ($) {
  'use strict';

  var frame;

  function updateDisplay($container, attachment) {
    var metaKey = (window.ShermanCoreMSDS && ShermanCoreMSDS.metaKeyPdfId) ? ShermanCoreMSDS.metaKeyPdfId : '_ps_msds_pdf_id';
    var $input = $container.find('input#' + metaKey);
    var $display = $container.find('.ps-msds-pdf-file-display');
    var $remove = $container.find('.ps-msds-pdf-remove');

    if (attachment) {
      $input.val(attachment.id || '');
      $remove.prop('disabled', false);

      var title = attachment.title || attachment.filename || 'PDF';
      var url = attachment.url || '';

      if (url) {
        $display.html('<a href="' + url + '" target="_blank" rel="noopener noreferrer">' +
          $('<div>').text(title).html() +
          '</a>');
      } else {
        $display.text(title);
      }
    } else {
      $input.val('');
      $remove.prop('disabled', true);
      var noFile = (window.ShermanCoreMSDS && ShermanCoreMSDS.strings && ShermanCoreMSDS.strings.noFile) ? ShermanCoreMSDS.strings.noFile : 'No file selected.';
      $display.html('<span class="description">' + $('<div>').text(noFile).html() + '</span>');
    }
  }

  $(document).on('click', '.ps-msds-pdf-upload', function (e) {
    e.preventDefault();
    var $container = $(this).closest('.form-field');

    if (frame) {
      frame.open();
      frame.off('select');
    }

    var title = (window.ShermanCoreMSDS && ShermanCoreMSDS.strings && ShermanCoreMSDS.strings.selectTitle) ? ShermanCoreMSDS.strings.selectTitle : 'Select MSDS PDF';
    var button = (window.ShermanCoreMSDS && ShermanCoreMSDS.strings && ShermanCoreMSDS.strings.selectButton) ? ShermanCoreMSDS.strings.selectButton : 'Use this file';

    frame = wp.media({
      title: title,
      button: { text: button },
      library: { type: 'application/pdf' },
      multiple: false
    });

    frame.on('select', function () {
      var attachment = frame.state().get('selection').first().toJSON();
      updateDisplay($container, attachment);
    });

    frame.open();
  });

  $(document).on('click', '.ps-msds-pdf-remove', function (e) {
    e.preventDefault();
    var $container = $(this).closest('.form-field');
    updateDisplay($container, null);
  });

})(jQuery);
