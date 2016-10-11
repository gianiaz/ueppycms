/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (11/05/2016)                                                                          **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
var module_name = 'languages';
var debug_ajax = false;
$(function() {
  switch(Utility.getUrlParam(1, rel_root)) {

    case 'new':

      Utility.tabIndexInit();

      $(document).on('change', '#sigla', function(e) {
        if($(this).val() != '0') {
          $('#estesa').val($.trim($(this).find('option:selected').text().split('-').pop()));
        }
      });

      afterSubmit = function(ret_data, $form) {
        Utility.unlockScreen();
        if(parseInt(ret_data.result, 10) === 1) {
          Ueppy.saveSuccess(ret_data.dati);
        } else {
          Ueppy.showFormErrors(ret_data.errors, ret_data.wrongs);
        }

      }

      $(document).on('change', '#attivo_admin', function(e) {
        if(parseInt($(this).val(), 10) == 0) {
          $('#attivo').val('0');
        }
      });

      break;

    case null:
    case '#':

      // placeholder per modifica
      var col = {
        'title'     : Utility.getLang('base', 'MODIFICA'),
        'searchable': false,
        'className' : 'dt0 dt-body-center',
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
        'title'    : Utility.getLang(module_name, 'SIGLA'),
        searchable : true,
        data       : 'sigla',
        'className': 'dt2 dt-body-center'
      };

      cols.push(col);

      col = {
        'title'   : Utility.getLang(module_name, 'ESTESA'),
        searchable: true,
        className : 'dt3',
        data      : 'estesa'
      };

      cols.push(col);

      // placeholder per visibilita
      col = {'className': 'dt4 dt-body-center'};
      cols.push(col);

      // placeholder per cancellare
      col = {'className': 'dt5 dt-body-center'};
      cols.push(col);

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
                var m = '<a href="#" class="btn visibility' + disabled + '" data-id="' + parseInt(full.id, 10) + '" data-title="' + full.estesa + '"';
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
                return '<a href="#"  title="' + Utility.getLang('base', 'ELIMINA_RECORD') + '" class="btn delete" data-id="' + parseInt(full.id, 10) + '" data-title="' + full.estesa + '"><i class="fa fa-trash"></i></a>';
              }
            }
          ]
        }
      );


  }
});