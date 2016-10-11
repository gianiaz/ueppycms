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
  $restrizione['regola'] = 'Pattern';
  $restrizione['args']   = array('regex' => '/^[A-Z\_]+$/');

$restrizioni['chiave'][] = $restrizione;

// chiave_ext
$restrizione           = array();

  $restrizione['regola'] = 'Obbligatorio';

$restrizioni['chiave_ext'][] = $restrizione;

  $restrizione           = array();
  $restrizione['regola'] = 'StrRange';
  $restrizione['args']   = array('min' => 1, 'max' => '255');

$restrizioni['chiave_ext'][] = $restrizione;

// id_gruppo_settaggi
$restrizione           = array();

  $restrizione['regola'] = 'Obbligatorio';

$restrizioni['id_gruppo_settaggi'][] = $restrizione;

  $restrizione           = array();
  $restrizione['regola'] = 'Numeric';

$restrizioni['id_gruppo_settaggi'][] = $restrizione;

// descrizione
$restrizione           = array();

  $restrizione['regola'] = 'Obbligatorio';

$restrizioni['descrizione'][] = $restrizione;

  $restrizione           = array();
  $restrizione['regola'] = 'StrRange';
  $restrizione['args']   = array('min' => 1, 'max' => '255');

$restrizioni['descrizione'][] = $restrizione;

// editabile
$restrizione           = array();

  $restrizione           = array();
  $restrizione['regola'] = 'InArray';
  $restrizione['args']   = array('accepted' => array('0', '1'));

$restrizioni['editabile'][] = $restrizione;

// super_admin
$restrizione           = array();

  $restrizione           = array();
  $restrizione['regola'] = 'InArray';
  $restrizione['args']   = array('accepted' => array('0', '1'));

$restrizioni['super_admin'][] = $restrizione;

// type
$restrizione           = array();

  $restrizione           = array();
  $restrizione['regola'] = 'InArray';
  $restrizione['args']   = array('accepted' => array('text', 'boolean'));

$restrizioni['type'][] = $restrizione;
?>