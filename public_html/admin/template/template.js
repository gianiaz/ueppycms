/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (08/06/16, 16.06)                                                                     **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
var module_name = 'template';
var template = false;
var unsaved = false;
var $saveBtn = false;
$(function() {

  fixedHeader();

  $saveBtn = $('.saveTemplate');

  $saveBtn.attr('disabled', true).addClass('disabled');

  $(document).on('click', 'a.list-group-item:not(.disabled)', function(e) {
    var $this = $(this);
    e.preventDefault();
    $('.list-group-item').removeClass('list-group-item-info');
    $this.addClass('list-group-item-info');
    template = $this.data('template');
    loadTemplateData($this.data('template'));
  });

  $(document).on('click', '.infoModulo:not(.disabled)', function(e) {
    e.preventDefault();
    var msg = '<p>' + $(this).data('info') + '<br /><br />' + Utility.getLang(module_name, 'AUTHOR') + ': ';
    var autore = $(this).data('author').split('-');
    autore[0] = $.trim(autore[0]);
    autore[1] = $.trim(autore[1]);
    autore = autore[0] + '&nbsp;&lt;' + '<a href="mailto:' + autore[1] + '">' + autore[1] + '</a>' + '&gt;';
    msg += autore + '</p>';

    Utility.alert({
      'title'  : Utility.getLang(module_name, 'INFO'),
      'message': msg,
      'type'   : 'info'
    });
  });

  $(document).on('click', '.delModulo:not(.disabled)', function(e) {
    e.preventDefault();
    var $widget = $(this).closest('.widget');
    if($widget.data('duplicabile') == 0) {

      var data = {
        nome: $widget.data('widget'),
        info: {
          'info'       : $widget.find('.infoModulo').data('info'),
          'author'     : $widget.find('.infoModulo').data('author'),
          'duplicabile': $widget.data('duplicabile'),
          'nome'       : $widget.find('span.name').text()
        }
      };

      var availHandlebars = Utility.template({'name': 'avail', jqObj: $('#avail-template')});
      $(availHandlebars(data)).appendTo('.panel-disponibili');
      applyDraggable();
    }
    $(this).closest('.widget').remove();
    somethingUnsaved();
  });

  $(document).on('click', '.settingsModulo:not(.disabled)', function(e) {
    e.preventDefault();
    var $widget = $(this).closest('.widget');
    var data = {'istanza': $widget.data('widget') + '-' + $widget.data('istanza')};
    $.ajax({
      type    : "POST",
      url     : main_host + 'admin/' + module_name + '/load_module_config/',
      data    : data,
      dataType: "json",
      success : function(ret_data) {
        if(parseInt(ret_data.result, 10) == 1) {
          var $frm = $('#ajaxForm2');
          $frm.empty();

          openModal(null, Utility.getLang(module_name, 'CONFIGURAZIONE_MODULO') + $widget.find('span.name').text(), function($modal, formData) {

            $modal.on('hidden.bs.modal', function(e) {
              $(this).data('bs.modal', null);
              $(this).remove();
            });

            var $frm = $modal.find('#ajaxForm2');

            if(ret_data.data.copia) {
              var compiled = Utility.template({'name': 'selectcopy', jqObj: $('#selectcopy-template')});
              var data = {
                'data': ret_data.data.copia
              };
              $(compiled(data)).appendTo($frm);
            }

            for(i = 0; i < ret_data.data.generali.length; i++) {
              var item = ret_data.data.generali[i];
              if(item.type == 'boolean') {
                item.SI = Utility.getLang('base', 'SI');
                item.NO = Utility.getLang('base', 'NO');
              }
              var compiled = Utility.template({'name': item.type, jqObj: $('#' + item.type + '-template')});
              var $input = $(compiled(item));
              $input.appendTo($frm);
              if($input.find('select').length > 0) {
                $input.find('select').val(item.default);
              }
            }
            $('<div class="clearfix"></div>').appendTo($frm);
            if(ret_data.data.multilingua.length) {
              var compiled = Utility.template({'name': 'ml', jqObj: $('#ml-template')});
              $(compiled()).appendTo($frm);
              $('.tab-lang').each(function() {
                var $tab = $(this);
                var lingua = $tab.data('lingua');
                for(var i = 0; i < ret_data.data.multilingua.length; i++) {
                  var item = ret_data.data.multilingua[i];
                  item.lingua = lingua;
                  item.value = item.default[lingua];
                  //Utility.log(item);
                  var compiled = Utility.template({
                    'name': item.type + '-ml',
                    jqObj : $('#' + item.type + '-ml-template')
                  });
                  $(compiled(item)).appendTo($tab);
                }
              });
            }

            $('<input type="hidden" name="istanza" value="' + $widget.data('widget') + '-' + $widget.data('istanza') + '" />').appendTo($frm);

            $('.cmsHelp').tooltip();


          });

        } else {
          Utility.alert({message: ret_data.error});
        }
      }
    });
  });

  $(document).on('click', '#copyFrom', function(e) {
    e.preventDefault();
    if($('#copy').val() == '0') {
      Utility.alert({'message': 'SELEZIONA_SORGENTE'});
    } else {
      loadConfig();
    }
  });
  $(document).on('click', '#widgets', function(e) {
    e.preventDefault();

    $('#wd').ScrollTo({
      duration: 500
    });

  });

  $saveBtn.on('click', function(e) {
    e.preventDefault();
    salvaDisposizione();
  });

  $(document).on('click', '#newWidget', function(e) {
    if(template && unsaved) {
      Utility.confirm({
        'message': Utility.getLang(module_name, 'CANT_EDIT_WIDGET_SAVE_FIRST'),
        'onOk'   : function($modal) {
          salvaDisposizione(newWidget);
        }
      });
    } else {
      newWidget();
    }
  });

  $(document).on('change', '.view', function(e) {
    e.preventDefault();
    somethingUnsaved();
  });

  $(document).on('click', '.editWidget', function(e) {
    e.preventDefault();
    var id = $(this).data('id');

    if(template && unsaved) {
      Utility.confirm({
        'message': Utility.getLang(module_name, 'CANT_EDIT_WIDGET_SAVE_FIRST'),
        'onOk'   : function($modal) {
          salvaDisposizione(function() {
            editWidget(id, function() {
              if(template) {
                loadTemplateData(template);
              }
            });
          });
        }
      });
    } else {
      editWidget(id, function() {
        if(template) {
          loadTemplateData(template);
        }
      });
    }


  });

  $(document).on('click', '.deleteWidget', function(e) {
    e.preventDefault();
    var id = $(this).data('id');
    var title = $(this).data('title');
    Utility.confirm({
      'message': sprintf(Utility.getLang('base', 'VUOI_RIMUOVERE_LA_VOCE'), title),
      'onOk'   : function() {

        var data = {id: id};
        $.ajax({
          type    : "POST",
          url     : main_host + 'admin/' + module_name + '/del_widget/',
          data    : data,
          dataType: "json",
          success : function(ret_data) {
            if(parseInt(ret_data.result, 10) == 1) {
              PNotify.removeAll();
              new PNotify({
                title   : Utility.getLang('base', 'ELEMENTO_CANCELLATO'),
                text    : '',
                type    : 'success',
                nonblock: {
                  nonblock        : true,
                  nonblock_opacity: .2
                }
              });
              $dt.ajax.reload(null, false);
            } else {
              Utility.alert({message: ret_data.error});
            }
          }
        });
      }
    });
  })


  cols = [];

  // placeholder per modifica
  var col = {
    'title'     : Utility.getLang('base', 'MODIFICA'),
    'className' : 'dt0 dt-body-center',
    'searchable': false
  };
  cols.push(col);

  col = {
    'title'    : 'ID',
    searchable : true,
    'className': 'dt1 dt-body-center',
    data       : 'id'
  };

  cols.push(col);

  col = {
    'title'    : Utility.getLang(module_name, 'NOME'),
    searchable : true,
    'className': 'dt2',
    data       : 'nome'
  };

  cols.push(col);

  // placeholder per cancellare
  col = {'className': 'dt4 dt-body-center'};

  cols.push(col);

  $dt = $('#dataTable').DataTable({
      'order'         : [[2, "asc"]],
      'language'      : {
        url: main_host + 'bower_components/datatables-plugins/i18n/Italian.lang'
      },
      'sDom'          : 'tip',
      'iDisplayLength': 15,
      'stateSave'     : true,
      responsive      : true,
      ajax            : {
        method: 'post',
        url   : main_host + 'admin/' + module_name + '/get_moduli_dinamici_list/',
      },
      columns         : cols,
      'columnDefs'    : [
        {
          'targets'   : 0,
          'searchable': false,
          'orderable' : false,
          'className' : 'dt-body-center',
          'render'    : function(data, type, full, meta) {
            return '<a href="#" title="' + Utility.getLang('base', 'MODIFICA_RECORD') + '" class="btn btn-primary editWidget" data-id="' + full.id + '"><i class="fa fa-pencil"></i></a>';
          }
        },
        {
          'targets'  : 1,
          'className': 'dt-body-center'
        },
        {
          'targets'   : cols.length - 1,
          'searchable': false,
          'className' : 'dt-body-center',
          'orderable' : false,
          'render'    : function(data, type, full, meta) {
            var ret = '<a href="#" class="btn btn-danger deleteWidget';
            if(parseInt(full.cancellabile, 10) == 0) {
              ret += ' disabled';
            }
            ret += '" title="' + Utility.getLang('base', 'ELIMINA_RECORD') + '" data-id="' + parseInt(full.id, 10) + '" data-title="' + full.nome + '"><i class="fa fa-trash"></i></a>';
            return ret
          }
        }
      ]
    }
  );

});

