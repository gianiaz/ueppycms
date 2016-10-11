<?php
/***************/
/** v.1.01    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.01 (08/07/2016, 15.41)                                                                   **/
/** - Aggiunta la funzione smarty getPrivacyLink                                                 **/
/** - Aggiunta la funzione smarty widgetDinamico che permette di caricare in qualsiasi punto del **/
/**   template un widget dinamico (anche in quelli principali)                                   **/
/** - Implementati i filitr di output per lo spam e per rimuovere i data-upy*                    **/
/** v.1.00 (13/06/16, 16.35)                                                                     **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/

use Ueppy\core\Traduzioni;
use Ueppy\utils\Utility;
use Ueppy\core\Menu;
use Ueppy\core\LinkManager;
use Ueppy\core\ModuloDinamico;
use Ueppy\core\Ueppy;

function getSmartyLang($params) {

  global $TRADUZIONI_OPTION;

  if(!isset($params['module'])) {
    $params['module'] = 'default';
  }
  if(!isset($params['key'])) {
    $params['key'] = 'NOT_PROVIDED';
  }

  Traduzioni::getInstance($TRADUZIONI_OPTION);

  $result = Traduzioni::getLang($params['module'], $params['key']);

  if(isset($params['htmlallowed']) && $params['htmlallowed']) {
    $result = html_entity_decode($result, ENT_COMPAT, 'UTF-8');
  }

  if(isset($params['nbsp']) && $params['nbsp']) {
    $result = str_replace(' ', '&nbsp;', $result);
  }

  if(isset($params['nl2br']) && $params['nl2br']) {
    return nl2br($result);
  } else {
    return $result;
  }
}

$smarty->registerPlugin("function", "getLang", "getSmartyLang");

function widgetDinamico($params) {

  $return = '';

  if(Utility::isPositiveInt($params['id'])) {

    $options                  = [];
    $options['tableFilename'] = 'moduli_dinamici';

    $ModuloDinamico = new ModuloDinamico($options);

    $ModuloDinamico = $ModuloDinamico->getById($params['id']);

    if($ModuloDinamico) {
      $return = $ModuloDinamico->fields[ACTUAL_LANGUAGE]['testo'];
    }

  }

  return $return;
}

$smarty->registerPlugin("function", "widgetDinamico", "widgetDinamico");

function getPrivacyLink() {

  $a = [];

  if(defined('SET_PRIVACY_ID_INTERNA') && SET_PRIVACY_ID_INTERNA && !SET_IUBENDA_POLICY_IDS) {
    $options                  = [];
    $options['tableFilename'] = 'menu';
    $m                        = new Menu($options);
    $m                        = $m->getById(SET_PRIVACY_ID_INTERNA);

    if($m) {
      $lm         = LinkManager::getInstance();
      $a          = [];
      $a['href']  = $lm->get('cmd/pagina/href/'.$m->fields[ACTUAL_LANGUAGE]['href'].'/parent/0');
      $a['class'] = 'newwindow';
    }
  } elseif(SET_IUBENDA_POLICY_IDS) {

    $a          = [];
    $a['href']  = '//www.iubenda.com/privacy-policy/'.$GLOBALS['smarty']->tpl_vars['IUBENDA_POLICY_ID']->value;
    $a['class'] = 'iubenda-nostyle no-brand iubenda-embed';
  }

  if($a) {
    $link = '<a href="'.$a['href'].'"';
    if(isset($a['class']) && $a['class']) {
      $link .= ' class="'.$a['class'].'"';
    }
    $link .= '>'.Traduzioni::getLang('privacy', 'PRIVACY_LABEL').'</a>';

    return $link;
  } else {
    return 'Qualcosa non va, configura iubenda, oppure fornisci l\'id di una pagina contenente il testo privacy nei settaggi del cms';
  }
}

$smarty->registerPlugin("function", "getPrivacyLink", "getPrivacyLink");

function make_url($params) {

  $options = [];

  $lm = \Ueppy\core\Linkmanager::getInstance();

  if(isset($params['lang']) && $params['lang']) {
    $lm->setLang($params['lang']);
  }

  $debug = false;
  if(isset($params['debug']) && $params['debug']) {
    $debug = true;
  }

  $urlParams = '';

  if(isset($params['params'])) {
    $urlParams = $params['params'];
  }

  return $lm->get($urlParams, $debug);

}


$smarty->registerPlugin("function", "make_url", "make_url");


function numberFormat($number, $decimals = 2, $onlydecimal = false) {

  if($onlydecimal) {
    $THOUSANDS_SEP = '';
  } else {
    $THOUSANDS_SEP = SET_THOUSANDS_SEP;
  }

  return number_format($number, $decimals, DEC_POINT, $THOUSANDS_SEP);
}

$smarty->registerPlugin("modifier", "smartyNumberFormat", "numberFormat");

function includePlugin($params) {

  if(!isset($params['blocco'])) {
    die('Blocco non fornito');
  } else {
    $blocco = $params['blocco'];
  }

  if(!isset($params['debug'])) {
    $debug = 0;
  } else {
    $debug = $params['debug'];
  }

  global $smarty;
  global $cache_id;
  global $NO_CACHE;
  global $comp_id;

  $SMARTY_VARS = $smarty->tpl_vars['SMARTY_VARS']->value;

  if(isset($smarty->tpl_vars['SMARTY_VARS']->value['widgets'][$blocco]) && is_array($smarty->tpl_vars['SMARTY_VARS']->value['widgets'][$blocco])) {

    $widgets = $smarty->tpl_vars['SMARTY_VARS']->value['widgets'][$blocco];

    $i = 0;
    foreach($smarty->tpl_vars['SMARTY_VARS']->value['widgets'][$blocco] as $istanza => $widgetData) {
      $smarty->assign('widgetData', $widgetData['data']);
      $smarty->display($widgetData['tpl']);
      unset($smarty->tpl_vars['SMARTY_VARS']->value['widgets'][$blocco][$istanza]);
    }
  }


}

$smarty->registerPlugin("function", "includePlugin", "includePlugin");


function pre($params) {

  if(isset($params['data'])) {
    Utility::pre($params['data']);
  }
  if(isset($params['die']) && $params['die']) {
    die;
  }
}


$smarty->registerPlugin("function", "pre", "pre");


// funzioni richiamate dinamicamente da smarty per la creazione dei file js e css anche se il footer e
// l'header sono in cache


function insert_getCSSFiles() {

  global $CSS_VERSION, $cacheObj, $smarty;

  foreach($smarty->styles as $media => $browsers) {
    foreach($browsers as $browser => $versions) {
      if($browser != 'all') {
        foreach($versions as $version => $files) {

          $cachefile = [];

          $cachefile[] = $cacheObj->cssFName($browser, $version);
          $cachefile[] = $CSS_VERSION;
          $cachefile   = DOC_ROOT.REL_ROOT.UPLOAD.'smarty/public/cache/'.implode('.', $cachefile).'.css';

          $cachefile = str_replace(DOC_ROOT, '', $cachefile);

          preg_match_all('/([\w]*)([\d]+)/', $version, $matches);
          $if = '<!--[if ';
          if(isset($matches['1'][0])) {
            $if .= $matches['1'][0];
          }
          $if .= strtoupper($browser).' '.$matches[2][0].']>';
          $if .= '<link rel="stylesheet" href="'.$cachefile.'" type="text/css" media="'.$media.'" />';
          $if .= '<![endif]-->';
          $cssTag[] = $if;

        }
      } else {

        $cachefile   = [];
        $cachefile[] = $cacheObj->cssFName();
        $cachefile[] = $CSS_VERSION;
        $cachefile   = DOC_ROOT.REL_ROOT.UPLOAD.'smarty/public/cache/'.implode('.', $cachefile).'.css';
        $cachefile   = str_replace(DOC_ROOT, '', $cachefile);

        $cssTag[] = '<link rel="stylesheet" href="'.$cachefile.'" type="text/css" media="'.$media.'" />';

      }
    }
  }

  return "\n".implode("\n", $cssTag)."\n";
}

function insert_getJsFile() {

  global $JS_VERSION;
  $cachefile   = [];
  $cachefile[] = 'ueppy';
  $cachefile[] = $JS_VERSION;
  $cachedjs    = REL_ROOT.UPLOAD.'smarty/public/cache/'.implode('.', $cachefile).'.js';

  return '<script type="text/javascript" src="'.$cachedjs.'"></script>';
}


/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Purpose:  Converte le mail in nospam email
 * -------------------------------------------------------------
 */

