<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (26/05/16, 10.26)                                                                      **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/

namespace Ueppy\utils;
use Ueppy\utils\Utility;

class Image {

  /**
   * Crea una copia ridimensionata dell'immagine passata nel path passato
   *
   * @param string $path percorso di destinazione
   * @param string $immagine Immagine originale (con percorso)
   * @param int $Xmax Larghezza massima dell'immagine
   * @param int $Ymax Altezza Massima dell'immagine
   * @param string $name Nome dell'immagine
   * @param int $quality Percentuale di compressione jpeg
   * @param boolean image_magick Se passato vero viene usato imagemagick anzichè le GD
   * @param array $image_magick_settings Array di impostazioni di image magick, per
   *              il momento contiene solo l'indice script_path che richiede il percorso all'eseguibile di imagemagick
   * @param int $debug 0/1 per avere un debug dalla funzione
   */
  static function thumbnail($path, $immagine, $Xmax, $Ymax, $name, $quality = 100, $image_magick = false, $image_magick_settings = [], $orient = true, $debug = 0) {

    if($orient) {
      Image::orient($immagine);
    }

    if($image_magick) {

      $script_path = $image_magick_settings['script_path'];

      $cmd = $script_path.' '.$immagine.' -thumbnail '.$Xmax.'x'.$Ymax.' -quality '.$quality.' '.$path.$name;

      exec($cmd);

      return $name;

    } else {

      list($width_orig, $height_orig) = getimagesize($immagine);

      $estensione = Utility::getEstensione($name);

      if($debug) {
        $str = 'Image::thumbnail';
        $str .= "\n";
        $str .= 'Path destinazione: '.$path;
        $str .= "\n";
        $str .= 'Immagine originale: '.$immagine;
        $str .= "\n";
        $str .= 'Estensione immagine: '.$estensione;
        $str .= "\n";
        $str .= 'Larghezza originale : '.$width_orig;
        $str .= "\n";
        $str .= 'Altezza originale : '.$height_orig;
        $str .= "\n";
        Image::log($str);
      }

      if(($width_orig < $Xmax) && ($height_orig < $Ymax)) {

        if($debug) {
          $str = 'immagine copiatata da '.$immagine.' a '.$path.$name;
          Image::log($str);
        }

        $newimg = $path.$name;

        copy($immagine, $path.$name);

      } else {

        if($Xmax == 0) {  // FIXED Y

          $yg = $Ymax;

          $xg = ($Ymax / $height_orig) * $width_orig; // FOTO VERTICALE

        } elseif($Ymax == 0) { // FIXED X

          $xg = $Xmax;

          $yg = ($Xmax / $width_orig) * $height_orig;

        } elseif($width_orig > $height_orig) { // FOTO ORIZZONTALE

          $xg = $Xmax;

          $yg = ($Xmax / $width_orig) * $height_orig;

          if($yg > $Ymax) {

            $yg = $Ymax;

            $xg = ($Ymax / $height_orig) * $width_orig;

          }

        } else { // FOTO VERTICALE

          $yg = $Ymax;

          $xg = ($Ymax / $height_orig) * $width_orig;

          if($xg > $Xmax) {

            $xg = $Xmax;

            $yg = ($Xmax / $width_orig) * $height_orig;

          }

        }

        if($debug) {
          $str = 'Nuove dimensioni: x:'.$xg.', y:'.$yg;
          Image::log($str);
        }

        switch($estensione) {

          case "jpg":

          case "jpeg":

            $source = ImageCreateFromJpeg($immagine);

            $dest = imagecreatetruecolor($xg, $yg);

            ImageCopyResampled($dest, $source, 0, 0, 0, 0, $xg, $yg, $width_orig, $height_orig);

            $newimg = $path.$name;

            ImageJpeg($dest, $newimg, $quality);

            break;

          case "gif" :

            $source = imagecreatefromgif($immagine);

            $dest = imagecreatetruecolor($xg, $yg);

            ImageCopyResampled($dest, $source, 0, 0, 0, 0, $xg, $yg, $width_orig, $height_orig);

            $newimg = $path.$name;

            ImageGif($dest, $newimg);

            break;

          case "png" :

            $source      = imagecreatefrompng($immagine);
            $width       = imagesx($source);
            $height      = imagesy($source);
            $dest        = imagecreatetruecolor($xg, $yg);
            $newimg      = $path.$name;
            $transparent = imagecolorallocatealpha($dest, 0, 0, 0, 127);
            imagefill($dest, 0, 0, $transparent);
            imagealphablending($dest, false);
            imagesavealpha($dest, true);
            ImageCopyResampled($dest, $source, 0, 0, 0, 0, $xg, $yg, $width_orig, $height_orig);

            ImagePng($dest, $newimg);

            break;

        }

        @chmod($newimg, 0666);

      }

      if($debug) {
        $str = 'File Salvato in :'.$newimg;
        Image::log($str);
      }

      return $name;
    }

  }

