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
$table = 'allegati';
$cp    = ['id' => 'int'];
$ci    = ['nomefile'    => 'file',
          'id_genitore' => 'string',
          'genitore'    => 'string',
          'time'        => 'int',
          'ordine'      => 'int',
          'estensione'  => 'string'
];
$cd    = ['descrizione' => 'string',
          'alt'         => 'string',
          'title'       => 'string'];