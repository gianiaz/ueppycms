<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (08/06/16, 15.49)                                                                      **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
use Ueppy\core\Traduzioni;
use Ueppy\utils\Utility;
use Ueppy\core\Module;
use Ueppy\core\ModuloDinamico;
use Ueppy\core\Ueppy;

switch($act) {

  case 'insert':

    if(isset($_POST['data']) && $_POST['data']) {

      $data = json_decode($_POST['data'], true);
      if($data) {

        // sezioni header e footer
        $tpls = [];
        $tpls = array_merge($tpls, glob(DEFAULT_TPL.'*.tpl'));
        $tpls = array_merge($tpls, glob(SECTIONS_DIR.'*.tpl'));

        $found = false;

        foreach($tpls as $tpl) {
          if(Utility::withoutExtension(basename($tpl)) == $data['template']) {
            $found = basename($tpl);
            break;
          }
        }

        if($found) {

          $options                  = [];
          $options['tableFilename'] = 'modules';

          $ModuleObj = new Module($options);

          $ajaxReturn['result'] = 1;

          if(is_array($data['blocchi'])) {
            foreach($data['blocchi'] as $blocco) {
              if(is_array($blocco['moduli'])) {

                $ModuleObj->deleteBlocco($blocco['nome'], $found);

                foreach($blocco['moduli'] as $ordine => $widget) {

                  $options                  = [];
                  $options['tableFilename'] = 'modules';

                  $ModuleObj = new Module($options);

                  $ModuleObj->modulo     = $widget['nome'];
                  $ModuleObj->principale = $blocco['principale'];
                  $ModuleObj->posizione  = $blocco['nome'];
                  $ModuleObj->view       = '';
                  if($widget['nome'] != 'main') {
                    $ModuleObj->view = $widget['vista'];
                  }
                  $ModuleObj->ordine   = $ordine;
                  $ModuleObj->istanza  = $widget['istanza'];
                  $ModuleObj->template = $found;

                  $ModuleObj->save();
                }
              }
            }
          }

        } else {
          $ajaxReturn['result'] = 0;
          if(isset($operator) && $operator->isSuperAdmin()) {
            $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' ('.__FILE__.','.__LINE__.')';
          } else {
            $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' (Error: '.__LINE__.')';
          }
        }


      } else {
        $ajaxReturn['result'] = 0;
        if(isset($operator) && $operator->isSuperAdmin()) {
          $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' ('.__FILE__.','.__LINE__.')';
        } else {
          $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' (Error: '.__LINE__.')';
        }

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

  case 'load_module_config':
    if(isset($_POST['istanza']) && $_POST['istanza']) {

      $istanza = basename($_POST['istanza']);

      $options                  = [];
      $options['tableFilename'] = 'modules';

      $ModuleObj = new Module($options);

      $data = $ModuleObj->loadWidgetConfig($istanza);

      $ajaxReturn['data']   = $data;
      $ajaxReturn['result'] = 1;

    } else {
      $ajaxReturn['result'] = 0;
      if(isset($operator) && $operator->isSuperAdmin()) {
        $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' ('.__FILE__.','.__LINE__.')';
      } else {
        $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' (Error: '.__LINE__.')';
      }
    }
    break;

  case 'save_config':

    if(isset($_POST['istanza']) && $_POST['istanza']) {
      $istanza = basename($_POST['istanza']);

      $data = $_POST;
      unset($data[$_POST['istanza']]);

      $confFile = DOC_ROOT.REL_ROOT.UPLOAD.'widgets/'.$istanza.'.json';

      file_put_contents($confFile, json_encode($data));

      $ajaxReturn['result'] = 1;

    } else {
      $ajaxReturn['result'] = 0;
      if(isset($operator) && $operator->isSuperAdmin()) {
        $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' ('.__FILE__.','.__LINE__.')';
      } else {
        $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' (Error: '.__LINE__.')';
      }
    }


    break;

  case 'load_module_data':

    if($_POST['nome']) {

      $options                  = [];
      $options['tableFilename'] = 'modules';
      $ModuleObj                = new Module($options);

      $ajaxReturn['result']         = 1;
      $ajaxReturn['data']['modulo'] = $ModuleObj->createModule($_POST['nome']);
      if(is_numeric($_POST['dyn'])) {
        $ajaxReturn['data']['modulo']['dyn'] = $_POST['dyn'];
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

  case 'load_template_data':

    if($_POST['template']) {

      // sezioni header e footer
      $tpls = [];
      $tpls = array_merge($tpls, glob(DEFAULT_TPL.'*.tpl'));
      $tpls = array_merge($tpls, glob(SECTIONS_DIR.'*.tpl'));

      $found = false;

      foreach($tpls as $tpl) {
        if(Utility::withoutExtension(basename($tpl)) == $_POST['template']) {
          $found = $tpl;
          break;
        }
      }

      if($found) {

        $templateMarkup = file_get_contents($found);

        $re  = '/<.*data-upy.*>/';
        $re2 = '/data-([^\=]+)\=\"([^\"]+)\"/';

        $blocchi = [];

        if(preg_match_all($re, $templateMarkup, $m)) {
          foreach($m[0] as $divTrovato) {
            if(preg_match_all($re2, $divTrovato, $m2)) {
              $blocco               = [];
              $blocco['nome']       = $m2[2][0];
              $blocco['principale'] = $m2[2][1] == 'main';
              if($m2[2][1] == 'side') {
                $blocco['typeDescr'] = Traduzioni::getLang($module_name, 'BLOCCO_COMUNE');
              } else {
                $blocco['typeDescr'] = Traduzioni::getLang($module_name, 'BLOCCO_PRINCIPALE');
              }
              $blocchi[] = $blocco;
            }

          }
        }

        $options                  = [];
        $options['tableFilename'] = 'modules';
        $ModuleObj                = new Module($options);

        $data            = $ModuleObj->getDisponibili(basename($found));
        $data['blocchi'] = $blocchi;

        $ajaxReturn['result'] = 1;
        $ajaxReturn['data']   = $data;

      } else {
        $ajaxReturn['result'] = 0;
        if(isset($operator) && $operator->isSuperAdmin()) {
          $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' ('.__FILE__.','.__LINE__.')';
        } else {
          $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' (Error: '.__LINE__.')';
        }
      }

      $ajaxReturn['result'] = 1;

    } else {
      $ajaxReturn['result'] = 0;
      if(isset($operator) && $operator->isSuperAdmin()) {
        $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' ('.__FILE__.','.__LINE__.')';
      } else {
        $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' (Error: '.__LINE__.')';
      }
    }

    break;

  case 'get_moduli_dinamici_list':

    $options                  = [];
    $options['tableFilename'] = 'moduli_dinamici';

    $ModuloDinamicoObj = new ModuloDinamico($options);

    $ModuloDinamicoObjList = $ModuloDinamicoObj->getlist();

    $data = [];

    foreach($ModuloDinamicoObjList as $ModuloDinamicoObj) {

      $record                 = [];
      $record['id']           = $ModuloDinamicoObj->id;
      $record['nome']         = $ModuloDinamicoObj->nome;
      $record['cancellabile'] = $ModuloDinamicoObj->cancellabile();

      $data[] = $record;

    }

    $ajaxReturn           = [];
    $ajaxReturn['result'] = 1;
    $ajaxReturn['data']   = $data;


    break;

  case 'save_widget':

    if($_POST) {

      $options                  = [];
      $options['tableFilename'] = 'moduli_dinamici';
      $options['forceAllLang']  = true;

      $ModuloDinamicoObj = new ModuloDinamico($options);

      if(Utility::isPositiveInt($_POST['id'])) {
        $opts              = ['forceAllLang' => 1];
        $ModuloDinamicoObj = $ModuloDinamicoObj->getById($_POST['id'], $opts);
      }

      if($ModuloDinamicoObj) {

        $ModuloDinamicoObj->nome = $_POST['nome'];

        foreach($langs as $l) {
          $ModuloDinamicoObj->$l = ['testo' => $_POST[$l]['testo']];
        }

        if($ModuloDinamicoObj->isValid()) {

          $ModuloDinamicoObj->save();

          $ajaxReturn['result'] = 1;

        } else {

          $opts['glue']         = '<br />';
          $ajaxReturn['result'] = 0;
          $ajaxReturn['error']  = $Obj->getErrors($opts);

        }


      } else {
        $ajaxReturn['result'] = 0;
        if(isset($operator) && $operator->isSuperAdmin()) {
          $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' ('.__FILE__.','.__LINE__.')';
        } else {
          $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' (Error: '.__LINE__.')';
        }
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

  case  'del_widget':

    if(Utility::isPositiveInt($_POST['id'])) {

      $options                  = [];
      $options['tableFilename'] = 'moduli_dinamici';

      $ModuloDinamicoObj = new ModuloDinamico($options);

      $ModuloDinamicoObj = $ModuloDinamicoObj->getById($_POST['id']);

      if($ModuloDinamicoObj) {

        $ModuloDinamicoObj->delete();

        $ajaxReturn['result'] = 1;

      } else {
        $ajaxReturn['result'] = 0;
        if(isset($operator) && $operator->isSuperAdmin()) {
          $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' ('.__FILE__.','.__LINE__.')';
        } else {
          $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' (Error: '.__LINE__.')';
        }
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

  case 'load_widget':
    if(Utility::isPositiveInt($_POST['id'])) {

      $options                  = [];
      $options['tableFilename'] = 'moduli_dinamici';

      $ModuloDinamicoObj = new ModuloDinamico($options);

      $opts = ['forceAllLang' => 1];

      $ModuloDinamicoObj = $ModuloDinamicoObj->getById($_POST['id'], $opts);

      if($ModuloDinamicoObj) {

        $ajaxReturn['result'] = 1;
        $ajaxReturn['data']   = $ModuloDinamicoObj->toArray();

      } else {
        $ajaxReturn['result'] = 0;
        if(isset($operator) && $operator->isSuperAdmin()) {
          $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' ('.__FILE__.','.__LINE__.')';
        } else {
          $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' (Error: '.__LINE__.')';
        }
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

  case '':

    // scroll-to
    $pathJS[] = DOC_ROOT.REL_ROOT.'bower_components/jquery-scrollto/src/documents/lib/jquery-scrollto.js';

    // sortable/draggable
    $pathJS[] = DOC_ROOT.REL_ROOT.'bower_components/jquery-ui/ui/core.js';
    $pathJS[] = DOC_ROOT.REL_ROOT.'bower_components/jquery-ui/ui/widget.js';
    $pathJS[] = DOC_ROOT.REL_ROOT.'bower_components/jquery-ui/ui/mouse.js';
    $pathJS[] = DOC_ROOT.REL_ROOT.'bower_components/jquery-ui/ui/sortable.js';
    $pathJS[] = DOC_ROOT.REL_ROOT.'bower_components/jquery-ui/ui/draggable.js';

    // include i fogli di stile e i js per l'editor tinymce
    Ueppy::includeTinymce();

    $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE'));

    // sezioni header e footer
    $tpl1 = glob(DEFAULT_TPL.'*.tpl');

// altre sezioni
    $tpl2 = glob(SECTIONS_DIR.'*.tpl');

    $defaults = [];

    foreach($tpl1 as $val) {
      $name            = Utility::withoutExtension(basename($val));
      $defaults[$name] = ucfirst(str_replace('_', ' ', $name));
    }
    $sezioni = [];
    foreach($tpl2 as $val) {
      $name           = Utility::withoutExtension(basename($val));
      $sezioni[$name] = ucfirst($name);
    }

    ksort($sezioni);
    $smarty->assign('sezioni', $sezioni);
    $smarty->assign('defaults', $defaults);


    $BUTTONS['widgets'] = ['text'       => Traduzioni::getLang($module_name, 'WIDGET_DINAMICI'),
                           'icon'       => 'columns',
                           'attributes' => ['class' => 'btn btn-info',
                                            'id'    => 'widgets']];

    $BUTTONS['btnSave'] = ['text'       => Traduzioni::getLang('default', 'SALVA'),
                           'icon'       => 'floppy-o',
                           'attributes' => ['class' => 'btn btn-success saveTemplate']];

    if($operator->isSuperAdmin()) {
      $headerButtons[] = $BUTTONS['widgets'];
    }
    $headerButtons[] = $BUTTONS['btnSave'];
    $footerButtons[] = $BUTTONS['btnSave'];

    break;

  default:
    $urlParams = 'cmd/'.$cmd;

    header('Location:'.$lm->get($urlParams));

    die;
    break;

}

