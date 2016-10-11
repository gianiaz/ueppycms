<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (24/05/16, 14.12)                                                                      **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
use Ueppy\core\Traduzioni;
use Ueppy\core\Backup;
use Ueppy\utils\Utility;
use Ueppy\core\Dba;

$mainObjOptions                  = [];
$mainObjOptions['tableFilename'] = 'backup';

$Obj = new Backup($mainObjOptions);

switch($act) {

  case 'insert':

    if(Utility::isPositiveInt($_POST['id'])) {
      $Obj = $Obj->getById($_POST['id']);
    }

    $Obj->nome  = $_POST['nome'];
    $Obj->email = $_POST['email'];
    $Obj->ftp   = $_POST['ftp'];

    if(Utility::isPositiveInt($_POST['directories_all'])) {
      $Obj->directories = '*';
    } else {
      if(isset($_POST['directories']) && is_array($_POST['directories']) && count($_POST['directories'])) {
        $Obj->directories = implode(',', $_POST['directories']);
      } else {
        $Obj->directories = '';
      }
    }

    if(Utility::isPositiveInt($_POST['tabelle_all'])) {
      $Obj->tabelle = '*';
    } else {
      if(isset($_POST['tabelle']) && is_array($_POST['tabelle']) && count($_POST['tabelle'])) {
        $Obj->tabelle = implode(',', $_POST['tabelle']);
      } else {
        $Obj->tabelle = '';
      }
    }

    if(Utility::isPositiveInt($_POST['cron'])) {
      $Obj->cron     = '1';
      $Obj->cron_h   = $_POST['cron_h'];
      $Obj->cron_dom = $_POST['cron_dom'];
      $Obj->cron_dow = $_POST['cron_dow'];
    } else {
      $Obj->cron     = '0';
      $Obj->cron_h   = '*';
      $Obj->cron_dom = '*';
      $Obj->cron_dow = '*';
    }

    if($Obj->isValid()) {

      $result = $Obj->save();

      if($result) {

        $ajaxReturn['result'] = 1;
        $ajaxReturn['dati']   = $Obj->ajaxResponse();

      } else {

        $ajaxReturn['result'] = 0;
        $ajaxReturn['errors'] = [Traduzioni::getLang('default', 'ERRORE_INDEFINITO')];
        $ajaxReturn['wrongs'] = [];

      }

    } else {

      $opts['glue']         = false;
      $ajaxReturn['result'] = 0;
      $ajaxReturn['errors'] = $Obj->getErrors($opts);
      $ajaxReturn['wrongs'] = array_keys($Obj->wrongFields);

    }

    break;

  case 'upload':

    $DIR = DOC_ROOT.REL_ROOT.BACKUP_DIR;


    $DIR .= intval($_POST['id']).'/';

    $ext = explode('.', $_FILES['backup_file']['name']);
    $ext = array_pop($ext);
    $ext = strtolower($ext);

    if($_FILES['backup_file']['type'] == 'application/zip' && $ext == 'zip' && preg_match('/^\d{1,2}\-\d{1,2}\-\d{4}\_\d{1,2}\-\d{1,2}\.zip$/', $_FILES['backup_file']['name'])) {

      if(file_exists($DIR.$_FILES['backup_file']['name'])) {

        $ajaxReturn['result'] = 0;
        $ajaxReturn['error']  = Traduzioni::getLang($module_name, 'BACKUP_GIA_ESISTENTE');

      } else {
        move_uploaded_file($_FILES['backup_file']['tmp_name'], $DIR.$_FILES['backup_file']['name']);
        $ajaxReturn['result'] = 1;
      }

    } else {

      $ajaxReturn['result'] = 0;
      $ajaxReturn['error']  = Traduzioni::getLang($module_name, 'FILE_NON_VALIDO');

    }

    break;

  case 'dobackup':

    if(Utility::isPositiveInt($_POST['id'])) {
      $Obj = $Obj->getById($_POST['id']);

      if($Obj) {

        $Obj->doBackup();

        $ajaxReturn['result'] = 1;

      } else {
        $ajaxReturn['result'] = 0;
        if(isset($operator) && $operator->isSuperAdmin()) {
          $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' ('.__FILE__.','.__LINE__.')';
        } else {
          $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' (Error: '.__LINE__.')';
        }
      }
    } else {
      $ajaxReturn['result'] = 0;
      if(isset($operator) && $operator->isSuperAdmin()) {
        $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' ('.__FILE__.','.__LINE__.')';
      } else {
        $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' (Error: '.__LINE__.')';
      }
    }

    break;

  case 'delete_archive':

    if(Utility::isPositiveInt($_POST['id'])) {
      $Obj = $Obj->getById($_POST['id']);
      if($Obj) {
        $Obj->deleteArchive($_POST['title']);
        $ajaxReturn['result'] = 1;
      } else {
        $ajaxReturn['result'] = 0;
        if(isset($operator) && $operator->isSuperAdmin()) {
          $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' ('.__FILE__.','.__LINE__.')';
        } else {
          $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' (Error: '.__LINE__.')';
        }
      }
    } else {
      $ajaxReturn['result'] = 0;
      if(isset($operator) && $operator->isSuperAdmin()) {
        $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' ('.__FILE__.','.__LINE__.')';
      } else {
        $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' (Error: '.__LINE__.')';
      }
    }
    break;

  case 'restore_archive':

    if(Utility::isPositiveInt($_POST['id'])) {
      $Obj = $Obj->getById($_POST['id']);
      if($Obj) {
        $Obj->restoreArchive($_POST['title']);
        $ajaxReturn['result'] = 1;
      } else {
        $ajaxReturn['result'] = 0;
        if(isset($operator) && $operator->isSuperAdmin()) {
          $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' ('.__FILE__.','.__LINE__.')';
        } else {
          $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' (Error: '.__LINE__.')';
        }
      }
    } else {
      $ajaxReturn['result'] = 0;
      if(isset($operator) && $operator->isSuperAdmin()) {
        $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' ('.__FILE__.','.__LINE__.')';
      } else {
        $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' (Error: '.__LINE__.')';
      }
    }
    break;

  case 'getlist_archives':

    $DIR = DOC_ROOT.REL_ROOT.BACKUP_DIR;

    if(Utility::isPositiveInt($_POST['backup_id'])) {

      $DIR .= $_POST['backup_id'].'/';

      if(!is_dir($DIR)) {
        Utility::mkdirp($DIR);
      }

      $zip = glob($DIR.'*.zip');

      $list = [];

      if($zip) {

        foreach($zip as $k => $z) {

          $record         = [];
          $record['id']   = $k;
          $record['nome'] = basename($z);
          $record['size'] = Utility::humanReadable(filesize($z));

          $list[] = $record;

        }

      }

      $ajaxReturn['data']   = $list;
      $ajaxReturn['result'] = 1;

    } else {
      $ajaxReturn['result'] = 0;
      if(isset($operator) && $operator->isSuperAdmin()) {
        $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' ('.__FILE__.','.__LINE__.')';
      } else {
        $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' (Error: '.__LINE__.')';
      }
    }

    break;

  case 'list_archives':

    if(Utility::isPositiveInt($_POST['id'])) {

      $Obj = $Obj->getById($_POST['id']);

      $smarty->assign('selezione', $_POST['id']);

      $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang($module_name, 'LIST_ARCHIVES').' -> '.$Obj->fields['nome']);

      $headerButtons[] = $BUTTONS['btnClose'];
      $headerButtons[] = ['text'       => Traduzioni::getLang($module_name, 'ESEGUI_BACKUP'),
                          'icon'       => 'file-archive-o',
                          'attributes' => ['class' => 'btn btn-primary',
                                           'id'    => 'doBackup']];

      $headerButtons[] = ['text'       => Traduzioni::getLang($module_name, 'UPLOAD_BACKUP'),
                          'icon'       => 'upload',
                          'attributes' => ['class' => 'btn btn-info',
                                           'id'    => 'uploadBackup']];


    } else {
      $urlParams ='cmd/'.$cmd;
      header('Location:'.$lm->get($urlParams));
    }

    break;


  case 'del':

    if(Utility::isPositiveInt($_POST['id'])) {

      $Obj = $Obj->getById($_POST['id']);

      if($Obj) {

        if($Obj->cancellabile()) {

          $Obj->delete();

          $ajaxReturn['result'] = 1;

        } else {
          $ajaxReturn['result'] = 0;
          $ajaxReturn['error']  = Traduzioni::getLang($module_name, 'BACKUP_NON_CANCELLABILE');
        }

      } else {
        $ajaxReturn['result'] = 0;
        if(isset($operator) && $operator->isSuperAdmin()) {
          $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' ('.__FILE__.','.__LINE__.')';
        } else {
          $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' (Error: '.__LINE__.')';
        }
      }

    } else {
      $ajaxReturn['result'] = 0;
      if(isset($operator) && $operator->isSuperAdmin()) {
        $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' ('.__FILE__.','.__LINE__.')';
      } else {
        $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' (Error: '.__LINE__.')';
      }
    }
    break;

  case 'download':

    if(Utility::isPositiveInt($_POST['id'])) {
      $Obj = $Obj->getById($_POST['id']);
      if($Obj) {
        $DIR = DOC_ROOT.REL_ROOT.BACKUP_DIR;
        $DIR .= $_POST['id'].'/';
        $File = $DIR.basename($_POST['title']);
        if(file_exists($File)) {
          Utility::readfileheader($File);
        } else {
          $errore = Traduzioni::getLang($module_name, 'FILE_NOT_FOUND');
        }
      } else {
        $errore = Traduzioni::getLang($module_name, 'FILE_NOT_FOUND');
      }
    } else {
      $errore = Traduzioni::getLang($module_name, 'FILE_NOT_FOUND');
    }

    break;

  case 'profiliftp':

    $headerButtons[] = ['text'       => Traduzioni::getLang('default', 'NEW'),
                        'icon'       => 'file-o',
                        'attributes' => ['class' => 'btn btn-primary',
                                         'id'    => 'newprofile']];

    $headerButtons[] = $BUTTONS['btnClose'];

    $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang($module_name, 'LIST_PROFILI'));

    break;

  case 'save_profile':

    $options                  = [];
    $options['tableFilename'] = 'backup_data';

    $Obj = new Dba($options);

    if(Utility::isPositiveInt($_POST['id'])) {
      $Obj = $Obj->getById($_POST['id']);
    }

    $Obj->ftp_user     = $_POST['ftp_user'];
    $Obj->ftp_pwd      = $_POST['ftp_pwd'];
    $Obj->ftp_ip       = $_POST['ftp_ip'];
    $Obj->ftp_wd       = $_POST['ftp_wd'];
    $Obj->email        = $_POST['email'];
    $Obj->profile_name = $_POST['profile_name'];

    if($Obj->isValid()) {

      $result = $Obj->save();

      if($result) {

        $ajaxReturn['result'] = 1;
        $ajaxReturn['dati']   = $Obj->ajaxResponse();

      } else {

        $ajaxReturn['result'] = 0;
        $ajaxReturn['errors'] = [Traduzioni::getLang('default', 'ERRORE_INDEFINITO')];
        $ajaxReturn['wrongs'] = [];

      }

    } else {

      $opts['glue']         = '<br />';
      $ajaxReturn['result'] = 0;
      $ajaxReturn['error']  = $Obj->getErrors($opts);
      $ajaxReturn['wrongs'] = array_keys($Obj->wrongFields);

    }
    break;

  case 'load_profile':

    if(Utility::isPositiveInt($_POST['id'])) {

      $options                  = [];
      $options['tableFilename'] = 'backup_data';

      $Obj = new Dba($options);

      $Obj = $Obj->getById($_POST['id']);

      if($Obj) {

        $ajaxReturn['result'] = 1;
        $ajaxReturn['data']   = $Obj->toArray();

      } else {
        $ajaxReturn['result'] = 0;
        if(isset($operator) && $operator->isSuperAdmin()) {
          $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' ('.__FILE__.','.__LINE__.')';
        } else {
          $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' (Error: '.__LINE__.')';
        }
      }

    } else {
      $ajaxReturn['result'] = 0;
      if(isset($operator) && $operator->isSuperAdmin()) {
        $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' ('.__FILE__.','.__LINE__.')';
      } else {
        $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' (Error: '.__LINE__.')';
      }

    }
    break;

  case 'del_profile':

    if(Utility::isPositiveInt($_POST['id'])) {

      $options                  = [];
      $options['tableFilename'] = 'backup_data';

      $Obj = new Dba($options);

      $Obj = $Obj->getById($_POST['id']);

      if($Obj) {

        $Obj->delete();

        $ajaxReturn['result'] = 1;

      } else {
        $ajaxReturn['result'] = 0;
        if(isset($operator) && $operator->isSuperAdmin()) {
          $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' ('.__FILE__.','.__LINE__.')';
        } else {
          $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' (Error: '.__LINE__.')';
        }
      }

    } else {
      $ajaxReturn['result'] = 0;
      if(isset($operator) && $operator->isSuperAdmin()) {
        $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' ('.__FILE__.','.__LINE__.')';
      } else {
        $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' (Error: '.__LINE__.')';
      }

    }
    break;

  case 'getlist_profili':

    $options                  = [];
    $options['tableFilename'] = 'backup_data';

    $Obj = new Dba($options);

    $opzioni        = [];
    $opzioni['raw'] = 1;

    $data = $Obj->getlist($opzioni);

    $ajaxReturn['result'] = 1;
    $ajaxReturn['data']   = $data;

    break;

  case 'getlist':

    $opzioni        = [];
    $opzioni['raw'] = 1;

    $data = $Obj->getlist($opzioni);

    $ajaxReturn['result'] = 1;
    $ajaxReturn['data']   = $data;


    break;

  case 'switchvisibility':
    if(Utility::isPositiveInt($_POST['id'])) {

      $Obj = $Obj->getById($_POST['id']);

      if($Obj) {

        if($Obj->cron) {
          $Obj->cron = 0;
        } else {
          $Obj->cron = 1;
        }

        $Obj->save();
        $ajaxReturn['result'] = 1;

      } else {
        $ajaxReturn['result'] = 0;
        if(isset($operator) && $operator->isSuperAdmin()) {
          $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' ('.__FILE__.','.__LINE__.')';
        } else {
          $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' (Error: '.__LINE__.')';
        }
      }

    } else {
      $ajaxReturn['result'] = 0;
      if(isset($operator) && $operator->isSuperAdmin()) {
        $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' ('.__FILE__.','.__LINE__.')';
      } else {
        $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' (Error: '.__LINE__.')';
      }
    }
    break;

  case 'new':

    $headerButtons = array_merge($headerButtons, $BUTTONS['set']['save-close-new']);
    $footerButtons = $BUTTONS['set']['save-close'];

    if(Utility::isPositiveInt($_POST['id'])) {

      $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('default', 'MODIFY'));

      $Obj = $Obj->getById($_POST['id']);

    } else {

      $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('default', 'INSERT'));

    }

    $smarty->assign('Obj', $Obj);

    /** FILTRO DIRECTORY - INIZIO **/

    $filtri_attivi    = [];
    $filtri_attivi[1] = Traduzioni::getLang($module_name, 'TUTTE');
    $filtri_attivi[0] = Traduzioni::getLang($module_name, 'SELEZIONA');

    $smarty->assign('filtri_attivi', $filtri_attivi);

    /** FILTRO DIRECTORY - FINE   **/

    /** FILTRO DIRECTORY - INIZIO **/

    $cron_attivo    = [];
    $cron_attivo[0] = Traduzioni::getLang($module_name, 'NO_CRON');
    $cron_attivo[1] = Traduzioni::getLang($module_name, 'CRON_ATTIVO');

    $smarty->assign('cron_attivo', $cron_attivo);

    /** FILTRO DIRECTORY - FINE   **/

    /** ELENCO PROFILI - INIZIO **/

    $options                  = [];
    $options['tableFilename'] = 'backup_data';

    $profiles = new Dba($options);
    $profiles = $profiles->getlist();

    $profili_email = [];
    $profili_ftp   = [];

    $profili_email[0] = Traduzioni::getLang($module_name, 'NON_ABILITATO');
    $profili_ftp[0]   = Traduzioni::getLang($module_name, 'NON_ABILITATO');

    foreach($profiles as $p) {
      if($p->fields['email']) {
        $profili_email[$p->fields['id']] = $p->fields['profile_name'];
      }

      if($p->fields['ftp_ip']) {
        $profili_ftp[$p->fields['id']] = $p->fields['profile_name'];
      }
    }

    $smarty->assign('profili_ftp', $profili_ftp);
    $smarty->assign('profili_email', $profili_email);

    /** ELENCO PROFILI - FINE   **/

    /** ELENCO DIRECTORY **/

    $directories = [];

    $dirs = glob(DOC_ROOT.REL_ROOT.UPLOAD.'*', GLOB_ONLYDIR);

    foreach($dirs as $d) {

      $record                 = [];
      $record['inp_id']       = 'dir_'.$d;
      $record['inp_name']     = 'directories[]';
      $record['inp_class']    = '';
      $record['lbl_class']    = 'radiolbl';
      $record['inp_value']    = basename($d);
      $record['etichetta']    = basename($d);
      $record['inp_selected'] = false;

      $directories[] = $record;

    }

    $smarty->assign('directories', $directories);

    /** ELENCO DIRECTORY **/

    /** ELENCO TABELLE **/

    $tables = glob(TABLES_DIR.'*.table.php');

    $tabelle = [];

    foreach($tables as $tbl) {

      $tbl = str_replace('.table.php', '', basename($tbl));

      $record                 = [];
      $record['inp_id']       = 'tbl_'.$tbl;
      $record['inp_name']     = 'tabelle[]';
      $record['inp_class']    = '';
      $record['lbl_class']    = 'radiolbl';
      $record['inp_value']    = $tbl;
      $record['etichetta']    = $tbl;
      $record['inp_selected'] = false;

      $tabelle[] = $record;

    }

    $smarty->assign('tabelle', $tabelle);

    /** ELENCO TABELLE **/

    break;

  /* elenco */
  case '':

    $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('default', 'LIST_ELEMENTS'));

    $urlParams ='cmd/'.$cmd.'/act/profiliftp';

    $headerButtons[] = ['text'       => Traduzioni::getLang($module_name, 'PROFILI'),
                        'icon'       => 'male',
                        'attributes' => ['class' => 'btn btn-info',
                                         'href'  => $lm->get($urlParams)]];


    if($operator->isSuperAdmin()) {
      $headerButtons[] = $BUTTONS['btnNew'];
    }

    break;

  default:
    $urlParams ='cmd/'.$cmd;

    header('Location:'.$lm->get($urlParams));

    die;
    break;

}

