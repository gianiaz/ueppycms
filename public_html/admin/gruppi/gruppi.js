/*****************/
/***ueppy3.1.01***/
/*****************/
/**  CHANGELOG  **/
/************************************************************************************************/
/** v.3.1.01 (04/03/2014)                                                                      **/
/** - Aggiunto codice per gestire i bottoni "salva e chiudi" e "salva e nuovo"                 **/
/**                                                                                            **/
/** v.3.1.00                                                                                   **/
/** - Versione stabile                                                                         **/
/**                                                                                            **/
/************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                    **/
/** copyright: Ueppy s.r.l                                                                     **/
/************************************************************************************************/

var module_name = 'gruppi';
var debug_ajax = false;
$(function() {
  switch(Utility.getUrlParam(1)) {

    case 'new':

      Utility.tabIndexInit();

      afterSubmit = function(ret_data, $form) {
        if(parseInt(ret_data.result, 10) === 1) {
          Ueppy.saveSuccess(ret_data.dati);
        } else {
          Ueppy.showFormErrors(ret_data.errors, ret_data.wrongs);
        }

      }

      break;

    case 'permessi':

      $(':checkbox').bootstrapSwitch();

      $(document).on('switchChange.bootstrapSwitch', '#permessi_list', function(e) {
        if($(this).bootstrapSwitch('state')) {
          $('.singlePermission').bootstrapSwitch('state', false);
          $('.singlePermission').bootstrapSwitch('disabled', true);
        } else {
          $('.singlePermission').bootstrapSwitch('disabled', false);
        }
      });

      afterSubmit = function(ret_data, $form) {
        if(parseInt(ret_data.result, 10) === 1) {
          Ueppy.saveSuccess(ret_data.dati);
        } else {
          Ueppy.showFormErrors(ret_data.errors, ret_data.wrongs);
        }

      }

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

    case 'order':
      $('.sort').sortable({
        placeholder: "hover",
        stop       : function() {
          ids = $(this).sortable('toArray').toString().replace(/ele_/g, '');
          $('#neworder').val(ids);
        }
      });

      break;

    case '#':
    case null:
      
      $(document).on('click', '.permessi', function(e) {
        var id = $(this).data('id');
        var data = {id: id};
        Utility.postData({
          action: main_host + 'admin/' + module_name + '/permessi/',
          data  : data
        });
      });

      // placeholder per modifica
      var col = {
        'title'     : Utility.getLang('base', 'MODIFICA'),
        'searchable': false,
        'className' : 'dt0'
      };
      cols.push(col);

      col = {
        'title'    : 'ID',
        searchable : true,
        data       : 'id',
        'className': 'dt1'
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
        'title'    : Utility.getLang(module_name, 'ORDINE'),
        searchable : false,
        data       : 'ordine',
        'className': 'dt3'
      };

      cols.push(col);

      // placeholder per permessi
      col = {'className': 'dt4'};
      cols.push(col);

      // placeholder per visibilita
      col = {'className': 'dt5'};
      cols.push(col);

      // placeholder per cancellare
      col = {'className': 'dt6'};
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
              'targets'   : cols.length - 3,
              'searchable': false,
              'className' : 'dt-body-center',
              'orderable' : false,
              'render'    : function(data, type, full, meta) {
                var m = '<a href="#" title="' + Utility.getLang(module_name, 'MODIFICA_PERMESSI') + '" class="btn btn-info permessi" data-id="' + parseInt(full.id, 10) + '" data-title="' + full.nome + '">';
                m += '<i class="fa fa-lock"></i>';
                m += '</a>';
                return m;
              }
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