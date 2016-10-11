var module_name = 'home';

$(function() {
  switch(Utility.getUrlParam(1, rel_root)) {
    case 'new':

      Utility.tabIndexInit();

      beforeSerialize = function() {
        tinyMCE.triggerSave();
      }

      afterSubmit = function(ret_data, $form) {
        Utility.unlockScreen();
        if(parseInt(ret_data.result, 10) === 1) {
          Ueppy.saveSuccess(ret_data.dati);
        } else {
          Ueppy.showFormErrors(ret_data.errors, ret_data.wrongs);
        }

      }

      Ueppy.tinyMCESetOpts({
        'main_host': main_host,
        'width'    : '100%',
        'selector' : 'textarea.mce'
      });
      Ueppy.tinyMCEInit();


      break;

    case null:
    case '#':

      cols = [];

      // placeholder per modifica
      var col = {
        'title'     : Utility.getLang('base', 'MODIFICA'),
        'className' : 'dt0',
        'searchable': false
      };
      cols.push(col);

      col = {
        'title'    : 'ID',
        searchable : true,
        'className': 'dt1',
        data       : 'id'
      };

      cols.push(col);

      col = {
        'title'    : Utility.getLang(module_name, 'HTML_ID'),
        searchable : true,
        'className': 'dt2',
        data       : 'htmlid'
      };

      cols.push(col);

      col = {
        'title'    : Utility.getLang(module_name, 'TESTO'),
        searchable : true,
        'className': 'dt3',
        data       : 'testo'
      };

      cols.push(col);

      $dt = $('#dataTable').DataTable({
          'order'         : [[1, "asc"]],
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
            }
          ]
        }
      );

      break;
  }
});