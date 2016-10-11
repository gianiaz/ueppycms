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
module_name = 'mails';

$(function() {

  switch(Utility.getUrlParam(1, rel_root)) {

    case 'new':

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

      var external_plugins = Ueppy.tinyMCEOpts[LEVEL]['external_plugins'];
      if(!external_plugins) {
        external_plugins = {};
      }
      external_plugins['mailfield'] = "/lib/tinymce/plugins/mailfield/plugin.min.js"

      var toolbar = Ueppy.tinyMCEOpts[LEVEL]['toolbar'];
      Utility.log(toolbar);
      toolbar += ' | mailfield';

      Ueppy.tinyMCESetOpts({
        'main_host'         : main_host,
        'relative_urls'     : false,
        'remove_script_host': false,
        'convert_urls'      : false,
        'toolbar'           : toolbar,
        'external_plugins'  : external_plugins,
        'selector'          : 'textarea.mce'
      });

      Ueppy.tinyMCEInit();

      break;

    case 'insert':

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
        'title'    : Utility.getLang(module_name, 'DESCRIZIONE'),
        searchable : true,
        data       : 'descrizione',
        'className': 'dt3'
      };

      cols.push(col);

      var classeOggetto = 'dt4-1';

      if(SUPERADMIN) {
        classeOggetto = 'dt4-2';
      }

      col = {
        'title'    : Utility.getLang(module_name, 'OGGETTO'),
        searchable : true,
        data       : 'oggetto',
        'className': classeOggetto
      };

      cols.push(col);

      if(SUPERADMIN) {
        // placeholder per cancellare
        col = {
          'title'    : Utility.getLang('base', 'ELIMINA'),
          'className': 'dt5  dt-body-center'
        };
        cols.push(col);
      }

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

      if(SUPERADMIN) {
        colDefs.push({
          'targets'   : cols.length - 1,
          'searchable': false,
          'className' : 'dt-body-center',
          'orderable' : false,
          'render'    : function(data, type, full, meta) {
            return '<a href="#"  title="' + Utility.getLang('base', 'ELIMINA_RECORD') + '" class="btn delete" data-id="' + parseInt(full.id, 10) + '" data-title="' + full.nome + '"><i class="fa fa-trash"></i></a>';
          }
        });

      }

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
          'columnDefs'    : colDefs
        }
      );

      break;

  }
});