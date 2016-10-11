<?php
ini_set('display_errors', 1);
ini_set("memory_limit", '128M');
header('Content-type: text/html;charset=utf-8');

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
    $path     = explode('/', trim($getcwd, '/'));
    $doc_root = '/media/ext/www/'.$path[3].'/public_html';
  } else {
    $doc_root = '/home/ueppybox/subdomains/4/public_html';
  }
}


define('DOC_ROOT', $doc_root);
require(DOC_ROOT.'/conf/boot.php');
require(DOC_ROOT.REL_ROOT.'vendor/autoload.php');

use Ueppy\utils\Utility;
use Ueppy\core\Db;

if(!isset($db)) {
  $db = new Db();
  $db->connect();
}

$dir_to_import = DOC_ROOT.REL_ROOT.UPLOAD.'export/';

$files = glob($dir_to_import.'*.sql');

print_r($files);

foreach($files as $file) {

  $queries_sporche = explode(";\n", file_get_contents($file));

  $queries = [];

  foreach($queries_sporche as $q) {
    $q = trim($q);
    if($q && strpos(trim($q), '/*') !== 0) {
      $queries[] = $q;
    }
  }

  foreach($queries as $q) {
    $db->doQuery($q, false);
  }

}

if(isset($_SERVER['DOCUMENT_ROOT']) && $_SERVER['DOCUMENT_ROOT']) {
  $htmlout = true;
} else {
  $htmlout = false;
}

//Utility::pre('Importati i files: '."\n".implode("\n", $files), false, $htmlout);