<?php
/***************/
/** v.1.02    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.02 (19/04/2016)                                                                          **/
/** - Adeguamento a nuova organizzazione files.                                                  **/
/**                                                                                              **/
/** v.1.01 (08/11/2015, 16.47)                                                                   **/
/** - Aggiunto autoload delle classi.                                                            **/
/**                                                                                              **/
/** v.1.00 (05/09/2015)                                                                          **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
use Ueppy\core\Dba;
use Ueppy\utils\Utility;
use Ueppy\core\Settings;
use Ueppy\core\Ueppy;
use Ueppy\core\Traduzioni;

$mainObjOptions                  = [];
$mainObjOptions['tableFilename'] = 'settings';
$mainObjOptions['loadRules']     = true;

switch($act) {

  case 'save_key':

    if($operator->fields['super_admin']) {

      $Obj = new Settings($mainObjOptions);

      if(Utility::isPositiveInt($_POST['id_entry'])) {
        $Obj = $Obj->getById($_POST['id_entry']);
      }

      $opts                      = [];
      $opts['field']             = 'chiave';
      $opts['rule']              = 'Unico';
      $opts['args']              = [];
      $opts['args']['table']     = 'settings';
      $opts['args']['confronto'] = 'id';
      $opts['args']['escludi']   = $_POST['id_entry'];

      $Obj->addRule($opts); // Aggiungo regola di unicità sul db

      $Obj->chiave             = $_POST['chiave'];
      $Obj->gruppo_settaggi_id = $_POST['gruppo'];
      $Obj->chiave_ext         = $_POST['chiave_ext'];
      $Obj->type               = $_POST['type'];
      $Obj->descrizione        = $_POST['descrizione'];
      if(Utility::isPositiveInt($_POST['super_admin'])) {
        $Obj->super_admin = 1;
      } else {
        $Obj->super_admin = 0;
      }
      $Obj->editabile = 1;
      if($Obj->fields['type'] == 'boolean') {
        if(Utility::isPositiveInt($_POST['valore_booleano'])) {
          $Obj->valore = 1;
        } else {
          $Obj->valore = 0;
        }
      } else {
        $Obj->valore = $_POST['valore_testuale'];
      }

      if($Obj->isValid()) {
        $res = $Obj->save();

        $ajaxReturn['result'] = 1;

        $Obj = new Settings($mainObjOptions);
        $Obj->generaCostanti();

      } else {
        $ajaxReturn['result'] = 0;
        $ajaxReturn['error']  = $Obj->getErrors();
      }

    }


    break;

  case 'load_element':

    if(Utility::isPositiveInt($_POST['id'])) {

      $Obj = new Settings($mainObjOptions);

      $Obj = $Obj->getById($_POST['id']);

      if($Obj) {
        $ajaxReturn['result'] = 1;
        $ajaxReturn['data']   = $Obj->fields;
      } else {
        $ajaxReturn['result'] = 0;
        $ajaxReturn['error']  = Traduzioni::getLang('default', 'UNDEFINED_ERROR');
      }
    } else {

      $ajaxReturn['result'] = 0;
      if(isset($operator) && $operator->isSuperAdmin()) {
        $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' ('.__FILE__.','.__LINE__.')';
      } else {
        $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' (Error: '.__LINE__.')';
      }
    }

    break;

  case 'del':

    if($operator->fields['super_admin']) {

      if(Utility::isPositiveInt($_POST['id'])) {

        $Obj = new Settings($mainObjOptions);

        if(Utility::isPositiveInt($_POST['id'])) {
          $Obj = $Obj->getById($_POST['id']);
          if($Obj) {
            $Obj->delete();
            $Obj->generaCostanti();
            $ajaxReturn['result'] = 1;
          } else {
            $ajaxReturn['result'] = 0;
            $ajaxReturn['error']  = Traduzioni::getLang('default', 'UNDEFINED_ERROR');
          }
        } else {
          $ajaxReturn['result'] = 0;
          $ajaxReturn['error']  = Traduzioni::getLang('default', 'BAD_PARAMS');
        }

      } else {
        $ajaxReturn['result'] = 0;
        $ajaxReturn['error']  = Traduzioni::getLang('default', 'BAD_PARAMS');

      }

    } else {
      $ajaxReturn['result'] = 0;
      $ajaxReturn['error']  = Traduzioni::getLang('default', 'NOT_AUTH');
    }

    break;

  case 'save':

    $Obj = new Settings($mainObjOptions);

    $filters = [];

    $filter_record              = [];
    $filter_record['chiave']    = 'editabile';
    $filter_record['operatore'] = '=';
    $filter_record['valore']    = '1';

    $filters[] = $filter_record;

    if(!$operator->fields['super_admin']) {

      $filter_record           = [];
      $filter_record['chiave'] = 'super_admin';
    }

    $opts              = [];
    $opts['sortField'] = 'super_admin';
    $opts['sortOrder'] = 'desc';
    $opts['filters']   = $filters;
    $opts['operatore'] = 'AND';

    $ObjList = $Obj->getlist($opts);

    foreach($ObjList as $Obj) {
      if($Obj->fields['type'] == 'boolean') {
        if(isset($_POST[$Obj->fields['chiave']]) && $_POST[$Obj->fields['chiave']]) {
          $Obj->valore = 1;
        } else {
          $Obj->valore = 0;
        }
      } else {
        if(isset($_POST[$Obj->fields['chiave']])) {
          $Obj->valore = $_POST[$Obj->fields['chiave']];
        }
      }

      $Obj->save();

    }

    $Obj = new Settings($mainObjOptions);

    $Obj->generaCostanti();

    $ajaxReturn['result'] = 1;

    break;

  case '':

    $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE'));

    // GRUPPI SETTAGGI

    $options                  = [];
    $options['tableFilename'] = 'gruppi_settaggi';
    $options['debug']         = 0;

    $Obj = new Dba($options);

    $opts              = [];
    $opts['sortField'] = 'ordine';
    $opts['sortOrder'] = 'asc';
    $opts['debug']     = 0;

    $list = $Obj->getlist($opts);

    $gruppi = [];

    $gruppi_opt    = [];
    $gruppi_opt[0] = Traduzioni::getLang('default', 'SELECT_ONE');

    foreach($list as $Obj) {
      $record                     = [];
      $record['nome']             = $Obj->fields['nome_gruppo'];
      $record['descrizione']      = $Obj->fields['descrizione'];
      $gruppi[$Obj->fields['id']] = $record;

      $gruppi_opt[$Obj->fields['id']] = $Obj->fields['nome_gruppo'];
    }

    $smarty->assign('gruppi', $gruppi);
    $smarty->assign('gruppi_opt', $gruppi_opt);

    $settings = new Settings($mainObjOptions);

    $filters = [];

    $filter_record              = [];
    $filter_record['chiave']    = 'editabile';
    $filter_record['operatore'] = '=';
    $filter_record['valore']    = '1';

    $filters[] = $filter_record;

    if(!$operator->fields['super_admin']) {

      $filter_record              = [];
      $filter_record['chiave']    = 'super_admin';
      $filter_record['operatore'] = '!=';
      $filter_record['valore']    = '1';

      $filters[] = $filter_record;

    }

    $opts              = [];
    $opts['filters']   = $filters;
    $opts['operatore'] = 'AND';
    $opts['sortField'] = 'super_admin asc, ordine ASC, chiave_ext';
    $opts['sortOrder'] = 'asc';
    $opts['debug']     = 0;

    $settings = $settings->getlist($opts);

    $list_settings = [];

    foreach($settings as $sett) {
      if(!isset($list_settings[$sett->fields['gruppo_settaggi_id']])) {
        $list_settings[$sett->fields['gruppo_settaggi_id']] = [];
      }
      $list_settings[$sett->fields['gruppo_settaggi_id']][] = $sett->fields;
    }

    $smarty->assign('settings', $list_settings);

    /** SUPER_ADMIN OPTIONS - INIZIO **/

    $sa_options = [];

    $record                 = [];
    $record['inp_id']       = 'super_admin';
    $record['inp_name']     = 'super_admin';
    $record['inp_class']    = '';
    $record['lbl_class']    = 'radiolbl';
    $record['inp_value']    = '1';
    $record['etichetta']    = '';
    $record['inp_selected'] = false;

    $sa_options[] = $record;

    $smarty->assign('sa_options', $sa_options);

    /** SUPER_ADMIN OPTIONS - FINE   **/

    /** VALORE OPTIONS - INIZIO **/

    $val_options = [];

    $val_options[0] = Traduzioni::getLang('default', 'NO_ANSWER');
    $val_options[1] = Traduzioni::getLang('default', 'SI_ANSWER');

    $smarty->assign('val_options', $val_options);

    /** VALORE OPTIONS - FINE   **/

    $tipo_input = [];

    $tipo_input['text']    = Traduzioni::getLang($module_name, 'TESTO');
    $tipo_input['boolean'] = Traduzioni::getLang($module_name, 'BOOLEAN');

    $smarty->assign('tipo_input', $tipo_input);

    if($operator->isSuperAdmin()) {
      $headerButtons[] = ['text'       => Traduzioni::getLang('default', 'NEW'),
                          'icon'       => 'file-o',
                          'attributes' => ['class' => 'btn btn-primary',
                                           'id'    => 'newbtn']];

    }
    $headerButtons[] = $BUTTONS['btnSave'];

    $footerButtons[] = $BUTTONS['btnSave'];

}
