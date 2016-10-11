<?php
/***************/
/** v.1.03    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.03 (26/07/2016, 15.07)                                                                   **/
/** - Bugfix nel metodo rescan                                                                   **/
/** v.1.02 (23/07/2016, 15.42)                                                                   **/
/**                                                                                              **/
/** - Aggiunta la generazione dei niceurl a prodotti e pagine                                    **/
/** - Aggiunta l'estrazione del nice url globalmente                                             **/
/** v.1.01 (12/11/2015, 18.55)                                                                   **/
/** - Aggiunto namespace                                                                         **/
/**                                                                                              **/
/** v.1.00                                                                                       **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
namespace Ueppy\core;

use Ueppy\blog\News;
use Ueppy\blog\NewsCategory;
use Ueppy\core\Dba;
use Ueppy\ecommerce\Prodotto;
use Ueppy\core\Pagina;
use Ueppy\utils\Utility;

class Allegati extends Dba {

  private $forceType = false;

  function __construct($opts = null) {

    parent::__construct($opts);

    $this->fields['id'] = 0;

  }

  protected function fillresults() {

    if($this->forceType) {
      $this->dataDescription['types']['nomefile'] = $this->forceType;
    }

    if($this->resultSet) {

      $result = [];

      $k = 0;
      while($row = mysqli_fetch_assoc($this->resultSet)) {
        if($this->opts['debug']) {
          $this->log($row);
        }
        foreach($row as $chiave => $valore) {
          if($chiave == $this->dataDescription['desc']['cp']) {
            $result[$k][$chiave] = $valore;
          } elseif(in_array($chiave, $this->dataDescription['desc']['ci'])) {
            $result[$k][$chiave] = $valore;
          } elseif(preg_match_all('/^(.*)_([a-z]{2})$/', $chiave, $m) && (in_array($m[1][0], $this->dataDescription['desc']['cd']) || $m[1][0] == 'id')) {
            $result[$k][$m[2][0]][$m[1][0]] = $valore;
          } else {
            if(strpos($chiave, '.') !== false) {
              list($grp, $chiave) = explode('.', $chiave);
              if(!isset($result[$k][$grp])) {
                $result[$k][$grp] = [];
              }
              $result[$k][$grp][$chiave] = $valore;
            } else {
              $result[$k][$chiave] = $valore;
            }
          }
        }
        if($this->opts['debug']) {
          $this->log($result[$k]);
        }

        // estrazione informazioni sugli eventuali files allegati
        if(is_array($this->opts['files']) && count($this->opts['files'])) {
          foreach($this->opts['files'] as $fileField) {
            if(in_array($fileField, $this->dataDescription['desc']['ci'])) {
              $result[$k]['fileData'][$fileField] = $this->getFileData($fileField, $result[$k], false);
            } elseif(in_array($fileField, $this->dataDescription['desc']['cd'])) {
              foreach($this->opts['langs'] as $l) {
                $result[$k]['fileData'][$fileField][$l] = $this->getFileData($fileField, $result[$k], $l);
              }

            }
          }
        }
        $k++;
      }

      // unsetto il resultset che non mi serve più
      unset($this->resultSet);

      $class = get_class($this);

      $res = [];

      foreach($result as $val) {

        $opts = $this->opts;
        if(isset($opts['forceAllLangList'])) {
          $opts['forceAllLang'] = $opts['forceAllLangList'];
        }
        $tmp         = new $class($opts);
        $tmp->fields = $val;

        $tmp->formatData();

        $res[] = $tmp;
      }
      $debug_backtrace = debug_backtrace();

      $cacheFile = DOC_ROOT.REL_ROOT.UPLOAD.'cache/rewrite.json';


      if(!file_exists($cacheFile)) {
        $urls = [];
        file_put_contents($cacheFile, json_encode($urls));
      }

      $urls = json_decode(file_get_contents($cacheFile), true);

      $arr = [];

      foreach($res as $obj) {

        if($obj->fields['fileData']['nomefile']['exists']) {

          foreach($obj->fields['fileData']['nomefile']['versioni'] as $k => $versione) {

            $niceUrl = array_search($versione['rel_path'], $urls);

            if(!$niceUrl) {
              $niceUrl = $versione['rel_path'];
            }
            $obj->fields['fileData']['nomefile']['versioni'][$k]['niceUrl'] = $niceUrl;
          }

        }

        $arr[] = $obj;

      }

      if(count($arr) == 1 && $this->getById) {
        return $arr[0];
      } else {
        return $arr;
      }

    } else {
      $this->log("Errore Query:\n".$this->sql."\n\nMysql Errore:\n".$this->lastError, ['level' => 'error', 'dieAfterError' => true]);
    }
  }

  function getNextOrderAllegato() {

    $sql = 'SELECT MAX(ordine) + 1 FROM allegati WHERE genitore = "'.$this->fields['genitore'].'" AND id_genitore = "'.$this->fields['id_genitore'].'"';

    $res = $this->doQuery($sql);

    $row = mysqli_fetch_row($res);

    if(!$row[0]) {
      return 1;
    }

    return $row[0];

  }

  function isAnImage() {

    return in_array($this->fields['estensione'], ['jpg', 'jpeg', 'png', 'gif']);
  }

  public function getAllegati($opts = null) {

    $opzioni                 = [];
    $opzioni['genitore']     = false;
    $opzioni['id_genitore']  = false;
    $opzioni['forceAllLang'] = false;   // boolean, se impostato a true reperisce tutte le lingue altrimenti esegue ricerche ed estrazione sulla sola lingua attuale
    $opzioni['debug']        = 0;       // debug
    $opzioni['countOnly']    = false;   // passare true per ottenere solo il numero di record che soddisfano la ricerca
    $opzioni['estensioni']   = false;

    if($opts) {
      $opzioni = $this->array_replace_recursive($opzioni, $opts);
    }

    if($opzioni['debug']) {
      $this->opts['debug'] = $opzioni['debug'];
      $this->log($opzioni);
    }

    if(!$opzioni['genitore'] || !$opzioni['id_genitore']) {
      $this->log($opzioni, ['level' => 'error', 'dieAfterError' => true]);
    }

    $filters = [];

    $filter_record              = [];
    $filter_record['chiave']    = 'id_genitore';
    $filter_record['operatore'] = '=';
    $filter_record['valore']    = $opzioni['id_genitore'];

    $filters[] = $filter_record;

    $filter_record              = [];
    $filter_record['chiave']    = 'genitore';
    $filter_record['operatore'] = '=';
    $filter_record['valore']    = $opzioni['genitore'];

    $filters[] = $filter_record;

    if($opzioni['estensioni'] !== false) {
      $operator = 'IN';
      if($opzioni['estensioni'] == 'img') {
        $opzioni['estensioni'] = '"jpg","jpeg","png","gif"';
      } elseif($opzioni['estensioni'] == 'notimg') {
        $opzioni['estensioni'] = '"jpg","jpeg","png","gif"';
        $operator              = 'NOT IN';
      }
      $filter_record              = [];
      $filter_record['chiave']    = 'estensione';
      $filter_record['operatore'] = $operator;
      $filter_record['valore']    = '('.$opzioni['estensioni'].')';

      $filters[] = $filter_record;
    }

    $options                  = [];
    $options['tableFilename'] = 'allegati';

    $allegati = new Dba($options);

    $opzioni['sortField'] = 'ordine';
    $opzioni['sortOrder'] = 'asc';
    $opzioni['filters']   = $filters;
    $opzioni['operatore'] = 'AND';

    $list = $allegati->getlist($opzioni);

    return $list;

  }

  function updateId($genitore, $md5, $id) {

    $sql = 'UPDATE allegati SET id_genitore ="'.$id.'" WHERE id_genitore = "'.$md5.'" AND genitore = "'.$genitore.'"';

    $this->doQuery($sql);

  }

  function save($opts = null) {

    $result = parent::save($opts);

    $this->generaNiceUrl();

    return $result;

  }

  function generaNiceUrl() {

    $cacheFile = DOC_ROOT.REL_ROOT.UPLOAD.'cache/rewrite.json';


    if(!file_exists($cacheFile)) {
      $urls = [];
      file_put_contents($cacheFile, json_encode($urls));
    }

    $urls = json_decode(file_get_contents($cacheFile), true);


    switch($this->genitore) {

      case 'news':

        $options                  = [];
        $options['tableFilename'] = 'news';

        $NewsObj = new \Ueppy\blog\News($options);

        $NewsObj = $NewsObj->getById($this->fields['id_genitore']);

        $urlNewsObjList = $NewsObj->getUrl();

        foreach($urlNewsObjList as $lingua => $urlNewsObj) {
          $baseUrl = explode('.', $urlNewsObj);
          $baseUrl = array_shift($baseUrl);
          if(isset($this->fields['fileData']['nomefile']['versioni'])) {
            foreach($this->fields['fileData']['nomefile']['versioni'] as $versione) {
              $basename       = basename($versione['path']);
              $basename       = explode('.', $basename);
              $ext            = array_pop($basename);
              $parts          = array_pop($basename);
              $parts          = explode('-', $parts);
              $numeroVersione = array_pop($parts);
              $nomefile       = explode('.', $this->fields['nomefile']);
              $nomefile       = array_shift($nomefile);
              $urlGenerato    = $nomefile.'-'.$this->fields['id'].'-'.$numeroVersione.'.'.$ext;
              $urlGenerato    = $baseUrl.'/'.$urlGenerato;

              $urls[str_replace(HOST.REL_ROOT, 'immagini/', $urlGenerato)] = $versione['rel_path'];
            }
          }
        }

        break;

      case 'pagine':

        $options                  = [];
        $options['tableFilename'] = 'pagine';

        $PaginaObj = new \Ueppy\core\Pagina($options);

        $PaginaObj = $PaginaObj->getById($this->fields['id_genitore']);

        $urlPaginaListObj = $PaginaObj->getUrl();

        foreach($urlPaginaListObj as $lingua => $urlPaginaObj) {
          $baseUrl = explode('.', $urlPaginaObj);
          $baseUrl = array_shift($baseUrl);
          if(isset($this->fields['fileData']['nomefile']['versioni'])) {
            foreach($this->fields['fileData']['nomefile']['versioni'] as $versione) {
              $basename       = basename($versione['path']);
              $basename       = explode('.', $basename);
              $ext            = array_pop($basename);
              $parts          = array_pop($basename);
              $parts          = explode('-', $parts);
              $numeroVersione = array_pop($parts);
              $nomefile       = explode('.', $this->fields['nomefile']);
              $nomefile       = array_shift($nomefile);
              $urlGenerato    = $nomefile.'-'.$this->fields['id'].'-'.$numeroVersione.'.'.$ext;
              $urlGenerato    = $baseUrl.'/'.$urlGenerato;

              $urls[str_replace(HOST.REL_ROOT, 'immagini/', $urlGenerato)] = $versione['rel_path'];
            }
          }
        }

        break;

      case 'prodotti':

        $options                  = [];
        $options['tableFilename'] = 'prodotti';

        $ProdottoObj = new \Ueppy\ecommerce\Prodotto($options);

        $ProdottoObj = $ProdottoObj->getById($this->fields['id_genitore']);

        $UrlProdottoObj = $ProdottoObj->getUrl();

        foreach($UrlProdottoObj as $lingua => $UrlProdottoObj) {
          $baseUrl = explode('.', $UrlProdottoObj);
          $baseUrl = array_shift($baseUrl);
          if(isset($this->fields['fileData']['nomefile']['versioni'])) {
            foreach($this->fields['fileData']['nomefile']['versioni'] as $versione) {
              $basename       = basename($versione['path']);
              $basename       = explode('.', $basename);
              $ext            = array_pop($basename);
              $parts          = array_pop($basename);
              $parts          = explode('-', $parts);
              $numeroVersione = array_pop($parts);
              $nomefile       = explode('.', $this->fields['nomefile']);
              $nomefile       = array_shift($nomefile);
              $urlGenerato    = $nomefile.'-'.$this->fields['id'].'-'.$numeroVersione.'.'.$ext;
              $urlGenerato    = $baseUrl.'/'.$urlGenerato;

              $urls[str_replace(HOST.REL_ROOT, 'immagini/', $urlGenerato)] = $versione['rel_path'];
            }
          }
        }

        break;

    }

    file_put_contents($cacheFile, json_encode($urls));

  }

  function rescanRewrite() {

    $genitori = [];

    if(defined('SET_NEWS_IMG_SIZE')) {
      $genitori['news'] = SET_NEWS_IMG_SIZE;
    }

    if(defined('SET_PRODOTTI_IMG_SIZE')) {
      $genitori['prodotti'] = SET_PRODOTTI_IMG_SIZE;
    }
    if(defined('SET_PAGINE_IMG_SIZE')) {
      $genitori['prodotti'] = SET_PAGINE_IMG_SIZE;
    }

    $urls = [];

    $cacheFile = DOC_ROOT.REL_ROOT.UPLOAD.'cache/rewrite.json';

    foreach($genitori as $genitore => $dimensioniImmagini) {

      $options                  = [];
      $options['tableFilename'] = 'allegati';
      $dimensioni               = explode('-', strtolower(str_replace('px', '', $dimensioniImmagini)));

      foreach($dimensioni as $k => $dimensione) {
        if(strpos($dimensione, '|') !== false) {
          @list($dimensione, $tipo, $typeofcrop) = explode('|', $dimensione);
        } else {
          if($k) {
            $tipo = 'thumbnail';
          } else {
            $tipo = 'crop';
          }
        }

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
        $options['imgSettings']['nomefile'][] = $imgSetting;
      }

      $AllegatiObj = new Allegati($options);

      $AllegatiObj->forceType = 'image';


      $filters = [];

      $filter_record              = [];
      $filter_record['chiave']    = 'genitore';
      $filter_record['operatore'] = '=';
      $filter_record['valore']    = $genitore;

      $filters[] = $filter_record;

      $filters = [];

      $filter_record              = [];
      $filter_record['chiave']    = 'estensione';
      $filter_record['operatore'] = '=';
      $filter_record['valore']    = ["jpg", "jpeg", "png", "gif"];

      $filters[] = $filter_record;

      $opzioni            = [];
      $opzioni['filters'] = $filters;


      $AllegatiObjList = $AllegatiObj->getlist($opzioni);

      foreach($AllegatiObjList as $AllegatiObj) {

        if($AllegatiObj->fields['fileData']['nomefile']['exists']) {

          $id       = $AllegatiObj->fields['id_genitore'];
          $genitore = $AllegatiObj->fields['genitore'];

          switch($genitore) {

            case 'news':
              $options                  = [];
              $options['tableFilename'] = 'news';

              $Obj = new News($options);
              break;
            case 'prodotti':
              $options                  = [];
              $options['tableFilename'] = 'prodotti';

              $Obj = new Prodotto($options);
              break;
            case 'pagine':
              $options                  = [];
              $options['tableFilename'] = 'pagine';

              $Obj = new Pagina($options);
              break;
          }

          $Obj = $Obj->getById($id);

          if($Obj) {

            $urlObjList = $Obj->getUrl();

            foreach($urlObjList as $lingua => $urlOggetto) {
              if(in_array($lingua, $this->opts['langs'])) {

                $baseUrl = explode('.', $urlOggetto);
                array_pop($baseUrl);
                $baseUrl = implode('.', $baseUrl);
                if(isset($AllegatiObj->fields['fileData']['nomefile']['versioni'])) {
                  foreach($AllegatiObj->fields['fileData']['nomefile']['versioni'] as $versione) {
                    $basename       = basename($versione['path']);
                    $basename       = explode('.', $basename);
                    $ext            = array_pop($basename);
                    $parts          = array_pop($basename);
                    $parts          = explode('-', $parts);
                    $numeroVersione = array_pop($parts);
                    $nomefile       = explode('.', $AllegatiObj->fields['nomefile']);
                    $nomefile       = array_shift($nomefile);
                    $urlGenerato    = $nomefile.'-'.$AllegatiObj->fields['id'].'-'.$numeroVersione.'.'.$ext;
                    $urlGenerato    = $baseUrl.'/'.$urlGenerato;

                    $urls[str_replace(HOST.REL_ROOT, '/immagini/', $urlGenerato)] = $versione['rel_path'];
                  }
                }
              }
            }
          }
        }
      }
    }

    file_put_contents($cacheFile, json_encode($urls));

  }

}