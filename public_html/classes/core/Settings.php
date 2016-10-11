<?php
/***************/
/** v.1.02    **/
/***************/
/** CHANGELOG **/
/****************************************************************************************************/
/** v.1.02 (06/11/2015, 16.50)                                                                     **/
/** - Aggiunto namespace per autoloading                                                           **/
/**                                                                                                **/
/** v.1.01 (29/07/2013)                                                                            **/
/** - Bugfix, aggiunto escape per il file di cache nel caso nei valori sia presente il carattere $ **/
/**                                                                                                **/
/** v.1.00                                                                                         **/
/** - Versione stabile                                                                             **/
/**                                                                                                **/
/****************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                        **/
/** copyright: Ueppy s.r.l                                                                         **/
/****************************************************************************************************/
namespace Ueppy\core;

class Settings extends Dba {

  function __construct($opts = null) {

    $opts['upload_dir'] = DOC_ROOT.REL_ROOT.UPLOAD.$opts['tableFilename'];

    parent::__construct($opts);
  }

  function getByKey($key, $debug = 0) {

    $filters = [];

    $filter_record = [];

    $filter_record['chiave']    = 'chiave';
    $filter_record['valore']    = $key;
    $filter_record['operatore'] = '=';

    $filters[] = $filter_record;

    $opts              = [];
    $opts['start']     = 0;
    $opts['quanti']    = 1;
    $opts['debug']     = $debug;
    $opts['filters']   = $filters;
    $opts['operatore'] = 'AND';

    $list = $this->getlist($opts);

    if(is_array($list) && count($list)) {

      return $list[0];

    } else {

      return false;

    }


  }

  function generaCache() {

    $list         = $this->getlist();
    $PLG_SETTINGS = [];
    foreach($list as $settplug) {
      if(!isset($PLG_SETTINGS[$settplug->fields['gruppo']])) {
        $PLG_SETTINGS[$settplug->fields['gruppo']] = [];
      }
      $PLG_SETTINGS[$settplug->fields['gruppo']][$settplug->fields['chiave']] = $settplug->fields['valore'];
    }
    $fname = 'cache.ser';
    file_put_contents($this->opts['upload_dir'].'/'.$fname, serialize($PLG_SETTINGS));
  }

  function generaCostanti() {

    $list        = $this->getlist();
    $sett_smarty = [];
    $phpStr      = '<?php';
    $phpStr .= "\n";
    $smartySettings = [];
    foreach($list as $val) {
      $key = 'SET_';
      $phpStr .= 'define(\''.$key.strtoupper($val->fields['chiave']).'\',\''.addSlashes($val->fields['valore']).'\');';
      $phpStr .= "\n";
      $smartySettings[] = '$sett_smarty[\''.strtoupper($val->fields['chiave']).'\']= \''.addslashes($val->fields['valore']).'\';';
    }


    $phpStr .= '$sett_smarty = [];';
    $phpStr .= "\n";
    $phpStr .= implode("\n", $smartySettings);
    file_put_contents($this->opts['upload_dir'].'/settings.php', $phpStr);
  }

}

?>