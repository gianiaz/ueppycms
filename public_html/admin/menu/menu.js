/********************/
/*** UeppyCMS 4.0 ***/
/********************/
/*** v. 1.00      ***/
/********************/
/**  CHANGELOG     **/
/************************************************************************************************/
/** v.1.00 (06/10/2015)                                                                        **/
/** - Versione stabile                                                                         **/
/**                                                                                            **/
/************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                    **/
/** copyright: Ueppy s.r.l                                                                     **/
/************************************************************************************************/

var module_name = 'menu';

$(function() {

    switch(Utility.getUrlParam(1, rel_root)) {

      case 'insert':
        break;

      case 'new':

        Utility.tabIndexInit();

        $(document).on('click', '#cambiagenitore', function(e) {
          e.preventDefault();

          var data = {
            'SELEZIONA': Utility.getLang(module_name, 'SELEZIONA'),
            'CANCEL'   : Utility.getLang('base', 'CANCEL'),
            'title'    : Utility.getLang(module_name, 'MODIFICA_GENITORE')
          };

          $(document).on('click', '[href^="#node-"]', function(e) {
            e.preventDefault();
          });

          Utility.template({
            name           : 'treemenu',
            path           : '/admin/menu/',
            callback       : function() {
              var $modal = $('.modal').modal();

              $modal.on('hidden.bs.modal', function(e) {
                $modal.remove();
              });

              $modal.find('.btn-confirm').on('click', function(e) {
                e.preventDefault();
                var $tree = $('#tree');
                var res = $tree.treeview('getSelected');
                var selectedNode = res[0];
                var id = selectedNode['href'].replace('#node-', '');

                $('#genitore').val(id);
                var percorso = [];
                percorso.push(selectedNode.text);

                while(typeof selectedNode.parentId != 'undefined') {
                  selectedNode = $tree.treeview('getParent', selectedNode);
                  percorso.push(selectedNode.text);
                }
                percorso.reverse();

                var btnText = '<i class="fa fa-pencil"></i> ' + percorso.join('&nbsp;&gt;&nbsp;');

                $('#cambiagenitore').html(btnText);

                $modal.modal('hide').data('bs.modal', null);
              });

              var data = {
                exclude: $('#id').val()
              };
              $.ajax({
                type    : "POST",
                url     : main_host + 'admin/' + module_name + '/get_parents/',
                data    : data,
                dataType: "json",
                success : function(ret_data) {
                  if(parseInt(ret_data.result, 10) == 1) {
                    var $tree = $('#tree');
                    $tree.treeview({
                      enableLinks: true,
                      data       : ret_data.data
                    });

                    $tree.treeview('expandAll', {silent: true});

                    var $a = $('[href="#node-' + $('#genitore').val());
                    var $li = $a.closest('li');
                    var nodeId = $li.data('nodeid');

                    $tree.treeview('selectNode', nodeId);

                  } else {
                    Utility.alert({message: ret_data.error});
                  }
                }
              });
            },
            elem           : $('body'),
            typeofinjection: 'append', // append, prepend, html
            data           : data
          });

        });

        beforeSubmit = function() {
          return true;
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

        break;

      case 'sort':

        $('.panel-menu').each(function() {
          if($(this).find('li').length < 2) {
            $(this).remove();
          } else {
            var $panelBody = $(this).find('.panel-body');
            var $dd = $panelBody.find('.dd');
            $dd.nestable({group: $panelBody.data('group')}).on('change', updateOutput);
          }
        });

        updateOutput();

        afterSubmit = function(ret_data, form) {
          if(parseInt(ret_data.result, 10) == 1) {
            Ueppy.saveSuccess([]);
          } else {
            Utility.alert({'message': ret_data.error});
          }
        }

        break;

      case 'sort-1':

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

      default:

        // placeholder per modifica
        var col = {
          'title'     : Utility.getLang('base', 'MODIFICA'),
          'className' : 'dt0 dt-body-center',
          'searchable': false
        };
        cols.push(col);

        col = {
          'title'    : 'ID',
          'className': 'dt1 dt-body-center',
          searchable : true,
          data       : 'id'
        };

        cols.push(col);

        col = {
          'title'    : Utility.getLang(module_name, 'DICITURA'),
          searchable : true,
          'className': 'dt2',
          data       : 'dicitura_it'
        };

        cols.push(col);

        col = {
          'title'    : Utility.getLang(module_name, 'INCLUSIONE'),
          'className': 'dt3',
          searchable : true,
          data       : 'nomefile'
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
                  return '<a href="#" title="' + Utility.getLang('base', 'MODIFICA_RECORD') + '" class="btn btn-default edit" data-id="' + full.id + '"><i class="fa fa-pencil"></i></a>';
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
                  var m = '<a href="#" class="btn btn-default visibility" data-id="' + parseInt(full.id, 10) + '" data-title="' + full.dicitura_it + '"';
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
                  return '<a href="#"  title="' + Utility.getLang('base', 'ELIMINA_RECORD') + '" class="btn btn-default delete" data-id="' + parseInt(full.id, 10) + '" data-title="' + full.dicitura_it + '"><i class="fa fa-trash"></i></a>';
                }
              }
            ]
          }
        );

    }
  }
);


var updateOutput = function() {

  var data = {};

  $('.panel-menu .panel-body').each(function() {
    var group = $(this).data('group');
    var list = $(this).find('.dd');
    data[group] = window.JSON.stringify(list.nestable('serialize'));
  })

  $('#neworder').val(window.JSON.stringify(data));

};