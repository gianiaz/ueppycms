<?php
/***************/
/** v.1.01    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.01 (09/11/2015, 14.59)                                                                   **/
/** - Implementato autoloading delle classi                                                      **/
/**                                                                                              **/
/** v.1.00 (22/09/2015)                                                                          **/
/** - Versione stabile a partire da versione 3.1.05                                              **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
use Ueppy\core\Ueppy;
use Ueppy\utils\Utility;
use Ueppy\blog\News;
use Ueppy\blog\NewsCategory;
use Ueppy\blog\Tag;
use Ueppy\core\Traduzioni;
use Ueppy\utils\Time;
use Ueppy\blog\Commento;

$mainObjOptions                  = [];
$mainObjOptions['tableFilename'] = 'news';
$mainObjOptions['forceAllLang']  = 1;
switch($act) {

  case 'insert':

    $Obj = new News($mainObjOptions);

    if(Utility::isPositiveInt($_POST['id'])) {
      $opts                 = [];
      $opts['forceAllLang'] = 1;
      $Obj                  = $Obj->getById($_POST['id'], $opts);
      $Obj->getTags();
      $Obj->additionalData['md5'] = $Obj->fields['id'];
    }

    // cose che può fare l'utente normale:
    // 1. impostare la data
    // 2. impostare lo stato di visibilità.
    if($operator->isMedium()) {

      $Obj->stato = $_POST['stato'];

      $time = new Time($_POST['attiva_dal']);

      $attiva_dal = $time->toMySqlDateTime();

      if($attiva_dal != '-') {
        $Obj->attiva_dal = $attiva_dal;
      } else {
        $Obj->addError(Traduzioni::getLang('default', 'INVALID_DATE'), 'attiva_dal');
      }

      foreach($lingue as $sigla_lingua => $lingua_estesa) {
        if(in_array($sigla_lingua, $_POST['lingua_attiva'])) {
          $Obj->$sigla_lingua = ['lingua_attiva' => 1];
        } else {
          $Obj->$sigla_lingua = ['lingua_attiva' => 0];
        }
      }

    }

    // cose che può fare solo l'utente avanzato:
    // 1. Decidere chi è l'autore di una news.
    if($operator->isAdvanced()) {
      $Obj->operatori_id = $_POST['operatori_id'];
      if(defined('SET_NEWS_COMMENTI') && SET_NEWS_COMMENTI && Utility::isPositiveInt($_POST['commenti'])) {
        $Obj->commenti = 1;
      } else {
        $Obj->commenti = 0;
      }

      if($_POST['disattivazione'] == 1) {
        $time          = new Time($_POST['disattiva_dal']);
        $disattiva_dal = $time->toMySqlDateTime();

        if($disattiva_dal != '-') {
          $Obj->disattiva_dal = $disattiva_dal;
        } else {
          $Obj->addError(Traduzioni::getLang('default', 'INVALID_DATE'), 'disattiva_dal');
        }
      } else {
        $Obj->disattiva_dal = '0000-00-00 00:00:00';
      }

    }


    $genitori = [];

    if(SET_ENABLE_CAT_NEWS) {
      // genitori della news
      // chiave : id della categoria
      // valore : 0|1 (1 se principale)

      if(Utility::isPositiveInt($_POST['genitore'])) {
        $genitori[$_POST['genitore']] = 1;
      } else {
        if(isset($Obj->additionalData['parents']) && isset($Obj->additionalData['parents']['principale']) && Utility::isPositiveInt($Obj->additionalData['parents']['principale']['id'])) {
          $genitori[$Obj->additionalData['parents']['principale']['id']] = 1;
        } else {
          $Obj->addError(Traduzioni::getLang($module_name, 'CATEGORIA_PRINCIPALE'), 'genitore');
        }
      }

      // ora mi estraggo le categorie a cui sono autorizzato in modo da non sovrascrivere associazioni per categorie che nel form non posso vedere.
      // ovviamente solo se non sono admin.
      if(isset($Obj->additionalData['parents']) && count(isset($Obj->additionalData['parents']['accessorie'])) && !$operator->isAdmin()) {

        if($operator->isAdvanced()) {

          $options                  = [];
          $options['tableFilename'] = 'news_category';
          $options['debug']         = 0;

          $cat = new NewsCategory($options);

          $filters = [];

          $opts              = [];
          $opts['sortField'] = 'ordine';
          $opts['sortOrder'] = 'ASC';
          $opts['operatore'] = 'AND';

          $join              = [];
          $join['table']     = 'news_category_gruppi_auth';
          $join['alias']     = 'auth';
          $join['on1']       = 'news_category_id';
          $join['on2']       = 'news_category.id';
          $join['operatore'] = '=';

          $opts['joins'][] = $join;

          $filter_record              = [];
          $filter_record['chiave']    = 'auth.id_gruppo';
          $filter_record['operatore'] = '=';
          $filter_record['valore']    = $operator->fields['gruppi_id'];
          $filters[]                  = $filter_record;

          $opts['filters'] = $filters;

          $list = $cat->getlist($opts);

          $catAbilitate = [];

          foreach($list as $c) {
            $catAbilitate[] = $c->fields['id'];
          }


          foreach($Obj->additionalData['parents']['accessorie'] as $k => $v) {
            if(!in_array($k, $catAbilitate)) {
              $genitori[$k] = 0;
            }
          }

        } else {
          foreach($Obj->additionalData['parents']['accessorie'] as $id_c => $v) {
            $genitori[$id_c] = 0;
          }
        }

      }

    }

    if(isset($_POST['parents']) && count($_POST['parents'])) {
      foreach($_POST['parents'] as $id_p) {
        $genitori[$id_p] = 0;
      }
    }

    $Obj->additionalData['nuove_categorie'] = $genitori;

    $Obj->resetRules('intro');

    $opts['field'] = 'intro';
    $opts['rule']  = 'StrRange';
    $opts['args']  = ['min' => 1, 'max' => SET_NEWS_INTRO_MAX_CHARS];

    $Obj->addRule($opts);

    foreach($lingue as $sigla_lingua => $lingua_estesa) {

      if($Obj->fields[$sigla_lingua]['lingua_attiva']) {

        $Obj->$sigla_lingua = ['titolo' => $_POST[$sigla_lingua]['titolo']];
        $Obj->$sigla_lingua = ['intro' => $_POST[$sigla_lingua]['intro']];
        $Obj->$sigla_lingua = ['testo' => $_POST[$sigla_lingua]['testo']];

        $Obj->$sigla_lingua = ['lingua_attiva' => 1];

        $Obj->additionalData['newTags'][$sigla_lingua] = $_POST[$sigla_lingua]['tags'];

        if($operator->isAdvanced()) {

          $Obj->$sigla_lingua = ['description' => $_POST[$sigla_lingua]['description']];
          $Obj->$sigla_lingua = ['htmltitle' => $_POST[$sigla_lingua]['htmltitle']];

          if($_POST[$sigla_lingua]['href']) {

            $Obj->resetRules('href');

            $opts['field'] = 'href';
            $opts['rule']  = 'StrRange';
            $opts['args']  = ['min' => 1, 'max' => 255];

            $Obj->addRule($opts);

            $opts['field']             = 'href';
            $opts['rule']              = 'Unico';
            $opts['args']              = [];
            $opts['args']['table']     = 'news_langs';
            $opts['args']['confronto'] = 'news_id';
            $opts['args']['errore']    = Traduzioni::getLang($module_name, 'HREF_SU_STESSO_LIVELLO');

            $join                    = [];
            $join['tbl']             = 'news';
            $join['on1']             = 'news_id';
            $join['operatore']       = '=';
            $join['on2']             = 'news.id';
            $opts['args']['join']    = $join;
            $opts['args']['escludi'] = $Obj->fields['id'];

            $Obj->addRule($opts);

            $Obj->$sigla_lingua = ['href' => $_POST[$sigla_lingua]['href']];

          } else {
            $Obj->$sigla_lingua = ['href' => ''];
          }

        }

      } else {
        $Obj->$sigla_lingua = ['lingua_attiva' => 0];
        $Obj->resetRules('titolo');
      }

    }


    if(!isset($Obj->fields['log'])) {
      $Obj->log = $_SESSION['LOG_INFO']['UID'];
    }


// VERIFICA CONTROLLI EFFETTUATI
    if($Obj->isValid()) {

      $opts          = [];
      $opts['debug'] = false;

      $result = $Obj->save($opts);

      if($result) {

        $ajaxReturn           = [];
        $ajaxReturn['result'] = 1;
        $ajaxReturn['dati']   = $Obj->ajaxResponse();

        $ajaxReturn['dati']['attiva_dal']    = $Obj->additionalData['attiva_dal'];
        $ajaxReturn['dati']['disattiva_dal'] = $Obj->additionalData['disattiva_dal'];

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

  case 'copy':

    if(Utility::isPositiveInt($_POST['id'])) {
      $Obj                  = new News($mainObjOptions);
      $opts                 = [];
      $opts['forceAllLang'] = 1;
      $Obj                  = $Obj->getById($_POST['id'], $opts);
      if($Obj) {

        $Obj->getTags();

        $copia = clone $Obj;


        foreach($copia->opts['langs'] as $l) {
          $copia->additionalData['newTags'][$l] = $Obj->additionalData['tags'][$l]['string'];
          unset($copia->additionalData['tags'][$l]['list']);
        }

        $opts           = [];
        $opts['format'] = 'toSave';

        unset($copia->additionalData['parents_id']);
        $copia->additionalData['nuove_categorie'] = $Obj->getParents($opts);

        $opts          = [];
        $opts['debug'] = 0;

        $copia->save($opts);

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


  case 'getcommenti':

    $options                  = [];
    $options['tableFilename'] = 'commenti_news';

    $CommentoObj = new Commento($options);

    $filters = [];
    $joins   = [];

    if(Utility::isPositiveInt($_POST['news_id'])) {

      $filter_record              = [];
      $filter_record['chiave']    = 'parent_id';
      $filter_record['operatore'] = '=';
      $filter_record['valore']    = $_POST['news_id'];

      $filters[] = $filter_record;

    } else {

      $join              = [];
      $join['table']     = 'news_langs';
      $join['alias']     = 'nl';
      $join['on1']       = 'lingua_attiva = 1 AND nl.news_id';
      $join['on2']       = 'commenti_news.parent_id';
      $join['operatore'] = '=';

      $joins[] = $join;

      $CommentoObj->addField('nl.titolo as titolo');

    }

    if(!$operator->isAdmin()) {

      if(SET_ENABLE_CAT_NEWS) {

        $join              = [];
        $join['table']     = 'news';
        $join['alias']     = 'n';
        $join['on1']       = 'id';
        $join['on2']       = 'commenti_news.parent_id';
        $join['operatore'] = '=';

        $joins[] = $join;

        $join              = [];
        $join['table']     = 'rel_news_category_news';
        $join['alias']     = 'rel';
        $join['on1']       = 'id_news';
        $join['on2']       = 'n.id';
        $join['operatore'] = '=';

        $joins[] = $join;

        $join              = [];
        $join['table']     = 'news_category_gruppi_auth';
        $join['alias']     = 'auth';
        $join['on1']       = 'news_category_id';
        $join['on2']       = 'rel.news_category_id';
        $join['operatore'] = '=';

        $joins[] = $join;

        $filter_record              = [];
        $filter_record['chiave']    = 'auth.id_gruppo';
        $filter_record['operatore'] = '=';
        $filter_record['valore']    = $operator->fields['gruppi_id'];
        $filters[]                  = $filter_record;

      }

      $filter_record              = [];
      $filter_record['chiave']    = 'n.eliminato';
      $filter_record['operatore'] = '=';
      $filter_record['valore']    = 0;

      $filters[] = $filter_record;

    }

    $opzioni              = [];
    $opzioni['filters']   = $filters;
    $opzioni['operatore'] = 'AND';
    $opzioni['joins']     = $joins;

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
      $options['tableFilename'] = 'commenti_news';

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

    $news_id = 0;

    if(Utility::isPositiveInt($_POST['id'])) {

      $news_id = $_POST['id'];

      $Obj    = new News($mainObjOptions);
      $Obj    = $Obj->getById($_POST['id']);
      $titolo = $Obj->getTitoloElenchi();

      $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('news', 'GESTIONE_COMMENTI').' "'.$titolo.'"');

    } else {

      $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('news', 'GESTIONE_COMMENTI'));

    }

    /** SUPERADMIN OPTIONS - INIZIO **/

    $validoOptions    = [];
    $validoOptions[1] = Traduzioni::getLang("default", 'SI_ANSWER');
    $validoOptions[0] = Traduzioni::getLang("default", 'NO_ANSWER');

    $smarty->assign('validoOptions', $validoOptions);

    /** SUPERADMIN OPTIONS - FINE   **/


    $smarty->assign('news_id', $news_id);


    break;

  case 'getlist':
    /* ELENCO DEGLI ELEMENTI GENERALE */

    $Obj = new News($mainObjOptions);

    $filters = [];

    $joins = [];

    $sort_field = 'attiva_dal';
    $sort_order = 'DESC';

    $opts = [];

    if(!$operator->isAdmin()) {

      if(SET_ENABLE_CAT_NEWS) {

        if(!count($joins)) {
          $join              = [];
          $join['table']     = 'rel_news_category_news';
          $join['alias']     = 'rel';
          $join['on1']       = 'id_news';
          $join['on2']       = 'news.id';
          $join['operatore'] = '=';

          $joins[] = $join;
        }

        $join              = [];
        $join['table']     = 'news_category_gruppi_auth';
        $join['alias']     = 'auth';
        $join['on1']       = 'news_category_id';
        $join['on2']       = 'rel.news_category_id';
        $join['operatore'] = '=';

        $joins[] = $join;

        $filter_record              = [];
        $filter_record['chiave']    = 'auth.id_gruppo';
        $filter_record['operatore'] = '=';
        $filter_record['valore']    = $operator->fields['gruppi_id'];
        $filters[]                  = $filter_record;

      }

    }

    if(!$operator->isAdmin()) {

      $filter_record              = [];
      $filter_record['chiave']    = 'eliminato';
      $filter_record['operatore'] = '=';
      $filter_record['valore']    = 0;

      $filters[] = $filter_record;

    }

    $join              = [];
    $join['table']     = 'operatori';
    $join['alias']     = 'o';
    $join['on1']       = 'id';
    $join['on2']       = 'news.operatori_id';
    $join['operatore'] = '=';

    $joins[] = $join;

    $opts['sortField']    = $sort_field;
    $opts['sortOrder']    = $sort_order;
    $opts['forceAllLang'] = 1;
    $opts['countOnly']    = false;
    $opts['filters']      = $filters;
    $opts['operatore']    = 'AND';
    $opts['joins']        = $joins;

    $Obj->addField('o.nomecompleto as autore');
    $lista = $Obj->getlist($opts);

    $list = [];

    foreach($lista as $Obj) {

      $titolo = '';

      $record       = [];
      $record['id'] = $Obj->fields['id'];

      $record['titolo']     = $Obj->getTitoloElenchi();
      $record['lingue']     = $Obj->getLingueAttive();
      $record['attiva_dal'] = $Obj->additionalData['attiva_dal'];
      $record['autore']     = $Obj->fields['autore'];

      $opts           = [];
      $opts['format'] = 'text';

      if(defined('SET_ENABLE_CAT_NEWS') && SET_ENABLE_CAT_NEWS) {
        $record['category'] = $Obj->getParents($opts);
      }

      $record['commenti'] = $Obj->fields['commenti'];

      $opts                    = [];
      $opts['countOnly']       = 1;
      $record['contaCommenti'] = $Obj->getCommenti($opts);

      $record['stato']     = $Obj->fields['stato'];
      $record['eliminato'] = $Obj->eliminato;

      if(!$operator->isAdmin() && $operator->fields['id'] != $Obj->fields['operatori_id'] && !SET_MODIFICA_NEWS_DI_ALTRI) {
        $record['del_enabled'] = 0;
      } else {
        $record['del_enabled'] = 1;
      }


      $list[] = $record;

    }


    $ajaxReturn['data']   = $list;
    $ajaxReturn['result'] = 1;

    break;

  case 'fdel':

    if($operator->isAdmin()) {

      $Obj = new News($mainObjOptions);

      $opts                 = [];
      $opts['forceAllLang'] = 1;

      $Obj = $Obj->getById($_POST['id'], $opts);

      if(!$Obj->eliminato) {

        $opts          = [];
        $opts['reale'] = 0;
        $Obj->delete($opts);

      } else {

        $Obj->eliminato = 0;

        $Obj->save();

      }

      $ajaxReturn['result'] = 1;

    } else {
      $ajaxReturn['result'] = 0;

    }

    break;

  case 'getseo':
    /* ELENCO DEGLI ELEMENTI SEO */

    $Obj = new News($mainObjOptions);

    $filters = [];

    $joins = [];

    $sort_field = 'attiva_dal';
    $sort_order = 'DESC';

    $opts = [];

    if(!$operator->isAdmin()) {

      if(SET_ENABLE_CAT_NEWS) {

        if(!count($joins)) {
          $join              = [];
          $join['table']     = 'rel_news_category_news';
          $join['alias']     = 'rel';
          $join['on1']       = 'id_news';
          $join['on2']       = 'news.id';
          $join['operatore'] = '=';

          $joins[] = $join;
        }

        $join              = [];
        $join['table']     = 'news_category_gruppi_auth';
        $join['alias']     = 'auth';
        $join['on1']       = 'news_category_id';
        $join['on2']       = 'rel.news_category_id';
        $join['operatore'] = '=';

        $joins[] = $join;

        $filter_record              = [];
        $filter_record['chiave']    = 'auth.id_gruppo';
        $filter_record['operatore'] = '=';
        $filter_record['valore']    = $operator->fields['gruppi_id'];
        $filters[]                  = $filter_record;

      }

    }

    $opts['sortField']    = $sort_field;
    $opts['sortOrder']    = $sort_order;
    $opts['forceAllLang'] = 1;
    $opts['countOnly']    = false;
    $opts['filters']      = $filters;
    $opts['operatore']    = 'AND';
    $opts['joins']        = $joins;

    $lista = $Obj->getlist($opts);
    $list  = [];

    foreach($lista as $Obj) {

      foreach($Obj->opts['langs'] as $lang) {
        if($Obj->fields[$lang]['lingua_attiva']) {

          $record                = [];
          $record['id']          = $Obj->fields['id'];
          $record['lingua']      = $lingue[$lang];
          $record['lang']        = $lang;
          $record['titolo']      = $Obj->fields[$lang]['titolo'];
          $record['htmltitle']   = $Obj->fields[$lang]['htmltitle'];
          $record['description'] = $Obj->fields[$lang]['description'];
          $record['attiva_dal']  = $Obj->additionalData['attiva_dal'];
          $list[]                = $record;

        }
      }

    }


    $ajaxReturn['data']   = $list;
    $ajaxReturn['result'] = 1;

    break;

  case 'save_seo_key':

    $Obj = new News($mainObjOptions);

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


    break;

  case 'new':

    /* DATETIMEPICKER */
    $pathJS[]  = DOC_ROOT.REL_ROOT.'bower_components/moment/min/moment-with-locales.min.js';
    $pathJS[]  = DOC_ROOT.REL_ROOT.'bower_components/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js';
    $pathCSS[] = DOC_ROOT.REL_ROOT.'bower_components/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css';

    // include i fogli di stile e i js per l'editor tinymce
    Ueppy::includeTinymce();

    $Obj = new News($mainObjOptions);

    $disabledButtons = ' disabled';

    if(Utility::isPositiveInt($_POST['id'])) {

      $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('default', 'MODIFY'));

      $opts                 = [];
      $opts['forceAllLang'] = 1;

      $Obj = $Obj->getById($_POST['id'], $opts);

      // readonly
      //Utility::pre('Operatore admin: '.$operator->isAdmin()."\n".'Proprietario: '.$Obj->fields['operatori_id']."\n".'Id operatore in sessione: '.$operator->fields['id']."\n".'Permesso di modificare news di altri:'.SET_MODIFICA_NEWS_DI_ALTRI);
      if(!$operator->isAdmin() && $operator->fields['id'] != $Obj->fields['operatori_id'] && !SET_MODIFICA_NEWS_DI_ALTRI) {
        $READONLY = 1;
        $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('default', 'READONLY'));
        //Utility::pre('test');
      }

      $opts               = [];
      $opts['langFields'] = $langs;

      $links = $Obj->getUrl($opts);

      $smarty->assign('links', $links);

      $Obj->getTags();

      $disabledButtons = '';

    } else {

      $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('default', 'INSERT'));

    }

    $smarty->assign('Obj', $Obj);

    /* ABILITAZIONE */

    $abilitazioni               = [];
    $abilitazioni['ATTIVO']     = Traduzioni::getLang('default', 'ATTIVO');
    $abilitazioni['SPENTA']     = Traduzioni::getLang('default', 'SPENTA');
    $abilitazioni['CANCELLATA'] = Traduzioni::getLang('default', 'CANCELLATA');
    $abilitazioni['SCHEDULATA'] = Traduzioni::getLang('default', 'SCHEDULATA');

    $smarty->assign('abilitazioni', $abilitazioni);

    /* ABILITAZIONE */

    /* SCADENZA */

    $scadenze     = [];
    $scadenze[-1] = Traduzioni::getLang('default', 'ILLIMITATA');
    $scadenze[1]  = Traduzioni::getLang('default', 'SCADE IL');

    $smarty->assign('scadenze', $scadenze);

    /* ABILITAZIONE */

    /* LINGUE ABILITATE - INIZIO */

    foreach($lingue as $sigla => $estesa) {

      $record                 = [];
      $record['inp_id']       = 'lingua_'.$sigla;
      $record['inp_name']     = 'lingua_attiva[]';
      $record['inp_class']    = 'lingue_attive';
      $record['lbl_class']    = 'radiolbl';
      $record['inp_value']    = $sigla;
      $record['etichetta']    = $estesa;
      $record['inp_selected'] = false;

      $lista_lingue[] = $record;

    }

    $smarty->assign('lista_lingue', $lista_lingue);

    /* LINGUE ABILITATE - INIZIO */

    /** ELENCO TAGS - INIZIO **/

    $options                  = [];
    $options['tableFilename'] = 'tags';
    $options['debug']         = 0;

    $tags              = new Tag($options);
    $opts              = [];
    $opts['sortField'] = 'count';
    $opts['sortOrder'] = 'DESC';

    $list = $tags->getlist($opts);

    $tags = [];
    foreach($list as $tag) {
      $tags[$tag->fields['lang']][$tag->fields['id']]['tag']   = $tag->fields['tag'];
      $tags[$tag->fields['lang']][$tag->fields['id']]['count'] = $tag->fields['count'];
    }
    $smarty->assign('tags', $tags);

    /** ELENCO TAGS - FINE   **/

    // se voglio usare delle categorie per le news si deve abilitare il settaggio
    //nella sezione settaggi super admin
    if(SET_ENABLE_CAT_NEWS) {

      $options                  = [];
      $options['tableFilename'] = 'news_category';
      $options['debug']         = 0;

      $cat = new NewsCategory($options);

      $filters = [];

      $opts              = [];
      $opts['sortField'] = 'ordine';
      $opts['sortOrder'] = 'ASC';
      $opts['operatore'] = 'AND';

      if(!$operator->isAdmin()) {

        $join              = [];
        $join['table']     = 'news_category_gruppi_auth';
        $join['alias']     = 'auth';
        $join['on1']       = 'news_category_id';
        $join['on2']       = 'news_category.id';
        $join['operatore'] = '=';

        $opts['joins'][] = $join;

        $filter_record              = [];
        $filter_record['chiave']    = 'auth.id_gruppo';
        $filter_record['operatore'] = '=';
        $filter_record['valore']    = $operator->fields['gruppi_id'];
        $filters[]                  = $filter_record;

      }

      $opts['filters'] = $filters;

      $list = $cat->getlist($opts);

      $cat_news_options = [];

      $cat_news_options_single    = [];
      $cat_news_options_single[0] = Traduzioni::getLang('default', 'SELECT_ONE');

      foreach($list as $catNews) {
        $nome = $catNews->fields[ACTUAL_LANGUAGE]['name'];
        if(!$catNews->fields['attivo']) {
          $nome .= '('.Traduzioni::getLang('default', 'INATTIVO').')';
        }
        $cat_news_options[$catNews->fields['id']]        = $nome;
        $cat_news_options_single[$catNews->fields['id']] = $nome;
      }
      $smarty->assign('cat_options', $cat_news_options);
      $smarty->assign('cat_options_single', $cat_news_options_single);
      $smarty->assign('CAT_NEWS_AVAILABLE', 1);
      if(!count($cat_news_options)) {
        $errore = Traduzioni::getLang('news', 'NO_CAT_AVAIL');
      }

      // bugifx 3.1.2
      if(!$READONLY) {
        if($Obj->id && !$operator->isAdmin() && !in_array($Obj->additionalData['genitore'], array_keys($cat_news_options))) {
          $readonlyCat                                                             = 1;
          $cat_options_single                                                      = [];
          $cat_options_single[$Obj->additionalData['parents']['principale']['id']] = $Obj->additionalData['parents']['principale']['name'];
          $smarty->assign('cat_options_single', $cat_options_single);
        } else {
          $readonlyCat = 0;
        }
      } else {
        $readonlyCat = 1;
      }

      $categorie_non_modificabili = [];

      if($Obj->fields['id']) {

        foreach($Obj->additionalData['parents']['accessorie'] as $k => $v) {
          if(!in_array($k, array_keys($cat_news_options))) {
            $categorie_non_modificabili[] = $v['name'];
          }
        }

      }

      $smarty->assign('categorie_non_modificabili', implode(', ', $categorie_non_modificabili));

      $smarty->assign('readOnlyCat', $readonlyCat);
    } else {
      $smarty->assign('CAT_NEWS_AVAILABLE', 0);
    }

    /** COMMENTI OPTIONS - INIZIO **/

    $commentiOptions = [];

    $commentiOptions[0] = Traduzioni::getLang('default', 'NO_ANSWER');
    $commentiOptions[1] = Traduzioni::getLang('default', 'SI_ANSWER');

    $smarty->assign('commentiOptions', $commentiOptions);

    /** COMMENTI OPTIONS - FINE   **/

    // SELEZIONE DELL'OPERATORE AUTORE (solo se advanced)

    if($operator->isAdvanced()) {
      // devo selezionare gli utenti che hanno davero accesso a questa sezione

      $options                  = [];
      $options['tableFilename'] = 'operatori';

      $OperatoriObj = new \Ueppy\core\Operatore($options);

      $joins = [];

      $join              = [];
      $join['table']     = 'gruppi';
      $join['alias']     = 'g';
      $join['on1']       = 'id';
      $join['on2']       = 'operatori.gruppi_id';
      $join['operatore'] = '=';

      $joins[] = $join;


      $filters = [];

      $filter_record              = [];
      $filter_record['chiave']    = 'operatori.super_admin';
      $filter_record['operatore'] = '=';
      $filter_record['valore']    = '1';

      $filters[] = $filter_record;

      $filter_record              = [];
      $filter_record['chiave']    = 'g.all_elements';
      $filter_record['operatore'] = '=';
      $filter_record['valore']    = '1';

      $filters[] = $filter_record;

      $filter_record              = [];
      $filter_record['chiave']    = 'operatori.gruppi_id';
      $filter_record['operatore'] = 'IN';
      $filter_record['valore']    = '(SELECT gruppi_id from permessi LEFT JOIN menu ON menu.id = permessi.menu_id WHERE gruppi_id = operatori.gruppi_id AND menu.nomefile = "news" AND menu.level < 90)';

      $filters[] = $filter_record;

      $opzioni              = [];
      $opzioni['sortField'] = 'nomecompleto';
      $opzioni['sortOrder'] = 'ASC';
      $opzioni['filters']   = $filters;
      $opzioni['operatore'] = 'OR';
      $opzioni['joins']     = $joins;

      $OperatoriObjList = $OperatoriObj->getlist($opzioni);

      $autori = [];

      foreach($OperatoriObjList as $OperatoriObj) {
        $autori[$OperatoriObj->fields['id']] = $OperatoriObj->nomecompleto;
      }

      $smarty->assign('autori', $autori);

    }

    $headerButtons[] = ['text'       => Traduzioni::getLang('default', 'PREVIEW'),
                        'icon'       => 'desktop',
                        'attributes' => ['class'     => 'upybtn btn btn-info'.$disabledButtons,
                                         'data-role' => 'preview']];

    $headerButtons[] = ['text'       => Traduzioni::getLang('default', 'SALVATAGGIO_AUTOMATICO'),
                        'icon'       => 'clock-o',
                        'attributes' => ['class'     => 'btn btn-info'.$disabledButtons,
                                         'id'        => 'salvataggioAutomaticoBtn',
                                         'data-time' => $autoSaveTime]];

    $headerButtons = array_merge($headerButtons, $BUTTONS['set']['save-close-new']);
    $footerButtons = $BUTTONS['set']['save-close'];

    // inclusione del plugin per la gestione degli allegati.
    if($Obj->allegatiAbilitati()) {
      Ueppy::loadAllegatiResources();
    }

    break;


  case 'del':

    if($operator->isAdmin()) {

      $options                  = [];
      $options['tableFilename'] = 'news';
      $options['forceAllLang']  = 1;

      $Obj = new News($mainObjOptions);

      if(Utility::isPositiveInt($_POST['id'])) {

        $opts                 = [];
        $opts['forceAllLang'] = 1;
        $Obj                  = $Obj->getById($_POST['id'], $opts);

        if(!$operator->isAdmin() && $operator->fields['id'] != $Obj->fields['operatori_id'] && !SET_MODIFICA_NEWS_DI_ALTRI) {
          $ajaxReturn           = [];
          $ajaxReturn['result'] = 0;
          $ajaxReturn['error']  = getLang('default', 'NOT_AUTH');
        } else {
          $Obj->delete();
          $ajaxReturn           = [];
          $ajaxReturn['result'] = 1;
        }

      }

    } else {

      $ajaxReturn['result'] = 0;

    }

    break;

  case 'commenti':

    /** ATTIVO OPTIONS - INIZIO **/

    $attivo_options = [];

    $record                 = [];
    $record['inp_id']       = 'valido';
    $record['inp_name']     = 'valido';
    $record['inp_class']    = '';
    $record['lbl_class']    = 'radiolbl';
    $record['inp_value']    = '1';
    $record['etichetta']    = '';
    $record['inp_selected'] = false;

    $attivo_options[] = $record;

    $smarty->assign('attivo_options', $attivo_options);

    /** ATTIVO OPTIONS - FINE   **/

    $options                  = [];
    $options['tableFilename'] = 'news';
    $options['debug']         = 0;

    $Obj     = new News($options);
    $Obj->id = 0;

    if(Utility::isPositiveInt($_POST['id'])) {
      $Obj = $Obj->getById($_POST['id']);
    }
    $smarty->assign('Obj', $Obj);

    if(!isset($_SESSION[$module_name]['to_approve'])) {
      $_SESSION[$module_name]['to_approve'] = 0;
    }

    $smarty->assign('to_approve', $_SESSION[$module_name]['to_approve']);
    
    $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang($module_name, 'GESTIONE_COMMENTI'));

    $_SESSION[$module_name]           = [];
    $_SESSION[$module_name]['filter'] = [];

    $smarty->assign('filters', json_encode($_SESSION[$module_name]['filter']));

    break;

  case 'load_commento':

    if(Utility::isPositiveInt($_POST['id'])) {

      $options                  = [];
      $options['tableFilename'] = 'commenti_news';

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

  case 'delete_commento':

    if(Utility::isPositiveInt($_POST['id'])) {

      $options                  = [];
      $options['tableFilename'] = 'commenti_news';

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
      $options['tableFilename'] = 'commenti_news';

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

  case 'seo':

    $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('default', 'SEO_TOOLS'));

    $headerButtons[] = $BUTTONS['btnDemo'];
    $headerButtons[] = $BUTTONS['btnClose'];

    break;

  case '':
    $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('default', 'LIST_ELEMENTS'));

    $urlParams = 'cmd/'.$cmd.'/act/comments';

    $headerButtons[] = ['text'       => Traduzioni::getLang($module_name, 'COMMENTI'),
                        'icon'       => 'comments',
                        'attributes' => ['class' => 'btn btn-info',
                                         'href'  => $lm->get($urlParams)]];

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
