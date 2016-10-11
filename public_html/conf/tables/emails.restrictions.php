<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/************************************************************************************************/
/** v.1.00 (10/05/2016)                                                                        **/
/** - Versione stabile                                                                         **/
/**                                                                                            **/
/************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                    **/
/** copyright: Ueppy s.r.l                                                                     **/
/************************************************************************************************/

$restrizioni = [];

// nome
$restrizione           = [];
$restrizione['regola'] = 'Obbligatorio';

$restrizioni['nome'][] = $restrizione;

$restrizione           = [];
$restrizione['regola'] = 'Pattern';
$restrizione['args']   = ['regex' => '/^[A-Z\_]+$/'];

$restrizioni['nome'][] = $restrizione;

// oggetto
$restrizione           = [];
$restrizione['regola'] = 'Obbligatorio';

$restrizioni['oggetto'][] = $restrizione;

$restrizione           = [];
$restrizione['regola'] = 'StrRange';
$restrizione['args']   = ['min' => 1, 'max' => 255];

$restrizioni['oggetto'][] = $restrizione;

// descrizione
$restrizione           = [];
$restrizione['regola'] = 'StrRange';
$restrizione['args']   = ['min' => 1, 'max' => 255];

$restrizioni['descrizione'][] = $restrizione;

// superadmin
$restrizione           = [];
$restrizione['regola'] = 'InArray';
$restrizione['args']   = ['accepted' => ['0', '1']];

$restrizioni['superadmin'][] = $restrizione;
?>