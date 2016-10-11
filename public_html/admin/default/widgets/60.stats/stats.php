<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (12/07/16, 16.59)                                                                     **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
use Ueppy\utils\Utility;
use Ueppy\core\Traduzioni;

if(!function_exists('statsWidget')) {
  function statsWidget($debug = 0) {

    $return = false;

    if(defined('SET_GOOGLE_ANALYTICS_PROFILE_ID') && SET_GOOGLE_ANALYTICS_PROFILE_ID) {

      $GLOBALS['pathJS'][] = DOC_ROOT.REL_ROOT.'bower_components/raphael/raphael.min.js';
      $GLOBALS['pathJS'][] = DOC_ROOT.REL_ROOT.'bower_components/moment/min/moment-with-locales.min.js';

      $GLOBALS['pathJS'][] = 'https://www.google.com/jsapi';
      $GLOBALS['pathJS'][] = 'https://www.gstatic.com/charts/loader.js';


      $return            = [];
      $return['widget']  = 'stats';
      $return['icon']    = ' fa-pie-chart';
      $return['size']    = 12;
      $return['title']   = Traduzioni::getLang('stats', 'TITLE');
      $return['periodo'] = [];
      if(date('d') == 1) {
        $t_start                    = new \Ueppy\utils\Time(strtotime('-1day'));
        $return['periodo']['start'] = $t_start->format();
        $t_end                      = new \Ueppy\utils\Time(mktime(0, 0, 0, date('m', $t_start->toTimeStamp()), 1, date('Y')));
        $return['periodo']['end']   = $t_end->format();
        $return['periodo']['mese']  = date('Y').'-'.date('m', $t_start->toTimeStamp());
        $return['periodoString']    = $t_start->format('%B %Y');
      } else {
        $t_start                    = new \Ueppy\utils\Time(mktime(0, 0, 0, date('m'), 1, date('Y')));
        $return['periodo']['start'] = $t_start->format();
        $t_end                      = new \Ueppy\utils\Time(strtotime('-1day'));
        $return['periodo']['end']   = $t_end->format();
        $return['periodo']['mese']  = date('Y-m');
        $return['periodoString']    = $t_start->format('%B %Y');
      }

    }

    return $return;

  }

}

$widgetsData['stats'] = statsWidget();