function smarty_output_filter_nospam($source, Smarty_Internal_Template $template) {


  //$re = '#<a.*class="nspm".*href="mailto:(.*)">.*</a>#';
  $re = '#<a\s+.*nspm.*>.*</a>#sU';
  if(preg_match_all($re, $source, $matches)) {

    foreach($matches[0] as $m) {
      $re1 = '#"mailto:(.*)"#U';
      preg_match_all($re1, $m, $match);
      foreach($match[1] as $mail) {
        $email  = Ueppy::obfuscate($mail);
        $source = str_replace($m, $email, $source);
      }
    }
  }

  return $source;
}

$smarty->registerFilter("output", "smarty_output_filter_nospam");


function smarty_output_filter_no_ueppy_data($source, Smarty_Internal_Template $template) {


  //$re = '#<a.*class="nspm".*href="mailto:(.*)">.*</a>#';
  $re = '#<(.*)(data-upy.*="[^"]+")([^>]*)>#U';
  if(preg_match_all($re, $source, $matches)) {
    foreach($matches[0] as $k => $m) {
      $source = str_replace($m, '<'.trim(trim($matches[1][$k]).' '.trim($matches[2][$k])).'>', $source);
    }

    $re = '#<(.*)(data-upy.*="[^"]+")([^>]*)>#U';
    if(preg_match_all($re, $source, $matches)) {
      foreach($matches[0] as $k => $m) {
        $source = str_replace($m, '<'.trim(trim($matches[1][$k]).' '.trim($matches[3][$k])).'>', $source);
      }
    }

  }


  return $source;
}

$smarty->registerFilter("output", "smarty_output_filter_no_ueppy_data");

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Purpose:  Converte le mail in nospam email
 * -------------------------------------------------------------
 */
function smarty_output_noComments($source, Smarty_Internal_Template $template) {

  if(!defined('SHOW_COMMENTS') || !SHOW_COMMENTS) {
    $re     = '#\<\!\-\-.*\-\-\>#';
    $source = preg_replace($re, '', $source);
  }

  return $source;
}

function smarty_output_iframeIubenda($source, Smarty_Internal_Template $template) {

  $re = '/<iframe(.*)src="([^"]*)"(.*)><\/iframe>/';

  $source = preg_replace_callback($re, function ($matches) {

    if(strpos('suppressedsrc', $matches[1]) === false) {
      $newIframe = '<iframe '.trim($matches[1]).' '.trim($matches[3]).' class="_iub_cs_activate" suppressedsrc="'.$matches[2].'" src="data:text/html;base64,PGh0bWw+PGJvZHk+U3VwcHJlc3NlZDwvYm9keT48L2h0bWw+"></iframe>';
    } else {
      $newIframe = $matches[0];
    }

    return $newIframe;
  }, $source);


  return $source;
}