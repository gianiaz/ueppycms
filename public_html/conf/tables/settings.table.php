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
$table = 'settings';
$cp    = ['id' => 'int'];
$ci    = ['chiave'             => 'varchar(255)',
          'chiave_ext'         => 'varchar(255)',
          'gruppo_settaggi_id' => 'int',
          'valore'             => 'varchar(255)',
          'descrizione'        => 'text',
          'editabile'          => 'int(1)',
          'super_admin'        => 'int(1)',
          'type'               => 'string',
          'ordine'             => 'int(1)',
          'created_at'         => 'datetime',
          'updated_at'         => 'datetime'
];
$cd    = [];