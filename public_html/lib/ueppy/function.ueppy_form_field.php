<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**********************************************************************************************************/
/** v.1.00 (28/09/2015)                                                                                  **/
/** - Versione stabile                                                                                   **/
/**********************************************************************************************************/
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {ueppy_forms_field} function plugin
 *
 * File:       function.ueppyformfield.php<br>
 * Type:       function<br>
 * Name:       ueppy_form_field<br>
 * Date:       19.Ago.2011<br>
 * Purpose:    Creazione Forms
 * Input:<br>
 *           - inp_*      (optional) - Attributi del campo input
 *           - lbl_*      (optional) - Attributi dell'elemento label
 *           - i_*        (optional) - Attributi dell'elemento i dell'help.
 *           - validate   (optional) - Regole del plugin validate separate da pipe "|".
 *           - etichetta  (required) - modulo.CHIAVE etichetta
 *           - type       (optional) - "text"/"checkboxes"/"select"/"textarea"", default "text"
 *           - options    (optional) - associative array
 *           - checked    (optional) - array default not set
 *           - restricted (optional) - stringa contenente i caratteri accettati separati da pipe "\" utilizzando le regex
 *           - help       (optional) - true/false
 */

use Ueppy\utils\Utility;
use Ueppy\core\Traduzioni;

function smarty_function_ueppy_form_field($params, &$smarty) {

  $p                = [];
  $p['level']       = 'ADVANCED';
  $p['etichetta']   = 'default.NON_FORNITA';
  $p['customLabel'] = '';
  $p['help']        = 1;
  $p['debug']       = false;
  $p['readonly']    = 0;
  $p['restricted']  = '';
  $p['required']    = 0;
  $p['type']        = 'text';
  $p['required']    = false;
  $p['container']   = 'div.form-group';

  $p = array_replace_recursive($p, $params);


  // per compatibilità con la sintassi smarty utilizzata da tempo
  $p['inp_type'] = $p['type'];
  unset($p['type']);

  // per compatibilità con la sintassi smarty utilizzata da tempo
  $p['inp_required'] = $p['required'];
  unset($p['required']);

  // per compatibilità con la sintassi smarty utilizzata da tempo
  $p['inp_readonly'] = $p['readonly'];
  unset($p['readonly']);

  // per compatibilità con la sintassi smarty utilizzata da tempo
  $p['lbl_etichetta'] = $p['etichetta'];
  unset($p['etichetta']);

  $p['lbl_etichetta_custom'] = $p['customLabel'];


  // per compatibilità con la sintassi smarty utilizzata da tempo

  $attr = [];

  $attr['inp'] = [];
  $attr['lbl'] = [];
  $attr['i']   = [];

  if(!isset($attr['inp']['data'])) {
    $attr['inp']['data'] = [];
  }

  $re = '/^(inp|lbl|i)\_(.*)/';

  $re2 = '/^(inp|lbl|i)_data_(.*)/';

  $options = [];

  foreach($p as $nomeParametro => $valoreParametro) {
    if(preg_match($re, $nomeParametro, $m)) {
      if(preg_match($re2, $nomeParametro, $m2)) {
        $attr[$m[1]]['data'][$m2[2]] = $valoreParametro;
      } else {
        $attr[$m[1]][$m[2]] = $valoreParametro;
      }
    } else {
      $options[$nomeParametro] = $valoreParametro;
    }
  }

  if(!isset($attr['inp']['class'])) {
    $attr['inp']['class'] = [];
  } else {
    $attr['inp']['class'] = [$attr['inp']['class']];
  }

  if(!isset($attr['i']['class'])) {
    $attr['i']['class'] = [];
  } else {
    $attr['i']['class'] = [$attr['i']['class']];
  }

  // valori di default per il tag di help

  list($module_lang, $key_lang) = explode('.', $attr['lbl']['etichetta']);

  if($p['help']) {

    $attr['i']['class'][]           = 'fa fa-question-circle cmsHelp text-info';
    $attr['i']['data']['toggle']    = 'tooltip';
    $attr['i']['data']['placement'] = 'top';
    $attr['i']['data']['title']     = Traduzioni::getLang($module_lang, strtoupper($key_lang).'_HELP');

  }

  if(!isset($attr['lbl']['class'])) {
    $attr['lbl']['class'] = [];
  } else {
    $attr['lbl']['class'] = [$attr['lbl']['class']];
  }

  $attr['lbl']['class'][] = 'control-label';

  // classe si default per tutti
  $attr['inp']['class'][] = 'form-control';

  // aggiunta di classi personalizzate
  switch($attr['inp']['type']) {
    case 'password':
    case 'text':
    case 'textarea':
    case 'select':
      break;
    case 'radio':
      $attr['inp']['class'][] = 'ueppyrdio';
      break;
    case 'checkbox':
      $attr['inp']['class'][] = 'ueppychkb';
      break;
    case 'imgedit':
    case 'fileedit':
      if(!isset($p['inp_id']) || !$p['inp_id']) {
        $p['inp']['id'] = $p['inp']['name'];
      }
      break;
  }

  if(isset($attr['inp']['required']) && $attr['inp']['required']) {
    $attr['lbl']['class'][] = 'req';
  }

  if(isset($options['restricted']) && $options['restricted']) {
    $attr['inp']['class'][]            = 'restricted';
    $attr['inp']['data']['restricted'] = $options['restricted'];
    unset($options['restricted']);
  }

  if(isset($attr['inp']['id'])) {
    $attr['lbl']['for'] = $attr['inp']['id'];
  }


  if((!isset($attr['inp']['name']) || (!$attr['inp']['name'])) && (!isset($attr['inp']['options']) || !is_array($attr['inp']['options']))) {
    Utility::pre('Errore: Manca il name o le options'."\n".print_r($attr['inp'], true));
    die;
  }


  if(isset($options['placeholder'])) {
    if($options['placeholder'] === 1 || $options['placeholder'] === true) {
      if(isset($attr['lbl']['etichetta']) && $attr['lbl']['etichetta']) {
        list($module_lang, $key_lang) = explode('.', $attr['lbl']['etichetta']);
        $attr['inp']['placeholder'] = Traduzioni::getLang($module_lang, $key_lang.'_PLACEHOLDER');
      }
    } else {
      $attr['inp']['placeholder'] = $options['placeholder'];
    }
    unset($options['placeholder']);
  }

  list($module_lang, $key_lang) = explode('.', $attr['lbl']['etichetta']);
  $attr['lbl']['etichetta'] = Traduzioni::getLang($module_lang, $key_lang);

  if($options['debug']) {
    Utility::pre($attr);
    Utility::pre($options);
  }


  return smarty_function_ueppy_form_field_output($attr, $options);

}

