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
$table = 'news_category';
$cp    = ['id' => 'int'];
$ci    = ['attivo'      => 'tinyint(1)',
          'predefinita' => 'tinyint(1)',
          'template'    => 'varchar(255)',
          'ordine'      => 'int(3)'];
$cd    = ['name'        => 'varchar(255)',
          'href'        => 'varchar(255)',
          'htmltitle'   => 'varchar(255)',
          'description' => 'varchar(255)',
          'testo'       => 'text'];