<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (14/06/16, 6.55)                                                                      **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/

namespace Ueppy\core;

use Smarty;
use Ueppy\utils\Utility;

class UeppySmarty extends Smarty {

  public $JS;
  public $CSS;
  public $queries;

  function __construct($opts = null) {

    $opzioni                    = [];
    $opzioni['template_dir']    = false;
    $opzioni['compile_dir']     = false;
    $opzioni['caching']         = Smarty::CACHING_OFF;
    $opzioni['error_reporting'] = E_ALL & ~E_NOTICE;

    if($opts) {
      $opzioni = array_replace_recursive($opzioni, $opts);
    }

    parent::__construct();
    $this->setTemplateDir($opzioni['template_dir']);
    $this->setCompileDir($opzioni['compile_dir']);
    $this->setCaching($opzioni['caching']);
    $this->error_reporting = $opzioni['error_reporting'];
  }

  function loadCSS($opts = null) {

    $CSS = [];

    $opzioni          = [];
    $opzioni['debug'] = 0;
    $opzioni['var']   = 'css';

    if($opts) {
      $opzioni = array_replace_recursive($opzioni, $opts);
    }

    if($opzioni['debug']) {
      Utility::pre($opzioni);
      Utility::pre($GLOBALS[$opzioni['var']]);
    }


    $CSS['generali'] = [];
    $CSS['pagina']   = [];

    if(is_array($GLOBALS[$opzioni['var']])) {

      foreach($GLOBALS[$opzioni['var']] as $style) {

        $styleRP = realpath($style);

        if(file_exists($styleRP)) {

          $CSS['generali'][] = str_replace(DOC_ROOT.REL_ROOT, '/', $styleRP);

        } elseif($opzioni['debug']) {
          Utility::debug('Il file '.$styleRP.' non esiste, fornito in configurazione il valore : '.$style, ['level' => 'error']);
        }

      }
    }

    if($opzioni['debug']) {
      Utility::pre($CSS);
    }

    $this->CSS = $CSS;

    $this->assign('CSS', $this->CSS);

  }

  function addCSS($opts = null) {

    $opzioni              = [];
    $opzioni['path']      = '';
    $opzioni['output']    = true;
    $opzioni['posizione'] = 'last'; // FIRST | LAST | index numerico
    $opzioni['debug']     = 0;

    if($opts) {
      $opzioni = array_replace_recursive($opzioni, $opts);
    }

    if(!is_array($opzioni['path'])) {
      $opzioni['path'] = [$opzioni['path']];
    }

    $opzioni['path'] = array_filter($opzioni['path']);

    if($opzioni['path']) {

      foreach($opzioni['path'] as $pathFromArray) {

        if(filter_var($pathFromArray, FILTER_VALIDATE_URL) || file_exists($pathFromArray)) {

          if(filter_var($pathFromArray, FILTER_VALIDATE_URL) === false) {

            $path = realpath($pathFromArray);

            $path = str_replace(DOC_ROOT.REL_ROOT, '/', $path);

          } else {
            $path = $pathFromArray;
          }

          if($opzioni['debug']) {

            $str = 'Aggiungo '.$path;

            if($opzioni['posizione'] == 'last') {
              $str .= ' come ultimo css di pagina';
            } elseif($opzioni['posizione'] == 'first') {
              $str .= ' come primo css di pagina';
            } else {
              $str .= ' in posizione '.$opzioni['posizione'];
            }

            Utility::pre($str);

          }

          if(!in_array($path, $this->CSS['pagina'])) {
            if(!count($this->CSS['pagina']) || $opzioni['posizione'] == 'last') {
              $this->CSS['pagina'][] = $path;
            } else {
              if($opzioni['posizione'] == 'first') {
                array_unshift($this->CSS['pagina'], $path);
              } elseif(is_numeric($opzioni['posizione'])) {

                $before  = array_slice($this->CSS['pagina'], 0, $opzioni['posizione']);
                $after   = array_slice($this->CSS['pagina'], $opzioni['posizione']);
                $between = [$path];

                $this->CSS['pagina'] = array_merge($before, $between, $after);

              }
            }
          } else {
            if($opzioni['debug']) {
              Utility::pre('Non aggiungo file già presenti');
            }
          }

          if($opzioni['debug']) {
            Utility::pre($this->CSS);
          }

          $this->assign('CSS', $this->CSS);

        } else {
          if($opzioni['output']) {
            Utility::debug('Il percorso passato non esiste ('.$pathFromArray.')', ['level' => 'error']);
          }
        }

      }

    } else {
      if($opzioni['debug']) {
        Utility::pre('Non hai passato un path per il foglio di stile');
      }
    }

  }