  /**
   *
   * @param string $originale Percorso all'immagine originale
   * @param string $new_name Nuovo nome da assegnare all'immagine
   * @param array $dim_data Array delle opzioni, vedi documentazione all'interno del metodo.
   * @param boolean $debug Se vero stampa a video tutti i calcoli fatti dalla classe per decidere dove e come ritagliare.
   */
  static function crop($originale, $new_name, $dim_data = [], $orient = true, $debug = 0) {

    if($orient) {
      Image::orient($originale);
    }

    $debug_log = [];

    // opzioni default
    $options                   = [];
    $options['width']          = 0;
    $options['height']         = 0;
    $options['bg']             = 'trasparent'; // colore di sfondo per immagini ottenute più piccole del contenitore
    $options['center']         = 1;         // centra l'immagine ottenuta nel contenitore
    $options['left']           = 0;         // posizionamento relativo dell'immagine ottenuta rispetto al margine sinistro, ignorato in caso di center = 1
    $options['top']            = 0;         // posizionamento relativo dell'immagine ottenuta rispetto al margine superiore, ignorato in caso di center = 1
    $options['resize']         = 1;         // indica se eseguire il resize della foto prima di ritagliarla
    $options['type_of_resize'] = 'loss';    // obbligatorio solo se resize = 1, se loss le immagini che hanno anche un solo lato più corto del contenitore non verranno ridimensionate, se lossless la foto verra ridimensionata in modo da non perdere nessun dettaglio
    $options['quality']        = SET_QUALITY_PHOTO;       // percentuale di qualità
    $options['pngquality']     = intval(10 - ($options['quality'] / 10) - 1);         // indice di compressione per png

    foreach($options as $key => $val) {
      if(isset($dim_data[$key])) {
        $options[$key] = $dim_data[$key];
      }
    }

    $file_data                       = [];
    $file_data['extension']          = Utility::getEstensione($new_name);
    $file_data['extensionOriginale'] = Utility::getEstensione($originale);
    if(isset($originale) && file_exists($originale)) {
      $file_data['path'] = $originale;
    } else {
      Image::log('File '.$originale.' non trovato');
      die;
    }

    if(isset($new_name) && $new_name) {
      $file_data['new_name'] = $new_name;
    }

    list($file_data['width'], $file_data['height']) = getimagesize($originale);

    if($debug) {
      $debug_log[] = __LINE__.'. Opzioni:';
      foreach($options as $key => $val) {
        $debug_log[] = __LINE__.".\t".str_pad($key, 15, ' ', STR_PAD_RIGHT).': '.$val;
      }
      $debug_log[] = '';
      $debug_log[] = __LINE__.'. File data:';
      foreach($file_data as $key => $val) {
        $debug_log[] = __LINE__.".\t".str_pad($key, 15, ' ', STR_PAD_RIGHT).': '.$val;
      }
    }

    // mi creo il puntatore all'immagine, se faccio il resize questo puntatore verrà aggiornato con la versione
    // ridimensionata
    $source = ImageCreateFromString(file_get_contents($file_data['path']));

    $debug_log[] = '';
    // prima decisione, faccio il resize?
    if($options['resize'] &&
      (
        ($options['type_of_resize'] == 'loss' && ($file_data['width'] > $options['width'] && $file_data['height'] > $options['height']))
        ||
        ($options['type_of_resize'] == 'lossless' && ($file_data['width'] > $options['width'] || $file_data['height'] > $options['height']))
      )
    ) {

      $rapporto_width  = $file_data['width'] / $options['width'];
      $rapporto_height = $file_data['height'] / $options['height'];

      $resize           = [];
      $resize['width']  = 0;
      $resize['height'] = 0;

      if($options['type_of_resize'] == 'loss') {
        $debug_log[] = __LINE__.". Eseguo resize in quanto tutti e due i lati sono superiori al contenitore fornito";

        $debug_log[] = '';
        $debug_log[] = __LINE__.". Rapporto Larghezza:".$rapporto_width;
        $debug_log[] = __LINE__.". Rapporto Altezza:".$rapporto_height;

        if($rapporto_width > 1 && $rapporto_width < $rapporto_height) {
          $resize['width']  = $options['width'];
          $resize['height'] = ($file_data['height'] * $resize['width']) / $file_data['width'];
        } elseif($rapporto_height <= $rapporto_width && $rapporto_height > 1) {
          $resize['height'] = $options['height'];
          $resize['width']  = ($file_data['width'] * $resize['height']) / $file_data['height'];
        }

      } else {
        $debug_log[] = __LINE__.". Eseguo resize in quanto almeno uno dei due lati è superiore al contenitore fornito";

        $debug_log[] = '';
        $debug_log[] = __LINE__.". Rapporto Larghezza:".$rapporto_width;
        $debug_log[] = __LINE__.". Rapporto Altezza:".$rapporto_height;

        if($rapporto_width > 1 && $rapporto_width > $rapporto_height) {
          $resize['width']  = $options['width'];
          $resize['height'] = ($file_data['height'] * $resize['width']) / $file_data['width'];
        } elseif($rapporto_height >= $rapporto_width && $rapporto_height > 1) {
          $resize['height'] = $options['height'];
          $resize['width']  = ($file_data['width'] * $resize['height']) / $file_data['height'];
        }

      }


      $debug_log[] = __LINE__.". DATI RESIZE";
      foreach($resize as $key => $val) {
        $debug_log[] = __LINE__.".\t".str_pad($key, 15, ' ', STR_PAD_RIGHT).': '.$val;
      }

      $resized = imagecreatetruecolor($resize['width'], $resize['height']);
      if($file_data['extensionOriginale'] == 'png') {
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
      }

      ImageCopyResampled($resized, $source, 0, 0, 0, 0, $resize['width'], $resize['height'], $file_data['width'], $file_data['height']);

      $source = $resized;

      $file_data['width']  = $resize['width'];
      $file_data['height'] = $resize['height'];

    } else {
      $debug_log[] = __LINE__.". Non eseguo resize";
    }

    // a questo punto qualsiasi sia stata l'operazione effettuata sulla foto, mi devo
    // assicurare che nessuno dei due della foto fornita superi le dimensioni del contenitore.
    // se è stato fornita l'opzione center ritaglierò in modo da prendere la parte centrale
    // altrimenti ritaglio secondo le dimensioni e coordinate fornite in left e top
    // salverò momentaneamente nel nome del file fornito come risultato, alla fine sovrascriverò
    if($file_data['width'] > $options['width'] || $file_data['height'] > $options['height']) {
      $debug_log[] = '';
      $debug_log[] = __LINE__.". Eseguo ritaglio";

      // ora decido dove ritagliare
      if($options['center']) {
        $ritaglio['left']   = 0;
        $ritaglio['top']    = 0;
        $ritaglio['width']  = 0;
        $ritaglio['height'] = 0;

        if($file_data['width'] > $options['width']) {
          $ritaglio['left']  = ($file_data['width'] - $options['width']) / 2;
          $ritaglio['width'] = $options['width'];
        } else {
          $ritaglio['width'] = $file_data['width'];
        }

        if($file_data['height'] > $options['height']) {
          $ritaglio['top']    = ($file_data['height'] - $options['height']) / 2;
          $ritaglio['height'] = $options['height'];
        } else {
          $ritaglio['height'] = $file_data['height'];
        }
      } else {

        $ritaglio['left']   = $options['left'];
        $ritaglio['top']    = $options['top'];
        $ritaglio['width']  = $options['width'];
        $ritaglio['height'] = $options['height'];

      }


      $debug_log[] = '';
      $debug_log[] = __LINE__.". Dati di ritaglio";
      foreach($ritaglio as $key => $val) {
        $debug_log[] = __LINE__.".\t".str_pad($key, 15, ' ', STR_PAD_RIGHT).': '.$val;
      }

      $imgritagliata = imagecreatetruecolor($ritaglio['width'], $ritaglio['height']);
      if($file_data['extension'] == 'png') {
        imagealphablending($imgritagliata, false);
        imagesavealpha($imgritagliata, true);
      }

      imagecopyresampled($imgritagliata, $source, 0, 0, $ritaglio['left'], $ritaglio['top'], $ritaglio['width'], $ritaglio['height'], $ritaglio['width'], $ritaglio['height']);

      $source = $imgritagliata;

      $file_data['width']  = $ritaglio['width'];
      $file_data['height'] = $ritaglio['height'];

    }

    // creo l'immagine di destinazione
    $dest = imagecreatetruecolor($options['width'], $options['height']);
    if($options['bg'] == 'trasparent') {
      if($file_data['extension'] == 'png') {
        imagealphablending($dest, false);
        imagesavealpha($dest, true);
        $bg = imagecolorallocatealpha($dest, 0, 0, 0, 127);
      } else {
        $options['bg'] = '#ffffff';
        $color         = Image::fromHexToRGB($options['bg']);
        $bg            = imagecolorallocate($dest, $color['R'], $color['G'], $color['B']);
      }
    } else {
      $color = Image::fromHexToRGB($options['bg']);
      $bg    = imagecolorallocate($dest, $color['R'], $color['G'], $color['B']);
    }


    imagefill($dest, 0, 0, $bg);

    if($options['center']) {

      $left = 0;
      $top  = 0;

      if($options['width'] > $file_data['width']) {
        $left = ($options['width'] - $file_data['width']) / 2;
      }

      if($options['height'] > $file_data['height']) {
        $top = ($options['height'] - $file_data['height']) / 2;
      }

    } else {

      // FIXME? ATTENZIONE, NON RICORDO PIU' PERCHé HO SETTATO 2 VOLTE IL LEFT E IL TOP, RISETTO A ZERO MA LASCIO
      // NEL CASO SALTINO FUORI BACHI CHE ORA NON MI VENGONO IN MENTE
      $left = $options['left'];
      $top  = $options['top'];

      $left = 0;
      $top  = 0;

    }

    $debug_log[] = __LINE__.". Posiziono immagine a $left, $top";
    $debug_log[] = __LINE__.". Immagine creata";

    imagecopyresampled($dest, $source, $left, $top, 0, 0, $file_data['width'], $file_data['height'], $file_data['width'], $file_data['height']);

    imagedestroy($source);

    switch($file_data['extension']) {
      case 'jpg':
      case 'jpeg':
        imagejpeg($dest, $file_data['new_name'], $options['quality']);
        break;
      case 'png':
        imagepng($dest, $file_data['new_name'], $options['pngquality']);
        imagealphablending($dest, false);
        imagesavealpha($dest, true);
        break;
      case 'gif':
        imagegif($dest, $file_data['new_name']);
        break;
    }

    if($debug) {
      Image::log(implode("\n", $debug_log));
    }

  }

