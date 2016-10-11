<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/*************************************************************/
/** v.1.00 (13/04/2016)                                     **/
/** - Versione stabile                                      **/
/**                                                         **/
/*************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com> **/
/** copyright: Ueppy s.r.l                                  **/
/*************************************************************/
$table = 'traduzioni';
$cp    = ['id' => 'int'];
$ci    = ['chiave'     => 'varchar(50)',
          'sezione'    => 'varchar(20)',
          'modulo'     => 'varchar(50)',
          'linguaggio' => 'enum("php", "javascript")',
          'created_at' => 'datetime',
          'updated_at' => 'datetime'];
$cd    = ['dicitura' => 'text'];