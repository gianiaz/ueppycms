<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (08/06/16, 9.11)                                                                     **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
namespace Ueppy\front;

use DebugBar\StandardDebugBar;
use Ueppy\core\Menu;
use Ueppy\core\Module;
use Ueppy\core\ModuloDinamico;
use Ueppy\core\Route;
use Ueppy\ecommerce\Carrello;
use Ueppy\ecommerce\Cliente;
use Ueppy\ecommerce\Listino;
use Ueppy\ecommerce\Preventivo;
use Ueppy\ecommerce\WishList;
use Ueppy\utils\Utility;
use Ueppy\core\Operatore;
use Ueppy\core\Ueppy;
use Ueppy\core\Traduzioni;

class MainController {

  public $route;

  protected $out;

  private   $sectionFile = 'default.tpl';
  private   $footerFile  = false;
  private   $headerFile  = false;
  private   $dynCache    = [];
  protected $meta        = false;
  protected $operator    = false;

  private $blocchi = [];

  public $CONFIG   = false;
  public $DEBUGBAR = false;

  private $modules = [];

  protected $MenuObj = false;
  private   $mainObj = false;
  protected $carrello;
  protected $preventivo;

  protected $ClienteObj = false;

  function __construct(Route $route) {

    $this->route = $route;

    $this->out           = [];
    $this->out['GLOBAL'] = [];

    $this->setGlobal('act', $this->route->act);

    $this->loadIubenda();
    $this->loadMeta();

    $this->getConfig();
    $this->loadOperator();
    if(SET_ENABLE_ECOMMERCE) {
      $this->loadCliente();
      $this->loadCarrello();
      $this->loadPreventivo();
    }

  }


  function loadTemplate() {

    $options                  = [];
    $options['tableFilename'] = 'menu';
    $this->MenuObj            = new Menu($options);

    if($this->route->cmd != 'pagina') {

      $opts             = [];
      $opts['name']     = $this->route->cmd;
      $opts['minLevel'] = 99;
      $opts['debug']    = 0;

      $this->MenuObj = $this->MenuObj->getByName($opts);

      if($this->MenuObj && $this->MenuObj->fields['fileData']['img0'][ACTUAL_LANGUAGE]['exists']) {
        $this->out['GLOBAL']['IMG0']['path']  = $this->MenuObj->fields['fileData']['img0'][ACTUAL_LANGUAGE]['versioni'][0]['rel_path'];
        $this->out['GLOBAL']['IMG0']['alt']   = $this->MenuObj->fields[ACTUAL_LANGUAGE]['img0_alt'];
        $this->out['GLOBAL']['IMG0']['title'] = $this->MenuObj->fields[ACTUAL_LANGUAGE]['img0_title'];
      }

    } else {


      $lev = '';
      if($this->route->getParam('genitore')) {
        $lev = $this->route->getParam('url');
        $lev = rtrim($lev, '/');
        $lev = explode('/', $lev);
        array_pop($lev);
        $lev = implode('|', $lev);
      }
      $opts          = [];
      $opts['href']  = $this->route->getParam('href');
      $opts['path']  = $lev;
      $opts['debug'] = 0;

      $this->MenuObj = $this->MenuObj->getByHref($opts);

    }

    if($this->MenuObj) {
      $this->setGlobal('PAGE_TITLE', $this->MenuObj->fields[ACTUAL_LANGUAGE]['dicitura']);
      $this->setGlobal('TITLE', $this->MenuObj->fields[ACTUAL_LANGUAGE]['htmltitle']);
      $this->setGlobal('DESCRIPTION', $this->MenuObj->fields[ACTUAL_LANGUAGE]['description']);
      $this->sectionFile = $this->MenuObj->fields['template'].'.tpl';
    }

    $this->loadMainModule();
    $this->loadSecondaryTemplate();
    $this->getModules();
    $this->loadModules();
  }

  private function getConfig() {

    $CONFIG['header']              = [];
    $CONFIG['header']['file']      = DEFAULT_TPL.'header.tpl';
    $CONFIG['header']['show']      = true;
    $CONFIG['header']['eccezioni'] = [];

    $CONFIG['footer']['file']      = DEFAULT_TPL.'footer.tpl';
    $CONFIG['footer']['show']      = true;
    $CONFIG['footer']['eccezioni'] = [];

    if(isset($GLOBALS['CONFIG'])) {
      $CONFIG = array_replace_recursive($CONFIG, $GLOBALS['CONFIG']);
    }

    $this->CONFIG = $CONFIG;

  }

