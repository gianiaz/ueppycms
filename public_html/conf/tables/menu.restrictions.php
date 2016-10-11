<?php
/*****************/
/***ueppy3.1.01***/
/*****************/
/**  CHANGELOG  **/
/*************************************************************/
/** v.3.1.01 (21/05/2013)                                   **/
/** - Aggiunta obbligatorietà su dicitura                   **/
/**                                                         **/
/** v.3.1.00                                                **/
/** - Versione stabile                                      **/
/**                                                         **/
/*************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com> **/
/** copyright: Ueppy s.r.l                                  **/
/*************************************************************/
// array delle restrizioni sui settaggi dei campi
$restrizioni = [];

// nomefile
$restrizione           = [];
$restrizione['regola'] = 'Obbligatorio';

$restrizioni['nomefile'][] = $restrizione;

$restrizione           = [];
$restrizione['regola'] = 'StrRange';
$restrizione['args']   = ['min' => 1, 'max' => 255];

$restrizioni['nomefile'][] = $restrizione;

$restrizione           = [];
$restrizione['regola'] = 'Obbligatorio';

// dicitura
$restrizioni['dicitura'][] = $restrizione;

$restrizione           = [];
$restrizione['regola'] = 'StrRange';
$restrizione['args']   = ['min' => 1, 'max' => 255];

$restrizioni['dicitura'][] = $restrizione;


// robots
$restrizione           = [];
$restrizione['regola'] = 'InArray';
$restrizione['args']   = ['accepted' => ['index,follow', 'index,nofollow', 'noindex,follow', 'noindex,nofollow']];

$restrizioni['robots'][] = $restrizione;


// attivo
$restrizione           = [];
$restrizione['regola'] = 'InArray';
$restrizione['args']   = ['accepted' => ['-1', '0', '1']];

$restrizioni['attivo'][] = $restrizione;

// ordine
$restrizione           = [];
$restrizione['regola'] = 'Numeric';

$restrizioni['ordine'][] = $restrizione;

// name

/** CAMPI DI LINGUA **/

$restrizione           = [];
$restrizione['regola'] = 'StrRange';
$restrizione['args']   = ['min' => 1, 'max' => 255];

$restrizioni['href'][] = $restrizione;

// htmltitle
$restrizione           = [];
$restrizione['regola'] = 'StrRange';
$restrizione['args']   = ['min' => 1, 'max' => 255];

$restrizioni['htmltitle'][] = $restrizione;

// keywords
$restrizione           = [];
$restrizione['regola'] = 'StrRange';
$restrizione['args']   = ['min' => 1, 'max' => 255];

$restrizioni['keywords'][] = $restrizione;

// description
$restrizione           = [];
$restrizione['regola'] = 'StrRange';
$restrizione['args']   = ['min' => 1, 'max' => 255];

$restrizioni['description'][] = $restrizione;
?>