function somethingUnsaved() {
  if(!unsaved) {
    new PNotify({
      title: Utility.getLang('base', 'MODIFICHE_NON_SALVATE'),
      text : '',
      hide : false,
      type : 'warning'
    });
    unsaved = true;
    $saveBtn.removeClass('disabled').removeAttr('disabled');
  }
}

function loadTemplateData(template) {
  Utility.lockScreen({'message': Utility.getLang('base', 'WAIT_PLEASE')});
  $('.list-group-item').addClass('disabled');

  var data = {'template': template};
  $.ajax({
    type    : "POST",
    url     : main_host + 'admin/' + module_name + '/load_template_data/',
    data    : data,
    dataType: "json",
    success : function(ret_data) {
      Utility.unlockScreen();
      $('.list-group-item').removeClass('disabled');
      if(parseInt(ret_data.result, 10) == 1) {
        Utility.log(ret_data.data);
        var $panelDisponibili = $('.panel-disponibili');
        var $panelBlocchi = $('.panel-blocchi');
        $panelDisponibili.empty();
        $panelBlocchi.empty();
        var availHandlebars = Utility.template({'name': 'avail', jqObj: $('#avail-template')});
        var widgetHandlebars = Utility.template({'name': 'widget', jqObj: $('#widget-template')});
        var bloccoHandlebars = Utility.template({'name': 'blocco', jqObj: $('#blocco-template')});

        for(var i in ret_data.data.disponibili) {
          var markup = availHandlebars(ret_data.data.disponibili[i]);
          $(markup).appendTo($panelDisponibili);
        }

        for(var i = 0; i < ret_data.data.blocchi.length; i++) {
          var blocco = ret_data.data.blocchi[i];
          blocco.markup = '';
          for(var x = 0; x < ret_data.data.usati.length; x++) {
            var usato = ret_data.data.usati[x];
            usato.VISTA_DA_UTILIZARE = Utility.getLang(module_name, 'VISTA');
            if(usato.db.posizione == blocco.nome) {
              blocco.markup += widgetHandlebars(usato);
            }
          }
          $(bloccoHandlebars(blocco)).appendTo($panelBlocchi);
        }


        applyDraggable();

        $(".connectedSortable").sortable({
          update     : function() {
            somethingUnsaved();
          },
          zIndex     : 200,
          dropOnEmpty: true,
          appendTo   : document.body,
          helper     : 'clone',
          handle     : '.anchor',
          connectWith: ".connectedSortable",
          cursorAt   : {top: 0, left: 0},
          placeholder: 'sortPlaceholder',
          start      : function() {
            $(this).data("startingScrollTop", window.pageYOffset);
          },
          receive    : function(event, ui) {
            if($(ui.item).data('widget') == 'main') {
              $(ui.sender).sortable('cancel');
            } else {
              somethingUnsaved();
            }
          }

        });

      } else {
        Utility.alert({message: ret_data.error});
      }
    }
  });

}


