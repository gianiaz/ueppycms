/*****************/
/***ueppy3.4.00***/
/*****************/
/**  CHANGELOG  **/
/************************************************************************************************/
/** v.4.1.00                                                                                   **/
/** - Versione stabile                                                                         **/
/**                                                                                            **/
/************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                    **/
/** copyright: Ueppy s.r.l                                                                     **/
/************************************************************************************************/
module_name = 'traduzioni';
var lingueTraduzioni = [];
lingueTraduzioni.push(['it', 'Italiano']);
lingueTraduzioni.push(['en', 'English']);
lingueTraduzioni.push(['de', 'Deutsch']);
lingueTraduzioni.push(['fr', 'Français']);
lingueTraduzioni.push(['ru', 'Pусский']);
lingueTraduzioni.push(['es', 'Español']);

var sigleTraduzioni = ['it', 'en', 'de', 'es', 'fr', 'ru'];

$(function() {
  switch(Utility.getUrlParam(1, rel_root)) {

    default:

      $('#noncompilate').bootstrapSwitch();

      $('#noncompilate').on('switchChange.bootstrapSwitch', function(e) {
        $dt.ajax.reload(null, false);
      });


      $(document).on('click', '.selRowTraduzioni', function(e) {
        salvaSelezioneTraduzioni();
      });

      // click sul bottone di modifica
      $('#dataTable').on('click', '.editChiave', function(e) {
        var id = $(this).data('id');
        var scope = $(this).data('scope');
        editChiave(id, scope);
      });

      $(document).on('click', '#newbtn', function(e) {
        e.preventDefault();
        openModal(null, Utility.getLang(module_name, 'MODIFICA_CHIAVE'));
      });


      // click sull'esportazione delle chiavi
      $(document).on('click', '#exportButton', function() {
        Utility.lockScreen({message: Utility.getLang(module_name, 'ESPORTAZIONE_IN_CORSO')});
        $.ajax({
          type    : "POST",
          url     : main_host + 'admin/' + module_name + '/export/',
          dataType: "json",
          success : function(returned_data) {
            Utility.unlockScreen();
            if(parseInt(returned_data.result, 10) == 1) {
              Utility.alert({message: Utility.getLang('traduzioni', 'EXPORT_SUCCESS'), 'type': 'success'});
            } else {
              Utility.alert({message: returned_data.error});
            }
          }
        });
      });

      // click sull'esportazione delle chiavi
      $(document).on('click', '#exportGlobal', function() {
        Utility.lockScreen({message: Utility.getLang(module_name, 'ESPORTAZIONE_IN_CORSO')});
        $.ajax({
          type    : "POST",
          url     : main_host + 'admin/' + module_name + '/export_global/',
          dataType: "json",
          success : function(returned_data) {
            Utility.unlockScreen();
            if(parseInt(returned_data.result, 10) == 1) {
              Utility.alert({message: Utility.getLang('traduzioni', 'EXPORT_SUCCESS'), 'type': 'success'});
            } else {
              Utility.alert({message: returned_data.error});
            }
          }
        });
      });

      // click sul bottone per creare una nuova chiave
      $(document).on('click', '#del_selected', function(e) {
        e.preventDefault();
        data = {
          'action': main_host + 'admin/traduzioni/del_selected/'
        };
        Utility.postData(data);

      });

      // placeholder per selezione
      var col = {
        'title'     : Utility.getLang('base', 'SELEZIONA'),
        'searchable': false,
        'className' : 'dt0 dt-body-center',
        'checkbox'  : true
      };
      cols.push(col);

      // placeholder per modifica
      col = {
        'title'     : Utility.getLang('base', 'MODIFICA'),
        'className' : 'dt1 dt-body-center',
        'searchable': false
      };
      cols.push(col);

      col = {
        'title'    : Utility.getLang(module_name, 'PROVENIENZA'),
        'className': 'dt2 dt-body-center',
        searchable : true,
        data       : 'scope'
      };

      cols.push(col);

      col = {
        'title'    : Utility.getLang(module_name, 'CHIAVE'),
        searchable : true,
        'className': 'dt3',
        data       : 'chiave'
      };

      cols.push(col);

      col = {
        'title'    : Utility.getLang(module_name, 'SEZIONE'),
        'className': 'dt4',
        searchable : true,
        data       : 'sezione'
      };

      cols.push(col);

      col = {
        'title'    : Utility.getLang(module_name, 'MODULO'),
        'className': 'dt5',
        searchable : true,
        data       : 'modulo'
      };

      cols.push(col);


      for(var i = 0; i < lingueTraduzioni.length; i++) {
        var sigla = lingueTraduzioni[i][0];
        var estesa = lingueTraduzioni[i][1];
        col = {
          'campo': sigla + '.dicitura',
        }

        col = {
          'title'   : estesa,
          searchable: true,
          data      : sigla + '_dicitura',
          className : 'lang lang-' + sigla
        };

        cols.push(col);

      }

      // placeholder per cancellare
      col = {'className': 'dt6'};

      cols.push(col);

      $('#dataTable').on('xhr.dt', function() {
        Utility.unlockScreen();
      });

      $('#dataTable').on('preXhr.dt', function() {
        setTimeout(function() {
          Utility.lockScreen({'message': Utility.getLang(module_name, 'ATTENDERE')});
        }, 0);
      });

      $('#dataTable').on('draw.dt', function() {
        $('.dataTable_wrapper tbody tr').each(function() {
          $(this).find('td:gt(5)').not(':last-child').addClass('inline-edit');
        });

        $('.inline-edit').editable(function(value, settings) {
          var $tr = $(this).closest('tr');
          var id = $tr.find('.editChiave').data('id');
          var scope = $tr.find('.editChiave').data('scope');
          var data = {
            'id'  : id,
            'lang': $(this).closest('td').data('lingua'),
            value : value,
            scope : scope
          };
          $.ajax({
            type    : "POST",
            url     : main_host + 'admin/' + module_name + '/saveat/',
            data    : data,
            dataType: "json",
            success : function(ret_data) {
              if(parseInt(ret_data.result, 10) != 1) {
                Utility.alert({message: ret_data.error});
              }
            }
          });
          return value;
        }, {'onblur': 'submit', tooltip: '', placeholder: '', type: 'textarea'});

        $('.inline-edit').on('keydown', 'textarea', function(e) {
          var keyCode = e.keyCode || e.which;

          if(keyCode == 9 || keyCode == 39) {
            e.preventDefault();
            $(this).blur();
            var $td = $(this).closest('td');
            var $next = $td.next('.inline-edit');
            if(!$next.length) {
              $tr = $td.closest('tr');
              $nextTr = $tr.next('tr');
              $next = $nextTr.find('.inline-edit:first');
            }
            if($next.length) {
              $next.trigger('click');
            }
          }

          if(keyCode == 40) {
            e.preventDefault();
            var $td = $(this).closest('td');
            $tr = $td.closest('tr');
            var idx = $tr.find('td').index($td);
            $nextTr = $tr.next('tr');
            $tdProssimaRiga = $nextTr.find('td:eq(' + idx + ')');
            $next = $tdProssimaRiga;
            if($next.length) {
              $(this).blur();
              $next.trigger('click');
            }
          }

          if(keyCode == 37) {
            e.preventDefault();
            var $td = $(this).closest('td');
            var $prevTd = $td.prev('.inline-edit');
            if(!$prevTd.length) {
              $tr = $td.closest('tr');
              $tr.css({'border': '1px solid red'});
              $prevTr = $tr.prev('tr');
              $prevTd = $prevTr.find('.inline-edit:last');
            }
            if($prevTd.length) {
              $(this).blur();
              $prevTd.trigger('click');
            }
          }

          if(keyCode == 38) {
            e.preventDefault();
            var $td = $(this).closest('td');
            var $tr = $td.closest('tr');
            var idx = $tr.find('td').index($td);
            var $prevTr = $tr.prev('tr');
            if($prevTr.find('td').length) {
              var $tdRigaPrecedente = $prevTr.find('td:eq(' + idx + ')');
              if($tdRigaPrecedente.length) {
                $tdRigaPrecedente.trigger('click');
              }

            }
          }

          Utility.log(keyCode);

        });

      });

      $dt = $('#dataTable').DataTable({
          'language'      : {
            url: main_host + 'bower_components/datatables-plugins/i18n/Italian.lang',
          },
          'stateSave'     : true,
          'sDom'          : 'tip',
          "order"         : [[2, "asc"]],
          'iDisplayLength': 15,
          responsive      : true,
          createdRow      : function(row, data, dataIndex) {
            for(i = 0; i < data.lingua.length; i++) {
              $(row).find('td:eq(' + (6 + i) + ')').data('lingua', data.lingua[i]);
            }
          },
          xhr             : function() {
            Utility.unlockScreen();
          },
          ajax            : function(data, callback, settings) {
            data.non_compilate = $('#noncompilate:checked').length;
            $.ajax({
              type    : "POST",
              url     : main_host + 'admin/' + module_name + '/getlist/',
              data    : data,
              dataType: "json",
              success : function(ret_data) {
                if(parseInt(ret_data.result, 10) == 1) {
                  if(parseInt(ret_data.delButton, 10) == 1) {
                    $('#del_selected').removeClass('disabled');
                  } else {
                    $('#del_selected').addClass('disabled');
                  }
                  callback(ret_data);
                } else {
                  Utility.alert({message: ret_data.error});
                }
              }
            });
          },
          columns         : cols,
          'columnDefs'    : [
            {
              'targets'   : 0,
              'searchable': false,
              'orderable' : false,
              'className' : 'dt-body-center',
              'render'    : function(data, type, full, meta) {
                var checkboxHtml = '<input type="checkbox" name="id[]" value="' + full.id + '" class="selRowTraduzioni"';
                if(full.selected) {
                  checkboxHtml += ' checked';
                }
                checkboxHtml += '>';
                return checkboxHtml;
              }
            },
            {
              'targets'   : 1,
              'searchable': false,
              'orderable' : false,
              'className' : 'dt-body-center',
              'render'    : function(data, type, full, meta) {
                return '<a href="#" class="btn btn-primary editChiave" data-id="' + full.id + '" data-scope="' + full.scope + '"><i class="fa fa-pencil"></i></a>';
              }
            },
            {
              'targets'  : 2,
              'className': 'dt-body-center'
            },
            {
              'targets'   : cols.length - 1,
              'searchable': false,
              'className' : 'dt-body-center',
              'orderable' : false,
              'render'    : function(data, type, full, meta) {
                return '<a href="#" class="btn btn-default delete" data-id="' + full.id + '" data-title="' + full.it_dicitura + '"><i class="fa fa-trash"></i></a>';
              }
            }
          ]
        }
      );

      break;

  }

})
;

