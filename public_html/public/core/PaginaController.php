<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (08/06/16, 6.54)                                                                     **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
namespace Ueppy\front;

use Ueppy\core\LinkManager;
use Ueppy\utils\Utility;
use Ueppy\core\Pagina;

class PaginaController extends MainController {

  protected function getFiglie($PaginaObj, $pag = 1) {

    // estrazione delle figlie:
    $filters = [];

    $filter_record              = [];
    $filter_record['chiave']    = 'parent';
    $filter_record['operatore'] = '=';
    $filter_record['valore']    = $PaginaObj->fields['id'];

    $filters[] = $filter_record;

    $filter_record              = [];
    $filter_record['chiave']    = 'attivo';
    $filter_record['operatore'] = '=';
    $filter_record['valore']    = 1;

    $filters[] = $filter_record;

    $opts               = [];
    $opts['sortField']  = 'ordine';
    $opts['sortOrder']  = 'ASC';
    $opts['filters']    = $filters;
    $opts['countOnly']  = true;
    $opts['operatore']  = 'AND';
    $opts['joins']      = [];
    $opts['datiPagina'] = 1;

    // conto le pagine
    $contaPagine = $PaginaObj->getlist($opts);

    $paginatore = false;

    $start = 0;

    // sono + di quelle che voglio mostrare in una pagina?
    if($contaPagine > SET_PAGINE_ELEMENTI_PAGINA) {

      // creo l'array paginatore
      $paginatore                  = [];
      $paginatore['numero_pagine'] = ceil($contaPagine / SET_PAGINE_ELEMENTI_PAGINA);
      $paginatore['prev']          = false;
      $paginatore['next']          = false;
      if(Utility::isPositiveInt($pag)) {
        if($pag <= $paginatore['numero_pagine']) {
          $paginatore['pag'] = $pag;
        } else {
          $this->route->redirectTo('notFound');
        }
      } else {
        $paginatore['pag'] = 1;
      }

      /**
       * Con un po' di conti contorti, qui decido quante pagine mostrare nel paginatore,
       * in pratica se voglio mostrare 3 pagine tipo 1[2]3 cerco di fare in modo che siano sempre 3 in qualsiasi
       * pagina mi trova.
       *
       */

      $lm = LinkManager::getInstance();
      if($paginatore['numero_pagine'] > SET_PAGINE_PAGINATORE) {
        $SET_PAGINE_PAGINATORE = SET_PAGINE_PAGINATORE;

        if(!($SET_PAGINE_PAGINATORE % 2)) {
          $SET_PAGINE_PAGINATORE--;
        }

        $meta  = ($SET_PAGINE_PAGINATORE - 1) / 2;
        $prima = $meta;
        $dopo  = $meta;

        if(($paginatore['pag'] - $prima) <= 0) {
          $prima = $paginatore['pag'] - 1;
        }
        if($prima < $meta) {
          $dopo = $dopo + $meta - $prima;
        }
        if(($paginatore['pag'] + $dopo) > $paginatore['numero_pagine']) {
          $dopo = $paginatore['numero_pagine'] - $paginatore['pag'];
          if($dopo < $meta) {
            if(($paginatore['pag'] - $prima - ($meta - $dopo)) > 0) {
              $prima += ($meta - $dopo);
            } else {
              $prima = $paginatore['pag'] - 1;
            }
          }
        }
        for($i = ($paginatore['pag'] - $prima); $i <= ($paginatore['pag'] + $dopo); $i++) {
          $urlParams = 'cmd/pagina/href/'.$PaginaObj->additionalData['menu']->fields[ACTUAL_LANGUAGE]['href'].'/parent/'.$PaginaObj->additionalData['menu']->fields['parent'].'/pag/'.$i;

          $paginatore['pagine'][$i] = $lm->get($urlParams);
        }
      } else {
        for($i = 1; $i <= ($paginatore['numero_pagine']); $i++) {
          $urlParams                = 'cmd/pagina/href/'.$PaginaObj->additionalData['menu']->fields[ACTUAL_LANGUAGE]['href'].'/parent/'.$PaginaObj->additionalData['menu']->fields['parent'].'/pag/'.$i;
          $paginatore['pagine'][$i] = $lm->get($urlParams);
        }
      }

      if($paginatore['pag'] > 1) {
        $paginatore['prev'] = $paginatore['pagine'][$paginatore['pag'] - 1];
      }
      if($paginatore['pag'] < $paginatore['numero_pagine']) {
        $paginatore['next'] = $paginatore['pagine'][$paginatore['pag'] + 1];
      }

      // se vuoi fare debug per capire come deve essere un array paginatore decommenta qui:
      // Utility::pre($paginatore);

      $start = ($paginatore['pag'] - 1) * SET_PAGINE_ELEMENTI_PAGINA;

    }

    $this->setGlobal('paginatore', $paginatore);

    $opts['countOnly'] = false;
    $opts['start']     = $start;
    $opts['quanti']    = SET_PAGINE_ELEMENTI_PAGINA;

    $list = $PaginaObj->getlist($opts);

    $childs = [];

    foreach($list as $childObj) {

      $url = $childObj->getUrl();

      $child = [];

      $child['titolo']       = $childObj->additionalData['menu']->fields[ACTUAL_LANGUAGE]['dicitura'];
      $child['sottotitolo']  = $childObj->fields[ACTUAL_LANGUAGE]['sottotitolo'];
      $child['titolo_breve'] = $childObj->additionalData['menu']->fields[ACTUAL_LANGUAGE]['titolo_breve'];
      if(!$child['titolo_breve']) {
        $child['titolo_breve'] = $child['titolo'];
      }

      if(strpos($childObj->fields[ACTUAL_LANGUAGE]['testo'], '--INTRO--') !== false) {
        $intro = explode('--INTRO--', $childObj->fields[ACTUAL_LANGUAGE]['testo']);
        $intro = array_shift($intro);
        $tidy  = tidy_parse_string($intro, ['show-body-only' => true, 'indent' => true], 'utf8');
        $tidy->cleanRepair();
        $child['intro'] = $tidy->value;
      } else {
        $child['intro'] = Utility::htmlTruncate(240, $childObj->fields[ACTUAL_LANGUAGE]['testo'], true);
      }

      $child['url'] = $url[ACTUAL_LANGUAGE];

      $child['img']                 = [];
      $child['img']['url']          = REL_ROOT.'placeholder-'.$childObj->additionalData['menu']->opts['imgSettings']['img0'][0]['dimensione'].'.jpg';
      $child['img']['absolute_url'] = HOST.$child['img']['url'];
      $child['img']['alt']          = $childObj->additionalData['menu']->fields[ACTUAL_LANGUAGE]['dicitura'];
      $child['img']['title']        = false;

      if($childObj->additionalData['menu']->fields['fileData']['img0'][ACTUAL_LANGUAGE]['exists']) {
        $child['img']['url']          = $childObj->additionalData['menu']->fields['fileData']['img0'][ACTUAL_LANGUAGE]['versioni'][0]['rel_path'];
        $child['img']['absolute_url'] = $childObj->additionalData['menu']->fields['fileData']['img0'][ACTUAL_LANGUAGE]['versioni'][0]['url'];
        if($childObj->additionalData['menu']->fields[ACTUAL_LANGUAGE]['img0_alt']) {
          $child['img']['alt'] = $childObj->additionalData['menu']->fields[ACTUAL_LANGUAGE]['img0_alt'];
        }
        $child['img']['title'] = $childObj->additionalData['menu']->fields[ACTUAL_LANGUAGE]['img0_title'];
      }

      $childs[] = $child;

    }

    return $childs;

  }