function openModal(formData, title, cb) {

  if(typeof formData == 'undefined') {
    formData = false;
  }

  var opts = {
    TITLE   : title,
    content : $('#modal').html(),
    size    : 'modal-lg',
    onSave  : function($modal) {
      $modal.find('#ajaxForm2').ajaxSubmit({
        'dataType': 'json',
        'success' : function(ret_data) {
          if(parseInt(ret_data.result, 10) == 1) {

            $modal.modal('hide');


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
            Utility.alert({'message': ret_data.error});
          }
        }
      });
    },
    callback: function($modal) {
      $modal.find('.cmsHelp').tooltip();
      if(formData) {
        for(var i in formData) {

          if(typeof(formData['formattedData'][i]) != 'undefined') {
            $modal.find('#' + i).val(formData['formattedData'][i]);
          } else {
            $modal.find('#' + i).val(formData[i]);
          }
          $modal.data(i, formData[i]);
        }
      }
      if(typeof(cb) == 'function') {
        cb($modal, formData);
      }
    }
  };

  Utility.modalForm(opts);

}

function openModalWidget(formData, title, cb, onSave) {

  if(typeof formData == 'undefined') {
    formData = false;
  }

  var opts = {
    TITLE   : title,
    content : $('#modal2').html(),
    size    : 'modal-lg',
    onSave  : function($modal) {
      $('.toMce').trigger('click');
      tinyMCE.triggerSave();
      $modal.find('#ajaxForm3').ajaxSubmit({
        'dataType': 'json',
        'success' : function(ret_data) {
          if(parseInt(ret_data.result, 10) == 1) {

            $modal.modal('hide');


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

            $dt.ajax.reload(null, false);

            if(onSave) {
              onSave();
            }

          } else {
            Utility.alert({'message': ret_data.error});
          }
        }
      });
    },
    callback: function($modal) {
      $modal.find('.cmsHelp').tooltip();
      if(formData) {
        for(var i in formData) {

          if(typeof(formData['formattedData'][i]) != 'undefined') {
            $modal.find('#' + i).val(formData['formattedData'][i]);
          } else {
            $modal.find('#' + i).val(formData[i]);
          }
          $modal.data(i, formData[i]);
        }
      }
      if(typeof(cb) == 'function') {
        cb($modal, formData);
      }
    }
  };

  Utility.modalForm(opts);

}


