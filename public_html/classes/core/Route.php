<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.01 (11/07/2016, 11.30)                                                                   **/
/** - Bugfix nel sanitize di un parametro di tipo url.                                           **/
/** - Aggiunta la possibilità di settare in un test group il nome dei parametri di cui non fare  **/
/**   il sanitize.                                                                               **/
/**                                                                                              **/
/** v.1.00 (03/04/16, 6.05)                                                                      **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
namespace Ueppy\core;

use Ueppy\core\LinkManager;
use Ueppy\utils\Utility;

class Route {

  public  $cmd;
  public  $act;
  public  $requestUri;
  private $routes;
  private $langs;
  private $lang;
  private $debug;
  private $defaultLang = 'it';
  private $captured    = [];
  private $params      = [];
  private $_GET        = '';

  private $pathParts;
  private $cmds       = [];
  private $defaultCmd = 'pagina';
  private $estensione = false;
  private $route      = false;
  public  $pathData   = [];
  private $errors     = [];
  public  $page       = 1;

  function __construct($langs, $debug = 0) {

    $this->langs = $langs;
    if(isset($_SERVER['REDIRECT_URL'])) {
      $this->requestUri = ltrim($_SERVER['REDIRECT_URL'], '/');
    }
    $this->debug = $debug;

    if($debug) {
      Utility::pre($this->requestUri);
    }

    $this->loadRules();
    $this->setLanguage();
    $this->process();

  }

  private function loadRules() {

    include(CONF_DIR.'routes.conf.php');

    foreach($routes['routes'] as $route) {
      $route['default'] = false;
      if($route['cmd'] == $routes['default']) {
        $route['default'] = true;
      }
      $this->routes[$route['cmd']] = $route;
    }

    if($this->debug) {
      Utility::pre($this->routes);
    }

  }

  private function setLanguage() {

    $langs = array_diff($this->langs, [$this->defaultLang]);

    foreach($langs as $l) {
      if(strpos($this->requestUri, $l) === 0) {
        $this->lang       = $l;
        $this->requestUri = substr($this->requestUri, 3);
        break;
      }
    }

    if(!$this->lang) {
      $this->lang = $this->defaultLang;
    }

    if($this->debug) {
      Utility::pre('lingua in uso: '.$this->lang);
    }

    define('ACTUAL_LANGUAGE', $this->lang);

  }

  private function process() {


    if(isset($_SERVER['REDIRECT_QUERY_STRING']) && $_SERVER['REDIRECT_QUERY_STRING']) {
      parse_str($_SERVER['REDIRECT_QUERY_STRING'], $this->_GET);
    }

    $debugString   = [];
    $debugString[] = 'Url da verificare: '.$this->requestUri;

    foreach($this->routes as $cmd => $routeData) {
      if(isset($routeData['testGroups'])) {
        $debugString[] = 'Sezione: '.$cmd;
        foreach($routeData['testGroups'] as $numeroRegex => $testGroup) {
          $debugString[] = 'Testo la regex '.$testGroup['regex'].' sull\'url '.$this->requestUri;
          if(preg_match($testGroup['regex'], $this->requestUri, $m)) {
            $debugString[]  = 'Regex numero '.$numeroRegex.' soddisfatta';
            $this->cmd      = $routeData['cmd'];
            $this->act      = $testGroup['act'];
            $this->captured = $m;
            $debugString[]  = 'Catturati: '."\n".print_r($m, true);
            if(isset($testGroup['params']) && $testGroup['params']) {
              foreach($testGroup['params'] as $k => $param) {
                if($param && isset($this->captured[$k])) {
                  $this->captured[$k] = urldecode($this->captured[$k]);
                  $value              = $this->captured[$k];
                  if(!in_array($this->cmd, ['ajax']) && !in_array($param, ['url']) && (!isset($testGroup['noSanitize']) || (isset($testGroup['noSanitize']) && !in_array($param, $testGroup['noSanitize'])))) {
                    $value = ($k) ? Utility::sanitize($this->captured[$k]) : $this->captured[$k];
                  }
                  if($param == 'act') {
                    $this->act = $value;
                  } else {
                    $this->params[$param] = $value;
                  }
                }
              }
              $debugString[] = 'Parametri: '."\n".print_r($this->params, true);
            }
            break(2);
          }
        }
      }
    }

    if(!$this->cmd) {
      $debugString[] = 'Nessuna regola soddisfatta, carico il defaul';
      foreach($this->routes as $cmd => $routeData) {
        if($routeData['default']) {
          $this->cmd = $cmd;
          break;
        }
      }
    }

    $debugString[] = 'CMD: '.$this->cmd;
    $debugString[] = 'ACT: '.$this->act;

    if($this->debug) {
      Utility::pre(implode("\n", $debugString));
      die;
    }

    // non esiste l'url pag1.html
    if(isset($this->params['pag']) && $this->params['pag'] == 1) {
      $this->redirectTo('notFound');
    }

  }

  function redirectTo($params = '', $opts = null) {

    $opzioni          = [];
    $opzioni['debug'] = false;

    if($opts) {
      $opzioni = array_replace_recursive($opzioni, $opts);
    }

    $lm = LinkManager::getInstance();

    if($opzioni['debug']) {
      Utility::pre($params);
    } else {
      if($params == 'notFound') {
        $params = 'cmd/notFound';
      }
      Header('Location:'.$lm->get($params));
      die;
    }

  }


  function getParam($param) {

    if(isset($this->params[$param])) {
      return $this->params[$param];
    }

    return false;

  }

  function getParams() {

    return $this->params;
  }

  function GET($param) {

    if(isset($this->_GET[$param])) {
      return $this->_GET[$param];
    }

    return false;

  }


}