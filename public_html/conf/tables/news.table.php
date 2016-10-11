<?php
/***************/
/** v.1.01    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.01 (04/11/2015, 17.10)                                                                   **/
/** - Modificati i tipi e i nomi dei campi legati alla pubblicazione, rimosse keywords e counter **/
/**                                                                                              **/
/** v.1.00                                                                                       **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
$table = 'news';
$cp    = ['id' => 'int'];
$ci    = ['operatori_id'  => 'int',
          'stato'         => 'varchar(255)',
          'commenti'      => 'int(1)',
          'updated_at'    => 'datetime',
          'created_at'    => 'datetime',
          'attiva_dal'    => 'datetime',
          'disattiva_dal' => 'datetime',
          'operatori_id'  => 'int',
          'eliminato'     => 'int'];
$cd    = ['titolo'        => 'string',
          'htmltitle'     => 'string',
          'description'   => 'string',
          'href'          => 'string',
          'intro'         => 'text',
          'testo'         => 'text',
          'lingua_attiva' => 'int'];