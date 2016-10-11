<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (26/05/16, 10.47)                                                                      **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
namespace Ueppy\utils;
if(!function_exists('array_replace_recursive') && !function_exists('recurse')) {
  function recurse($array, $array1) {

    foreach($array1 as $key => $value) {
      // create new key in $array, if it is empty or not an array
      if(!isset($array[$key]) || (isset($array[$key]) && !is_array($array[$key]))) {
        $array[$key] = [];
      }
      // overwrite the value in the base array
      if(is_array($value)) {
        $value = recurse($array[$key], $value);
      }
      $array[$key] = $value;
    }

    return $array;
  }

  function array_replace_recursive($array, $array1) {

    // handle the arguments, merge one by one
    $args  = func_get_args();
    $array = $args[0];
    if(!is_array($array)) {
      return $array;
    }
    for($i = 1; $i < count($args); $i++) {
      if(is_array($args[$i])) {
        $array = recurse($array, $args[$i]);
      }
    }

    return $array;
  }
}

/**
 * Classe per l'interfacciamento al sistema di mailing list Emaily
 */
class Emaily {

  var $id_lista;

  var $config = [];

  var $loggin_output = false;

  var $errori = [];

  /**
   * Costruttore, è possibile non passare nulla nel caso siano state definite le costanti:
   * SET_EMAILY_HOST, SET_EMAILY_PWD, SET_EMAILY_UID, SET_EMAILY_LIST, altrimenti passare i seguenti
   * parametri:
   *
   * $opts['HOST'] = 'Emaily Host';
   * $opts['UID']  = 'Uid utente';
   * $opts['PWD']  = 'Password utente';
   * $opts['LIST'] = 'Id della lista';
   *
   * @param array $opts
   */

  function __construct($opts = null) {

    $this->config['HOST'] = false;
    $this->config['UID']  = false;
    $this->config['PWD']  = false;
    $this->config['LIST'] = false;

    if(defined('SET_EMAILY_HOST')) {
      $this->config['HOST'] = SET_EMAILY_HOST;
    }
    if(defined('SET_EMAILY_UID')) {
      $this->config['UID'] = SET_EMAILY_UID;
    }
    if(defined('SET_EMAILY_PWD')) {
      $this->config['PWD'] = SET_EMAILY_PWD;
    }
    if(defined('SET_EMAILY_LIST')) {
      $this->config['LIST'] = SET_EMAILY_LIST;
    }

    if($opts) {
      $this->config = $this->array_replace_recursive($this->config, $opts);
    }

    if(!$this->config['HOST'] || !$this->config['UID'] || !$this->config['PWD'] || !$this->config['LIST']) {
      $opts                  = [];
      $opts['dieAfterError'] = true;
      $opts['level']         = 'error';
      $this->log('Configurazione errata, di seguito i dati forniti:'."\n".print_r($this->config, true), $opts);
    }

  }

  /**
   * Controlla grazie alle funzionalità di php5 se un'indirizzo email è valido
   * @param string $val email da verificare
   * @return boolean
   */
  protected function validateAddress($val) {

    return filter_var(filter_var($val, FILTER_SANITIZE_EMAIL), FILTER_VALIDATE_EMAIL);
  }

  /**
   * Controlla che l'indirizzo fornito sia presente nei DB di emaily per una particolare lista
   *
   * @param string $emailAddress Indirizzo email da testare
   * @param int $lista id della lista da verificare
   * @return boolean vero o falso
   */
  public function isSubscribed2NL($emailAddress = false, $lista = 0) {

    $preError = 'ERRORE'."\n".'Emaily::isSubscribed2NL('.$emailAddress.', '.$lista.') - ';

    // controllo lista
    if(!$lista) {
      $opts                  = [];
      $opts['dieAfterError'] = true;
      $opts['level']         = 'error';
      if($this->loggin_output) {
        $this->log($preError.'Id lista vuoto', $opts);
      } else {
        $this->log('Id lista vuoto', $opts);
      }
    }

    // controllo indirizzo
    if(!$emailAddress || !$this->validateAddress($emailAddress)) {
      $opts                  = [];
      $opts['dieAfterError'] = true;
      $opts['level']         = 'error';
      if($this->loggin_output) {
        $this->log($preError.'Indirizzo fornito non valido', $opts);
      } else {
        $this->log('Indirizzo fornito non valido', $opts);

        return false;
      }
    }

    $data = [];

    $url = $this->config['HOST'].'/e/getstatus/?uid='.$this->config['UID'].'&pwd='.urlencode($this->config['PWD']).'&email='.$emailAddress.'&lst='.$lista;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    $response = curl_exec($ch);
    $data     = @unserialize($response);
    if(!$data) {
      $opts                  = [];
      $opts['dieAfterError'] = true;
      $opts['level']         = 'error';
      if($this->loggin_output) {
        $this->log($preError.'Risposta ricevuta:'.$response);
      } else {
        $this->log('Risposta ricevuta:'.$response, $opts);

        return false;
      }
    }
    if(isset($data[$lista])) {
      return $data[$lista];
    } else {
      return false;
    }

  }