  public function outList() {

    $options                  = [];
    $options['tableFilename'] = 'pagine';

    $dimensioni = explode('-', SET_PAGINE_DIM_INLINEA);

    if($dimensioni[0]) {
      $imgSetting = [];
      list($imgSetting['dimensione'], $imgSetting['tipo']) = explode('|', $dimensioni[0]);
      $options['imgSettings']['img0'][] = $imgSetting;
    }

    if(isset($dimensioni[1]) && $dimensioni[1]) {
      $imgSetting = [];
      list($imgSetting['dimensione'], $imgSetting['tipo']) = explode('|', $dimensioni[1]);
      $options['imgSettings']['img1'][] = $imgSetting;
    }

    $PaginaObj = new Pagina($options);


    $lev = '';
    if($this->route->getParam('genitore')) {
      $lev = $this->route->getParam('url');
      $lev = rtrim($lev, '/');
      $lev = explode('/', $lev);
      array_pop($lev);
      $lev = implode('|', $lev);
    }
    $opts               = [];
    $opts['href']       = $this->route->getParam('href');
    $opts['path']       = $lev;
    $opts['debug']      = 0;
    $opts['datiPagina'] = 1;

    $PaginaObj = $PaginaObj->getByHref($opts);

    if(!$PaginaObj || !$PaginaObj->additionalData['menu']->fields['attivo']) {
      $this->route->redirectTo('notFound');
    }

    $PAGINA = $this->getDatiPagina($PaginaObj);

    $PAGINA['pagine_figlie'] = $this->getFiglie($PaginaObj, $this->route->getParam('pagina'));

    $out['OBJ']    = $PaginaObj;
    $out['SMARTY'] = $PAGINA;

    return $out;

  }

