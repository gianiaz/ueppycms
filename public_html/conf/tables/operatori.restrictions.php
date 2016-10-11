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
$restrizioni = [];

// nomecompleto
$restrizione = [];

$restrizione['regola'] = 'Obbligatorio';

$restrizioni['nomecompleto'][] = $restrizione;

$restrizione           = [];
$restrizione['regola'] = 'StrRange';
$restrizione['args']   = ['min' => 1, 'max' => 255];

$restrizioni['nomecompleto'][] = $restrizione;

// nomecompleto
$restrizione = [];

$restrizione['regola'] = 'Obbligatorio';

$restrizioni['username'][] = $restrizione;

$restrizione           = [];
$restrizione['regola'] = 'Pattern';
$restrizione['args']   = ['regex' => '/^\w{5,14}$/'];

$restrizioni['username'][] = $restrizione;

// gruppo
$restrizione = [];

$restrizione['regola'] = 'Obbligatorio';

$restrizioni['gruppo'][] = $restrizione;

$restrizione           = [];
$restrizione['regola'] = 'Numeric';

$restrizioni['gruppo'][] = $restrizione;

// email
$restrizione = [];

$restrizione['regola'] = 'Obbligatorio';

$restrizioni['email'][] = $restrizione;

$restrizione['regola'] = 'Email';

$restrizioni['email'][] = $restrizione;

// active
$restrizione           = [];
$restrizione['regola'] = 'InArray';
$restrizione['args']   = ['accepted' => ['0', '1']];

$restrizioni['active'][] = $restrizione;

// super_admin
$restrizione = [];

$restrizione           = [];
$restrizione['regola'] = 'InArray';
$restrizione['args']   = ['accepted' => ['0', '1']];

$restrizioni['super_admin'][] = $restrizione;

// upload_classico
$restrizione           = [];
$restrizione['regola'] = 'InArray';
$restrizione['args']   = ['accepted' => ['0', '1']];

$restrizioni['upload_classico'][] = $restrizione;

// cancellabile
$restrizione = [];

$restrizione           = [];
$restrizione['regola'] = 'InArray';
$restrizione['args']   = ['accepted' => ['0', '1']];

$restrizioni['cancellabile'][] = $restrizione;

// level
$restrizione           = [];
$restrizione['regola'] = 'Numeric';

$restrizioni['level'][] = $restrizione;

// nomecompleto
$restrizione = [];

$restrizione['regola'] = 'Obbligatorio';

$restrizioni['passwd'][] = $restrizione;

$restrizione           = [];
$restrizione['regola'] = 'Pattern';
$restrizione['args']   = ['regex' => '/^.{5,14}$/'];

$restrizioni['passwd'][] = $restrizione;