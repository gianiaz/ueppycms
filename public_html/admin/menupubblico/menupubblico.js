/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/************************************************************************************************/
/** v.1.00 (11/05/2016)                                                                        **/
/** - Versione stabile                                                                         **/
/**                                                                                            **/
/************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                    **/
/** copyright: Ueppy s.r.l                                                                     **/
/************************************************************************************************/
var module_name = 'menupubblico';

$(function() {

    switch(Utility.getUrlParam(1, rel_root)) {

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

      case 'new':

        afterSubmit = function(ret_data, $form) {
          Utility.unlockScreen();
          if(parseInt(ret_data.result, 10) == 1) {
            Utility.log(ret_data.dati);
            Ueppy.saveSuccess(ret_data.dati);
          } else {
            Ueppy.showFormErrors(ret_data.errors, ret_data.wrongs);
          }
        }

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

        break;
      
      case 'seo':


        col = {
          'title'   : 'ID',
          searchable: true,
          className : 'seo0',
          data      : 'id'
        };

        cols.push(col);

        col = {
          'title'   : Utility.getLang(module_name, 'LINGUA'),
          searchable: true,
          className : 'seo1',
          data      : 'lingua'
        };

        cols.push(col);

        col = {
          'title'   : Utility.getLang(module_name, 'TITOLO'),
          searchable: true,
          className : 'seo2',
          data      : 'titolo'
        };

        cols.push(col);

        col = {
          'title'   : Utility.getLang('base', 'HTMLTITLE'),
          searchable: true,
          className : 'seo3 inline-edit htmltitle',
          data      : 'htmltitle'
        };

        cols.push(col);

        col = {
          'title'   : Utility.getLang('base', 'DESCRIPTION'),
          searchable: true,
          className : 'seo4 inline-edit description',
          data      : 'description'
        };

        cols.push(col);

        $('#dataTable').on('draw.dt', function() {

          $('.inline-edit').editable(function(value, settings) {
            var $tr = $(this).closest('tr');
            var data = {
              'id'  : $tr.data('id'),
              'lang': $tr.data('lang'),
              value : value
            };
            if($(this).hasClass('htmltitle')) {
              data.type = 'htmltitle';
            } else {
              data.type = 'description';
            }
            $.ajax({
              type    : "POST",
              url     : main_host + 'admin/' + module_name + '/save_seo_key/',
              data    : data,
              dataType: "json",
              success : function(ret_data) {
                if(parseInt(ret_data.result, 10) != 1) {
                  Utility.alert({message: ret_data.error});
                }
              }
            });
            return value;
          }, {'onblur': 'submit', tooltip: '', placeholder: '', type: 'textarea'});

          $('.inline-edit').on('keydown', 'textarea', function(e) {
            var keyCode = e.keyCode || e.which;

            if(keyCode == 9 || keyCode == 39) {
              e.preventDefault();
              $(this).blur();
              var $td = $(this).closest('td');
              var $next = $td.next('.inline-edit');
              if(!$next.length) {
                $tr = $td.closest('tr');
                $nextTr = $tr.next('tr');
                $next = $nextTr.find('.inline-edit:first');
              }
              if($next.length) {
                $next.trigger('click');
              }
            }

            if(keyCode == 40) {
              e.preventDefault();
              var $td = $(this).closest('td');
              $tr = $td.closest('tr');
              var idx = $tr.find('td').index($td);
              $nextTr = $tr.next('tr');
              $tdProssimaRiga = $nextTr.find('td:eq(' + idx + ')');
              $next = $tdProssimaRiga;
              if($next.length) {
                $(this).blur();
                $next.trigger('click');
              }
            }

            if(keyCode == 37) {
              e.preventDefault();
              var $td = $(this).closest('td');
              var $prevTd = $td.prev('.inline-edit');
              if(!$prevTd.length) {
                $tr = $td.closest('tr');
                $tr.css({'border': '1px solid red'});
                $prevTr = $tr.prev('tr');
                $prevTd = $prevTr.find('.inline-edit:last');
              }
              if($prevTd.length) {
                $(this).blur();
                $prevTd.trigger('click');
              }
            }

            if(keyCode == 38) {
              e.preventDefault();
              var $td = $(this).closest('td');
              var $tr = $td.closest('tr');
              var idx = $tr.find('td').index($td);
              var $prevTr = $tr.prev('tr');
              if($prevTr.find('td').length) {
                var $tdRigaPrecedente = $prevTr.find('td:eq(' + idx + ')');
                if($tdRigaPrecedente.length) {
                  $tdRigaPrecedente.trigger('click');
                }

              }
            }

            Utility.log(keyCode);

          });

          var $htmlTitle = $('.htmltitle:eq(1)');
          $htmlTitle.attr('data-demo', 1);
          $htmlTitle.data('demo-msg', Utility.getLang('base', 'DEMO_HTMLTITLE'));
          $htmlTitle.data('demo-step', 1);

          var $description = $('.description:eq(1)');
          $description.attr('data-demo', 1);
          $description.data('demo-msg', Utility.getLang('base', 'DEMO_DESCRIPTION'));
          $description.data('demo-step', 2);

        });

        var firstRow = true;

        $dt = $('#dataTable').DataTable({
            'language'      : {
              url: main_host + 'bower_components/datatables-plugins/i18n/Italian.lang'
            },
            createdRow      : function(row, data, dataIndex) {
              $(row).data('lang', data.lang);
              $(row).data('id', data.id);
              if(firstRow) {
                firstRow = false;
              }
            },
            'sDom'          : 'tip',
            "order"         : [[0, "asc"], [1, 'asc']],
            'iDisplayLength': 15,
            'stateSave'     : true,
            responsive      : true,
            ajax            : {
              method: 'post',
              url   : main_host + 'admin/' + module_name + '/getseo/',
            },
            columns         : cols,
            'columnDefs'    : colDefs
          }
        );
        break;

      case null:
      case '#':

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
          'className': 'dt2',
          searchable : true,
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

        col = {
          'title'    : Utility.getLang(module_name, 'TEMPLATE'),
          'className': 'dt4',
          searchable : true,
          data       : 'template'
        };

        cols.push(col);

        if(SUPERADMIN) {
          // placeholder per visibilita
          col = {'className': 'dt5 dt-body-center'};
          cols.push(col);

          // placeholder per cancellare
          col = {'className': 'dt6 dt-body-center'};
          cols.push(col);
        }

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

        if(SUPERADMIN) {
          var colDefinitition = {
            'targets'   : cols.length - 2,
            'searchable': false,
            'className' : 'dt-body-center',
            'orderable' : false,
            'render'    : function(data, type, full, meta) {

              var m = '<a href="#" class="btn btn-default visibility" data-id="' + parseInt(full.id, 10) + '" data-title="' + full.dicitura_it + '"';
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

          colDefs.push(colDefinitition);
          var colDefinitition = {
            'targets'   : cols.length - 1,
            'searchable': false,
            'className' : 'dt-body-center',
            'orderable' : false,
            'render'    : function(data, type, full, meta) {
              return '<a href="#"  title="' + Utility.getLang('base', 'ELIMINA_RECORD') + '" class="btn btn-default delete" data-id="' + parseInt(full.id, 10) + '" data-title="' + full.dicitura_it + '"><i class="fa fa-trash"></i></a>';
            }
          };


          colDefs.push(colDefinitition);
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