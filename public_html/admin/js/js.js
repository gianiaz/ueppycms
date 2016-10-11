$(function() {
  codeMirrorOptions = {};
  codeMirrorOptions.theme = 'dracula';
  codeMirrorOptions.lineNumbers = true;
  codeMirrorOptions.height = '600';
  codeMirrorOptions.mode = 'javascript';
  codeMirrorOptions.extraKeys = {
    "Ctrl-S": function(instance) {
      $('#ajaxForm').submit();
    }
  };
  var cm = CodeMirror.fromTextArea($('#js').get(0), codeMirrorOptions);
  $('.CodeMirror').height(500);

  iframeSrc = $('#preview').attr('src');

  $('#preview').load(function() {
    $("#preview").contents().find('script[href*="general.js"]').remove();
    $("#preview").contents().find('#cntrlpnl').remove();
    $('<script style="text/javascript" href="/public/js/general.js?' + Math.random() + '"></script>').appendTo($("#preview").contents().find('head'));
    iframeSrc = this.contentWindow.location;
  });

  beforeSerialize = function() {
    $('#js').val(cm.getValue());
  }

  afterSubmit = function(ret_data, form) {
    if(parseInt(ret_data.result, 10) == 1) {
      Ueppy.saveSuccess([]);
    } else {
      Utility.alert({'message': ret_data.error});
      $('#preview').attr('src', iframeSrc);
    }
  }


});
