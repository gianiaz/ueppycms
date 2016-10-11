/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (21/05/16, 15.03)                                                                     **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
var module_name = 'schedule';

$(function() {
  switch(Utility.getUrlParam(1, rel_root)) {
    case 'new':

      Utility.tabIndexInit();

      afterSubmit = function(ret_data, $form) {
        Utility.unlockScreen();
        if(parseInt(ret_data.result, 10) === 1) {
          Ueppy.saveSuccess(ret_data.dati);
        } else {
          Ueppy.showFormErrors(ret_data.errors, ret_data.wrongs);
        }
      }

      $('#giorno_del_mese').on('change', function(e) {
        if($(this).val() == 'TUTTI') {
          $('#giorni_del_mese').prop('disabled', true);
        } else {
          $('#giorni_del_mese').removeAttr('disabled');
        }
      });

      $('#giorno_del_mese').trigger('change');

      $('#minuto_dell_ora').on('change', function(e) {
        if($(this).val() == 'TUTTI') {
          $('#minuti_dell_ora').prop('disabled', true);
        } else {
          $('#minuti_dell_ora').removeAttr('disabled');
        }
      });
      $('#minuto_dell_ora').trigger('change');

      $('#ora_del_giorno').on('change', function(e) {
        if($(this).val() == 'TUTTE') {
          $('#ore_del_giorno').prop('disabled', true);
        } else {
          $('#ore_del_giorno').removeAttr('disabled');
        }
      });
      $('#ora_del_giorno').trigger('change');

      break;

    case 'logs':

      $('#emptyLogs').click(function(e) {
        e.preventDefault();

        var that = this;

        var opts = {
          title    : Utility.getLang('base', 'WARNING'),
          message  : Utility.getLang(module_name, 'SVUOTA_LOGS'),
          okLbl    : Utility.getLang('base', 'OK'),
          cancelLbl: Utility.getLang('base', 'CANCEL'),
          onOk     : function() {
            window.location.assign($(that).attr('href'));
          }
        };

        Utility.confirm(opts);

      });

      var col = {
        'title'   : Utility.getLang(module_name, 'DATAORA'),
        searchable: true,
        sortable  : true,
        className : 'dl0',
        data      : 'created_at'
      };

      cols.push(col);

      col = {
        'title'   : Utility.getLang(module_name, 'FILE'),
        searchable: true,
        className : 'dl1',
        sortable  : false,
        data      : 'file'
      };

      cols.push(col);

      col = {
        'title'   : Utility.getLang(module_name, 'OPERATOR'),
        sortable  : false,
        searchable: true,
        className : 'dl2',
        data      : 'autore'
      };

      cols.push(col);

      col = {
        'title'   : Utility.getLang(module_name, 'TEXT'),
        sortable  : false,
        className : 'dl3',
        searchable: true,
        data      : 'text'
      };

      cols.push(col);

      $.fn.dataTable.moment('DD/MM/YYYY HH:mm');

      $dt = $('#dataTable').DataTable({
          'language'      : {
            url: main_host + 'bower_components/datatables-plugins/i18n/Italian.lang'
          },
          'sDom'          : 'tip',
          "order"         : [[0, "desc"]],
          'iDisplayLength': 15,
          'stateSave'     : true,
          responsive      : true,
          ajax            : {
            method: 'post',
            url   : main_host + 'admin/' + module_name + '/get_logs/'
          },
          columns         : cols
        }
      );
      break;
    case null:
    case '#':

      cols = [];

      // placeholder per modifica
      var col = {
        'title'     : Utility.getLang('base', 'MODIFICA'),
        'className' : 'dt0  dt-body-center',
        'searchable': false
      };
      cols.push(col);

      col = {
        'title'    : 'ID',
        searchable : true,
        'className': 'dt1  dt-body-center',
        data       : 'id'
      };

      cols.push(col);

      col = {
        'title'    : Utility.getLang(module_name, 'COMANDO'),
        searchable : true,
        'className': 'dt2',
        data       : 'comando'
      };

      cols.push(col);

      col = {
        'title'    : Utility.getLang(module_name, 'GIORNO'),
        searchable : true,
        'className': 'dt3',
        data       : 'giorno'
      };

      cols.push(col);

      col = {
        'title'    : Utility.getLang(module_name, 'ORA'),
        searchable : true,
        'className': 'dt4',
        data       : 'ora'
      };

      cols.push(col);

      col = {
        'title'    : Utility.getLang(module_name, 'MINUTO'),
        searchable : true,
        'className': 'dt5',
        data       : 'minuto'
      };

      cols.push(col);

      // placeholder per visibilita
      col = {'className': 'dt6  dt-body-center'};
      cols.push(col);

      // placeholder per cancellare
      col = {
        'className': 'dt7 dt-body-center'
      };

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
                var m = '<a href="#" class="btn btn-warning visibility' + disabled + '" data-id="' + parseInt(full.id, 10) + '" data-title="' + full.nome + '"';
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
                var ret = '<a href="#" class="btn btn-danger delete';
                if(parseInt(full.cancellabile, 10) == 0) {
                  ret += ' disabled';
                }
                ret += '" title="' + Utility.getLang('base', 'ELIMINA_RECORD') + '" data-id="' + parseInt(full.id, 10) + '" data-title="' + full.comando + '"><i class="fa fa-trash"></i></a>';
                return ret
              }
            }
          ]
        }
      );

      break;
  }
});