/*****************/
/***v 1.00     ***/
/*****************/
/**  CHANGELOG  **/
/************************************************************************************************/
/** v.1.00 (08/10/2015)                                                                        **/
/** - Versione stabile                                                                         **/
/**                                                                                            **/
/************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                    **/
/** copyright: Ueppy s.r.l                                                                     **/
/************************************************************************************************/
var module_name = 'operatori';

$(function() {
  switch(Utility.getUrlParam(1, rel_root)) {

    case 'insert':
      break;

    case 'new':

      Utility.tabIndexInit();

      afterSubmit = function(ret_data, $form) {
        if(parseInt(ret_data.result, 10) == 1) {
          Ueppy.saveSuccess(ret_data.dati);
        } else {
          Ueppy.showFormErrors(ret_data.errors, ret_data.wrongs);
        }

      }

      break;

    case null:

      // azioni di default per il bottone di cambio visibilita delle tabelle
      $('.dataTable_wrapper').on('click', '.privileges', function(e) {
        var id = $(this).data('id');
        var data = {
          data    : {'selezione': id},
          'action': main_host + 'admin/' + module_name + '/privileges/'
        };

        Utility.postData(data);

      });

      // placeholder per modifica
      var col = {
        'title'     : Utility.getLang('base', 'MODIFICA'),
        'searchable': false
      };
      cols.push(col);

      col = {
        'title'   : 'ID',
        searchable: true,
        data      : 'id'
      };

      cols.push(col);

      col = {
        'title'   : Utility.getLang(module_name, 'NOMECOMPLETO'),
        searchable: true,
        data      : 'nomecompleto'
      };

      cols.push(col);

      col = {
        'title'   : Utility.getLang(module_name, 'USERNAME'),
        searchable: true,
        data      : 'username'
      };

      cols.push(col);

      col = {
        'title'   : Utility.getLang(module_name, 'EMAIL'),
        searchable: true,
        data      : 'email'
      };

      cols.push(col);

      col = {
        'title'   : Utility.getLang(module_name, 'GRUPPO'),
        searchable: true,
        data      : 'gruppo_string'
      };

      cols.push(col);

      // placeholder per assunzione permessi
      if(SUPERADMIN) {
        col = {};
        cols.push(col);
      }

      // placeholder per visibilita
      col = {};
      cols.push(col);

      // placeholder per cancellare
      col = {};
      cols.push(col);


      var columnDefs = [];

      var columnDef = {
        'targets'   : 0,
        'searchable': false,
        'orderable' : false,
        'className' : 'dt-body-center',
        'render'    : function(data, type, full, meta) {
          return '<a href="#" title="' + Utility.getLang('base', 'MODIFICA_RECORD') + '" class="btn btn-default edit" data-id="' + full.id + '"><i class="fa fa-pencil"></i></a>';
        }
      };

      columnDefs.push(columnDef);

      columnDef = {
        'targets'  : 1,
        'className': 'dt-body-center'
      };

      if(SUPERADMIN) {

        columnDef = {
          'targets'   : cols.length - 3,
          'searchable': false,
          'className' : 'dt-body-center',
          'orderable' : false,
          'render'    : function(data, type, full, meta) {
            var ret = '<a href="#" class="btn btn-info privileges';
            ret += '" title="' + Utility.getLang(module_name, 'ASSUMI_PRIVILEGI') + '" data-id="' + parseInt(full.id, 10) + '" data-title="' + full.nomecompleto + '"><i class="fa fa-user"></i></a>';
            return ret
          }
        };

        columnDefs.push(columnDef);

      }

      // pulsante per accendere e spegnere
      columnDef = {
        'targets'   : cols.length - 2,
        'searchable': false,
        'className' : 'dt-body-center',
        'orderable' : false,
        'render'    : function(data, type, full, meta) {
          var icon;
          var title;
          if(parseInt(full.attivo, 10) == 1) {
            icon = '<i class="fa fa-eye"></i>';
            title = Utility.getLang('base', 'DISABILITA');
          } else {
            icon = '<i class="fa fa-eye-slash"></i>';
            title = Utility.getLang('base', 'ABILITA');
          }
          var m = '<a href="#" title="' + title + '" class="btn btn-default visibility" data-id="' + parseInt(full.id, 10) + '" data-title="' + full.nomecompleto + '">';
          m += icon;
          m += '</a>';
          return m;
        }
      };
      columnDefs.push(columnDef);

      columnDef = {
        'targets'   : cols.length - 1,
        'searchable': false,
        'className' : 'dt-body-center',
        'orderable' : false,
        'render'    : function(data, type, full, meta) {
          var ret = '<a href="#" class="btn btn-default delete';
          if(parseInt(full.cancellabile, 10) == 0) {
            ret += ' disabled';
          }
          ret += '" title="' + Utility.getLang('base', 'ELIMINA_RECORD') + '" data-id="' + parseInt(full.id, 10) + '" data-title="' + full.nomecompleto + '"><i class="fa fa-trash"></i></a>';
          return ret
        }
      };

      columnDefs.push(columnDef);

      $dt = $('#dataTable').DataTable({
          'language'      : {
            url: main_host + 'bower_components/datatables-plugins/i18n/Italian.lang'
          },
          'sDom'          : 'tip',
          "order"         : [[2, "asc"]],
          'iDisplayLength': 15,
          'stateSave'     : true,
          responsive      : true,
          ajax            : {
            method: 'post',
            url   : main_host + 'admin/' + module_name + '/getlist/',
          },
          columns         : cols,
          'columnDefs'    : columnDefs
        }
      );

    default:
      break;

  }
});