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
var module_name = 'tags';

$(function() {

  $(document).on('click', '#del_selected', function(e) {
    e.preventDefault();
    var id = $(this).data('id');
    var title = $(this).data('title');
    Utility.confirm({
      'message': sprintf(Utility.getLang(module_name, 'VUOI_CANCELLARE_I_SELEZIONATI'), title),
      'onOk'   : function() {

        $.ajax({
          type    : "POST",
          url     : main_host + 'admin/' + module_name + '/del_selected/',
          dataType: "json",
          success : function(ret_data) {
            if(parseInt(ret_data.result, 10) == 1) {
              PNotify.removeAll();
              new PNotify({
                title   : Utility.getLang('base', 'ELEMENTI_CANCELLATI'),
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

  // placeholder per selezione
  var col = {
    'title'     : Utility.getLang('base', 'SELEZIONA'),
    'searchable': false,
    'className' : 'dt0 dt-body-center',
    'checkbox'  : true
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
    'title'    : Utility.getLang(module_name, 'TAG'),
    searchable : true,
    data       : 'tag',
    'className': 'dt2'
  };

  cols.push(col);

  col = {
    'title'    : Utility.getLang(module_name, 'LINGUA'),
    searchable : true,
    data       : 'lingua',
    'className': 'dt3'
  };

  cols.push(col);

  // placeholder per cancellare
  col = {
    'title'    : Utility.getLang('base', 'ELIMINA'),
    'className': 'dt4 dt-body-center'
  };
  cols.push(col);

  var colDefs = [
    {
      'targets'   : 0,
      'searchable': false,
      'orderable' : false,
      'className' : 'dt-body-center',
      'render'    : function(data, type, full, meta) {
        var checkboxHtml = '<input type="checkbox" name="id[]" value="' + full.id + '" class="selRow"';
        if(full.selected) {
          checkboxHtml += ' checked';
        }
        checkboxHtml += '>';
        return checkboxHtml;
      }
    }
  ];

  colDefs.push({
    'targets'   : cols.length - 1,
    'searchable': false,
    'className' : 'dt-body-center',
    'orderable' : false,
    'render'    : function(data, type, full, meta) {
      return '<a href="#" title="' + Utility.getLang('base', 'ELIMINA_RECORD') + '" class="btn delete" data-id="' + parseInt(full.id, 10) + '" data-title="' + full.tag + '"><i class="fa fa-trash"></i></a>';
    }
  });

  $dt = $('#dataTable').DataTable({
      'language'      : {
        url: main_host + 'bower_components/datatables-plugins/i18n/Italian.lang'
      },
      'sDom'          : 'tip',
      "order"         : [[2, "asc"], [3, "asc"]],
      'iDisplayLength': 15,
      'stateSave'     : true,
      responsive      : true,
      ajax            : function(data, callback, settings) {
        $.ajax({
          type    : "POST",
          url     : main_host + 'admin/' + module_name + '/getlist/',
          data    : data,
          dataType: "json",
          success : function(ret_data) {
            if(parseInt(ret_data.result, 10) == 1) {
              if(parseInt(ret_data.delButton, 10) == 1) {
                $('#del_selected').removeClass('disabled');
              } else {
                $('#del_selected').addClass('disabled');
              }
              callback(ret_data);
            } else {
              Utility.alert({message: ret_data.error});
            }
          }
        });
      },
      columns         : cols,
      'columnDefs'    : colDefs
    }
  );

  /*
  $('#del_selected').button({'icons': {'primary':'ui-icon-trash'}});
  if(parseInt($('#del_selected').data('enabled'), 10) != 1) {
    $('#del_selected').button('option', 'disabled', true);
  }

  selectFunction = function(obj, action) {
    checkbox = $('input', $(obj));
    data = 'cmd=GENERIC.MAKESELECTION&module_name='+module_name+'&checked='+checkbox[0].checked+'&id='+checkbox.val().replace('elem_','');
    $.ajax({type: "POST",
            url: main_host+"ajax.php",
            data: data,
            dataType: "json",
            success: function(returned_data) { showDelButton(returned_data); }
           });
  };

  htmlSelectFunction = function(record) {
    if(parseInt(record.selected, 10) == 1) {
      checked = 'checked="checked" ';
    } else {
      checked = '';
    }
    check = '<input type="checkbox" '+checked+' value="elem_'+record.id+'" name="elem_id" class="enabled" />';
    return check;
  };
*/
  delFunction = function(obj, action) {

    modal_confirm(Utility.getLang('base', 'WARNING'),
      Utility.getLang('base', 'CONFIRM_DELETION'),
      function() {
        $(this).dialog('destroy');
        id = $(obj).parent().get(0).id.replace(/[a-zA-Z\_]*/g, '');
        data = 'cmd=' + module_name.toUpperCase() + '.DELETE&id=' + id;
        $.ajax({
          type    : "POST",
          url     : main_host + "ajax.php",
          data    : data,
          dataType: "json",
          success : function(ret_data) {
            if(parseInt(ret_data.result, 10) == 1) {
              $at.reload();
            } else {
              modal_dialog(Utility.getLang('base', 'WARNING'), ret_data.error);
            }
          }
        });
      }
    );

  };
  newFunction = function() {
  };

  /*
  params = [{chiave:'cmd', valore:module_name.toUpperCase()+'.GETLIST'}];

  cols = new Array();

  col = {'campo'       : 'id',
         'titolo'      : 'ID',
         'actionclass' : 'new',
         'clickHandler': newFunction,
         'htmltitle'   : Utility.getLang('base', 'EDIT')};

  cols.push(col);

  col = {'campo'       : '',
         'titolo'      : '',
         'hasFilter'   : false,
         'actionclass' : 'select',
         'tipo'        : 'function',
         'htmloutfunct': htmlSelectFunction,
         'ordinato'    : true,
         'clickHandler': selectFunction,
         'htmltitle'   : Utility.getLang('base', 'SELECT'),
         'filtrato'    : false };

  cols.push(col);

  col = {'campo'       : 'tag',
         'titolo'      : Utility.getLang(module_name, 'TAG'),
         'actionclass' : 'new',
         'clickHandler': newFunction,
         'htmltitle'   : Utility.getLang('base', 'EDIT')};

  cols.push(col);

  col = {'campo'       : 'count',
         'titolo'      : Utility.getLang(module_name, 'COUNT'),
         'actionclass' : 'new',
         'hasFilter'   : false,
         'clickHandler': newFunction,
         'htmltitle'   : Utility.getLang('base', 'EDIT')};

  cols.push(col);

  col = {'campo'       : 'lang',
        'titolo'      : Utility.getLang(module_name, 'LANG'),
        'filter_type' : 'select',
        'clickHandler': newFunction,
        'htmltitle'   : Utility.getLang('base', 'EDIT'),
        'filter'      : eval("(" + $('#lingue_filtro').val() + ")"),
        'actionclass' : 'new'};

  cols.push(col);


  col = {'campo'       : '',
         'titolo'      : '',
         'tipo'        : 'action',
         'htmltitle'   : Utility.getLang('base', 'DELETE_ELEMENT'),
         'clickHandler': delFunction,
         'img0'       : rel_root+'images/trash.png',
         'img1'       : rel_root+'images/trash_disabled.png',
         'actionclass' : 'del'};

  cols.push(col);

  $at = $('#ajaxtable').ajaxtable({params: params,
                                   page : parseInt($('#page').val(), 10),
                                   cols: cols,
                                   filters : eval("(" + $('#filters_values').val() + ")")});


  $('#del_selected').on('click', function(e) {
    e.preventDefault();
    modal_confirm(Utility.getLang('base', 'WARNING'),
                  Utility.getLang('base', 'CONFIRM_DELETION'),
                  function() {
                    $(this).dialog('destroy');
                    data = 'cmd='+module_name.toUpperCase()+'.DELSELECTED';
                    $.ajax({
                      type: "POST",
                      url: main_host+"ajax.php",
                      data: data,
                      dataType: "json",
                      success: function(ret_data) {
                        if(parseInt(ret_data.result, 10) == 1) {
                          $at.reload();
                        } else {
                          modal_dialog(Utility.getLang('base', 'WARNING'), ret_data.error);
                        }
                      }
                    });
                  }
                );
  });
*/
});

function salvaSelezione() {
  var seleziona = [];
  var deseleziona = [];

  $('#dataTable tbody input[type="checkbox"]').each(function() {
    if(this.checked) {
      seleziona.push($(this).val());
    } else {
      deseleziona.push($(this).val());
    }
  });

  var data = {
    seleziona  : seleziona,
    deseleziona: deseleziona,
    module_name: module_name
  };
  $.ajax({
    type    : "POST",
    url     : main_host + 'admin/' + module_name + '/select/',
    data    : data,
    dataType: "json",
    success : function(ret_data) {
      if(parseInt(ret_data.result, 10) != 1) {
        Utility.alert({message: ret_data.error});
      } else {
        if(parseInt(ret_data.delButton, 10) == 1) {
          $('#del_selected').removeClass('disabled');
        } else {
          $('#del_selected').addClass('disabled');
        }
      }
    }
  });

}