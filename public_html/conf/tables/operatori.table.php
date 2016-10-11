<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00                                                                                       **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
// dati per la tabella operatori
$table = 'operatori';
$cp    = ['id' => 'int'];
$ci    = ['nomecompleto' => 'varchar(255)',
          'avatar'       => 'image',
          'username'     => 'varchar(20)',
          'passwd'       => 'varchar(65)',
          'gruppi_id'       => 'int(11)',
          'email'        => 'varchar(255)',
          'attivo'       => 'int(1)',
          'super_admin'  => 'int(1)',
          'cancellabile' => 'int(1)',
          'level'        => 'int(2)',
          'created_at'   => 'datetime',
          'updated_at'   => 'datetime'];
$cd    = [];