  private function loadSecondaryTemplate() {

    $headerFile     = false;
    $footerFile     = false;
    $this->template = [];
    if($this->CONFIG['header']['show']) {
      $headerFile = $this->CONFIG['header']['file'];
      if(isset($this->CONFIG['header']['eccezioni'][$this->route->cmd])) {
        $headerFile = $this->CONFIG['header']['eccezioni'][$this->route->cmd];
      }
    }
    if($this->CONFIG['footer']['show']) {
      $footerFile = $this->CONFIG['footer']['file'];
      if(isset($this->CONFIG['footer']['eccezioni'][$this->route->cmd])) {
        $footerFile = $this->CONFIG['footer']['eccezioni'][$this->route->cmd];
      }
    }


    if($headerFile) {
      $this->headerFile = $headerFile;
      $this->template[] = $this->headerFile;
    }
    if($headerFile) {
      $this->footerFile = $footerFile;
      $this->template[] = $this->footerFile;
    }

  }

  private function identificaBlocchi() {

    $templateMarkup = file_get_contents($this->getSectionFile());

    $re  = '/<.*data-upy.*>/';
    $re2 = '/data-([^\=]+)\=\"([^\"]+)\"/';

    $this->blocchi['principale'] = false;
    $this->blocchi['secondari']  = [];

    if(preg_match_all($re, $templateMarkup, $m)) {
      foreach($m[0] as $divTrovato) {
        if(preg_match_all($re2, $divTrovato, $m2)) {
          if($m2[2][1] == 'main') {
            $this->blocchi['principale'] = $m2[2][0];
          } else {
            $this->blocchi['secondari'][] = $m2[2][0];
          }
        }
      }
    }

    foreach($this->blocchi['secondari'] as $blocco) {
      $this->out['widgets'][$blocco] = [];
    }

  }

  function getModules() {

    $options                  = [];
    $options['tableFilename'] = 'modules';

    $ModuleObj = new Module($options);

    $filterTemplate   = [];
    $filterTemplate[] = $this->sectionFile;
    foreach($this->template as $templateFile) {
      $filterTemplate[] = basename($templateFile);
    }

    $filters = [];

    $filter_record              = [];
    $filter_record['chiave']    = 'template';
    $filter_record['operatore'] = '=';
    $filter_record['valore']    = $filterTemplate;

    $filters[] = $filter_record;

    $opzioni              = [];
    $opzioni['filters']   = $filters;
    $opzioni['sortField'] = 'ordine';
    $opzioni['sortOrder'] = 'ASC';
    $opzioni['raw']       = 1;
    $opzioni['debug']     = 0;

    $Modulelist = $ModuleObj->getlist($opzioni);

    $this->modules = $Modulelist;

  }

  private function cacheDynamic() {

    $dyns = [];

    foreach($this->modules as $module) {
      if(is_numeric($module['view'])) {
        $dyns[] = $module['view'];
      }
    }

    if($dyns) {

      $options                  = [];
      $options['tableFilename'] = 'moduli_dinamici';

      $ModuloDinamico = new ModuloDinamico($options);

      $filters = [];

      $filter_record              = [];
      $filter_record['chiave']    = 'id';
      $filter_record['operatore'] = '=';
      $filter_record['valore']    = $dyns;

      $filters[] = $filter_record;

      $opzioni            = [];
      $opzioni['filters'] = $filters;
      $opzioni['raw']     = 1;

      $ModuliDinamici = $ModuloDinamico->getlist($opzioni);

      foreach($ModuliDinamici as $ModuloDinamico) {
        $this->dynCache[$ModuloDinamico['id']] = $ModuloDinamico['testo_'.ACTUAL_LANGUAGE];
      }

    }

  }

