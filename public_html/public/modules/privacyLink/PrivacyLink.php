<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (16/06/16, 10.11)                                                                     **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
namespace Ueppy\widgets\privacyLink;

use Ueppy\front\Widget;
use Ueppy\utils\Utility;
use Ueppy\core\Traduzioni;
use Ueppy\core\Menu;
use Ueppy\core\LinkManager;

class PrivacyLink extends Widget {

  function out() {

    $out['a_class'] = false;
    $out['a_href']  = '';

    if(defined('SET_PRIVACY_ID_INTERNA') && SET_PRIVACY_ID_INTERNA && !SET_IUBENDA_POLICY_IDS) {

      $options                  = [];
      $options['tableFilename'] = 'menu';
      $m                        = new Menu($options);
      $m                        = $m->getById(SET_PRIVACY_ID_INTERNA);

      if($m) {
        $lm            = LinkManager::getInstance();
        $out['a_href'] = $lm->get('cmd/pagina/href/'.$m->fields[ACTUAL_LANGUAGE]['href'].'/parent/0');
      }
    } elseif(SET_IUBENDA_POLICY_IDS) {
      $out['a_class'] = 'iubenda-nostyle no-brand iubenda-embed';
      $out['a_href']  = '//www.iubenda.com/privacy-policy/'.$this->mainController->getGlobal('IUBENDA_POLICY_ID');
    }

    if($this->config['debug']) {
      Utility::pre($this->config);
      Utility::pre($out);
    }

    return $out;
  }

}