  function removeCSS($opts = null) {

    $opzioni          = [];
    $opzioni['path']  = false;
    $opzioni['debug'] = 0;

    if($opts) $opzioni = array_replace_recursive($opzioni, $opts);

    if(!$opzioni['path']) {
      Utility::pre('Non hai passato nessun percorso al file da rimuovere');
    } else {

      $CSS_NUOVO             = [];
      $CSS_NUOVO['dist']     = '';
      $CSS_NUOVO['generali'] = [];
      $CSS_NUOVO['pagina']   = [];

      foreach($this->CSS['generali'] as $fileCSS) {

        if(realpath(str_replace('//', '/', DOC_ROOT.REL_ROOT.$fileCSS)) != realpath($opzioni['path'])) {
          if($opzioni['debug']) {
            Utility::pre('Il file '.realpath(DOC_ROOT.REL_ROOT.$fileCSS).'('.$fileCSS.') non corrisponde con '.realpath($opzioni['path']).'('.$opzioni['path']).')';
          }
          $CSS_NUOVO['generali'][] = $fileCSS;
        } else {
          if($opzioni['debug']) {
            Utility::pre('Il file '.realpath(DOC_ROOT.REL_ROOT.$fileCSS).'('.$fileCSS.') corrisponde con '.realpath($opzioni['path']).'('.$opzioni['path'].' ed è stato rimosso');
          }
        }
      }
      foreach($this->CSS['pagina'] as $fileCSS) {
        if(realpath(DOC_ROOT.$fileCSS) != realpath($opzioni['path'])) {
          if($opzioni['debug']) {
            Utility::pre('Il file '.$fileCSS.' non corrisponde con '.$opzioni['path']);
          }
          $CSS_NUOVO['pagina'][] = $fileCSS;
        } else {
          if($opzioni['debug']) {
            Utility::pre('Il file '.$fileCSS.' corrisponde con '.$opzioni['path'].' ed è stato rimosso');
          }
        }
      }

      $this->CSS = $CSS_NUOVO;

      $this->assign('CSS', $this->CSS);

    }

  }

  function loadJS($opts = null) {

    $JS = [];

    $opzioni          = [];
    $opzioni['debug'] = 0;
    $opzioni['var']   = 'js';

    if($opts) {
      $opzioni = array_replace_recursive($opzioni, $opts);
    }

    $JS['generali'] = [];
    $JS['pagina']   = [];

    if(is_array($GLOBALS[$opzioni['var']])) {

      foreach($GLOBALS[$opzioni['var']] as $javascript) {

        $javascriptRP = realpath($javascript);

        if(file_exists($javascriptRP)) {

          $JS['generali'][] = str_replace(DOC_ROOT.REL_ROOT, '/', $javascriptRP);

        } elseif($opzioni['debug']) {
          Utility::pre('Il file '.$javascriptRP.' non esiste, fornito in configurazione il valore : '.$javascript);
        }

      }
    }

    if($opzioni['debug']) {
      Utility::pre($JS);
    }

    $this->JS = $JS;


    $this->assign('JS', $this->JS);

  }