  static function log($array, $file = '', $htmlout = -1) {

    $wrap = 130;

    $debug_array = debug_backtrace();
    $debug_array = array_reverse($debug_array);

    if($file) {
      if(!is_dir(basename($file))) {
        Image::log('Directory '.basename($file).' inesistente');
        die;
      } elseif(!is_writable($file)) {
        Image::log('File '.$file.' non scrivibile');
        die;
      }
    }
    if($htmlout != -1) {
      if(!$htmlout) {
        $acapo     = "\n";
        $pre       = "\n-------\n";
        $pre_close = "\n------\n";
      } else {
        $acapo     = "\n";
        $pre       = "<pre class=\"debug ui-corner-all\">";
        $pre_close = "</pre>";
      }
    } else {
      if($file) {
        $acapo     = "\n";
        $pre       = "\n-------\n";
        $pre_close = "\n------\n";
      } else {
        $acapo     = "\n";
        $pre       = "<pre class=\"debug ui-corner-all\">";
        $pre_close = "</pre>";
      }
    }

    $string = '';
    $string .= $pre;
    foreach($debug_array as $da) {
      $f = $da['file'];
      if(defined('DOC_ROOT')) {
        $f = str_replace(DOC_ROOT, '', $f);
      }
      $string .= $f.', '.$da['line'];
      $string .= $acapo;
    }
    $string .= $acapo;


    if(is_array($array)) {
      $string .= "Tipo: Array";
      $string .= $acapo;
      if(empty($array)) {
        $string .= "array vuoto";
        $string .= $acapo;
      } else {
        $string .= "Numero Elementi :".count($array);
        $string .= $acapo;
        $string .= print_r($array, true);
        $string .= $acapo;
      }
    } elseif(is_object($array)) {
      $string .= "Oggetto";
      $string .= $acapo;
      $string .= print_r($array, true);
      $string .= $acapo;
    } elseif(is_bool($array)) {
      if($array) {
        $string .= "bool: true";
      } else {
        $string .= "bool: false";
      }
      $string .= $acapo;
    } else {
      $string .= "Tipo: Stringa(".strlen($array).")";

      $string .= $acapo;
      if(strlen($array) > $wrap) {
        $string .= $acapo;
        $string .= '!WRAPPED!';
        $string .= $acapo;
        $string .= $acapo;
        $array = wordwrap($array, $wrap, $break = "\n", false);
      }
      $string .= htmlentities($array, ENT_QUOTES, 'UTF-8');
      $string .= $acapo;
    }
    $string .= $pre_close;

    if($file) {
      file_put_contents($file, $string, FILE_APPEND);
    } else {
      echo $string;
    }

  }