  function loadModules() {

    Ueppy::info('Main Controller: '.get_class($this).'::out'.ucfirst(strtolower($this->route->act)), false);

    $this->out['widgets'] = [];

    $this->cacheDynamic();

    foreach($this->modules as $module) {
      if($module['modulo'] != 'main') {

        if(is_numeric($module['view'])) {

          $posizione = $module['posizione'];
          $istanza   = $module['modulo'].'-'.$module['istanza'];

          $this->out['widgets'][$posizione][$istanza]           = [];
          $this->out['widgets'][$posizione][$istanza]['modulo'] = $module;
          $this->out['widgets'][$posizione][$istanza]['tpl']    = DOC_ROOT.REL_ROOT.MODULES.'dyn_/dyn_.tpl';
          $this->out['widgets'][$posizione][$istanza]['data']   = $this->dynCache[$module['view']];

        } else {

          // la classe del cms
          $className = '\Ueppy\widgets\\'.$module['modulo'].'\\'.ucfirst($module['modulo']);

          // file js
          $jsModulo = DOC_ROOT.REL_ROOT.MODULES.$module['modulo'].'/'.$module['view'];

          if(PRODUZIONE) {
            $jsModulo .= '.min';
          }
          $jsModulo .= '.js';

          if(file_exists($jsModulo)) {
            $this->addJS($jsModulo);
          }

          Ueppy::info('Classe chiamata: '.$className, false);

          //Utility::pre($className);

          $posizione = $module['posizione'];
          $istanza   = $module['modulo'].'-'.$module['istanza'];

          $this->out['widgets'][$posizione][$istanza]           = [];
          $this->out['widgets'][$posizione][$istanza]['modulo'] = $module;
          $this->out['widgets'][$posizione][$istanza]['tpl']    = DOC_ROOT.REL_ROOT.MODULES.$module['modulo'].'/'.$module['view'].'.tpl';
          if(class_exists($className)) {
            Ueppy::startTime($istanza, $className);
            $widget                                             = new $className($this->MenuObj, $module['istanza'], $this);
            $this->out['widgets'][$posizione][$istanza]['data'] = $widget->getOutput();
            Ueppy::endTime($istanza);
          } else {
            Ueppy::error('La classe '.$className.' non esiste');
//            Utility::pre('non esiste la classe '.$className);
          }
        }

      } else {

        $posizione = $module['posizione'];
        $istanza   = 'MAIN';

        $this->out['widgets'][$posizione][$istanza]['tpl']    = $this->getMainFile();
        $this->out['widgets'][$posizione][$istanza]['modulo'] = $module;
        $this->out['widgets'][$posizione][$istanza]['data']   = $this->out['Main']['SMARTY'];

        //Utility::pre($this->out['widgets'][$posizione][$istanza]['data']);

        if(isset($this->out['widgets'][$posizione][$istanza]['data']['messaggio'])) {
          $this->out['widgets'][$posizione][$istanza]['tpl'] = DOC_ROOT.REL_ROOT.STRUTTURA_PUB.'common/messaggio.tpl';
        } else {
          $this->addJS();
        }
      }
    }


    if(!isset($this->out['widgets'][$this->blocchi['principale']]['MAIN'])) {

      $posizione = $this->blocchi['principale'];
      $istanza   = 'MAIN';

      $this->out['widgets'][$posizione][$istanza]['tpl']    = $this->getMainFile();
      $this->out['widgets'][$posizione][$istanza]['modulo'] = false;
      $this->out['widgets'][$posizione][$istanza]['data']   = $this->out['Main']['SMARTY'];


      if(isset($this->out['widgets'][$posizione][$istanza]['data']['messaggio'])) {
        $this->out['widgets'][$posizione][$istanza]['tpl'] = DOC_ROOT.REL_ROOT.STRUTTURA_PUB.'common/messaggio.tpl';
      } else {
        $this->addJS();
      }


    }

  }

  function getOut() {

    return $this->out;
  }

  function getSectionFile() {

    $tpl = 'default';

    if($this->MenuObj) {
      $tpl = $this->MenuObj->fields['template'];
    }

    return SECTIONS_DIR.$tpl.'.tpl';

  }

  function addJS($jsFile = null) {

    if(!$jsFile) {
      $jsFile = DOC_ROOT.REL_ROOT.STRUTTURA_PUB.MAIN_MODULES.$this->route->cmd.'/'.$this->route->cmd.'.js';
    }

    if(file_exists($jsFile)) {
      $opts          = [];
      $opts['debug'] = 0;
      $opts['path']  = $jsFile;

      $GLOBALS['smarty']->addJS($opts);
    }

  }

  function addCSS($cssFile) {
    
    if(file_exists($cssFile)) {
      $opts          = [];
      $opts['debug'] = 0;
      $opts['path']  = $cssFile;

      $GLOBALS['smarty']->addCSS($opts);
    }

  }

  function removeCSS($cssFile) {

    if(file_exists($cssFile)) {
      $opts          = [];
      $opts['debug'] = 0;
      $opts['path']  = $cssFile;

      $GLOBALS['smarty']->removeCSS($opts);
    }

  }

