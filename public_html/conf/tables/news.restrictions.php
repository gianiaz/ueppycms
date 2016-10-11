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
// array delle restrizioni sui settaggi dei campi
$restrizioni = [];

// stato
$restrizione           = [];
$restrizione['regola'] = 'InArray';
$restrizione['args']   = ['accepted' => ['ATTIVO', 'SPENTA', 'SCHEDULATA', 'CANCELLATA']];

$restrizioni['stato'][] = $restrizione;

/** CAMPI DI LINGUA **/

// titolo

$restrizione           = [];
$restrizione['regola'] = 'Obbligatorio';

$restrizioni['titolo'][] = $restrizione;

$restrizione           = [];
$restrizione['regola'] = 'StrRange';
$restrizione['args']   = ['min' => 1, 'max' => 255];

$restrizioni['titolo'][] = $restrizione;

// description
$restrizione           = [];
$restrizione['regola'] = 'StrRange';
$restrizione['args']   = ['min' => 1, 'max' => 255];

$restrizioni['description'][] = $restrizione;

// htmltitle
$restrizione           = [];
$restrizione['regola'] = 'StrRange';
$restrizione['args']   = ['min' => 1, 'max' => 255];

$restrizioni['htmltitle'][] = $restrizione;

// attivo
$restrizione           = [];
$restrizione['regola'] = 'InArray';
$restrizione['args']   = ['accepted' => ['0', '1']];

$restrizioni['lingua_attiva'][] = $restrizione;

// commenti
$restrizione           = [];
$restrizione['regola'] = 'InArray';
$restrizione['args']   = ['accepted' => ['0', '1']];

$restrizioni['commenti'][] = $restrizione;

?>