  /**
   * @param string $color colore in formato esadecimale es. #ffffff
   * @return array array con le proprietà R, G, B con i rispettivi valori
   */
  static function fromHexToRGB($color) {

    $colore      = [];
    $col         = sscanf($color, '#%2x%2x%2x');
    $colore['R'] = $col[0];
    $colore['G'] = $col[1];
    $colore['B'] = $col[2];

    return $colore;
  }

  /**
   * Funzione che genera una foto in bianco e nero partendo da una a colori.
   *
   * @param string $path Percorso dell'immagine originale
   * @param type $new_name Percorso del risultato (può coincidere con il precedente)
   * @param type $dither
   */

  static function greyscale($path, $new_name, $dither = true) {

    $ext = Utility::getEstensione($path);

    switch($ext) {

      case 'jpg':
      case 'jpeg':
        $img = imagecreatefromjpeg($path);
        break;

      case 'png':
        $img = ImageCreateFromPng($path);
        imagealphablending($img, false);
        imagesavealpha($img, true);
        break;

      case 'gif':
        $img = ImageCreateFromGif($path);
        break;

    }

    $t = 256;

    imagetruecolortopalette($img, $dither, $t);

    for($c = 0; $c < $t; $c++) {
      $col = imagecolorsforindex($img, $c);
      $min = min($col['red'], $col['green'], $col['blue']);
      $max = max($col['red'], $col['green'], $col['blue']);
      $i   = ($max + $min) / 2;
      imagecolorset($img, $c, $i, $i, $i);
    }

    switch($ext) {

      case 'jpg':
      case 'jpeg':
        ImageJpeg($img, $new_name);
        break;

      case 'png':
        ImagePng($img, $new_name);
        break;

      case 'gif':
        ImageGif($img, $new_name);
        break;

    }

  }