  /**
   * Metodo per l'iscrizione di un indirizzo ad una newsletter, si aspetta i dati dell'utente nella forma:
   *
   *  $data['nome']         = 'nome, facoltativo';
   *  $data['cognome']      = 'cognome, facoltativo';
   *  $data['emailAddress'] = 'indirizzzo email obbligatorio';
   *  $data['lista']        = 'Se non specificato viene usato il valore passato al costruttore';
   *
   * @param array $data array dei dati utente
   * @param boolean $sendmail indica se mandare la mail di iscrizione oppure no
   * @return boolean
   */
  function subscribeNL($data = null, $sendmail = false) {

    $record = [];

    $record['nome']         = '';
    $record['cognome']      = '';
    $record['emailAddress'] = '';
    $record['lista']        = $this->config['LIST'];

    if($data) {
      $record = $this->array_replace_recursive($record, $data);
    }

    $preError = 'ERRORE'."\n".'Emaily::subscribeNL('.print_r($record, true).') - ';

    if(!$record['lista']) {
      $opts                  = [];
      $opts['dieAfterError'] = true;
      $opts['level']         = 'error';
      if($this->loggin_output) {
        $this->log($preError.'Id lista vuoto', $opts);
      } else {
        $this->log('Id lista vuoto', $opts);

        return false;
      }
    }

    if(!isset($record['emailAddress']) || !$record['emailAddress'] || !$this->validateAddress($record['emailAddress'])) {
      $opts                  = [];
      $opts['dieAfterError'] = true;
      $opts['level']         = 'error';
      if($this->loggin_output) {
        $this->log($preError.'Indirizzo fornito non valido', $opts);
      } else {
        $this->log('Indirizzo fornito non valido', $opts);

        return false;
      }
    }

    $url = $this->config['HOST'].'/e/subscribe/?uid='.urlencode($this->config['UID']).'&pwd='.urlencode($this->config['PWD']).'&email='.urlencode($record['emailAddress']).'&lst='.$record['lista'].'&nome='.urlencode($record['nome']).'&cognome='.urlencode($record['cognome']).'&sendmail='.$sendmail;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    $data = curl_exec($ch);

    switch($data) {
      case '0':
      case '1':
        return $data;
        break;
      default:
        $opts                  = [];
        $opts['dieAfterError'] = true;
        $opts['level']         = 'error';
        if($this->loggin_output) {
          $this->log($preError.'Risposta ricevuta:'."\n".$data, $opts);
        } else {
          $this->log('Risposta ricevuta:'."\n".$data, $opts);

          return false;
        }
        break;
    }
  }

