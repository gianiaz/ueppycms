<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (08/05/16, 7.14)                                                                     **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
use Ueppy\utils\Utility;

$htmlWidgets = [];
$widgetDir   = DOC_ROOT.REL_ROOT.'admin/default/widgets/';
$widgetsList = glob($widgetDir.'*', GLOB_ONLYDIR);
$debug       = 0;

$sempre = ['rssbenvenuto', 'rss', 'stats'];
//$sempre    = [];
$debugData = [];

$debugData[] = print_r($widgetsList, true);

$widgets     = [];
$widgetsData = [];
$size        = [];

foreach($widgetsList as $widget) {

  $widgetName = basename($widget);

  list($ordine, $widgetName) = explode('.', $widgetName);

  if($operator->hasRights($widgetName) || in_array($widgetName, $sempre)) {

    $php = $widget.'/'.$widgetName.'.php';
    $css = $widget.'/'.$widgetName.'.css';
    $js  = $widget.'/'.$widgetName.'.js';

    $pathCSS[] = $css;
    $pathJS[]  = $js;

    if(file_exists($php)) {
      include($php);
    }
    $size[$widgetName] = $widgetsData[$widgetName]['size'];

  }

}

$smarty->assign('widgetsData', $widgetsData);

//Utility::pre($widgetsData);

$conteggio     = 0;
$htmlWidgets[] = '<div class="row">';

foreach($widgetsList as $widget) {

  $widgetName = basename($widget);

  list($ordine, $widgetName) = explode('.', $widgetName);

  if($conteggio + $size[$widgetName] > 12) {
    $htmlWidgets[] = '</div><div class="row">';
    $conteggio     = $size[$widgetName];
  } else {
    $conteggio += $size[$widgetName];
  }

  if($operator->hasRights($widgetName) || in_array($widgetName, $sempre)) {

    $tpl = $widget.'/'.$widgetName.'.tpl';

    $htmlWidgets[] = $smarty->fetch($tpl);

  }

}

$htmlWidgets[] = '</div>';


if($debug) {
  Utility::pre(implode("\n", $debugData));
}

$smarty->assign('html', implode("\n", $htmlWidgets));