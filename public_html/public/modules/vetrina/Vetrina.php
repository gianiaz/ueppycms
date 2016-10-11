<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (13/06/16, 15.54)                                                                     **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
namespace Ueppy\widgets\vetrina;

use Ueppy\core\VetrinaSettings;
use Ueppy\core\ViewManager;
use Ueppy\front\Widget;
use Ueppy\utils\Utility;


class Vetrina extends Widget {

  private $dimensioni = false;

  function getDimensioni() {

    if(!$this->dimensioni) {

      $options                  = [];
      $options['tableFilename'] = 'vetrina_settings';

      $VetrinaSettingsObj = new \Ueppy\core\VetrinaSettings($options);

      $gruppoConfigurazione = 'default';

      if($this->config['gruppo']) {
        $gruppoConfigurazione = $this->config['gruppo'];
      }

      $this->dimensioni = $VetrinaSettingsObj->getByGruppo($gruppoConfigurazione);

    }

    return $this->dimensioni;

  }

  function getlist() {

    $imgSetting['tipo']       = 'crop';
    $imgSetting['dimensione'] = $this->getDimensioni();
    $imgSetting['options']    = [];

    $options                              = [];
    $options['tableFilename']             = 'vetrina';
    $options['imgSettings']['img'][]      = $imgSetting;
    $options['options']['type_of_resize'] = 'loss';

    $VetrinaObj = new \Ueppy\core\Vetrina($options);

    $filter_record              = [];
    $filter_record['chiave']    = 'attivo';
    $filter_record['operatore'] = '=';
    $filter_record['valore']    = '1';

    $filters[] = $filter_record;

    if($this->config['gruppo']) {

      $filter_record              = [];
      $filter_record['chiave']    = 'gruppo';
      $filter_record['operatore'] = '=';
      $filter_record['valore']    = $this->config['gruppo'];

      $filters[] = $filter_record;

    }

    $opts              = [];
    $opts['sortField'] = 'ordine';
    $opts['sortOrder'] = 'ASC';
    $opts['filters']   = $filters;
    $opts['operatore'] = 'AND';
    $opts['debug']     = 0;

    $VetrinaObjList = $VetrinaObj->getlist($opts);

    return $VetrinaObjList;

  }

  function out() {

    $out = [];

    $out['pause']       = $this->config['pause'];
    $out['transizione'] = $this->config['transizione'];

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

    $VetrinaObjList = $this->getlist();

    $out['items'] = [];

    foreach($VetrinaObjList as $VetrinaObj) {

      $record                = [];
      $record['titolo']      = $VetrinaObj->fields[ACTUAL_LANGUAGE]['titolo'];
      $record['sottotitolo'] = $VetrinaObj->fields[ACTUAL_LANGUAGE]['sottotitolo'];
      $record['testo']       = $VetrinaObj->fields[ACTUAL_LANGUAGE]['testo'];
      $record['url']         = false;
      $record['img']         = [];
      $record['img']['url']  = '/placeholder-'.$this->getDimensioni().'.jpg';
      $record['img']['alt']  = $VetrinaObj->fields[ACTUAL_LANGUAGE]['titolo'];

      if($VetrinaObj->fields['fileData']['img'][ACTUAL_LANGUAGE]['exists']) {
        $record['img']['url'] = $VetrinaObj->fields['fileData']['img'][ACTUAL_LANGUAGE]['versioni'][0]['rel_path'];
      }

      if($VetrinaObj->fields[ACTUAL_LANGUAGE]['img_alt']) {
        $record['img']['alt'] = $VetrinaObj->fields[ACTUAL_LANGUAGE]['img_alt'];
      }

      if($VetrinaObj->fields[ACTUAL_LANGUAGE]['url']) {
        $record['url'] = $VetrinaObj->fields[ACTUAL_LANGUAGE]['url'];
      }

      $out['items'][] = $record;

    }

    if($this->config['debug']) {
      Utility::pre($this->config);
      Utility::pre($out);
    }

    return $out;

  }

}