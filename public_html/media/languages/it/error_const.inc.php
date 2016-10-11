<?php

/* File "it_error_const.inc.php" */

define('STR_TOO_LONG', '"%s" non può contenere più di %d caratteri' ) ;
define('STR_TOO_SHORT', '"%s" deve contenere almeno %d caratteri' ) ;
define('STR_BAD_RANGE', '"%s" deve contenere da %d a %d caratteri') ;
define('STR_BAD_LEN', '"%s" deve contenere %d caratteri') ;
define('STR_EMPTY', 'Il campo "%s" � vuoto');
define('DATE_BAD_ARGUMENTS','Uno dei valori del campo "%s" non contiene numeri' );
define('DATE_INVALID','"%s" non è stato compilato con una data corretta' );
define('TAG_EMPTY','il campo "%s" � vuoto' );
define('EMPTY_SELECTION', 'seleziona almeno uno degli elementi del campo "%s"');
define('ARR_TOO_LONG', '"%s" non può contenere più di %d elementi' ) ;
define('ARR_TOO_SHORT', '"%s" deve contenere almeno %d elementi' ) ;
define('ARR_BAD_RANGE', '"%s" deve contenere da %d a %d elementi') ;
define('ARR_BAD_LEN', '"%s" deve contenere %d elementi') ;
define('LACK_OF_ELEMENTS', '"%s" non contiene un valore valido') ;
define('IS_REQUIRED', 'Il campo %s è un campo obbligatorio') ;
define('BAD_PATTERN', 'Il contenuto di "%s" non corrisponde al formato richiesto') ;
define('NOT_NUM', '"%s" non contiene un numero valido') ;
define('NUM_BAD_RANGE', 'il valore di "%s" deve essere compreso tra %d e %d') ;
define('TIME_ERROR', 'Campo "%s" errato, ore: %d, minuti %d') ;
define('NOT_UNIQUE', 'Spiacente, il valore scelto per "%s" (%s) è già stato utilizzato') ;
define('NOT_EQUAL_PASS', 'Spiacente, il valore dei 2 campi password non corrispondono');
define('NOT_PRESENT', 'Il valore selezionato per il campo %s (%s) non è presente in database');
define('BAD_FILE', 'L\'estensione del file caricato per il campo %s (%s) per non è tra quelle ammesse (%s)');
define('PRESENT', 'Il valore scelto per %s è già presente in database e non può essere accettato');
class AbstrErrHandler{

  var $errors ;

  /* Costruttore */
  function AbstrErrHandler() {

    $this->errors = array() ;

  }

  function isValid() {
  /* TRUE se 0 errori */
    return !(bool)count($this->errors) ;
  }

  /* Restituisce il primo errore verificatosi */
  function getError() {

    return array_shift($this->errors) ;

  }

  /* Restituisce tutto lo stack degli errori */
  function getErrors() {

    return $this->errors ;

  }

}//END AbstrErrHandler

?>
