/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/************************************************************************************************/
/** v.1.00 (09/05/2016)                                                                        **/
/** - Versione stabile                                                                         **/
/**                                                                                            **/
/************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                    **/
/** copyright: Ueppy s.r.l                                                                     **/
/************************************************************************************************/
var module_name = 'news_category';

$(function() {
  switch(Utility.getUrlParam(1, rel_root)) {

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

    case 'new':

      Utility.tabIndexInit();

      $('#newbtn').click(function(e) {
        location.href = location.href;
      });

      beforeSerialize = function() {
        tinyMCE.triggerSave();
        return true;
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
        'selector' : 'textarea.mce'
      });

      Ueppy.tinyMCEInit();

      break;

    case '#':
    case null:

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
        'title'    : Utility.getLang(module_name, 'NAME'),
        searchable : true,
        data       : 'name_it',
        'className': 'dt2'
      };

      cols.push(col);

      col = {
        'title'    : Utility.getLang(module_name, 'HREF'),
        data       : 'href_it',
        'className': 'dt3'
      };

      cols.push(col);

      col = {
        'title'    : Utility.getLang(module_name, 'ORDINE'),
        searchable : false,
        data       : 'ordine',
        'className': 'dt4'
      };

      cols.push(col);

      // placeholder per visibilita
      col = {'className': 'dt5'};
      cols.push(col);

      // placeholder per cancellare
      col = {
        'title'    : Utility.getLang('base', 'ELIMINA'),
        'className': 'dt6'
      };
      cols.push(col);
      
      var colDefs = [
        {
          'targets'   : 0,
          'searchable': false,
          'orderable' : false,
          'className' : 'dt-body-center',
          'render'    : function(data, type, full, meta) {
            return '<a href="#" class="btn edit" data-id="' + full.id + '"><i class="fa fa-pencil"></i></a>';
          }
        },
        {
          'targets'  : 1,
          'className': 'dt-body-center'
        }
      ];

      // pulsante per accendere e spegnere
      columnDef = {
        'targets'   : cols.length - 2,
        'searchable': false,
        'className' : 'dt-body-center',
        'orderable' : false,
        'render'    : function(data, type, full, meta) {
          var m = '<a href="#" class="btn visibility" data-id="' + parseInt(full.id, 10) + '" data-title="' + full.nomecompleto + '"';
          var icon, title;
          if(parseInt(full.attivo, 10) == 1) {
            icon = '<i class="fa fa-eye"></i>';
            title = Utility.getLang('base', 'DISABILITA');
          } else {
            icon = '<i class="fa fa-eye-slash"></i>';
            title = Utility.getLang('base', 'ABILITA');
          }
          m += ' title="' + title + '">' + icon;
          m += '</a>';
          return m;
        }
      };
      colDefs.push(columnDef);

      colDefs.push({
        'targets'   : cols.length - 1,
        'searchable': false,
        'className' : 'dt-body-center',
        'orderable' : false,
        'render'    : function(data, type, full, meta) {
          return '<a href="#" title="' + Utility.getLang('base', 'ELIMINA_RECORD') + '" class="btn delete" data-id="' + parseInt(full.id, 10) + '" data-title="' + full.name_it + '"><i class="fa fa-trash"></i></a>';
        }
      });

      $dt = $('#dataTable').DataTable({
          'language'      : {
            url: main_host + 'bower_components/datatables-plugins/i18n/Italian.lang'
          },
          'sDom'          : 'tip',
          "order"         : [[4, "asc"]],
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