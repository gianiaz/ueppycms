<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/*************************************************************/
/** v.1.00                                                  **/
/** - Versione stabile                                      **/
/**                                                         **/
/*************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com> **/
/** copyright: Ueppy s.r.l                                  **/
/*************************************************************/
$table = 'gruppi';
$cp    = ['id' => 'int'];
$ci    = ['nome'         => 'varchar(150)',
          'attivo'       => 'int(1)',
          'all_elements' => 'int(1)',
          'cancellabile' => 'int(1)',
          'ordine'       => 'int(2)',
          'created_at'   => 'datetime',
          'updated_at'   => 'datetime'];
$cd    = [];