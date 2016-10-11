<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (21/05/16, 15.33)                                                                     **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
define('PRODUZIONE', false); // va impostato a true quando finito e serve per caricare js e css compressi.

$CONFIG = [];

$CONFIG['header']                      = [];
$CONFIG['header']['file']              = DEFAULT_TPL.'header.tpl';
$CONFIG['header']['show']              = true;
$CONFIG['header']['eccezioni']         = [];
$CONFIG['header']['eccezioni']['home'] = DEFAULT_TPL.'header_home.tpl';

$CONFIG['footer']['file']                  = DEFAULT_TPL.'footer.tpl';
$CONFIG['footer']['show']                  = true;
$CONFIG['footer']['eccezioni']             = [];
$CONFIG['footer']['eccezioni']['carrello'] = DEFAULT_TPL.'footer_base.tpl';

$css = [DOC_ROOT.REL_ROOT.CSS_PUB.'bootstrap.min.css',
        DOC_ROOT.REL_ROOT.CSS_PUB.'font-awesome.min.css',
        DOC_ROOT.REL_ROOT.CSS_PUB.'clean-blog.min.css',
        DOC_ROOT.REL_ROOT.CSS_PUB.'ueppy.css'];

$js = [DOC_ROOT.REL_ROOT.'bower_components/jquery-legacy/dist/jquery.js',
       DOC_ROOT.REL_ROOT.'bower_components/jquery-form/jquery.form.js',
       DOC_ROOT.REL_ROOT.'bower_components/js-cookie/src/js.cookie.js',
       DOC_ROOT.REL_ROOT.'bower_components/handlebars/handlebars.min.js',
       DOC_ROOT.REL_ROOT.'bower_components/lightbox2/dist/js/lightbox.min.js',
       DOC_ROOT.REL_ROOT.LIB.'php.js',
       DOC_ROOT.REL_ROOT.LIB.'ueppy/Utils.Class.js',
       DOC_ROOT.REL_ROOT.JS_PUB.'jquery.cycle.js',
       DOC_ROOT.REL_ROOT.JS_PUB.'jquery.carouFredSel.js',
       DOC_ROOT.REL_ROOT.JS_PUB.'bootstrap.min.js',
       DOC_ROOT.REL_ROOT.JS_PUB.'bootstrap-slider.js',
       DOC_ROOT.REL_ROOT.JS_PUB.'general.js',
       DOC_ROOT.REL_ROOT.JS_PUB.'ecommerce.js',
       DOC_ROOT.REL_ROOT.JS_PUB.'upyEcomTrack.Class.js',
       DOC_ROOT.REL_ROOT.JS_PUB.'main.js'
];


if(PRODUZIONE) {

  $jsDaComprimere  = $js;
  $cssDaComprimere = $css;

  $css = [DOC_ROOT.REL_ROOT.CSS_PUB.'layout.css'];
  $js  = [DOC_ROOT.REL_ROOT.JS_PUB.'script.min.js'];

}