  /**
   * Ritorna immagine pixelata
   *
   * @param string $path Percorso dell'immagine originale
   * @param type $new_name Percorso del risultato (può coincidere con il precedente)
   * @param type $blocksize Grandezza della squadrettatura
   */

  static function pixelate($path, $new_name, $blocksize = 12) {

    $ext = Utility::getEstensione($path);

    switch($ext) {

      case 'jpg':
      case 'jpeg':
        $img = imagecreatefromjpeg($path);
        break;

      case 'png':
        $img = ImageCreateFromPng($path);
        imagealphablending($img, false);
        imagesavealpha($img, true);
        break;

      case 'gif':
        $img = ImageCreateFromGif($path);
        break;

    }

    list($imagex, $imagey) = getimagesize($path);

    for($x = 0; $x < $imagex; $x += $blocksize) {
      for($y = 0; $y < $imagey; $y += $blocksize) {
        $rgb = imagecolorat($img, $x, $y);
        imagefilledrectangle($img, $x, $y, $x + $blocksize - 1, $y + $blocksize - 1, $rgb);
      }
    }

    switch($ext) {

      case 'jpg':
      case 'jpeg':
        ImageJpeg($img, $new_name);
        break;

      case 'png':
        ImagePng($img, $new_name);
        break;

      case 'gif':
        ImageGif($img, $new_name);
        break;

    }

  }

  /**
   * Ritorna un'immagine seppia
   *
   * @param string $path Percorso dell'immagine originale
   * @param type $new_name Percorso del risultato (può coincidere con il precedente)
   */
  static function seppia($path, $new_name) {

    $ext = Utility::getEstensione($path);

    switch($ext) {

      case 'jpg':
      case 'jpeg':
        $img = imagecreatefromjpeg($path);
        break;

      case 'png':
        $img = ImageCreateFromPng($path);
        imagealphablending($img, false);
        imagesavealpha($img, true);
        break;

      case 'gif':
        $img = ImageCreateFromGif($path);
        break;

    }

    $t = 256;

    imagetruecolortopalette($img, true, $t);

    $total = imagecolorstotal($img);

    for($i = 0; $i < $total; $i++) {
      $index = imagecolorsforindex($img, $i);
      $red   = ($index["red"] * 0.393 + $index["green"] * 0.769 + $index["blue"] * 0.189);
      $green = ($index["red"] * 0.349 + $index["green"] * 0.686 + $index["blue"] * 0.168);
      $blue  = ($index["red"] * 0.272 + $index["green"] * 0.534 + $index["blue"] * 0.131);
      if($red > 255) {
        $red = 255;
      }
      if($green > 255) {
        $green = 255;
      }
      if($blue > 255) {
        $blue = 255;
      }
      imagecolorset($img, $i, $red, $green, $blue);
    }

    switch($ext) {

      case 'jpg':
      case 'jpeg':
        ImageJpeg($img, $new_name);
        break;

      case 'png':
        ImagePng($img, $new_name);
        imagealphablending($img, false);
        imagesavealpha($img, true);
        break;

      case 'gif':
        ImageGif($img, $new_name);
        break;

    }

  }

