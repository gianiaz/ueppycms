<?php
/*****************/
/***ueppy3.4.00***/
/*****************/
/**  CHANGELOG  **/
/**************************************************************************************************/
/** v.3.4.00 (08/10/15, 6.34)                                                                   **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
use Ueppy\core\Operatore;
use Ueppy\utils\Utility;
use Ueppy\core\Traduzioni;

$pathCSS[] = 'https://fonts.googleapis.com/css?family=Raleway:400,600';

$opzioni         = [];
$opzioni['path'] = DOC_ROOT.REL_ROOT.'admin/generic/clock.js';

$smarty->removeJS($opzioni);


if($act == 'entra') {

  $options                  = [];
  $options['tableFilename'] = 'operatori';
  $operator                 = new Operatore($options);

  $loginResult = $operator->login($_POST['user'], $_POST['password']);

  if($loginResult) {

    $LOGGER->addLine(['text' => 'Login eseguito, (user: '.$_POST['user'].') - '.$_SERVER['REMOTE_ADDR'], 'azione' => 'LOGIN']);
    if(Utility::isPositiveInt($_POST['remember'])) {
      setcookie('upycms_user', $_POST['user'], time() + (30 * 24 * 3600), '/');
      setcookie('upycms_pass', $loginResult, time() + (30 * 24 * 3600), '/');
    }
    $urlParams = '';
    header('Location:'.$lm->get($urlParams));
  } else {

    $LOGGER->addLine(['text' => 'Login fallito (user:'.$_POST['user'].', pass:'.$_POST['password'].') - '.$_SERVER['REMOTE_ADDR'], 'azione' => 'LOGIN']);

    $_SESSION['login_error'] = Traduzioni::getLang($module_name, 'LOGIN_FALLITO');

    $urlParams = 'cmd/login';
    header('Location:'.$lm->get($urlParams));

  }

}

if(isset($_SESSION['login_error']) && $_SESSION['login_error']) {

  $smarty->assign('login_error', $_SESSION['login_error']);

//  unset($_SESSION['login_error']);

}