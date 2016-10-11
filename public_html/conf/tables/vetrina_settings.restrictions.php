<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (24/05/16, 10.41)                                                                     **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
$restrizioni = [];

// gruppo

$restrizione           = [];
$restrizione['regola'] = 'Obbligatorio';

$restrizioni['gruppo'][] = $restrizione;

// gruppo
  $restrizione           = array();
  $restrizione['regola'] = 'StrRange';
  $restrizione['args']   = array('min' => 1, 'max' => 255);

$restrizioni['gruppo'][] = $restrizione;


// dimensioni

$restrizione           = [];
$restrizione['regola'] = 'Obbligatorio';

$restrizioni['dimensioni'][] = $restrizione;

// dimensioni 
 
  $restrizione           = array();
  $restrizione['regola'] = 'Pattern';
  $restrizione['args']   = array('regex' => '/^[\d]+x[\d]+$/');

$restrizioni['dimensioni'][] = $restrizione;
