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
$table = 'freemenu';
$cp    = ['id' => 'int'];
$ci    = ['nome'               => 'varchar(255)',
          'freemenu_styles_id' => 'int',
          'updated_at'         => 'datetime',
          'created_at'         => 'datetime'];
$cd    = ['titolo' => 'varchar(255)',
          'dati'   => 'text'];