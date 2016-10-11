<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (20/05/16, 12.18)                                                                     **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
$table = 'commenti_news';
$cp    = ['id' => 'int'];
$ci    = ['parent_id'  => 'int',
          'commento'   => 'text',
          'nome'       => 'varchar(255)',
          'email'      => 'varchar(255)',
          'valido'     => 'int(1)',
          'ip'         => 'varchar(15)',
          'fb_data'    => 'text',
          'created_at' => 'datetime',
          'updated_at' => 'datetime'];
$cd    = [];