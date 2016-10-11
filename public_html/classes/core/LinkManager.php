<?php
/***************/
/** v.1.01    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.01 (14/05/2016)                                                                          **/
/** - Aggiunta la possibilità di estrarre un url di elenco nel caso la pagina abbia figli.       **/
/**                                                                                              **/
/** v.1.00 (14/06/16, 14.46)                                                                     **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
namespace Ueppy\core;

use Ueppy\utils\Utility;
use Ueppy\ecommerce\CategoriaProdotti;

class LinkManager {

  var $base;

  private $opzioni = false;

  public static function getInstance($opzioni = null) {

    static $instance = null;

    if(null === $instance) {
      $instance = new static($opzioni);
    }

    return $instance;
  }

  private function __construct($opts = null) {

    $opzioni         = [];
    $opzioni['page'] = 'index'; // index | admin
    $opzioni['host'] = HOST;
    $opzioni['root'] = REL_ROOT;
    $opzioni['lang'] = ACTUAL_LANGUAGE;

    if($opts) {
      $opzioni = array_replace_recursive($opzioni, $opts);
    }

    $this->opzioni = $opzioni;

    $this->setBase();

  }

  private function setBase() {

    $base = $this->opzioni['host'].$this->opzioni['root'];

    if($this->opzioni['page'] == 'index') {

      if($this->opzioni['lang'] && $this->opzioni['lang'] != 'it') {

        $this->opzioni['lang'] = substr(Utility::sanitize($this->opzioni['lang']), 0, 2);

        $base .= $this->opzioni['lang'].'/';

      }

    } elseif($this->opzioni['page'] == 'admin') {

      $base .= 'admin/';

    }

    $this->base = $base;

  }

  public function setLang($lang) {

    $lang = substr(Utility::sanitize($lang), 0, 2);

    $this->opzioni['lang'] = $lang;

    $this->setBase();

  }

  public function setPage($page) {

    if(in_array($page, ['index', 'admin'])) {

      $this->opzioni['page'] = $page;

      $this->setBase();

    }

  }

  public function setOptions($options) {

    $this->opzioni = array_replace_recursive($this->opzioni, $options);

    $this->setBase();

  }

  public function get($params, $debug = false) {

    $params = $this->readParams($params);

    if($debug) {
      Utility::pre($params);
    }

    $url = $this->base;

    if($this->opzioni['page'] == 'admin') {

      if(isset($params['cmd']) && $params['cmd']) {
        $url .= $params['cmd'].'/';
      }
      if(isset($params['act']) && $params['act']) {
        $url .= $params['act'].'/';
      }

      return $url;
    }

    if(isset($params['cmd'])) {

      switch($params['cmd']) {

        case 'api':

          $url .= 'api/'.$params['version'].'/'.$params['object'].'/'.$params['action'].'/';

          break;

        case 'carrello':

          $url .= 'carrello/';

          if(isset($params['act']) && $params['act']) {
            $url .= $params['act'].'/';
          }

          if(isset($params['id'])) {
            $url .= Utility::sanitize($params['id']).'.html';
          }

          break;

        case 'catalogo':

          $url .= 'catalogo/';

          if(isset($params['categoria'])) {

            $url .= $params['categoria'];

            if(Utility::isPositiveInt($params['pag']) && $params['pag'] > 1) {
              $url .= '/pag'.$params['pag'].'.html';
            } else {
              $url .= '/';
            }
          }

          break;

        case 'cerca':

          $url .= 'cerca/';

          $p = [];

          if(isset($params['q'])) {
            $p[] = 'q='.$params['q'];
          }

          if(isset($params['pag']) && $params['pag'] > 1) {
            $p[] = 'pag='.$params['pag'];
          }

          if(count($p)) {
            $url .= '?'.implode('&', $p);
          }

          break;


        case 'home':
          break;

        case 'login':
          $url .= 'utente/';
          $url .= 'login/';
          break;

        case 'logout':

          $url .= 'utente/';
          $url .= 'logout/';

          break;

        case 'news':

          $url .= $params['cmd'];

          if(isset($params['tag'])) {
            $url .= '/tag/'.urlencode($params['tag']).'/';
          } else {

            if(isset($params['cat'])) {
              $url .= '/'.Utility::sanitize($params['cat']);
            }

            if(isset($params['href']) && $params['href']) {
              $url .= '/'.Utility::sanitize($params['href']).'.html';
            } else {
              if(isset($params['type']) && $params['type'] == 'rss') {
                $url .= '.rss';
              } else {
                $url .= '/';
              }
            }
          }

          if(Utility::isPositiveInt($params['pag']) && $params['pag'] > 1) {
            $url .= 'pagina'.$params['pag'].'.html';
          }

          break;

        case 'marchio':

          $url .= 'marchio/';
          $url .= $params['id'].'/';
          $url .= Utility::sanitize($params['title']).'/';
          break;

        case 'orders':

          $url .= 'orders/';

          if(isset($params['act']) && $params['act']) {
            switch($params['act']) {
              case 'fattura':
                $url .= $params['act'].'-'.$params['id'].'.pdf';
                break;
              case 'pay':
                $url .= $params['act'].'-'.$params['id'].'/';
                break;
            }
          }

          break;

        case 'pagamenti':

          $url .= 'pagamenti/';
          $url .= $params['act'].'/';

          break;

        case 'pagina':

          $lista = [];

          if(isset($params['parent']) && $params['parent']) {

            while($params['parent'] != 0) {

              $options                  = [];
              $options['tableFilename'] = 'menu';

              $m = new Menu($options);

              $opts                 = [];
              $opts['forceAllLang'] = 1;

              $m = $m->getById($params['parent'], $opts);

              $params['parent'] = $m->fields['parent'];

              $lista[] = $m->fields[$this->opzioni['lang']]['href'];

            }

          }

          $lista = array_reverse($lista);

          foreach($lista as $val) {
            $url .= $val.'/';
          }

          if(isset($params['href']) && $params['href']) {
            $url .= Utility::sanitize($params['href']);
          }
          if(Utility::isPositiveInt($params['pag']) && $params['pag'] > 1) {
            $url .= '/pag'.$params['pag'];
          }

          if(isset($params['elenco']) && $params['elenco']) {
            $url .= '/';
          } else {
            $url .= '.html';
          }
          break;

        case 'prodotti':

          $url .= 'catalogo/';

          if(isset($params['href'])) {
            $url .= Utility::sanitize($params['href']);
            $url .= '.html';
          }

          break;

        case 'profile':
          $url .= 'utente/';
          break;

        case 'voucher':
          $url .= 'make-a-gift/';
          $url .= $params['anagrafica'].'/';
          if(isset($params['act']) && $params['act']) {
            $url .= $params['act'].'.html';
          }
          break;


        default:
          if($params['cmd']) {
            $url .= $params['cmd'].'/';
          }
          if(isset($params['act']) && $params['act']) {
            $url .= $params['act'].'/';
          }
      }

    }

    return $url;

  }

  private function readParams($params) {

    $data = [];

    if(is_string($params) && $params) {

      $params = trim($params, '/');
      $params = explode('/', $params);

      for($i = 0; $i < count($params); $i += 2) {
        $data[$params[$i]] = $params[$i + 1];
      }

    }

    return $data;

  }

  public function getPage() {

    return $this->opzioni['page'];
  }

}