  function removeJS($jsFile) {

    if(file_exists($jsFile)) {
      $opts          = [];
      $opts['debug'] = 0;
      $opts['path']  = $jsFile;

      $GLOBALS['smarty']->removeJS($opts);
    }

  }

  function getMainFile() {

    return DOC_ROOT.REL_ROOT.STRUTTURA_PUB.MAIN_MODULES.$this->route->cmd.'/'.$this->route->cmd.'.tpl';
  }

  function getHeaderFile() {

    return $this->headerFile;
  }

  function getFooterFile() {

    return $this->footerFile;
  }

  private function loadMainModule() {

    $this->identificaBlocchi();

    $action = 'Default';

    if($this->route->act) {
      $action = ucfirst($this->route->act);
    }

    $method = 'out'.$action;

    if(method_exists($this, $method)) {

      $out           = [];
      $out['SMARTY'] = false;
      $out['OBJ']    = false;
      $out['data']   = false;

      $this->out['Main'] = array_replace_recursive($out, $this->$method());

      if(isset($this->out['Main']['OBJ'])) {
        $this->mainObj = $this->out['Main']['OBJ'];
      } else {
        Utility::pre('Main OBJ NON SETTATO');
        die;
      }

    } else {
      Utility::pre('Manca il metodo '.$method.' nella classe '.get_class($this));
    }

  }

  function outDefault() {

    $out           = [];
    $out['SMARTY'] = 'Dati che userai in smarty';
    $out['OBJ']    = 'Oggetto principale';
    $out['data']   = 'Dati vari strutturati come vuoi';

    Utility::pre('Dovresti creare un metodo chiamato "outDefault" nella classe '.get_class($this).' che ritorna un array di questo genere: '."\n".print_r($out, true));

    die;

  }

  function setGlobal($varName, $varValue) {

    $this->out['GLOBAL'][$varName] = $varValue;
  }

  function getGlobal($varName = null) {

    $data = '"'.$varName.'" UNDEFINED';

    if($varName && is_string($varName)) {

      if(isset($this->out['GLOBAL'][$varName])) {
        $data = $this->out['GLOBAL'][$varName];
      }

    }

    return $data;

  }

  function loadOperator() {

    $this->operator                          = false;
    $this->out['GLOBAL']['SUPERADMIN']       = false;
    $this->out['GLOBAL']['GOD']              = false;
    $this->out['GLOBAL']['debugBarRenderer'] = false;

    if(isset($_COOKIE['upycms_user']) && $_COOKIE['upycms_user'] && isset($_COOKIE['upycms_pass']) && $_COOKIE['upycms_pass']) {
      $options                  = [];
      $options['tableFilename'] = 'operatori';
      $operator                 = new Operatore($options);
      $userPotenziale           = preg_replace('/\W/', '', $_COOKIE['upycms_user']);
      $operator                 = $operator->getByUsername($userPotenziale);
      if($operator && $operator->fields['passwd'] == $_COOKIE['upycms_pass']) {

        $_SESSION['LOG_INFO']['UID']    = $operator->fields['id'];
        $_SESSION['LOG_INFO']['LOGGED'] = 1;
      }
    }

    if(isset($_SESSION['LOG_INFO']) && isset($_SESSION['LOG_INFO']['UID']) && Utility::isPositiveInt($_SESSION['LOG_INFO']['UID'])) {

      $options                  = [];
      $options['tableFilename'] = 'operatori';
      $operator                 = new Operatore($options);

      $operator = $operator->getById($_SESSION['LOG_INFO']['UID']);

      if($operator) {

        $this->operator = $operator;

        if($operator->isSuperAdmin()) {

          $this->setGlobal('SUPERADMIN', true);

          if($operator->isGod()) {
            $this->setGlobal('GOD', true);
          }

          $this->DEBUGBAR = new StandardDebugBar();

          $collector = new \Ueppy\utils\UeppyDatabaseCollector($GLOBALS['smarty']);

          $this->DEBUGBAR->addCollector($collector);

          $DEBUGBARRenderer = $this->DEBUGBAR->getJavascriptRenderer();

          $this->out['GLOBAL']['debugBarRenderer'] = $DEBUGBARRenderer;

          $this->out['GLOBAL']['operator'] = $operator;

        }

      }

    }


  }

