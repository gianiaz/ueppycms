<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (14/04/16, 6.41)                                                                     **/
/**-Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
namespace Ueppy\core;

use Ueppy\utils\Utility;
use Ueppy\core\UeppySmarty;
use Ueppy\utils\Zip;

class Traduzioni extends Dba {

  static         $lang       = null;
  static         $sezione    = null;
  static         $langs      = null;
  public         $definition = [];
  private static $singleton  = null;

  static function getInstance($opts = null) {

    $opzioni                  = [];
    $opzioni['forceAllLang']  = true;
    $opzioni['tableFilename'] = 'traduzioni';
    $opzioni['sezione']       = 'public';

    if($opts) {
      $opzioni = array_replace_recursive($opzioni, $opts);
    }


    if(Traduzioni::$singleton == null) {
      Traduzioni::$singleton = new Traduzioni($opzioni);
    }

    return Traduzioni::$singleton;
  }

  function __construct($opts = null) {

    if(!$this->definition) {

      if(!defined('ACTUAL_LANGUAGE')) {
        $debug_array = debug_backtrace();
        $debug_array = array_reverse($debug_array);
        $string      = '';
        foreach($debug_array as $da) {
          $f = $da['file'];
          if(defined('DOC_ROOT')) {
            $f = str_replace(DOC_ROOT, '', $f);
          }
          $string .= $f.', '.$da['line'];
          $string .= "\n";
        }
        $string .= 'ACTUAL_LANGUAGE non definito';
        die($string);
      }

      $opzioni['langs']          = [];
      $opzioni['lang']           = ACTUAL_LANGUAGE;
      $opzioni['tableFilename']  = '';
      $opzioni['loadRules']      = false;
      $opzioni['forceAllLang']   = true;
      $opzioni['autoLoadLabels'] = false;
      $opzioni['acceptedExt']    = ['jpg', 'png', 'gif', 'jpeg', 'pdf'];
      $opzioni['logActions']     = true;
      $opzioni['moveFiles']      = true;
      $opzioni['imgSettings']    = false;
      $opzioni['upload_dir']     = false;
      $opzioni['tablesDir']      = false;
      $opzioni['restrDir']       = false;
      $opzioni['db']             = false;
      $opzioni['debug']          = false;
      $opzioni['sezione']        = 'admin';

      if($opts) {
        $opzioni = $this->array_replace_recursive($opzioni, $opts);
      }

      Traduzioni::$lang    = $opzioni['lang'];
      Traduzioni::$sezione = $opzioni['sezione'];

      parent::__construct($opzioni);

      Traduzioni::$langs = $this->opts['langs'];

      $this->loadPHP();
    }

  }

  static function loadJS(UeppySmarty $smarty) {

    $pathJS   = [];
    $pathJS[] = DOC_ROOT.REL_ROOT.UPLOAD.'languages/'.Traduzioni::$lang.'/locale-'.Traduzioni::$sezione.'-js.json';

    $opts         = [];
    $opts['path'] = $pathJS;

    $smarty->addJs($opts);

  }

  private function loadPHP() {

    $filename = DOC_ROOT.REL_ROOT.UPLOAD.'languages/'.Traduzioni::$lang.'/locale-'.Traduzioni::$sezione.'-php.json';

    $this->definition = json_decode(file_get_contents($filename), true);

  }

  function export($opts = null) {


    $opzioni          = [];
    $opzioni['dir']   = DOC_ROOT.REL_ROOT.UPLOAD.'languages/';
    $opzioni['db']    = null;
    $opzioni['langs'] = [];

    if($opts) {
      $opzioni = array_replace_recursive($opzioni, $opts);
    }

    if(!$opzioni['langs']) {
      $opzioni['langs'] = explode(',', SET_LANGUAGES);
    }

    $opts          = [];
    $opts['langs'] = $opzioni['langs'];
    $opts['db']    = $opzioni['db'];

    $data = $this->extract($opts);

    foreach($opzioni['langs'] as $l) {

      if(!is_dir($opzioni['dir'].$l)) {
        Utility::mkdirp($opzioni['dir'].$l);
      }

      file_put_contents($opzioni['dir'].$l.'/locale-admin-php.json', json_encode($data[$l]['admin']['php']));
      file_put_contents($opzioni['dir'].$l.'/locale-admin-js.json', 'var Lang='.json_encode($data[$l]['admin']['javascript']));
      file_put_contents($opzioni['dir'].$l.'/locale-public-php.json', json_encode($data[$l]['public']['php']));
      file_put_contents($opzioni['dir'].$l.'/locale-public-js.json', 'var Lang='.json_encode($data[$l]['public']['javascript']));

    }

  }

  function exportGlobal($opts = null) {

    $opzioni                   = [];
    $opzioni['avanzaVersione'] = true;
    $opzioni['path']           = DOC_ROOT.REL_ROOT.UPLOAD.'traduzioni/';

    if($opts) {
      $opzioni = $this->array_replace_recursive($opzioni, $opts);
    }

    $file        = $opzioni['path'].'traduzioni.json';
    $versionFile = $opzioni['path'].'traduzioni.version';

    if(!is_dir($opzioni['path'])) {
      Utility::mkdirp($opzioni['path']);
    }

    $version = 1;

    $opzioniGetList                 = [];
    $opzioniGetList['filters']      = [];
    $opzioniGetList['forceAllLang'] = true;
    $opzioniGetList['debug']        = 0;
    $opzioniGetList['raw']          = 1;

    $data = $this->getlist($opzioniGetList);

    $newData         = [];
    $newData['data'] = $data;

    if(file_exists($versionFile)) {
      $version = trim(file_get_contents($versionFile));
      if($opzioni['avanzaVersione']) {
        $version += 0.01;
      }
    }
    file_put_contents($versionFile, number_format($version, 2, '.', ''));

    $version            = number_format($version, 2, '.', '');
    $newData['version'] = $version;

    file_put_contents($file, json_encode($newData));

    $zipFileName = $opzioni['path'].'traduzioni.'.$version.'.zip';

    // ora zippo il tutto
    $zip = new Zip();
    $zip->open($zipFileName, \ZIPARCHIVE::CREATE);
    $zip->addEntryToZip($file, $opzioni['path']);
    $zip->close();

    unlink($file);

    return true;

  }

  function extract($opts = null) {

    $opzioni          = [];
    $opzioni['langs'] = [];
    $opzioni['db']    = null;
    $opzioni['debug'] = 0;

    if($opts) {
      $opzioni = array_replace_recursive($opzioni, $opts);
    }

    if(!$opzioni['langs']) {
      $opzioni['langs'] = explode(',', SET_LANGUAGES);
    }

    $mainObjOptions                  = [];
    $mainObjOptions['tableFilename'] = 'traduzioni';
    $mainObjOptions['debug']         = 0;
    $mainObjOptions['forceAllLang']  = 1;
    $mainObjOptions['logActions']    = false;
    $mainObjOptions['langs']         = $opzioni['langs'];

    if($opzioni['db']) {
      $mainObjOptions['db'] = $opzioni['db'];
    }

    $traduzioniUeppyOptions                  = [];
    $traduzioniUeppyOptions['tableFilename'] = 'traduzioni_ueppy';
    $traduzioniUeppyOptions['debug']         = 0;
    $traduzioniUeppyOptions['forceAllLang']  = 1;
    $traduzioniUeppyOptions['logActions']    = false;
    $traduzioniUeppyOptions['langs']         = $opzioni['langs'];

    if($opzioni['db']) {
      $traduzioniUeppyOptions['db'] = $opzioni['db'];
    }

    $TraduzioniObj = new Traduzioni($mainObjOptions);

    $opzioniGetList                 = [];
    $opzioniGetList['forceAllLang'] = true;
    $opzioniGetList['raw']          = 1;
    $opzioniGetList['debug']        = 0;

    $raw = $TraduzioniObj->getlist($opzioniGetList);

    $data                         = [];
    $data['admin']                = [];
    $data['admin']['javascript']  = [];
    $data['admin']['php']         = [];
    $data['public']['javascript'] = [];
    $data['public']['php']        = [];

    $chiaviSito = [];

    foreach($opzioni['langs'] as $l) {
      $chiaviSito[$l] = $data;
    }

    foreach($raw as $record) {

      foreach($opzioni['langs'] as $l) {

        if(!isset($chiaviSito[$l][$record['sezione']][$record['linguaggio']][$record['modulo']])) {
          $chiaviSito[$l][$record['sezione']][$record['linguaggio']][$record['modulo']] = [];
        }

        $chiaviSito[$l][$record['sezione']][$record['linguaggio']][$record['modulo']][$record['chiave']] = $record['dicitura_'.$l];

      }

    }

    $TraduzioniUeppyObj = new Traduzioni($traduzioniUeppyOptions);

    $raw = $TraduzioniUeppyObj->getlist($opzioniGetList);

    $chiaviUeppy = [];

    foreach($opzioni['langs'] as $l) {
      $chiaviUeppy[$l] = $data;
    }

    foreach($raw as $record) {

      foreach($opzioni['langs'] as $l) {

        if(!isset($chiaviUeppy[$l][$record['sezione']][$record['linguaggio']][$record['modulo']])) {
          $chiaviUeppy[$l][$record['sezione']][$record['linguaggio']][$record['modulo']] = [];
        }

        $chiaviUeppy[$l][$record['sezione']][$record['linguaggio']][$record['modulo']][$record['chiave']] = $record['dicitura_'.$l];

      }

    }

    $return = array_replace_recursive($chiaviUeppy, $chiaviSito);

    return $return;

  }

  static function getLang($module, $key, $opts = null) {

    global $TRADUZIONI_OPTION;

    $opzioni                   = [];
    $opzioni['nbsp']           = false;
    $opzioni['traduzione_ita'] = '';
    $opzioni['debug']          = 0;

    if($opts) {
      $opzioni = array_replace_recursive($opzioni, $opts);
    }

    $traduzioni = Traduzioni::getInstance($TRADUZIONI_OPTION);


    $traduzione = '';
    $nonEsiste  = true;

    if($module && $key) {
      $traduzione = $module.'.'.$key;
      if($opzioni['debug']) {
        Utility::pre('Modulo: '.$module."\nChiave: ".$key);
      }
      if(isset($traduzioni->definition[$module][$key])) {
        if($opzioni['debug']) {
          Utility::pre($traduzioni);
          Utility::pre('La chiave non esiste');
        }
        $traduzione = $traduzioni->definition[$module][$key];
      } else {

        if(defined('SOURCE') && SOURCE && 0) {

          $filters = [];

          $filter_record              = [];
          $filter_record['chiave']    = 'chiave';
          $filter_record['operatore'] = '=';
          $filter_record['valore']    = $key;
          $filters[]                  = $filter_record;

          $filter_record              = [];
          $filter_record['chiave']    = 'modulo';
          $filter_record['operatore'] = '=';
          $filter_record['valore']    = $module;
          $filters[]                  = $filter_record;

          $filter_record              = [];
          $filter_record['chiave']    = 'linguaggio';
          $filter_record['operatore'] = '=';
          $filter_record['valore']    = 'php';
          $filters[]                  = $filter_record;

          $filter_record              = [];
          $filter_record['chiave']    = 'sezione';
          $filter_record['operatore'] = '=';
          $filter_record['valore']    = Traduzioni::$sezione;
          $filters[]                  = $filter_record;

          $Obj = Traduzioni::getInstance();

          $opts                 = [];
          $opts['forceAllLang'] = true;
          $opts['countOnly']    = true;
          $opts['filters']      = $filters;
          $opts['operatore']    = 'AND';
          $opts['start']        = 0;
          $opts['quanti']       = 1;
          $opts['debug']        = 0;

          $count = $Obj->getlist($opts);

          if(!$count) {

            $options                  = [];
            $options['tableFilename'] = 'traduzioni_ueppy';
            $options['forceAllLang']  = true;

            $Obj = new Traduzioni($options);

            $Obj->chiave     = $key;
            $Obj->modulo     = $module;
            $Obj->sezione    = $TRADUZIONI_OPTION['sezione'];
            $Obj->linguaggio = 'php';

            foreach(Traduzioni::$langs as $lang) {
              if($lang == 'it' && $opzioni['traduzione_ita']) {
                $Obj->$lang = ['dicitura' => $opzioni['traduzione_ita']];
              } else {
                $Obj->$lang = ['dicitura' => $module.'.'.$key];
              }
            }

            $Obj->save(['debug' => 0]);
          }

        }

      }

      if($opzioni['nbsp']) {
        $traduzione = str_replace(' ', ' & nbsp;', $traduzione);
      }

      return $traduzione;

    } else {
      Utility::pre('Chiamata errata("'.$module.'", "'.$key.'")');
      die;
    }

  }


  static function moduleExists($module) {

    $trad = Traduzioni::getInstance();

    return isset($trad->definition[$module]) && count($trad->definition[$module]);

  }

  function updateFromJson($debug = false) {

    $opzioni                   = [];
    $opzioni['path']           = DOC_ROOT.REL_ROOT.UPLOAD.'traduzioni/bck/';
    $opzioni['avanzaVersione'] = false;
    $opzioni['langs']          = $this->opts['langs'];

    $out = [];

    if(!is_dir($opzioni['path'])) {
      Utility::mkdirp($opzioni['path']);
    }

    $this->export($opzioni);

    $fileDaImportare = DOC_ROOT.REL_ROOT.UPLOAD.'traduzioni/traduzioni.json';
    $fileVersione    = DOC_ROOT.REL_ROOT.UPLOAD.'traduzioni/traduzioni.version';
    $data            = json_decode(file_get_contents($fileDaImportare), true);

    $sql = 'TRUNCATE table traduzioni_ueppy';

    $dbg = 'Eseguo query: '.$sql."\n";

    if($debug) {
      echo $dbg;
    } else {
      $out[] = $dbg;
      $this->doQuery($sql);
    }

    $sql = 'TRUNCATE table traduzioni_ueppy_langs';

    $dbg = 'Eseguo query: '.$sql."\n";

    if($debug) {
      echo $dbg;
    } else {
      $out[] = $dbg;
      $this->doQuery($sql);
    }

    $version = $data['version'];

    $sql     = 'REPLACE INTO `traduzioni_ueppy` (`id`, `chiave`, `modulo`, `linguaggio`, `sezione` , `created_at`, `updated_at`) VALUES ';
    $sqlLang = 'REPLACE INTO `traduzioni_ueppy_langs` (`id`, `traduzioni_ueppy_id`, `dicitura`, `lingua`) VALUES ';

    $righe     = [];
    $righeLang = [];

    foreach($data['data'] as $record) {
      $righe[] = '("'.$this->realEscape($record['id']).'","'.$this->realEscape($record['chiave']).'","'.
        $this->realEscape($record['modulo']).'","'.$this->realEscape($record['linguaggio']).'","'.
        $this->realEscape($record['sezione']).'","'.$this->realEscape($record['created_at']).'","'.$this->realEscape($record['updated_at']).'")';
      foreach($this->opts['langs'] as $l) {
        $righeLang[] = '("'.$this->realEscape($record['id_'.$l]).'","'.$this->realEscape($record['id']).'","'.$this->realEscape($record['dicitura_'.$l]).'","'.$l.'")';
      }
    }

    $sql .= implode(",\n", $righe);

    $dbg = 'Importo '.count($righe).' chavi di traduzione';
    if($debug) {
      echo $dbg;
    } else {

      $out[] = $dbg;

      $res = $this->doQuery($sql);

      $sqlLang .= implode(",\n", $righeLang);

      $this->doQuery($sqlLang);

      file_put_contents($fileVersione, $version);

    }

    $dir  = $fileDaImportare = DOC_ROOT.REL_ROOT.UPLOAD.'traduzioni/';
    $json = $dir.'traduzioni.json';

    if(file_exists($json)) {
      $out[] = 'Cancello il file '.$json;
      @unlink($json);
    }

    $zip = glob($dir.'*.zip');

    if(is_array($zip)) {
      foreach($zip as $zipFile) {
        $out[] = 'Cancello il file '.$zipFile;
        @unlink($zipFile);
      }
    }

    return implode("\n", $out);

  }


}