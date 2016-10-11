<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.01 (06/11/2015, 16.51)                                                                   **/
/** - Aggiunta gestione dell'autoload                                                            **/
/**                                                                                              **/
/** v.1.00 (03/11/2015, 12:18)                                                                   **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
use Ueppy\core\Lingue;
use Ueppy\utils\Utility;
use Ueppy\core\Settings;
use Ueppy\core\Traduzioni;
use Ueppy\core\Db;

$mainObjOptions                  = [];
$mainObjOptions['tableFilename'] = 'languages';

$mainObjOptions['imgSettings'] = [];

$imgSetting               = [];
$imgSetting['dimensione'] = '24x24';
$imgSetting['tipo']       = 'exact';

$mainObjOptions['imgSettings']['img0'][] = $imgSetting;

switch($act) {

  /* Salvataggio dei dati ricevuti dal form */
  case 'insert':

    $isoNations       = [];
    $isoNations['it'] = 'it';
    $isoNations['en'] = 'en';
    $isoNations['de'] = 'de';
    $isoNations['fr'] = 'fr';
    $isoNations['ru'] = 'ru';
    $isoNations['es'] = 'es';

    $Obj = new Lingue($mainObjOptions);

    if(Utility::isPositiveInt($_POST['id'])) {
      $Obj = $Obj->getById($_POST['id']);
    } else {
      $Obj->sigla = $_POST['sigla'];
    }

    if(Utility::isPositiveInt($_POST['attivo'])) {
      $attivo = 1;
    } else {
      $attivo = 0;
    }

    if(Utility::isPositiveInt($_POST['attivo_admin'])) {
      $Obj->attivo_admin = 1;
    } else {
      $Obj->attivo_admin = 0;
    }

    if(!$Obj->attivo_admin) {
      $attivo = 0;
    }

    if($Obj->fields['sigla'] == 'it') {
      $Obj->attivo_admin = 1;
      $attivo            = 1;
    }

    $Obj->estesa = $_POST['estesa'];

    if(isset($_POST['img0action']) && $_POST['img0action'] == 'del') {
      $Obj->img0 = ['', false];
    }

    if(isset($_FILES['img0']['tmp_name'])) {
      $Obj->img0 = [$_FILES['img0']['tmp_name'], $_FILES['img0']['name']];
    }

    if($operator->isMedium()) {
      $Obj->img0_alt   = $_POST['img0_alt'];
      $Obj->img0_title = $_POST['img0_title'];
    } else {
      if(!isset($Obj->fields['img0_alt'])) {
        $Obj->img0_alt = '';
      }
      if(!isset($Obj->fields['img0_title'])) {
        $Obj->img0_title = '';
      }
    }


    $result = false;

    if($Obj->isValid()) {

      $result = $Obj->save();

      if($result) {

        $ajaxReturn           = [];
        $ajaxReturn['result'] = 1;
        $ajaxReturn['dati']   = $Obj->ajaxResponse();

        if(Utility::isPositiveInt($attivo) && !in_array($Obj->fields['sigla'], explode(',', SET_LANGUAGES))) {

          $lng   = explode(',', SET_LANGUAGES);
          $lng[] = $Obj->fields['sigla'];

          $options                  = [];
          $options['tableFilename'] = 'settings';
          $options['debug']         = 0;

          $settings = new Settings($options);

          $settings = $settings->getByKey('LANGUAGES');

          $settings->valore = implode(',', $lng);

          $settings->save();

          $settings->generaCostanti();

        } elseif(in_array($Obj->fields['sigla'], $langs) && !Utility::isPositiveInt($attivo)) {

          $lng = explode(',', SET_LANGUAGES);

          foreach($lng as $key => $l) {
            if($l == $Obj->fields['sigla']) {
              unset($lng[$key]);
            }
          }

          $options                  = [];
          $options['tableFilename'] = 'settings';
          $options['debug']         = 0;

          $settings = new Settings($options);

          $settings = $settings->getByKey('LANGUAGES');

          $settings->valore = implode(',', $lng);

          $settings->save();

          $settings->generaCostanti();

        }

      } else {

        $ajaxReturn['result'] = 0;
        $ajaxReturn['errors'] = [Traduzioni::getLang('default', 'ERRORE_INDEFINITO')];
        $ajaxReturn['wrongs'] = [];

      }

    } else {

      $opts['glue']             = false;
      $ajaxReturn['result']     = 0;
      $ajaxReturn['errors']     = $Obj->getErrors($opts);
      $ajaxReturn['wrongs']     = array_keys($Obj->wrongFields);
      $ajaxReturn['wrongLangs'] = array_keys($Obj->wrongLangs);

    }

    break;

  /* Estrazione degli elementi per la tabella di elenco */
  case 'getlist':
    $options                  = [];
    $options['tableFilename'] = 'languages';

    $Obj = new Lingue($options);


    $sort_field = (isset($_POST['sorted']) && $_POST['sorted']) ? $_POST['sorted'] : 'sigla';
    $sort_order = (isset($_POST['sorted_order']) && $_POST['sorted_order']) ? $_POST['sorted_order'] : 'asc';

    $opts              = [];
    $opts['sortField'] = $sort_field;
    $opts['sortOrder'] = $sort_order;
    $opts['operatore'] = 'AND';

    $lista = $Obj->getlist($opts);

    $options                  = [];
    $options['tableFilename'] = 'settings';
    $settings                 = new Settings($options);

    $settings = $settings->getByKey('LANGUAGES');

    $lngs = explode(',', $settings->valore);

    $list = [];

    foreach($lista as $tempObj) {

      $record = [];

      $record['id']     = $tempObj->fields['id'];
      $record['estesa'] = $tempObj->fields['estesa'];
      $record['sigla']  = $tempObj->fields['sigla'];
      if($tempObj->fields['fileData']['img0']['exists']) {
        $record['immagine'] = $tempObj->fields['fileData']['img0']['versioni'][0]['url'];;
      } else {
        $record['immagine'] = '';
      }

      if(in_array($record['sigla'], $lngs)) {
        $record['attivo'] = 1;
      } else {
        $record['attivo'] = 0;
      }
      if($record['sigla'] == 'it') {
        $record['visibility_disabled'] = 1;
      }
      $list[] = $record;

    }

    $ajaxReturn['data']   = $list;
    $ajaxReturn['result'] = 1;
    break;

  case 'new':

    $options                  = [];
    $options['tableFilename'] = 'languages';

    $Obj          = new Lingue($mainObjOptions);
    $lista_lingue = $Obj->getlist();

    $lingue_options = [];
    foreach($lista_lingue as $lObj) {
      $lingue_options[$lObj->fields['id']] = $lObj->fields['estesa'];
    }

    $smarty->assign('lingue_options', $lingue_options);

    $options                  = [];
    $options['tableFilename'] = 'languages';

    $Obj = new Lingue($mainObjOptions);

    if(isset($_POST['id']) && $_POST['id']) {

      $Obj = $Obj->getById($_POST['id']);

      $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('default', 'MODIFY'));

    } else {

      $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('default', 'INSERT'));

    }

    if(isset($Obj->fields['sigla']) && in_array($Obj->fields['sigla'], explode(',', SET_LANGUAGES))) {
      $Obj->additionalData['attivo'] = '1';
    } else {
      $Obj->additionalData['attivo'] = '0';
    }

    $smarty->assign('Obj', $Obj);

    /** ATTIVO OPTIONS - INIZIO **/

    $attivo_options    = [];
    $attivo_options[0] = Traduzioni::getLang('default', 'NO_ANSWER');
    $attivo_options[1] = Traduzioni::getLang('default', 'SI_ANSWER');

    $smarty->assign('attivo_options', $attivo_options);

    /** ATTIVO OPTIONS - FINE   **/

    /** SIGLE NAZIONI **/

    $isoNations       = [];
    $isoNations[0]    = Traduzioni::getLang('default', 'SELECT_ONE');
    $isoNations['it'] = 'it - Italiano';
    $isoNations['en'] = 'en - English';
    $isoNations['de'] = 'de - Deutsch';
    $isoNations['fr'] = 'fr - Français';
    $isoNations['ru'] = 'ru - Pусский';
    $isoNations['es'] = 'es - Español';

    foreach($isoNations as $sigla => $estesa) {
      if(in_array($sigla, $langs, true)) {
        unset($isoNations[$sigla]);
      }
    }

    $smarty->assign('isoNations', $isoNations);

    $headerButtons = $BUTTONS['set']['save-close-new'];
    $footerButtons = $BUTTONS['set']['save-close'];

    break;

  case 'del':

    if(Utility::isPositiveInt($_POST['id']) && count($langs) > 1) {

      $options                  = [];
      $options['tableFilename'] = 'languages';

      $Obj = new Lingue($mainObjOptions);

      $Obj = $Obj->getById($_POST['id']);

      $query = 'show tables like "%_'.$Obj->fields['sigla'].'"';

      $db = new Db();
      $db->connect();

      $result = $db->doQuery($query);

      if($result) {

        while($row = mysqli_fetch_row($result)) {

          $sql = "DROP TABLE ".$row[0];

          $db = new Db();
          $db->connect();

          $db->doQuery($sql);

        }

      }

      if(in_array($Obj->fields['sigla'], $langs)) {
        foreach($langs as $key => $l) {
          if($l == $Obj->fields['sigla']) {
            unset($langs[$key]);
          }
        }

        $options                  = [];
        $options['tableFilename'] = 'settings';
        $options['debug']         = 0;

        $settings = new Settings($options);

        $settings = $settings->getByKey('LANGUAGES');

        $settings->valore = implode(',', $langs);

        $settings->save();

        $settings->generaCostanti();

      }

      $tables_files = glob(CONF_DIR.'tables/*.table.php');

      foreach($tables_files as $tfile) {


        $cd = [];

        include($tfile);

        if(count($cd)) {

          include($tfile);

          if($table != 'traduzioni') {

            if($table[strlen($table) - 1] != '_') {

              $sql = 'DELETE FROM '.$table.'_langs WHERE lingua = "'.$Obj->fields['sigla'].'"';

              $db = new Db();
              $db->doQuery($sql);

            }
          }

        }


      }

      $Obj->delete();

    }

    $ajaxReturn['result'] = 1;

    break;

  /* Attivazione/disattivazione di un operatore */
  case
  'switchvisibility':

    if(Utility::isPositiveInt($_POST['id'])) {

      $options                  = [];
      $options['tableFilename'] = 'languages';

      $Obj = new Lingue($mainObjOptions);

      $Obj = $Obj->getById($_POST['id']);

      $options                  = [];
      $options['tableFilename'] = 'settings';
      $options['debug']         = 0;

      $settings = new Settings($options);

      $settings = $settings->getByKey('LANGUAGES');

      $lngs = explode(',', $settings->valore);

      if(in_array($Obj->fields['sigla'], $lngs)) {

        if(count($lngs) > 1) {

          foreach($lngs as $key => $l) {
            if($l == $Obj->fields['sigla']) {
              unset($lngs[$key]);
            }
          }

          $settings->valore = implode(',', $lngs);

        }

      } else {

        $lngs[] = $Obj->fields['sigla'];

        $settings->valore = implode(',', $lngs);

      }

      $settings->save();

      $settings->generaCostanti();

    }

    $ajaxReturn['result'] = 1;

    break;

  case '':

    $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('default', 'LIST_ELEMENTS'));

    $headerButtons[] = $BUTTONS['btnNew'];

    break;

  default:
    $urlParams ='cmd/'.$cmd;

    header('Location:'.$lm->get($urlParams));

    die;
    break;

}