  function loadCliente() {

    if(!$this->ClienteObj) {

      $ClienteObj = false;

      if(isset($_SESSION['cliente']) && $_SESSION['cliente'] && isset($_SESSION['cliente']['logged']) && $_SESSION['cliente']['logged'] == 1) {
        $options                  = [];
        $options['tableFilename'] = 'clienti';
        $ClienteObj               = new Cliente($options);
        $ClienteObj               = $ClienteObj->getById($_SESSION['cliente']['id']);
      } elseif(isset($_COOKIE['upycms_email_cliente']) && Utility::validMail($_COOKIE['upycms_email_cliente'])) {

        $options                  = [];
        $options['tableFilename'] = 'clienti';
        $ClienteObj               = new Cliente($options);
        $ClienteObj               = $ClienteObj->getByMail($_COOKIE['upycms_email_cliente']);

        if($ClienteObj && $ClienteObj->fields['password'] == $_COOKIE['upycms_hash_cliente']) {
          $_SESSION['cliente']['logged'] = 1;
          $_SESSION['cliente']['id']     = $ClienteObj->fields['id'];
        } else {
          $ClienteObj = false;
        }

      } elseif(isset($_SESSION['cliente']) && isset($_SESSION['cliente']['id']) && Utility::isPositiveInt($_SESSION['cliente']['id'])) {

        // di qui ci devo passare solo se l'utente non è registrato ma guest
        $options                  = [];
        $options['tableFilename'] = 'clienti';

        $ClienteObj = new Cliente($options);
        $ClienteObj = $ClienteObj->getById($_SESSION['cliente']['id']);

        if($ClienteObj->registered) {
          $ClienteObj = false;
        }

      }

      $key = false;

      if($ClienteObj) {

        if($ClienteObj->registered) {
          $key = 'CLIENTE';
        } else {
          $key = 'GUEST';
        }

        $this->out['GLOBAL']['CLIENTE'] = [];
        $this->out['GLOBAL']['GUEST']   = [];

        $this->out['GLOBAL'][$key]['id']             = $ClienteObj->id;
        $this->out['GLOBAL'][$key]['registered']     = $ClienteObj->registered;
        $this->out['GLOBAL'][$key]['nome']           = $ClienteObj->nome;
        $this->out['GLOBAL'][$key]['cognome']        = $ClienteObj->cognome;
        $this->out['GLOBAL'][$key]['nazione']        = $ClienteObj->nazione;
        $this->out['GLOBAL'][$key]['indirizzo']      = $ClienteObj->indirizzo;
        $this->out['GLOBAL'][$key]['provincia']      = $ClienteObj->provincia;
        $this->out['GLOBAL'][$key]['citta']          = $ClienteObj->citta;
        $this->out['GLOBAL'][$key]['cap']            = $ClienteObj->cap;
        $this->out['GLOBAL'][$key]['telefono']       = $ClienteObj->telefono;
        $this->out['GLOBAL'][$key]['cellulare']      = $ClienteObj->cellulare;
        $this->out['GLOBAL'][$key]['email']          = $ClienteObj->email;
        $this->out['GLOBAL'][$key]['societa']        = $ClienteObj->societa;
        $this->out['GLOBAL'][$key]['partita_iva']    = $ClienteObj->partita_iva;
        $this->out['GLOBAL'][$key]['codice_fiscale'] = $ClienteObj->codice_fiscale;

        if(!$ClienteObj->nome && !$ClienteObj->cognome) {
          $this->out['GLOBAL'][$key]['nome'] = Traduzioni::getLang('ecommerce', 'UTENTE');
        }

        $campiNecessari = ['nome', 'cognome', 'nazione', 'indirizzo', 'provincia', 'citta', 'cap', 'email'];
        if(SET_RICHIEDI_CODICE_FISCALE) {
          $campiNecessari[] = 'codice_fiscale';
        }

        $this->out['GLOBAL'][$key]['profilocompleto'] = 1;

        foreach($campiNecessari as $campo) {
          if(!$this->out['GLOBAL'][$key][$campo]) {
            $this->out['GLOBAL'][$key]['profilocompleto'] = 0;
            break;
          }
        }

        $this->out['GLOBAL'][$key]['hasIndirizzo'] = $ClienteObj->hasIndirizzo();

        $this->out['GLOBAL'][$key]['menu'] = $ClienteObj->getMenu();

        if($key == 'CLIENTE') {

          $options                  = [];
          $options['tableFilename'] = 'wishlist';

          $WishlistObj = new WishList($options);
          $Wishlist    = $WishlistObj->loadWishListUtente($ClienteObj->id);

          $this->out['GLOBAL']['CLIENTE']['wishlist'] = $Wishlist;
          $this->out['GLOBAL']['CLIENTE']['wish_ids'] = [];
          foreach($this->out['GLOBAL']['CLIENTE']['wishlist'] as $item) {
            $this->out['GLOBAL']['CLIENTE']['wish_ids'][] = $item['prodotti_id'];
          }

        }

        if($ClienteObj->listini_id) {
          $options                  = [];
          $options['tableFilename'] = 'listini';
          $l                        = new Listino($options);
          $l                        = $l->getById($ClienteObj->listini_id);
          if($l) {
            $ID_LISTINO = $ClienteObj->listini_id;
            define('LISTINO_SELEZIONATO', $ID_LISTINO);
          }
        }

        $this->setGlobal('CLIENTE_SESSIONE', $this->out['GLOBAL'][$key]);

        $this->ClienteObj = $ClienteObj;

      }

      if(!defined('LISTINO_SELEZIONATO')) {
        $options                  = [];
        $options['tableFilename'] = 'listini';

        $ListinoObj = new Listino($options);

        define('LISTINO_SELEZIONATO', $ListinoObj->getPredefinito());

      }


    }

  }

