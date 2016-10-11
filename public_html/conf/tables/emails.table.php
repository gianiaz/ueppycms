<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/************************************************************************************************/
/** v.1.00 (10/05/2016)                                                                        **/
/** - Versione stabile                                                                         **/
/**                                                                                            **/
/************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                    **/
/** copyright: Ueppy s.r.l                                                                     **/
/************************************************************************************************/
$table = 'emails';
$cp    = ['id' => 'int'];
$ci    = ['nome'        => 'varchar(255)',
          'descrizione' => 'textarea',
          'oggetto'     => 'varchar(255)',
          'chiavi'      => 'varchar(255)',
          'testo'       => 'text',
          'superadmin'  => 'int',
          'created_at'  => 'datetime',
          'updated_at'  => 'datetime'];
$cd    = [];