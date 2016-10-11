/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (20/05/16, 17.40)                                                                      **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/

var module_name = 'vetrina';
$(function() {

  switch(Utility.getUrlParam(1, rel_root)) {

    case 'insert':
      break;

    case 'new':

      Utility.tabIndexInit();

      beforeSerialize = function() {
        tinyMCE.triggerSave();
      }

      afterSubmit = function(ret_data, $form) {
        Utility.unlockScreen();
        if(parseInt(ret_data.result, 10) == 1) {
          Utility.log(ret_data.dati);
          Ueppy.saveSuccess(ret_data.dati);
        } else {
          Ueppy.showFormErrors(ret_data.errors, ret_data.wrongs);
        }
      }


      Ueppy.tinyMCESetOpts({
        'main_host'        : main_host,
        'width'            : '100%',
        'forced_root_block': '',
        'selector'         : 'textarea'
      });
      Ueppy.tinyMCEInit();

      break;

    case 'ordina':

      highest = 0;

      $('.ueppy-widget').each(function() {
        if($(this).height() > highest) {
          highest = $(this).height();
        }
      });

      $('.ueppy-widget').css({'height': highest + 'px'});

      $(".ueppy-widget").find('ul').sortable({
        placeholder: "placeholder",
        stop       : function() {
          saveState();
        }
      });
      $(".ueppy-widget").find('ul').disableSelection();

      saveState();

      break;

    case 'sort':

      $('.sort').sortable().bind('sortupdate', function() {
        //Triggered when the user stopped sorting and the DOM position has changed.
        var ids = [];
        $('.sort li').each(function() {
          ids.push($(this).data('id'));
        });

        $('#neworder').val(ids.join(','));

      });

      $('.sort').sortable();

      afterSubmit = function(ret_data, form) {
        if(parseInt(ret_data.result, 10) == 1) {
          Ueppy.saveSuccess([]);
        } else {
          Utility.alert({'message': ret_data.error});
        }
      }
      break;

    case 'settings':

      $(document).on('click', '#newsetting', function(e) {
        e.preventDefault();
        openModal(null, Utility.getLang(module_name, 'MODIFICA_SETTAGGIO'));
      });

      $(document).on('click', '.editsetting', function(e) {

        e.preventDefault();

        var data = {id: $(this).data('id')};

        $.ajax({
          type    : "POST",
          url     : main_host + 'admin/' + module_name + '/load_setting/',
          data    : data,
          dataType: "json",
          success : function(ret_data) {
            if(parseInt(ret_data.result, 10) == 1) {
              openModal(ret_data.data, Utility.getLang(module_name, 'MODIFICA_SETTAGGIO'));
            } else {
              Utility.alert({message: ret_data.error});
            }
          }
        });

      });

      $(document).on('click', '.deletesetting', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var title = $(this).data('title');
        Utility.confirm({
          'message': sprintf(Utility.getLang('base', 'VUOI_RIMUOVERE_LA_VOCE'), title),
          'onOk'   : function() {

            var data = {id: id};
            $.ajax({
              type    : "POST",
              url     : main_host + 'admin/' + module_name + '/del_setting/',
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

      cols = [];

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
        'title'    : Utility.getLang(module_name, 'GRUPPO'),
        searchable : true,
        data       : 'gruppo',
        'className': 'dts2'
      };

      cols.push(col);

      col = {
        'title'    : Utility.getLang(module_name, 'DIMENSIONI'),
        searchable : true,
        data       : 'dimensioni',
        'className': 'dts3'
      };

      cols.push(col);

      // placeholder per cancellare
      col = {'className': 'dt6 dt-body-center'};
      cols.push(col);

      $dt = $('#dataTable').DataTable({
          'order'         : [[3, "asc"]],
          'language'      : {
            url: main_host + 'bower_components/datatables-plugins/i18n/Italian.lang'
          },
          'sDom'          : 'tip',
          'iDisplayLength': 15,
          'stateSave'     : true,
          responsive      : true,
          ajax            : {
            method: 'post',
            url   : main_host + 'admin/' + module_name + '/get_settings_list/',
          },
          columns         : cols,
          'columnDefs'    : [
            {
              'targets'   : 0,
              'searchable': false,
              'orderable' : false,
              'className' : 'dt-body-center',
              'render'    : function(data, type, full, meta) {
                return '<a href="#" title="' + Utility.getLang('base', 'MODIFICA_RECORD') + '" class="btn btn-primary editsetting" data-id="' + full.id + '"><i class="fa fa-pencil"></i></a>';
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
                var ret = '<a href="#" class="btn btn-danger deletesetting';
                if(parseInt(full.cancellabile, 10) == 0) {
                  ret += ' disabled';
                }
                ret += '" title="' + Utility.getLang('base', 'ELIMINA_RECORD') + '" data-id="' + parseInt(full.id, 10) + '" data-title="' + full.gruppo + '"><i class="fa fa-trash"></i></a>';
                return ret
              }
            }
          ]
        }
      );

      break;

    case '#':
    case null:

      cols = [];

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
        'title'    : Utility.getLang(module_name, 'TITOLO'),
        searchable : true,
        data       : 'nome',
        'className': 'dt2'
      };

      cols.push(col);

      col = {
        'title'    : Utility.getLang(module_name, 'GRUPPO'),
        searchable : true,
        data       : 'gruppo',
        'className': 'dt3'
      };

      cols.push(col);

      col = {
        'title'    : Utility.getLang(module_name, 'ORDINE'),
        searchable : true,
        data       : 'ordine',
        'className': 'dt4'
      };

      cols.push(col);

      // placeholder per visibilita
      col = {'className': 'dt5 dt-body-center'};
      cols.push(col);

      // placeholder per cancellare
      col = {'className': 'dt6 dt-body-center'};
      cols.push(col);

      $dt = $('#dataTable').DataTable({
          'order'         : [[3, "asc"]],
          'language'      : {
            url: main_host + 'bower_components/datatables-plugins/i18n/Italian.lang'
          },
          'sDom'          : 'tip',
          'iDisplayLength': 15,
          'stateSave'     : true,
          responsive      : true,
          ajax            : {
            method: 'post',
            url   : main_host + 'admin/' + module_name + '/getlist/',
          },
          columns         : cols,
          'columnDefs'    : [
            {
              'targets'   : 0,
              'searchable': false,
              'orderable' : false,
              'className' : 'dt-body-center',
              'render'    : function(data, type, full, meta) {
                return '<a href="#" title="' + Utility.getLang('base', 'MODIFICA_RECORD') + '" class="btn edit" data-id="' + full.id + '"><i class="fa fa-pencil"></i></a>';
              }
            },
            {
              'targets'  : 1,
              'className': 'dt-body-center'
            },
            {
              'targets'   : cols.length - 2,
              'searchable': false,
              'className' : 'dt-body-center',
              'orderable' : false,
              'render'    : function(data, type, full, meta) {
                var disabled = '';
                if(typeof(full.visibility_disabled) != 'undefined' && parseInt(full.visibility_disabled, 10) == 1) {
                  disabled = ' disabled';
                }
                var m = '<a href="#" class="btn visibility' + disabled + '" data-id="' + parseInt(full.id, 10) + '" data-title="' + full.nome + '"';
                var icon, title;
                if(parseInt(full.attivo, 10) == 1) {
                  icon = '<i class="fa fa-eye"></i>';
                  title = Utility.getLang('base', 'DISABILITA');
                } else {
                  icon = '<i class="fa fa-eye-slash"></i>';
                  title = Utility.getLang('base', 'ABILITA');
                }
                m += ' title="' + title + '"' + disabled + '>';
                m += icon;
                m += '</a>';
                return m;
              }
            },
            {
              'targets'   : cols.length - 1,
              'searchable': false,
              'className' : 'dt-body-center',
              'orderable' : false,
              'render'    : function(data, type, full, meta) {
                var ret = '<a href="#" class="btn delete';
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

      break;

  }
});


function openModal(formData, title, cb) {

  Utility.log(formData);

  if(typeof formData == 'undefined') {
    formData = false;
  }

  var opts = {
    TITLE   : title,
    content : $('#modal').html(),
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
      Utility.log(formData);
      if(formData) {
        for(i in formData) {
          $modal.find('#' + i).val(formData[i]);
          $modal.data(i, formData[i]);
        }
      }
      if(formData.id == 1) {
        $modal.find('#gruppo').attr('readonly', 'true');
      } else {
        $modal.find('#gruppo').removeAttr('readonly');
      }
      if(typeof(cb) == 'function') {
        cb($modal);
      }
    }
  };

  Utility.modalForm(opts);

}
