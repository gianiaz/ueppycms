<?php
/***************/
/** v.1.08    **/
/***************/
/** CHANGELOG **/
/***************************************************************************************************/
/** v.1.08 (06/11/2015, 16.56)                                                                    **/
/** - Aggiunto namespace.                                                                         **/
/** v.1.07 (18/03/2014)                                                                           **/
/** - Bugfix, readurl perdeva i valori ricevuti via get nel caso in cui ci si trovasse nella root **/
/** v.1.06 (29/11/2013)                                                                           **/
/** - SECURITY: FIX DEL METODO readUrl                                                            **/
/**   Tutto ciò che arriva da $_GET nell'url ora finisce in $GET_REC['query'][parametro]          **/
/**   In pratica prima questo url:                                                                **/
/**   http://ueppy/it/ dava come $GET_REC['lang'] "it" e non veniva controllato in quanto si      **/
/**   poteva dare per scontato che l'htaccess lo facesse passare solo se formato da 2 lettere     **/
/**   minuscole.                                                                                  **/
/**   Il problema sorge quando si passa un url di questo genere:                                  **/
/**   http://ueppy/it/?lang=valorearbitrario                                                      **/
/**   In questo caso l'htaccess passa come lang it, ma poi questo viene sovrascritto dal lang     **/
/**   nell'url                                                                                    **/
/**   Pertanto, per evitare di dare per scontato la sicurezza di determinati parametri, tutto ciò **/
/**   che arriverà dall'url con il classico formato ?paremtro=valore finirà in $GET_REC['query']  **/
/**   e andrà trattato con tutti gli accorgimenti del caso.                                       **/
/**   Per essere più chiari possibili, il precedente esempio:                                     **/
/**   http://ueppy/it/?lang=valorearbitrario                                                      **/
/**   creerà questo $GET_REC                                                                      **/
/**                                                                                               **/
/**   Tipo: Array                                                                                 **/
/**   Numero Elementi :2                                                                          **/
/**   Array                                                                                       **/
/**   (                                                                                           **/
/**       [lang] => it                                                                            **/
/**       [query] => Array                                                                        **/
/**           (                                                                                   **/
/**               [lang] => valorearbitrario                                                      **/
/**           )                                                                                   **/
/**                                                                                               **/
/**   )                                                                                           **/
/**   Ovviamente questo implica alcuni possibili malfunzionamenti i procedure che utilizzano ad   **/
/**   esempio $GET_REC['pag'] prelevato dall'url, e dovranno essere aggiornate per usare          **/
/**   $GET_REC['query']['pag'] e questo valore andrà controllato in modo da essere sicuri che     **/
/**   contenga un valore numerico.                                                                **/
/**                                                                                               **/
/** v.1.05 (24/10/2013)                                                                           **/
/** - Aggiunti i puntini di sospensione al metodo htmltruncate.                                   **/
/**                                                                                               **/
/** v.1.04 (12/09/2013)                                                                           **/
/** - Modificato il metodo textpreview, che permette di troncare anche le parole tenendo          **/
/**   in considerazione la lunghezza dei puntini di sospensione                                   **/
/**                                                                                               **/
/** v.1.03 (01/07/2013)                                                                           **/
/** - Aggiunto il metodo validMail per validare gli indirizzi email                               **/
/**                                                                                               **/
/** v.1.02 (18/01/2013)                                                                           **/
/** - Modificato metodo mkdirp, rimosso codice obsoleto                                           **/
/**                                                                                               **/
/** v.1.01 (14/01/2013)                                                                           **/
/** - Bugfix (Strict Standards: Only variables should be passed by reference)                     **/
/**                                                                                               **/
/** v.1.00                                                                                        **/
/** - Versione stabile                                                                            **/
/**                                                                                               **/
/***************************************************************************************************/
namespace Ueppy\utils;

class Utility {

