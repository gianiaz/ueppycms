/***************/
/** v.1.01    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.01 (22/08/2016, 12.14)                                                                   **/
/** - Bugfix, veniva copiata due volte la news.                                                  **/
/**                                                                                              **/
/** v.1.00 (27/04/2016)                                                                          **/
/** - Adattamenti alla nuova versione del cms (4.0)                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
var module_name = 'news';
var t = false;

$(function() {

  switch(Utility.getUrlParam(1, rel_root)) {

    case 'insert':
      break;

    case 'new':

      Utility.tabIndexInit();

      $('.data').datetimepicker({locale: ACTUAL_LANGUAGE});

      if(parseInt($('#id').val(), 10) !== 0 && $('#ajaxForm').data('readonly') != 1) {
        $('#previewBtn').removeClass('disabled');
        $('#salvataggioAutomaticoBtn').removeClass('disabled');
      }

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
            autoSave();
          }, timerAutosave);

        }
      });

      /** SALVATAGGIO AUTOMATICO - FINE **/

      $('#disattivazione').change(function() {
        if($(this).val() == '-1') {
          $('#disattivazioneContainer').hide();
        } else {
          $('#disattivazioneContainer').show();
        }
      });

      $('#disattivazione').trigger('change');

      /** GESTIONE CATEGORIE - INIZIO **/
      $(document).on('change', '#genitore', function(e) {
        setUpCorrelate();
      });
      setUpCorrelate();
      /** GESTIONE CATEGORIE - FINE **/

      /** SELEZIONE DELLE LINGUE - INIZIO **/
      $(document).on('click', '.lingue_attive', function(e) {
        if($('.lingue_attive:checked').length == 0) {
          e.preventDefault();
        } else {
          setUpActiveTabs();
        }
      });

      setUpActiveTabs();
      /** SELEZIONE DELLE LINGUE - FINE **/

      /** GESTIONE DEI TAGS - INIZIO **/
      $(document).on('click', '#tagPrecenti', function(e) {
        e.preventDefault();
        $('.elenco_tags').slideDown();
      });

      $(document).on('click', '.elenco_tags span', function(e) {
        var $row = $(this).closest('.row');
        var $inputTags = $row.find('[type="text"]');

        var tagsInput = $inputTags.val().split(',');
        var tags = [];

        for(var i = 0; i < tagsInput.length; i++) {
          var tag = $.trim(tagsInput[i]);
          if(tag != '') {
            tags.push(tag);
          }
        }

        tags.push($(this).data('tag'));

        tags = array_unique(tags);

        Utility.log(tags);

        $inputTags.val(tags.join(','));
      });
      /** GESTIONE DEI TAGS - FINE **/

      beforeSerialize = function() {
        tinyMCE.triggerSave();
      }


      afterSubmit = function(ret_data, $form) {
        Utility.unlockScreen();
        if(parseInt(ret_data.result, 10) === 1) {
          Ueppy.saveSuccess(ret_data.dati);
          if(parseInt(ret_data.id, 10) > 0) {
            $('#previewBtn').removeClass('disabled');
            $('#salvataggioAutomaticoBtn').removeClass('disabled');
          }
        } else {
          Ueppy.showFormErrors(ret_data.errors, ret_data.wrongs);
        }
      }

      Ueppy.tinyMCESetOpts({
        'main_host': main_host,
        'readonly' : $('#ajaxForm').data('readonly'),
        'selector' : 'textarea.mce'
      });

      Ueppy.tinyMCEInit();
      Ueppy.inizializzaPluginAllegati();
      PNotify.removeAll();
      unsaved = false;
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
                        tinyMCE.remove();
                        $('.modal').modal('hide');
                        $dt.ajax.reload(null, false);
                      } else {
                        Utility.alert({'message': ret_data.error});
                      }
                    }
                  });
                },
                onCancel: function() {
                  tinyMCE.remove();
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

      if($('#dataTable').data('news_id') == 0) {

        col = {
          'title'   : Utility.getLang(module_name, 'TITOLO_NEWS'),
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
            data  : {'news_id': $('#dataTable').data('news_id')}
          },
          columns         : cols,
          'columnDefs'    : columnDefs
        }
      );

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

      col = {
        'title'   : Utility.getLang(module_name, 'ATTIVA_DAL'),
        searchable: true,
        className : 'seo4',
        data      : 'attiva_dal'
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
          "order"         : [[5, "desc"]],
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

      var col = {
        'title'     : Utility.getLang('base', 'MODIFICA'),
        'searchable': false
      };
      Utility.log(cols);
      cols.push(col);

      col = {
        'title'   : 'ID',
        searchable: true,
        data      : 'id'
      };

      cols.push(col);

      col = {
        'title'   : Utility.getLang(module_name, 'TITOLO'),
        searchable: true,
        data      : 'titolo'
      };

      cols.push(col);

      col = {
        'title'   : Utility.getLang(module_name, 'DATA'),
        searchable: true,
        data      : 'attiva_dal'
      };

      cols.push(col);

      if($('#dataTable').data('categorie') == 1) {

        col = {
          'title'   : Utility.getLang(module_name, 'CATEGORY'),
          searchable: true,
          data      : 'category'
        };

        cols.push(col);

      }

      col = {
        'title'   : Utility.getLang(module_name, 'AUTHOR'),
        searchable: true,
        data      : 'autore'
      };

      cols.push(col);

      col = {
        'title'   : Utility.getLang(module_name, 'STATO'),
        searchable: true,
        data      : 'stato'
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

      var targets = {};

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

      if($('#dataTable').data('categorie') == 1) {
        colDefs.push({
            'targets': 6
          }
        );
      }


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
            return '<a href="#" title="' + title + '" class="btn fdel' + disabled + '" data-id="' + parseInt(full.id, 10) + '" data-title="' + full.titolo + '"><i class="fa ' + icona + '"></i></a>';
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
            return '<a href="#" title="' + Utility.getLang('base', 'DELETE') + '" class="btn delete' + disabled + '" data-id="' + parseInt(full.id, 10) + '" data-title="' + full.titolo + '"><i class="fa fa-trash"></i></a>';
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

function autoSave() {
  tinyMCE.triggerSave();
  $('#ajaxForm').submit();
}


/**
 * Cicla i checkbox delle lingue, se attivo toglie la classe disabled al li e aggiunge il data-role="tab" al div di contenuto
 * viceversa, aggiugne la class disabled e rimuove data-role tab.
 */
function setUpActiveTabs() {
  var schedaAttiva = false;
  $('.lingue_attive').each(function() {
    var selettore = '#scheda_' + $(this).val();
    var $a = $('[href="' + selettore + '"]');
    var $li = $a.closest('li');
    var $scheda = $(selettore);
    if(this.checked) {
      $li.removeClass('disabled');
      $a.attr('data-toggle', 'tab');
      if(!schedaAttiva) {
        schedaAttiva = true;
        $scheda.addClass('active');
        $li.addClass('active');
      } else {
        $scheda.removeClass('active');
        $li.removeClass('active');
      }
    } else {
      $li.addClass('disabled');
      $a.attr('data-toggle', false);
      $scheda.removeClass('active');
      $li.removeClass('active');
    }
  });

}

function setUpCorrelate() {
  var $parents = $('#parents');
  var $selected = $('#parents').find('option[value="' + $('#genitore').val() + '"]');

  $parents.find('option').removeAttr('disabled');
  $selected.removeAttr('selected').prop('disabled', true);
}