  /**
   *
   * @param string $path Percorso dell'immagine originale
   * @param type $new_name Percorso del risultato (può coincidere con il precedente)
   * @param type $intensita Intensità dell'effetto
   * @param type $debug
   */

  static function scatter($path, $new_name, $intensita = 1, $debug = 0) {

    $ext = Utility::getEstensione($path);

    switch($ext) {

      case 'jpg':
      case 'jpeg':
        $img = imagecreatefromjpeg($path);
        break;

      case 'png':
        $img = ImageCreateFromPng($path);
        imagealphablending($img, false);
        imagesavealpha($img, true);
        break;

      case 'gif':
        $img = ImageCreateFromGif($path);
        break;

    }

    list($imagex, $imagey) = getimagesize($path);

    for($x = 0; $x < $imagex; ++$x) {
      for($y = 0; $y < $imagey; ++$y) {
        $distx = rand(-($intensita), $intensita);
        $disty = rand(-($intensita), $intensita);

        if($x + $distx >= $imagex) continue;
        if($x + $distx < 0) continue;
        if($y + $disty >= $imagey) continue;
        if($y + $disty < 0) continue;

        $oldcol = imagecolorat($img, $x, $y);
        $newcol = imagecolorat($img, $x + $distx, $y + $disty);
        imagesetpixel($img, $x, $y, $newcol);
        imagesetpixel($img, $x + $distx, $y + $disty, $oldcol);
      }
    }

    switch($ext) {

      case 'jpg':
      case 'jpeg':
        ImageJpeg($img, $new_name);
        break;

      case 'png':
        ImagePng($img, $new_name);
        break;

      case 'gif':
        ImageGif($img, $new_name);
        break;

    }

  }

  /**
   * Ruota un'immagine
   *
   * @param type $source Percorso dell'originale
   * @param type $dest Percorso dell'immagine di destinazione, può coincidere con l'orignale
   * @param type $gradi Gradi di rotazione
   * @param type $bgd_color Colore di background, default trasparent, che vale solo per le png per le altre estensioni trasparent equivale a #ffffff
   */
  static function rotate($source, $dest, $gradi = 0, $bgd_color = 'trasparent') {

    if($gradi) {

      if($bgd_color != 'trasparent') {
        $bgd_color = Image::fromHexToRGB($bgd_color);
      }

      $ext = Utility::getEstensione($source);

      switch($ext) {

        case 'jpg':
        case 'jpeg':
          $source    = ImageCreateFromJpeg($source);
          $bgd_color = imagecolorallocate($source, $bgd_color['R'], $bgd_color['G'], $bgd_color['B']);
          $rotate    = imagerotate($source, $gradi, $bgd_color, 0);
          imagejpeg($rotate, $dest);
          break;
        case 'png':
          $source = imagecreatefrompng($source);
          if($bgd_color == 'trasparent') {
            $bgd_color = imagecolorallocatealpha($source, 0, 0, 0, 127);
          } else {
            $bgd_color = imagecolorallocate($source, $bgd_color['R'], $bgd_color['G'], $bgd_color['B']);
          }
          $rotate = imagerotate($source, $gradi, $bgd_color, 0);
          imagealphablending($rotate, false);
          imagesavealpha($rotate, true);
          imagepng($rotate, $dest);
          break;
        case 'gif':
          $source    = imagecreatefromgif($source);
          $bgd_color = imagecolorallocate($source, $bgd_color['R'], $bgd_color['G'], $bgd_color['B']);
          $rotate    = imagerotate($source, $gradi, $bgd_color, 0);
          imagegif($rotate, $dest);
          break;

      }

    }

  }

  /**
   * Luminosità
   * @param type $source Percorso dell'originale
   * @param type $dest Percorso dell'immagine di destinazione, può coincidere con l'orignale
   * @param type $valore Valore della luminosità
   */
  static function brightness($source, $dest, $valore = 0) {

    $ext = Utility::getEstensione($source);

    switch($ext) {

      case 'jpg':
      case 'jpeg':
        $img = imagecreatefromjpeg($source);
        break;

      case 'png':
        $img = ImageCreateFromPng($source);
        imagealphablending($img, false);
        imagesavealpha($img, true);
        break;

      case 'gif':
        $img = ImageCreateFromGif($source);
        break;

    }

    imagefilter($img, IMG_FILTER_BRIGHTNESS, $valore);
    switch($ext) {

      case 'jpg':
      case 'jpeg':
        ImageJpeg($img, $dest);
        break;

      case 'png':
        ImagePng($img, $dest);
        break;

      case 'gif':
        ImageGif($img, $dest);
        break;

    }


  }

