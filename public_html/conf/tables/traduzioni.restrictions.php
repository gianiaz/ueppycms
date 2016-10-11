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
$restrizioni           = array();

// chiave

$restrizione           = array();

  $restrizione['regola'] = 'Obbligatorio';

$restrizioni['chiave'][] = $restrizione;

  $restrizione           = array();
  $restrizione['regola'] = 'StrRange';
  $restrizione['args']   = array('min' => 1, 'max' => 100);

$restrizioni['chiave'][] = $restrizione;

// modulo
$restrizione           = array();

  $restrizione['regola'] = 'Obbligatorio';

$restrizioni['modulo'][] = $restrizione;

  $restrizione           = array();
  $restrizione['regola'] = 'StrRange';
  $restrizione['args']   = array('min' => 1, 'max' => 100);

$restrizioni['modulo'][] = $restrizione;

// sezione
$restrizione           = array();

  $restrizione['regola'] = 'Obbligatorio';

$restrizioni['sezione'][] = $restrizione;

  $restrizione           = array();
  $restrizione['regola'] = 'InArray';
  $restrizione['args']   = array('accepted' => array('admin', 'public'));

$restrizioni['sezione'][] = $restrizione;

// linguaggio
$restrizione           = array();

  $restrizione['regola'] = 'Obbligatorio';

$restrizioni['linguaggio'][] = $restrizione;

  $restrizione           = array();
  $restrizione['regola'] = 'InArray';
  $restrizione['args']   = array('accepted' => array('php', 'javascript'));

$restrizioni['linguaggio'][] = $restrizione;
?>