function editChiave(id, scope) {
  Utility.lockScreen({message: Utility.getLang('base', 'PLEASE_WAIT')});
  var data = {
    id   : id,
    scope: scope
  };
  $.ajax({
    type    : "POST",
    url     : main_host + 'admin/' + module_name + '/load/',
    data    : data,
    dataType: "json",
    success : function(ret_data) {
      Utility.unlockScreen();
      if(parseInt(ret_data.result, 10) == 1) {
        openModal(ret_data.data, Utility.getLang(module_name, 'MODIFICA_CHIAVE'));
      } else {
        Utility.alert({'message': ret_data.error});
      }
    }
  });

}

function salvaSelezioneTraduzioni() {
  var seleziona = [];
  var deseleziona = [];

  $('#dataTable tbody input[type="checkbox"]').each(function() {
    var $tr = $(this).closest('tr');
    var $edit = $tr.find('.editChiave');

    var record = {
      'id'   : $edit.data('id'),
      'scope': $edit.data('scope')
    };

    if(this.checked) {
      seleziona.push(record);
    } else {
      deseleziona.push(record);
    }
  });

  var data = {
    seleziona  : seleziona,
    deseleziona: deseleziona,
    module_name: module_name,
  };
  $.ajax({
    type    : "POST",
    url     : main_host + 'admin/' + module_name + '/select/',
    data    : data,
    dataType: "json",
    success : function(ret_data) {
      if(parseInt(ret_data.result, 10) != 1) {
        Utility.alert({message: ret_data.error});
      } else {
        if(parseInt(ret_data.delButton, 10) == 1) {
          $('#del_selected').removeClass('disabled');
        } else {
          $('#del_selected').addClass('disabled');
        }
      }
    }
  });

}


function openModal(formData, title, cb) {

  Utility.log(formData);

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

            $dt.ajax.reload(null, false);
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
          Utility.log(i);
          if(typeof(formData['formattedData']) != 'undefined' && typeof(formData['formattedData'][i]) != 'undefined') {
            $modal.find('#' + i).val(formData['formattedData'][i]);
          } else {
            if($.inArray(i, sigleTraduzioni) != -1) {
              $modal.find('#dicitura_' + i).val(formData[i]['dicitura']);
            } else {
              $modal.find('#' + i).val(formData[i]);
            }
          }
          $modal.data(i, formData[i]);
        }

        $('<input type="hidden" name="scope" value="' + $modal.find('#scope').val() + '" />').appendTo($modal.find('form'));
        $modal.find('#scope').attr('disabled', true);
      }
      if(typeof(cb) == 'function') {
        cb($modal);
      }
    }
  };

  Utility.modalForm(opts);

}
