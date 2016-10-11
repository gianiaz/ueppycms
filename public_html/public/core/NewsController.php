<?php
/***************/
/** v.1.02    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.02 (23/08/2016, 10.13)                                                                   **/
/** - Bugfix nell'identificare i parametri opzionali in title e description                      **/
/**                                                                                              **/
/** v.1.01 (22/08/2016, 9.46)                                                                    **/
/** - Aggiunta la possibilità di fornire un cmd diverso per generare l'url nel metodo getPagina  **/
/**   e per i metodi outList e outRead                                                           **/
/**                                                                                              **/
/** v.1.00 (09/07/16, 15.06)                                                                     **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
namespace Ueppy\front;

use Ueppy\blog\News;
use Ueppy\blog\NewsCategory;
use Ueppy\core\LinkManager;
use Ueppy\core\Ueppy;
use Ueppy\utils\Time;
use Ueppy\utils\Utility;
use Ueppy\core\Traduzioni;


class NewsController extends MainController {

  private $NewsObj         = null;
  private $NewsCategoryObj = null;
  private $pag             = null;
  private $openGraphImage  = '';

  private function setVariabiliGlobali($tipo = 'read') {


    $search_meta = ['TITOLO'   => '',
                    'CATEGORY' => '',
                    'TAG'      => '',
                    'PAG'      => ''];

    if($this->NewsObj && $this->NewsObj->id) {
      $search_meta['TITOLO'] = $this->NewsObj->fields[ACTUAL_LANGUAGE]['titolo'];
    }

    if($this->NewsCategoryObj && $this->NewsCategoryObj->id) {
      $search_meta['CATEGORY'] = $this->NewsCategoryObj->fields[ACTUAL_LANGUAGE]['name'];
    }

    if($tipo == 'tag') {
      $search_meta['TAG'] = $this->route->getParam('tag');
    }

    $pag = intval($this->route->getParam('pag'));
    if($pag > 1) {
      $search_meta['PAG'] = $this->route->getParam('pag');
    }

    if($this->NewsObj && $this->NewsObj->fields[ACTUAL_LANGUAGE]['description']) {
      $DESCRIPTION = $this->NewsObj->fields[ACTUAL_LANGUAGE]['description'];
    } else {
      $DESCRIPTION = $this->meta['news']['casi'][$tipo]['description'];
    }


    if($this->NewsObj && $this->NewsObj->fields[ACTUAL_LANGUAGE]['htmltitle']) {
      $TITLE = $this->NewsObj->fields[ACTUAL_LANGUAGE]['htmltitle'];
    } else {
      $TITLE = $this->meta['news']['casi'][$tipo]['htmltitle'];
    }

    foreach($search_meta as $k => $value) {
      $re = '/\[\[(.*)\{'.$k.'\}(.*)\]\]/U';
      if(preg_match($re, $TITLE, $m)) {
        if($value) {
          $TITLE       = str_replace($m[0], $m[1].$value.$m[2], $TITLE);
          $DESCRIPTION = str_replace($m[0], $m[1].$value.$m[2], $DESCRIPTION);
        } else {
          $TITLE       = str_replace($m[0], '', $TITLE);
          $DESCRIPTION = str_replace($m[0], '', $DESCRIPTION);
        }
      } else {
        $TITLE       = str_replace('{'.$k.'}', $value, $TITLE);
        $DESCRIPTION = str_replace('{'.$k.'}', $value, $DESCRIPTION);
      }
    }

    $this->setGlobal('DESCRIPTION', $DESCRIPTION);
    $this->setGlobal('TITLE', $TITLE);

  }

  private function getPagina($filters = [], $joins = [], $cmd = null) {

    if(!$cmd) {
      $cmd = 'news';
    }

    $lm = LinkManager::getInstance();

    $filtersMetodo = [];

    $filter_record              = [];
    $filter_record['chiave']    = 'stato';
    $filter_record['operatore'] = '=';
    $filter_record['valore']    = 'ATTIVO';

    $filtersMetodo[] = $filter_record;

    $filter_record              = [];
    $filter_record['chiave']    = 'lingua_attiva';
    $filter_record['operatore'] = '=';
    $filter_record['valore']    = '1';

    $filtersMetodo[] = $filter_record;

    $filters = array_merge($filters, $filtersMetodo);

    $joinsMetodo = [];

    $join              = [];
    $join['table']     = 'operatori';
    $join['alias']     = 'o';
    $join['on1']       = 'id';
    $join['on2']       = 'news.operatori_id';
    $join['operatore'] = '=';

    $joinsMetodo[] = $join;

    $joins = array_merge($joins, $joinsMetodo);

    $opts              = [];
    $opts['sortField'] = 'attiva_dal';
    $opts['sortOrder'] = 'DESC';
    $opts['filters']   = $filters;
    $opts['countOnly'] = true;
    $opts['operatore'] = 'AND';
    $opts['debug']     = 0;
    $opts['joins']     = $joins;

    $options                  = [];
    $options['tableFilename'] = 'news';

    $this->NewsObj = new News($options);

    // conto le news
    $contaNews = $this->NewsObj->getlist($opts);

    $paginatore = false;

    $start = 0;

    // sono + di quelle che voglio mostrare in una pagina?
    if($contaNews > SET_NEWS_ELEMENTI_PAGINA) {
      // creo l'array paginatore
      $paginatore                  = [];
      $paginatore['numero_pagine'] = ceil($contaNews / SET_NEWS_ELEMENTI_PAGINA);
      $paginatore['prev']          = false;
      $paginatore['next']          = false;
      if($this->pag <= $paginatore['numero_pagine']) {
        $paginatore['pag'] = $this->pag;
      } else {
        $this->route->redirectTo('notFound');
      }

      /**
       * Con un po' di conti contorti, qui decido quante pagine mostrare nel paginatore,
       * in pratica se voglio mostrare 3 pagine tipo 1[2]3 cerco di fare in modo che siano sempre 3 in qualsiasi
       * pagina mi trova.
       *
       */
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
          $lmParams = 'cmd/'.$cmd.'/act/list/';
          if($this->route->getParam('tag')) {
            $lmParams .= 'tag/'.$this->route->getParam('tag').'/';
          } else {
            if($this->NewsCategoryObj) {
              $lmParams .= 'cat/'.$this->NewsCategoryObj->fields[ACTUAL_LANGUAGE]['href'].'/';
            }
          }
          $lmParams .= 'pag/'.$i;
          $paginatore['pagine'][$i] = $lm->get($lmParams);
        }
      } else {
        for($i = 1; $i <= ($paginatore['numero_pagine']); $i++) {
          $lmParams = 'cmd/'.$cmd.'/act/list/';
          if($this->route->getParam('tag')) {
            $lmParams .= 'tag/'.$this->route->getParam('tag').'/';
          } else {
            if($this->NewsCategoryObj) {
              $lmParams .= 'cat/'.$this->NewsCategoryObj->fields[ACTUAL_LANGUAGE]['href'].'/';
            }
          }
          $lmParams .= 'pag/'.$i;
          $paginatore['pagine'][$i] = $lm->get($lmParams);
        }
      }
      if($paginatore['pag'] > 1) {
        $paginatore['prev'] = $paginatore['pagine'][$paginatore['pag'] - 1];
      }
      if($paginatore['pag'] < $paginatore['numero_pagine']) {
        $paginatore['next'] = $paginatore['pagine'][$paginatore['pag'] + 1];
      }

      $this->setGlobal('paginatore', $paginatore);

      $start = ($paginatore['pag'] - 1) * SET_NEWS_ELEMENTI_PAGINA;

    }

    $this->NewsObj->addField('o.nomecompleto as autore');
    $opts['countOnly'] = false;
    $opts['start']     = $start;
    $opts['quanti']    = SET_NEWS_ELEMENTI_PAGINA;
    $opts['debug']     = 0;
    $NewsObjList       = $this->NewsObj->getlist($opts);

    if($this->route->getParam('type') == 'rss') {

      $opzioni = null;
      if($this->NewsCategoryObj && $this->NewsCategoryObj) {

        $opzioni          = [];
        $opzioni['fname'] = $this->NewsCategoryObj->fields[ACTUAL_LANGUAGE]['href'];
        $opzioni['title'] = $this->NewsCategoryObj->fields[ACTUAL_LANGUAGE]['name'];
      }

      echo $this->NewsObj->toRss($list, $opzioni);

      // FIXME
      die;

    } else {

      if($this->NewsCategoryObj && $this->NewsCategoryObj) {
        $lmParams = 'cmd/'.$cmd.'/act/list/type/rss/cat/'.$this->NewsCategoryObj->fields[ACTUAL_LANGUAGE]['href'];
      } else {
        $lmParams = 'cmd/'.$cmd.'/act/list/type/rss';
      }

      $rss = $lm->get($lmParams);

      $elenco = [];

      $dimensioni = explode('-', SET_NEWS_IMG_SIZE);

      $imgSettings = [];

      foreach($dimensioni as $d) {
        @list($dimensione, $tipo, $typeofcrop) = explode('|', $d);
        $imgSetting = [];

        $imgSetting               = [];
        $imgSetting['dimensione'] = $dimensione;
        $imgSetting['tipo']       = $tipo;
        if($tipo == 'crop') {
          $imgSetting['options'] = [];
          if(isset($typeofcrop) && $typeofcrop) {
            $imgSetting['options']['type_of_resize'] = $typeofcrop;
          } else {
            $imgSetting['options']['type_of_resize'] = 'lossless';
          }
        }
        $imgSettings[] = $imgSetting;
      }

      $pagina = [];

      foreach($NewsObjList as $NewsObj) {


        $opts = [];
        if($cmd) {
          $opts['cmd'] = $cmd;
        }

        $url = $NewsObj->getUrl($opts);

        $newsRecord = [];

        $newsRecord['titolo']   = $NewsObj->fields[ACTUAL_LANGUAGE]['titolo'];
        $newsRecord['intro']    = $NewsObj->fields[ACTUAL_LANGUAGE]['intro'];
        $newsRecord['commenti'] = false;

        if(SET_NEWS_COMMENTI) {
          $opts                   = [];
          $opts['countOnly']      = true;
          $newsRecord['commenti'] = $NewsObj->getCommenti($opts);
        }


        $t                  = new Time($NewsObj->attiva_dal);
        $newsRecord['data'] = $t->format(Traduzioni::getLang('cal', 'DATE_FORMAT'));
        $newsRecord['ora']  = $t->format(Traduzioni::getLang('cal', 'TIME_FORMAT'));

        $newsRecord['autore'] = $NewsObj->fields['autore'];

        $newsRecord['img']          = [];
        $newsRecord['img']['url']   = '/placeholder-'.$imgSettings[0]['dimensione'].'.jpg';
        $newsRecord['img']['alt']   = $NewsObj->fields[ACTUAL_LANGUAGE]['titolo'];
        $newsRecord['img']['title'] = '';
        $newsRecord['url']          = $url[ACTUAL_LANGUAGE];

        $opts               = [];
        $opts['estensioni'] = 'img';
        $opts['quanti']     = 1;

        $immaginiAllegate = $NewsObj->getAllegati($opts);

        if(count($immaginiAllegate) && $immaginiAllegate[0]->fields['fileData']['nomefile']['exists'] && $immaginiAllegate[0]->fields['fileData']['nomefile']['versioni'][2]['rel_path']) {
          $newsRecord['img']['url']   = $immaginiAllegate[0]->fields['fileData']['nomefile']['versioni'][2]['niceUrl'];
          $newsRecord['img']['title'] = $immaginiAllegate[0]->fields[ACTUAL_LANGUAGE]['title'];
          if($immaginiAllegate[0]->fields[ACTUAL_LANGUAGE]['alt']) {
            $newsRecord['img']['alt'] = $immaginiAllegate[0]->fields[ACTUAL_LANGUAGE]['alt'];
          }
        }

        $pagina[] = $newsRecord;

      }

      return $pagina;

    }
  }

  function outList($cmd = 'news') {

    /*
    $out           = [];
    $out['SMARTY'] = 'Dati che userai in smarty';
    $out['OBJ']    = 'Oggetto principale';
    $out['data']   = 'Dati vari strutturati come vuoi';
    */

    $tag       = $this->route->getParam('tag');
    $category  = $this->route->getParam('category');
    $this->pag = $this->route->getParam('pag');

    if(!Utility::isPositiveInt($this->pag)) {
      $this->pag = 1;
    }

    if($category) {

      $options                  = [];
      $options['tableFilename'] = 'news_category';

      $this->NewsCategoryObj = new NewsCategory($options);

      $this->NewsCategoryObj = $this->NewsCategoryObj->getByHref($category);

      if(!$this->NewsCategoryObj || !$this->NewsCategoryObj->attivo) {
        $this->route->redirectTo('notFound');
      }
    }

    $filters = [];
    $joins   = [];

    if($tag) {

      $join              = [];
      $join['table']     = 'rel_news_tags';
      $join['alias']     = 'rel';
      $join['on1']       = 'news_id';
      $join['on2']       = 'news.id';
      $join['operatore'] = '=';

      $joins[] = $join;

      $join              = [];
      $join['table']     = 'tags';
      $join['alias']     = '';
      $join['on1']       = 'id';
      $join['on2']       = 'rel.tag_id';
      $join['operatore'] = '=';

      $joins[] = $join;

      $filter_record              = [];
      $filter_record['chiave']    = 'tags.tag';
      $filter_record['operatore'] = '=';
      $filter_record['valore']    = urldecode($tag);

      $filters[] = $filter_record;

      $this->setGlobal('PAGE_TITLE', Traduzioni::getLang('news', 'NEWS_CON_TAG').' '.$tag);

      $this->setVariabiliGlobali('tag');

      // qui ci passo se sono in una pagina di elenco news per una determinata categoria
    } elseif($category) {

      $join              = [];
      $join['table']     = 'rel_news_category_news';
      $join['alias']     = 'rel';
      $join['on1']       = 'id_news';
      $join['on2']       = 'news.id';
      $join['operatore'] = '=';

      $joins[] = $join;

      $filter_record              = [];
      $filter_record['chiave']    = 'rel.news_category_id';
      $filter_record['operatore'] = '=';
      $filter_record['valore']    = $this->NewsCategoryObj->fields['id'];

      $filters[] = $filter_record;

      $this->setVariabiliGlobali('list');

    } else {
      $this->setVariabiliGlobali('list');
    }


    // e qui ci passo sempre, però se non son passato dai precedenti if vuol dire che sono nell'elenco generale.
    $data         = [];
    $data['news'] = $this->getPagina($filters, $joins, $cmd);
    
    $out                            = [];
    $out['OBJ']                     = $this->NewsObj;
    $out['SMARTY']                  = $data;
    $out['data']['NewsCategoryObj'] = $this->NewsCategoryObj;

    return $out;

  }

  function outRead($cmd = 'news') {

    $lm = LinkManager::getInstance();

    $return = [];

    $return              = [];
    $return['id']        = false;
    $return['titolo']    = false;
    $return['data']      = false;
    $return['ora']       = false;
    $return['intro']     = false;
    $return['testo']     = false;
    $return['tags']      = false;
    $return['autore']    = false;
    $return['categorie'] = false;

    $return['TITLE']       = false;
    $return['DESCRIPTION'] = false;
    $return['KEYWORDS']    = false;

    $return['commentabile']         = false;
    $return['allegati']             = [];
    $return['allegati']['immagini'] = [];
    $return['allegati']['files']    = [];

    $opts         = [];
    $opts['href'] = $this->route->getParam('href');

    $options                  = [];
    $options['tableFilename'] = 'news';

    $this->NewsObj = new News($options);

    $this->NewsObj = $this->NewsObj->getByHref($opts);

    if(!$this->NewsObj) {
      $this->route->redirectTo('notFound');
    }


    if($this->NewsObj) {

      if($this->NewsObj->stato == 'CANCELLATA') {


        $messaggio            = [];
        $messaggio['type']    = 'info';
        $messaggio['title']   = Traduzioni::getLang('news', 'NEWS');
        $messaggio['msg']     = html_entity_decode(Traduzioni::getLang('news', 'NEWS_CANCELLATA'), ENT_QUOTES, 'UTF-8');
        $messaggio['back']    = Traduzioni::getLang('default', 'TORNA_ALLA_HOME');
        $messaggio['backUrl'] = $lm->get('');

        $return['messaggio'] = $messaggio;

      } else {

        $return['id']     = $this->NewsObj->fields['id'];
        $return['titolo'] = $this->NewsObj->fields[ACTUAL_LANGUAGE]['titolo'];
        $return['intro']  = $this->NewsObj->fields[ACTUAL_LANGUAGE]['intro'];

        $t              = new Time($this->NewsObj->attiva_dal);
        $return['data'] = $t->format(Traduzioni::getLang('cal', 'DATE_FORMAT'));
        $return['ora']  = $t->format(Traduzioni::getLang('cal', 'TIME_FORMAT'));

        $return['testo']  = $this->NewsObj->fields[ACTUAL_LANGUAGE]['testo'];
        $return['autore'] = '';
        if($this->NewsObj->fields['author']) {
          $return['autore'] = $this->NewsObj->fields['author'];
        }
        $return['commentabile'] = $this->NewsObj->fields['commenti'];

        if(SET_NEWS_COMMENTI) {
          $opts               = [];
          $opts['countOnly']  = true;
          $return['commenti'] = $this->NewsObj->getCommenti($opts);
        }

        // tags
        $this->NewsObj->getTags();
        if(isset($this->NewsObj->additionalData['tags']) && isset($this->NewsObj->additionalData['tags'][ACTUAL_LANGUAGE]) && $this->NewsObj->additionalData['tags'][ACTUAL_LANGUAGE]['string']) {
          $return['tags'] = [];
          foreach($this->NewsObj->additionalData['tags'][ACTUAL_LANGUAGE]['list'] as $tagData) {
            $tag              = [];
            $tag['tag']       = $tagData['tag'];
            $tag['link']      = $lm->get('cmd/'.$cmd.'/act/list/tag/'.$tagData['tag']);
            $return['tags'][] = $tag;
          }
        }

        if(defined('SET_ENABLE_CAT_NEWS') && SET_ENABLE_CAT_NEWS) {

          $options                  = [];
          $options['tableFilename'] = 'news_category';

          $this->NewsCategoryObj = new NewsCategory($options);
          $this->NewsCategoryObj = $this->NewsCategoryObj->getByHref($this->NewsObj->additionalData['parents']['principale']['href']);

          $CATEGORIANEWS         = [];
          $CATEGORIANEWS['nome'] = $this->NewsObj->additionalData['parents']['principale']['name'];
          $CATEGORIANEWS['link'] = $lm->get('cmd/'.$cmd.'/act/list/cat/'.$this->NewsObj->additionalData['parents']['principale']['href']);

          $return['categorie'][] = $CATEGORIANEWS;

          foreach($this->NewsObj->additionalData['parents']['accessorie'] as $catAccessoria) {

            $categoria             = [];
            $CATEGORIANEWS['nome'] = $catAccessoria['name'];
            $CATEGORIANEWS['link'] = $lm->get('cmd/'.$cmd.'/act/list/cat/'.$catAccessoria['href']);

            $return['categorie'][] = $categoria;

          }

        }

        if($this->NewsObj->allegatiAbilitati()) {
          $opzioni               = [];
          $opzioni['estensioni'] = 'img';

          $immagini = $this->NewsObj->getAllegati($opzioni);

          $return['allegati']['immagini'] = [];

          foreach($immagini as $k => $img) {
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
              $v['width']             = $versione['imgData'][0];
              $v['height']            = $versione['imgData'][1];
              $immagine['versioni'][] = $v;
            }


            $return['allegati']['immagini'][] = $immagine;

            if(!$k) {
              $this->setOpenGraph(HOST.$immagine['versioni'][0]['url']);
            }
          }

          $opzioni               = [];
          $opzioni['estensioni'] = 'notimg';

          $files = $this->NewsObj->getAllegati($opzioni);

          $return['allegati']['files'] = [];

          foreach($files as $f) {
            $file                          = [];
            $file['path']                  = $f->fields['fileData']['nomefile']['rel_path'];
            $file['nomefile']              = basename($f->fields['fileData']['nomefile']['rel_path']);
            $file['title']                 = $f->fields[ACTUAL_LANGUAGE]['title'];
            $file['estensione']            = $f->fields['fileData']['nomefile']['ext'];
            $file['descrizione']           = $f->fields[ACTUAL_LANGUAGE]['descrizione'];
            $return['allegati']['files'][] = $file;
          }

        }

        $this->setGlobal('PAGE_TITLE', $return['titolo']);

      }
    }

    $this->setVariabiliGlobali('read');

    $data                    = [];
    $data['NewsCategoryObj'] = $this->NewsCategoryObj;

    $out           = [];
    $out['OBJ']    = $this->NewsObj;
    $out['SMARTY'] = $return;
    $out['data']   = $data;

    return $out;


  }

}
