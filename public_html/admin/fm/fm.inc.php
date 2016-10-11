<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (24/10/15, 13.59)                                                                     **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
if($operator->hasRights($module_name)) {

  $_SESSION['simogeoFM']['ROOT'] = DOC_ROOT.REL_ROOT;

} else {
  $_SESSION['simogeoFM'] = false;
  $errore                = Traduzioni::getLang('default', 'NOT_AUTH');
}