function applyDraggable() {
  $('div.avail').draggable({
    'connectToSortable': '.connectedSortable',
    helper             : 'clone',
    revert             : 'invalid',
    handle             : '.anchor',
    zIndex             : 200,
    stop               : function(event, ui) {
      var $droppato = $('.connectedSortable').find('.avail');
      if($droppato.length > 0) {  // se ho droppato nell'area preposta (altrimenti l'elemento torna alla posizione originaria)
        var $originale = $(ui.helper.context);
        if(!$originale.data('duplicabile') == 1) {
          $originale.remove();
        }
        // chiamata ajax per reperire ciò che serve per aggiungere il modulo
        var data = {
          'nome': $droppato.data('nome'),
          'dyn' : $droppato.data('dyn')
        };
        $.ajax({
          type    : "POST",
          url     : main_host + 'admin/' + module_name + '/load_module_data/',
          data    : data,
          dataType: "json",
          success : function(ret_data) {
            if(parseInt(ret_data.result, 10) == 1) {
              $('.widget[data-widget="' + data.nome + '"]').each(function() {
                Utility.log($(this).data('istanza') + '>=' + parseInt(ret_data.data.modulo.db.istanza, 10));
                if($(this).data('istanza') >= ret_data.data.modulo.db.istanza) {
                  ret_data.data.modulo.db.istanza = $(this).data('istanza') + 1;
                }
              });
              var widgetHandlebars = Utility.template({'name': 'widget', jqObj: $('#widget-template')});
              ret_data.data.modulo.VISTA_DA_UTILIZARE = Utility.getLang(module_name, 'VISTA');
              $droppato.replaceWith(widgetHandlebars(ret_data.data.modulo));
              somethingUnsaved();
            } else {
              Utility.alert({message: ret_data.error});
            }
          }
        });
      }
    }
  });

}

function newWidget() {
  openModalWidget(null, Utility.getLang(module_name, 'WIDGET_DINAMICI'), function($modal, formData) {

    $frm = $modal.find('#ajaxForm3');

    var compiled = Utility.template({'name': 'ml', jqObj: $('#ml-template')});

    $(compiled()).appendTo($frm);
    $frm.find('.tab-lang').each(function() {
      var $tab = $(this);
      var lingua = $tab.data('lingua');
      var textareaData = {
        'lingua': lingua,
        'var'   : 'testo',
        'label' : Utility.getLang(module_name, 'TESTO'),
        'help'  : Utility.getLang(module_name, 'TESTO_HELP'),
        'value' : ''
      };
      var compiledTextarea = Utility.template({'name': 'textarea-ml', jqObj: $('#textarea-ml-template')});
      $(compiledTextarea(textareaData)).appendTo($tab);

    });

    $modal.on('hidden.bs.modal', function(e) {
      var length = tinyMCE.editors.length;
      for(var i = length; i > 0; i--) {
        tinyMCE.editors[i - 1].remove();
      }
      ;
      $(this).data('bs.modal', null);
      $(this).remove();
    });

    Ueppy.tinyMCESetOpts({
      'main_host'        : main_host,
      'selector'         : 'textarea.mce',
      'forced_root_block': ''
    });
    Ueppy.tinyMCEInit();

  }, function() {
    if(template) {
      loadTemplateData(template);
    }
  });

}

