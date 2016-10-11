<?php
/***************/
/** v.1.01    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.01 (04/12/2015, 10.26)                                                                   **/
/** - Aggiunto il parametro debug che inibisce l'header e stampa alcune informazioni             **/
/**   sicuramente estendibili per dare maggiori dettagli, al momento basta cosi.                 **/
/**                                                                                              **/
/** v.1.00 (03/09/15, 14.45)                                                                     **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
require_once('conf/boot.php');
require_once('vendor/autoload.php');
require_once(DOC_ROOT.REL_ROOT.UPLOAD.'settings/settings.php');
use Ueppy\utils\Utility;
use Ueppy\utils\Image;

$debug = false;
session_start();
if(!$debug) {
  header('Content-Type: image/jpeg');
}

$debugData = [];

function imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct) {

  if(!isset($pct)) {
    return false;
  }
  $pct /= 100;
  // Get image width and height
  $w = imagesx($src_im);
  $h = imagesy($src_im);
  // Turn alpha blending off
  imagealphablending($src_im, false);
  // Find the most opaque pixel in the image (the one with the smallest alpha value)
  $minalpha = 127;
  for($x = 0; $x < $w; $x++)
    for($y = 0; $y < $h; $y++) {
      $alpha = (imagecolorat($src_im, $x, $y) >> 24) & 0xFF;
      if($alpha < $minalpha) {
        $minalpha = $alpha;
      }
    }
  //loop through image pixels and modify alpha for each
  for($x = 0; $x < $w; $x++) {
    for($y = 0; $y < $h; $y++) {
      //get current alpha value (represents the TANSPARENCY!)
      $colorxy = imagecolorat($src_im, $x, $y);
      $alpha   = ($colorxy >> 24) & 0xFF;
      //calculate new alpha
      if($minalpha !== 127) {
        $alpha = 127 + 127 * $pct * ($alpha - 127) / (127 - $minalpha);
      } else {
        $alpha += 127 * $pct;
      }
      //get the color index with new alpha
      $alphacolorxy = imagecolorallocatealpha($src_im, ($colorxy >> 16) & 0xFF, ($colorxy >> 8) & 0xFF, $colorxy & 0xFF, $alpha);
      //set pixel with the new color + opacity
      if(!imagesetpixel($src_im, $x, $y, $alphacolorxy)) {
        return false;
      }
    }
  }
  // The image copy
  imagecopy($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h);
}


$preferredOrder = 'jpg,png,gif';
$destFile       = DOC_ROOT.REL_ROOT.UPLOAD.'cache/placeholder-'.$_GET['w'].'x'.$_GET['h'].'.jpg';
$percentuale    = 80;
$trasparenza    = 60;
$colore         = '#ffffff';

$debugData[] = 'Dimensioni richieste: '.$_GET['w'].'x'.$_GET['h'];
$debugData[] = 'File di destinazione: '.$destFile;
$debugData[] = 'Dimensioni del logo rispetto alla dimensione totale: '.$percentuale.'%';
$debugData[] = 'Trasparenza del logo: '.$trasparenza.'%';
$debugData[] = 'Colore di sfondo: '.$colore;

if(!file_exists($destFile)) {

  $origin = DOC_ROOT.REL_ROOT.'/images/partners/www.ueppy.com.png';

  $preferredOrder = explode(',', $preferredOrder);

  $default = DOC_ROOT.REL_ROOT.'images/site/logo';

  $images = glob($default.'.*');

  $origin = '/images/partners/www.ueppy.com.png';

  $loghi = [];

  if(count($images)) {
    $loghi = [];
    foreach($images as $image) {
      $ext         = Utility::getEstensione($image);
      $loghi[$ext] = $image;
    }

    foreach($preferredOrder as $ext) {
      if(isset($loghi[$ext])) {
        $origin = $loghi[$ext];
        break;
      }
    }
  }

  if($debug) {
    $debugData[] = 'Immagine di origine: '.$origin;
  }

  $originData = getimagesize($origin);

  $maxWidth  = ($percentuale / 100) * $_GET['w'];
  $maxHeight = ($percentuale / 100) * $_GET['h'];

  $tempFile = DOC_ROOT.REL_ROOT.UPLOAD.'cache/temp.'.session_id().'.jpg';

  if($originData[0] > $maxWidth || $originData[1] > $maxHeight) {

    $dimData                   = [];
    $dimData['width']          = $maxWidth;
    $dimData['height']         = $maxHeight;
    $dimData['bg']             = $colore;
    $dimData['type_of_resize'] = 'lossless';
    Image::crop($origin, $tempFile, $dimData);

  } else {
    copy($origin, $tempFile);
  }

  $logoTempData = getimagesize($tempFile);

  $scostamentoX = ($_GET['w'] - $logoTempData[0]) / 2;
  $scostamentoY = ($_GET['h'] - $logoTempData[1]) / 2;

  $im = imagecreatetruecolor($_GET['w'], $_GET['h']);

  $logo = imagecreatefromstring(file_get_contents($tempFile));

  $coloreSfondo = Image::fromHexToRGB($colore);

  $background = imagecolorallocate($im, $coloreSfondo['R'], $coloreSfondo['G'], $coloreSfondo['B']);

  imagefill($im, 0, 0, $background);

  imagecopymerge_alpha($im, $logo, $scostamentoX, $scostamentoY, 0, 0, $logoTempData[0], $logoTempData[1], $trasparenza);

  imagejpeg($im, $destFile);

  $tempFiles = glob(DOC_ROOT.REL_ROOT.UPLOAD.'cache/temp.*');

  foreach($tempFiles as $tempFile) {
    @unlink($tempFile);
  }


}

if($debug) {
  echo '<pre>';
  echo implode("\n", $debugData);
  echo '</pre>';
} else {
  echo file_get_contents($destFile);
}