function smarty_function_ueppy_form_field_output($attr, $p) {

  $_output = "\n";

  $tabs = 0;

  if(!in_array($attr['inp']['type'], ['filedit', 'imgedit'])) {

    if($p['container']) {

      preg_match_all('/([^\#\.]*)\#?([^\#\.]*)\.?([^\#\.]*)/', $p['container'], $m);

      $_output .= '<'.$m[1][0];

      if($m[3][0]) {
        $_output .= ' class="'.$m[3][0].'"';
      }

      if($m[2][0]) {
        $_output .= ' id="'.$m[2][0].'"';
      }

      $_output .= '>';

      $_output .= "\n";
      $tabs++;
      $_output .= str_repeat("\t", $tabs);
    }

    // help
    if($p['help']) {
      $_output .= '<i ';
      foreach($attr['i'] as $attributo => $valore) {
        switch($attributo) {
          case 'data':
            foreach($valore as $nomeData => $valoreData) {
              $re = '/^[a-z]+\.[A-Z0-9\_]+$/';
              if(preg_match($re, $valoreData)) {
                list($modulo, $chiave) = explode('.', $valoreData);
                $valoreData = Traduzioni::getLang($modulo, $chiave);
              }
              $_output .= 'data-'.$nomeData.'="'.$valoreData.'" ';
            }
            break;
          default:
            if($valore) {
              if(is_array($valore)) {
                $_output .= $attributo.'="'.implode(' ', $valore).'" ';
              } else {
                $_output .= $attributo.'="'.$valore.'" ';
              }
            }
            break;
        }
      }
      $_output = trim($_output);
      $_output .= '></i>';
    }

    // label
    $_output .= '<label ';
    foreach($attr['lbl'] as $attributo => $valore) {
      switch($attributo) {
        case 'etichetta':
        case 'etichetta_custom':
          break;
        case 'data':
          foreach($valore as $nomeData => $valoreData) {
            $re = '/^[a-z]+\.[A-Z0-9\_]+$/';
            if(preg_match($re, $valoreData)) {
              list($modulo, $chiave) = explode('.', $valoreData);
              $valoreData = Traduzioni::getLang($modulo, $chiave);
            }
            $_output .= 'data-'.$nomeData.'="'.$valoreData.'" ';
          }
          break;
        default:
          if($valore) {
            if(is_array($valore)) {
              $_output .= $attributo.'="'.implode(' ', $valore).'" ';
            } else {
              $_output .= $attributo.'="'.$valore.'" ';
            }
          }
          break;
      }
    }
    $_output = trim($_output);
    $_output .= '>';
    if($attr['lbl']['etichetta_custom']) {
      $_output .= $attr['lbl']['etichetta_custom'].'</label>';
    } else {
      $_output .= $attr['lbl']['etichetta'].'</label>';
    }
    $_output .= "\n";
    $_output .= str_repeat("\t", $tabs);
  }

  switch($attr['inp']['type']) {

    case 'text':
    case 'file':
    case 'password':

      // input
      $_output .= '<input ';
      foreach($attr['inp'] as $attributo => $valore) {
        switch($attributo) {
          case 'required':
            if($valore) $_output .= 'required ';
            break;
          case 'readonly':
            if($valore) $_output .= 'readonly ';
            break;
          case 'etichetta':
            break;
          case 'data':
            foreach($valore as $nomeData => $valoreData) {
              $re = '/^[a-z]+\.[A-Z0-9\_]+$/';
              if(preg_match($re, $valoreData)) {
                list($modulo, $chiave) = explode('.', $valoreData);
                $valoreData = Traduzioni::getLang($modulo, $chiave);
              }
              $_output .= 'data-'.$nomeData.'="'.$valoreData.'" ';
            }
            break;
          default:
            if(is_array($valore)) {
              if(count($valore)) {
                $_output .= $attributo.'="'.implode(' ', $valore).'" ';
              }
            } else {
              $_output .= $attributo.'="'.$valore.'" ';
            }
            break;
        }
      }
      $_output = trim($_output);
      $_output .= '/>';
      $_output .= "\n";
      $tabs--;
      $_output .= str_repeat("\t", $tabs);
      break;

    case 'radio':
    case 'checkbox':

      $_output .= '<ul class="choices">';

      foreach($attr['inp']['options'] as $_k => $record) {

        $_output .= '<li>';
        $parametriElemento        = [];
        $parametriElemento['inp'] = [];
        $parametriElemento['lbl'] = [];

        $re = '/^(inp|lbl)\_(.*)/';

        $re2 = '/^(inp|lbl)_data_(.*)/';


        foreach($record as $nomeParametro => $valoreParametro) {
          if(preg_match($re, $nomeParametro, $m)) {
            if(preg_match($re2, $nomeParametro, $m2)) {
              $parametriElemento[$m[1]]['data'][$m2[2]] = $valoreParametro;
            } else {
              $parametriElemento[$m[1]][$m[2]] = $valoreParametro;
            }
          }
        }

        if(!isset($parametriElemento['lbl']['class'])) {
          $parametriElemento['lbl']['class'] = [];
        } else {
          $parametriElemento['lbl']['class'] = [$parametriElemento['lbl']['class']];
        }

        $parametriElemento['lbl']['class'][] = $attr['inp']['type'].'-inline';


        if(!isset($record['etichetta']) || !$record['etichetta']) {
          //$record['etichetta'] = $attr['lbl']['etichetta'];
          $record['etichetta'] = '&nbsp;';
        }

        $parametriElemento['lbl']['etichetta'] = $record['etichetta'];


        // label
        $_output .= '<label ';
        foreach($parametriElemento['lbl'] as $attributo => $valore) {
          switch($attributo) {
            case 'etichetta':
              break;
            case 'data':
              foreach($valore as $nomeData => $valoreData) {
                $re = '/^[a-z]+\.[A-Z0-9\_]+$/';
                if(preg_match($re, $valoreData)) {
                  list($modulo, $chiave) = explode('.', $valoreData);
                  $valoreData = Traduzioni::getLang($modulo, $chiave);
                }
                $_output .= 'data-'.$nomeData.'="'.$valoreData.'" ';
              }
              break;
            default:
              if($valore) {
                if(is_array($valore)) {
                  $_output .= $attributo.'="'.trim(implode(' ', $valore)).'" ';
                } else {
                  $_output .= $attributo.'="'.$valore.'" ';
                }
              }
              break;
          }
        }
        $_output = trim($_output);
        $_output .= '>';

        // input
        $_output .= '<input type="'.$attr['inp']['type'].'"';
        foreach($parametriElemento['inp'] as $attributo => $valore) {
          switch($attributo) {
            case 'etichetta':
            case 'options':
              break;
            case 'data':
              foreach($valore as $nomeData => $valoreData) {
                $re = '/^[a-z]+\.[A-Z0-9\_]+$/';
                if(preg_match($re, $valoreData)) {
                  list($modulo, $chiave) = explode('.', $valoreData);
                  $valoreData = Traduzioni::getLang($modulo, $chiave);
                }
                $_output .= 'data-'.$nomeData.'="'.$valoreData.'" ';
              }
              break;
            case 'value':
              $_output .= 'value="'.$valore.'" ';
              if(isset($attr['inp']['value'])) {
                if(is_array($attr['inp']['value'])) {
                  foreach($attr['inp']['value'] as $v) {
                    if($v == $valore) {
                      $_output .= 'checked ';
                      break;
                    }
                  }
                } else {
                  if(isset($attr['inp']['value']) && $attr['inp']['value'] == $valore) {
                    $_output .= 'checked ';
                  }
                }
              }
              break;

            default:
              if(is_array($valore)) {
                if(count($valore)) {
                  $_output .= $attributo.'="'.implode(' ', $valore).'" ';
                }
              } else {
                if($valore) {
                  $_output .= $attributo.'="'.$valore.'" ';
                }
              }
              break;
          }
        }
        $_output = trim($_output);
        $_output .= '/>';

        $_output .= $parametriElemento['lbl']['etichetta'].'</label>';
        $_output .= "\n";
        $_output .= str_repeat("\t", $tabs);

        $_output .= '</label>';
        $_output .= '</li>';
      }

      $_output .= '</ul>';

      break;

    case 'textarea':
      $_output .= '<textarea ';
      foreach($attr['inp'] as $attributo => $valore) {
        switch($attributo) {
          case 'required':
            if($valore) $_output .= 'required ';
            break;
          case 'readonly':
            if($valore) $_output .= 'readonly ';
            break;
          case 'etichetta':
            break;
          case 'data':
            foreach($valore as $nomeData => $valoreData) {
              $re = '/^[a-z]+\.[A-Z0-9\_]+$/';
              if(preg_match($re, $valoreData)) {
                list($modulo, $chiave) = explode('.', $valoreData);
                $valoreData = Traduzioni::getLang($modulo, $chiave);
              }
              $_output .= 'data-'.$nomeData.'="'.$valoreData.'" ';
            }
            break;
          case 'options':
          case 'value':
          case 'type':
            break;
          default:
            if(is_array($valore)) {
              if(count($valore)) {
                $_output .= $attributo.'="'.implode(' ', $valore).'" ';
              }
            } else {
              $_output .= $attributo.'="'.$valore.'" ';
            }
            break;
        }
      }
      $_output = trim($_output);
      $_output .= '>';
      $_output .= $attr['inp']['value'];
      $_output .= '</textarea>';
      break;

    case 'select':
      $_output .= '<select ';
      foreach($attr['inp'] as $attributo => $valore) {
        switch($attributo) {
          case 'required':
            if($valore) $_output .= 'required ';
            break;
          case 'readonly':
            if($valore) $_output .= 'readonly disabled ';
            break;
          case 'etichetta':
            break;
          case 'data':
            foreach($valore as $nomeData => $valoreData) {
              $re = '/^[a-z]+\.[A-Z0-9\_]+$/';
              if(preg_match($re, $valoreData)) {
                list($modulo, $chiave) = explode('.', $valoreData);
                $valoreData = Traduzioni::getLang($modulo, $chiave);
              }
              $_output .= 'data-'.$nomeData.'="'.$valoreData.'" ';
            }
            break;
          case 'options':
          case 'value':
          case 'type':
            break;
          default:
            if(is_array($valore)) {
              if(count($valore)) {
                $_output .= $attributo.'="'.implode(' ', $valore).'" ';
              }
            } else {
              $_output .= $attributo.'="'.$valore.'" ';
            }
            break;
        }
      }
      $_output = trim($_output);
      $_output .= '>';
      $tabs++;
      foreach($attr['inp']['options'] as $value => $lbl) {
        if(is_array($lbl)) {

          $_output .= "\n";
          $_output .= str_repeat("\t", $tabs);
          $_output .= '<option';
          foreach($lbl as $k => $v) {
            if($k != 'label') {
              $_output .= ' '.$k.'="'.$v.'"';
            }
          }
          if((is_array($attr['inp']['value']) && in_array($lbl['value'], $attr['inp']['value'])) || ((is_numeric($lbl['value']) && is_numeric($attr['inp']['value']) && $lbl['value'] == $attr['inp']['value']) || ($lbl['value'] === $attr['inp']['value']))) {
            $_output .= ' selected';
          }
          $_output .= '>'.$lbl['label'].'</option>';
        } else {
          $_output .= "\n";
          $_output .= str_repeat("\t", $tabs);
          $_output .= '<option value="'.$value.'"';
          if((is_array($attr['inp']['value']) && in_array($value, $attr['inp']['value'])) || ((is_numeric($value) && is_numeric($attr['inp']['value']) && $value == $attr['inp']['value']) || ($value === $attr['inp']['value']))) {
            //          Utility::pre($value.' == '.$attr['inp']['value'].':'.($value == $attr['inp']['value']));
            $_output .= ' selected';
          }
          $_output .= '>'.$lbl.'</option>';
        }
      }
      $tabs--;
      $_output .= "\n";
      $_output .= str_repeat("\t", $tabs);
      $_output .= '</select>';
      $_output .= "\n";
      break;


    case 'imgedit':
      /*
                  <div class="col-md-8">
            <label>Allegato</label>
          </div>
*/

      $_output .= '<div class="panel panel-default panel-attachment">'; // startp panel
      $_output .= "\n";
      $_output .= '<div class="panel-heading">Dati '.$attr['lbl']['etichetta'].'</div>';
      $_output .= "\n";
      $_output .= '<div class="panel-body" id="filePanel'.$attr['inp']['id'].'">'; // start .panel-body;
      $_output .= '<div class="col-lg-8">';

      $_output .= '<i ';
      foreach($attr['i'] as $attributo => $valore) {
        switch($attributo) {
          case 'data':
            foreach($valore as $nomeData => $valoreData) {
              $re = '/^[a-z]+\.[A-Z0-9\_]+$/';
              if(preg_match($re, $valoreData)) {
                list($modulo, $chiave) = explode('.', $valoreData);
                $valoreData = Traduzioni::getLang($modulo, $chiave);
              }
              $_output .= 'data-'.$nomeData.'="'.$valoreData.'" ';
            }
            break;
          default:
            if($valore) {
              if(is_array($valore)) {
                $_output .= $attributo.'="'.implode(' ', $valore).'" ';
              } else {
                $_output .= $attributo.'="'.$valore.'" ';
              }
            }
            break;
        }
      }
      $_output = trim($_output);
      $_output .= '></i>';

      // label
      $_output .= '<label ';
      foreach($attr['lbl'] as $attributo => $valore) {
        switch($attributo) {
          case 'etichetta':
            break;
          case 'data':
            foreach($valore as $nomeData => $valoreData) {
              $re = '/^[a-z]+\.[A-Z0-9\_]+$/';
              if(preg_match($re, $valoreData)) {
                list($modulo, $chiave) = explode('.', $valoreData);
                $valoreData = Traduzioni::getLang($modulo, $chiave);
              }
              $_output .= 'data-'.$nomeData.'="'.$valoreData.'" ';
            }
            break;
          default:
            if($valore) {
              if(is_array($valore)) {
                $_output .= $attributo.'="'.implode(' ', $valore).'" ';
              } else {
                $_output .= $attributo.'="'.$valore.'" ';
              }
            }
            break;
        }
      }
      $_output = trim($_output);
      $_output .= '>';
      $_output .= $attr['lbl']['etichetta'].'</label>';
      $_output .= "\n";
      $_output .= '<div class="input-group attachment">';
      $_output .= '<input type="hidden" class="action" name="'.$attr['inp']['name'];
      if(strpos($attr['inp']['name'], '[') !== false) {
        $_output .= '[action]';
      } else {
        $_output .= 'action';
      }
      $_output .= '"';
      $_output .= ' value="';
      if($attr['inp']['value']) {
        $_output .= 'keep';
      }
      $_output .= '">';
      $_output .= '<span class="input-group-btn">';
      $_output .= '<span class="btn btn-primary btn-file">';
      $_output .= Traduzioni::getLang('default', 'SFOGLIA');
      $_output .= '<input name="'.$attr['inp']['name'].'" id="'.$attr['inp']['id'].'" type="file" />';
      $_output .= '</span>';
      $_output .= '</span>';
      $_output .= '<input type="text" class="form-control fileFeedback" readonly="" value="';
      if($attr['inp']['value']) {
        $_output .= basename($attr['inp']['value']);
      }
      $_output .= '">';
      $_output .= '</div>';

      /** INIZIO METADATA **/
      if($p['level'] > 10) {

        if($attr['inp']['alt_value'] != -1 || $attr['inp']['title_value'] != -1) {
          $_output .= '<div class="row">';
        }

        if($attr['inp']['alt_value'] != -1) {


          if($attr['inp']['type'] == 'imgedit') {

            $_output .= '<div class="col-lg-6">';

            $_output .= '<div class="form-group">';
            // help
            $_output .= '<i ';
            foreach($attr['i'] as $attributo => $valore) {
              switch($attributo) {
                case 'data':
                  foreach($valore as $nomeData => $valoreData) {
                    if($nomeData == 'title') {
                      $_output .= 'data-title="'.Traduzioni::getLang('default', 'ALT_HELP').'" ';
                    } else {
                      $re = '/^[a-z]+\.[A-Z0-9\_]+$/';
                      if(preg_match($re, $valoreData)) {
                        list($modulo, $chiave) = explode('.', $valoreData);
                        $valoreData = Traduzioni::getLang($modulo, $chiave);
                      }
                      $_output .= 'data-'.$nomeData.'="'.$valoreData.'" ';
                    }
                  }
                  break;
                default:
                  if($valore) {
                    if(is_array($valore)) {
                      $_output .= $attributo.'="'.implode(' ', $valore).'" ';
                    } else {
                      $_output .= $attributo.'="'.$valore.'" ';
                    }
                  }
                  break;
              }
            }
            $_output = trim($_output);
            $_output .= '></i>';

            $_output .= '<label>';
            $_output .= Traduzioni::getLang('default', 'ALT').'</label>';
            $_output .= '<input class="form-control" ';
            if(isset($attr['inp']['id']) && $attr['inp']['id']) {
              $_output .= ' id="'.$attr['inp']['id'].'_alt"';
            }
            if(strpos($attr['inp']['name'], ']') !== false) {
              $_output .= ' name ="'.str_replace(']', '_alt]', $attr['inp']['name']).'"';
            } else {
              $_output .= ' name ="'.$attr['inp']['name'].'_alt"';
            }
            if(!isset($attr['inp']['alt_value'])) {
              $attr['inp']['alt_value'] = '';
            }
            $_output .= ' type="text" value="'.htmlentities($attr['inp']['alt_value'], ENT_QUOTES, 'UTF-8').'"';
            $_output .= '/>';
            $_output .= '</div>';
            $_output .= '</div>';

          }

        }

        if(!isset($attr['inp']['title_value'])) {
          $attr['inp']['title_value'] = '';
        }

        if($attr['inp']['title_value'] != -1) {

          // help
          $_output .= '<div class="col-lg-6">';
          $_output .= '<div class="form-group">';
          $_output .= '<i ';
          foreach($attr['i'] as $attributo => $valore) {
            switch($attributo) {
              case 'data':
                foreach($valore as $nomeData => $valoreData) {
                  if($nomeData == 'title') {
                    $_output .= 'data-title="'.Traduzioni::getLang('default', 'TITLE_HELP').'" ';
                  } else {
                    $re = '/^[a-z]+\.[A-Z0-9\_]+$/';
                    if(preg_match($re, $valoreData)) {
                      list($modulo, $chiave) = explode('.', $valoreData);
                      $valoreData = Traduzioni::getLang($modulo, $chiave);
                    }
                    $_output .= 'data-'.$nomeData.'="'.$valoreData.'" ';
                  }
                }
                break;
              default:
                if($valore) {
                  if(is_array($valore)) {
                    $_output .= $attributo.'="'.implode(' ', $valore).'" ';
                  } else {
                    $_output .= $attributo.'="'.$valore.'" ';
                  }
                }
                break;
            }
          }
          $_output = trim($_output);
          $_output .= '></i>';

          $_output .= '<label>';
          $_output .= Traduzioni::getLang('default', 'TITLE').'</label>';


          $_output .= '<input class="form-control" ';
          if(isset($attr['inp']['id']) && $attr['inp']['id']) {
            $_output .= ' id="'.$attr['inp']['id'].'_title"';
          }
          if(strpos($attr['inp']['name'], ']') !== false) {
            $_output .= ' name ="'.str_replace(']', '_title]', $attr['inp']['name']).'"';
          } else {
            $_output .= ' name ="'.$attr['inp']['name'].'_title"';
          }
          if(!isset($attr['inp']['title_value'])) {
            $attr['inp']['title_value'] = '';
          }
          $_output .= ' type="text" value="'.htmlentities($attr['inp']['title_value'], ENT_QUOTES, 'UTF-8').'"';
          $_output .= '/>';
          $_output .= '</div>';
          $_output .= '</div>';
        }

        if($attr['inp']['alt_value'] != -1 || $attr['inp']['title_value'] != -1) {
          $_output .= '</div>';
        }

      }


      /** FINE METADATA **/

      $_output .= '</div>'; // end lg-8 (quello che contiene gli input)
      $_output .= '<div class="col-lg-4 preview"';
      if(!$attr['inp']['value']) {
        $_output .= 'style="display:none"';
      }
      $_output .= '>';
      if($attr['inp']['type'] == 'imgedit') {
        $_output .= '<img class="img-responsive" src="'.$attr['inp']['value'].'" />';
      }
      $_output .= '<a class="btn btn-danger btn-block delPanelImg">'.Traduzioni::getLang('default', 'DELETE_IMG').'</a>';
      $_output .= '</div>'; // end preview
      $_output .= '</div>'; // end panel-body
      $_output .= '</div>'; // end panel

      break;

    case 'fileedit':
    case 'imgedit1':


      $_output .= '<div class="panel panel-default"><!-- start panel -->';
      $_output .= "\n";
      $_output .= '<div class="panel-heading">Dati '.$attr['lbl']['etichetta'].'</div>';
      $_output .= "\n";
      $_output .= '<div class="panel-body"><!-- start panel-body -->';
      $_output .= '<div class="col-lg-12">';

      // help
      $_output .= '<i ';
      foreach($attr['i'] as $attributo => $valore) {
        switch($attributo) {
          case 'data':
            foreach($valore as $nomeData => $valoreData) {
              $re = '/^[a-z]+\.[A-Z0-9\_]+$/';
              if(preg_match($re, $valoreData)) {
                list($modulo, $chiave) = explode('.', $valoreData);
                $valoreData = Traduzioni::getLang($modulo, $chiave);
              }
              $_output .= 'data-'.$nomeData.'="'.$valoreData.'" ';
            }
            break;
          default:
            if($valore) {
              if(is_array($valore)) {
                $_output .= $attributo.'="'.implode(' ', $valore).'" ';
              } else {
                $_output .= $attributo.'="'.$valore.'" ';
              }
            }
            break;
        }
      }
      $_output = trim($_output);
      $_output .= '></i>';

      // label
      $_output .= '<label ';
      foreach($attr['lbl'] as $attributo => $valore) {
        switch($attributo) {
          case 'etichetta':
            break;
          case 'data':
            foreach($valore as $nomeData => $valoreData) {
              $re = '/^[a-z]+\.[A-Z0-9\_]+$/';
              if(preg_match($re, $valoreData)) {
                list($modulo, $chiave) = explode('.', $valoreData);
                $valoreData = Traduzioni::getLang($modulo, $chiave);
              }
              $_output .= 'data-'.$nomeData.'="'.$valoreData.'" ';
            }
            break;
          default:
            if($valore) {
              if(is_array($valore)) {
                $_output .= $attributo.'="'.implode(' ', $valore).'" ';
              } else {
                $_output .= $attributo.'="'.$valore.'" ';
              }
            }
            break;
        }
      }
      $_output = trim($_output);
      $_output .= '>';
      $_output .= $attr['lbl']['etichetta'].'</label>';
      $_output .= "\n";
      $_output .= '</div>';
      $_output .= str_repeat("\t", $tabs);

      $_output .= "\n";

      if($attr['inp']['value']) {
        $_output .= '<div id="imgEditCont'.$attr['inp']['id'].'" class="row">';
        $_output .= '<div class="col-lg-4">';
        $_output .= "\n";
        $_output .= '<input class="img_action_selection" type="radio" id="keep_'.$attr['inp']['id'].'" name="'.$attr['inp']['name'];
        if(strpos($attr['inp']['name'], '[') !== false) {
          $_output .= '[action]';
        } else {
          $_output .= 'action';
        }
        $_output .= '" value="keep" checked="checked" /> <label style="display:inline" for="keep_'.$attr['inp']['id'].'">';
        if($attr['inp']['type'] == 'imgedit') {
          $_output .= '<img style="max-width:200px" src="'.$attr['inp']['value'].'" />'.Traduzioni::getLang('default', 'KEEP_IMG').'</label>';
        } else {
          $_output .= Traduzioni::getLang('default', 'KEEP_FILE').': ';
          $_output .= '<a href="'.$attr['inp']['value'].'" target="_blank">'.basename($attr['inp']['value']).'</a>';
        }
        $_output .= "\n";
        $_output .= '</div>';
        $_output .= '<div class="col-lg-4">';
        $_output .= '<input class="img_action_selection" type="radio" id="replace_'.$attr['inp']['id'].'" name="'.$attr['inp']['name'];
        if(strpos($attr['inp']['name'], '[') !== false) {
          $_output .= '[action]';
        } else {
          $_output .= 'action';
        }
        $_output .= '" value="replace" /> <label style="display:inline" for="replace_'.$attr['inp']['id'].'">';
        if($attr['inp']['type'] == 'imgedit') {
          $_output .= Traduzioni::getLang('default', 'REPLACE_IMG');
        } else {
          $_output .= Traduzioni::getLang('default', 'REPLACE_FILE');
        }
        $_output .= '</label>';
        $_output .= '<input disabled="disabled" name="'.$attr['inp']['name'].'" id="'.$attr['inp']['id'].'" type="file" />';
        $_output .= '</div>';
        $_output .= '<div class="col-lg-4">';
        $_output .= '<input class="img_action_selection" type="radio" id="del_'.$attr['inp']['id'].'" name="'.$attr['inp']['name'];
        if(strpos($attr['inp']['name'], '[') !== false) {
          $_output .= '[action]';
        } else {
          $_output .= 'action';
        }
        $_output .= '" value="del" /> <label style="display:inline" for="del_'.$attr['inp']['id'].'">';

        if($attr['inp']['type'] == 'imgedit') {
          $_output .= Traduzioni::getLang('default', 'DELETE_IMG');
        } else {
          $_output .= Traduzioni::getLang('default', 'DELETE_FILE');
        }
        $_output .= '</label></div>';
        $_output .= '</div>';
      } else {
        $_output .= "\n";

        $_output .= '<div id="imgEditCont'.$attr['inp']['id'].'" class="row">';
        $_output .= '<div class="col-lg-12">';
        $_output .= '<input type="file" ';
        foreach($attr['inp'] as $attributo => $valore) {
          if(!in_array($attributo, ['etichetta', 'alt_value', 'title_value', 'type', 'class'])) {
            switch($attributo) {
              case 'required':
                if($valore) $_output .= 'required ';
                break;
              case 'readonly':
                if($valore) $_output .= 'readonly ';
                break;
              case 'data':
                foreach($valore as $nomeData => $valoreData) {
                  $re = '/^[a-z]+\.[A-Z0-9\_]+$/';
                  if(preg_match($re, $valoreData)) {
                    list($modulo, $chiave) = explode('.', $valoreData);
                    $valoreData = Traduzioni::getLang($modulo, $chiave);
                  }
                  $_output .= 'data-'.$nomeData.'="'.$valoreData.'" ';
                }
                break;
              default:
                if(is_array($valore)) {
                  if(count($valore)) {
                    $_output .= $attributo.'="'.implode(' ', $valore).'" ';
                  }
                } else {
                  $_output .= $attributo.'="'.$valore.'" ';
                }
                break;
            }
          }
        }
        $_output = trim($_output);
        $_output .= '/>';
        $_output .= "\n";
        $_output .= '</div>';
        $_output .= "\n";
        $_output .= '</div>';
        $_output .= "\n";
      }
      $_output .= "\n";

      /** INIZIO METADATA **/
      if($attr['inp']['alt_value'] != -1) {

        if($p['level'] > 10) {

          if($attr['inp']['type'] == 'imgedit') {

            $_output .= '<div class="col-lg-6">';

            $_output .= '<div class="form-group">';
            // help
            $_output .= '<i ';
            foreach($attr['i'] as $attributo => $valore) {
              switch($attributo) {
                case 'data':
                  foreach($valore as $nomeData => $valoreData) {
                    if($nomeData == 'title') {
                      $_output .= 'data-title="'.Traduzioni::getLang('default', 'ALT_HELP').'" ';
                    } else {
                      $re = '/^[a-z]+\.[A-Z0-9\_]+$/';
                      if(preg_match($re, $valoreData)) {
                        list($modulo, $chiave) = explode('.', $valoreData);
                        $valoreData = Traduzioni::getLang($modulo, $chiave);
                      }
                      $_output .= 'data-'.$nomeData.'="'.$valoreData.'" ';
                    }
                  }
                  break;
                default:
                  if($valore) {
                    if(is_array($valore)) {
                      $_output .= $attributo.'="'.implode(' ', $valore).'" ';
                    } else {
                      $_output .= $attributo.'="'.$valore.'" ';
                    }
                  }
                  break;
              }
            }
            $_output = trim($_output);
            $_output .= '></i>';

            $_output .= '<label>';
            $_output .= Traduzioni::getLang('default', 'ALT').'</label>';
            $_output .= '<input class="form-control" ';
            if(isset($attr['inp']['id']) && $attr['inp']['id']) {
              $_output .= ' id="'.$attr['inp']['id'].'_alt"';
            }
            if(strpos($attr['inp']['name'], ']') !== false) {
              $_output .= ' name ="'.str_replace(']', '_alt]', $attr['inp']['name']).'"';
            } else {
              $_output .= ' name ="'.$attr['inp']['name'].'_alt"';
            }
            if(!isset($attr['inp']['alt_value'])) {
              $attr['inp']['alt_value'] = '';
            }
            $_output .= ' type="text" value="'.htmlentities($attr['inp']['alt_value'], ENT_QUOTES, 'UTF-8').'"';
            $_output .= '/>';
            $_output .= '</div>';
            $_output .= '</div>';

          }

        }

        if(!isset($attr['inp']['title_value'])) {
          $attr['inp']['title_value'] = '';
        }

        if($attr['inp']['title_value'] != -1) {

          if($p['level'] > 10) {
            // help
            $_output .= '<div class="col-lg-6">';
            $_output .= '<div class="form-group">';
            $_output .= '<i ';
            foreach($attr['i'] as $attributo => $valore) {
              switch($attributo) {
                case 'data':
                  foreach($valore as $nomeData => $valoreData) {
                    if($nomeData == 'title') {
                      $_output .= 'data-title="'.Traduzioni::getLang('default', 'TITLE_HELP').'" ';
                    } else {
                      $re = '/^[a-z]+\.[A-Z0-9\_]+$/';
                      if(preg_match($re, $valoreData)) {
                        list($modulo, $chiave) = explode('.', $valoreData);
                        $valoreData = Traduzioni::getLang($modulo, $chiave);
                      }
                      $_output .= 'data-'.$nomeData.'="'.$valoreData.'" ';
                    }
                  }
                  break;
                default:
                  if($valore) {
                    if(is_array($valore)) {
                      $_output .= $attributo.'="'.implode(' ', $valore).'" ';
                    } else {
                      $_output .= $attributo.'="'.$valore.'" ';
                    }
                  }
                  break;
              }
            }
            $_output = trim($_output);
            $_output .= '></i>';

            $_output .= '<label>';
            $_output .= Traduzioni::getLang('default', 'TITLE').'</label>';


            $_output .= '<input class="form-control" ';
            if(isset($attr['inp']['id']) && $attr['inp']['id']) {
              $_output .= ' id="'.$attr['inp']['id'].'_title"';
            }
            if(strpos($attr['inp']['name'], ']') !== false) {
              $_output .= ' name ="'.str_replace(']', '_title]', $attr['inp']['name']).'"';
            } else {
              $_output .= ' name ="'.$attr['inp']['name'].'_title"';
            }
            if(!isset($attr['inp']['title_value'])) {
              $attr['inp']['title_value'] = '';
            }
            $_output .= ' type="text" value="'.htmlentities($attr['inp']['title_value'], ENT_QUOTES, 'UTF-8').'"';
            $_output .= '/>';
            $_output .= '</div>';
            $_output .= '</div>';
          }
        }
        /** FINE METADATA **/
      }


      $_output .= '</div> <!-- end panel-body -->';
      $_output .= '</div><!-- end panel -->';
      //$_output .= '</div>';

      break;
  }


  if(!in_array($attr['inp']['type'], ['filedit', 'imgedit'])) {
    if($p['container']) {
      preg_match_all('/([^\#\.]*)\#?([^\#\.]*)\.?([^\#\.]*)/', $p['container'], $m);
      $_output .= '</'.$m[1][0].'>';
    }
  }

  if($p['debug']) {
    Utility::pre($_output);
    if($p['debug'] == 2) {
      die;
    }
  }

  return $_output;
}