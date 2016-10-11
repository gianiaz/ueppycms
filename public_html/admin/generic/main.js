/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (06/07/16, 17.12)                                                                      **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/

/**
 * Se impostato a true cerca di fare un output nella console
 */
var debug = true;
var main_host = '';
var rel_root = '/';
var unsaved = false;
var lingue;
var sigleLingua = [];
var timeOutRicerca = false;
var beforeSubmit = false;
var afterSubmit = false;
var beforeSerialize = false;
var SUPERADMIN = false;
var LEVEL = false;
var GOD = false;
var $dt;
var ACTUAL_LANGUAGE = 'it';
var cols = []; // colonne datatables
var sogliaAllegati = 0;
$(function() {

    var $body = $('body');
    main_host = location.protocol + '//' + location.hostname + rel_root;

    Utility.lockScreenPre = function() {
      $('.page-header').css('position', 'relative');
    };

    if($body.data('superadmin') == 1) {
      SUPERADMIN = true;
    }

    LEVEL = $body.data('level');

    if($body.data('god') == 1) {
      GOD = true;
    }

    PNotify.prototype.options.styling = "fontawesome";

    $('.cmsHelp').tooltip();

    // il bottone di ricerca che si trasforma in croce per svuotare l'input di ricerca
    // e riportare il menu allo stato iniziale di default
    $(document).on('click', '#srcMenu', function() {
      if($(this).find('.fa-close').length) {
        $('.sidebar-search input').val('');
        setUpMenu();
      }
    });

    // ascolto dell'evento keyup per l'input di ricerca e scaturisco la ricerca
    // quando l'utente smette di digitare per più di 300ms
    $(document).on('keyup', '.sidebar-search input', function() {

      clearTimeout(timeOutRicerca);

      timeOutRicerca = setTimeout(function() {
        setUpMenu();
      }, 300);

    });

    // sposto l'header in caso di scrolling
    if(Utility.getUrlParam(1, rel_root) == 'new') {
      fixedHeader();
      $(document).on('change', ':input', function(e) {
        somethingUnsaved();
      });

    }

    $('#ajaxForm').ajaxForm({
      delegation     : true,
      'dataType'     : 'json',
      beforeSerialize: function() {
        if(beforeSerialize !== false && typeof beforeSerialize == 'function') {
          return beforeSerialize();
        }
      },
      beforeSubmit   : function() {
        Utility.lockScreen({'message': Utility.getLang('base', 'SALVATAGGIO_IN_CORSO')})
        if(beforeSubmit !== false && typeof beforeSubmit == 'function') {
          return beforeSubmit();
        }
        return true;
      },
      'success'      : function(ret_data, statusText, xhr, $form) {
        Utility.unlockScreen();
        if(afterSubmit !== false && typeof afterSubmit == 'function') {
          if(ret_data.result == 1) {
            PNotify.removeAll();
            unsaved = false;
          }
          afterSubmit(ret_data, $(this));
          if($form.data('aftersubmit') == 'new') {
            var url = location.protocol + '//' + location.host + '/admin/' + Utility.getUrlParam(0, rel_root) + '/new/';
            location.href = url;
          }

          if($form.data('aftersubmit') == 'close') {
            var url = location.protocol + '//' + location.host + '/admin/' + Utility.getUrlParam(0, rel_root);
            location.href = url;
          }

        } else {
          if(parseInt(ret_data.result, 10) != 1) {
            Utility.alert({'message': ret_data.error});
          }
        }
      }
    });


    $('#dataTable').on('init.dt', function(e, settings) {


      var api = new $.fn.dataTable.Api(settings);
      var state = api.state.loaded();

      $('<tr class="searchbar"></tr>').appendTo($('#dataTable thead'));
      var idx = 0;

      $('#dataTable thead tr:first th').each(function() {
        var title = $.trim($('#dataTable thead tr:first th').eq($(this).index()).text());

        if(typeof cols[idx]['checkbox'] !== 'undefined') {
          $('<th class="dt-head-center"><input type="checkbox" class="selAll" /></th>').appendTo($('.searchbar'));
        } else {
          if(cols[idx]['searchable']) {
            var searchedBefore = '';
            if(state && typeof state['columns'][idx]['search']['search'] !== 'undefined') {
              searchedBefore = state.columns[idx]['search']['search'];
            }
            $('<th><input class="form-control input-sm" type="text" placeholder="' + title + '..." value="' + searchedBefore + '" /></th>').appendTo($('.searchbar'));
          } else {
            $('<th>&nbsp;</th>').appendTo($('.searchbar'));
          }
        }
        idx++;
      });

      $(document).on('click', '.disabled', function(e) {
        e.preventDefault();
        alert('none');
      });

      $(document).on('click', '.selRow', function(e) {
        salvaSelezione();
      });

      $('.searchbar').find('.selAll').on('click', function(e) {
        if(this.checked) {
          $('#dataTable tbody input[type="checkbox"]:not(:checked)').attr('checked', true);
        } else {
          $('#dataTable tbody input[type="checkbox"]:checked').removeAttr('checked');
        }
        salvaSelezione();
      });

      $('.searchbar').find('input').on('keyup change', function() {
        var index = $('.searchbar').find('th').index($(this).closest('th'));
        if(typeof(cols[index]['checkbox']) == 'undefined') {
          var that = $dt.columns(index);
          if(that.search() !== this.value) {
            that
              .search(this.value)
              .draw();
          }
        }
      });

    });

    $('#dataTable').on('draw.dt', function() {
      $('#dataTable').find('.edit').addClass('btn-primary');
      $('#dataTable').find('.fdel').addClass('btn-danger');
      $('#dataTable').find('.delete').addClass('btn-danger');
      $('#dataTable').find('.visibility').addClass('btn-warning');
    });

    $(document).on('click', '.upybtn', function(e) {
      e.preventDefault();
      e.stopPropagation();

      var role = $(this).data('role');

      switch(role) {
        case 'submit':

          if($('#ajaxForm').length) {
            $('#ajaxForm').submit();
          } else {
            alert('Form ajax non presente');
          }
          break;

        case 'save-new':
          if($('#ajaxForm').length) {
            $('#ajaxForm').data('aftersubmit', 'new');
            $('#ajaxForm').submit();
          } else {
            alert('Form ajax non presente');
          }
          break;

        case 'submit-close':
          if($('#ajaxForm').length) {
            $('#ajaxForm').data('aftersubmit', 'close');
            $('#ajaxForm').submit();
          } else {
            alert('Form ajax non presente');
          }
          break;

        case 'new':
          var url = location.protocol + '//' + location.host + '/admin/' + Utility.getUrlParam(0, rel_root) + '/new/';
          location.href = url;
          break;

        case 'close':
          var url = location.protocol + '//' + location.host + '/admin/' + Utility.getUrlParam(0, rel_root) + '/';
          location.href = url;
          break;

        case 'sort':
          var url = location.protocol + '//' + location.host + '/admin/' + Utility.getUrlParam(0, rel_root) + '/sort/';
          location.href = url;
          break;

        case 'demo':
          Ueppy.startTour();
          break;

        case 'seo':
          var url = location.protocol + '//' + location.host + '/admin/' + Utility.getUrlParam(0, rel_root) + '/seo/';
          location.href = url;
          break;

        default:
          alert('role:' + role + '(' + url + ')');
          break;
      }

    });

    $(document).on('click', '#about', function(e) {
      e.preventDefault();
      Utility.template({
        name    : 'about',
        path    : '/admin/generic/snippets/',
        callback: function(markup) {
          $(markup).appendTo('body');
          $('.modal').modal();
        },
        data    : {}
      });
    });

// azioni di default per il bottone di modifica delle tabelle
    $(document).on('click', '.edit', function(e) {
      var id = $(this).data('id');
      var data = {
        data    : {'id': id},
        'action': main_host + 'admin/' + module_name + '/new/'
      };
      Utility.postData(data);
    });

    // azioni di default per il bottone di cambio visibilita delle tabelle
    $('.dataTable_wrapper').on('click', '.visibility', function(e) {
      e.preventDefault();
      var id = $(this).data('id');
      var data = {id: id};
      $.ajax({
        type    : "POST",
        url     : main_host + 'admin/' + module_name + '/switchvisibility/',
        data    : data,
        dataType: "json",
        success : function(ret_data) {
          if(parseInt(ret_data.result, 10) == 1) {
            $dt.ajax.reload(null, false);
          } else {
            Utility.alert({message: ret_data.error});
          }
        }
      });
    });

// azione di default per il bottone di cancellazione nelle tabelle
    $(document).on('click', '.delete', function(e) {
      e.preventDefault();
      var id = $(this).data('id');
      var title = $(this).data('title');
      Utility.confirm({
        'message': sprintf(Utility.getLang('base', 'VUOI_RIMUOVERE_LA_VOCE'), title),
        'onOk'   : function() {

          var data = {id: id};
          $.ajax({
            type    : "POST",
            url     : main_host + 'admin/' + module_name + '/del/',
            data    : data,
            dataType: "json",
            success : function(ret_data) {
              if(parseInt(ret_data.result, 10) == 1) {
                PNotify.removeAll();
                new PNotify({
                  title: Utility.getLang('base', 'ELEMENTO_CANCELLATO'),
                  text : '',
                  type : 'success'
                });
                $dt.ajax.reload(null, false);
              } else {
                Utility.alert({message: ret_data.error});
              }
            }
          });
        }
      });
    });

    /** Prelevo le lingue disponibili dal campo hidden inserito nell'header della pagina. **/
    if($('#lingue_json').size() > 0) {
      lingue = eval("(" + $('#lingue_json')[0].value + ")");

      for(i in lingue) {
        sigleLingua.push(i);

      }
    }

    /**
     * Prelevo il percorso di base da utilizzare come riferimento per il caricamento
     * delle immagini e per avere l'url completo per le richieste ajax.
     */
    if(lvl) {

      Ueppy.tinyMCESetOpts({
        'document_base_url': main_host,
        'relative_urls'    : false,
        'convert_urls'     : true,
        'content_css'      : main_host + 'admin/generic/tinymce.css'
      });
    }

    $.ajaxSetup({
      type : "POST",
      error: function(x, e) {
        Utility.unlockScreen();
        if(x.status == 404) {
          Utility.alert({message: (Utility.getLang('base', 'WARNING'), 'Url non trovato')});
        } else
          if(x.status == 500) {
            Utility.alert({message: (Utility.getLang('base', 'WARNING'), 'Errore interno del server')});
          } else
            if(e == 'parsererror') {
              Utility.alert({message: Utility.getLang('base', 'JSON_ERROR') + "\n" + x.responseText});
            } else
              if(e == 'timeout') {
                Utility.alert({message: (Utility.getLang('base', 'WARNING'), 'Richiesta andata in timeout')});
              } else {
                Utility.alert({message: (Utility.getLang('base', 'WARNING'), 'Errore sconosciuto')});
              }
      }
    });

    /** INIT - FINE **/

    /** EVENTI - INIZIO **/

    // avviso l'utente che ci sono modifiche non salvate, solo per i form con id insert_form
    $(document).on('keyup', '#insert_form input:not(.nounsave), #insert_form textarea:not(.nounsave)', function(e) {
      infoMsg(Utility.getLang('base', 'MODIFICHE_NON_SALVATE'), 'ui-icon-alert');
      unsaved = true;
    });
    $(document).on('click', '#insert_form input:radio:not(.nounsave), #insert_form input:checkbox:not(.nounsave)', function(e) {
      infoMsg(Utility.getLang('base', 'MODIFICHE_NON_SALVATE'), 'ui-icon-alert');
      unsaved = true;
    });

    $(document).on('change', '#insert_form select:not(.nounsave)', function(e) {
      infoMsg(Utility.getLang('base', 'MODIFICHE_NON_SALVATE'), 'ui-icon-alert');
      unsaved = true;
    });

// Intercetto tutti i campi per cui voglio restringere l'input.
    $(document).on('keypress', '[data-filter]', function(e) {
      return Utility.restricted(e, $(this).data('filter'));
    });

    $(document).on('click', '.toMce', function(e) {
      e.preventDefault();
      var idTextArea = this.id.replace('toMce', '');
      var cm = $(this).data('cm');
      cm.toTextArea();
      Ueppy.tinyMCESetOpts({'selector': '#' + idTextArea});
      Ueppy.tinyMCEInit();
    });

    /** EVENTI - FINE **/

    /** COMPONENTE FILE ALLEGATI **/
    $(document).on('change', '.btn-file :file', function() {
      var $panel = $(this).closest('.panel-attachment');
      var $input = $(this);
      var files = $input.get(0).files;
      var $fileFeedback = $panel.find('.fileFeedback');
      var $action = $panel.find('.action');
      var $img = $panel.find('img');
      var $preview = $panel.find('.preview');

      if(files.length) {
        var file = files[0];
        $fileFeedback.val(file.name);
        $action.val('replace');
        // FileReader support
        if(FileReader && $img.length) {
          var fr = new FileReader();
          fr.onload = function() {
            $img.attr('src', fr.result);
            $preview.show();
          }
          fr.readAsDataURL(file);
        }
      }
    });

    $(document).on('click', '.delPanelImg', function(e) {
      e.preventDefault();
      var $panel = $(this).closest('.panel-attachment');
      var $fileFeedback = $panel.find('.fileFeedback');
      var $action = $panel.find('.action');
      var $preview = $panel.find('.preview');
      var $img = $panel.find('img');
      $preview.hide();
      $img.attr('src', '');
      $fileFeedback.val('');
      $action.val('del');
    });

    $(document).on('click', '.fileFeedback', function(e) {
      e.preventDefault();
      var $panel = $(this).closest('.panel-attachment');
      var $input = $panel.find(':file');
      Utility.log($input);
      $input.trigger('click');
    });


  }
);

