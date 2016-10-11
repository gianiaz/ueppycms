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
// nomecompleto
$restrizione           = array();

  $restrizione['regola'] = 'Obbligatorio';

$restrizioni['nome'][] = $restrizione;

  $restrizione           = array();
  $restrizione['regola'] = 'StrRange';
  $restrizione['args']   = array('min' => 1, 'max' => 255);

$restrizioni['nome'][] = $restrizione;

// abilitato
$restrizione           = array();

  $restrizione           = array();
  $restrizione['regola'] = 'InArray';
  $restrizione['args']   = array('accepted' => array('0', '1'));

$restrizioni['abilitato'][] = $restrizione;

// all_elements
$restrizione           = array();

  $restrizione           = array();
  $restrizione['regola'] = 'InArray';
  $restrizione['args']   = array('accepted' => array('0', '1'));

$restrizioni['all_elements'][] = $restrizione;

// cancellabile
$restrizione           = array();

  $restrizione           = array();
  $restrizione['regola'] = 'InArray';
  $restrizione['args']   = array('accepted' => array('0', '1'));

$restrizioni['cancellabile'][] = $restrizione;

// ordine
$restrizione           = array();

  $restrizione           = array();
  $restrizione['regola'] = 'Numeric';

$restrizioni['ordine'][] = $restrizione;
?>
