<?php
/***************/
/** v.1.01    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.01 (08/07/2016, 11.34)                                                                   **/
/** - Fix nel caso sia stata aggiunta una lingua dopo la prima configurazione del modulo         **/
/** v.1.00 (08/06/16, 10.04)                                                                     **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
Namespace Ueppy\core;

use Ueppy\core\Dba;
use Ueppy\utils\Utility;
use Ueppy\core\ModuloDinamico;

class Module extends Dba {

  private $disponibili;
  private $usati;

  /*
  function __construct($opts = null) {
    parent::__construct($opts);
  }
  */

  /*
  function fillresults() {

    $list = parent::fillresults();

    if(!$list) {
      if($this->getById) {
        return false;
      } else {
        return [];
      }
    }

    if($this->getById) {
      $list = array($list);
    }

    $arr = array();

    foreach($list as $obj) {

      $arr[] = $obj;

    }

    if($this->getById) {
      return $arr[0];
    } else {
      return $arr;
    }

  }
  */

  /*
  function save($opts = null) {
    return parent::save($opts);
  }
  */

  function isPrincipale($template) {

    // sezioni header e footer
    $tpls = [];
    $tpls = array_merge($tpls, glob(DEFAULT_TPL.'*.tpl'));
    $tpls = array_merge($tpls, glob(SECTIONS_DIR.'*.tpl'));

    $found = false;

    foreach($tpls as $tpl) {
      if(Utility::withoutExtension(basename($tpl)) == $_POST['template']) {
        $found = $tpl;
        break;
      }
    }

    if($found) {

      $templateMarkup = file_get_contents($found);

      $re  = '/<.*data-upy.*>/';
      $re2 = '/data-([^\=]+)\=\"([^\"]+)\"/';

      $blocchi = [];

      if(preg_match_all($re, $templateMarkup, $m)) {
        foreach($m[0] as $divTrovato) {
          if(preg_match_all($re2, $divTrovato, $m2)) {
            if($m2[2][1] == 'main') return $m2[2][0];
          }
        }
      }
    }

    return false;
  }

  function getUsati($template) {

    $isPrincipale = $this->isPrincipale($template);

    $filters = [];

    $filter_record              = [];
    $filter_record['chiave']    = 'template';
    $filter_record['operatore'] = '=';
    $filter_record['valore']    = $template;

    $filters[] = $filter_record;

    $opzioni              = [];
    $opzioni['filters']   = $filters;
    $opzioni['sortField'] = 'posizione ASC, ordine';
    $opzioni['sortOrder'] = 'ASC';
    $opzioni['raw']       = 1;

    $rawData = $this->getlist($opzioni);

    $usati = [];

    $principaleFound = false;

    foreach($rawData as $modulo) {

      $usato                     = [];
      $usato['nome']             = $modulo['modulo'];
      $usato['main']             = $modulo['modulo'] == 'main';
      $usato['db']               = [];
      $usato['db']['id']         = $modulo['id'];
      $usato['db']['view']       = $modulo['view'];
      $usato['db']['principale'] = $modulo['principale'];
      $usato['db']['istanza']    = $modulo['istanza'];
      $usato['db']['posizione']  = $modulo['posizione'];
      $usato['db']['ordine']     = $modulo['ordine'];

      if($usato['nome'] == 'main') {

        $usato['info']                = [];
        $usato['info']['nome']        = 'Modulo Principale';
        $usato['info']['author']      = '';
        $usato['info']['duplicabile'] = 0;
        $usato['info']['principale']  = 1;
        $usato['info']['info']        = 'Il blocco principale della pagina';

        $usato['viste']           = false;
        $usato['disableSettings'] = false;

      } elseif(strpos($usato['nome'], '[DYN]') === 0) {

        $usato['viste'] = false;
        $usato['dyn']   = $modulo['view'];

        $info['nome']        = 'Modulo dinamico: '.str_replace('[DYN]', '', $usato['nome']);
        $info['info']        = 'Modulo dimamico creato tramite la gestione Widget';
        $info['author']      = 'Giovanni Battista Lenoci - gianiaz@gmail.com';
        $info['principale']  = 0;
        $info['duplicabile'] = 1;

        $usato['info']            = $info;
        $usato['disableSettings'] = true;


      } else {

        $usato['info'] = $this->disponibili[$usato['nome']]['info'];
        if(!$usato['info']['duplicabile']) {
          unset($this->disponibili[$usato['nome']]);
        }

        $usato['viste'] = [];

        $visteFiles = glob(DOC_ROOT.REL_ROOT.MODULES.$usato['nome'].'/*.tpl');

        if($visteFiles) {
          foreach($visteFiles as $vista) {
            $vista    = Utility::withoutExtension(basename($vista));
            $selected = false;
            if($vista == $usato['db']['view']) {
              $selected = true;
            }
            $usato['viste'][] = ['label' => $vista, 'selected' => $selected];
          }
        } else {
          $usato['viste'][] = '-';
        }
      }

      if(!isset($usato['settings'])) {

        $usato['disableSettings'] = !$this->isConfigurabile($usato['nome']);

      }

      if($isPrincipale && $usato['info']['principale']) {
        $principaleFound = true;
      }

      $usati[] = $usato;
    }

    if(!$principaleFound) {

      $usato                     = [];
      $usato['nome']             = 'main';
      $usato['main']             = true;
      $usato['db']               = [];
      $usato['db']['id']         = 0;
      $usato['db']['view']       = '';
      $usato['db']['principale'] = 1;
      $usato['db']['istanza']    = 0;
      $usato['db']['posizione']  = $isPrincipale;
      $usato['db']['ordine']     = 0;

      $usato['info']                = [];
      $usato['info']['nome']        = 'Modulo Principale';
      $usato['info']['author']      = '';
      $usato['info']['duplicabile'] = 0;
      $usato['info']['info']        = 'Il blocco principale della pagina';

      $usato['viste'] = false;

      $usati[] = $usato;

    }

    $this->usati = $usati;

  }

  function isConfigurabile($nomeModulo) {

    $confFile = DOC_ROOT.REL_ROOT.MODULES.$nomeModulo.'/config.json';

    if(file_exists($confFile)) {

      $data = json_decode(file_get_contents($confFile), true);

      if($data) {
        return (count($data['generali']) || count($data['multilingua']));
      }

    }

    return false;

  }

  function createModule($moduleName) {

    $usato = [];

    if(strpos($_POST['nome'], '[DYN]') === 0) {

      $usato['viste'] = false;

      $usato['nome']             = $moduleName;
      $usato['disableSettings']  = true;
      $usato['db']               = [];
      $usato['db']['id']         = 0;
      $usato['db']['view']       = '';
      $usato['db']['principale'] = 0;
      $usato['db']['istanza']    = 0;
      $usato['db']['posizione']  = 'SETTATO_IN_JS';
      $usato['db']['ordine']     = '';

      $usato['info']['nome']        = 'Modulo dinamico: '.str_replace('[DYN]', '', $usato['nome']);
      $usato['info']['info']        = 'Modulo dimamico creato tramite la gestione Widget';
      $usato['info']['author']      = 'Giovanni Battista Lenoci - gianiaz@gmail.com';
      $usato['info']['duplicabile'] = 1;
      $usato['dyn']                 = '-';
      $usato['viste']               = false;
      $usato['disableSettings']     = true;
    } else {

      $usato['viste'] = [];

      $visteFiles = glob(DOC_ROOT.REL_ROOT.MODULES.$moduleName.'/*.tpl');

      $usato          = [];
      $usato['viste'] = [];

      if($visteFiles) {
        foreach($visteFiles as $vista) {
          $vista            = Utility::withoutExtension(basename($vista));
          $usato['viste'][] = ['label' => $vista, 'selected' => false];
        }
      } else {
        $usato['viste'][] = '-';
      }

      $usato['nome']             = $moduleName;
      $usato['db']               = [];
      $usato['db']['id']         = 0;
      $usato['db']['view']       = false;
      $usato['db']['principale'] = 0;
      $usato['db']['istanza']    = $this->generaIstanza($moduleName);
      $usato['db']['posizione']  = 'SETTATO_IN_JS';
      $usato['db']['ordine']     = '';

      $usato['info'] = $this->getInfo($moduleName);

      // QUI FIXME
      $usato['disableSettings'] = !$usato['info']['hasSettings'];


    }


    return $usato;
  }


  function getDisponibili($escludi = '') {

    $DIR = DOC_ROOT.REL_ROOT.MODULES;

    $moduli = glob($DIR.'*', GLOB_ONLYDIR);

    $elenco = [];

    foreach($moduli as $directory) {

      $modulo         = [];
      $modulo['nome'] = basename($directory);
      $modulo['info'] = $this->getInfo($modulo['nome']);
      $modulo['dyn']  = false;


      $elenco[$modulo['nome']] = $modulo;

    }

    // estraggo i moduli dinamici
    $options                  = [];
    $options['tableFilename'] = 'moduli_dinamici';

    $ModuloDinamicoObj = new ModuloDinamico($options);

    $opzioni        = [];
    $opzioni['raw'] = 1;

    $list = $ModuloDinamicoObj->getlist($opzioni);

    foreach($list as $record) {

      $modulo         = [];
      $modulo['nome'] = '[DYN]'.$record['nome'];
      $modulo['dyn']  = $record['id'];

      $info['nome']        = 'Modulo dinamico: '.$record['nome'];
      $info['info']        = 'Modulo dimamico creato tramite la gestione Widget';
      $info['author']      = 'Giovanni Battista Lenoci - gianiaz@gmail.com';
      $info['duplicabile'] = 1;

      $modulo['info'] = $info;

      $elenco[$modulo['nome']] = $modulo;

    }


    $this->disponibili = $elenco;

    if($escludi) {
      $this->getUsati($escludi);
    }

    return ['disponibili' => $this->disponibili, 'usati' => $this->usati];

  }

  function getInfo($nomeModulo) {

    $info                = [];
    $info['nome']        = $nomeModulo;
    $info['info']        = 'Descrizione non disponibile.';
    $info['author']      = 'Giovanni Battista Lenoci - gianiaz@gmail.com';
    $info['principale']  = 0;
    $info['dyn']         = false;
    $info['duplicabile'] = 0;
    $info['hasSettings'] = 0;

    $confFile = DOC_ROOT.REL_ROOT.MODULES.$nomeModulo.'/config.json';
    if(file_exists($confFile)) {

      $data = json_decode(file_get_contents($confFile), true);

      $info['hasSettings'] = $data['generali'] || $data['multilingua'];

      $info = array_replace_recursive($info, $data['meta']);

    }

    return $info;
  }

  function generaIstanza($moduleName) {

    $sql = 'SELECT MAX(istanza) from modules WHERE modulo = "'.$this->realEscape($moduleName).'"';

    $res = $this->doQuery($sql);

    if(mysqli_num_rows($res)) {

      $row = mysqli_fetch_row($res);

      return $row[0] + 1;

    } else {

      return 0;

    }


  }

  function deleteBlocco($posizione, $template) {

    $sql = 'DELETE FROM modules WHERE posizione = "'.$this->realEscape($posizione).'" AND template = "'.$this->realEscape($template).'"';

    $this->doQuery($sql);

  }

  function getCandidatiCopia($istanza) {

    list($nome, $numeroIstanza) = explode('-', $istanza);

    $filters = [];

    $filter_record              = [];
    $filter_record['chiave']    = 'modulo';
    $filter_record['operatore'] = '=';
    $filter_record['valore']    = $nome;

    $filters[] = $filter_record;

    $filter_record              = [];
    $filter_record['chiave']    = 'istanza';
    $filter_record['operatore'] = '!=';
    $filter_record['valore']    = $numeroIstanza;

    $filters[] = $filter_record;

    $opzioni            = [];
    $opzioni['filters'] = $filters;
    $opzioni['raw']     = 1;

    $moduli = $this->getlist($opzioni);

    $possibiliValori = [];

    foreach($moduli as $modulo) {
      $istanza  = $modulo['modulo'].'-'.$modulo['istanza'];
      $confFile = DOC_ROOT.REL_ROOT.UPLOAD.'widgets/'.$istanza.'.json';
      if(file_exists($confFile)) {
        $record            = [];
        $record['value']   = $istanza;
        $record['label']   = $modulo['posizione'].' - '.$modulo['template'];
        $possibiliValori[] = $record;
      }
    }

    if($possibiliValori) {
      $possibiliValori = array_merge([['value' => '0', 'label' => 'Seleziona configurazione']], $possibiliValori);
    }

    if(!$possibiliValori) {
      return false;
    }

    return $possibiliValori;
  }

  function loadWidgetConfig($istanza) {

    $data = $this->loadWidgetDefault($istanza);

    $confFile = DOC_ROOT.REL_ROOT.UPLOAD.'widgets/'.$istanza.'.json';

    if(isset($data['multilingua']) && count($data['multilingua'])) {
      foreach($data['multilingua'] as $k => $item) {
        $default = $item['default'];
        foreach($this->opts['langs'] as $l) {
          $data['multilingua'][$k]['default'][$l] = $default;
        }
      }
    }

    if(file_exists($confFile)) {

      $config = json_decode(file_get_contents($confFile), true);
      foreach($data['generali'] as $k => $item) {
        if(isset($config[$item['var']])) {
          $data['generali'][$k]['default'] = $config[$item['var']];
        }
      }

      if(isset($data['multilingua']) && count($data['multilingua'])) {
        foreach($data['multilingua'] as $k => $item) {
          foreach($this->opts['langs'] as $l) {
            if(isset($config[$l])) {
              $data['multilingua'][$k]['default'][$l] = $config[$l][$item['var']];
            } else {
              $data['multilingua'][$k]['default'][$l] = '';
            }
          }
        }
      }

    }

    $data['copia'] = $this->getCandidatiCopia($istanza);

    return $data;

  }

  function loadWidgetDefault($istanza) {

    $widgetDir = explode('-', $istanza);
    $widgetDir = array_shift($widgetDir);

    $percorso = DOC_ROOT.REL_ROOT.MODULES.$widgetDir.'/config.json';

    if(file_exists($percorso)) {

      $default = file_get_contents($percorso);

      $default = json_decode($default, true);

      return $default;

    }

    return false;

  }

}