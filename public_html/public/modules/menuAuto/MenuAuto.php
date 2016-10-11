<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (08/06/16, 11.23)                                                                     **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
namespace Ueppy\widgets\menuAuto;

use Ueppy\front\Widget;
use Ueppy\utils\Utility;
use Ueppy\core\Menu;

class MenuAuto extends Widget {

  function out($debug = 0) {

    $out = [];

    $out['title'] = false;
    $out['url']   = false;

    if(isset($this->config[ACTUAL_LANGUAGE]['title']) && $this->config[ACTUAL_LANGUAGE]['title']) {
      $out['title'] = $this->config[ACTUAL_LANGUAGE]['title'];
    }
    if(isset($this->config[ACTUAL_LANGUAGE]['url']) && $this->config[ACTUAL_LANGUAGE]['url']) {
      $out['url'] = $this->config[ACTUAL_LANGUAGE]['url'];
    }

    $search = true;

    $filters = [];

    // Se l'id genitore del menu configurato da gestione template è >= 0 lo uso per filtrare i menu
    // che hanno come parent il valore fornito (in pratica se configurato vuol dire che voglio
    // i menu figli di una determinata pagina
    if(Utility::isPositiveInt($this->config['id_genitore'], true)) {

      $filter_record              = [];
      $filter_record['chiave']    = 'parent';
      $filter_record['operatore'] = '=';
      $filter_record['valore']    = $this->config['id_genitore'];

      $filters[] = $filter_record;

    } elseif($this->config['id_genitore'] == 'GET') {

      //Utility::pre();
      /*
       * Se in id genitore viene inserita la keyword "GET" provo a reperire le pagine direttamente
       * figlie di quella in cui mi trovo
       *
       * $loaded_menu viene impostato in /public/load_modules.php, se non valorizzato vuol dire
       * che il cms non sa in che pagina si trova (ci sono alcuni casi) e quindi non ho nulla per cui
       * filtrare.
       *
       * Per questo motivo nell'else valorizzo $search = false, per evitare di estrarre i menu usando
       * i filtri accessori, ma senza quello + importante
       */

      if($this->MenuObj) {

        if($this->config['ripercorriFinoAGenitore'] != -1) {

          $parent = $this->MenuObj;

          while($parent->fields['parent'] != $this->config['ripercorriFinoAGenitore']) {
            if($parent->fields['parent'] == 0) {
              $search = false;
              break;
            }
            $parent = $parent->getById($parent->fields['parent']);
          }

          if($debug) {
            Utility::pre($parent->fields[ACTUAL_LANGUAGE]['dicitura']);
          }

          if($search) {
            $filter_record              = [];
            $filter_record['chiave']    = 'parent';
            $filter_record['operatore'] = '=';
            $filter_record['valore']    = $parent->fields['id'];

            $filters[] = $filter_record;

            if(!$out['title']) {
              $out['title'] = $parent->fields[ACTUAL_LANGUAGE]['dicitura'];
            }

          }


        } else {


          $filter_record              = [];
          $filter_record['chiave']    = 'parent';
          $filter_record['operatore'] = '=';
          $filter_record['valore']    = $this->MenuObj->fields['id'];

          $filters[] = $filter_record;

        }

      } else {

        // se ho scelto come configurazione GET e non trovo il loaded menu uso questa variabile
        // per evitare l'estrazione.
        $search = false;

      }

    }

    if($search) {

      $filter_record = [];

      $filter_record['chiave'] = 'level';
      if(isset($this->config['level']) && $this->config['level']) {
        $filter_record['operatore'] = '=';
        $filter_record['valore']    = $this->config['level'];
      } else {
        $filter_record['operatore'] = '>=';
        $filter_record['valore']    = 100;
      }

      $filters[] = $filter_record;

      $options                  = [];
      $options['tableFilename'] = 'menu';
      $options['debug']         = 0;

      $MenuObj = new Menu($options);

      $filter_record              = [];
      $filter_record['chiave']    = 'attivo';
      $filter_record['operatore'] = '=';
      $filter_record['valore']    = '1';

      $filters[] = $filter_record;

      $opts              = [];
      $opts['sortField'] = 'ordine';
      $opts['sortOrder'] = 'asc';
      $opts['debug']     = 0;
      $opts['countOnly'] = false;
      $opts['filters']   = $filters;
      $opts['operatore'] = 'AND';

      $MenuObjList = $MenuObj->getlist($opts);

      $out['list'] = [];

      foreach($MenuObjList as $key => $MenuObj) {

        $record = [];

        /** CLASSE PER LA VOCE DI MENU - INIZIO **/
        $record['classe'] = [];

        if(!$key) {
          $record['classe'][] = 'first';
        }
        if($key == (count($MenuObjList) - 1)) {
          $record['classe'][] = 'last';
        }

        if(isset($this->MenuObj) && $this->MenuObj) {
          if($this->MenuObj->fields['id'] == $MenuObj->fields['id'] || $MenuObj->isParentOf($this->MenuObj->fields['id'])) {
            $record['classe'][] = $this->config['activeclass'];
          }
        }
        $record['classe'] = implode(' ', $record['classe']);
        /** CLASSE PER LA VOCE DI MENU - FINE **/

        $urls          = $MenuObj->getUrl();
        $record['url'] = $urls[ACTUAL_LANGUAGE];

        $record['img'] = false;

        if(isset($this->config['usaImg']) && $this->config['usaImg'] && isset($this->config['htmlid']) && $this->config['htmlid'] && $tmp->fields['fileData']['img0']['it']['exists']) {
          $record['img'] = $tmp->fields['fileData']['img0']['it']['versioni'][0]['rel_path'];
        }

        if($MenuObj->fields[ACTUAL_LANGUAGE]['titolo_breve']) {
          $record['dicitura'] = $MenuObj->fields[ACTUAL_LANGUAGE]['titolo_breve'];
        } else {
          $record['dicitura'] = $MenuObj->fields[ACTUAL_LANGUAGE]['dicitura'];
        }

        if(isset($this->config['estraiFigli']) && $this->config['estraiFigli']) {

          $record['childs'] = [];

          $filters = [];

          $filter_record              = [];
          $filter_record['chiave']    = 'parent';
          $filter_record['operatore'] = '=';
          $filter_record['valore']    = $MenuObj->fields['id'];

          $filters[] = $filter_record;

          $filter_record              = [];
          $filter_record['chiave']    = 'attivo';
          $filter_record['operatore'] = '=';
          $filter_record['valore']    = '1';

          $filters[] = $filter_record;

          $options                  = [];
          $options['tableFilename'] = 'menu';
          $options['debug']         = 0;

          $ChildObj = new Menu($options);

          $opts              = [];
          $opts['sortField'] = 'ordine';
          $opts['sortOrder'] = 'asc';
          $opts['debug']     = 0;
          $opts['countOnly'] = false;
          $opts['filters']   = $filters;
          $opts['operatore'] = 'AND';

          $ChildObjList = $ChildObj->getlist($opts);

          foreach($ChildObjList as $ChildObj) {

            $child = [];

            $urls         = $ChildObj->getUrl();
            $child['url'] = $urls;

            /** URL PER LA PAGINA - FINE **/
            if($ChildObj->fields[ACTUAL_LANGUAGE]['titolo_breve']) {
              $child['dicitura'] = $ChildObj->fields[ACTUAL_LANGUAGE]['titolo_breve'];
            } else {
              $child['dicitura'] = $ChildObj->fields[ACTUAL_LANGUAGE]['dicitura'];
            }

            $record['childs'][] = $child;

          }

        }

        $out['list'][] = $record;

      }

    }

    if($this->config['debug']) {
      Utility::pre($this->config);
      Utility::pre($out);
    }

    return $out;

  }

}