  function addJS($opts = null) {

    $opzioni              = [];
    $opzioni['path']      = [];
    $opzioni['posizione'] = 'last'; // FIRST | LAST | index numerico
    $opzioni['debug']     = 0;

    if($opts && is_array($opts)) {
      if(!is_array($opts)) {
        Utility::pre($opts);
      }
      $opzioni = array_replace_recursive($opzioni, $opts);
    }

    if(!is_array($opzioni['path'])) {
      $opzioni['path'] = [$opzioni['path']];
    }

    $opzioni['path'] = array_filter($opzioni['path']);

    if($opzioni['path']) {

      foreach($opzioni['path'] as $pathFromArray) {

        if(filter_var($pathFromArray, FILTER_VALIDATE_URL) || file_exists($pathFromArray)) {

          if(filter_var($pathFromArray, FILTER_VALIDATE_URL) === false) {

            $path = realpath($pathFromArray);

            $path = str_replace(DOC_ROOT.REL_ROOT, '/', $path);

          } else {
            $path = $pathFromArray;
          }


          if($opzioni['debug']) {

            $str = 'Aggiungo '.$path;

            if($opzioni['posizione'] == 'last') {
              $str .= ' come ultimo js di pagina';
            } elseif($opzioni['posizione'] == 'first') {
              $str .= ' come primo js di pagina';
            } else {
              $str .= ' in posizione '.$opzioni['posizione'];
            }

            Utility::pre($str);

          }

          if(!in_array($path, $this->JS['pagina'])) {
            if(!count($this->JS['pagina']) || $opzioni['posizione'] == 'last') {
              $this->JS['pagina'][] = $path;
            } else {
              if($opzioni['posizione'] == 'first') {
                array_unshift($this->JS['pagina'], $path);
              } elseif(is_numeric($opzioni['posizione'])) {

                $before  = array_slice($this->JS['pagina'], 0, $opzioni['posizione']);
                $after   = array_slice($this->JS['pagina'], $opzioni['posizione']);
                $between = [$path];

                $this->JS['pagina'] = array_merge($before, $between, $after);

              }
            }
          } else {
            if($opzioni['debug']) {
              Utility::pre('Non aggiungo file già presenti');
            }
          }

        }

      }

      if($opzioni['debug']) {
        Utility::debug($this->JS);
      }

      $this->assign('JS', $this->JS);

    } else {
      Utility::debug('Parametro "path" vuoto', ['level' => 'error']);
    }

  }

  function removeJS($opts = null) {

    $opzioni          = [];
    $opzioni['path']  = false;
    $opzioni['debug'] = 0;

    if($opts) $opzioni = array_replace_recursive($opzioni, $opts);

    if(!$opzioni['path']) {
      Utility::pre('Non hai passato nessun percorso al file da rimuovere');
    } else {

      $JS_NUOVO             = [];
      $JS_NUOVO['dist']     = '';
      $JS_NUOVO['generali'] = [];
      $JS_NUOVO['pagina']   = [];

      foreach($this->JS['generali'] as $fileJavascript) {
        if(realpath(str_replace('//', '/', DOC_ROOT.REL_ROOT.$fileJavascript)) != realpath($opzioni['path'])) {
          if($opzioni['debug']) {
            Utility::pre('Il file '.realpath(DOC_ROOT.REL_ROOT.$fileJavascript).'('.$fileJavascript.') non corrisponde con '.realpath($opzioni['path']).'('.$opzioni['path']).')';
          }
          $JS_NUOVO['generali'][] = $fileJavascript;
        } else {
          if($opzioni['debug']) {
            Utility::pre('Il file '.realpath(DOC_ROOT.REL_ROOT.$fileJavascript).'('.$fileJavascript.') corrisponde con '.realpath($opzioni['path']).'('.$opzioni['path'].' ed è stato rimosso');
          }
        }
      }

      foreach($this->JS['pagina'] as $fileJavascript) {
        if(realpath($fileJavascript) != realpath($opzioni['path'])) {
          if($opzioni['debug']) {
            Utility::pre('Il file '.$fileJavascript.' non corrisponde con '.$opzioni['path']);
          }
          $JS_NUOVO['pagina'][] = $fileJavascript;
        } else {
          Utility::pre('Il file '.$fileJavascript.' corrisponde con '.$opzioni['path'].' ed è stato rimosso');
        }
      }

      $this->JS = $JS_NUOVO;

      $this->assign('JS', $this->JS);

    }

  }

