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
var module_name = 'freemenu';

$(function() {

  switch(Utility.getUrlParam(1, rel_root)) {

    case 'stili':

      $('#newStyleBtn').on('click', function(e) {
        e.preventDefault();
        openModalStyle();
      });

      $(document).on('click', '.editStyle', function(e) {

        var data = {id: $(this).data('id')};

        $.ajax({
          type    : "POST",
          url     : main_host + 'admin/' + module_name + '/load_style/',
          data    : data,
          dataType: "json",
          success : function(ret_data) {
            if(parseInt(ret_data.result, 10) == 1) {
              openModalStyle(ret_data);
            } else {
              Utility.alert({'message': ret_data.error});
            }
          }
        });

      });

      $(document).on('click', '.deleteStyle', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var title = $(this).data('title');
        Utility.confirm({
          'message': sprintf(Utility.getLang('base', 'VUOI_RIMUOVERE_LA_VOCE'), title),
          'onOk'   : function() {

            var data = {id: id};
            $.ajax({
              type    : "POST",
              url     : main_host + 'admin/' + module_name + '/del_style/',
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
      });


      var col = {
        'title'     : Utility.getLang('base', 'MODIFICA'),
        'searchable': false,
        'className' : 'dts0 dt-body-center'
      };
      cols.push(col);

      col = {
        'title'    : 'ID',
        searchable : true,
        data       : 'id',
        'className': 'dts1 dt-body-center'
      };

      cols.push(col);

      col = {
        'title'    : Utility.getLang(module_name, 'NOME'),
        searchable : true,
        data       : 'nome',
        'className': 'dts2'
      };

      cols.push(col);

      // placeholder per cancellare
      col = {
        'title'    : Utility.getLang('base', 'ELIMINA'),
        'className': 'dts4 dt-body-center'
      };
      cols.push(col);

      var colDefs = [
        {
          'targets'   : 0,
          'searchable': false,
          'orderable' : false,
          'className' : 'dt-body-center',
          'render'    : function(data, type, full, meta) {
            return '<a href="#" title="' + Utility.getLang('base', 'MODIFICA_RECORD') + '" class="btn btn-primary editStyle" data-id="' + full.id + '"><i class="fa fa-pencil"></i></a>';
          }
        }
      ];

      colDefs.push({
        'targets'   : cols.length - 1,
        'searchable': false,
        'orderable' : false,
        'render'    : function(data, type, full, meta) {
          return '<a href="#"  title="' + Utility.getLang('base', 'ELIMINA_RECORD') + '" class="btn btn-danger deleteStyle" data-id="' + parseInt(full.id, 10) + '" data-title="' + full.nome + '"><i class="fa fa-trash"></i></a>';
        }
      });

      $dt = $('#dataTable').DataTable({
          'language'      : {
            url: main_host + 'bower_components/datatables-plugins/i18n/Italian.lang'
          },
          'sDom'          : 'tip',
          "order"         : [[1, "asc"]],
          'iDisplayLength': 15,
          'stateSave'     : true,
          responsive      : true,
          ajax            : {
            method: 'post',
            url   : main_host + 'admin/' + module_name + '/getstili/',
          },
          columns         : cols,
          'columnDefs'    : colDefs
        }
      );

      break;

    case 'new':

      $('.deleteRow').click(function(e) {
        e.preventDefault();
        $(this).closest('tr').remove();
      });

      beforeSerialize = function() {
        $('.table').each(function() {
          var str = '';
          $(this).find('tbody tr').each(function() {
            str += $(this).find('input:first').val();
            str += '***';
            str += $(this).find('input:eq(1)').val();
            str += '***';
            str += $(this).find('input:eq(2):checked').length;
            str += '***';
            str += $(this).find('input:eq(3):checked').length;
            str += "\n";
          });
          $(this).closest('div').find('textarea').html(str);
        });
      }

      afterSubmit = function(ret_data, $form) {
        Utility.unlockScreen();
        if(parseInt(ret_data.result, 10) === 1) {
          Ueppy.saveSuccess(ret_data.dati);
        } else {
          Ueppy.showFormErrors(ret_data.errors, ret_data.wrongs);
        }
      }

      $('.plus').click(function(e) {
        e.preventDefault();
        tbl = $(this).closest('div').find('table');
        inputvuoto = false;
        $(tbl).find('tbody tr').each(function() {
          $(this).find('input').each(function() {
            if($(this).val() === '') {
              inputvuoto = true;
            }
          });
        });
        if(!inputvuoto) {
          $('<tr>' + $('.rowTpl').find('tr').html() + '</tr>').appendTo(tbl);
        }
      });

      break;

    case null:
    case '#':

      var col = {
        'title'     : Utility.getLang('base', 'MODIFICA'),
        'searchable': false,
        'className' : 'dt0 dt-body-center'
      };
      cols.push(col);

      col = {
        'title'    : 'ID',
        searchable : true,
        data       : 'id',
        'className': 'dt1 dt-body-center'
      };

      cols.push(col);

      col = {
        'title'    : Utility.getLang(module_name, 'NOME'),
        searchable : true,
        data       : 'nome',
        'className': 'dt2'
      };

      cols.push(col);

      col = {
        'title'    : Utility.getLang(module_name, 'STILE'),
        searchable : true,
        data       : 'stile',
        'className': 'dt3'
      };

      cols.push(col);

      // placeholder per cancellare
      col = {
        'title'    : Utility.getLang('base', 'ELIMINA'),
        'className': 'dt4 dt-body-center'
      };
      cols.push(col);

      var colDefs = [
        {
          'targets'   : 0,
          'searchable': false,
          'orderable' : false,
          'className' : 'dt-body-center',
          'render'    : function(data, type, full, meta) {
            return '<a href="#" title="' + Utility.getLang('base', 'MODIFICA_RECORD') + '" class="btn edit" data-id="' + full.id + '"><i class="fa fa-pencil"></i></a>';
          }
        }
      ];

      colDefs.push({
        'targets'   : cols.length - 1,
        'searchable': false,
        'className' : 'dt-body-center',
        'orderable' : false,
        'render'    : function(data, type, full, meta) {
          return '<a href="#"  title="' + Utility.getLang('base', 'ELIMINA_RECORD') + '" class="btn delete" data-id="' + parseInt(full.id, 10) + '" data-title="' + full.nome + '"><i class="fa fa-trash"></i></a>';
        }
      });

      $dt = $('#dataTable').DataTable({
          'language'      : {
            url: main_host + 'bower_components/datatables-plugins/i18n/Italian.lang'
          },
          'sDom'          : 'tip',
          "order"         : [[1, "asc"]],
          'iDisplayLength': 15,
          'stateSave'     : true,
          responsive      : true,
          ajax            : {
            method: 'post',
            url   : main_host + 'admin/' + module_name + '/getlist/',
          },
          columns         : cols,
          'columnDefs'    : colDefs
        }
      );

      break;

  }
});

function openModalStyle(formData) {

  if(typeof formData == 'undefined') {
    formData = false;
  }

  var opts = {
    TITLE   : Utility.getLang(module_name, 'MODIFICA_STILE'),
    content : $('#new_stile').html(),
    size    : 'modal-lg', // modal-lg modal-sm
    onSave  : function($modal) {
      var cm = $modal.find('#markup').data('CodeMirrorInstance');
      $modal.find('#markup').val(cm.getValue());
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
      if(formData) {
        $modal.find('#id_style').val(formData.data.id);
        $modal.find('#nome').val(formData.data.nome);
        $modal.find('#markup').val(formData.data.markup);
      }
      codeMirrorOptions = {};
      codeMirrorOptions.theme = 'dracula';
      codeMirrorOptions.lineNumbers = true;
      codeMirrorOptions.mode = 'htmlmixed';
      var cm = CodeMirror.fromTextArea($modal.find('#markup').get(0), codeMirrorOptions);
      $modal.find('#markup').data('CodeMirrorInstance', cm);

    }
  };

  Utility.modalForm(opts);

}