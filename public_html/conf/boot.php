<?php
/*****************/
/***ueppy3.4.00***/
/*****************/
/**  CHANGELOG  **/
/**********************************************************************************************/
/** v.3.4.00                                                                                 **/
/** - Versione stabile                                                                       **/
/**                                                                                          **/
/**********************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                  **/
/** copyright: Ueppy s.r.l                                                                   **/
/**********************************************************************************************/
if(!file_exists(__DIR__.'/config.php')) {
  die('Manca il file di config');
} else {
  require(__DIR__.'/config.php');
}

// filesystem
define('UPLOAD', 'media/');
define('SOURCE', 0); // questo nei siti deve essere sempre a 0, vale solo per il codice sorgente di lavoro

// non toccare - inizio
if(!defined('DOC_ROOT')) {
  if(isset($_SERVER['DOCUMENT_ROOT']) && $_SERVER['DOCUMENT_ROOT']) {
    if($_SERVER['DOCUMENT_ROOT']{strlen($_SERVER['DOCUMENT_ROOT']) - 1} == '/') {
      $doc_root = substr($_SERVER['DOCUMENT_ROOT'], 0, -1);
    } else {
      $doc_root = $_SERVER['DOCUMENT_ROOT'];
    }
  } elseif(isset($_SERVER['pwd'])) {
    $doc_root = $_SERVER['pwd'];
  } else { // shell
    $getcwd = getcwd();
    if(strpos($getcwd, '/media/ext/www/') !== false) {
      $path     = explode('/', $getcwd);
      $doc_root = '/media/ext/www/'.$path[3].'/public_html';
    } else {
      // personalizzazione serverplan per funzionamento cron.
      $home     = explode('_', DB_NAME);
      $home     = array_shift($home);
      $doc_root = '/home/'.$home.'/public_html';
    }
  }
  define('DOC_ROOT', $doc_root);
}
// non toccare - fine

date_default_timezone_set('Europe/Rome');

// Parametri template
define('LIB', 'lib/');
define('CLASSES', DOC_ROOT.REL_ROOT.'classes/');
define('FUNCTIONS', DOC_ROOT.REL_ROOT.'functions/');
define('MODULES', 'public/modules/');
define('STRUTTURA_PUB', 'public/');
define('BACKUP_DIR', 'bck/');
define('MAIN_MODULES', 'main_modules/');
define('ADMIN_DIR', 'admin/');
define('CSS_PUB', 'css/');
define('JS_PUB', 'js/');
define('LANG_DIR', DOC_ROOT.REL_ROOT.UPLOAD.'languages/');
define('TEMP_DIR', DOC_ROOT.REL_ROOT.UPLOAD.'temp/');
define('CONF_DIR', DOC_ROOT.REL_ROOT.'conf/');
define('TABLES_DIR', CONF_DIR.'tables/');
define('SECTIONS_DIR', DOC_ROOT.REL_ROOT.STRUTTURA_PUB.'sections/');
define('DEFAULT_TPL', DOC_ROOT.REL_ROOT.STRUTTURA_PUB.'defaults/');
define('AJAX', DOC_ROOT.REL_ROOT.'ajax/');
define('BB_DIR', DOC_ROOT.REL_ROOT.'bb/');
define('BB_DIR_PUB', BB_DIR.'public/');
define('BB_DIR_AUTH', BB_DIR.'restricted/');


$css_admin = [DOC_ROOT.REL_ROOT.'bower_components/bootstrap/dist/css/bootstrap.min.css',
              DOC_ROOT.REL_ROOT.'bower_components/metisMenu/dist/metisMenu.min.css',
              DOC_ROOT.REL_ROOT.'bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.css',
              DOC_ROOT.REL_ROOT.'bower_components/pnotify/dist/pnotify.css',
              DOC_ROOT.REL_ROOT.'bower_components/pnotify/dist/pnotify.buttons.css',
              DOC_ROOT.REL_ROOT.'bower_components/datatables-responsive/css/responsive.bootstrap.css',
              DOC_ROOT.REL_ROOT.'bower_components/startbootstrap-sb-admin-2/dist/css/sb-admin-2.css',
              DOC_ROOT.REL_ROOT.'bower_components/font-awesome/css/font-awesome.min.css',
              DOC_ROOT.REL_ROOT.'bower_components/Tourist.js/tourist.css',
              DOC_ROOT.REL_ROOT.'admin/generic/ueppy.css',
              DOC_ROOT.REL_ROOT.'admin/generic/datatables.subset.css'];

$css_admin = [DOC_ROOT.REL_ROOT.'admin/styles/admin.css',
              DOC_ROOT.REL_ROOT.'bower_components/font-awesome/css/font-awesome.min.css'];

$js_admin = [DOC_ROOT.REL_ROOT.'bower_components/jquery/dist/jquery.min.js',
             DOC_ROOT.REL_ROOT.'bower_components/bootstrap/dist/js/bootstrap.min.js',
             DOC_ROOT.REL_ROOT.'bower_components/datatables/media/js/jquery.dataTables.min.js',
             DOC_ROOT.REL_ROOT.'bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.min.js',
             DOC_ROOT.REL_ROOT.'bower_components/metisMenu/dist/metisMenu.min.js',
             DOC_ROOT.REL_ROOT.'bower_components/raphael/raphael.js',
             DOC_ROOT.REL_ROOT.'bower_components/morrisjs/morris.min.js',
             DOC_ROOT.REL_ROOT.'bower_components/startbootstrap-sb-admin-2/dist/js/sb-admin-2.js',
             DOC_ROOT.REL_ROOT.'bower_components/pnotify/dist/pnotify.js',
             DOC_ROOT.REL_ROOT.'bower_components/pnotify/dist/pnotify.buttons.js',
             DOC_ROOT.REL_ROOT.'bower_components/handlebars/handlebars.min.js',
             DOC_ROOT.REL_ROOT.'bower_components/jquery-form/jquery.form.js',
             DOC_ROOT.REL_ROOT.'bower_components/underscore/underscore.js',
             DOC_ROOT.REL_ROOT.'bower_components/backbone/backbone.js',
             DOC_ROOT.REL_ROOT.'bower_components/Tourist.js/tourist.js',
             DOC_ROOT.REL_ROOT.LIB.'jquery.jeditable.min.js',
             DOC_ROOT.REL_ROOT.ADMIN_DIR.'generic/clock.js',
             DOC_ROOT.REL_ROOT.LIB.'php.js',
             DOC_ROOT.REL_ROOT.LIB.'jquery.gianiazutils.js',
             DOC_ROOT.REL_ROOT.LIB.'ueppy/Utils.Class.js',
             DOC_ROOT.REL_ROOT.LIB.'ueppy/Ueppy.Class.js',
             DOC_ROOT.REL_ROOT.ADMIN_DIR.'generic/main.js'];

$jsAdminProduzione = [DOC_ROOT.REL_ROOT.ADMIN_DIR.'generic/dist.min.js'];

require('site-config.php');
