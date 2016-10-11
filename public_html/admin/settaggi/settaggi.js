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
var module_name = 'settaggi';

$(function() {

  Utility.tabIndexInit();

  $('.editbtn').click(function(e) {
    e.preventDefault();
    editSetting(this.id.replace('e', ''));
  });

  $('.delbtn').click(function(e) {
    e.preventDefault();
    delSetting(this.id.replace('d', ''));
  });

  $('#newbtn').click(function(e) {
    e.preventDefault();
    openSettingForm();
  });

  $(document).on('change', '#type', function(e) {
    if($(this).val() == 'text') {
      $('#testuale').show();
      $('#booleano').hide();
    } else {
      $('#testuale').hide();
      $('#booleano').show();
    }
  });


  $('#insert_form').ajaxForm({
    beforeSubmit: function() {
      lockWait(Utility.getLang('settaggi', 'SALVATAGGIO_IN_CORSO'));
    },
    success     : keySaved,
    dataType    : 'json'
  });

  $('#salva_tutto').ajaxForm({
    beforeSubmit: function() {
      lockWait(Utility.getLang('settaggi', 'SALVATAGGIO_IN_CORSO'));
    },
    success     : savedForm,
    dataType    : 'json'
  });


});


function editSetting(id) {
  var data = {'id': id};
  $.ajax({
    type    : "POST",
    url     : main_host + 'admin/' + module_name + '/load_element/',
    data    : data,
    dataType: "json",
    success : function(returned_data) {
      openSettingForm(returned_data)
    }
  });
}


function openSettingForm(data) {

  if(typeof data == 'undefined' || parseInt(data.result, 10) == 1) {


    var opts = {
      TITLE   : Utility.getLang(module_name, 'EDIT_SETTING'),
      SAVE    : Utility.getLang('base', 'SALVA'),
      CANCEL  : Utility.getLang('base', 'CANCEL'),
      size    : 'modal-lg',
      content : '',
      callback: function() {

        $('.modal-body').html($('#form').html());

        if(typeof(data) != 'undefined') {
          $('#id_entry').val(data.data.id);
          $('#gruppo').val(data.data.gruppo_settaggi_id);
          $('#type').val(data.data.type);
          if(data.data.type == 'text') {
            $('#valore_testuale').val(data.data.valore);
            $('#booleano').hide();
          } else {
            $('.valtextlbl').val('');
            $('#testuale').hide();
            $('#booleano').show();
            if(parseInt(data.data.valore, 10) == 1) {
              $('#valore_booleano').val('1');
            } else {
              $('#valore_booleano').val('0');
            }
          }

          $('#chiave').val(data.data.chiave);
          $('#chiave_ext').val(data.data.chiave_ext);
          $('#descrizione').val(data.data.descrizione);
          if(parseInt(data.data.super_admin, 10) == 1) {
            $('#super_admin').attr('checked', true);
          } else {
            $('#super_admin').removeAttr('checked');
          }

        } else {

          $('#id_entry').val('0');
          $('#gruppo').val('0');
          $('#type').val('text');
          $('.valtextlbl').val('').show();
          $('#booleano').hide();
          $('#chiave').val('');
          $('#chiave_ext').val('');
          $('#descrizione').val('');
          $('#super_admin').removeAttr('checked');
          $('#valore_booleano').val('0');

        }

      },
      onSave  : function() {
        $('#ajaxForm2').ajaxSubmit({
          'dataType': 'json',
          success   : function(ret_data) {
            if(parseInt(ret_data.result, 10) == 1) {
              $('.modal').modal('hide').data('bs.modal', null);
              //location.reload();
            } else {
              Utility.alert({'message': ret_data.error});
            }
          }
        });
      },
      onCancel: function() {
        $('.modal').modal('hide').data('bs.modal', null);
      }
    };

    Utility.modalForm(opts);

  } else {
    Utility.alert({'message': ret_data.error});
  }

}

function keySaved(ret_data) {
  if(parseInt(ret_data.result, 10) == 1) {
    $('.info_status').html('');
    unsaved = false;
    location.href = location.href;
  } else {
    unlockWait();
    Utility.alert({message: ret_data.error});
  }
}

function delSetting(id) {

  Utility.confirm({
    'message': Utility.getLang(module_name, 'CONFIRM_DELETION'),
    'onOk'   : function() {
      var data = {id: id};
      $.ajax({
        type    : "POST",
        url     : main_host + 'admin/' + module_name + '/del/',
        data    : data,
        dataType: "json",
        success : function(ret_data) {
          if(parseInt(ret_data.result, 10) == 1) {
            location.href = location.href;
          } else {
            Utility.alert({message: ret_data.error});
          }
        }
      });
    }
  });
}

function savedForm(ret_data) {
  if(parseInt(ret_data.result, 10) == 1) {
    unlockWait();
    Utility.alert({message: Utility.getLang('settaggi', 'SAVED')});
  } else {
    Utility.alert({message: ret_data.error});
  }
}