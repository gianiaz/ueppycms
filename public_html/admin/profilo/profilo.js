/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (07/06/16, 17.20)                                                                     **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
var module_name = 'profilo';

$(function() {
  Utility.tabIndexInit();

  afterSubmit = function(ret_data, $form) {
    Utility.unlockScreen();
    if(parseInt(ret_data.result, 10) === 1) {
      Ueppy.saveSuccess(ret_data.dati);
    } else {
      Ueppy.showFormErrors(ret_data.errors, ret_data.wrongs);
    }

  }
});