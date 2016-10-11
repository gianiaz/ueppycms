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

// sigla
  $restrizione           = array();
  $restrizione['regola'] = 'Obbligatorio';

$restrizioni['sigla'][] = $restrizione;

  $restrizione           = array();
  $restrizione['regola'] = 'StrRange';
  $restrizione['args']   = array('min' => 2, 'max' => 2);

$restrizioni['sigla'][] = $restrizione;

// estesa
  $restrizione           = array();
  $restrizione['regola'] = 'Obbligatorio';

$restrizioni['estesa'][] = $restrizione;

  $restrizione           = array();
  $restrizione['regola'] = 'StrRange';
  $restrizione['args']   = array('min' => 1, 'max' => 255);

$restrizioni['estesa'][] = $restrizione;

// img0_alt
  $restrizione           = array();
  $restrizione['regola'] = 'StrRange';
  $restrizione['args']   = array('min' => 1, 'max' => 255);

$restrizioni['img0_alt'][] = $restrizione;

// img0_title
  $restrizione               = array();
  $restrizione['regola']     = 'StrRange';
  $restrizione['args']       = array('min' => 1, 'max' => 255);

$restrizioni['img0_title'][] = $restrizione;

// attivo_admin
  $restrizione           = array();
  $restrizione['regola'] = 'InArray';
  $restrizione['args']   = array('accepted' => array('-1', '0', '1'));

$restrizioni['attivo_admin'][] = $restrizione;
?>