  private function getDatiPagina($PaginaObj) {

    $PAGINA                  = [];
    $PAGINA['id']            = false;
    $PAGINA['titolo']        = false;
    $PAGINA['titolo_breve']  = false;
    $PAGINA['testo']         = false;
    $PAGINA['pagine_figlie'] = [];
    $PAGINA['commentabile']  = false;

    $PAGINA['img0']          = [];
    $PAGINA['img0']['url']   = false;
    $PAGINA['img0']['alt']   = false;
    $PAGINA['img0']['title'] = false;

    $PAGINA['img1']          = [];
    $PAGINA['img1']['url']   = false;
    $PAGINA['img1']['alt']   = false;
    $PAGINA['img1']['title'] = false;

    $PAGINA['allegati']             = [];
    $PAGINA['allegati']['immagini'] = [];
    $PAGINA['allegati']['files']    = [];

    $PAGINA['id']          = $PaginaObj->fields['id'];
    $PAGINA['titolo']      = $PaginaObj->additionalData['menu']->fields[ACTUAL_LANGUAGE]['dicitura'];
    $PAGINA['sottotitolo'] = $PaginaObj->fields[ACTUAL_LANGUAGE]['sottotitolo'];

    $this->setGlobal('SUBTITLE', $PAGINA['sottotitolo']);

    if($PaginaObj->additionalData['menu']->fields[ACTUAL_LANGUAGE]['titolo_breve']) {
      $PAGINA['titolo_breve'] = $PaginaObj->additionalData['menu']->fields[ACTUAL_LANGUAGE]['titolo_breve'];
    } else {
      $PAGINA['titolo_breve'] = $PaginaObj->additionalData['menu']->fields[ACTUAL_LANGUAGE]['dicitura'];
    }

    // testo
    if(strpos($PaginaObj->fields[ACTUAL_LANGUAGE]['testo'], '--INTRO--') !== false) {
      list($intro, $testo) = explode('--INTRO--', $PaginaObj->fields[ACTUAL_LANGUAGE]['testo']);
      $tidy = tidy_parse_string($testo, ['show-body-only' => true, 'indent' => true], 'utf8');
      $tidy->cleanRepair();
      $PAGINA['testo'] = $tidy->value;
      $tidy            = tidy_parse_string($intro, ['show-body-only' => true, 'indent' => true], 'utf8');
      $tidy->cleanRepair();
      $PAGINA['intro'] = $tidy->value;
    } else {
      $PAGINA['testo'] = $PaginaObj->fields[ACTUAL_LANGUAGE]['testo'];
    }

    $PAGINA['commentabile'] = $PaginaObj->fields['commenti'];

    if($PaginaObj->additionalData['menu']->fields['fileData']['img0'][ACTUAL_LANGUAGE]['exists']) {

      $IMG0          = [];
      $IMG0['path']  = $PaginaObj->additionalData['menu']->fields['fileData']['img0'][ACTUAL_LANGUAGE]['versioni'][0]['rel_path'];
      $IMG0['title'] = $PaginaObj->additionalData['menu']->fields[ACTUAL_LANGUAGE]['img0_title'];

      if($PaginaObj->additionalData['menu']->fields[ACTUAL_LANGUAGE]['img0_alt']) {
        $IMG0['alt'] = $PaginaObj->additionalData['menu']->fields[ACTUAL_LANGUAGE]['img0_alt'];
      } else {
        $IMG0['alt'] = $PaginaObj->additionalData['menu']->fields[ACTUAL_LANGUAGE]['dicitura'];
      }

      $this->setGlobal('IMG0', $IMG0);

    }

    if($PaginaObj->additionalData['menu']->fields['fileData']['img1'][ACTUAL_LANGUAGE]['exists']) {
      $IMG1['path']  = $PaginaObj->additionalData['menu']->fields['fileData']['img1'][ACTUAL_LANGUAGE]['versioni'][0]['rel_path'];
      $IMG1['title'] = $PaginaObj->additionalData['menu']->fields[ACTUAL_LANGUAGE]['img1_title'];

      if($PaginaObj->additionalData['menu']->fields[ACTUAL_LANGUAGE]['img0_alt']) {
        $IMG1['alt'] = $PaginaObj->additionalData['menu']->fields[ACTUAL_LANGUAGE]['img1_alt'];
      } else {
        $IMG1['alt'] = $PaginaObj->additionalData['menu']->fields[ACTUAL_LANGUAGE]['dicitura'];
      }

      $this->setGlobal('IMG1', $IMG1);

    }

    if($PaginaObj->allegatiAbilitati()) {

      $opzioni               = [];
      $opzioni['estensioni'] = 'img';

      $immagini = $PaginaObj->getAllegati($opzioni);


      foreach($immagini as $img) {

        if($img->fields['fileData']['nomefile']['exists']) {
          // escludo dall'elenco la versione originale e la thumb usata in admin.
          $versioni = $img->fields['fileData']['nomefile']['versioni'];
          array_shift($versioni);
          array_shift($versioni);

          $immagine                = [];
          $immagine['versioni']    = [];
          $immagine['title']       = $img->fields[ACTUAL_LANGUAGE]['title'];
          $immagine['alt']         = $img->fields[ACTUAL_LANGUAGE]['alt'];
          $immagine['descrizione'] = $img->fields[ACTUAL_LANGUAGE]['descrizione'];

          foreach($versioni as $versione) {
            $v                      = [];
            $v['url']               = $versione['rel_path'];
            $immagine['versioni'][] = $v;
          }

          $PAGINA['allegati']['immagini'][] = $immagine;
        }
      }

      $opzioni               = [];
      $opzioni['estensioni'] = 'notimg';

      $files = $PaginaObj->getAllegati($opzioni);

      foreach($files as $f) {
        $file                          = [];
        $file['path']                  = $f->fields['fileData']['nomefile']['rel_path'];
        $file['nomefile']              = basename($f->fields['fileData']['nomefile']['rel_path']);
        $file['title']                 = $f->fields[ACTUAL_LANGUAGE]['title'];
        $file['estensione']            = $f->fields['fileData']['nomefile']['ext'];
        $file['descrizione']           = $f->fields[ACTUAL_LANGUAGE]['descrizione'];
        $file['size']                  = Utility::humanreadable(filesize($f->fields['fileData']['nomefile']['path']));
        $PAGINA['allegati']['files'][] = $file;
      }

    }

    if(!$PaginaObj->additionalData['menu']->fields[ACTUAL_LANGUAGE]['htmltitle']) {
      $this->setGlobal('TITLE', $PaginaObj->additionalData['menu']->fields[ACTUAL_LANGUAGE]['dicitura']);
    }
    if($PaginaObj->additionalData['menu']->fields[ACTUAL_LANGUAGE]['description']) {
      $this->setGlobal('DESCRIPTION', $PaginaObj->additionalData['menu']->fields[ACTUAL_LANGUAGE]['description']);
    }

    return $PAGINA;


  }