function salvaDisposizione(callback) {

  var data = {
    'template': template,
    'blocchi' : []
  };

  $('.panel-blocco').each(function() {

    var $blocco = $(this);

    var blocco = {
      'nome'      : $(this).data('nome'),
      'principale': $(this).data('principale'),
      'moduli'    : []
    };

    $blocco.find('.widget').each(function() {
      var $widget = $(this);
      var widget = {
        'nome'   : $widget.data('widget'),
        'istanza': $widget.data('istanza'),
        'id'     : $widget.data('id')
      };

      if($widget.data('dyn')) {
        widget.vista = $widget.data('dyn');
      } else {
        widget.vista = $widget.find('.vista select').val();
      }

      blocco.moduli.push(widget);
    });

    data.blocchi.push(blocco);

  });

  var jsonData = JSON.stringify(data);

  data = {'data': jsonData};

  $.ajax({
    type    : "POST",
    url     : main_host + 'admin/' + module_name + '/insert/',
    data    : data,
    dataType: "json",
    success : function(ret_data) {
      if(parseInt(ret_data.result, 10) == 1) {
        $saveBtn.addClass('disabled').attr('disabled', true);
        unsaved = false;
        PNotify.removeAll();
        loadTemplateData(template);
        if(callback) {
          callback();
        }
      } else {
        Utility.alert({message: ret_data.error});
      }
    }
  });

}

function editWidget(id, callback) {
  var data = {'id': id};

  var $frm = $('#ajaxForm3');
  $.ajax({
    type    : "POST",
    url     : main_host + 'admin/' + module_name + '/load_widget/',
    data    : data,
    dataType: "json",
    success : function(ret_data) {
      if(parseInt(ret_data.result, 10) == 1) {
        openModalWidget(ret_data.data, Utility.getLang(module_name, 'WIDGET_DINAMICI'), function($modal, formData) {

          $frm = $modal.find('#ajaxForm3');

          var compiled = Utility.template({'name': 'ml', jqObj: $('#ml-template')});

          $(compiled()).appendTo($frm);
          $frm.find('.tab-lang').each(function() {
            var $tab = $(this);
            var lingua = $tab.data('lingua');
            var textareaData = {
              'lingua': lingua,
              'var'   : 'testo',
              'label' : Utility.getLang(module_name, 'TESTO'),
              'help'  : Utility.getLang(module_name, 'TESTO_HELP'),
              'value' : formData[lingua]['testo']
            };
            var compiledTextarea = Utility.template({'name': 'textarea-ml', jqObj: $('#textarea-ml-template')});
            $(compiledTextarea(textareaData)).appendTo($tab);

          });

          $modal.on('hidden.bs.modal', function(e) {
            var length = tinyMCE.editors.length;
            for(var i = length; i > 0; i--) {
              tinyMCE.editors[i - 1].remove();
            }
            $(this).data('bs.modal', null);
            $(this).remove();
          });

          Ueppy.tinyMCESetOpts({
            'main_host'        : main_host,
            'selector'         : 'textarea.mce',
            'forced_root_block': ''
          });

          Ueppy.tinyMCEInit();

        }, function() {
          callback();
        });
      } else {
        Utility.alert({message: ret_data.error});
      }
    }
  });

}

function loadConfig() {

  var data = {'istanza': $('#copy').val()};
  $.ajax({
    type    : "POST",
    url     : main_host + 'admin/' + module_name + '/load_module_config/',
    data    : data,
    dataType: "json",
    success : function(ret_data) {
      if(parseInt(ret_data.result, 10) == 1) {
        for(var i = 0; i < ret_data.data.generali.length; i++) {
          var item = ret_data.data.generali[i];
          Utility.log('#' + item.var + ' = "' + item.default + '"');
          $('#' + item.var).val(item.default);
        }
        if(ret_data.data.multilingua.length) {
          $('.tab-lang').each(function() {
            var $tab = $(this);
            var lingua = $tab.data('lingua');
            for(var i = 0; i < ret_data.data.multilingua.length; i++) {
              var item = ret_data.data.multilingua[i];
              Utility.log('#' + item.var + '_' + lingua + ' ="' + item.default[lingua] + '"');
              $('#' + item.var + '_' + lingua).val(item.default[lingua]);
            }
          });
        }
      } else {
        Utility.alert({message: ret_data.error});
      }
    }
  });

}