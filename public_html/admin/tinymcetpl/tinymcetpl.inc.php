<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/************************************************************************************************/
/** v.1.00 (10/05/2016)                                                                        **/
/** - Versione stabile                                                                         **/
/**                                                                                            **/
/************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                    **/
/** copyright: Ueppy s.r.l                                                                     **/
/************************************************************************************************/
use Ueppy\core\Ueppy;
use Ueppy\utils\Utility;
use Ueppy\core\Traduzioni;

switch($act) {

  case 'insert':

    if(!isset($_POST['filename']) || !$_POST['filename'] || !isset($_POST['content']) || !$_POST['content']) {

      $errori = [];

      if(!isset($_POST['filename']) || !$_POST['filename']) {
        $errori['filename'] = Traduzioni::getLang($module_name, 'NOME_VUOTO');
      }

      if(!isset($_POST['content']) || !$_POST['content']) {
        $errori['content'] = Traduzioni::getLang($module_name, 'CONTENT_VUOTO');
      }

      $ajaxReturn['result'] = 0;
      $ajaxReturn['errors'] = $errori;
      $ajaxReturn['wrongs'] = array_keys($ajaxReturn['errors']);

    } else {

      $_SESSION[$module_name] = false;

      $file_name      = DOC_ROOT.REL_ROOT.UPLOAD.'tinymcetpl/'.$_POST['filename'].'.tp';
      $desc_file_name = DOC_ROOT.REL_ROOT.UPLOAD.'tinymcetpl/'.$_POST['filename'].'.desc';

      if(!isset($_POST['nome']) || !$_POST['nome']) {
        $metadata = $_POST['filename'].'§';
      } else {
        $metadata = $_POST['nome'].'§';
      }

      if(isset($_POST['descrizione']) && $_POST['descrizione']) {
        $metadata .= $_POST['descrizione'];
      }

      file_put_contents($desc_file_name, $metadata);

      file_put_contents($file_name, $_POST['content']);

      $ajaxReturn['result'] = 1;

    }

    break;

  case 'new':

    Ueppy::includeTinymce();

    $file = [];

    $file['content']  = '';
    $file['filename'] = '';

    if(Utility::isPositiveInt($_POST['id'])) {

      $id = $_POST['id'] - 1;

      if(!file_exists(DOC_ROOT.REL_ROOT.UPLOAD.'tinymcetpl')) {
        Utility::mkdirp(DOC_ROOT.REL_ROOT.UPLOAD.'tinymcetpl');
      }

      $files = glob(DOC_ROOT.REL_ROOT.UPLOAD.'tinymcetpl/*.tp');

      sort($files);

      $file['content']  = file_get_contents($files[$id]);
      $file['filename'] = explode('.', basename($files[$id]));
      $file['filename'] = array_shift($file['filename']);

      $nm = explode('.', basename($files[$id]));
      $nm = array_shift($nm);

      $fname_desc = dirname($files[$id]).'/'.$nm.'.desc';

      $metadata = explode('§', file_get_contents($fname_desc));

      $file['nome']        = $metadata[0];
      $file['descrizione'] = $metadata[1];

      $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('default', 'MODIFY'));

    } else {

      $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('default', 'INSERT'));

    }

    $smarty->assign('file', $file);

    $headerButtons = $BUTTONS['set']['save-close-new'];
    $footerButtons = $BUTTONS['set']['save-close'];

    break;

  case 'getlist':
    if(!file_exists(DOC_ROOT.REL_ROOT.UPLOAD.'tinymcetpl')) {
      Utility::mkdirp(DOC_ROOT.REL_ROOT.UPLOAD.'tinymcetpl');
    }

    $files = glob(DOC_ROOT.REL_ROOT.UPLOAD.'tinymcetpl/*.desc');

    $list = [];

    if(is_array($files) && count($files)) {

      sort($files);

      foreach($files as $key => $file) {
        $metadata              = explode('§', file_get_contents($file));
        $record                = [];
        $record['id']          = ($key + 1);
        $record['file']        = explode('.', basename($file));
        $record['file']        = array_shift($record['file']);
        $record['nome']        = $metadata[0];
        $record['descrizione'] = $metadata[1];
        $list[]                = $record;
      }

    }

    $ajaxReturn['data']   = $list;
    $ajaxReturn['result'] = 1;

    break;

  case 'del':

    if(Utility::isPositiveInt($_POST['id'])) {

      $id = $_POST['id'] - 1;

      $files = glob(DOC_ROOT.REL_ROOT.UPLOAD.'tinymcetpl/*.tp');

      sort($files);

      $nm = explode('.', $files[$id]);
      $nm = array_shift($nm);

      $fname_desc = $nm.'.desc';

      unlink($files[$id]);
      unlink($fname_desc);

      $ajaxReturn['result'] = 1;

    } else {

      $ajaxReturn['result'] = 0;
      $ajaxReturn['error']  = Traduzioni::getLang('default', 'NOT_AUTH');

    }

    break;

  case '':
    /* elenco */

    $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('default', 'LIST_ELEMENTS'));

    $headerButtons[] = $BUTTONS['btnNew'];
    break;

  default:
    $urlParams ='cmd/'.$cmd;

    header('Location:'.$lm->get($urlParams));

    die;
    break;

}
