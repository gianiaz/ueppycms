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
var module_name = 'tinymcetpl';
var defaction = '';
$().ready(function() {
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

      Utility.tabIndexInit();

      Ueppy.tinyMCESetOpts({
        'main_host'            : main_host,
        extended_valid_elements: "div[class|id|style],+strong[class],+i[class]",
        'selector'             : 'textarea'
      });
      Ueppy.tinyMCEInit();

      break;

    case null:
    case '#':

      var cols = [];

      var col = {
        'title'     : Utility.getLang('base', 'MODIFICA'),
        'searchable': false,
        'className' : 'dt0 dt-body-center'
      };
      cols.push(col);

      col = {
        'title'    : Utility.getLang(module_name, 'FILE'),
        searchable : true,
        data       : 'file',
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
        'title'    : Utility.getLang(module_name, 'DESCRIZIONE'),
        searchable : true,
        data       : 'descrizione',
        'className': 'dt3'
      };

      cols.push(col);

      // placeholder per cancellare
      col = {
        'title'    : Utility.getLang('base', 'ELIMINA'),
        'className': 'dt4 dt-body-center'
      };
      cols.push(col);

      $('#dataTable').on('init.dt', function(e, settings) {

        var api = new $.fn.dataTable.Api(settings);
        var state = api.state.loaded();

        $('<tr class="searchbar"></tr>').appendTo($('#dataTable thead'));
        var idx = 0;
        $('#dataTable thead tr:first th').each(function() {
          var title = $.trim($('#dataTable thead tr:first th').eq($(this).index()).text());

          if(typeof cols[idx]['checkbox'] !== 'undefined') {
            $('<th class="dt-head-center"><input type="checkbox" class="selAll" /></th>').appendTo($('.searchbar'));
          } else {
            if(cols[idx]['searchable']) {
              var searchedBefore = '';
              if(state && typeof state['columns'][idx]['search']['search'] !== 'undefined') {
                searchedBefore = state.columns[idx]['search']['search'];
              }
              $('<th><input class="form-control input-sm" type="text" placeholder="' + title + '..." value="' + searchedBefore + '" /></th>').appendTo($('.searchbar'));
            } else {
              $('<th>&nbsp;</th>').appendTo($('.searchbar'));
            }
          }
          idx++;
        });

        $('.searchbar').find('input').on('keyup change', function() {
          var index = $('.searchbar').find('th').index($(this).closest('th'));
          if(typeof(cols[index]['checkbox']) == 'undefined') {
            var that = $dt.columns(index);
            if(that.search() !== this.value) {
              that
                .search(this.value)
                .draw();
            }
          }
        });

      });

      var colDefs = [
        {
          'targets'   : 0,
          'searchable': false,
          'orderable' : false,
          'className' : 'dt-body-center',
          'render'    : function(data, type, full, meta) {
            return '<a href="#" title="' + Utility.getLang('base', 'MODIFICA_RECORD') + '" class="btn btn-default edit" data-id="' + full.id + '"><i class="fa fa-pencil"></i></a>';
          }
        }
      ];

      colDefs.push({
        'targets'   : cols.length - 1,
        'searchable': false,
        'className' : 'dt-body-center',
        'orderable' : false,
        'render'    : function(data, type, full, meta) {
          return '<a href="#"  title="' + Utility.getLang('base', 'ELIMINA_RECORD') + '" class="btn btn-default delete" data-id="' + parseInt(full.id, 10) + '" data-title="' + full.nome + '"><i class="fa fa-trash"></i></a>';
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

    default:

      $('#newbtn').click(function(e) {
        e.preventDefault();
        newAction('new');
      });

      params = [{chiave: 'cmd', valore: module_name.toLocaleUpperCase() + '.GETLIST'}];

      delFunction = newFunction = switchvisibilityFunction = function(obj, action) {
        gotoAction(obj, action);
      };

      cols = [];

      col = {
        'campo'       : 'file',
        'titolo'      : Utility.getLang(module_name, 'FILE'),
        'classe'      : 'col0',
        'actionclass' : 'new',
        'sortable'    : false,
        'hasFilter'   : false,
        'clickHandler': newFunction,
        'htmltitle'   : Utility.getLang('base', 'EDIT')
      };

      cols[cols.length] = col;

      col = {
        'campo'       : 'nome',
        'titolo'      : Utility.getLang(module_name, 'NOME'),
        'sortable'    : false,
        'hasFilter'   : false,
        'classe'      : 'col1',
        'actionclass' : 'new',
        'clickHandler': newFunction,
        'htmltitle'   : Utility.getLang('base', 'EDIT')
      };

      cols[cols.length] = col;

      col = {
        'campo'       : 'descrizione',
        'titolo'      : Utility.getLang(module_name, 'DESCRIZIONE'),
        'sortable'    : false,
        'hasFilter'   : false,
        'classe'      : 'col2',
        'actionclass' : 'new',
        'clickHandler': newFunction,
        'htmltitle'   : Utility.getLang('base', 'EDIT')
      };

      cols[cols.length] = col;

      col = {
        'campo'       : '',
        'titolo'      : '',
        'classe'      : 'col3',
        'tipo'        : 'action',
        'htmltitle'   : Utility.getLang('base', 'DELETE_ELEMENT'),
        'clickHandler': delFunction,
        'img0'        : rel_root + 'images/trash.png',
        'img1'        : rel_root + 'images/trash_disabled.png',
        'actionclass' : 'del'
      };

      cols[cols.length] = col;

      $at = $('#ajaxtable').ajaxtable({
        formUrl: '', url: main_host + 'ajax.php',
        params : params,
        cols   : cols,
        filters: eval("(" + $('#filters_values').val() + ")")
      });

  }
});