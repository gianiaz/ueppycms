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
$table = 'menu';
$cp    = ['id' => 'int'];

$ci = ['nomefile'    => 'varchar(255)',
       'attivo'      => 'int(1)',
       'eliminato'   => 'int(1)',
       'ordine'      => 'int(3)',
       'level'       => 'int(3)',
       'is_category' => 'int(1)',
       'template'    => 'varchar(255)',
       'parent'      => 'int(3)',
       'created_at'  => 'datetime',
       'updated_at'  => 'datetime',
       'superadmin'  => 'int',
       'pubdate'     => 'date'];

$cd = ['dicitura'     => 'string',
       'titolo_breve' => 'string',
       'keywords'     => 'string',
       'htmltitle'    => 'string',
       'description'  => 'string',
       'href'         => 'string',
       'img0'         => 'image',
       'img0_alt'     => 'string',
       'img0_title'   => 'string',
       'img1'         => 'image',
       'img1_alt'     => 'string',
       'img1_title'   => 'string'
];