<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (15/04/16, 18.51)                                                                     **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
if(isset($_SESSION['OLD_LOG_INFO'])) {
  $_SESSION['LOG_INFO']     = $_SESSION['OLD_LOG_INFO'];
  $_SESSION['OLD_LOG_INFO'] = false;
  
  $urlParams ='';
  Header('Location:'.$lm->get($urlParams));
  die;
}