  /**
   * @param type $source Percorso dell'originale
   * @param type $dest Percorso dell'immagine di destinazione, può coincidere con l'orignale
   * @param type $fx accetta le seguenti costanti:
   *
   * IMG_FILTER_NEGATE: inverte tutti i colori ottenendo un effetto negativo
   * IMG_FILTER_GRAYSCALE: inverte tutti i colori
   * IMG_FILTER_EDGEDETECT: inverte tutti i colori
   * IMG_FILTER_EMBOSS: inverte tutti i colori
   * IMG_FILTER_GAUSSIAN_BLUR: inverte tutti i colori
   * IMG_FILTER_SELECTIVE_BLUR: inverte tutti i colori
   * IMG_FILTER_MEAN_REMOVAL: inverte tutti i colori
   *
   * richiede argomento 1
   *
   * IMG_FILTER_BRIGHTNESS
   * IMG_FILTER_CONTRAST
   * IMG_FILTER_SMOOTH
   *
   *
   */
  static function fx($source, $dest, $fx, $arg1 = 0, $arg2 = 0, $arg3 = 0, $arg4 = 0) {

    $ext = Utility::getEstensione($source);

    switch($ext) {

      case 'jpg':
      case 'jpeg':
        $img = imagecreatefromjpeg($source);
        break;

      case 'png':
        $img = ImageCreateFromPng($source);
        imagealphablending($img, false);
        imagesavealpha($img, true);
        break;

      case 'gif':
        $img = ImageCreateFromGif($source);
        break;

    }

    switch($fx) {

      case IMG_FILTER_NEGATE:
      case IMG_FILTER_GRAYSCALE:
      case IMG_FILTER_EDGEDETECT:
      case IMG_FILTER_EMBOSS:
      case IMG_FILTER_GAUSSIAN_BLUR:
      case IMG_FILTER_SELECTIVE_BLUR:
      case IMG_FILTER_MEAN_REMOVAL:
        imagefilter($img, $fx);
        break;

      case IMG_FILTER_BRIGHTNESS:
      case IMG_FILTER_CONTRAST:
      case IMG_FILTER_SMOOTH:
        imagefilter($img, $fx, $arg1);
        break;

      case IMG_FILTER_PIXELATE:
        imagefilter($img, $fx, $arg1, $arg2);
        break;

      case IMG_FILTER_COLORIZE:

        $rgb = Image::fromHexToRGB($arg1);
        imagefilter($img, $fx, $rgb['R'], $rgb['G'], $rgb['B'], $arg2);

        break;


    }

    switch($ext) {

      case 'jpg':
      case 'jpeg':
        ImageJpeg($img, $dest);
        break;

      case 'png':
        ImagePng($img, $dest);
        break;

      case 'gif':
        ImageGif($img, $dest);
        break;

    }

  }

  /**
   *
   * @param string $source Percorso dell'originale
   * @param string $dest Percorso dell'immagine di destinazione, può coincidere con l'orignale
   * @param string $logo Percorso del logo da applicare
   * @param mixed $pos Stringa o array, nel caso di stringa accetta center, mentre in caso di array posizione x e y array(x => 0, $y => 0);
   */
  static function watermark($source, $dest, $logo, $pos = 'center') {

    $ext = Utility::getEstensione($source);

    switch($ext) {

      case 'jpg':
      case 'jpeg':
        $img = imagecreatefromjpeg($source);
        break;

      case 'png':
        $img = ImageCreateFromPng($source);
        imagealphablending($img, false);
        imagesavealpha($img, true);
        break;

      case 'gif':
        $img = ImageCreateFromGif($source);
        break;

    }

    $ext_logo = Utility::getEstensione($logo);

    switch($ext_logo) {

      case 'jpg':
      case 'jpeg':
        $logoImg = imagecreatefromjpeg($logo);
        break;

      case 'png':
        $logoImg = ImageCreateFromPng($logo);
        imagealphablending($img, false);
        imagesavealpha($img, true);
        break;

      case 'gif':
        $logoImg = ImageCreateFromGif($logo);
        break;

    }


    $dimensioni           = [];
    $dimensioni['logo']   = getimagesize($logo);
    $dimensioni['source'] = getimagesize($source);

    if($dimensioni['logo'][0] > $dimensioni['source'][0] || $dimensioni['logo'][1] > $dimensioni['source'][1]) {
      Image::log('Logo più grande dell\'immagine');

      return;
    } else {

      $posizione = [];

      $posizione['center']['x'] = ($dimensioni['source'][0] - $dimensioni['logo'][0]) / 2;
      $posizione['center']['y'] = ($dimensioni['source'][1] - $dimensioni['logo'][1]) / 2;

      $posizione['max']['x'] = $dimensioni['source'][0] - $dimensioni['logo'][0];
      $posizione['max']['y'] = $dimensioni['source'][1] - $dimensioni['logo'][1];

      if($pos == 'center') {
        $pos = $posizione['center'];
      } else {
        if(!isset($pos['x']) || $pos['x'] > $posizione['max']['x'] ||
          !isset($pos['y']) || $pos['y'] > $posizione['max']['y']
        ) {

          $pos['x'] = 0;
          $pos['y'] = 0;
        }
      }
      imagecopy($img, $logoImg, $pos['x'], $pos['y'], 0, 0, $dimensioni['logo'][0], $dimensioni['logo'][1]);
    }


    switch($ext) {

      case 'jpg':
      case 'jpeg':
        ImageJpeg($img, $dest);
        break;

      case 'png':
        ImagePng($img, $dest);
        break;

      case 'gif':
        ImageGif($img, $dest);
        break;

    }

  }

