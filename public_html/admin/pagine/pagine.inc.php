<?php
/***************/
/** v.1.01    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.01 (08/07/2016, 16.11)                                                                   **/
/** - Piccolo bugfix su valori non definiti nel caso sia definita una sola immagine allegata     **/
/**                                                                                              **/
/** v.1.00 (17/06/16, 17.05)                                                                     **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/

use Ueppy\core\Menu;
use Ueppy\utils\Utility;
use Ueppy\core\Pagina;
use Ueppy\core\Gruppo;
use Ueppy\core\Traduzioni;
use Ueppy\core\Ueppy;
use Ueppy\utils\Time;
use Ueppy\blog\Commento;

$mainObjOptions                  = [];
$mainObjOptions['tableFilename'] = 'pagine';
$mainObjOptions['forceAllLang']  = true;
$mainObjOptions['loadRules']     = true;

$dimensioni = explode('-', SET_PAGINE_DIM_INLINEA);

if($dimensioni[0]) {
  $imgSetting = [];
  list($imgSetting['dimensione'], $imgSetting['tipo']) = explode('|', $dimensioni[0]);
  $mainObjOptions['imgSettings']['img0'][] = $imgSetting;
}

if(isset($dimensioni[1]) && $dimensioni[1]) {
  $imgSetting = [];
  list($imgSetting['dimensione'], $imgSetting['tipo']) = explode('|', $dimensioni[1]);
  $mainObjOptions['imgSettings']['img1'][] = $imgSetting;
}


switch($act) {

  case 'insert':

    $Obj = new Pagina($mainObjOptions);

    if(Utility::isPositiveInt($_POST['id'])) {
      $opts['forceAllLang']       = 1;
      $Obj                        = $Obj->getById($_POST['id'], $opts);
      $Obj->additionalData['md5'] = $Obj->fields['id'];
    } else {
      $Obj->additionalData['md5'] = $_POST['id'];
    }

    switch($_POST['attivo']) {
      case '1':
        $Obj->additionalData['menu']->attivo = 1;
        if(!isset($Obj->additionalData['menu']->fields['pubdate']) || $Obj->additionalData['menu']->fields['pubdate'] == '0000-00-00') {
          $Obj->additionalData['menu']->pubdate = date('Y-m-d');
        }
        break;

      case '-1':
        $Obj->additionalData['menu']->attivo = -1;

        $t = new Time($_POST['pubdate']);
        if($t->d) {
          $Obj->additionalData['menu']->pubdate = $t->toMySqlDate();
        } else {
          $Obj->additionalData['menu']->addError(Traduzioni::getLang('default', 'FORMATO_DATA_ERRATO'), 'pubdate');
        }

        break;

      default:
        $Obj->additionalData['menu']->attivo  = 0;
        $Obj->additionalData['menu']->pubdate = '0000-00-00';
        break;
    }

    if(Utility::isPositiveInt($_POST['genitore'])) {
      $opts                               = [];
      $opts['forceAllLang']               = true;
      $parent                             = $Obj->additionalData['menu']->getById($_POST['genitore'], $opts);
      $Obj->additionalData['menu']->level = $parent->fields['level'];
    } else {
      if(Utility::isPositiveInt($_POST['posizione'])) {
        $Obj->additionalData['menu']->level = $_POST['posizione'];
      } else {
        if(!isset($Obj->additionalData['menu']->fields['level'])) {
          $Obj->additionalData['menu']->level = 100;
        }
      }
    }

    // aggiorno il livello di tutti i figli in modo da non far comprendere i figli di questa pagina in menu che
    // fanno la selezione per livello
    if(isset($Obj->additionalData['menu']->fields['id']) && is_int($Obj->additionalData['menu']->fields['id'])) {
      // solo pubblici, quindi con level >= 100
      $filters = [];

      $filter_record              = [];
      $filter_record['chiave']    = 'level';
      $filter_record['operatore'] = '>=';
      $filter_record['valore']    = '100';
      $filters[]                  = $filter_record;

      $opts                 = [];
      $opts['filters']      = $filters;
      $opts['forceAllLang'] = true;
      $opts['operatore']    = 'AND';

      $list_m = $Obj->additionalData['menu']->getlist($opts);

      foreach($list_m as $_m) {
        if($Obj->additionalData['menu']->isParentOf($_m->fields['id'])) {
          $_m->level = $Obj->additionalData['menu']->fields['level'];
          $_m->save();
        }
      }
    }

    $Obj->additionalData['menu']->parent = $_POST['genitore'];

    if($operator->isAdvanced()) {
      $Obj->additionalData['menu']->robots   = $_POST['robots'];
      $Obj->additionalData['menu']->template = $_POST['template'];

      $Obj->additionalData['menu']->is_category = $_POST['is_category'];
      if(!Utility::isPositiveInt($_POST['is_category']) && $Obj->id && $Obj->additionalData['menu']->hasChilds()) {
        $Obj->addError(Traduzioni::getLang('pagine', 'LA_PAGINA_HA_FIGLIE'), 'is_category');
      }
    }

    foreach($lingue as $sigla_lingua => $lingua_estesa) {

      $Obj->additionalData['menu']->$sigla_lingua = ['dicitura' => $_POST[$sigla_lingua]['dicitura']];

      if($operator->isMedium()) {
        $Obj->additionalData['menu']->$sigla_lingua = ['titolo_breve' => $_POST[$sigla_lingua]['titolo_breve']];
      }
      if($operator->isAdvanced()) {

        $Obj->resetRules('href');

        $opts['field'] = 'href';
        $opts['rule']  = 'StrRange';
        $opts['args']  = ['min' => 1, 'max' => 255];

        $Obj->addRule($opts);

        $opts['field']              = 'href';
        $opts['rule']               = 'Unico';
        $opts['args']               = [];
        $opts['args']['table']      = 'menu_langs';
        $opts['args']['confronto']  = 'menu_id';
        $opts['args']['errore']     = Traduzioni::getLang($module_name, 'HREF_SU_STESSO_LIVELLO');
        $opts['args']['query_part'] = ' AND menu.parent = '.$Obj->additionalData['menu']->fields['parent'];
        $opts['args']['debug']      = 0;

        $join                    = [];
        $join['tbl']             = 'menu';
        $join['on1']             = 'menu_id';
        $join['operatore']       = '=';
        $join['on2']             = 'menu.id';
        $opts['args']['join']    = $join;
        $opts['args']['escludi'] = $Obj->fields['id'];

        $Obj->addRule($opts);

        $Obj->additionalData['menu']->$sigla_lingua = ['href' => $_POST[$sigla_lingua]['href']];
        $Obj->additionalData['menu']->$sigla_lingua = ['description' => $_POST[$sigla_lingua]['description']];
        $Obj->additionalData['menu']->$sigla_lingua = ['img0_alt' => $_POST[$sigla_lingua]['img0_alt']];
        $Obj->additionalData['menu']->$sigla_lingua = ['img0_title' => $_POST[$sigla_lingua]['img0_title']];
        if(isset($mainObjOptions['imgSettings']['img1'])) {
          $Obj->additionalData['menu']->$sigla_lingua = ['img1_alt' => $_POST[$sigla_lingua]['img1_alt']];
          $Obj->additionalData['menu']->$sigla_lingua = ['img1_title' => $_POST[$sigla_lingua]['img1_title']];
        }
        $Obj->additionalData['menu']->$sigla_lingua = ['htmltitle' => $_POST[$sigla_lingua]['htmltitle']];
      }
    }

    $Obj->additionalData['menu']->nomefile   = 'pagina';
    $Obj->additionalData['menu']->superadmin = 0;

    if($Obj->additionalData['menu']->isValid()) {

      if(defined('SET_PAGINE_COMMENTI') && SET_PAGINE_COMMENTI && Utility::isPositiveInt($_POST['commenti'])) {
        $Obj->commenti = 1;
      } else {
        $Obj->commenti = 0;
      }
      foreach($langs as $l) {
        if(isset($_POST[$l]['img0']['action']) && $_POST[$l]['img0']['action'] == 'del') {
          $Obj->additionalData['menu']->$l = ['img0' => ['', false]];
        }
        if(isset($_POST[$l]['img1']['action']) && $_POST[$l]['img1']['action'] == 'del') {
          $Obj->additionalData['menu']->$l = ['img1' => ['', false]];
        }
      }
      if(isset($_FILES) && count($_FILES)) {
        foreach($langs as $l) {
          if(isset($_FILES[$l]['name']['img0']) && $_FILES[$l]['size']['img0']) {
            $Obj->additionalData['menu']->$l = ['img0' => [$_FILES[$l]['tmp_name']['img0'], $_FILES[$l]['name']['img0']]];
          }
          if(isset($_FILES[$l]['name']['img1']) && $_FILES[$l]['size']['img1']) {
            $Obj->additionalData['menu']->$l = ['img1' => [$_FILES[$l]['tmp_name']['img1'], $_FILES[$l]['name']['img1']]];
          }
        }
      }
      foreach($lingue as $sigla_lingua => $lingua_estesa) {
        $Obj->$sigla_lingua = ['testo' => $_POST[$sigla_lingua]['testo']];
        $Obj->$sigla_lingua = ['sottotitolo' => $_POST[$sigla_lingua]['sottotitolo']];
      }

      if($Obj->isValid()) {

        /**
         * Autorizzazione a editare una pagina, devo fare in modo che:
         * 1. in fase di creazione una pagina sia accessibile e tutti i gruppi sopra a quello dell'operatore che
         * l'ha creata.
         * 2. Se entro da admin nella pagina e tolgo il permesso ad un gruppo, quando l'utente modifica la pagina possa
         * decidere se nasconderla solo ai gruppi sotto il proprio gli altri dati devono rimanere invariati.
         * A questo scopo creo un array con indice l'ordine del gruppo e valore l'id del gruppo, dopodichè leggerò i dati
         * dell'auth prima di modificarlo mantenendo quelli già presenti.
         */
        $options                  = [];
        $options['tableFilename'] = 'gruppi';

        $grpObj = new Gruppo($options);

        $filters = [];

        $opts              = [];
        $opts['sortField'] = 'ordine';
        $opts['sortOrder'] = 'ASC';
        $opts['filters']   = $filters;
        $opts['operatore'] = 'AND';

        $list = $grpObj->getlist($opts);

        $gerarchia = [];

        foreach($list as $grp) {
          $gerarchia[$grp->fields['ordine']] = $grp->fields['id'];
        }

        $auth = [];
        // non mi arriva nulla? Probabilmente sono l'utente del gruppo più sfigato.
        if(!isset($_POST['auth']) || !is_array($_POST['auth'])) {
          // se non è settato, sto creando e quindi assegno i permessi a tutti i gruppi
          // sopra il mio + il mio.
          if(!isset($Obj->additionalData['auth'])) {
            foreach($gerarchia as $ordine => $id_grp) {
              if($operator->additionalData['grp_fields']['ordine'] >= $ordine) {
                $auth[] = $id_grp;
              }
            }
          } else { // se sono in modifica e non passo nulla tutto rimane com'è
            $auth = $Obj->additionalData['auth'];
          }
        } else {
          // arriva qualcosa, potrei essere un super admin ma devo partire dall'idea che potrei
          // appartenere ad un gruppo di mezzo, e che stia settando alcuni permessi per i gruppi inferiori
          // al mio.

          // quindi prima mi occupo dei permessi che non posso toccare...

          // se non è settato sono in creazione, aggiungo ai miei permessi i permessi a tutti i gruppi sopra di me.
          if(!isset($Obj->additionalData['auth'])) {
            foreach($gerarchia as $ordine => $id_grp) {
              // se un gruppo è + importante del mio
              if($ordine <= $operator->additionalData['grp_fields']['ordine']) {
                $auth[] = $id_grp;
              } else {
                // esco perchè da qui in poi prendo quello che arriva da post
                break;
              }
            }
          } else {
            // controllo lo stato attuale e lo copio solo per i gruppi superiori al mio
            foreach($gerarchia as $ordine => $id_grp) {
              if($ordine <= $operator->additionalData['grp_fields']['ordine'] && in_array($id_grp, $Obj->additionalData['auth'])) {
                $auth[] = $id_grp;
              }
            }
            // ... poi aggiungo il mio gruppo
            $auth[] = $operator->fields['gruppi_id'];
          }
          // ... poi di quelli che passo via post.
          //
          // ciclo nuovamente gerarchia e controllo solo i gruppi che mi interessano per evitare di
          // prendere dati da post che potrebbero anche essere manomessi.
          foreach($gerarchia as $ordine => $id_grp) {
            if($ordine > $operator->additionalData['grp_fields']['ordine'] && in_array($id_grp, $_POST['auth'])) {
              $auth[] = $id_grp;
            }
          }

        }

        $Obj->additionalData['auth'] = $auth;

        $opts          = [];
        $opts['debug'] = 0;

        $result = $Obj->save($opts);

        if($result) {

          $ajaxReturn['result']          = 1;
          $ajaxReturn['dati']            = $Obj->additionalData['menu']->ajaxResponse();
          $opts                          = [];
          $opts['langFields']            = $langs;
          $ajaxReturn['dati']['urls']    = $Obj->getUrl($opts);
          $ajaxReturn['dati']['pubdate'] = $Obj->additionalData['menu']->additionalData['pubdate'];

        }

      } else {

        $opts['glue']     = false;
        $return['result'] = 0;
        $return['errors'] = $Obj->getErrors($opts);
        $return['wrongs'] = array_keys($Obj->wrongFields);
        echo str_replace("\n", '', json_encode($return));
        die;

      }

    } else {

      $opts['glue']     = false;
      $return['result'] = 0;
      $return['errors'] = $Obj->additionalData['menu']->getErrors($opts);
      $return['wrongs'] = array_keys($Obj->additionalData['menu']->wrongFields);
      echo str_replace("\n", '', json_encode($return));
      die;

    }

    break;

  case 'save_seo_key':

    if($operator->isAdvanced()) {

      $options                  = [];
      $options['tableFilename'] = 'menu';

      $Obj = new Menu($options);

      $opts                 = [];
      $opts['forceAllLang'] = 1;

      $Obj = $Obj->getById($_POST['id'], $opts);


      if($Obj) {

        if(in_array($_POST['lang'], $langs)) {

          $lang = $_POST['lang'];

          if(in_array($_POST['type'], ['htmltitle', 'description'])) {
            $Obj->$lang = [$_POST['type'] => $_POST['value']];
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

      } else {
        $ajaxReturn['result'] = 0;
        if(isset($operator) && $operator->isSuperAdmin()) {
          $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' ('.__FILE__.','.__LINE__.')';
        } else {
          $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' (Error: '.__LINE__.')';
        }
      }

    }

    break;


  case 'getseo':

    if($operator->isAdvanced()) {

      /* ELENCO DEGLI ELEMENTI SEO */

      $options                  = [];
      $options['tableFilename'] = 'menu';

      $Obj = new Menu($options);

      $filters = [];

      $filter_record              = [];
      $filter_record['chiave']    = 'nomefile';
      $filter_record['operatore'] = '=';
      $filter_record['valore']    = 'pagina';

      $filters[] = $filter_record;

      $joins = [];

      if($operator->isSuperAdmin()) {

        $join              = [];
        $join['table']     = 'pagine_gruppi_auth';
        $join['on1']       = 'menu_id';
        $join['on2']       = 'menu.id';
        $join['operatore'] = '=';
        $joins[]           = $join;

        $filter_record              = [];
        $filter_record['chiave']    = 'pagine_gruppi_auth.gruppi_id';
        $filter_record['operatore'] = '=';
        $filter_record['valore']    = $operator->fields['gruppi_id'];
        $filters[]                  = $filter_record;

      }

      $opts                 = [];
      $opts['forceAllLang'] = 1;
      $opts['filters']      = $filters;
      $opts['joins']        = $joins;

      $lista = $Obj->getlist($opts);
      $list  = [];

      foreach($lista as $Obj) {
        foreach($Obj->opts['langs'] as $lang) {
          $record                = [];
          $record['id']          = $Obj->fields['id'];
          $record['lingua']      = $lingue[$lang];
          $record['lang']        = $lang;
          $record['titolo']      = $Obj->fields[$lang]['dicitura'];
          $record['htmltitle']   = $Obj->fields[$lang]['htmltitle'];
          $record['description'] = $Obj->fields[$lang]['description'];
          $list[]                = $record;

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

  case 'copy':

    if(Utility::isPositiveInt($_POST['id'])) {
      $Obj                  = new Pagina($mainObjOptions);
      $opts                 = [];
      $opts['forceAllLang'] = 1;
      $Obj                  = $Obj->getById($_POST['id'], $opts);
      if($Obj) {

        $copia = clone $Obj;

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

  case 'seo':

    if($operator->isAdvanced()) {

      $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('default', 'SEO_TOOLS'));

      $headerButtons[] = $BUTTONS['btnDemo'];
      $headerButtons[] = $BUTTONS['btnClose'];

    } else {

      $urlParams = 'cmd/'.$cmd;

      header('Location:'.$lm->get($urlParams));

      die;

    }

    break;

  case 'fdel':

    if($operator->isMedium()) {

      $options                  = [];
      $options['tableFilename'] = 'menu';

      $Obj = new Menu($options);

      $opts                 = [];
      $opts['forceAllLang'] = 1;

      $Obj = $Obj->getById($_POST['id'], $opts);

      if($Obj) {

        if(!$Obj->eliminato) {

          if(!$Obj->hasChilds()) {

            $opts          = [];
            $opts['reale'] = 0;

            $Obj->delete($opts);

            $ajaxReturn['result'] = 1;

          } else {

            $ajaxReturn['result'] = 0;
            $ajaxReturn['error']  = Traduzioni::getLang($module_name, 'CANT_DELETE_PARENT_PAGE');

          }

        } else {

          $Obj->eliminato = 0;

          $Obj->save();

          $ajaxReturn['result'] = 1;

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

  case 'getcommenti':

    $options                  = [];
    $options['tableFilename'] = 'commenti_pagine';

    $CommentoObj = new Commento($options);

    $filters = [];
    $joins   = [];

    if(Utility::isPositiveInt($_POST['menu_id'])) {

      $filter_record              = [];
      $filter_record['chiave']    = 'parent_id';
      $filter_record['operatore'] = '=';
      $filter_record['valore']    = $_POST['menu_id'];

      $filters[] = $filter_record;

    } else {

      $join              = [];
      $join['table']     = 'menu_langs';
      $join['alias']     = 'ml';
      $join['on1']       = 'lingua = "'.ACTUAL_LANGUAGE.'" AND ml.menu_id';
      $join['on2']       = 'commenti_pagine.parent_id';
      $join['operatore'] = '=';

      $joins[] = $join;

      $CommentoObj->addField('ml.dicitura as titolo');

    }


    if(!$operator->isAdmin()) {

      $join              = [];
      $join['table']     = 'menu';
      $join['on1']       = 'id';
      $join['on2']       = 'ml.menu_id';
      $join['operatore'] = '=';
      $joins[]           = $join;

      $join              = [];
      $join['table']     = 'pagine_gruppi_auth';
      $join['on1']       = 'menu_id';
      $join['on2']       = 'menu.id';
      $join['operatore'] = '=';
      $joins[]           = $join;

      $filter_record              = [];
      $filter_record['chiave']    = 'pagine_gruppi_auth.gruppi_id';
      $filter_record['operatore'] = '=';
      $filter_record['valore']    = $operator->fields['gruppi_id'];
      $filters[]                  = $filter_record;

      $filter_record              = [];
      $filter_record['chiave']    = 'menu.eliminato';
      $filter_record['operatore'] = '=';
      $filter_record['valore']    = 0;

      $filters[] = $filter_record;

    }

    $opzioni              = [];
    $opzioni['filters']   = $filters;
    $opzioni['operatore'] = 'AND';
    $opzioni['joins']     = $joins;
    $opzioni['debug']     = 0;

    $CommentoObjList = $CommentoObj->getlist($opzioni);

    $data = [];

    foreach($CommentoObjList as $CommentoObj) {
      $record['id']       = $CommentoObj->id;
      $record['nome']     = $CommentoObj->nome;
      $record['email']    = $CommentoObj->email;
      $record['commento'] = Utility::textpreview(strip_tags($CommentoObj->commento), 100);
      $record['valido']   = $CommentoObj->valido;

      $record['created_at'] = $CommentoObj->additionalData['created_at'];
      if(!Utility::isPositiveInt($_POST['news_id'])) {
        $record['titolo'] = $CommentoObj->titolo;
      }

      $data[] = $record;

    }

    $ajaxReturn['result'] = 1;
    $ajaxReturn['data']   = $data;


    break;

  case 'save_commento':

    if(Utility::isPositiveInt($_POST['id'])) {

      $options                  = [];
      $options['tableFilename'] = 'commenti_pagine';

      $CommentoObj = new Commento($options);

      $CommentoObj = $CommentoObj->getById($_POST['id']);

      if($CommentoObj) {

        $CommentoObj->nome     = $_POST['nome'];
        $CommentoObj->valido   = $_POST['valido'];
        $CommentoObj->email    = $_POST['email'];
        $CommentoObj->commento = $_POST['commento'];

        if($CommentoObj->isValid()) {
          $CommentoObj->save();

          $ajaxReturn['result'] = 1;
        } else {

          $opts['glue']             = false;
          $ajaxReturn['result']     = 0;
          $ajaxReturn['errors']     = $CommentoObj->getErrors($opts);
          $ajaxReturn['wrongs']     = array_keys($CommentoObj->wrongFields);
          $ajaxReturn['wrongLangs'] = array_keys($CommentoObj->wrongLangs);

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

  case 'comments':

    // include i fogli di stile e i js per l'editor tinymce
    Ueppy::includeTinymce();

    $headerButtons[] = $BUTTONS['btnClose'];

    $menu_id = 0;

    if(Utility::isPositiveInt($_POST['id'])) {

      $menu_id = $_POST['id'];

      $options                  = [];
      $options['tableFilename'] = 'menu';

      $Obj = new Menu($options);
      $Obj = $Obj->getById($_POST['id']);

      $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('pagine', 'GESTIONE_COMMENTI').' "'.$Obj->fields[ACTUAL_LANGUAGE]['dicitura'].'"');

    } else {

      $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('pagine', 'GESTIONE_COMMENTI'));

    }

    /** SUPERADMIN OPTIONS - INIZIO **/

    $validoOptions    = [];
    $validoOptions[1] = Traduzioni::getLang("default", 'SI_ANSWER');
    $validoOptions[0] = Traduzioni::getLang("default", 'NO_ANSWER');

    $smarty->assign('validoOptions', $validoOptions);

    /** SUPERADMIN OPTIONS - FINE   **/


    $smarty->assign('menu_id', $menu_id);


    break;

  case 'getlist':

    // id delle pagine non cancellabili
    $undeletable = [];

    $Obj = new Pagina($mainObjOptions);

    $filters = [];

    $filter_record              = [];
    $filter_record['chiave']    = 'level';
    $filter_record['valore']    = '100';
    $filter_record['operatore'] = '>=';

    $filters[] = $filter_record;

    $filter_record              = [];
    $filter_record['chiave']    = 'nomefile';
    $filter_record['valore']    = 'pagina';
    $filter_record['operatore'] = '=';

    $filters[] = $filter_record;

    if(!$operator->isAdmin()) {

      $filter_record              = [];
      $filter_record['chiave']    = 'eliminato';
      $filter_record['operatore'] = '=';
      $filter_record['valore']    = 0;

      $filters[] = $filter_record;

    }

    $opts          = [];
    $opts['joins'] = [];

    if(!$operator->fields['super_admin']) {

      $join              = [];
      $join['table']     = 'pagine_gruppi_auth';
      $join['on1']       = 'menu_id';
      $join['on2']       = 'menu.id';
      $join['operatore'] = '=';
      $opts['joins'][]   = $join;

      $filter_record              = [];
      $filter_record['chiave']    = 'pagine_gruppi_auth.gruppi_id';
      $filter_record['operatore'] = '=';
      $filter_record['valore']    = $operator->fields['gruppi_id'];
      $filters[]                  = $filter_record;

    }

    $opts['datiPagina'] = 1;
    $opts['filters']    = $filters;
    $opts['debug']      = 0;

    $lista = $Obj->getlist($opts);

    $list = [];

    foreach($lista as $Obj) {

      $record = [];

      $record['id']           = $Obj->additionalData['menu']->fields['id'];
      $record['dicitura']     = $Obj->additionalData['menu']->fields[ACTUAL_LANGUAGE]['dicitura'];
      $record['titolo_breve'] = $Obj->additionalData['menu']->fields[ACTUAL_LANGUAGE]['titolo_breve'];
      $record['href']         = $Obj->additionalData['menu']->fields[ACTUAL_LANGUAGE]['href'];
      $record['template']     = $Obj->additionalData['menu']->fields['template'];
      $record['eliminato']    = $Obj->additionalData['menu']->fields['eliminato'];

      if($Obj->additionalData['menu']->fields['parent']) {
        $record['parent'] = $Obj->additionalData['menu']->getPercorso(['out' => 'string']);
      } else {
        $record['parent'] = Traduzioni::getLang($module_name, 'LIVELLO_BASE');
      }

      switch($Obj->additionalData['menu']->fields['attivo']) {
        case '1':
          $record['attivo'] = Traduzioni::getLang('default', 'ATTIVO');
          break;
        case '0':
          $record['attivo'] = Traduzioni::getLang('default', 'DISATTIVO');
          break;
        case '-1':
          $record['attivo'] = Traduzioni::getLang('default', 'PROGRAMMATA').' ('.$Obj->additionalData['menu']->additionalData['pubdate'].')';
          break;
      }

      $record['commenti'] = $Obj->fields['commenti'];

      if(in_array($record['id'], $undeletable)) {
        $record['del_enabled'] = 0;
      } else {
        $record['del_enabled'] = 1;
      }

      $opts                    = [];
      $opts['countOnly']       = 1;
      $record['contaCommenti'] = $Obj->getCommenti($opts);

      $list[] = $record;

    }

    $ajaxReturn['data']   = $list;
    $ajaxReturn['result'] = 1;

    break;

  case 'delete_commento':

    if(Utility::isPositiveInt($_POST['id'])) {

      $options                  = [];
      $options['tableFilename'] = 'commenti_pagine';

      $CommentoObj = new Commento($options);

      $CommentoObj = $CommentoObj->getById($_POST['id']);

      if($CommentoObj) {

        $CommentoObj->delete();

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

  case 'visibility_commento':

    if(Utility::isPositiveInt($_POST['id'])) {

      $options                  = [];
      $options['tableFilename'] = 'commenti_pagine';

      $CommentoObj = new Commento($options);

      $CommentoObj = $CommentoObj->getById($_POST['id']);

      if($CommentoObj) {

        if($CommentoObj->fields['valido']) {
          $CommentoObj->valido = 0;
        } else {
          $CommentoObj->valido = 1;
        }

        $CommentoObj->save();

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

  case 'load_commento':

    if(Utility::isPositiveInt($_POST['id'])) {

      $options                  = [];
      $options['tableFilename'] = 'commenti_pagine';

      $CommentoObj = new Commento($options);

      $CommentoObj = $CommentoObj->getById($_POST['id']);

      if($CommentoObj) {

        $ajaxReturn['result']   = 1;
        $ajaxReturn['commento'] = $CommentoObj->toArray();

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

    /* TREE VIEW */
    $pathJS[] = DOC_ROOT.REL_ROOT.'bower_components/bootstrap-treeview/dist/bootstrap-treeview.min.js';

    /* DATETIMEPICKER */
    $pathJS[] = DOC_ROOT.REL_ROOT.'bower_components/moment/min/moment-with-locales.min.js';
    $pathJS[] = DOC_ROOT.REL_ROOT.'bower_components/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js';

    // include i fogli di stile e i js per l'editor tinymce
    Ueppy::includeTinymce();

    $Obj = new Pagina($mainObjOptions);

    $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('default', 'INSERT'));

    $disabledButtons = ' disabled';

    if(Utility::isPositiveInt($_POST['id'])) {

      $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('default', 'MODIFY'));

      $opts                 = [];
      $opts['forceAllLang'] = true;

      $Obj = $Obj->getById($_POST['id'], $opts);

      $opts['langFields'] = $langs;

      $links = $Obj->getUrl($opts);

      $smarty->assign('links', $links);

      $disabledButtons = '';

    }

    $smarty->assign('Obj', $Obj);

    /** TREE MENU PER IL GENITORE - INIZIO **/
    $c = [];

    if(isset($Obj->additionalData['menu']->fields['parent']) && $Obj->additionalData['menu']->fields['parent']) {

      $options                  = [];
      $options['tableFilename'] = 'menu';
      $options['debug']         = 0;

      $m = new Menu($options);
      $m = $m->getById($Obj->fields['id']);

      $genitore       = [];
      $genitore['id'] = $Obj->additionalData['menu']->fields['parent'];

      $c['open_nodes']   = [];
      $c['open_nodes'][] = 'm-'.$m->fields['parent'];
      while($m->fields['parent']) {

        $m                   = $m->getById($m->fields['parent']);
        $genitore['label'][] = $m->fields[ACTUAL_LANGUAGE]['dicitura'];

        $c['open_nodes'][] = 'm-'.$m->fields['parent'];

      }
      $c['selected_node'] = array_shift($c['open_nodes']);

      $genitore['label'] = array_reverse($genitore['label']);

      $genitore['label'] = 'Livello Base > '.implode(' > ', $genitore['label']);

    } else {

      $genitore          = [];
      $genitore['id']    = 0;
      $genitore['label'] = 'Livello Base';

      $c['open_nodes']    = [];
      $c['selected_node'] = 'm-0';

    }
    setcookie('tree', json_encode($c), false, '/');

    $smarty->assign('genitore', $genitore);

    /** TREE MENU PER IL GENITORE - FINE **/

    /* FILES DI TEMPLATE - INIZIO */

    $file_di_template = glob(SECTIONS_DIR.'*.tpl');
    $files            = [];

    foreach($file_di_template as $val) {
      $files[str_replace([SECTIONS_DIR, '.tpl'], '', $val)] = str_replace([SECTIONS_DIR, '.tpl'], '', $val);
    }

    $smarty->assign('files_di_template', $files);

    /* FILES DI TEMPLATE - FINE */

    /* Commenti options - inizio */

    $commenti_options = [];

    $record                 = [];
    $record['inp_id']       = 'commenti';
    $record['inp_name']     = 'commenti';
    $record['inp_class']    = '';
    $record['lbl_class']    = 'radiolbl';
    $record['inp_value']    = '1';
    $record['etichetta']    = '';
    $record['inp_selected'] = false;

    $commenti_options[] = $record;

    $smarty->assign('commenti_options', $commenti_options);

    /* Commenti options - fine */


    /* ABILITAZIONE */

    $abilitazioni     = [];
    $abilitazioni[0]  = Traduzioni::getLang('default', 'DISATTIVO');
    $abilitazioni[-1] = Traduzioni::getLang('default', 'PROGRAMMATA');
    $abilitazioni[1]  = Traduzioni::getLang('default', 'ATTIVO');

    $smarty->assign('abilitazioni', $abilitazioni);

    /* ABILITAZIONE */

    /* POSIZIONI - INIZIO */

    $posizioni = [];

    $posizioni[100] = Traduzioni::getLang($module_name, 'MENU_TOP');
    $posizioni[105] = Traduzioni::getLang($module_name, 'MENU_MIDDLE');
    $posizioni[110] = Traduzioni::getLang($module_name, 'MENU_BOTTOM');
    $posizioni[200] = Traduzioni::getLang($module_name, 'FUORI_MENU');

    $smarty->assign('list_posizione', $posizioni);

    /* POSIZIONI - FINE   */

    /** IS_CATEGORY OPTIONS - INIZIO **/

    $isCategoryOptions    = [];
    $isCategoryOptions[1] = Traduzioni::getLang("default", 'SI_ANSWER');
    $isCategoryOptions[0] = Traduzioni::getLang("default", 'NO_ANSWER');

    $smarty->assign('is_categoryOptions', $isCategoryOptions);

    /** IS_CATEGORY OPTIONS - FINE   **/

    /* GRUPPI ABILITATI ALLA MODIFICA - INIZIO */

    $options                  = [];
    $options['tableFilename'] = 'gruppi';

    $grpObj = new Gruppo($options);

    $filters = [];

    $filter_record              = [];
    $filter_record['chiave']    = 'ordine';
    $filter_record['operatore'] = '>';
    $filter_record['valore']    = $operator->additionalData['grp_fields']['ordine'];
    $filters[]                  = $filter_record;

    $opts              = [];
    $opts['sortField'] = 'ordine';
    $opts['sortOrder'] = 'ASC';
    $opts['filters']   = $filters;
    $opts['operatore'] = 'AND';

    $list = $grpObj->getlist($opts);

    $lista_gruppi = [];

    foreach($list as $obj) {

      $record                 = [];
      $record['inp_id']       = 'gruppo_'.$obj->fields['id'];
      $record['inp_name']     = 'auth[]';
      $record['inp_class']    = '';
      $record['lbl_class']    = 'radiolbl';
      $record['inp_value']    = $obj->fields['id'];
      $record['etichetta']    = $obj->fields['nome'];
      $record['inp_selected'] = false;

      $lista_gruppi[] = $record;

    }

    $smarty->assign('lista_gruppi', $lista_gruppi);

    /* GRUPPI ABILITATI ALLA MODIFICA - FINE */

    /** ROBOTS **/

    $robots_options                     = [];
    $robots_options['index,follow']     = 'index,follow';
    $robots_options['index,nofollow']   = 'index,nofollow';
    $robots_options['noindex,follow']   = 'noindex,follow';
    $robots_options['noindex,nofollow'] = 'noindex,nofollow';

    $smarty->assign('robots_options', $robots_options);

    /** COMMENTI OPTIONS - INIZIO **/

    $commentiOptions = [];

    $commentiOptions[0] = Traduzioni::getLang('default', 'NO_ANSWER');
    $commentiOptions[1] = Traduzioni::getLang('default', 'SI_ANSWER');

    $smarty->assign('commentiOptions', $commentiOptions);

    /** COMMENTI OPTIONS - FINE   **/

    // inclusione del plugin per la gestione degli allegati.
    if($Obj->allegatiAbilitati()) {
      Ueppy::loadAllegatiResources();
    }

    $headerButtons[] = ['text'       => Traduzioni::getLang('default', 'PREVIEW'),
                        'icon'       => 'desktop',
                        'attributes' => ['class' => 'btn btn-info'.$disabledButtons,
                                         'id'    => 'previewBtn']];

    $headerButtons[] = ['text'       => Traduzioni::getLang('default', 'SALVATAGGIO_AUTOMATICO'),
                        'icon'       => 'clock-o',
                        'attributes' => ['class'     => 'btn btn-info'.$disabledButtons,
                                         'id'        => 'salvataggioAutomaticoBtn',
                                         'data-time' => $autoSaveTime]];

    $headerButtons = array_merge($headerButtons, $BUTTONS['set']['save-close-new']);
    $footerButtons = $BUTTONS['set']['save-close'];

    break;

  case 'get_parents':

    $options                  = [];
    $options['tableFilename'] = 'menu';

    $Obj = new Menu($options);

    $opzioni                 = [];
    $opzioni['maxLevel']     = 200;
    $opzioni['minLevel']     = 100;
    $opzioni['parent']       = 0;
    $opzioni['debug']        = 0;
    $opzioni['exclude']      = $_POST['exclude'];
    $opzioni['onlyCatPages'] = true;
    $opzioni['utente']       = $operator;
    $opzioni['soloConFigli'] = 0;

    $data = $Obj->getArrayMenu($opzioni);

    $menu = [];

    $record['text']  = Traduzioni::getLang($module_name, 'LIVELLO_BASE');
    $record['href']  = '#node-0';
    $record['nodes'] = $data;

    $menu[] = $record;

    $ajaxReturn['result'] = 1;
    $ajaxReturn['data']   = $menu;

    break;

  case 'del':

    if($operator->isAdmin()) {

      if(Utility::isPositiveInt($_POST['id'])) {

        $Obj = new Pagina($mainObjOptions);

        $Obj = $Obj->getById($_POST['id']);

        if($Obj->additionalData['menu'] && !$Obj->additionalData['menu']->hasChilds()) {

          $Obj->delete();

          $ajaxReturn['result'] = 1;

        } else {

          $ajaxReturn['result'] = 0;
          $ajaxReturn['error']  = Traduzioni::getLang($module_name, 'CANT_DELETE_PARENT_PAGE');

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


  case '':
    $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('default', 'LIST_ELEMENTS'));

    if(SET_PAGINE_COMMENTI) {
      $urlParams = 'cmd/'.$cmd.'/act/comments';

      $headerButtons[] = ['text'       => Traduzioni::getLang($module_name, 'COMMENTI'),
                          'icon'       => 'comments',
                          'attributes' => ['class' => 'btn btn-info',
                                           'href'  => $lm->get($urlParams)]];
    }

    if($operator->isAdvanced()) {
      $headerButtons[] = $BUTTONS['btnSeo'];

    }
    $headerButtons[] = $BUTTONS['btnNew'];

    break;

  default:
    $urlParams = 'cmd/'.$cmd;

    header('Location:'.$lm->get($urlParams));

    die;
    break;

}