  public function outRead() {

    $options                  = [];
    $options['tableFilename'] = 'pagine';

    $dimensioni = explode('-', SET_PAGINE_DIM_INLINEA);

    if($dimensioni[0]) {
      $imgSetting = [];
      list($imgSetting['dimensione'], $imgSetting['tipo']) = explode('|', $dimensioni[0]);
      $options['imgSettings']['img0'][] = $imgSetting;
    }

    if(isset($dimensioni[1]) && $dimensioni[1]) {
      $imgSetting = [];
      list($imgSetting['dimensione'], $imgSetting['tipo']) = explode('|', $dimensioni[1]);
      $options['imgSettings']['img1'][] = $imgSetting;
    }

    $PaginaObj = new Pagina($options);

    $lev = '';
    if($this->route->getParam('genitore')) {
      $lev = $this->route->getParam('url');
      $lev = rtrim($lev, '/');
      $lev = explode('/', $lev);
      array_pop($lev);
      $lev = implode('|', $lev);
    }
    $opts               = [];
    $opts['href']       = $this->route->getParam('href');
    $opts['path']       = $lev;
    $opts['debug']      = 0;
    $opts['datiPagina'] = 1;

    $PaginaObj = $PaginaObj->getByHref($opts);

    if(!$PaginaObj || !$PaginaObj->additionalData['menu']->fields['attivo']) {
      $this->route->redirectTo('notFound');
    }

    $PAGINA = $this->getDatiPagina($PaginaObj);

    $out['OBJ']    = $PaginaObj;
    $out['SMARTY'] = $PAGINA;

    return $out;

  }
}