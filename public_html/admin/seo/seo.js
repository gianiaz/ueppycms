/*****************/
/***ueppy3.1.00***/
/*****************/
/**  CHANGELOG  **/
/**************************************************************************************************/
/** v.3.1.01 (01/04/2014)                                                                        **/
/** - Aggiustamenti pignoli nel codice                                                           **/
/**                                                                                              **/
/** v.3.1.00 (01/01/2013)                                                                        **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
var focusAttuale = false;
var module_name = 'seo';
$(function() {


  $('#insert_form input, #insert_form textarea, #insert_form input:radio, #insert_form input:checkbox, #insert_form select').unbind();

  if($('#lingue > option').length == 1) {
    $('#lingue').closest('label').hide();
  }

  $('#lingue').change(function(e) {
    loadData();
  });

  loadData();

  $('#dynamic').on('focus', ":input", function() {
    $(':input').removeClass('active');
    focusAttuale = this.id;
    //Utility.log(focusAttuale);
    $(this).addClass('active');
  });

  fixedHeader();
  $(document).on('change', ':input', function(e) {
    somethingUnsaved();
  });

  afterSubmit = function(ret_data, $form) {
    if(parseInt(ret_data.result, 10) == 1) {
      PNotify.removeAll();

      new PNotify({
        title   : Utility.getLang('base', 'SALVATAGGIO_AVVENUTO'),
        text    : '',
        type    : 'success',
        nonblock: {
          nonblock        : true,
          nonblock_opacity: .2
        }
      });
    } else {
      Ueppy.showFormErrors(ret_data.errors, ret_data.wrongs);
    }

  }


});

function loadData() {
  var lingua = $('#lingue').val();
  var data = {'lang': lingua};
  $.ajax({
    type    : "POST",
    url     : main_host + 'admin/' + module_name + '/load_meta/',
    data    : data,
    dataType: "json",
    success : showForm
  });
}


function showForm(ret_data) {
  if(parseInt(ret_data.result, 10) == 1) {

    $('#dynamic').empty();

    Utility.log(ret_data.data);

    for(var i in ret_data.data) {
      var sezione = ret_data.data[i];
      Utility.log(sezione);

      var content = '';

      for(var c in sezione.casi) {
        var caso = sezione.casi[c];
        var data = {
          vars             : caso.vars,
          title            : caso.label,
          sezione          : i,
          act              : c,
          DESCRIPTION_LABEL: Utility.getLang(module_name, 'DESCRIPTION_LABEL'),
          DESCRIPTION_HELP : Utility.getLang(module_name, 'DESCRIPTION_HELP'),
          HTMLTITLE_LABEL  : Utility.getLang(module_name, 'HTMLTITLE_LABEL'),
          HTMLTITLE_HELP   : Utility.getLang(module_name, 'HTMLTITLE_HELP'),
          htmltitle        : caso.htmltitle,
          description      : caso.description
        };

        Utility.log(data);

        var markup = Utility.template({
          'name' : 'meta',
          'jqObj': $('#meta-template'),
          'data' : data
        });

        content += markup;
      }
      var panel = Utility.template({
        'name': 'grp', jqObj: $('#grp-template'),
        data  : {
          'title': sezione.title,
          content: content
        }
      });
      $(panel).appendTo($('#dynamic'));
    }

    $('#dynamic').find('.help').tooltip();

    $('#dynamic').find('.cmsHelp').tooltip();

    /*

    log(ret_data.data);
    $('.dynamic_data').empty();
    for(var k in ret_data.data) {

      gruppo = ret_data.data[k];

      gruppoHTML = '<fieldset><legend>' + k + '</legend>';

      for(var i = 0; i < gruppo.length; i++) {

        meta = gruppo[i];

        compiled = Handlebars.compile($('#meta-template').html());

        wildcards = [];
        for(x = 0; x < meta.specials.length; x++) {
          wildcards.push('<a class="wildCard" href="{' + meta.specials[x] + '}">' + meta.specials[x] + '</a>');
        }

        meta.wildcards = wildcards.join('');
        meta.oddClass = '';
        if(i % 2) {
          meta.oddClass = ' dispari';
        }

        gruppoHTML += compiled(meta);

      }
      gruppoHTML += '</fieldset>';

      $('.dynamic_data').append(gruppoHTML);

    }

    $('input[type=text], input[type=password]').css({
      'border': '1px solid #cecece',
      'width' : '300px'
    });

    /**
     * Gestione degli helptip.
     */

    $('.help').click(function(e) {
      e.preventDefault();
      if(focusAttuale === false || $(this).closest('.case').find('#' + focusAttuale).length === 0) {
        $(this).closest('.case').find('input:first').focus();
      }
      if($(this).closest('.case').find('#' + focusAttuale).length > 0) {
        var pos = $('#' + focusAttuale).caret();
        var val = $('#' + focusAttuale).val();
        var newVal = val.substring(0, pos) + '{' + $(this).attr('href') + '}' + val.substring(pos);
        $('#' + focusAttuale).val(newVal);
      }
      $('#' + focusAttuale).focus();
    });

  } else {
    modal_dialog(Utility.getLang('base', 'WARNING'), ret_data.error);
  }
}

function savedFunction(ret_data) {
  unlockWait();
  if(parseInt(ret_data.result, 10) != 1) {
    modal_dialog(Utility.getLang('base', 'WARNING'), ret_data.error);
  }
}