  /**
   * Metodo per la disiscrizione dalla newsletter
   * @param string $emailAddress
   * @param type $lista
   * @param type $debug
   */
  public function unsubscribeNL($emailAddress = false, $lista = 0, $debug = 0) {

    $preError = 'ERRORE'."\n".'Emaily::unsubscribeNL('.$emailAddress.', '.$lista.','.$debug.') - ';

    // controllo lista
    if(!$lista) {
      $opts                  = [];
      $opts['dieAfterError'] = true;
      $opts['level']         = 'error';
      if($this->loggin_output) {
        $this->log($preError.'Id lista vuoto', $opts);
      } else {
        $this->log('Id lista vuoto', $opts);

        return false;
      }
    }

    // controllo indirizzo
    if(!$emailAddress || !$this->validateAddress($emailAddress)) {
      $opts                  = [];
      $opts['dieAfterError'] = true;
      $opts['level']         = 'error';
      if($this->loggin_output) {
        $this->log($preError.'Indirizzo fornito non valido', $opts);
      } else {
        $this->log('Indirizzo fornito non valido', $opts);

        return false;
      }
    }

    $url = $this->config['HOST'].'/e/getdata/?uid='.$this->config['UID'].'&pwd='.urlencode($this->config['PWD']).'&email='.$emailAddress.'&lst='.$lista;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    $response = curl_exec($ch);

    $dati = unserialize($response);

    if(!$dati) {
      $opts                  = [];
      $opts['dieAfterError'] = true;
      $opts['level']         = 'error';
      if($this->loggin_output) {
        $this->log($preError.'Risposta ricevuta:'.$response, $opts);
      } else {
        $this->log('Risposta ricevuta:'.$response, $opts);

        return false;
      }
    } else {
      $url = $this->config['HOST'].'/e/remove/?code='.$dati['data']['codice'].'&id='.$dati['data']['id'].'&lst='.SET_EMAILY_LIST;
      $ch  = curl_init();
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_URL, $url);
      $response = curl_exec($ch);
      if($debug) {
        $this->log($url);
        $this->log($response);
      }
    }

  }

  /**
   * Funzione di utilità per unire l'array delle opzioni passate con quelle di default della classe
   * @param type $opts array delle opzioni
   */
  protected function array_replace_recursive($array1, $array2) {

    return array_replace_recursive($array1, $array2);
  }

  /**
   * Funzione per il debug della classe
   */
  private function log($data, $opts = null) {

    $logOpts = ['file'          => false,
                'htmlout'       => true,
                'level'         => 'info',
                'wrap'          => 200,
                'dieAfterError' => false];

    if($opts) {
      $logOpts = $this->array_replace_recursive($logOpts, $opts);
    }

    $debug_array = debug_backtrace();
    $debug_array = array_reverse($debug_array);

    if($logOpts['file']) {
      if(!is_dir(basename($logOpts['file']))) {
        die('Directory '.basename($logOpts['file']).' inesistente');
      } elseif(!is_writable($logOpts['file'])) {
        die('File '.$logOpts['file'].' non scrivibile');
      }
    }

    if($logOpts['file']) {
      $acapo     = "\n";
      $pre       = "\n------- ".$logOpts['level']." -------\n";
      $pre_close = "\n------- ".$logOpts['level']." -------\n";
    } else {
      if($logOpts['htmlout']) {
        $acapo     = "\n";
        $pre       = "<pre class=\"debug ui-corner-all ".$logOpts['level']."\">";
        $pre_close = "</pre>";
      } else {
        $acapo     = "\n";
        $pre       = "\n------- ".$logOpts['level']." -------\n";
        $pre_close = "\n------- ".$logOpts['level']." -------\n";
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

    if(is_array($data)) {
      $string .= "Tipo: Array";
      $string .= $acapo;
      if(empty($data)) {
        $string .= "array vuoto";
        $string .= $acapo;
      } else {
        $string .= "Numero Elementi :".count($data);
        $string .= $acapo;
        $string .= print_r($data, true);
        $string .= $acapo;
      }
    } elseif(is_object($data)) {
      $string .= "Oggetto";
      $string .= $acapo;
      $string .= print_r($data, true);
      $string .= $acapo;
    } elseif(is_bool($data)) {
      if($data) {
        $string .= "bool: true";
      } else {
        $string .= "bool: false";
      }
      $string .= $acapo;
    } else {
      $string .= "Tipo: Stringa(".strlen($data).")";
      $string .= $acapo;
      if(strlen($data) > $logOpts['wrap']) {
        $string .= $acapo;
        $string .= '!WRAPPED!';
        $string .= $acapo;
        $string .= $acapo;
        $data = wordwrap($data, $logOpts['wrap'], $acapo, false);
      }
      $string .= htmlentities($data, ENT_QUOTES, 'UTF-8');
      $string .= $acapo;
    }
    $string .= $pre_close;

    if($logOpts['file']) {
      file_put_contents($logOpts['file'], $string, FILE_APPEND);
    } else {
      if($this->loggin_output) {
        echo $string;
      } else {
        $this->errori[] = $data;
      }
    }

    if($this->loggin_output && $logOpts['dieAfterError'] && $logOpts['level'] == 'error') {
      die();
    }

  }

  private function isJson($string) {

    json_decode($string);

    return (json_last_error() == JSON_ERROR_NONE);
  }

  private function remoteUrlAccessEnabled() {

    return ini_get('allow_url_fopen');
  }

}

?>