  function loadCarrello() {

    // CARRELLI IN DATABASE
    $options                  = [];
    $options['tableFilename'] = 'carrello';

    $CarrelloObj = new Carrello($options);
    $CarrelloObj->pulisciCarrelliVecchi();

    // CARRELLO UTENTE
    $opts               = [];
    $opts['session_id'] = session_id();

    $CARRELLO = $CarrelloObj->getBySessionId($opts);

    $this->carrello = $CARRELLO;

    $this->setGlobal('CARRELLO', $CARRELLO);

  }

  function loadPreventivo() {

    // PREVENTIVI IN DATABASE
    $options                  = [];
    $options['tableFilename'] = 'preventivo';

    $PreventivoObj = new Preventivo($options);
    $PreventivoObj->pulisciVecchi();

    // PREVENTIVO UTENTE
    $opts               = [];
    $opts['session_id'] = session_id();

    $PREVENTIVO = $PreventivoObj->getBySessionId($opts);

    $this->preventivo = $PREVENTIVO;

    $this->setGlobal('PREVENTIVO', $PREVENTIVO);

  }

  function getOperator() {

    if(isset($this->operator)) {
      return $this->operator;
    }

    return false;
  }

  function getCliente() {

    return $this->ClienteObj;
  }

  function ajaxResponse($out) {

    echo json_encode($out);
    die;
  }

  function getMainObj() {

    return $this->mainObj;

  }

  function getMainData() {

    if(!isset($this->out['Main']['data'])) {
      return [];
    }

    return $this->out['Main']['data'];

  }

  function loadIubenda() {

    // integrazione iubenda
    $re                = '/^([a-z]{2}\:([\d]+),?)+$/';
    $IUBENDA_POLICY_ID = false;
    if(defined('SET_IUBENDA_SITE_ID') && defined('SET_IUBENDA_POLICY_IDS') && SET_IUBENDA_POLICY_IDS && preg_match($re, SET_IUBENDA_POLICY_IDS, $m)) {

      $tuple = explode(',', SET_IUBENDA_POLICY_IDS);

      foreach($tuple as $tupla) {
        $tupla = explode(':', $tupla);
        if($tupla[0] == ACTUAL_LANGUAGE) {
          $IUBENDA_POLICY_ID = $tupla[1];
          break;
        }
      }

    }

    $this->setGlobal('IUBENDA_POLICY_ID', $IUBENDA_POLICY_ID);

  }

  function loadMeta() {

    $fileDati = DOC_ROOT.REL_ROOT.UPLOAD.'cache/meta-'.ACTUAL_LANGUAGE.'.json';

    if(file_exists($fileDati)) {
      $this->meta = json_decode(file_get_contents($fileDati), true);
    }

  }

  function setOpenGraph($image = null) {

    $openGraphData                = [];
    $openGraphData['title']       = $this->getGlobal('TITLE');
    $openGraphData['description'] = $this->getGlobal('DESCRIPTION');
    $openGraphData['url']         = HOST.$_SERVER['REQUEST_URI'];
    if($image) {
      $openGraphData['image'] = $image;
    }

    $this->setGlobal('openGraphData', $openGraphData);
  }


}

