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
// array delle restrizioni sui settaggi dei campi
$restrizioni = [];

// nome
$restrizione           = [];
$restrizione['regola'] = 'Obbligatorio';

$restrizioni['nome'][] = $restrizione;

$restrizione           = [];
$restrizione['regola'] = 'StrRange';
$restrizione['args']   = ['min' => 1, 'max' => 255];

$restrizioni['titolo'][] = $restrizione;

// ordine
$restrizione           = [];
$restrizione['regola'] = 'Numeric';

$restrizioni['ordine'][] = $restrizione;

// attivo
$restrizione           = [];
$restrizione['regola'] = 'InArray';
$restrizione['args']   = ['accepted' => ['0', '1']];

$restrizioni['attivo'][] = $restrizione;

// gruppo
$restrizione           = [];
$restrizione['regola'] = 'StrRange';
$restrizione['args']   = ['min' => 1, 'max' => 255];

$restrizioni['gruppo'][] = $restrizione;

// url
$restrizione           = [];
$restrizione['regola'] = 'StrRange';
$restrizione['args']   = ['min' => 0, 'max' => 255];

$restrizioni['link'][] = $restrizione;