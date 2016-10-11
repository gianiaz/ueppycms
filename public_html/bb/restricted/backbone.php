<?php
/***************/
/** v.1.06    **/
/***************/
/** CHANGELOG **/
/******************************************************************************************************************/
/** v.1.06 (12/11/2015, 18.53)                                                                                   **/
/** - Aggiunto autoloading delle classi                                                                          **/
/**                                                                                                              **/
/** v.1.05 (25/03/2015)                                                                                          **/
/** - Bugfix su estrazione allegati, problemi nella distinzione tra immagini e file di diverso genere.           **/
/**                                                                                                              **/
/** v.1.04 (25/06/2014)                                                                                          **/
/** - Bugfix su estrazione allegati, i filtri venivano azzerati e e venivano estratti tutti gli allegati.        **/
/**                                                                                                              **/
/** v.1.03 (13/06/2014)                                                                                          **/
/** - Pulizia automatica dei record rimasti appesi da precedenti upload falliti.                                 **/
/** - Bugfix su reperimento immagini, se il tipo del campo non è espressametne settato a immagine viene trattato **/
/**   come allegato generico.                                                                                    **/
/**                                                                                                              **/
/** v.1.02 (17/05/2013)                                                                                          **/
/** - Bugfix, errore nel caso non sia passato il tipo di ritaglio che si vuole effettuare (ora default lossless) **/
/**                                                                                                              **/
/** v.1.01 (12/01/2013)                                                                                          **/
/** - Bugfix (Strict Standards: Only variables should be passed by reference)                                    **/
/**                                                                                                              **/
/** v.1.00                                                                                                       **/
/** - Versione stabile                                                                                           **/
/**                                                                                                              **/
/***********************************************************                                                     **/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                                      **/
/** copyright: Ueppy s.r.l                                                                                       **/
/******************************************************************************************************************/
use Ueppy\core\Allegati;
use Ueppy\utils\Image;
use Ueppy\utils\Utility;


