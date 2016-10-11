<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (20/05/16, 18.12)                                                                      **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
$table = 'vetrina';
$cp    = ['id' => 'int'];
$ci    = ['ordine'     => 'int',
          'gruppo'     => 'string(255)',
          'nome'       => 'varchar(255)',
          'attivo'     => 'int(1)',
          'created_at' => 'datetime',
          'updated_at' => 'datetime'];
$cd    = ['img'         => 'image',
          'titolo'      => 'varchar(255)',
          'sottotitolo' => 'varchar(255)',
          'img_alt'     => 'varchar(255)',
          'testo'       => 'text',
          'url'         => 'varchar(255)'];
