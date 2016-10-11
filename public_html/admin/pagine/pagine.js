/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (20/05/16, 7.00)                                                                      **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
var module_name = 'pagine';
var tempPercorso = false;
var tree, tempIdGenitore = false;
var t;
$(function() {

  switch(Utility.getUrlParam(1, rel_root)) {

    case 'del':
    case 'del_selected':
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
          "order"         : [[0, "desc"], [1, "asc"]],
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

    case 'comments':

      $(document).on('click', '.edit_commento', function(e) {

        e.preventDefault();
        var id = $(this).data('id');
        var data = {id: id};

        $.ajax({
          type    : "POST",
          url     : main_host + 'admin/' + module_name + '/load_commento/',
          data    : data,
          dataType: "json",
          success : function(ret_data) {
            if(parseInt(ret_data.result, 10) == 1) {

              var opts = {
                TITLE   : Utility.getLang(module_name, 'EDIT_COMMENTO'),
                SAVE    : Utility.getLang('base', 'SALVA'),
                CANCEL  : Utility.getLang('base', 'CANCEL'),
                size    : 'modal-lg',
                content : '',
                callback: function($modal) {

                  $modal.on('hide.bs.modal', function(e) {
                    tinyMCE.remove();
                  });

                  $('.modal-body').html($('#formEditCommento').html());
                  Utility.log(ret_data);
                  $modal.find('#commenti_id').val(ret_data.commento.id);
                  $modal.find('#nome').val(ret_data.commento.nome);
                  $modal.find('#valido').val(ret_data.commento.valido);
                  $modal.find('#commento').val(ret_data.commento.commento);
                  $modal.find('#email').val(ret_data.commento.email);
                  $modal.find('#commento').addClass('mce');
                  Ueppy.tinyMCESetOpts({
                    'main_host': main_host,
                    'readonly' : $('#ajaxForm').data('readonly'),
                    'selector' : 'textarea.mce'
                  });

                  Ueppy.tinyMCEInit();

                },
                onSave  : function($modal) {
                  tinyMCE.triggerSave();
                  $modal.find('form').ajaxSubmit({
                    'dataType': 'json',
                    success   : function(ret_data) {
                      if(parseInt(ret_data.result, 10) == 1) {
                        $('.modal').modal('hide');
                        $dt.ajax.reload(null, false);
                      } else {
                        Utility.alert({'message': ret_data.error});
                      }
                    }
                  });
                },
                onCancel: function() {
                  $('.modal').modal('hide');
                }
              };

              Utility.modalForm(opts);

            } else {
              Utility.alert({message: ret_data.error});
            }
          }
        });
      });


      $(document).on('click', '.delete_commento', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var title = $(this).data('title');
        Utility.confirm({
          'message': sprintf(Utility.getLang('base', 'VUOI_RIMUOVERE_ID'), title),
          'onOk'   : function() {

            var data = {id: id};
            $.ajax({
              type    : "POST",
              url     : main_host + 'admin/' + module_name + '/delete_commento/',
              data    : data,
              dataType: "json",
              success : function(ret_data) {
                if(parseInt(ret_data.result, 10) == 1) {
                  PNotify.removeAll();
                  new PNotify({
                    title   : Utility.getLang('base', 'ELEMENTO_CANCELLATO'),
                    text    : '',
                    type    : 'success',
                    nonblock: {
                      nonblock        : true,
                      nonblock_opacity: .2
                    }
                  });
                  $dt.ajax.reload(null, false);
                } else {
                  Utility.alert({message: ret_data.error});
                }
              }
            });
          }
        });
      });

      $(document).on('click', '.visibility_commento', function(e) {

        e.preventDefault();
        var id = $(this).data('id');
        var title = $(this).data('title');

        var data = {id: id};
        $.ajax({
          type    : "POST",
          url     : main_host + 'admin/' + module_name + '/visibility_commento/',
          data    : data,
          dataType: "json",
          success : function(ret_data) {
            if(parseInt(ret_data.result, 10) == 1) {
              $dt.ajax.reload(null, false);
            } else {
              Utility.alert({message: ret_data.error});
            }
          }
        });

      });

      cols = [];

      // placeholder per modifica
      var col = {
        'title'     : Utility.getLang('base', 'MODIFICA'),
        'searchable': false,
        className   : 'comm0 dt-body-center',
      };
      cols.push(col);

      col = {
        'title'   : 'ID',
        searchable: true,
        className : 'comm1',
        data      : 'id'
      };

      cols.push(col);

      col = {
        'title'   : Utility.getLang(module_name, 'AUTORE_COMMENTO'),
        searchable: true,
        className : 'comm2',
        data      : 'nome'
      };

      cols.push(col);

      col = {
        'title'   : Utility.getLang(module_name, 'EMAIL_COMMENTO'),
        searchable: true,
        className : 'comm3',
        data      : 'email'
      };

      cols.push(col);

      col = {
        'title'   : Utility.getLang(module_name, 'COMMENTO'),
        searchable: true,
        className : 'comm4',
        data      : 'commento'
      };

      cols.push(col);

      if($('#dataTable').data('menu_id') == 0) {

        col = {
          'title'   : Utility.getLang(module_name, 'TITOLO_PAGINA'),
          searchable: true,
          data      : 'titolo'
        };

        cols.push(col);

      }

      col = {
        'title'   : Utility.getLang(module_name, 'CREATO'),
        searchable: true,
        data      : 'created_at'
      };

      cols.push(col);

      // placeholder per visibilita
      col = {className: 'comm5 dt-body-center'};
      cols.push(col);

      // placeholder per cancellare
      col = {className: 'comm6 dt-body-center'};
      cols.push(col);

      var columnDefs = [];

      var columnDef = {
        'targets'   : 0,
        'searchable': false,
        'orderable' : false,
        'className' : 'dt-body-center',
        'render'    : function(data, type, full, meta) {
          return '<a href="#" title="' + Utility.getLang('base', 'MODIFICA_RECORD') + '" class="btn btn-primary edit_commento" data-id="' + full.id + '"><i class="fa fa-pencil"></i></a>';
        }
      };

      columnDefs.push(columnDef);

      columnDef = {
        'targets'  : 1,
        'className': 'dt-body-center'
      };

      // pulsante per accendere e spegnere
      columnDef = {
        'targets'   : cols.length - 2,
        'searchable': false,
        'className' : 'dt-body-center',
        'orderable' : false,
        'render'    : function(data, type, full, meta) {
          var icon;
          var title;
          if(parseInt(full.valido, 10) == 1) {
            icon = '<i class="fa fa-eye"></i>';
            title = Utility.getLang('base', 'DISABILITA');
          } else {
            icon = '<i class="fa fa-eye-slash"></i>';
            title = Utility.getLang('base', 'ABILITA');
          }
          var m = '<a href="#" title="' + title + '" class="btn btn-warning visibility_commento" data-id="' + parseInt(full.id, 10) + '" data-title="' + full.id + '">';
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
          var ret = '<a href="#" class="btn btn-danger delete_commento';
          if(parseInt(full.cancellabile, 10) == 0) {
            ret += ' disabled';
          }
          ret += '" title="' + Utility.getLang('base', 'ELIMINA_RECORD') + '" data-id="' + parseInt(full.id, 10) + '" data-title="' + full.id + '"><i class="fa fa-trash"></i></a>';
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
            url   : main_host + 'admin/' + module_name + '/getcommenti/',
            data  : {'menu_id': $('#dataTable').data('menu_id')}
          },
          columns         : cols,
          'columnDefs'    : columnDefs
        }
      );

      break;

    case 'new':
      
      $('.data').datetimepicker({
        locale  : ACTUAL_LANGUAGE,
        'format': 'DD/MM/YYYY'
      });

      /** SALVATAGGIO AUTOMATICO - INIZIO **/
      $(document).on('click', '#salvataggioAutomaticoBtn', function(e) {
        if($(this).hasClass('active')) {
          $(this).removeClass('active');
          clearInterval(t);

          PNotify.removeAll();
          new PNotify({
            title: Utility.getLang('base', 'TITLE_SALVATAGGIO_AUTOMATICO'),
            text : Utility.getLang('base', 'DISABILITATO_SALVATAGGIO_AUTOMATICO'),
            type : 'info'
          });

        } else {
          PNotify.removeAll();
          new PNotify({
            title: Utility.getLang('base', 'TITLE_SALVATAGGIO_AUTOMATICO'),
            text : Utility.getLang('base', 'TESTO_SALVATAGGIO_AUTOMATICO') + Ueppy.millisToStr(parseInt($(this).data('time'), 10) * 1000),
            type : 'info'
          });

          $(this).addClass('active');
          timerAutosave = parseInt($(this).data('time'), 10) * 1000;
          t = setInterval(function() {
            $('#ajaxForm').submit();
          }, timerAutosave);
        }
      });

      /** SALVATAGGIO AUTOMATICO - FINE **/

      beforeSerialize = function() {
        tinyMCE.triggerSave();
      }

      afterSubmit = function(ret_data, $form) {
        Utility.unlockScreen();
        if(parseInt(ret_data.result, 10) == 1) {
          Ueppy.saveSuccess(ret_data.dati);
          if(parseInt(ret_data.dati.id, 10) > 0) {
            $('#previewBtn').removeClass('disabled');
            $('#salvataggioAutomaticoBtn').removeClass('disabled');
            $('.tab-pane').each(function() {
              var l = $(this).attr('id').replace('scheda_', '');
              $(this).find('.lnkpg').attr('href', ret_data.dati.urls[l]).html(ret_data.dati.urls[l]);
            });

          }
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

      Ueppy.tinyMCESetOpts({
        'main_host': main_host,
        'width'    : '100%',
        'selector' : 'textarea'
      });
      Ueppy.tinyMCEInit();

      Utility.tabIndexInit();

      Ueppy.inizializzaPluginAllegati();
      break;
    
    case null:
    case '#':

      $(document).on('click', '.fdel', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var title = $(this).data('title');
        if($(this).find('.fa-trash').length) {
          Utility.confirm({
            'message': sprintf(Utility.getLang('base', 'VUOI_RIMUOVERE_LA_VOCE'), title),
            'onOk'   : function() {

              var data = {id: id};
              $.ajax({
                type    : "POST",
                url     : main_host + 'admin/' + module_name + '/fdel/',
                data    : data,
                dataType: "json",
                success : function(ret_data) {
                  if(parseInt(ret_data.result, 10) == 1) {
                    PNotify.removeAll();
                    new PNotify({
                      title   : Utility.getLang('base', 'ELEMENTO_CANCELLATO'),
                      text    : '',
                      type    : 'success',
                      nonblock: {
                        nonblock        : true,
                        nonblock_opacity: .2
                      }
                    });
                    $dt.ajax.reload(null, false);
                  } else {
                    Utility.alert({message: ret_data.error});
                  }
                }
              });
            }
          });

        } else {
          var data = {id: id};
          $.ajax({
            type    : "POST",
            url     : main_host + 'admin/' + module_name + '/fdel/',
            data    : data,
            dataType: "json",
            success : function(ret_data) {
              if(parseInt(ret_data.result, 10) == 1) {
                PNotify.removeAll();
                new PNotify({
                  title   : Utility.getLang('base', 'ELEMENTO_RIPRISTINATO'),
                  text    : '',
                  type    : 'success',
                  nonblock: {
                    nonblock        : true,
                    nonblock_opacity: .2
                  }
                });
                $dt.ajax.reload(null, false);
              } else {
                Utility.alert({message: ret_data.error});
              }
            }
          });

        }
      });

      $(document).on('click', '.clone', function(e) {

        var data = {id: $(this).data('id')};

        $.ajax({
          type    : "POST",
          url     : main_host + 'admin/' + module_name + '/copy/',
          data    : data,
          dataType: "json",
          success : function(ret_data) {
            if(parseInt(ret_data.result, 10) == 1) {
              $dt.ajax.reload(null, false);
            } else {
              Utility.alert({message: ret_data.error});
            }
          }
        });

      });

      $(document).on('click', '.comments', function(e) {

        e.preventDefault();

        Utility.postData({
          'action': main_host + 'admin/' + module_name + '/comments/',
          'data'  : {'id': $(this).data('id')}
        });

      });


      Utility.log(cols);
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
        'title'   : Utility.getLang(module_name, 'DICITURA'),
        searchable: true,
        data      : 'dicitura'
      };

      cols.push(col);

      col = {
        'title'   : Utility.getLang(module_name, 'GENITORE'),
        searchable: true,
        data      : 'parent'
      };

      cols.push(col);

      col = {
        'title'   : Utility.getLang(module_name, 'TEMPLATE'),
        searchable: true,
        data      : 'template'
      };

      cols.push(col);

      col = {
        'title'   : Utility.getLang(module_name, 'ATTIVO'),
        searchable: true,
        data      : 'attivo'
      };

      cols.push(col);

      if($('#dataTable').data('commenti') == 1) {

        // placeholder per commenti
        col = {
          'title': Utility.getLang(module_name, 'COMMENTI')
        };

        cols.push(col);

      }

      // placeholder per clone
      col = {
        'title': Utility.getLang('base', 'COPY')
      };
      cols.push(col);


      if(LEVEL >= 10) {
        // placeholder per cancellare
        col = {
          'title': Utility.getLang('base', 'ELIMINA')
        };
        cols.push(col);
      }

      if(LEVEL >= 20) {
        // placeholder per cancellare davvero
        col = {
          'title': Utility.getLang('base', 'ELIMINA')
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
        },
        {
          'targets'  : 1,
          'className': 'dt-body-center'
        },
        {
          'targets': 2
        },
        {
          'targets': 3
        },
        {
          'targets': 4
        },
        {
          'targets': 5
        }
      ];

      if($('#dataTable').data('commenti') == 1) {
        colDefs.push({
          'targets'   : colDefs.length,
          'searchable': false,
          'className' : 'dt-body-center',
          'orderable' : false,
          'render'    : function(data, type, full, meta) {
            return '<a href="#" title="' + Utility.getLang('base', 'GESTISCI_COMMENTI') + '" class="btn btn-info comments" data-id="' + parseInt(full.id, 10) + '" data-title="' + full.dicitura_it + '"><i class="fa fa-comments"></i> (' + full.contaCommenti + ')</a>';
          }
        });
      }

      colDefs.push({
        'targets'   : colDefs.length,
        'searchable': false,
        'className' : 'dt-body-center',
        'orderable' : false,
        'render'    : function(data, type, full, meta) {
          return '<a href="#" title="' + Utility.getLang('base', 'CLONA') + '" class="btn btn-info clone" data-id="' + parseInt(full.id, 10) + '" data-title="' + full.dicitura_it + '"><i class="fa fa-copy"></i></a>';
        }
      });

      if(LEVEL >= 10) {
        colDefs.push({
          'targets'   : colDefs.length,
          'searchable': false,
          'className' : 'dt-body-center',
          'orderable' : false,
          'render'    : function(data, type, full, meta) {
            var disabled = '';
            if(parseInt(full.del_enabled, 10) != 1) {
              disabled = ' disabled';
            }
            var title = '';
            var icona = '';
            if(parseInt(full.eliminato, 10) != 1) {
              icona = 'fa-trash';
              title = Utility.getLang('base', 'CANCELLAZIONE_FITTIZIA');
            } else {
              icona = 'fa-unlock';
              title = Utility.getLang('base', 'RIATTIVA_ELEMENTO');
            }
            return '<a href="#" title="' + title + '" class="btn fdel' + disabled + '" data-id="' + parseInt(full.id, 10) + '" data-title="' + full.dicitura + '"><i class="fa ' + icona + '"></i></a>';
          }
        });
      }

      if(LEVEL >= 20) {
        colDefs.push({
          'targets'   : colDefs.length,
          'searchable': false,
          'className' : 'dt-body-center',
          'orderable' : false,
          'render'    : function(data, type, full, meta) {
            var disabled = '';
            if(parseInt(full.del_enabled, 10) != 1) {
              disabled = ' disabled';
            }
            return '<a href="#" title="' + Utility.getLang('base', 'DELETE') + '" class="btn delete' + disabled + '" data-id="' + parseInt(full.id, 10) + '" data-title="' + full.dicitura + '"><i class="fa fa-trash"></i></a>';
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

    case 'default':

      $('#seobtn').button({'icons': {'primary': 'ui-icon-tag'}});

      $('#seobtn').click(function() {
        newAction('seo');
      });

      $('#del_selected').button({'icons': {'primary': 'ui-icon-trash'}});
      if(parseInt($('#del_selected').data('enabled'), 10) != 1) {
        $('#del_selected').button('option', 'disabled', true);
      }

      $('#commentibtn').click(function(e) {
        e.preventDefault();
        newAction('commenti');
      });

      $('#commentibtn').button({icons: {primary: 'ui-icon-comment'}});

      $('#newbtn').click(function(e) {
        e.preventDefault();
        newAction('new');
      });

      selectFunction = function(obj, action) {
        checkbox = $('input', $(obj));
        data = 'cmd=GENERIC.MAKESELECTION&module_name=' + module_name + '&checked=' + checkbox[0].checked + '&id=' + checkbox.val().replace('elem_', '');
        $.ajax({
          type    : "POST",
          url     : main_host + "ajax.php",
          data    : data,
          dataType: "json",
          success : function(returned_data) {
            showDelButton(returned_data);
          }
        });
      };

      htmlSelectFunction = function(record) {
        if(parseInt(record.selected, 10) == 1) {
          checked = 'checked="checked" ';
        } else {
          checked = '';
        }
        check = '<input type="checkbox" ' + checked + ' value="elem_' + record.id + '" name="elem_id" class="enabled" />';
        return check;
      };

      commentiBtnHtmlOut = function(record) {
        out = '';
        if(parseInt(record.commenti, 10) || record.contaCommenti > 0) {
          out += '<span class="enabled commPagina">' + record.contaCommenti + '</span>';
        } else {
          out += Utility.getLang('pagine', 'COMMENTI_DISABILITATI');
        }
        return out;
      };

      newFunction = function(obj, action) {
        gotoAction(obj, action);
      };

      commFunction = function(obj, action) {
        gotoAction(obj, action);
      };

      switchvisibilityFunction = function(obj, action) {
        gotoAction(obj, action);
      };

      delFunction = function(obj, action) {
        gotoAction(obj, action);
      };

      params = [{chiave: 'cmd', valore: module_name.toUpperCase() + '.GETLIST'}];

      cols = [];

      col = {
        'campo'       : 'id',
        'titolo'      : 'ID',
        'actionclass' : 'new',
        'clickHandler': newFunction,
        'htmltitle'   : Utility.getLang('base', 'EDIT')
      };

      cols.push(col);

      col = {
        'campo'       : '',
        'titolo'      : '',
        'hasFilter'   : false,
        'actionclass' : 'select',
        'tipo'        : 'function',
        'htmloutfunct': htmlSelectFunction,
        'ordinato'    : true,
        'clickHandler': selectFunction,
        'htmltitle'   : Utility.getLang('base', 'SELECT'),
        'filtrato'    : false
      };

      cols.push(col);

      col = {
        'campo'       : 'dicitura',
        'titolo'      : Utility.getLang(module_name, 'DICITURA'),
        'actionclass' : 'new',
        'clickHandler': newFunction,
        'htmltitle'   : Utility.getLang('base', 'EDIT')
      };

      cols.push(col);

      col = {
        'campo'       : 'parent',
        'titolo'      : Utility.getLang(module_name, 'GENITORE'),
        'filter_type' : 'select',
        'clickHandler': newFunction,
        'htmltitle'   : Utility.getLang('base', 'EDIT'),
        'filter'      : eval("(" + $('#genitori').val() + ")"),
        'actionclass' : 'new'
      };

      cols.push(col);

      col = {
        'campo'       : 'href',
        'titolo'      : Utility.getLang(module_name, 'HREF'),
        'actionclass' : 'new',
        'clickHandler': newFunction,
        'htmltitle'   : Utility.getLang('base', 'EDIT')
      };

      cols.push(col);

      col = {
        'campo'       : 'template',
        'titolo'      : Utility.getLang(module_name, 'TEMPLATE'),
        'actionclass' : 'new',
        'clickHandler': newFunction,
        'htmltitle'   : Utility.getLang('base', 'EDIT')
      };

      cols.push(col);

      if($('#commentibtn').length) {
        col = {
          'campo'       : 'commenti',
          'titolo'      : Utility.getLang(module_name, 'COMMENTI'),
          'actionclass' : 'commenti',
          'sortable'    : false,
          'tipo'        : 'function',
          'htmloutfunct': commentiBtnHtmlOut,
          'hasFilter'   : false,
          'clickHandler': commFunction,
          'htmltitle'   : Utility.getLang('base', 'EDIT')
        };

        cols.push(col);

      }

      col = {
        'campo'       : 'attivo',
        'titolo'      : Utility.getLang(module_name, 'STATO'),
        'filter_type' : 'select',
        'clickHandler': newFunction,
        'htmltitle'   : Utility.getLang('base', 'EDIT'),
        'filter'      : eval("(" + $('#stati').val() + ")"),
        'classe'      : 'col4',
        'actionclass' : 'new'
      };

      cols.push(col);

      col = {
        'campo'       : '',
        'titolo'      : '',
        'tipo'        : 'action',
        'htmltitle'   : Utility.getLang('base', 'DELETE_ELEMENT'),
        'clickHandler': delFunction,
        'img0'        : rel_root + 'images/trash.png',
        'img1'        : rel_root + 'images/trash_disabled.png',
        'actionclass' : 'del'
      };

      cols.push(col);

      $at = $('#ajaxtable').ajaxtable({
        params : params,
        page   : parseInt($('#page').val(), 10),
        cols   : cols,
        onload : function() {
          $('.commPagina').button({icons: {'primary': 'ui-icon-comment'}});
        },
        filters: eval("(" + $('#filters_values').val() + ")")
      });


      $('#del_selected').bind('click', function() {
        if($(this).hasClass('sel')) {
          this.blur();
          action = 'del_selected';
          modal_confirm(Utility.getLang('base', 'WARNING'),
            Utility.getLang('base', 'CONFIRM_DELETION'),
            function() {
              frm = $($('.selection_ajaxtable').get(0)).parent().get(0);
              frm.action = main_host + 'admin/' + Utility.getUrlParam(0, rel_root) + '/' + action + '/';
              frm.submit();
            });
        }
      });
  }
});


function showPreview(ret_data) {
  if(parseInt(ret_data.result) == 1) {
    window.open(ret_data.url, '', 'width=1024, height=768, scrollbars=yes');
  } else {
    modal_dialog(Utility.getLang('base', 'WARNING'), ret_data.error);
  }
}

function keySaved(ret_data) {
  if(parseInt(ret_data.result) == 1) {
    $at.reload(1);
    $('#commento').dialog('destroy');
  } else {
    modal_dialog(Utility.getLang('base', 'WARNING'), ret_data.error);
  }
}


function editCommento(obj) {
  id = $(obj).parent().attr('id').replace(/[a-zA-Z\_]*/g, '');
  data = 'cmd=' + module_name.toUpperCase() + '.EDITCOMMENTO&id=' + id;
  $.ajax({
    type    : "POST",
    url     : main_host + "ajax.php",
    data    : data,
    dataType: "json",
    success : function(returned_data) {
      fillCommentoForm(returned_data)
    }
  });
}

function fillCommentoForm(ret_data) {
  if(parseInt(ret_data.result) == 1) {
    commento = ret_data.commento;
    $('#id_commento').val(commento.id);
    $('#commento_text').val(commento.commento);
    $('#email').val(commento.email);
    $('#nome').val(commento.nome);
    if(parseInt(commento.valido) == 1) {
      $('#valido').attr({'checked': 'checked'});
    } else {
      $('#valido').removeAttr('checked');
    }

    buttons = {};

    buttons[Utility.getLang('base', 'SALVA')] = function() {
      $('#commento form').submit();
    };

    buttons[Utility.getLang('base', 'CANCEL')] = function() {
      $('#commento').dialog('destroy');
    };
    $('#commento').dialog({
      title   : Utility.getLang(module_name, 'MODIFICA_COMMENTO'),
      modal   : true,
      overlay : {
        backgroundColor: '#000',
        opacity        : 0.5
      },
      buttons : buttons,
      close   : function(event, ui) {
        $('#commento').dialog('destroy');
      },
      position: new Array('center', 20, 'center', 0),
      heigth  : '450px',
      width   : '600px'
    });


  } else {
    modal_dialog(Utility.getLang('base', 'WARNING'), ret_data.error);
  }
}


function updateImgForm(field, imgurl, lang) {

  log('--- function updateImgForm(' + field + ',' + imgurl + ',' + lang + ') - FINE   ---');

  data = {};

  if(imgurl == '') {
    compiled = Handlebars.compile($('#sceltaimmaginevuota-template').html());
    data.idCampoFile = field + '_' + lang;
    data.nomeCampoFile = lang + '[' + field + ']';
  } else {
    compiled = Handlebars.compile($('#sceltaimmagine-template').html());
    data.nomeScelta = lang + '[' + field + '][action]';
    data.idCampoKeep = 'keep_' + field + '_' + lang;
    data.urlCampo = imgurl;
    data.idCampoReplace = 'replace_' + field + '_' + lang;
    data.idCampoFile = field + '_' + lang;
    data.nomeCampoFile = lang + '[' + field + ']';
    data.idCampoDel = 'del_' + field + '_' + lang;
  }

  $('#ul' + data.idCampoFile).replaceWith(compiled(data));

  // ridimensionamento immagini tramite attributo maxwidth
  $('img[maxwidth]').each(function() {
    if($(this).width() > parseInt($(this).attr('maxwidth'))) {
      $(this).css({'width': $(this).attr('maxwidth') + 'px'});
    }
  });


  log('--- function updateImgForm(' + imgurl + ',' + lang + ') - FINE   ---');
}