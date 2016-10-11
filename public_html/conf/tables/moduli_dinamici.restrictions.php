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
$restrizioni           = array();

// nome
  $restrizione           = array();
  $restrizione['regola'] = 'Obbligatorio';

$restrizioni['nome'][] = $restrizione;

  $restrizione           = array();
  $restrizione['regola'] = 'StrRange';
  $restrizione['args']   = array('min' => 1, 'max' => 255);

$restrizioni['nome'][] = $restrizione;

// nome
  $restrizione           = array();
  $restrizione['regola'] = 'StrRange';
  $restrizione['args']   = array('min' => 1, 'max' => 255);

$restrizioni['style'][] = $restrizione;
?>