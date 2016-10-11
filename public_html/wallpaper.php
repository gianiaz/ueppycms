<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (07/06/16, 16.40)                                                                     **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
$dir = 'wallpapers/';
if(isset($_GET['category']) && in_array($_GET['category'], ['default', 'enogastronomico'])) {
  $dir .= $_GET['category'];
} else {
  $dir .= 'default';
}

$dir .= '/';

$files = glob($dir.'*.jpg');
header('Content-type:image/jpeg');

if(is_array($files) && $files) {
  shuffle($files);

  $file = array_pop($files);

  echo file_get_contents($file);

}