  function compressCSS() {

    $out = DOC_ROOT.REL_ROOT.CSS_PUB;
    if(!is_dir($out)) {
      Utility::mkdirp($out);
    }

    $fname = 'dist.'.SET_CSS_CACHE_VERSION.'.css';

    $content = '';

    foreach($this->CSS['generali'] as $file) {

      $file = str_replace('//', '/', DOC_ROOT.REL_ROOT.$file);

      $css = trim(file_get_contents($file));

      $content .= $css;
    }

    require_once(DOC_ROOT.REL_ROOT.'lib/minimizer/CSS.php');
    if(SET_MINIMZZA_CSS) $content = Minify_CSS::minify($content);

    file_put_contents($out.$fname, $content);

    $this->CSS['dist'] = [];

    $this->CSS['dist'][] = str_replace(DOC_ROOT.REL_ROOT, '/', $out.$fname);

    $distPaginaFile = 'dist.'.ACTUAL_LANGUAGE;
    if($GLOBALS['cmd']) {
      $distPaginaFile .= '.'.$GLOBALS['cmd'];
    }
    if($GLOBALS['act']) {
      $distPaginaFile .= '.'.$GLOBALS['act'];
    }
    $distPaginaFile .= '.css';

    $content = '';

    foreach($this->CSS['pagina'] as $file) {

      $file = str_replace('//', '/', DOC_ROOT.REL_ROOT.$file);

      $css = trim(file_get_contents($file));

      $content .= $css;
    }

    if(SET_MINIMZZA_CSS) $content = Minify_CSS::minify($content);

    file_put_contents($out.$distPaginaFile, $content);

    $this->CSS['dist'][] = str_replace(DOC_ROOT.REL_ROOT, '/', $out.$distPaginaFile);

    $this->assign('CSS', $this->CSS);


  }

  function compressJS() {

    $nonMinimizzareMai = ['handlebars-v4.0.0.js'];

    $out = DOC_ROOT.REL_ROOT.JS_PUB;
    if(!is_dir($out)) {
      Utility::mkdirp($out);
    }

    $fname = 'dist.'.SET_JS_CACHE_VERSION.'.js';

    require_once(DOC_ROOT.REL_ROOT.'lib/minimizer/Javascript.php');


    $content = '';

    $js = '';

    foreach($this->JS['generali'] as $file) {

      $file = str_replace('//', '/', DOC_ROOT.REL_ROOT.$file);

      $js = '/** '.basename($file).' **/'."\n";

      $js .= trim(file_get_contents($file));

      if($js[strlen($js) - 1] != ';') {
        $js .= ';';
      }

      $js .= "\n";
      if(strpos($file, '.min.js') === false && SET_MINIMIZZA_JS && !in_array(basename($file), $nonMinimizzareMai)) {
        $js = Minify_Javascript::minify($js);
      }

      if(!$content) {
        $content .= "\n";
      }

      $content .= $js;

    }

    file_put_contents($out.$fname, $content);

    $this->JS['dist'] = [];

    $this->JS['dist'][] = str_replace(DOC_ROOT.REL_ROOT, '/', $out.$fname);

    $distPaginaFile = 'dist.'.ACTUAL_LANGUAGE;
    if($GLOBALS['cmd']) {
      $distPaginaFile .= '.'.$GLOBALS['cmd'];
    }
    if($GLOBALS['act']) {
      $distPaginaFile .= '.'.$GLOBALS['act'];
    }
    $distPaginaFile .= '.js';

    $content = '';

    foreach($this->JS['pagina'] as $file) {

      $file = str_replace('//', '/', DOC_ROOT.REL_ROOT.$file);

      $js = trim(file_get_contents($file));

      if($js[strlen($js) - 1] != ';') {
        $js .= ';';
      }

      if(strpos($file, '.min.js') === false && SET_MINIMIZZA_JS) {
        $js = Minify_Javascript::minify($js);
      }

      $content .= $js;

    }

    file_put_contents($out.$distPaginaFile, $content);

    $this->JS['dist'][] = str_replace(DOC_ROOT.REL_ROOT, '/', $out.$distPaginaFile);

    $this->assign('JS', $this->JS);

  }

}