if(basename($_SERVER['SCRIPT_FILENAME']) == 'backbone.php') {

  $module_name = explode('.', basename(__FILE__));
  $module_name = array_shift($module_name);

  if($operator) {

    // READ
    if($_SERVER['REQUEST_METHOD'] == "GET") {

      $options                  = [];
      $options['tableFilename'] = 'allegati';
      $options['debug']         = 0;

      $allegati = new Allegati($options);

      $return = [];

      // ELEMENTO SINGOLO
      if(Utility::isPositiveInt($_GET['id'])) {

      } elseif(Utility::isPositiveInt($_GET['id_genitore']) && $_GET['genitore']) {
        // ELENCO

        $options                  = [];
        $options['tableFilename'] = 'allegati';
        $options['debug']         = 0;

        $allegati = new Allegati($options);

        $return = [];

        $opts                 = [];
        $opts['forceAllLang'] = true;
        $opts['sortField']    = 'ordine';
        $opts['sortOrder']    = 'ASC';
        $opts['debug']        = 0;

        $filters = [];

        $filter_record              = [];
        $filter_record['chiave']    = 'id_genitore';
        $filter_record['operatore'] = '=';
        $filter_record['valore']    = $_GET['id_genitore'];

        $filters[] = $filter_record;

        $filter_record              = [];
        $filter_record['chiave']    = 'genitore';
        $filter_record['operatore'] = '=';
        $filter_record['valore']    = $_GET['genitore'];

        $filters[] = $filter_record;

        $filter_record              = [];
        $filter_record['chiave']    = 'estensione';
        $filter_record['operatore'] = 'NOT IN';
        $filter_record['valore']    = '("png", "gif", "jpg", "jpeg")';

        $filters[] = $filter_record;


        $opts['filters']   = $filters;
        $opts['operatore'] = 'AND';

        $list = $allegati->getlist($opts);

        //Utility::pre($list[0]);

        foreach($list as $l) {
          if($l->fields['fileData']['nomefile']['exists']) {
            $allegato               = [];
            $allegato['nomefile']   = $l->fields['nomefile'];
            $allegato['estensione'] = $l->fields['fileData']['nomefile']['ext'];
            if(in_array($allegato['estensione'], ['jpg', 'png', 'gif', 'jpeg'])) {
              $allegato['thumb'] = $l->fields['fileData']['nomefile']['versioni'][0]['rel_path'];
              $allegato['fa']    = 'file-image-o';
            } else {
              $allegato['thumb'] = '/images/mime/16x16/'.$allegato['estensione'].'.png';
              switch($allegato['estensione']) {
                case 'pdf':
                  $allegato['fa'] = 'file-pdf-o';
                  break;
                case 'xls':
                  $allegato['fa'] = 'file-excel-o';
                  break;
                case 'zip':
                case 'rar':
                  $allegato['fa'] = 'file-archive-o';
                  break;
                case 'mp3':
                case 'wav':
                  $allegato['fa'] = 'file-audio-o';
                  break;
                default:
                  $allegato['fa'] = 'file-o';
                  break;
              }
            }

            foreach($langs as $sigla_lingua) {
              $allegato['descrizione'][$sigla_lingua] = $l->fields[$sigla_lingua]['descrizione'];
              $allegato['title'][$sigla_lingua]       = $l->fields[$sigla_lingua]['title'];
              $allegato['alt'][$sigla_lingua]         = $l->fields[$sigla_lingua]['alt'];
            }
            $allegato['time'] = $l->fields['time'];
            $allegato['id']   = $l->fields['id'];

            $cacheNiceUrls = DOC_ROOT.REL_ROOT.UPLOAD.'cache/rewrite.json';
            $niceUrls      = false;
            if(file_exists($cacheNiceUrls)) {
              $niceUrls = json_decode(file_get_contents($cacheNiceUrls), true);
            }

            if($l->isAnImage()) {
              $allegato['versioni'] = [];

              foreach($l->fileData['nomefile']['versioni'] as $f) {
                $record            = [];
                $record['path']    = $f['rel_path'];
                $record['niceUrl'] = false;
                if($niceUrls && array_search($record['path'], $niceUrls)) {
                  $record['niceUrl'] = array_search($record['path'], $niceUrls);
                }
                $record['dimensioni']   = $f['imgData'][0].'x'.$f['imgData'][1];
                $record['width']        = $f['imgData'][0];
                $record['height']       = $f['imgData'][1];
                $allegato['versioni'][] = $record;
              }

            } else {
              $allegato['versioni'] = false;
            }

            $return[] = $allegato;
          } else {
            //$l->delete();
          }

        }

        $options                  = [];
        $options['tableFilename'] = 'allegati';
        $options['debug']         = 0;

        $imgSetting = [];

        $imgSetting                              = [];
        $imgSetting['dimensione']                = '100x100';
        $imgSetting['tipo']                      = 'crop';
        $imgSetting['options']                   = [];
        $imgSetting['options']['type_of_resize'] = 'lossless';

        $options['imgSettings']['nomefile'][] = $imgSetting;

        $imgSetting         = [];
        $imgSetting['tipo'] = 'none';

        $options['imgSettings']['nomefile'][] = $imgSetting;

        $NOME_COSTANTE = 'SET_'.strtoupper($_GET['genitore']).'_IMG_SIZE';

        $costants = get_defined_constants();

        if(isset($costants[$NOME_COSTANTE])) {
          $dimensioni = $costants[$NOME_COSTANTE];
        } else {
          $dimensioni = '300x200-1024x768';
        }

        $dimensioni = explode('-', strtolower(str_replace('px', '', $dimensioni)));

        foreach($dimensioni as $k => $dimensione) {
          if(strpos($dimensione, '|') !== false) {
            @list($dimensione, $tipo, $typeofcrop) = explode('|', $dimensione);
          } else {
            if($k) {
              $tipo = 'thumbnail';
            } else {
              $tipo = 'crop';
            }
          }

          $imgSetting = [];

          $imgSetting               = [];
          $imgSetting['dimensione'] = $dimensione;
          $imgSetting['tipo']       = $tipo;
          if($tipo == 'crop') {
            $imgSetting['options'] = [];
            if(isset($typeofcrop) && $typeofcrop) {
              $imgSetting['options']['type_of_resize'] = $typeofcrop;
            } else {
              $imgSetting['options']['type_of_resize'] = 'lossless';
            }
          }
          $options['imgSettings']['nomefile'][] = $imgSetting;
        }

        $allegati                                       = new Allegati($options);
        $allegati->dataDescription['types']['nomefile'] = 'image';


        $opts                 = [];
        $opts['forceAllLang'] = true;
        $opts['sortField']    = 'ordine';
        $opts['sortOrder']    = 'ASC';
        $opts['debug']        = 0;

        $filters = [];

        $filter_record              = [];
        $filter_record['chiave']    = 'id_genitore';
        $filter_record['operatore'] = '=';
        $filter_record['valore']    = $_GET['id_genitore'];

        $filters[] = $filter_record;

        $filter_record              = [];
        $filter_record['chiave']    = 'genitore';
        $filter_record['operatore'] = '=';
        $filter_record['valore']    = $_GET['genitore'];

        $filters[] = $filter_record;

        $filter_record              = [];
        $filter_record['chiave']    = 'estensione';
        $filter_record['operatore'] = 'IN';
        $filter_record['valore']    = '("png", "gif", "jpg", "jpeg")';

        $filters[] = $filter_record;

        $opts['filters']   = $filters;
        $opts['operatore'] = 'AND';

        $list = $allegati->getlist($opts);


        foreach($list as $l) {
          if($l->fields['fileData']['nomefile']['exists']) {
            $allegato               = [];
            $allegato['nomefile']   = $l->fields['nomefile'];
            $allegato['estensione'] = $l->fields['fileData']['nomefile']['ext'];
            $allegato['fa']         = '';
            if(in_array($allegato['estensione'], ['jpg', 'png', 'gif', 'jpeg'])) {
              $allegato['thumb'] = $l->fields['fileData']['nomefile']['versioni'][0]['rel_path'];
              $allegato['fa']    = 'file-image-o';
            } else {
              $allegato['thumb'] = '/images/mime/16x16/'.$allegato['estensione'].'.png';
              switch($allegato['estensione']) {
                case 'pdf':
                  $allegato['fa'] = 'file-pdf-o';
                  break;
                case 'xls':
                  $allegato['fa'] = 'file-excel-o';
                  break;
                case 'zip':
                case 'rar':
                  $allegato['fa'] = 'file-archive-o';
                  break;
                case 'mp3':
                case 'wav':
                  $allegato['fa'] = 'file-audio-o';
                  break;
                default:
                  $allegato['fa'] = 'file-o';
                  break;
              }

            }
            foreach($langs as $sigla_lingua) {
              //$allegato['descrizione'][$sigla_lingua] = $l->fields[$sigla_lingua]['descrizione'];
              $allegato['title'][$sigla_lingua] = $l->fields[$sigla_lingua]['title'];
              $allegato['alt'][$sigla_lingua]   = $l->fields[$sigla_lingua]['alt'];
            }
            $allegato['time'] = $l->fields['time'];
            $allegato['id']   = $l->fields['id'];

            $cacheNiceUrls = DOC_ROOT.REL_ROOT.UPLOAD.'cache/rewrite.json';
            $niceUrls      = false;
            if(file_exists($cacheNiceUrls)) {
              $niceUrls = json_decode(file_get_contents($cacheNiceUrls), true);
            }

            if($l->isAnImage()) {
              $allegato['versioni'] = [];

              foreach($l->fileData['nomefile']['versioni'] as $f) {
                $record            = [];
                $record['path']    = $f['rel_path'];
                $record['niceUrl'] = false;
                if($niceUrls && array_search($record['path'], $niceUrls)) {
                  $record['niceUrl'] = REL_ROOT.array_search($record['path'], $niceUrls);
                }
                $record['dimensioni']   = $f['imgData'][0].'x'.$f['imgData'][1];
                $record['width']        = $f['imgData'][0];
                $record['height']       = $f['imgData'][1];
                $allegato['versioni'][] = $record;
              }

            } else {
              $allegato['versioni'] = false;
            }

            $return[] = $allegato;
          } else {
            //$l->delete();
          }

        }

      }

      echo json_encode($return);
      die;

    } else if(array_key_exists("_method", $_POST)) {


      switch($_POST['_method']) {

        case 'PUT':
          $model = json_decode($_POST['model']);
          if(Utility::isPositiveInt($model->id)) {

            $options                  = [];
            $options['tableFilename'] = 'allegati';
            $options['forceAllLang']  = true;
            $options['debug']         = 0;


            $allegato             = new Allegati($options);
            $opts['forceAllLang'] = true;
            $allegato             = $allegato->getById($model->id, $opts);

            if(Utility::isPositiveInt($model->ordine)) {
              $allegato->ordine = $model->ordine;
            }

            if($allegato) {
              if($allegato->isAnImage()) {
                foreach($langs as $l) {
                  $allegato->$l = ['alt' => $model->alt->$l];
                  $allegato->$l = ['title' => $model->title->$l];
                  $allegato->$l = ['descrizione' => $model->descrizione->$l];
                }
              } else {
                foreach($langs as $l) {
                  $allegato->$l = ['title' => $model->alt->$l];
                  $allegato->$l = ['title' => $model->title->$l];
                  $allegato->$l = ['descrizione' => $model->descrizione->$l];
                }
              }
            } else {

            }

            if($allegato->isValid()) {
              $opts['debug'] = 0;
              $allegato->save($opts);
            }

          }
          break;

        case 'DELETE':
          $options                  = [];
          $options['tableFilename'] = 'allegati';
          $options['debug']         = 0;

          $allegato = new Allegati($options);

          $opts['forceAllLang'] = true;

          $allegato = $allegato->getById($_GET['id']);

          if($allegato) {
            $allegato->delete();
          }
          break;

        case 'upload':

          $return           = [];
          $return['result'] = 1;
          $return['error']  = [];
          $return['files']  = [];

          $NOME_COSTANTE_IMG_LIMIT  = 'SET_'.strtoupper($_POST['genitore']).'_NIMG_LIMIT';
          $NOME_COSTANTE_FILE_LIMIT = 'SET_'.strtoupper($_POST['genitore']).'_NFILES_LIMIT';

          $costants = get_defined_constants();

          if(isset($costants[$NOME_COSTANTE_IMG_LIMIT])) {
            $limitImg = $costants[$NOME_COSTANTE_IMG_LIMIT];
          } else {
            $limitImg = 0;
          }

          if(isset($costants[$NOME_COSTANTE_FILE_LIMIT])) {
            $limitFiles = $costants[$NOME_COSTANTE_FILE_LIMIT];
          } else {
            $limitFiles = 0;
          }

          $NOME_IMG_ALLOWED   = 'SET_'.strtoupper($_POST['genitore']).'_IMG_ALLOWED';
          $NOME_FILES_ALLOWED = 'SET_'.strtoupper($_POST['genitore']).'_FILES_ALLOWED';

          if(isset($costants[$NOME_IMG_ALLOWED])) {
            $imgAllowed = $costants[$NOME_IMG_ALLOWED];
          } else {
            $imgAllowed = true;
          }

          if(isset($costants[$NOME_FILES_ALLOWED])) {
            $filesAllowed = $costants[$NOME_FILES_ALLOWED];
          } else {
            $filesAllowed = false;
          }

          $numeroImmagini = 0;
          $numeroFiles    = 0;

          if($limitImg || $limitFiles) {

            $options                  = [];
            $options['tableFilename'] = 'allegati';

            $allegati = new Allegati($options);

            if($limitImg) {

              $opzioni                 = [];
              $opzioni['genitore']     = $_POST['genitore'];
              $opzioni['id_genitore']  = $_POST['id_genitore'];
              $opzioni['forceAllLang'] = false;   // boolean, se impostato a true reperisce tutte le lingue altrimenti esegue ricerche ed estrazione sulla sola lingua attuale
              $opzioni['debug']        = 0;       // debug
              $opzioni['countOnly']    = true;   // passare true per ottenere solo il numero di record che soddisfano la ricerca
              $opzioni['estensioni']   = 'img';

              $numeroImmagini = $allegati->getAllegati($opzioni);

            }

            if($limitFiles) {

              $opzioni                = [];
              $opzioni['genitore']    = $_POST['genitore'];
              $opzioni['id_genitore'] = $_POST['id_genitore'];
              $opzioni['countOnly']   = true;   // passare true per ottenere solo il numero di record che soddisfano la ricerca
              $opzioni['estensioni']  = 'notimg';

              $numeroFiles = $allegati->getAllegati($opzioni);

            }

          }

          $stopImages = false;
          $stopFiles  = false;

          foreach($_FILES['file']['tmp_name'] as $k => $tmp_name) {

            $F             = [];
            $F['tmp_name'] = $_FILES['file']['tmp_name'][$k];
            $F['name']     = $_FILES['file']['name'][$k];
            $F['size']     = $_FILES['file']['size'][$k];
            $F['error']    = $_FILES['file']['error'][$k];

            $pz = explode('.', $F['name']);

            $ext = strtolower(array_pop($pz));

            $image = false;

            if(in_array($ext, ['gif', 'jpeg', 'jpg', 'png'])) {
              $image = true;
            }

            $dest = DOC_ROOT.REL_ROOT.UPLOAD.'allegati/temp/';

            if(!is_dir($dest)) {
              Utility::mkdirp($dest);
            }

            if($image) {
              if($imgAllowed) {
                $numeroImmagini++;
                if($limitImg && $numeroImmagini > $limitImg) {
                  $stopImages        = true;
                  $return['error'][] = sprintf(Traduzioni::getLang('allegati', 'LIMITE_IMMAGINI_RAGGIUNTO'), $F['name'], $limitImg);
                }
              } else {
                $stopImages        = true;
                $return['error'][] = sprintf(Traduzioni::getLang('allegati', 'IMMAGINI_NON_AMMESSE'), $F['name']);
              }
            } else {
              if($filesAllowed) {
                $numeroFiles++;
                if($limitFiles && $numeroFiles > $limitFiles) {
                  $stopFiles         = true;
                  $return['error'][] = sprintf(Traduzioni::getLang('allegati', 'LIMITE_FILES_RAGGIUNTO'), $F['name'], $limitFiles);
                }
              } else {
                $stopFiles         = true;
                $return['error'][] = sprintf(Traduzioni::getLang('allegati', 'FILES_NON_AMMESSI'), $F['name']);
              }
            }

            if($image && $stopImages) {
              $return['result'] = 0;
            } elseif(!$image && $stopFiles) {
              $return['result'] = 0;
            } else {

              if(in_array($ext, explode(',', SET_ACCEPTED_EXT))) {

                $name = strtolower(Utility::sanitize(implode('', $pz))).'.'.$ext;

                $hash = md5($_SESSION['LOG_INFO']['UID'].'-'.time().'-'.$name);

                $nameEncoded = $hash.'.'.$name;

                move_uploaded_file($F['tmp_name'], $dest.$nameEncoded);

                $file               = [];
                $file['hash']       = $hash;
                $file['nomefile']   = $name;
                $file['estensione'] = $ext;

                $return['files'][] = $file;

              } else {

                $return['result']  = 0;
                $return['error'][] = sprintf(Traduzioni::getLang('allegati', 'ESTENSIONE_NON_VALIDA'), $F['name'], $ext);

              }

            }


          }

          $return['error'] = implode('<br />', $return['error']);

          echo json_encode($return);
          die;
          break;
        case 'SAVESORT':
          $re = '/([\d]+\,?)+/';

          if(preg_match($re, $_POST['order'])) {

            $filters = [];

            $filter_record              = [];
            $filter_record['chiave']    = 'allegati.id';
            $filter_record['operatore'] = 'IN';
            $filter_record['valore']    = '('.$_POST['order'].')';

            $filters[] = $filter_record;

            $options                  = [];
            $options['tableFilename'] = 'allegati';
            $options['debug']         = 0;

            $allegato = new Allegati($options);

            $opts                 = [];
            $opts['sortField']    = 'FIELD(allegati.id,'.$_POST['order'].')';
            $opts['filters']      = $filters;
            $opts['operatore']    = 'AND';
            $opts['forceAllLang'] = true;

            $list = $allegato->getlist($opts);

            foreach($list as $k => $allegato) {
              $allegato->ordine = $k;
              $opts             = [];
              $allegato->save($opts);
            }
            $return['result'] = 1;

          } else {
            $return['result'] = 0;
            $return['error']  = Traduzioni::getLang('default', 'BAD_PARAMS');
          }
          break;

        case 'GENERAIMG':

          $return['result'] = 1;

          list($name, $ext) = explode('.', basename($_POST['path']));

          $name = $name.'.'.$ext;

          $re = '/(-([^\-]){1})?\-([^\-]+)\-([\d]+)\.'.$ext.'$/';

          preg_match_all($re, $name, $matches);

          $dim_data           = [];
          $dim_data['width']  = $_POST['width'];
          $dim_data['height'] = $_POST['height'];
          $dim_data['top']    = $_POST['y1'];
          $dim_data['left']   = $_POST['x1'];
          $dim_data['center'] = '0';
          $dim_data['resize'] = '0';

          $path      = DOC_ROOT.REL_ROOT.substr($_POST['path'], 1);
          $originale = DOC_ROOT.REL_ROOT.substr($_POST['originale'], 1);

          Image::crop($originale, $path, $dim_data, false, 0);

          list($dimensioni['w'], $dimensioni['h']) = explode('x', $matches[3][0]);

          if(isset($matches[2][0]) && $matches[2][0]) {
            $tipo = $matches[2][0];
          } else {
            $tipo = 'c';
          }
          switch($tipo) {
            case 'c':
              $dim_data           = [];
              $dim_data['width']  = $dimensioni['w'];
              $dim_data['height'] = $dimensioni['h'];
              $dim_data['center'] = '1';
              $dim_data['resize'] = '1';
              Image::crop($path, $path, $dim_data, false, 0);
              break;
            case 't':
              Image::thumbnail(dirname($path).'/', $path, $dimensioni['w'], $dimensioni['h'], basename($path));
              break;
          }

          $return['versione'] = $matches[4][0] - 2;
          echo json_encode($return);
          die;

          break;

        case 'SEPPIA':

          $return['result'] = 1;

          list($name, $ext) = explode('.', basename($_POST['path']));

          $name = $name.'.'.$ext;

          $re = '/(-([^\-]){1})?\-([^\-]+)\-([\d]+)\.'.$ext.'$/';

          preg_match_all($re, $name, $matches);

          $path = DOC_ROOT.REL_ROOT.substr($_POST['path'], 1);

          Image::seppia($path, $path);

          $return['versione'] = $matches[4][0] - 2;

          echo json_encode($return);
          die;

          break;

        case 'PIXELATE':

          $return['result'] = 1;

          list($name, $ext) = explode('.', basename($_POST['path']));

          $name = $name.'.'.$ext;

          $re = '/(-([^\-]){1})?\-([^\-]+)\-([\d]+)\.'.$ext.'$/';

          preg_match_all($re, $name, $matches);

          $path = DOC_ROOT.REL_ROOT.substr($_POST['path'], 1);


          Image::pixelate($path, $path);

          $return['versione'] = $matches[4][0] - 2;

          echo json_encode($return);
          die;

          break;

        case 'SCATTER':

          $return['result'] = 1;

          list($name, $ext) = explode('.', basename($_POST['path']));

          $name = $name.'.'.$ext;

          $re = '/(-([^\-]){1})?\-([^\-]+)\-([\d]+)\.'.$ext.'$/';

          preg_match_all($re, $name, $matches);

          $path = DOC_ROOT.REL_ROOT.substr($_POST['path'], 1);


          Image::scatter($path, $path);

          $return['versione'] = $matches[4][0] - 2;

          echo json_encode($return);
          die;

          break;

        case 'BN':

          $return['result'] = 1;

          list($name, $ext) = explode('.', basename($_POST['path']));

          $name = $name.'.'.$ext;

          $re = '/(-([^\-]){1})?\-([^\-]+)\-([\d]+)\.'.$ext.'$/';

          preg_match_all($re, $name, $matches);

          $path = DOC_ROOT.REL_ROOT.substr($_POST['path'], 1);


          Image::greyscale($path, $path);

          $return['versione'] = $matches[4][0] - 2;

          echo json_encode($return);
          die;

          break;

        case 'EMBOSS':

          $return['result'] = 1;

          list($name, $ext) = explode('.', basename($_POST['path']));

          $name = $name.'.'.$ext;

          $re = '/(-([^\-]){1})?\-([^\-]+)\-([\d]+)\.'.$ext.'$/';

          preg_match_all($re, $name, $matches);

          $path = DOC_ROOT.REL_ROOT.substr($_POST['path'], 1);


          Image::fx($path, $path, IMG_FILTER_EMBOSS);

          $return['versione'] = $matches[4][0] - 2;

          echo json_encode($return);
          die;

          break;

        case 'INTERLACE':

          $return['result'] = 1;

          list($name, $ext) = explode('.', basename($_POST['path']));

          $name = $name.'.'.$ext;

          $re = '/(-([^\-]){1})?\-([^\-]+)\-([\d]+)\.'.$ext.'$/';

          preg_match_all($re, $name, $matches);

          $path = DOC_ROOT.REL_ROOT.substr($_POST['path'], 1);


          Image::interlace($path, $path);

          $return['versione'] = $matches[4][0] - 2;

          echo json_encode($return);
          die;

          break;

        case 'NEGATIVE':

          $return['result'] = 1;

          list($name, $ext) = explode('.', basename($_POST['path']));

          $name = $name.'.'.$ext;

          $re = '/(-([^\-]){1})?\-([^\-]+)\-([\d]+)\.'.$ext.'$/';

          preg_match_all($re, $name, $matches);

          $path = DOC_ROOT.REL_ROOT.substr($_POST['path'], 1);


          Image::fx($path, $path, IMG_FILTER_NEGATE);

          $return['versione'] = $matches[4][0] - 2;

          echo json_encode($return);
          die;

          break;

        case 'REFLECT':

          $return['result'] = 1;

          list($name, $ext) = explode('.', basename($_POST['path']));

          $name = $name.'.'.$ext;

          $re = '/(-([^\-]){1})?\-([^\-]+)\-([\d]+)\.'.$ext.'$/';

          preg_match_all($re, $name, $matches);

          $path = DOC_ROOT.REL_ROOT.substr($_POST['path'], 1);


          Image::reflect($path, $path);

          $return['versione'] = $matches[4][0] - 2;

          echo json_encode($return);
          die;

          break;

      }

    } else {

      $options                  = [];
      $options['tableFilename'] = 'allegati';
      $options['forceAllLang']  = true;
      $options['debug']         = 0;

      $model = json_decode($_POST['model']);

      $image = false;


      if(in_array($model->estensione, ['jpg', 'png', 'gif', 'jpeg'])) {

        $image = true;

        $imgSetting = [];

        $imgSetting                              = [];
        $imgSetting['dimensione']                = '100x100';
        $imgSetting['tipo']                      = 'crop';
        $imgSetting['options']                   = [];
        $imgSetting['options']['type_of_resize'] = 'lossless';

        $options['imgSettings']['nomefile'][] = $imgSetting;

        $imgSetting         = [];
        $imgSetting['tipo'] = 'none';

        $options['imgSettings']['nomefile'][] = $imgSetting;

        $NOME_COSTANTE = 'SET_'.strtoupper($model->genitore).'_IMG_SIZE';

        $costants = get_defined_constants();

        if(isset($costants[$NOME_COSTANTE])) {
          $dimensioni = $costants[$NOME_COSTANTE];
        } else {
          $dimensioni = '300x200-1024x768';
        }

        $dimensioni = explode('-', strtolower(str_replace('px', '', $dimensioni)));

        foreach($dimensioni as $k => $dimensione) {
          if(strpos($dimensione, '|') !== false) {
            @list($dimensione, $tipo, $typeofcrop) = explode('|', $dimensione);
          } else {
            if($k) {
              $tipo = 'thumbnail';
            } else {
              $tipo = 'crop';
            }
          }

          $imgSetting = [];

          $imgSetting               = [];
          $imgSetting['dimensione'] = $dimensione;
          $imgSetting['tipo']       = $tipo;
          if($tipo == 'crop') {
            $imgSetting['options'] = [];
            if(isset($typeofcrop) && $typeofcrop) {
              $imgSetting['options']['type_of_resize'] = $typeofcrop;
            } else {
              $imgSetting['options']['type_of_resize'] = 'lossless';
            }
          }
          $options['imgSettings']['nomefile'][] = $imgSetting;
        }

      }

      $a = new Allegati($options);

      if(in_array($model->estensione, ['jpg', 'png', 'gif', 'jpeg'])) {
        $a->dataDescription['types']['nomefile'] = 'image';
      }

      $a->nomefile = [DOC_ROOT.REL_ROOT.UPLOAD.'allegati/temp/'.$model->hash.'.'.$model->nomefile, $model->nomefile];

      $a->id_genitore = $model->id_genitore;
      $a->genitore    = $model->genitore;
      $a->time        = $model->time;
      $a->ordine      = $a->getNextOrderAllegato();
      $a->estensione  = $model->estensione;
      $a->options     = $model->options;
      foreach($langs as $l) {
        $a->$l = ['descrizione' => ''];
      }

      $opts          = [];
      $opts['debug'] = 0;

      if($a->isValid()) {

        $a->save($opts);

        $allegato               = [];
        $allegato['nomefile']   = $a->fields['nomefile'];
        $allegato['estensione'] = $a->fields['fileData']['nomefile']['ext'];
        if(in_array($allegato['estensione'], ['jpg', 'png', 'gif', 'jpeg'])) {
          $allegato['thumb'] = $a->fields['fileData']['nomefile']['versioni'][0]['rel_path'];
          $allegato['fa']    = 'file-image-o';
        } else {
          $allegato['thumb'] = '/images/mime/16x16/'.$allegato['estensione'].'.png';
          switch($allegato['estensione']) {
            case 'pdf':
              $allegato['fa'] = 'file-pdf-o';
              break;
            case 'xls':
              $allegato['fa'] = 'file-excel-o';
              break;
            case 'zip':
            case 'rar':
              $allegato['fa'] = 'file-archive-o';
              break;
            case 'mp3':
            case 'wav':
              $allegato['fa'] = 'file-audio-o';
              break;
            default:
              $allegato['fa'] = 'file-o';
              break;
          }
        }
        foreach($langs as $sigla_lingua) {
          $allegato['descrizione'][$sigla_lingua] = $a->fields[$sigla_lingua]['descrizione'];
          $allegato['title'][$sigla_lingua]       = $a->fields[$sigla_lingua]['title'];
          $allegato['alt'][$sigla_lingua]         = $a->fields[$sigla_lingua]['alt'];
        }
        $allegato['time'] = $a->fields['time'];
        $allegato['id']   = $a->fields['id'];

        if($a->isAnImage()) {
          $allegato['versioni'] = [];

          foreach($a->fileData['nomefile']['versioni'] as $f) {
            $record         = [];
            $record['path'] = $f['rel_path'];

            $record['dimensioni'] = $f['imgData'][0].'x'.$f['imgData'][1];
            $record['width']      = $f['imgData'][0];
            $record['height']     = $f['imgData'][1];

            $allegato['versioni'][] = $record;
          }

        } else {
          $allegato['versioni'] = false;
        }


        echo json_encode($allegato);
        die;

      } else {
        header("HTTP/1.1 500");
        echo $a->getErrors();
        die;
      }

    }

  } else {

    $return['error']  = Traduzioni::getLang('default', 'NOT_AUTH');
    $return['result'] = 0;

  }

}
?>