  /**
   *
   * Ritorna la stampa formattata del contenuto e struttura
   * dell'array o dell'oggetto passato, c'è un controllo cablato sulla pagina
   * ajax.php che fa in modo che il risultato venga restituito in forma testuale,
   * in modo da vedere bene anche il debug tramite firebug.
   *
   * Stampa un trace di tutti i file che sono stati coinvolti nella chiamata.
   *
   * @param mixed $array array/stringa/oggetto
   * @param string $file file di output
   * @param boolean $htmlout output in sintassi html
   */
  static function pre($array, $file = '', $htmlout = -1) {

    $wrap = 130;

    $debug_array = debug_backtrace();
    $debug_array = array_reverse($debug_array);

    if(isset($GLOBALS['UTILITYPREFILE']) && $GLOBALS['UTILITYPREFILE'] && !$file) {
      $file = $GLOBALS['UTILITYPREFILE'];
    }

    if($file) {
      if(!is_dir(basename($file))) {
        Utility::pre('Directory '.basename($file).' inesistente');
        die;
      } elseif(!is_writable($file)) {
        Utility::pre('File '.$file.' non scrivibile');
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
        $pre       = "<code class=\"debug ui-corner-all\">";
        $pre_close = "</code>";
      }
    } else {
      if($file) {
        $acapo     = "\n";
        $pre       = "\n-------\n";
        $pre_close = "\n------\n";
      } else {
        $acapo     = "\n";
        $pre       = "<pre class=\"debug\">";
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

  static function debug($var, $opts = null) {

    $opzioni          = [];
    $opzioni['level'] = 'log';

    if($opts) {
      $opzioni = array_replace_recursive($opzioni, $opts);
    }


    if(isset($GLOBALS['debugbar']) && $GLOBALS['debugbar']) {

      $debug_array = debug_backtrace();
      $debug_array = array_reverse($debug_array);

      $string = [];
      foreach($debug_array as $da) {
        $f = $da['file'];
        if(defined('DOC_ROOT')) {
          $f = str_replace(DOC_ROOT, '', $f);
        }
        $string[] = $f.', '.$da['line'];
      }

      $toDbg          = [];
      $toDbg['stack'] = implode(' => ', $string);
      $toDbg['var']   = $var;

      switch($opzioni['level']) {
        case 'log':
          $GLOBALS['debugbar']["messages"]->info($toDbg);
          break;
        case 'error':
          $GLOBALS['debugbar']["messages"]->error($toDbg);
          break;
      }

    }
  }

  /**
   *
   * Dato un testo e il numero di caratteri questo viene troncato senza
   * spezzare la parola, aggiungendo i puntini di sospensione
   *
   * @param String $text Stringa originale non troncata
   * @param int $caratteri Numero di caratteri a cui troncare la stringa
   * @return string Testo troncato con puntini a seguire
   */

  static function textpreview($text, $caratteri = 50, $tronca = false) {

    if(strlen($text) > ($caratteri - 3)) {
      if(!$tronca) {
        $newtext    = wordwrap($text, $caratteri, "|");
        $nuovotesto = explode("|", $newtext);

        return $nuovotesto[0]."...";
      } else {
        $text = substr($text, 0, ($caratteri - 3));

        return $text.'...';
      }
    } else {
      return $text;
    }
  }

  static function backform($form_action, $form_data, $link = 'torna', $form_id = 'backform', $form_method = 'post') {

    $str = '';
    $str .= '<form id="'.$form_id.'" method="'.$form_method.'" action="'.$form_action.'">';
    $str .= '<div>';
    $str .= "\n";
    foreach($form_data as $key => $val) {
      $str .= '<input type="hidden" id="'.$key.'" name="'.$key.'" value="'.$val.'" />';
      $str .= "\n";
    }
    $str .= '<a href="#" onclick="$(this).parent().parent()[0].submit();return false;">'.$link.'</a>';
    $str .= "\n";
    $str .= '</div>';
    $str .= '</form>';

    return $str;

  }

  /**
   *
   * Trasforma e restituisce un testo sostituendo i ritorni a capo html con
   * i ritorni a capo testuali
   *
   * @param string $val Testo da modificare
   *
   * @return string Testo modificato
   */

  static function br2nl($val) {

    return str_replace("<br />", "\n", $val);

  }

  /**
   * Funzione per la visualizzazione formattata dell'ultima query e
   * dell'errore da essa generata
   *
   * Stampa un trace dei file coinvolti, l'errore mysql e la query passata come parametro
   */
  static function showmysqlerror($query, $db_conn) {

    $debug_array = debug_backtrace();
    $debug_array = array_reverse($debug_array);
    if(basename($debug_array[0]['file']) == 'ajax.php') {
      $acapo     = "\n";
      $pre       = "\n-------\n";
      $pre_close = "\n-------\n";
    } else {
      $acapo     = "<br />";
      $pre       = "<pre>";
      $pre_close = "</pre>";
    }
    echo $pre;
    foreach($debug_array as $da) {
      echo "File :".$da['file'].$acapo;
      echo "Linea :".$da['line'].$acapo;
    }
    echo "Query sql :".$query.$acapo;
    echo "Errore    :".wordwrap(mysqli_error($db_conn), 80, $acapo);
    echo $pre_close;
  }


  /**
   * Passando un link a questa funzione controlla che inizi con il protocollo
   * http, in caso contrario modifica la stringa e la ritorna con il protocollo http aggiunto.
   *
   * @param string URL
   * @return string URL modificato
   */

  static function addhttp($webpage) {

    $pos = strpos($webpage, 'http://');

    if($pos === false && $webpage != '') {

      $webpage = "http://".$webpage;

    }

    return $webpage;

  }

  /**
   * Metodo per la rimozione ricorsiva di file e directories partendo da quella fornita
   * ATTENZIONE UN UTILIZZO ERRATO DI QUESTA FUNZIONE PUO' CAUSARE LA PERDITA DI TUTTI I DATI
   * TESTARE PRIMA CON IL DEBUG ATTIVATO, CHE SIMULA L'OPERAZIONE.
   * @param string $dir Percorso assoluto ad una dir sul server
   */
  static function emptyDir($dir, $debug = 0) {

    if($dir[strlen($dir) - 1] != '/') {
      $dir .= '/';
    }

    $contents = glob($dir."*");

    $contents2 = glob($dir.".*");


    if(is_array($contents2)) {
      foreach($contents2 as $e) {
        if(basename($e) != '.' && basename($e) != '..') {
          $contents[] = $e;
        }
      }
    }

    if($debug) {

      echo "Metodo richiamato con dir argomento :".$dir."<br />";

      echo 'Array eliminazione<br />';
      print_r($contents);
      echo '<br />';

    }

    if($contents && is_array($contents) && count($contents)) {

      foreach($contents as $element) {

        if(is_dir($element)) {

          if($debug) {

            echo "Richiamo emptydir con argomento :".$element."/<br />";

          }

          Utility::emptyDir($element."/", $debug);

        } else {

          if($debug) {
            //unlink($element);
          } else {
            @unlink($element);
          }

        }

      }

    }

    if($debug) {
      //rmdir($dir);
    } else {
      @rmdir($dir);
    }

  }

  /**
   * Copia una directory in un altra
   *
   * @param string $source Directory da copiare
   * @param string $dest Directory in cui verrà copiata.
   * @param string $debug Stampa un debug facendo comunque la copia
   */

  static function copyRecursive($source, $dest, $debug = 0) {

    if(!file_exists($dest)) {
      if($debug) {
        echo "<hr />";
        echo "Copio $source in $dest<br />";
      }

      $source = str_replace('\\', '/', $source);

      if($source{strlen($source) - 1} != '/') {
        $source .= '/';
      }

      $dest = str_replace('\\', '/', $dest);

      if($dest{strlen($dest) - 1} != '/') {
        $dest .= '/';
      }

      $dir = basename($source);

      if($debug) {
        echo "Creo innanzi tutto la dir $dest<br /> ";
      }

      Utility::mkdirp($dest);

      $chiave_ricerca = $source."*";

      if($debug) {

        Utility::pre('cerco in "'.$chiave_ricerca.'"');

      }

      $contenuto_dir = glob($chiave_ricerca);

      if($debug) {
        Utility::pre($contenuto_dir);
      }

      if($contenuto_dir && is_array($contenuto_dir) && count($contenuto_dir)) {

        foreach($contenuto_dir as $val) {

          $val = str_replace('\\', '/', $val);

          if(is_dir($val)) {

            $sorgente = $val.'/';

            $destinazione = $dest.basename($val).'/';

            if($debug) {

              echo 'Sorgente: '.$sorgente.'<br />';

              echo 'Destinazione : '.$destinazione.'<br />';

            }

            Utility::copyRecursive($sorgente, $destinazione);

          } else {

            if($debug) {

              echo '<br /><span style="color:blue;font-weight:bold">Copio '.$val.' in '.$dest.basename($val).'</span>';
            }

            copy($val, $dest.basename($val));

          }

        }

      }

    }

  }

  /**
   * Ritorna vero se il valore esiste ed è maggiore di zero.
   *
   * @param string $val Il valore può essere ad esempio un dato che arriva via GET o POST
   * @param boolean $zero Se passo vero accetto anche lo zero.
   */
  static function isPositiveInt(&$val, $zero = false) {

    if((isset($val) && preg_match('/^([0-9])*$/', $val)) && ($val || $zero)) {
      //echo "true";
      return true;
    } else {
      //echo "false";
      return false;
    }
  }


  /**
   * Livello di astrazione della funzione number_format, in questo modo nei file
   * di configurazione posso mettere per ogni lingua il formato del numero nella
   * forma #.###,###, dove verra estratto alla posizione 1 il separatore di migliaia
   * alla posizione 5 il separatore di decimali.
   * Vengono inoltre contati i caratteri dopo il separatore di decimali per determinare
   * la precisione desiderata (con i limiti di precisione del numero passato);
   *
   * @param float $numero numero da formattare
   * @param string $format Formato nella forma '#.###,###'
   * @return string Numero formattato
   */
  static function numberFormatWrapper($numero, $format = '#.###,###') {

    $separatore_decimali = $format{5};
    $separatore_migliaia = $format{1};
    if(!($separatore_decimali == '.' || $separatore_decimali == ',')) {
      Utility::pre("DECIMAL SEPARATOR ERROR");
      die;
    }
    if(!($separatore_migliaia == '.' || $separatore_migliaia == ',')) {
      Utility::pre("DECIMAL SEPARATOR ERROR");
      die;
    }

    $numero_decimali = explode($separatore_decimali, $format);
    $numero_decimali = array_pop($numero_decimali);
    $numero_decimali = strlen($numero_decimali);

    return number_format($numero, $numero_decimali, $separatore_decimali, $separatore_migliaia);

  }

  /**
   * Formatta il numero in modo che questo sia valido per essere inserito in un campo
   * float di mysql
   *
   * @param float $numero numero da formattare
   * @param string $format Formato nella forma '#.###,###'
   * @return string Numero formattato
   */
  static function numberFormatMysql($numero, $format = '#.###,###') {

    if($numero) {

      $separatore_decimali = $format{5};
      $separatore_migliaia = $format{1};

      $numero = str_replace($separatore_migliaia, '', $numero);
      $numero = str_replace($separatore_decimali, '.', $numero);

    }

    return $numero;

  }

  /**
   * Genera una passaword di $n_chars caratteri, prendendo lettere (maiuscole/minuscole) e numeri
   */
  static function generatePass($n_chars) {

    $chars = [];

    $return_string = "";

    for($i = 48; $i <= 57; $i++) {

      $chars[] = $i;

    }

    for($i = 65; $i <= 90; $i++) {

      $chars[] = $i;

    }

    for($i = 97; $i <= 122; $i++) {

      $chars[] = $i;

    }

    for($i = 0; $i < $n_chars; $i++) {

      $r = rand(0, count($chars) - 1);

      $return_string .= chr($chars[$r]);

    }

    return $return_string;

  }

  /**
   * Questo metodo non fa altro che impostare l'header della pagina in modo da forzare un download
   *
   * @param string $nomefile File con percorso assoluto sul server.
   */
  static function readfileheader($nomefile = '', $download_filename = '') {


    header("Expires: Mon, 12 Jul 1976 06:20:00 GMT");
    header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");

    header("Cache-Control: no-store, no-cache, must-revalidate");
    header("Cache-Control: post-check=0, pre-check=0", false);

    header("Pragma: no-cache");

    header('Content-type: application/octet-stream');

    if(!$download_filename) {
      header('Content-Disposition: attachment; filename="'.basename($nomefile).'"');
    } else {
      header('Content-Disposition: attachment; filename="'.$download_filename.'"');
    }

    if($nomefile) {
      readfile($nomefile);
    }

  }


  /**
   * Passato un valore in byte ritorna una stringa formattata in modo leggibile.
   *
   * @param int byte Valore in byte da convertire
   * @return string Stringa formattata
   */
  static function humanReadable($byte) {

    $um = ['B', 'KB', 'MB', 'GB', 'TB'];

    $i = 0;

    while($byte >= 1024) {

      $i++;

      $byte = $byte / 1024;

    }

    return round($byte, 2).' '.$um[$i];

  }

  /**
   * Controlla se l'estensione del filename passato sia tra quelle accettate
   *
   * @param string $fname Nome del file senza percorso
   * @param array $estensioni Array di estensioni su cui fare il controllo es. array('jpg', 'jpeg', 'gif', 'png')
   *
   * @return boolean Vero o falso a seconda se il file deve essere accettato o meno.
   */
  static function checkExtension($fname, $estensioni) {

    $extensions = [];

    foreach($estensioni as $val) {

      $extensions[] = strtolower($val);

    }

    $fname = str_replace(' ', '_', $fname);
    $fname = strtolower($fname);

    $ext = Utility::getEstensione($fname);

    if(in_array($ext, $extensions)) {

      return $fname;

    } else {

      return false;

    }

  }

  /**
   * Riceve la query creata dalla funzione precedente e fa il parsing in modo che ci si ritrovi in un array
   * i valori come se fossero stati passati direttamente in get
   *
   * @param string $query_string Tutto quello che sta in $_GET[q]
   * @return array Per un link in forma http://www.sito.com/index/cmd/prova/act/azione/id/10
   *               ritorn array('cmd'=>'prova', 'act'=>'azione', 'id'=> '10')
   */
  static public function ReadUrl($query_string) {

    $query = '';

    if(strpos($query_string, 'query') !== false) {

      $pezzi = explode('query', $query_string);

      $q = explode('=', $pezzi[0]);

      $q = array_pop($q);

      if($q) {

        if($q[strlen($q) - 1] == '/') {
          $q = substr($q, 0, -1);
        }

        $q = explode('/', $q);

        $query = substr($pezzi[1], 1);

        if($query) {

          $query = explode('&', $query);

          $data = [];

          foreach($query as $queryPart) {
            $queryPart = explode('=', $queryPart);
            $data[]    = 'query['.$queryPart[0].']='.$queryPart[1];
          }

          $query = '&'.implode('&', $data);

        }

      }

    } elseif(strpos($query_string, 'q=') !== false) {
      $query_string = explode('=', $query_string);
      $q            = explode('/', array_pop($query_string));

    } else {
      $query = $query_string;
      if($query) {
        $query = explode('&', $query);

        $data = [];

        foreach($query as $queryPart) {
          $queryPart = explode('=', $queryPart);
          $data[]    = 'query['.$queryPart[0].']='.$queryPart[1];
        }

        $query = '&'.implode('&', $data);
      }
      $q = [];
    }

    $string = "";

    if($q) {

      for($i = 0; $i < count($q); $i++) {

        if($i % 2) {

          $string .= '='.$q[$i].'&';

        } else {

          $string .= $q[$i];

        }

      }

      $string = substr($string, 0, -1);

    }


    parse_str($string.$query, $output);

    return $output;

  }

  static function dirSize($dir, $files_da_escludere = '', $debug = 0) {

    if($debug) {
      Utility::pre($dir);
    }

    $mas = 0;

    $handle = opendir($dir);

    while($file = readdir($handle)) {

      if($debug) {
        Utility::pre($file);
      }

      $esclusione = ['.', '..'];

      if($files_da_escludere) {

        $esclusione = array_merge($esclusione, explode(',', $files_da_escludere));

      }

      if(!in_array($file, $esclusione) && !is_dir($dir.'/'.$file)) {
        if($debug) {
          Utility::pre('Sommo:' + filesize($dir.'/'.$file));
        }
        $mas += filesize($dir.'/'.$file);

      } else if(is_dir($dir.'/'.$file) && !in_array($file, $esclusione)) {

        if($debug) {
          Utility::pre('Utility::dirsize('.$dir.'/'.$file.','.$files_da_escludere.','.$debug.');');
        }

        $mas += Utility::dirSize($dir.'/'.$file, $files_da_escludere, $debug);

      }
    }

    return $mas;

  }


  static function glob2($directory, $type = '*', $filter = '', $exclude_array = [], $basename = 0, $recursive = 0, $debug = 0) {

    $directory = str_replace('\\', '/', $directory);

    if($debug) {
      $debug_string = "Ultimo carattere della dir passata:".$directory{strlen($directory) - 1}."";
      Utility::pre($debug_string);
    }

    if($directory{strlen($directory) - 1} != '/') {

      $directory .= '/';

    }

    if($debug) {
      $debug_string = "funzione richiamata con i paremtri:\n";
      $debug_string .= "directory:".$directory."\n";
      $debug_string .= "filter:".$filter."\n";
      Utility::pre($debug_string);
    }

    $items = [];

    $d = dir($directory);

    if($d) {

      while($entry = $d->read()) {

        $path_assoluto = $directory.$entry;

        if($debug) {
          Utility::pre("path assoluto:".$path_assoluto);

        }

        if(is_dir($path_assoluto)) {

          if($type != 'ONLY_FILES' && $entry != "." && $entry != '..') {

            if($debug) {

              Utility::pre("l'entry :".$path_assoluto." è stata aggiunta");

            }

            $add = true;

            if(in_array($entry, $exclude_array)) {

              $add = false;

            }

            if($add) {

              if($basename) {

                $items[] = basename($path_assoluto);

              } else {

                if($recursive) {

                  $items = array_merge($items, Utility::glob2($path_assoluto, $type, $filter, $exclude_array, $basename, $recursive));

                } else {

                  $items[] = $path_assoluto;

                }

              }

            }

          } else {

            if($debug) {

              Utility::pre("l'entry :".$path_assoluto." NON è stata aggiunta");

            }

          }

        } else {

          if($type != 'ONLY_DIR') {

            if($debug) {

              Utility::pre('FILE');

            }

            $ext = explode('.', $path_assoluto);

            $ext = strtolower(array_pop($ext));

            $add = true;

            if($filter) {

              if($debug) {

                Utility::pre('Estensione :'.$ext);

              }

              if($filter{0} == '!') {

                $filter_look = substr($filter, 1, strlen($filter));

                if($filter_look == $ext) {

                  $add = false;

                } else {

                  $add = true;

                }

              } else {

                $filter_look = $filter;

                if($filter_look == $ext) {

                  $add = true;

                } else {

                  $add = false;

                }

              }

            }

            if(in_array($entry, $exclude_array)) {

              $add = false;

            }

            if($add) {

              if($basename) {

                $items[] = $entry;

              } else {

                $items[] = $path_assoluto;

              }

            } elseif($debug) {

              Utility::pre("l'entry :".$entry." non soddisfa il filtro");

            }

          }

        }

      }

      $d->close();

      return $items;

    } elseif($debug) {

      echo "Non riesco a leggere la directory : ".$directory."<br />";

    }

  }

  static function sanitize($str, $delimiter = '-') {

    $clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
    $clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
    $clean = strtolower(trim($clean, '-'));
    $clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);

    return $clean;
  }

  static function mkdirp($path, $debug = 0) {

    if(!is_dir($path)) {
      if(!@mkdir($path, 0777, true)) {
        Utility::pre('Non ho potuto creare la directory '.$path);
      }
    }

  }


  static function getEstensione($filename) {

    $filename   = basename($filename);
    $estensione = explode('.', basename($filename));
    $estensione = array_pop($estensione);
    $estensione = strtolower($estensione);

    return $estensione;

  }

  static function htmlTruncate($maxLength, $html, $isUtf8 = true) {

    $printedLength = 0;
    $position      = 0;
    $tags          = [];

    $str = '';

    // For UTF-8, we need to count multibyte sequences as one character.
    $re = $isUtf8
      ? '{</?([a-z]+)[^>]*>|&#?[a-zA-Z0-9]+;|[\x80-\xFF][\x80-\xBF]*}'
      : '{</?([a-z]+)[^>]*>|&#?[a-zA-Z0-9]+;}';

    while($printedLength < $maxLength && preg_match($re, $html, $match, PREG_OFFSET_CAPTURE, $position)) {

      // estraggo il tag e la sua posizione nella stringa.
      list($tag, $tagPosition) = $match[0];

      // tiro fuori il pezzo di stringa dalla posizione attuale alla posizione del primo tag che ho trovato.
      $pz = substr($html, $position, $tagPosition - $position);

      // se la lunghezza già estratta precedentemente più quella estratta ora superano il limite
      if($printedLength + strlen($pz) > $maxLength) {
        // tronco la stringa prima del prossimo tag
        $lunghezzaRestante = $maxLength - $printedLength;
        $pz                = substr($pz, 0, $lunghezzaRestante);
        $str .= $pz;
        $printedLength = $maxLength;
        break;
      } else {
        // altrimenti aggiungo tutto il testo estratto e proseguo
        $str .= $pz;
      }
      // incremento il contatore del testo inserito
      $printedLength += strlen($pz);
      // se ho raggiunto il limte mi fermo
      if($printedLength >= $maxLength) break;

      if($tag[0] == '&' || ord($tag) >= 0x80) {
        // Pass the entity or UTF-8 multibyte sequence through unchanged.
        $str .= $tag;
        $printedLength++;
      } else {
        // gestione del tag
        $tagName = $match[1][0];
        if($tag[1] == '/') {
          // This is a closing tag.
          $openingTag = array_pop($tags);
          assert($openingTag == $tagName); // check that tags are properly nested.
          $str .= $tag;
        } else if($tag[strlen($tag) - 2] == '/') {
          $str .= $tag;
        } else {
          // Opening tag.
          $str .= $tag;
          $tags[] = $tagName;
        }
      }
      $position = $tagPosition + strlen($tag);
    }

    // stampo il testo rimanente
    if($printedLength < $maxLength && $position < strlen($html)) {
      $str .= substr($html, $position, $maxLength - $printedLength);
    }
    //$str .= '...';

    // Close any open tags.
    while(!empty($tags)) {
      $str .= sprintf('</%s>', array_pop($tags));
    }

    //Utility::pre($str.'('.(strlen(strip_tags($str))).')');
    return $str;

  }

  static function validMail($mail = false) {

    if(!$mail) {
      return false;
    }

    if(filter_var($mail, FILTER_VALIDATE_EMAIL)) {
      return true;
    }

    return false;
  }

  static function validateDate($date, $format = 'd/m/Y H:i', $format_out = 'd/m/Y H:i') {

    $d = \DateTime::createFromFormat($format, $date);

    if($d && $d->format($format) == $date) {
      return $d->format($format_out);
    }

    return false;

  }

  static function fromMysqlDateTimeToTs($date_string) {

    list($dateString, $timeString) = explode(' ', $date_string);

    list($date['y'], $date['m'], $date['d']) = explode('-', $dateString);

    list($time['hours'], $time['minutes'], $time['seconds']) = explode(':', $timeString);

    return mktime($time['hours'], $time['minutes'], $time['seconds'], $date['m'], $date['d'], $date['y']);

  }

  static function fromMysqlDateTimeToDate($date_string, $date_format = 'dd/mm/yy') {

    if($date_string == '0000-00-00 00:00:00') {
      return '-';
    }

    $ts = Utility::fromMysqlDateTimeToTs($date_string);

    return Utility::formatDate($ts, $date_format);

  }

  static function formatDate($date_int, $date_format = 'dd/mm/yy') {

    $sep    = $date_format[2];
    $order  = explode($sep, $date_format);
    $string = '';
    foreach($order as $f) {
      if($string) {
        $string .= $sep;
      }
      $f = $f[0];
      switch($f) {
        case 'y':
        case 'h':
          $f = strtoupper($f);
        default:
          $string .= date($f, $date_int);

      }
    }

    return $string;
  }

  static function withoutExtension($filename) {

    if(strpos($filename, '.') !== false) {
      $filename = explode('.', $filename);
      array_pop($filename);

      return implode('.', $filename);
    }

    return $filename;
  }

  static function remove_utf8_bom($text) {

    $bom  = pack('H*', 'EFBBBF');
    $text = preg_replace("/^$bom/", '', $text);

    return $text;
  }

  static function isValidMd5($md5 = '') {

    return preg_match('/^[a-f0-9]{32}$/', $md5);
  }
  
  


}