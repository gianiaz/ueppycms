<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (21/06/16, 11.26)                                                                      **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
use Ueppy\core\Traduzioni;
use Ueppy\utils\Utility;

switch($act) {

  case 'insert':

    $metaConf = json_decode(file_get_contents(CONF_DIR.'meta.json'), true);

    $fileDati = DOC_ROOT.REL_ROOT.UPLOAD.'cache/meta-'.$_POST['lingue'].'.json';

    foreach($_POST['meta'] as $sezione => $casi) {
      foreach($casi as $caso => $dati) {
        $metaConf[$sezione]['casi'][$caso]['htmltitle']   = $dati['htmltitle'];
        $metaConf[$sezione]['casi'][$caso]['description'] = $dati['description'];
      }
    }

    file_put_contents($fileDati, json_encode($metaConf));

    $ajaxReturn['result'] = 1;
    break;
  case 'load_meta':

    if(isset($_POST['lang']) && $_POST['lang']) {
      $metaConf = json_decode(file_get_contents(CONF_DIR.'meta.json'), true);

      $fileDati = DOC_ROOT.REL_ROOT.UPLOAD.'cache/meta-'.$_POST['lang'].'.json';

      if(file_exists($fileDati)) {
        $metaConf = array_replace_recursive($metaConf, json_decode(file_get_contents($fileDati), true));
      }


      $ajaxReturn['result'] = 1;
      $ajaxReturn['data']   = $metaConf;

    } else {
      $ajaxReturn['result'] = 0;
      if(isset($operator) && $operator->isSuperAdmin()) {
        $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' ('.__FILE__.','.__LINE__.')';
      } else {
        $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' (Error: '.__LINE__.')';
      }
    }

    break;
  case '':
    /** SELECT LINGUE **/
    $smarty->assign('lingue', $lingue);

    $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('default', 'SEO_TOOLS'));

    // caret plugin per conoscere posizione cursore
    $pathJS[] = DOC_ROOT.REL_ROOT.'bower_components/jquery-caret/jquery.caret.js';

    $opts         = [];
    $opts['path'] = $pathJS;

    $smarty->addJS($opts);

    $footerButtons[] = $BUTTONS['btnSave'];

    break;

  default:
    $urlParams = 'cmd/'.$cmd;

    header('Location:'.$lm->get($urlParams));

    die;

}
