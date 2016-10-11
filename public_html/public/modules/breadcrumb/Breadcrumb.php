<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (17/06/16, 14.46)                                                                     **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
namespace Ueppy\widgets\breadcrumb;

use Ueppy\core\LinkManager;
use Ueppy\core\Traduzioni;
use Ueppy\front\Widget;
use Ueppy\utils\Utility;
use Ueppy\core\Menu;

class Breadcrumb extends Widget {

  function getPercorsoBase() {

    $lm = LinkManager::getInstance();

    $out = [];

    $home = ['label' => Traduzioni::getLang('default', 'HOME'),
             'url'   => $lm->get('')];

    $out[] = $home;

    $url = $this->MenuObj->getUrl();

    $out[] = ['label' => $this->MenuObj->fields[ACTUAL_LANGUAGE]['dicitura'],
              'url'   => $url[ACTUAL_LANGUAGE]];

    return $out;

  }

  function getPercorsoPagina() {

    $out = [];

    $m = $this->MenuObj;

    $lm = LinkManager::getInstance();

    $bread = [];


    $parent = 1;
    while($parent) {
      $parent                    = $m->fields['parent'];
      $breadCrumbRecord['label'] = $m->fields[ACTUAL_LANGUAGE]['dicitura'];
      $url                       = $m->getUrl();
      $breadCrumbRecord['url']   = $url[ACTUAL_LANGUAGE];
      $bread[]                   = $breadCrumbRecord;
      if($m->fields['parent']) {
        $m = $m->getById($m->fields['parent']);
      }
    }

    $home = ['label' => Traduzioni::getLang('default', 'HOME'),
             'url'   => $lm->get('')];

    $bread[] = $home;

    $out = array_reverse($bread);

    return $out;

  }

  function getPercorsoProdotto() {


    $ProdottoObj = $this->mainController->getMainObj();

    $CategoriaProdottiObj = $ProdottoObj->additionalData['categoriaProdottiObj'];

    $out = $this->getPercorsoCategoria($CategoriaProdottiObj);

    $prodotto = ['label' => $ProdottoObj->fields[ACTUAL_LANGUAGE]['nome'],
                 'url'   => false];

    $out[] = $prodotto;

    return $out;

  }

  function getPercorsoCategoria($CategoriaProdottiObj) {


    $parent = $CategoriaProdottiObj->fields['genitore'];

    $breadCrumbRecord['label'] = $CategoriaProdottiObj->fields[ACTUAL_LANGUAGE]['nome'];
    $url                       = $CategoriaProdottiObj->getUrl();
    $breadCrumbRecord['url']   = $url[ACTUAL_LANGUAGE];
    $bread[]                   = $breadCrumbRecord;

    while($parent) {
      $CategoriaProdottiObj      = $CategoriaProdottiObj->getById($parent);
      $breadCrumbRecord['label'] = $CategoriaProdottiObj->fields[ACTUAL_LANGUAGE]['nome'];
      $url                       = $CategoriaProdottiObj->getUrl();
      $breadCrumbRecord['url']   = $url[ACTUAL_LANGUAGE];
      $bread[]                   = $breadCrumbRecord;
      $parent                    = $CategoriaProdottiObj->fields['genitore'];
    }

    $lm = LinkManager::getInstance();

    $bread = array_reverse($bread);

    $out = array_merge($this->getPercorsoBase(), $bread);

    return $out;

  }

  function getPercorsoUtente() {

    $out = $this->getPercorsoBase();

    if($this->mainController->route->act) {
      switch($this->mainController->route->act) {
        case 'changepass':
          $breadCrumbRecord          = [];
          $breadCrumbRecord['label'] = Traduzioni::getLang('utente', 'RESET_PASSWORD');
          $breadCrumbRecord['url']   = '';

          $out[] = $breadCrumbRecord;
          break;
        case 'profile':
          $breadCrumbRecord          = [];
          $breadCrumbRecord['label'] = Traduzioni::getLang('utente', 'PROFILO');
          $breadCrumbRecord['url']   = '';
          $out[]                     = $breadCrumbRecord;
          break;
      }
    }

    return $out;

  }

  private function getPercorsoNews($NewsObj) {

    $out = $this->getPercorsoBase();

    $data = $this->mainController->getMainData();

    if(isset($data['NewsCategoryObj'])) {

      $url = $data['NewsCategoryObj']->getUrl();

      $breadCrumbRecord          = [];
      $breadCrumbRecord['label'] = $data['NewsCategoryObj']->fields[ACTUAL_LANGUAGE]['name'];
      $breadCrumbRecord['url']   = $url[ACTUAL_LANGUAGE];

      $out[] = $breadCrumbRecord;

    }

    if($this->mainController->route->act == 'read') {

      $breadCrumbRecord          = [];
      $breadCrumbRecord['label'] = $NewsObj->fields[ACTUAL_LANGUAGE]['titolo'];
      $breadCrumbRecord['url']   = '';
      $out[]                     = $breadCrumbRecord;

    }

    return $out;
  }

  function out() {

    $out = [];


    if($this->MenuObj) {

      if($this->MenuObj->fields['nomefile'] == 'pagina') {

        $out = $this->getPercorsoPagina();

      } else {

        switch($this->MenuObj->fields['nomefile']) {
          case 'cerca':
            $out = $this->getPercorsoBase();
            break;
          case 'carrello':
            $out = $this->getPercorsoBase();
            break;
          case 'catalogo':
            $CategoriaProdottiObj = $this->mainController->getMainObj();
            if($CategoriaProdottiObj->fields['id']) {
              $out = $this->getPercorsoCategoria($CategoriaProdottiObj);
            } else {
              $out = $this->getPercorsoBase();
            }
            break;
          case 'news':

            $out = $this->getPercorsoNews($this->mainController->getMainObj());

            break;
          case 'utente':
            $out = $this->getPercorsoUtente();
            break;
          case 'wishlist':
            $lm = LinkManager::getInstance();

            $out = [];

            $home = ['label' => Traduzioni::getLang('default', 'HOME'),
                     'url'   => $lm->get('')];

            $out[] = $home;

            $breadCrumbRecord          = [];
            $breadCrumbRecord['label'] = Traduzioni::getLang('utente', 'ACCOUNT_UTENTE');
            $breadCrumbRecord['url']   = $lm->get('cmd/utente');

            $out[] = $breadCrumbRecord;

            $url = $this->MenuObj->getUrl();

            $out[] = ['label' => $this->MenuObj->fields[ACTUAL_LANGUAGE]['dicitura'],
                      'url'   => $url[ACTUAL_LANGUAGE]];

            break;
          case 'orders':

            $lm = LinkManager::getInstance();

            $out = [];

            $home = ['label' => Traduzioni::getLang('default', 'HOME'),
                     'url'   => $lm->get('')];

            $out[] = $home;

            $breadCrumbRecord          = [];
            $breadCrumbRecord['label'] = Traduzioni::getLang('utente', 'ACCOUNT_UTENTE');
            $breadCrumbRecord['url']   = $lm->get('cmd/utente');

            $out[] = $breadCrumbRecord;

            $url = $this->MenuObj->getUrl();

            $out[] = ['label' => $this->MenuObj->fields[ACTUAL_LANGUAGE]['dicitura'],
                      'url'   => $url[ACTUAL_LANGUAGE]];

            break;

          case 'prodotti':
            $out = $this->getPercorsoProdotto();
            break;
        }

      }

    } else {
      Utility::pre('bho');
    }

    return $out;
  }

}
