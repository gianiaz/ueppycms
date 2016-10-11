<?php
/*****************/
/***ueppy3.1.00***/
/*****************/
/**  CHANGELOG  **/
/*************************************************************/
/** v.3.1.00                                                **/
/** - Versione stabile                                      **/
/**                                                         **/
/*************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com> **/
/** copyright: Ueppy s.r.l                                  **/
/*************************************************************/
$table = 'backup';
$cp    = array('id'=>'int');
$ci    = array('nome'        => 'string',
               'tabelle'     => 'string',
               'directories' => 'string',
               'cron_h'      => 'string',
               'cron_dom'    => 'string',
               'cron_dow'    => 'string',
               'ftp'         => 'int',
               'email'       => 'int',
               'cron'        => 'int');
$cd    = array();
?>