  /**
   * @param string $source Percorso dell'originale
   * @param string $dest Percorso dell'immagine di destinazione, può coincidere con l'orignale
   * @param string $colore Colore in formato esadecimale
   *
   */
  static function interlace($source, $dest, $colore = '#000000', $passo = 1, $altezza = 1) {

    $ext = Utility::getEstensione($source);

    $rgb = Image::fromHexToRGB($colore);

    switch($ext) {

      case 'jpg':
      case 'jpeg':
        $img = imagecreatefromjpeg($source);
        break;

      case 'png':
        $img = ImageCreateFromPng($source);
        imagealphablending($img, false);
        imagesavealpha($img, true);
        break;

      case 'gif':
        $img = ImageCreateFromGif($source);
        break;

    }

    $colore = imagecolorallocate($img, $rgb['R'], $rgb['G'], $rgb['B']);

    $imagex = imagesx($img);
    $imagey = imagesy($img);

    $start = 1;

    for($y = 0; $y < $imagey; $y++) {
      if($start == $passo) {
        $start = 0;
        for($i = 0; $i < $altezza; $i++) {
          $y++;
          for($x = 0; $x < $imagex; ++$x) {
            ImageSetPixel($img, $x, $y, $colore);
          }
          if($y == $imagey) {
            break(2);
          }
        }
      }

      $start++;

    }

    switch($ext) {

      case 'jpg':
      case 'jpeg':
        ImageJpeg($img, $dest);
        break;

      case 'png':
        ImagePng($img, $dest);
        break;

      case 'gif':
        ImageGif($img, $dest);
        break;

    }

  }

  static function orient($file_path) {

    $exif = @exif_read_data($file_path);
    if($exif === false) {
      return false;
    }
    $orientation = intval(@$exif['Orientation']);
    if(!in_array($orientation, [3, 6, 8])) {
      return false;
    }
    $image = @imagecreatefromjpeg($file_path);
    switch($orientation) {
      case 3:
        $image = @imagerotate($image, 180, 0);
        break;
      case 6:
        $image = @imagerotate($image, 270, 0);
        break;
      case 8:
        $image = @imagerotate($image, 90, 0);
        break;
      default:
        return false;
    }
    $success = imagejpeg($image, $file_path);
    // Free up memory (imagedestroy does not delete files):
    @imagedestroy($image);

    return $success;
  }

  static function reflect($source, $dest) {

    $ext = Utility::getEstensione($source);

    list($w, $h) = getimagesize($source);

    switch($ext) {

      case 'jpg':
      case 'jpeg':
        $imgImport = imagecreatefromjpeg($source);
        break;

      case 'png':
        $imgImport = ImageCreateFromPng($source);
        imagealphablending($img, false);
        imagesavealpha($img, true);
        break;

      case 'gif':
        $imgImport = ImageCreateFromGif($source);
        break;

    }

    // Create new blank image with sizes.
    $background = imagecreatetruecolor($w, $h);

    $newImage = imagecreatetruecolor($w, $h);
    for($y = 0; $y < $h; $y++) {
      for($x = 0; $x < $w; $x++) {
        imagecopy($newImage, $imgImport, $w - $x + 1, $y, $x, $y, 1, 1);
      }
    }
    // Add it to the blank background image
    imagecopymerge($background, $newImage, 0, 0, 0, 0, $w, $h, 100);

    switch($ext) {

      case 'jpg':
      case 'jpeg':
        ImageJpeg($background, $dest);
        break;

      case 'png':
        ImagePng($background, $dest);
        break;

      case 'gif':
        ImageGif($background, $dest);
        break;

    }

  }


}

?>
