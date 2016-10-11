/*****************/
/***ueppy3.1.01***/
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
module_name = 'backup';

$(function() {

  switch(Utility.getUrlParam(1, rel_root)) {

    case 'new':

      $('#cron').change(function() {
        if(parseInt($(this).val(), 10) === 0) {
          $('.cron').hide();
        } else {
          $('.cron').show();
        }
      }).trigger('change');

      $('#directories_all').change(function() {
        if(parseInt($(this).val(), 10) == 1) {
          $('.selezione_directory').hide();
        } else {
          $('.selezione_directory').show();
        }
      }).trigger('change');

      $('#tabelle_all').change(function() {
        if(parseInt($(this).val(), 10) == 1) {
          $('.selezione_tabelle').hide();
        } else {
          $('.selezione_tabelle').show();
        }
      }).trigger('change');

      Utility.tabIndexInit();

      afterSubmit = function(ret_data, $form) {
        if(parseInt(ret_data.result, 10) === 1) {
          Ueppy.saveSuccess(ret_data.dati);
        } else {
          Ueppy.showFormErrors(ret_data.errors, ret_data.wrongs);
        }

      }

      break;


    case 'profiliftp':

      $(document).on('click', '#newprofile', function(e) {
        e.preventDefault();
        openModal(null, Utility.getLang(module_name, 'MODIFICA_PROFILO'));
      });

      $(document).on('click', '.editProfile', function(e) {

        e.preventDefault();

        var data = {id: $(this).data('id')};

        $.ajax({
          type    : "POST",
          url     : main_host + 'admin/' + module_name + '/load_profile/',
          data    : data,
          dataType: "json",
          success : function(ret_data) {
            if(parseInt(ret_data.result, 10) == 1) {
              openModal(ret_data.data, Utility.getLang(module_name, 'MODIFICA_PROFILO'));
            } else {
              Utility.alert({message: ret_data.error});
            }
          }
        });

      });

      $(document).on('click', '.deleteProfile', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var title = $(this).data('title');
        Utility.confirm({
          'message': sprintf(Utility.getLang('base', 'VUOI_RIMUOVERE_LA_VOCE'), title),
          'onOk'   : function() {

            var data = {id: id};
            $.ajax({
              type    : "POST",
              url     : main_host + 'admin/' + module_name + '/del_profile/',
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

      cols = [];

      // placeholder per modifica
      var col = {
        'title'     : Utility.getLang('base', 'MODIFICA'),
        'className' : 'dtp0 dt-body-center',
        'searchable': false
      };
      cols.push(col);

      col = {
        'title'    : 'ID',
        searchable : true,
        'className': 'dtp1 dt-body-center',
        data       : 'id'
      };

      cols.push(col);

      col = {
        'title'    : Utility.getLang(module_name, 'PROFILE_NAME'),
        searchable : true,
        'className': 'dtp2',
        data       : 'profile_name'
      };

      cols.push(col);

      // placeholder per cancellare
      col = {
        'className': 'dtp3 dt-body-center'
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
            url   : main_host + 'admin/' + module_name + '/getlist_profili/',
          },
          columns         : cols,
          'columnDefs'    : [
            {
              'targets'   : 0,
              'searchable': false,
              'orderable' : false,
              'className' : 'dt-body-center',
              'render'    : function(data, type, full, meta) {
                return '<a href="#" title="' + Utility.getLang('base', 'MODIFICA_RECORD') + '" class="btn btn-primary editProfile" data-id="' + full.id + '"><i class="fa fa-pencil"></i></a>';
              }
            },
            {
              'targets'  : 1,
              'className': 'dt-body-center'
            },
            {
              'targets'   : cols.length - 1,
              'searchable': false,
              'className' : 'dt-body-center',
              'orderable' : false,
              'render'    : function(data, type, full, meta) {
                var ret = '<a href="#" class="btn btn-danger deleteProfile';
                if(parseInt(full.cancellabile, 10) == 0) {
                  ret += ' disabled';
                }
                ret += '" title="' + Utility.getLang('base', 'ELIMINA_RECORD') + '" data-id="' + parseInt(full.id, 10) + '" data-title="' + full.profile_name + '"><i class="fa fa-trash"></i></a>';
                return ret
              }
            }
          ]
        }
      );

      break;

    case 'list_archives':

      $(document).on('click', '#uploadBackup', function(e) {
        e.preventDefault();
        openModal(null, Utility.getLang(module_name, 'CARICA_BACKUP'));
      });

      $(document).on('click', '.downloadArchive', function(e) {
        e.preventDefault();
        var title = $(this).data('title');

        var data = {
          id     : $('#dataTable').data('backup_id'),
          'title': title
        };

        Utility.postData({
          action: main_host + 'admin/' + module_name + '/download/',
          data  : data
        });
      });

      $(document).on('click', '.deleteArchive', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var title = $(this).data('title');

        Utility.confirm({
          'message': sprintf(Utility.getLang('base', 'VUOI_RIMUOVERE_LA_VOCE'), title),
          'onOk'   : function() {

            var data = {
              id     : $('#dataTable').data('backup_id'),
              'title': title
            };
            $.ajax({
              type    : "POST",
              url     : main_host + 'admin/' + module_name + '/delete_archive/',
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

      $(document).on('click', '.restoreArchive', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var title = $(this).data('title');

        Utility.confirm({
          'message': sprintf(Utility.getLang(module_name, 'VUOI_RIPRISTINARE_LA_VOCE'), title),
          'onOk'   : function() {

            var data = {
              id     : $('#dataTable').data('backup_id'),
              'title': title
            };
            $.ajax({
              type    : "POST",
              url     : main_host + 'admin/' + module_name + '/restore_archive/',
              data    : data,
              dataType: "json",
              success : function(ret_data) {
                if(parseInt(ret_data.result, 10) == 1) {
                  PNotify.removeAll();
                  new PNotify({
                    title   : Utility.getLang(module_name, 'ELEMENTO_RIPRISTINATO'),
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

      $(document).on('click', '#doBackup', function(e) {
        e.preventDefault();
        Utility.lockScreen({'message': Utility.getLang(module_name, 'GENERAZIONE_IN_CORSO')});

        var data = {'id': $('#dataTable').data('backup_id')};

        $.ajax({
          type    : "POST",
          url     : main_host + 'admin/' + module_name + '/dobackup/',
          data    : data,
          dataType: "json",
          success : function(ret_data) {
            if(parseInt(ret_data.result, 10) == 1) {
              Utility.unlockScreen();
              $dt.ajax.reload(null, false);
            } else {
              Utility.alert({message: ret_data.error});
            }
          }
        });

      });


      cols = [];

      col = {
        'title'    : Utility.getLang(module_name, 'NOME'),
        searchable : true,
        'className': 'dtla0',
        data       : 'nome'
      };

      cols.push(col);

      col = {
        'title'    : Utility.getLang(module_name, 'SIZE'),
        searchable : true,
        'className': 'dtla1',
        data       : 'size'
      };

      cols.push(col);

      // placeholder per scaricare
      col = {
        'className': 'dtla2 dt-body-center'
      };

      cols.push(col);

      // placeholder per recuperare
      col = {
        'className': 'dtla3 dt-body-center'
      };

      cols.push(col);

      // placeholder per cancellare
      col = {
        'className': 'dtla4 dt-body-center'
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
            data  : {backup_id: $('#dataTable').data('backup_id')},
            method: 'post',
            url   : main_host + 'admin/' + module_name + '/getlist_archives/',
          },
          columns         : cols,
          'columnDefs'    : [
            {
              'targets'   : cols.length - 3,
              'searchable': false,
              'className' : 'dt-body-center',
              'orderable' : false,
              'render'    : function(data, type, full, meta) {
                var ret = '<a href="#" class="btn btn-info downloadArchive';
                ret += '" title="' + Utility.getLang(module_name, 'SCARICA_ARCHIVIO') + '" data-id="' + parseInt(full.id, 10) + '" data-title="' + full.nome + '"><i class="fa fa-download"></i></a>';
                return ret
              }
            },
            {
              'targets'   : cols.length - 2,
              'searchable': false,
              'className' : 'dt-body-center',
              'orderable' : false,
              'render'    : function(data, type, full, meta) {
                var ret = '<a href="#" class="btn btn-warning restoreArchive';
                ret += '" title="' + Utility.getLang(module_name, 'RIPRISTINA_BACKUP') + '" data-id="' + parseInt(full.id, 10) + '" data-title="' + full.nome + '"><i class="fa fa-refresh"></i></a>';
                return ret
              }
            }, {
              'targets'   : cols.length - 1,
              'searchable': false,
              'className' : 'dt-body-center',
              'orderable' : false,
              'render'    : function(data, type, full, meta) {
                var ret = '<a href="#" class="btn btn-danger deleteArchive';
                if(parseInt(full.cancellabile, 10) == 0) {
                  ret += ' disabled';
                }
                ret += '" title="' + Utility.getLang('base', 'ELIMINA_RECORD') + '" data-id="' + parseInt(full.id, 10) + '" data-title="' + full.nome + '"><i class="fa fa-trash"></i></a>';
                return ret
              }
            }
          ]
        }
      );
      break;
    case '#':
    case null:

      $(document).on('click', '.archive', function(e) {
        var data = {id: $(this).data('id')};
        Utility.postData({
          action: main_host + 'admin/' + module_name + '/list_archives/',
          data  : data
        });
      });

      cols = [];

      // placeholder per modifica
      var col = {
        'title'     : Utility.getLang('base', 'MODIFICA'),
        'className' : 'dt0 dt-body-center',
        'searchable': false
      };
      cols.push(col);

      col = {
        'title'    : 'ID',
        searchable : true,
        'className': 'dt1 dt-body-center',
        data       : 'id'
      };

      cols.push(col);

      col = {
        'title'    : Utility.getLang(module_name, 'NOME'),
        searchable : true,
        'className': 'dt2',
        data       : 'nome'
      };

      cols.push(col);

      // placeholder per archivi
      col = {'className': 'dt3 dt-body-center'};
      cols.push(col);

      // placeholder per visibilita
      col = {'className': 'dt4 dt-body-center'};
      cols.push(col);

      // placeholder per cancellare
      col = {
        'className': 'dt5 dt-body-center'
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
              'targets'   : cols.length - 3,
              'searchable': false,
              'className' : 'dt-body-center',
              'orderable' : false,
              'render'    : function(data, type, full, meta) {
                var ret = '<a href="#" class="btn btn-info archive';
                ret += '" title="' + Utility.getLang(module_name, 'ARCHIVIO_BACKUPS') + '" data-id="' + parseInt(full.id, 10) + '" data-title="' + full.nome + '"><i class="fa fa-archive"></i></a>';
                return ret
              }
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
                if(parseInt(full.cron, 10) == 1) {
                  icon = '<i class="fa fa-play"></i>';
                  title = Utility.getLang('base', 'DISABILITA');
                } else {
                  icon = '<i class="fa fa-hand-paper-o"></i>';
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
                ret += '" title="' + Utility.getLang('base', 'ELIMINA_RECORD') + '" data-id="' + parseInt(full.id, 10) + '" data-title="' + full.nome + '"><i class="fa fa-trash"></i></a>';
                return ret
              }
            }
          ]
        }
      );

      break;
  }

});


function openModal(formData, title, cb) {

  Utility.log(formData);

  if(typeof formData == 'undefined') {
    formData = false;
  }

  var opts = {
    TITLE   : title,
    content : $('#modal').html(),
    onSave  : function($modal) {
      $modal.find('#ajaxForm2').ajaxSubmit({
        'dataType': 'json',
        'success' : function(ret_data) {
          if(parseInt(ret_data.result, 10) == 1) {

            $modal.modal('hide');

            PNotify.removeAll();

            new PNotify({
              title   : Utility.getLang('base', 'SALVATAGGIO_AVVENUTO'),
              text    : '',
              type    : 'success',
              nonblock: {
                nonblock        : true,
                nonblock_opacity: .2
              }
            });

            $dt.ajax.reload(null, false);
          } else {
            Utility.alert({'message': ret_data.error});
          }
        }
      });
    },
    callback: function($modal) {
      $modal.find('.cmsHelp').tooltip();
      if(formData) {
        for(i in formData) {
          Utility.log(i);
          $modal.find('#' + i).val(formData[i]);
          $modal.data(i, formData[i]);
        }
      }
      if(typeof(cb) == 'function') {
        cb($modal);
      }
    }
  };

  Utility.modalForm(opts);

}
