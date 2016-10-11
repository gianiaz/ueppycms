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
// attivo
  $restrizione           = array();
  $restrizione['regola'] = 'InArray';
  $restrizione['args']   = array('accepted' => array('0', '1'));

$restrizioni['attivo'][] = $restrizione;

// template
  $restrizione           = array();
  $restrizione['regola'] = 'Obbligatorio';

$restrizioni['template'][] = $restrizione;

  $restrizione           = array();
  $restrizione['regola'] = 'StrRange';
  $restrizione['args']   = array('min' => 1, 'max' => 30);

$restrizioni['template'][] = $restrizione;

// ordine
  $restrizione           = array();
  $restrizione['regola'] = 'Numeric';

$restrizioni['ordine'][] = $restrizione;

// MULTILINGUA

// name
  $restrizione           = array();
  $restrizione['regola'] = 'Obbligatorio';

$restrizioni['name'][] = $restrizione;

$restrizione           = array();
  $restrizione['regola'] = 'StrRange';
  $restrizione['args']   = array('min' => 1, 'max' => 255);

$restrizioni['name'][] = $restrizione;

// href
  $restrizione           = array();
  $restrizione['regola'] = 'Obbligatorio';

$restrizioni['href'][] = $restrizione;

$restrizione           = array();
  $restrizione['regola'] = 'StrRange';
  $restrizione['args']   = array('min' => 1, 'max' => 255);

$restrizioni['href'][] = $restrizione;

// htmltitle
  $restrizione           = array();
  $restrizione['regola'] = 'StrRange';
  $restrizione['args']   = array('min' => 1, 'max' => 255);

$restrizioni['htmltitle'][] = $restrizione;

// keywords
  $restrizione           = array();
  $restrizione['regola'] = 'StrRange';
  $restrizione['args']   = array('min' => 1, 'max' => 255);

$restrizioni['keywords'][] = $restrizione;

// description
  $restrizione           = array();
  $restrizione['regola'] = 'StrRange';
  $restrizione['args']   = array('min' => 1, 'max' => 255);

$restrizioni['description'][] = $restrizione;
?>