<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (15/06/16, 18.34)                                                                     **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
namespace Ueppy\widgets\socials;

use Ueppy\core\VetrinaSettings;
use Ueppy\core\ViewManager;
use Ueppy\front\Widget;
use Ueppy\utils\Utility;


class Socials extends Widget {

  function out() {

    $out['title'] = false;
    $out['testo'] = false;
    $out['url']   = false;

    if(isset($this->config[ACTUAL_LANGUAGE]['title']) && $this->config[ACTUAL_LANGUAGE]['title']) {
      $out['title'] = $this->config[ACTUAL_LANGUAGE]['title'];
    }
    if(isset($this->config[ACTUAL_LANGUAGE]['testo']) && $this->config[ACTUAL_LANGUAGE]['testo']) {
      $out['testo'] = $this->config[ACTUAL_LANGUAGE]['testo'];
    }
    if(isset($this->config[ACTUAL_LANGUAGE]['url']) && $this->config[ACTUAL_LANGUAGE]['url']) {
      $out['url'] = $this->config[ACTUAL_LANGUAGE]['url'];
    }

    $socials                = [];
    $socials['Facebook']    = SET_SOCIAL_FACEBOOK;
    $socials['Twitter']     = SET_SOCIAL_TWITTER;
    $socials['Google Plus'] = SET_SOCIAL_GOOGLEPLUS;
    $socials['Youtube']     = SET_SOCIAL_YOUTUBE;
    $socials['Mail']        = SET_SOCIAL_EMAIL;
    $socials['Skype']       = SET_SOCIAL_SKYPE;
    $socials['Pinterest']   = SET_SOCIAL_PINTEREST;
    $socials['Instagram']   = SET_SOCIAL_INSTAGRAM;
    $socials['Flickr']      = SET_SOCIAL_FLICKR;
    $socials['Rss']         = SET_SOCIAL_RSS;
    $socials['Github']      = SET_SOCIAL_GITHUB;

    $out['socials'] = $socials;

    if($this->config['debug']) {
      Utility::pre($this->config);
      Utility::pre($out);
    }

    return $out;


  }

}