$(window).bind('beforeunload', function() {
  if(unsaved) {
    return Utility.getLang('base', 'UNSAVED_DATA');
  }
});


function setUpMenu() {

  var val = $('.sidebar-search input').val();

  var hrefs = [];
  if(val !== '') {
    $('#side-menu .nav-second-level').find('[href*="' + val + '"],a:containsci("' + val + '")').each(function() {
      hrefs.push($(this).attr('href'));
    });
    $('#side-menu').find('.fa-search').removeClass('fa-search').addClass('fa-close');
    var index = 0;
    var openIndexes = [];
    $('#side-menu > li > a').each(function() {
      index++;
      var $li = $(this).closest('li');
      $li.find('.nav-second-level a').each(function() {
        if($.inArray($(this).attr('href'), hrefs) !== -1) {
          openIndexes.push(index);
          return false;
        }
      });
    });

    for(var i = 1; i < $('#side-menu > li').length; i++) {
      $li = $('#side-menu > li:eq(' + i + ')');
      var isOpened = $li.hasClass('active');
      Utility.log(i + ':' + isOpened);
      if($.inArray(i, openIndexes) != -1) { // menu da aprire
        if(!isOpened) {
          $li.find('a:first').trigger('click');
        }
        $li.find('.nav-second-level a').each(function() {
          href = $(this).attr('href');
          if($.inArray(href, hrefs) != -1) {
            $(this).closest('li').show();
          } else {
            $(this).closest('li').hide();
          }
        });
      } else {
        if(isOpened) {
          Utility.log('Devo chiudere questo li');
          $li.removeClass('active');
          $li.find('.nav-second-level').removeClass('in');
        }
      }

    }

    /*

    for(var i = 0; i < openIndexes.length; i++) {
      var index = parseInt(openIndexes[i], 10);
      var $li = $('#side-menu > li:eq(' + index + ')');
      $li.find('a').trigger('click');
    }*/

  } else {
    $('#side-menu > li').removeClass('active');
    $('#side-menu .nav-second-level').removeClass('in');
    $('#side-menu').find('.fa-close').removeClass('fa-close').addClass('fa-search');
  }


}

