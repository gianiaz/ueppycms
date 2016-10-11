<?php
/*****************/
/*** v 1.00    ***/
/*****************/
/**  CHANGELOG  **/
/**************************************************************************************/
/** v.1.00                                                                         **/
/** - Versione stabile                                                               **/
/**                                                                                  **/
/**************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                          **/
/** copyright: Ueppy s.r.l                                                           **/
/**************************************************************************************/
$LOGGER->addLine(['text' => 'Eseguito logout', 'azione' => 'LOGIN']);
setcookie('upycms_user', '', time() + (30 * 24 * 3600), '/');
setcookie('upycms_pass', '', time() + (30 * 24 * 3600), '/');
unset($_SESSION['LOG_INFO']);
$urlParams = 'cmd/login';
header('Location:'.$lm->get($urlParams));
die;