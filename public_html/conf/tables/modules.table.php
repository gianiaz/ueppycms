<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (08/06/16, 9.57)                                                                     **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
$table = 'modules';
$cp    = ['id' => 'int'];
$ci    = ['modulo'     => 'varchar(20)',
          'istanza'    => 'varchar(20)',
          'principale' => 'int(1)',
          'view'       => 'varchar(20)',
          'posizione'  => 'varchar(20)',
          'template'   => 'varchar(50)',
          'ordine'     => 'int(1)',
          'created_at' => 'datetime',
          'updated_at' => 'datetime'];
$cd    = [];