function fixedHeader() {
  $('.page-header').parent().css({'height': $('.page-header').outerHeight(true) + 'px'});
  $(window).on('scroll', function(e) {
    var sogliaTop = $('.navbar-static-top').height();
    var marginTop = 40;
    sogliaTop += marginTop; // il margine di page-header
    var css = {};
    var scrollTop = $(window).scrollTop();
    if(scrollTop > sogliaTop) {
      var $pH = $('.page-header');
      var hPadding = 30; // padding orizzontale
      var leftOffset = $('.sidebar-nav').width(); // la larghezza della sidebar
      leftOffset += hPadding + 1; // il padding + bordo che c'è tra la sidebar e il pannello
      var topOffset = '-' + marginTop + 'px';
      var panelWidth = $(window).width() - leftOffset - hPadding; // tolgo il left è il padding a destra
      css = {
        'position'        : 'fixed',
        'top'             : topOffset,
        'left'            : leftOffset,
        'width'           : panelWidth,
        'z-index'         : 10000,
        'background-color': $('#page-wrapper').css('background-color')
      };
    } else {
      css = {
        'position'        : 'static',
        'top'             : 'auto',
        'left'            : 'auto',
        'width'           : 'auto',
        'z-index'         : 'auto',
        'background-color': 'none'
      };
    }
    $('.page-header').css(css);
  });

}

function somethingUnsaved() {
  if(!unsaved) {
    new PNotify({
      title: Utility.getLang('base', 'MODIFICHE_NON_SALVATE'),
      text : '',
      hide : false,
      type : 'warning'
    });
    unsaved = true;
  }
};