<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (17/06/16, 17.01)                                                                      **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/

// array delle restrizioni sui settaggi dei campi
$restrizioni = [];

// commenti
$restrizione           = [];
$restrizione['regola'] = 'InArray';
$restrizione['args']   = ['accepted' => ['0', '1']];

$restrizioni['commenti'][] = $restrizione;

// sottotitolo
$restrizione           = [];
$restrizione['regola'] = 'StrRange';
$restrizione['args']   = ['min' => 1, 'max' => 255];

$restrizioni['sottotitolo'][] = $restrizione;
