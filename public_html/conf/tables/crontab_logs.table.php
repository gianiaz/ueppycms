<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (24/05/16, 7.12)                                                                      **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
$table = 'crontab_logs';
$cp    = ['id' => 'int'];
$ci    = ['file'       => 'text',
          'autore'     => 'varchar(100)',
          'text'       => 'text',
          'created_at' => 'datetime'];
$cd    = [];
