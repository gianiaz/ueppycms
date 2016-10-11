<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (19/05/16, 15.42)                                                                     **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
use Ueppy\core\Traduzioni;

// device
$mobile = false;
$tablet = false;

// valore sola lettura di default
$READONLY = 0;

// tipo di upload (fa comparire il button sfoglia)
$UPLOADCLASSICO = 0;

// moduli trattati in modo particolare
$moduliSenzaAuth       = ['login', 'oldpriv', 'logout', 'tinymcetemplateslist', 'stats'];
$moduliSenzaTraduzioni = ['tinymcetemplateslist', 'logout', 'login', 'oldpriv'];
$moduliSenzaActions    = ['default', 'login', 'logout', 'stats'];

// variabile di ritorno delle chiamate ajax, se valorizzata la risposta è data in json encodato
$ajaxReturn = [];

// pulsantiera da riempiere in header
$headerButtons = [];
$footerButtons = [];

// bottoni di default
$BUTTONS['btnNew'] = ['text'       => Traduzioni::getLang('default', 'NEW'),
                      'icon'       => 'file-o',
                      'attributes' => ['class'     => 'upybtn btn btn-primary',
                                       'data-role' => 'new']];

$BUTTONS['btnDemo'] = ['text'       => Traduzioni::getLang('default', 'DEMO'),
                       'icon'       => 'question-circle-o',
                       'attributes' => ['class'     => 'upybtn btn btn-info',
                                        'data-role' => 'demo']];

$BUTTONS['btnSort'] = ['text'       => Traduzioni::getLang('default', 'ORDINA'),
                       'icon'       => 'sort',
                       'attributes' => ['class'     => 'upybtn btn btn-info',
                                        'data-role' => 'sort']];

$BUTTONS['btnClose'] = ['text'       => Traduzioni::getLang('default', 'CLOSE'),
                        'icon'       => 'close',
                        'attributes' => ['class'     => 'upybtn btn btn-warning',
                                         'data-role' => 'close']];

$BUTTONS['btnSave'] = ['text'       => Traduzioni::getLang('default', 'SALVA'),
                       'icon'       => 'floppy-o',
                       'attributes' => ['class'     => 'upybtn btn btn-success',
                                        'data-role' => 'submit']];

$BUTTONS['btnSaveClose'] = ['text'       => Traduzioni::getLang('default', 'SALVA_E_CHIUDI'),
                            'icon'       => 'floppy-o',
                            'icon2'      => 'close',
                            'attributes' => ['class'     => 'upybtn btn btn-success',
                                             'data-role' => 'submit-close']];

$BUTTONS['btnSaveNew'] = ['text'       => Traduzioni::getLang('default', 'SALVA_E_NUOVO'),
                          'icon'       => 'floppy-o',
                          'icon2'      => 'file-o',
                          'attributes' => ['class'     => 'upybtn btn btn-primary',
                                           'data-role' => 'save-new']];

$BUTTONS['btnSeo'] = ['text'       => Traduzioni::getLang('default', 'SEO'),
                      'icon'       => 'search-plus',
                      'attributes' => ['class'     => 'upybtn btn btn-info',
                                       'data-role' => 'seo']];


$BUTTONS['set']['save-close-new'] = [$BUTTONS['btnSave'], $BUTTONS['btnClose'], $BUTTONS['btnNew']];

$BUTTONS['set']['save-close'] = [$BUTTONS['btnSave'], $BUTTONS['btnSaveClose'], $BUTTONS['btnClose'], $BUTTONS['btnSaveNew']];