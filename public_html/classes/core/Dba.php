<?php
/***************/
/** v.1.01    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.02 (23/07/2016, 15.53)                                                                   **/
/** - Modificato il metodo getallegati per fare in modo che usi la classe Allegati.              **/
/**                                                                                              **/
/** v.1.01 (20/07/2016, 12.05)                                                                   **/
/** - Aggiunta la possibilità di passare il parametro __ALL__ a reset rules per annullare tutte  **/
/**   le regole di validazione in una sola chiamata (da usare con loadRules true, altrimenti     **/
/**   vengono caricate a runtime                                                                 **/
/**                                                                                              **/
/** v.1.00 (22/06/16, 10.24)                                                                     **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/

namespace Ueppy\core;

use Ueppy\utils\Utility;
use Ueppy\utils\Time;
use Ueppy\utils\Image;
use Ueppy\utils\Logger;
use Ueppy\core\Traduzioni;

/**
 * Classe per l'interfacciamento al database.
 * Si propone di gestire:
 *
 * 1. Oggetti multilingua
 * 2. Eventuali file il cui nome è salvato in db e il percorso autogenerato in base a tabella e id.
 * 3. Gestire file allegati su tabella esterna
 * 4. Gestire la validazione dell'oggetto.
 *
 * @author Giovanni Battista Lenoci <gianiaz@gmail.com>
 * @copyright Ueppy s.r.l.
 */
class Dba {

  /**
   * Opzioni di default per l'oggetto.
   * @var array $opts
   */
  public $opts
    = ['langs'          => [],
       'lang'           => false,
       'tableFilename'  => '',
       'loadRules'      => false,
       'forceAllLang'   => false,
       'files'          => false,
       'autoLoadLabels' => false,
       'acceptedExt'    => ['jpg', 'png', 'gif', 'jpeg', 'pdf', 'php'],
       'moveFiles'      => true,
       'imgSettings'    => false,
       'upload_dir'     => false,
       'tablesDir'      => false,
       'restrDir'       => false,
       'tableAllegati'  => 'allegati',
       'db'             => false,
       'debug'          => false,
       'logActions'     => true];

  // descrizione della tabella
  public $dataDescription = false;

  // regole per il settaggio dei campi
  private $restrizioni = [];

  // ultima query eseguita
  private $sql = '';

  // la connessione al db viene passata qui
  private $db_conn;

  // risultato della query eseguita.
  protected $resultSet;

  // private last error
  private $lastError;

  /**
   * @var array variabile che accoglie i dati estratti dal db, non è possibile modificarla manualmente
   */
  protected $fields = [];

  /**
   * @var array variabile che accoglie gli errori scaturiti dalla validazione.
   */
  protected $errori = [];

  private $lastInsertedId = 0;

  // contiene le directory da muovere sotto la directory dell'oggetto dopo il salvataggio
  private $filesToMove = [];

  private $filesToDelete = [];

  private $acceptedFilterOperator = ['<', '>', '=', '<=', '>=', '!=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN', 'REGEXP', 'REGEXP BINARY', 'IS', 'IS NOT'];

  private $labels = [];

  public $additionalData = [];

  private $erroreQueryNotificato = false;

  /**
   * @var boolean dice al metodo fillresults se restituire un oggetto o un array di oggetti
   */
  protected $getById = false;

  /**
   * @var array Array che raccoglie i nomi dei campi errati.
   */
  public $wrongFields = [];

  public $wrongLangs = [];

  private $loadedRules = false;

  public $readonly = false;

  // estensioni pericolose
  private $dangerousExtensions = ['php', 'exe', 'js'];

  // impostato dal metodo addFields serve per aggiungere campi da estrarre oltre a quelli di default (ad esempio per estrarre
  // dati da joins)
  protected $additionalFields = [];

  /**
   * Il costruttore imposta tutti i dati richiesti per identificare la tabella su cui andremo a lavorare.
   *
   * Valori di default per $opts:
   * <code>
   *
   * $opts = array();
   *
   *
   * $opts['langs'] = array(); // array delle lingue da estrarre, di default viene compilato con le lingue presenti nell'array globale $langs.
   *
   * $opts['lang']  = false; // lingua attuale in caso non vengano richieste più lingue, se non compilata l'oggetto cerca di riempirla prima con la costante ACTUAL_LANGUAGE, se non definita con la
   *                         // stringa "it".
   *
   * $opts['tableFilename'] =  ''; // nome del file contenente la configurazione. Obbligatorio, se non fornito viene scaturito un errore che blocca lo script.
   *
   * $opts['loadRules'] = false; // indica se caricare il file delle regole di restrizione, se impostato a true e il file non esiste viene scaturito un errore. Se impostato a false, nel momento in
   *                             // cui cercheremo di settare una proprietà verrà comunque cercato il file e verrà caricato in caso esista, se non esiste nessun errore verrà scaturito.
   *                             // NOTA BENE. Se aggiungiamo una regola con il metodo addRules dobbiamo settare questo valore a true, altrimenti al momento in cui il setter riceve il valore caricherà
   *                             // le regole sovrascrivendo quella da noi aggiunta.
   *
   * $opts['forceAllLang'] = false; // Tiene tracca del tipo di estrazione fatta e di conseguenza del tipo di salvataggio
   *
   * $opts['autoLoadLabels'] = true; // Se impostato a false non carica le labels dei campi, per essere caricate le labels deve essere stato caricato a livello globale l'array $lang_def.
   *
   * $opts['acceptedExt'] = array('jpg','png','gif','jpeg','pdf'); // di default sono accettate immagini e pdf
   *
   * $opts['logActions'] = true; // se fare il loggin delle azioni su db.
   *
   * $opts['moveFiles'] = true; [true|false] // Se impostato a false i file che NON arrivano da upload vengono copiati e non spostati.
   *
   * $opts['imgSettings'] = false; // array contenente le opzioni per il postprocessing delle immagini allegate
   *
   * $opts['upload_dir'] = false; // se ci sono dei files da reperire e vogliamo passare il percorso di partenza dove posizionarli dobbiamo segnarlo in questa proprietà
   *
   * $opts['tablesDir'] = false; // directory dove vengono cercati i files di configurazione per le tabelle
   *
   * $opts['restrDir'] = false; // directory dove vengono cercati i files di restrizione
   *
   * $opts['db'] = false,  // eventuali dati del database nel formato:
   *
   *                          $opts['db']         = array();
   *                          $opts['db']['host'] = '127.0.0.1';
   *                          $opts['db']['user'] = 'roeot';
   *                          $opts['db']['pass'] = 'atreides';
   *                          $opts['db']['name'] = 'dba';
   *
   *                          se i dati non vengono passati la classe cerca di comporre l'array precedente cercando le costanti DB_HOST, DB_USER, DB_PASS, DB_NAME
   *
   *
   * $opts['debug'] = false;  // true o false per debug dei dati ricevuti
   * </code>
   *
   * @param array $opts Array delle opzioni sopra descritte
   * @return null Non deve ritornare nulla :-)
   *
   */
  function __construct($opts = null) {

    if(!$opts || is_string($opts)) {
      $this->log('Mi sa che stai usando la sintassi vecchia', ['level' => 'error', 'dieAfterError' => true]);
    }

    if(!$opts || !isset($opts['langs']) && isset($GLOBALS['langs'])) {
      $opts['langs'] = $GLOBALS['langs'];
    }

    if(($opts && !isset($opts['lang'])) && defined('ACTUAL_LANGUAGE')) {
      $opts['lang'] = ACTUAL_LANGUAGE;
    } elseif((!$opts || !isset($opts['lang'])) && !defined('ACTUAL_LANGUAGE')) {
      $opts['lang'] = 'it';
    }

    if($opts) {
      $this->opts = $this->array_replace_recursive($this->opts, $opts);
    }

    if(!isset($this->opts['langs']) || !count($this->opts['langs'])) {
      $this->opts['langs'][] = $this->opts['lang'];
    }

    if(!isset($this->opts['db']) || !$this->opts['db']) {
      if(defined('DB_HOST') && defined('DB_USER') && defined('DB_PASS') && defined('DB_NAME')) {
        $this->opts['db']         = [];
        $this->opts['db']['user'] = DB_USER;
        $this->opts['db']['pass'] = DB_PASS;
        $this->opts['db']['name'] = DB_NAME;
        $this->opts['db']['host'] = DB_HOST;
      } else {
        $this->log('Dati per la connessione al db non forniti', ['level' => 'error', 'dieAfterError' => true]);
      }
    }

    if(!$this->opts['tablesDir']) {
      if(defined('TABLES_DIR')) {
        $this->opts['tablesDir'] = TABLES_DIR;
      } else {
        $this->log('Directory per i file di configurazione delle tabelle non definita', ['level' => 'error', 'dieAfterError' => true]);
      }
    }

    if(!$this->opts['restrDir']) {
      if(defined('TABLES_DIR')) {
        $this->opts['restrDir'] = TABLES_DIR;
      } else {
        $this->log('Directory per i file di configurazione di restrizione non definita', ['level' => 'error', 'dieAfterError' => true]);
      }
    }

    if(!isset($this->opts['tableFilename']) || !$this->opts['tableFilename']) {
      $this->log('File di configurazione non fornito');
      $this->log($this->opts, ['level' => 'error', 'dieAfterError' => true]);
    } else {
      // reperisco configurazione tabella
      $table_file = $this->opts['tablesDir'].$this->opts['tableFilename'].'.table.php';

      if(file_exists($table_file)) {
        include($table_file);

        if(count($cd) && $table[strlen($table) - 1] == '_') {
          $table = substr($table, 0, -1);
        }

        $this->dataDescription['table']       = $table;
        $this->dataDescription['table_langs'] = false;
        if(count($cd)) {
          $this->dataDescription['table_langs'] = $this->dataDescription['table'].'_langs';
        }
        $nome_cp                             = array_keys($cp);
        $nome_cp                             = array_pop($nome_cp);
        $this->dataDescription['types'][]    = array_pop($cp);
        $this->dataDescription['desc']['cp'] = $nome_cp;
        $this->$nome_cp                      = 0;
        unset($cp);
        $this->dataDescription['desc']['ci'] = [];

        $fileFields = [];

        foreach($ci as $field => $type) {
          if($type == 'int') {
            $this->fields[$field] = 0;
          } else {
            $this->fields[$field] = '';
          }
          if($type == 'image' || $type == 'file') {
            $fileFields[] = $field;
          }
          $this->dataDescription['types'][$field] = $type;
          $this->dataDescription['desc']['ci'][]  = $field;
        }
        unset($ci);
        $this->dataDescription['desc']['cd'] = [];
        foreach($cd as $field => $type) {
          foreach($this->opts['langs'] as $l) {
            if($type == 'int') {
              $this->fields[$l][$field] = 0;
            } else {
              $this->fields[$l][$field] = '';
            }
          }

          if($type == 'image' || $type == 'file') {
            $fileFields[] = $field;
          }
          $this->dataDescription['types'][$field] = $type;
          $this->dataDescription['desc']['cd'][]  = $field;
        }
        unset($cd);

        if(count($fileFields)) {

          if(!$this->opts['upload_dir']) {
            $this->opts['upload_dir'] = DOC_ROOT.REL_ROOT.UPLOAD.$this->opts['tableFilename'];
            if(!is_dir($this->opts['upload_dir'])) {
              $this->mkdir($this->opts['upload_dir']);
            }
          }

          $this->opts['files'] = $fileFields;

        }
        unset($fileFields);

        if($this->opts['loadRules']) {
          $restrictionFile = $this->opts['restrDir'].$this->opts['tableFilename'].'.restrictions.php';
          if(file_exists($restrictionFile)) {
            $this->loadedRules = true;
            include($restrictionFile);
            if(isset($restrizioni)) {
              $this->restrizioni = $restrizioni;
              unset($restrizioni);
            }
          } else {
            $this->log('Mi hai chiesto di caricare il file delle restrizioni ma non lo trovo in :'.$restrictionFile, ['level' => 'error', 'dieAfterError' => 1]);
          }
        }

        if($this->opts['autoLoadLabels']) {
          if(!defined('DBA_AUTOLOAD_LABELS') || DBA_AUTOLOAD_LABELS) {
            if(isset($GLOBALS['lang_def']) && $this->opts['autoLoadLabels']) {
              $this->getLabels();
            }
          }
        }

        $this->fields['id'] = 0;
        if(isset($this->dataDescription['types']['created_at'])) {
          $this->fields['created_at'] = date('Y-m-d H:i:s');
        }

      } else {

        $this->log('File di configurazione fornito non trovato ('.$table_file.')');
      }

    }

  }

  /**
   * Quando cerco di accedere ad una delle proprietà della classe eseguo la riscrittura del metodo speciale __get
   *
   * Il comportamento è descritto nel seguente modo:
   *
   * Se chiedo $oggetto->fields mi viene restituito tutto l'array dei dati dell'oggetto.
   * Se chiedo $oggetto->nome viene controllato se la proprietà "nome" esiste nell'array fields e
   * se si viene restituito solo questo valore, altrimenti false
   *
   * @param mixed $var
   * @return mixed
   */
  public function __get($var) {

    if($var == 'fields') {
      return ($var != "instance" && isset($this->$var)) ? $this->$var : false;
    } else {
      return ($var != "instance" && isset($this->fields[$var])) ? $this->fields[$var] : false;
    }
  }

  /**
   * Riscrittura del metodo __set ogni volta che cerco di scrivere una variabile nell'oggetto, questa finisce nell'array fields.
   *
   * Il metodo setter si comporta in modo diverso a seconda dei parametri che vengono passati, in pratica la variabile
   * $name verrà riempita con la proprietà che noi cerchiamo di settare, mentre value con il valore al di là dell'operatore
   * di assegnazione (=). i casi sono:
   *
   * 1. passo una proprietà che è contenuta nella tabella generale dell'oggetto
   *
   *    <code>$oggetto->$name = $value;</code>
   *
   * 2. passo una proprietà che è contenuta nella tabella di lingua
   *
   *    $oggetto->$name = array($field => $valore);
   *
   *    In questo caso $name conterrà una lingua ("it" oppure "en" ecc ecc).
   *    $value viene rielaborato partendo dall'array fornito, in pratica passo alla lingua
   *    la coppia campo di lingua => valore da assegnare.
   *
   * 3. passo un file che è contenuto nella tabella generale dell'oggetto
   *
   *    $oggetto->$lang = array($percorsofile, $nuovonome);
   *
   *    $lang è il nome del campo che conterrà il nomefile
   *
   *    $percorsofile è il percorso al file che stiamo assegnando al campo, deve essere un percorso valido
   *    e potrebbe anche essere il percorso generato da un upload e presente nell'array $_FILES.
   *
   *    $nuovonome è il nome che vogliamo assegnare al file una volta caricato, questo verrà rielaborato per
   *    togliere caratteri speciali e per aggiungere alcuni dati significaitivi. Questo valore è facoltativo,
   *    in tal caso il nome verrà ricavato dal percorso orginale.
   *
   * 4. passo un file che è contenuto nella tabella di lingua.
   *
   *    Questo caso è un mix dei casi 2 e 3:
   *
   *    $oggetto->$lang = array($field => array($percorsofile, $nuovonome));
   *
   *    In pratica passo alla lingua l'array $campo => $valore, dove $valore contiene un array di immagine
   *
   * Il metodo _set esegue anche il compito di cercare di caricare il file contenente le regole di validazione se
   * esiste, e in tal caso chiamerà anche i metodi che si occupano di eseguire questi controlli.
   *
   * @param string $name nome della proprietà da settare/lingua.
   * @param mixed $value valore della proprietà da settare.
   */
  function __set($name, $value) {

    if(!$this->loadedRules) {
      $this->loadedRules = true;
      $restrictionFile   = $this->opts['restrDir'].$this->opts['tableFilename'].'.restrictions.php';
      if(file_exists($restrictionFile)) {
        include($restrictionFile);
        if(isset($restrizioni)) {
          $this->restrizioni = $restrizioni;
          unset($restrizioni);
        }
      }
    }

    if(is_array($this->opts['langs']) && in_array($name, $this->opts['langs'])) {
      $lingua = $name;
      $name   = array_keys($value);
      $name   = array_pop($name);
      $value  = $value[$name];

      if(is_array($this->opts['files']) && in_array($name, $this->opts['files'])) {
        list($path, $filename) = $value;
        if($this->opts['debug']) {
          $this->log('Gestione Files');
          $this->log('PATH:'.$path."\nName:".$name);
        }
        $this->saveFile($path, $filename, $name, $lingua);
      } else {
        if($this->check($name, $value, $lingua)) {
          if(isset($this->dataDescription['types'][$name]) && $this->dataDescription['types'][$name] != 'text') {
            $value = Dba::xssSanitize($value);
          }
          $this->fields[$lingua][$name] = $value;
        }
      }
      //$this->log('Lingua:'.$lingua."\nName:".$name."\nValue:".$value);
    } else {
      if(is_array($this->opts['files']) && in_array($name, $this->opts['files'])) {
        list($path, $filename) = $value;
        if($this->opts['debug']) {
          $this->log('Gestione Files');
          $this->log('PATH:'.$path."\nName:".$name);
        }
        $this->saveFile($path, $filename, $name);
      } else {
        if($this->check($name, $value)) {
          if(isset($this->dataDescription['types'][$name])) {
            if($name == 'id' || $this->dataDescription['types'][$name] != 'text') {
              $value = Dba::xssSanitize($value);
            }

          }
          $this->fields[$name] = $value;
        }
      }
    }

  }

  /**
   * Riempie i campi fields con i dati del record richiesto.
   *
   * Come primo parametro il metodo richiede l'id del record del database che vogliamo reperire, come secondo
   * parametro opzionale l'array opt con i seguenti posssibili valori:
   * nota: dopo l'uguale il valore di default, tra parentesi quadre i possibili valori
   * <code>
   * $opts['debug']        = false [true|false];  // se true stampa query
   * $opts['fields']       = false [false|array]; // può contenere una serie di nomi di campo, sottoforma di array
   *                                              // utile se non si vogliono estrarre tutti i campi, o in caso di join per estrarre dati di altre tabelle.
   * $opts['langFields']   = false [false|array]; // può contenere una serie di nomi di campo per la tabella
   *
   * $opts['forceAllLang'] = false [true|false];  // boolean, se impostato a true reperisce tutte le
   *                                        // lingue altrimenti esegue ricerche ed estrazione sulla sola
   *                                        // lingua attuale. Ignorato in caso di oggetto monolingua
   * $opts['joins']        = array();   // array di condizioni join, una condizione join è cosi rappresentata:
   *                                    //
   *                                    // $join = array();
   *                                    // $join['table']      = tabella con cui eseguire la join
   *                                    // $join['alias']      = alias da assegnare alla tabella (facoltativo)
   *                                    // $join['on1']        = nome del campo sulla tabella specificata per la condizione di join
   *                                    // $join['on2']        = il campo join['on1'] deve essere raffrontato a questo
   *                                    //                       campo (può includere riferimento a tabella nella forma tabella.campo).
   *                                    // $join['operatore']  = operatore di raffronto, ad esempio i 2 campi precedenti devono essere uguali
   *                                    // $opts['joins'][] = $join;
   *                                    // se non si vogliono effettuare join passare array vuoto.
   * </code>
   *
   * @param int $id Id del record di db da estrarre.
   * @param array|null $opts Array di opzioni, se non passato prende i valori di default
   * @return object
   */
  function getById($id, $opts = null) {

    $opzioni           = []; // opzioni di default
    $opzioni['debug']  = false;   // se true stampa query
    $opzioni['fields'] = false;   // può contenere una serie di nomi di campo, sottoforma di array
    // utile se non si vogliono estrarre tutti i campi
    $opzioni['langFields'] = false;   // può contenere una serie di nomi di campo per la tabella
    // di lingua sottoforma di array, utile se non si vogliono estrarre tutti i
    // campi (valido se l'oggetto è multilingua, altrimenti è ignorato
    $opzioni['forceAllLang'] = false;   // boolean, se impostato a true reperisce tutte le
    // lingue altrimenti esegue ricerche ed estrazione sulla sola
    // lingua attuale. Ignorato in caso di oggetto monolingua

    $opzioni['purpose'] = 'read';  // passare modify per indicare che si è estratto il valore con l'intenzione di modificarlo, in questo modo
    // verrà scritto il file di sessione che indica chi sta modificano il record, e nel caso in cui sia in edit da
    // altri setta la proprietà read only a 1. Questo non vieta di salvare, ma semplicemente ci dà un'indicatore, in
    // fase di creazione della interfaccia starà al programmatore decidere se ignorare o meno questo parametro.
    // il funzionamento dipende anche dal settaggio della costante SESSIONS che indica la directory che gestirà le
    // sessioni, dal garbage-collector in cron che deciderà se una sessione è troppo vecchia e cancellarla.
    $opzioni['joins'] = []; // array di condizioni join, una condizione join è cosi rappresentata:
    //
    // $join = array();
    // $join['table']      = tabella con cui eseguire la join
    // $join['alias']      = alias da assegnare alla tabella (facoltativo)
    // $join['on1']        = nome del campo sulla tabella specificata per la condizione di join
    // $join['on2']        = il campo join['on1'] deve essere raffrontato a questo
    //                       campo (può includere riferimento a tabella nella forma tabella.campo).
    // $join['operatore']  = operatore di raffronto, ad esempio i 2 campi precedenti devono essere uguali
    // $opzioni['joins'][] = $join;
    $opzioni['group_by'] = false;

    if($opts) {
      $opzioni = $this->array_replace_recursive($opzioni, $opts);
    }

    $this->opts['forceAllLang'] = $opzioni['forceAllLang'];

    if($opzioni['debug']) {
      $this->opts['debug'] = 1;
      $this->log($opzioni);
    }

    if($opzioni['fields'] === false) {
      $opzioni['fields'][] = $this->dataDescription['table'].'.'.$this->dataDescription['desc']['cp'];
      foreach($this->dataDescription['desc']['ci'] as $field) {
        if(strpos($field, '.') === false) {
          $opzioni['fields'][] = $this->dataDescription['table'].'.'.$field;
        } else {
          $opzioni['fields'][] = $field;
        }
      }
      if(isset($this->additionalFields) && is_array($this->additionalFields) && count($this->additionalFields)) {
        $opzioni['fields'] = array_merge($opzioni['fields'], $this->additionalFields);
      }
    } else {
      $fields            = $opzioni['fields'];
      $opzioni['fields'] = [];
      foreach($fields as $field) {
        if(strpos($field, '.') === false) {
          $opzioni['fields'][] = $this->dataDescription['table'].'.'.$field;
        } else {
          $opzioni['fields'][] = $field;
        }
      }
    }

    $sql = '';
    $sql .= 'SELECT ';
    $sql .= implode(', ', $opzioni['fields']);

    // se multilingua
    if($this->dataDescription['table_langs']) {

      if($opzioni['forceAllLang']) {

        if($opzioni['langFields'] === false) {
          foreach($this->dataDescription['desc']['cd'] as $field) {
            if($field != 'lingua') {
              $opzioni['langFields'][] = $field;
            }
          }
        }

        $sql_lang = [];
        if($this->dataDescription['table_langs']) {
          foreach($this->opts['langs'] as $l) {
            $subQuery   = '(SELECT id FROM '.$this->dataDescription['table_langs'].' WHERE '.$this->dataDescription['table'].'_'.$this->dataDescription['desc']['cp'].' = '.$id.' AND lingua = "'.$l.'") as id_'.$l;
            $sql_lang[] = $subQuery;
          }
          foreach($opzioni['langFields'] as $field) {
            foreach($this->opts['langs'] as $l) {
              $subQuery   = '(SELECT '.$field.' FROM '.$this->dataDescription['table_langs'].' WHERE '.$this->dataDescription['table'].'_'.$this->dataDescription['desc']['cp'].' = '.$id.' AND lingua = "'.$l.'") as '.$field.'_'.$l;
              $sql_lang[] = $subQuery;
            }
          }
          if(count($opzioni['fields'])) {
            $sql .= ', ';
          }
          $sql .= implode(', ', $sql_lang);
        }

      } else {

        if(!$opzioni['langFields']) {
          $opzioni['langFields'][] = $this->dataDescription['table_langs'].'.id AS id_'.$this->opts['lang'];
          foreach($this->dataDescription['desc']['cd'] as $field) {
            if($field != 'lingua') {
              $opzioni['langFields'][] = $this->dataDescription['table_langs'].'.'.$field.' AS '.$field.'_'.$this->opts['lang'];
            }
          }
        }
        if(count($opzioni['fields'])) {
          $sql .= ', ';
        }
        $sql .= implode(', ', $opzioni['langFields']);

      }

    }

    $sql .= ' FROM '.$this->dataDescription['table'];


    if(count($opzioni['joins'])) {

      //Utility::pre($opzioni['joins']);

      $joins_strings = [];

      foreach($opzioni['joins'] as $j) {

        $js = ' LEFT JOIN '.$j['table'];
        if(isset($j['alias']) && $j['alias']) {
          $js .= ' as '.$j['alias'];
        }

        $js .= ' ON ';
        if(isset($j['alias']) && $j['alias']) {
          $js .= $j['alias'];
        } else {
          $js .= $j['table'];
        }
        $js .= '.'.$j['on1'];
        $js .= $j['operatore'];
        $js .= $j['on2'];
        $joins_string[] = $js;
      }

      $sql .= ' '.implode(' ', $joins_string);

    }

    if($this->dataDescription['table_langs']) {

      if($opzioni['forceAllLang']) {
        $sql .= ' WHERE ';
        $sql .= $this->dataDescription['table'].'.'.$this->dataDescription['desc']['cp'].' = "'.$id.'"';
      } else {
        $sql .= ' LEFT JOIN '.$this->dataDescription['table_langs'].' ON '.$this->dataDescription['table_langs'].'.'.$this->dataDescription['table'].'_'.$this->dataDescription['desc']['cp'].' = '.$this->dataDescription['table'].'.'.$this->dataDescription['desc']['cp'];
        $sql .= ' WHERE ';
        $sql .= $this->dataDescription['table_langs'].'.lingua = "'.$this->opts['lang'].'"';
        $sql .= ' AND ';
        $sql .= $this->dataDescription['table'].'.'.$this->dataDescription['desc']['cp'].' = "'.$id.'"';
      }

    } else {
      // non multilingua
      $sql .= ' WHERE ';
      $sql .= $this->dataDescription['table'].'.'.$this->dataDescription['desc']['cp'].' = "'.$id.'"';
    }

    if($opzioni['group_by']) {
      $sql .= ' GROUP BY '.$opzioni['group_by'];
    }

    if($opzioni['debug']) {
      $this->log($sql);
    }

    // salvo l'ultima query
    $this->sql = $sql;

    $this->resultSet = $this->doQuery($sql);

    $this->getById = true;

    $ret = $this->fillresults();

    if($opzioni['purpose'] == 'modify') {
      $ret->checkModifyStatus();
    }

    return $ret;

  }

  /**
   * Il metodo getlist si prefigge il compito di estrarre un elenco di oggetti per la tabella in esame.
   * Grazie a questo metodo è possibile estrarre un array di oggetti seguendo diverse condizioni, nonchè joins con altre tabelle.
   * E' inoltre possibile passare un parametro che ci permette di conteggiare i record estratti.
   * L'array $opts è compilato con dei valori di default che fanno si che venga fatta un'estrazione senza nessun filtro sulla tabella,
   * con i valori seguenti è possibile restringere la ricerca.
   *
   * <code>
   * $opts['fields']       = false; [false,array()]                 // può contenere una serie di nomi di campo, utile se non si vogliono estrarre tutti i campi, oppure un riferimento ad un campo ottenuto da una join.
   * $opts['langFields']   = false; [false,array()]                 // può contenere una serie di nomi di campo per la tabella di lingua, utile se non si vogliono estrarre tutti i campi
   * $opts['forceAllLang'] = false; [true|false];                   // boolean, se impostato a true reperisce tutte le lingue altrimenti esegue ricerche ed estrazione sulla sola lingua attuale
   * $opts['sortField']    = '';    [null|nomecampo(string)]        // campo per cui ordinare
   * $opts['sortOrder']    = '';    [null|ASC(string)|DESC(string)] // ordinamento ASC (ascendente) o DESC (discendente)
   * $opts['start']        = false; [false|int]                     // valore di start da passare a limit
   * $opts['quanti']       = false; [false|int]                     // valore di quantità da passare a limit
   * $opts['debug']        = false; [true|false]                    // debug
   * $opts['countOnly']    = false; [true|false]                    // passare true per ottenere solo il numero di record che soddisfano la ricerca
   *
   * $opts['filters']      = array(); // array di filters
   *                                    // Es.
   *                                    // $filter_record              = array();
   *                                    // $filter_record['chiave']    = 'modulo';
   *                                    // $filter_record['operatore'] = '=';
   *                                    // $filter_record['valore']    = 'news';
   *                                    //
   *                                    // $opts['filters'][]       = $filter_record;
   *
   * $opts['operatore']    = 'AND';   // indicare una stringa per utilizzarla come unico operatore di concatenazione tra le condizioni
   *                                  // passate nei filtri, oppure passare un array per raggruppare i diversi filtri, ad es.
   *                                  // vengono passati 3 filtri, vogliamo che siano veri i primi 2 assieme oppure il 3.
   *                                  //
   *                                  // $opzioni['operatore']   = array();
   *                                  // $opzioni['operatore'][] = array('subOperator' => 'AND',
   *                                  //                                 'quanti'      => 2);
   *                                  // $opzioni['operatore'][] = 'OR';
   *                                  // $opzioni['operatore'][] = array('subOperator' => 'AND',
   *                                  //                                 'quanti'      => 1);
   *                                  // $opzioni['filters']     = $filters;
   * $opts['group_by']     = false;   // Se si estrae una lista di elementi multilingua il group_by viene impostato in automatico, nel caso
   *                                  // si voglia forzare il group by ad un altro valore impostarlo qui.
   *
   * $opts['distinct']     = false;   // Se si vuole impostare una clausola distinct su un campo, passare il nome del campo qui. N.B. Ha effetto solo
   *                                  // sui campi della tabella generale, non quella di lingua.
   *
   * $opts['joins']        = array(); // array di condizioni join, una condizione join è cosi rappresentata:
   *                                  //
   *                                  // $join = array();
   *                                  // $join['table']      = tabella con cui eseguire la join
   *                                  // $join['alias']      = alias da assegnare alla tabella (facoltativo)
   *                                  // $join['on1']        = nome del campo sulla tabella specificata per la condizione di join
   *                                  // $join['on2']        = il campo join['on1'] deve essere raffrontato a questo
   *                                  //                       campo (può includere riferimento a tabella nella forma tabella.campo).
   *                                  // $join['operatore']  = operatore di raffronto, ad esempio i 2 campi precedenti devono essere uguali
   *                                  // $opzioni['joins'][] = $join;
   * $opts['raw']          = boolean  // di default il metodo ritorna un array di oggetti, con questo metodo restituisce solo un
   *                                     array di records nel formato [proprietà => valore]
   * </code>
   * @param array $opts array di opzioni come da documentazione
   * @return array|false Risultato dell'estrazione sottoforma di array di oggetti o false in caso di estrazione vuota.
   */
  public function getlist($opts = null) {

    $opzioni                 = [];
    $opzioni['fields']       = false;
    $opzioni['langFields']   = false;
    $opzioni['forceAllLang'] = false;
    $opzioni['sortField']    = '';
    $opzioni['sortOrder']    = '';
    $opzioni['start']        = false;
    $opzioni['quanti']       = false;
    $opzioni['debug']        = false;
    $opzioni['countOnly']    = false;
    $opzioni['filters']      = [];
    $opzioni['operatore']    = 'AND';
    $opzioni['group_by']     = false;
    $opzioni['distinct']     = false;
    $opzioni['raw']          = false;
    $opzioni['joins']        = [];
    $opzioni['cache']        = true;

    $this->getById = false;

    if($opts) {
      $opzioni = $this->array_replace_recursive($opzioni, $opts);
    }

    $this->opts['forceAllLangList'] = $opzioni['forceAllLang'];

    if($opzioni['debug']) {
      $this->opts['debug'] = $opzioni['debug'];
      $this->log($opzioni);
    }

    if($opzioni['fields'] === false) {
      $opzioni['fields'][] = $this->dataDescription['table'].'.'.$this->dataDescription['desc']['cp'];
      foreach($this->dataDescription['desc']['ci'] as $field) {
        $opzioni['fields'][] = $this->dataDescription['table'].'.'.$field;
      }
      if(isset($this->additionalFields) && is_array($this->additionalFields) && count($this->additionalFields)) {
        $opzioni['fields'] = array_merge($opzioni['fields'], $this->additionalFields);
      }
    } else {
      $fields            = $opzioni['fields'];
      $opzioni['fields'] = [];
      foreach($fields as $field) {
        if(strpos($field, '.') === false) {
          $opzioni['fields'][] = $this->dataDescription['table'].'.'.$field;
        } else {
          $opzioni['fields'][] = $field;
        }
      }
    }
    if($opzioni['debug']) Utility::pre($opzioni['fields']);

    if($opzioni['distinct']) {
      foreach($opzioni['fields'] as $k => $f) {
        if($opzioni['distinct'] == $f) {
          $opzioni['fields'][$k] = 'DISTINCT('.$f.')';
          break;
        }
      }
    }

    // clausola WHERE riempita con filtri richiesti
    $where = '';

    // se multilingua
    if($this->dataDescription['table_langs']) {

      // se voglio estrarre tutte le lingue
      if($opzioni['forceAllLang']) {

        if($opzioni['langFields'] === false) {
          foreach($this->opts['langs'] as $l) {
            $opzioni['langFields'][] = '(SELECT id FROM '.$this->dataDescription['table_langs'].' WHERE '.$this->dataDescription['table'].'_'.$this->dataDescription['desc']['cp'].' = '.$this->dataDescription['table'].'.'.$this->dataDescription['desc']['cp'].' AND lingua = "'.$l.'") as id_'.$l;
          }
          foreach($this->dataDescription['desc']['cd'] as $field) {
            foreach($this->opts['langs'] as $l) {
              if($field != 'lingua') {
                $opzioni['langFields'][] = '(SELECT '.$field.' FROM '.$this->dataDescription['table_langs'].' WHERE '.$this->dataDescription['table'].'_'.$this->dataDescription['desc']['cp'].' = '.$this->dataDescription['table'].'.'.$this->dataDescription['desc']['cp'].' AND lingua = "'.$l.'") as '.$field.'_'.$l;
              }
            }
          }
        }

        $sql = 'SELECT ';
        $sql .= implode(', ', $opzioni['fields']);
        $sql .= ', '.implode(', ', $opzioni['langFields']);
        $sql .= ' FROM '.$this->dataDescription['table'];

      } else {
        // estraggo e cerco nella lingua attuale

        if($opzioni['langFields'] === false) {
          foreach($this->dataDescription['desc']['cd'] as $field) {
            if($field != 'lingua') {
              $opzioni['langFields'][] = $this->dataDescription['table_langs'].'.'.$field.' as '.$field.'_'.$this->opts['lang'];
            }
          }
        } else {
          $lf                    = $opzioni['langFields'];
          $opzioni['langFields'] = [];
          foreach($lf as $field) {
            $opzioni['langFields'][] = $this->dataDescription['table_langs'].'.'.$field.' as '.$field.'_'.$this->opts['lang'];
          }
        }

        $sql = 'SELECT ';
        $sql .= implode(', ', $opzioni['fields']);
        if($opzioni['langFields']) {
          $sql .= ', '.implode(', ', $opzioni['langFields']);
          $sql .= ' FROM '.$this->dataDescription['table'];
          $sql .= ' LEFT JOIN '.$this->dataDescription['table_langs'].' ON '.$this->dataDescription['table_langs'].'.'.$this->dataDescription['table'].'_'.$this->dataDescription['desc']['cp'].' = '.$this->dataDescription['table'].'.'.$this->dataDescription['desc']['cp'];
          $sql .= ' AND '.$this->dataDescription['table_langs'].'.lingua = "'.$this->opts['lang'].'"';
        } else {
          $sql .= ' FROM '.$this->dataDescription['table'];
        }

      }

    } else {

      $sql = 'SELECT ';
      $sql .= implode(', ', $opzioni['fields']);
      $sql .= ' FROM '.$this->dataDescription['table'];

    }

    // parte di ricerca
    if($opzioni['filters']) {

      if($opzioni['debug']) {
        $this->log($opzioni['filters']);
      }

      $where_filter = [];
      foreach($opzioni['filters'] as $filterIter => $filter) {
        if(isset($filter['chiave']) && $filter['chiave'] && isset($filter['valore']) && isset($filter['operatore']) && in_array($filter['operatore'], $this->acceptedFilterOperator)) {
          // se la chiave di ricerca è nella tabella lingue
          if(in_array($filter['chiave'], $this->dataDescription['desc']['cd'])) {
            $filter['chiave'] = $this->dataDescription['table_langs'].'.'.$filter['chiave'];
          } elseif($filter['chiave'] == $this->dataDescription['desc']['cp']) {
            $filter['chiave'] = $this->dataDescription['table'].'.'.$filter['chiave'];
          } elseif(in_array($filter['chiave'], $this->dataDescription['desc']['ci'])) {
            $filter['chiave'] = $this->dataDescription['table'].'.'.$filter['chiave'];
          }

          if($filter['operatore'] == 'IN' || $filter['operatore'] == 'NOT IN' || (in_array($filter['operatore'], ['IS', 'IS NOT']) && $filter['valore'] === 'NULL')) {
            $where_filter[] = $filter['chiave'].' '.$filter['operatore'].' '.$filter['valore'];
          } else {

            if(is_array($filter['valore'])) {
              $whereArray = [];
              foreach($filter['valore'] as $val) {
                $whereArray[] = $filter['chiave'].' '.$filter['operatore'].' "'.$this->realEscape($val).'"';
              }
              if($filter['operatore'] == '=') {
                $where_filter[] = '('.implode(' OR ', $whereArray).')';
              } elseif($filter['operatore'] == '!=') {
                $where_filter[] = '('.implode(' AND ', $whereArray).')';
              }
            } else {
              $where_filter[] = $filter['chiave'].' '.$filter['operatore'].' "'.$this->realEscape($filter['valore']).'"';
            }

          }
        } else {
          $str = 'Filter Errato:';
          $str .= "\n";
          $str .= print_r($filter, true);
          $this->log($str, ['level' => 'error', 'dieAfterError' => true]);
        }
      }

      if(is_array($opzioni['operatore'])) {
        $where .= '(';
        foreach($opzioni['operatore'] as $gruppo) {
          if(is_array($gruppo)) {
            $gruppoWhere = [];
            for($i = 1; $i <= $gruppo['quanti']; $i++) {
              $gruppoWhere[] = array_shift($where_filter);
            }
            $where .= '('.implode(' '.$gruppo['subOperator'].' ', $gruppoWhere).')';
          } else {
            $where .= ' '.$gruppo.' ';
          }
        }
        $where .= ')';
        //$this->log($opzioni['operatore']);
      } else {
        $where .= implode(' '.$opzioni['operatore'].' ', $where_filter);
      }
    }

    // metto la join solo se sto cercando qualcosa e ho forzato il lang
    if($where && $opzioni['forceAllLang']) {
      $join               = [];
      $join['table']      = $this->dataDescription['table_langs'];
      $join['alias']      = false;
      $join['on1']        = $this->dataDescription['table'].'_'.$this->dataDescription['desc']['cp'];
      $join['on2']        = $this->dataDescription['table'].'.'.$this->dataDescription['desc']['cp'];
      $join['operatore']  = '=';
      $opzioni['joins'][] = $join;
    }

    if(count($opzioni['joins'])) {

      $joins_strings = [];

      foreach($opzioni['joins'] as $j) {

        $js = ' LEFT JOIN '.$j['table'];
        if(isset($j['alias']) && $j['alias']) {
          $js .= ' as '.$j['alias'];
        }

        $js .= ' ON ';
        if(isset($j['alias']) && $j['alias']) {
          $js .= $j['alias'];
        } else {
          $js .= $j['table'];
        }
        $js .= '.'.$j['on1'];
        $js .= $j['operatore'];
        $js .= $j['on2'];
        $joins_string[] = $js;
      }

      $sql .= ' '.implode(' ', $joins_string);
    }

    if($where) {
      $sql .= ' WHERE ';
      $sql .= $where;
    }

    // se faccio una ricerca su tutte le lingue devo mettere un group by per non duplicare/triplicare/ecc i risultati
    if(!$opzioni['group_by'] && $this->dataDescription['table_langs'] && $where) {
      if($opzioni['debug']) {
        $this->log('Aggiungo group by per non duplicare i dati');
      }
      $opzioni['group_by'] = $this->dataDescription['table'].'.'.$this->dataDescription['desc']['cp'];
    }

    if($opzioni['group_by']) {
      $sql .= ' GROUP BY '.$opzioni['group_by'];
    }

    if($opzioni['sortField']) {
      $sql .= ' ORDER BY ';
      if(in_array($opzioni['sortField'], $this->dataDescription['desc']['ci'])) {
        $sql .= $this->dataDescription['table'].'.'.$opzioni['sortField'];
      } elseif(in_array($opzioni['sortField'], $this->dataDescription['desc']['cd'])) {
        $sql .= $this->dataDescription['table_langs'].'.'.$opzioni['sortField'];
      } else {
        $sql .= $opzioni['sortField'];
      }
    }
    if($opzioni['sortOrder']) {
      $sql .= ' '.$opzioni['sortOrder'];
    }

    if($opzioni['countOnly']) {

      if($opzioni['debug']) {
        $this->log($sql);
      }

      $this->sql = $sql;

      $this->resultSet = $this->doQuery($sql, $opzioni['cache']);

      if(!$this->resultSet) {
        if($this->isSqlErrorNotified()) {
          $this->log('Si è verificato un\'errore nell\'interrogazione del database, l\'amministratore è stato avvisato', ['showBacktrace' => false, 'level' => 'error', 'dieAfterError' => true]);
        } else {
          $this->log($this->sql."\n".$this->lastError, ['level' => 'error', 'dieAfterError' => true]);
        }
      }

      $count = mysqli_num_rows($this->resultSet);

      return $count;

    } else {
      if($opzioni['quanti']) {
        $sql .= ' LIMIT '.$opzioni['start'].', '.$opzioni['quanti'];
      }

      if($opzioni['debug']) {
        $this->log($sql);
      }

      // salvo l'ultima query
      $this->sql = $sql;

      $this->resultSet = $this->doQuery($sql, $opzioni['cache']);

      if($opzioni['raw']) {
        return $this->fillraw();
      } else {
        return $this->fillresults();
      }

    }

  }

  /**
   * Metodo alternativo per il salvataggio di un oggetto, fondamentalmente svolge lo stesso compito del metodo save, ma senza porsi il problema dell'esistenza del record, creandone quindi uno nuovo.
   * <code>
   * $opts['debug']       = false; [true|false] (viene passato direttamente al metodo save)
   * </code>
   * @param type $opts
   * @return boolean Ritorna vero o falso a seconda del risultato dell'operazione
   */
  public function insert($opts = null) {

    $opzioni          = [];
    $opzioni['debug'] = false;
    if($opts) {
      $opzioni = $this->array_replace_recursive($opzioni, $opts);
    }
    $opzioni['forceInsert'] = true;

    return $this->save($opzioni);
  }

  /**
   * Il metodo viene chiamato ogni qualvolta vogliamo salvare un oggetto.
   *
   * Accetta le seguenti opzioni:ù
   * <code>
   * $opts['fields']      = false; [false|array()] // può contenere una serie di campi per cui effettuare il salvataggio, quasi mai usato, utile per fare degli update su singoli campi
   * $opts['langFields']  = false; [false|array()] // può contenere una serie di campi per cui effettuare il salvataggio, quasi mai usato, utile per fare degli update su singoli campi
   * $opts['debug']       = false; [true|false]    // opzioni di debug
   * $opts['forceInsert'] = false; [true|false]    // passare true per forzare l'inserimento.
   * </code>
   *
   * @param array $opts Array delle opzioni
   * @return mixed false,1,2  Ritorna false in caso di errori, 1 in caso di inserimento, 2 in caso di update
   */
  public function save($opts = null) {

    $opzioni                = [];
    $opzioni['fields']      = false;
    $opzioni['langFields']  = false;
    $opzioni['debug']       = false;
    $opzioni['forceInsert'] = false;

    if($opts) {
      $opzioni = $this->array_replace_recursive($opzioni, $opts);
    }

    if($this->opts['logActions']) {

      $options                  = [];
      $options['tableFilename'] = 'logs';
      $options['logActions']    = false;
      $LoggerObj                = new Logger($options);

      $logTXT = false;

      if($this->fields['id']) {
        $class = get_class($this);

        $opts = $this->opts;
        if(isset($opts['forceAllLangList'])) {
          $opts['forceAllLang'] = $opts['forceAllLangList'];
        }

        $confronto = new $class($opts);
        $confronto = $confronto->getById($this->fields['id']);

        if($confronto) {

          $logTXT          = 'Record ('.$this->dataDescription['table'].':'.$this->fields['id'].') modificato:';
          $campiModificati = [];

          foreach($confronto->fields as $field => $valore) {
            if(is_array($valore)) {
              $lang = $field;
              foreach($valore as $fieldLingua => $valoreLingua) {
                if($valoreLingua != $this->fields[$lang][$fieldLingua]) {
                  $campoModificato = $lang.':'.$fieldLingua;
                  if(!in_array($this->dataDescription['types'][$fieldLingua], ['text', 'image'])) {
                    $campoModificato .= "\nda \"".$valoreLingua."\" a \"".$this->fields[$lang][$fieldLingua]."\"";
                  }
                  $campiModificati[] = $campoModificato;
                }
              }
            } else {
              if($valore != $this->fields[$field]) {
                $campoModificato = $field;
                if($this->dataDescription['types'][$field] != 'text') {
                  $campoModificato .= "\nda \"".$valore."\" a \"".$this->fields[$field]."\"";
                }
                $campiModificati[] = $campoModificato;
              }
            }
          }
          if(!$campiModificati) {
            $logTXT = 'Record ('.$this->dataDescription['table'].':'.$this->fields['id'].') salvato senza modifiche';
          } else {
            $logTXT .= "\n".implode(",\n", $campiModificati);
          }
        }
      } else {
        foreach($this->fields as $field => $valore) {
          if(is_array($valore)) {
            $lang = $field;
            if(in_array($lang, $this->opts['langs'])) {
              foreach($valore as $fieldLingua => $valoreLingua) {
                $campoModificato = $lang.':'.$fieldLingua;
                if(!isset($this->dataDescription['types'][$fieldLingua])) {
                  Utility::pre($valore);
                  Utility::pre($fieldLingua);
                } else {
                  if($this->dataDescription['types'][$fieldLingua] != 'text') {
                    //Utility::pre($fieldLingua);
                    $campoModificato .= " = \"".$this->fields[$lang][$fieldLingua]."\"";
                  }
                }
                $campiModificati[] = $campoModificato;
              }
            }
          } else {
            $campoModificato = $field;
            if($field != 'id' && isset($this->dataDescription['types'][$field]) && $this->dataDescription['types'][$field] != 'text') {
              $campoModificato .= " = \"".$this->fields[$field]."\"";
            }
            $campiModificati[] = $campoModificato;
          }
        }
      }
    }


    if(in_array('updated_at', $this->dataDescription['desc']['ci'])) {
      $this->updated_at = date('Y-m-d H:i:s');
    }

    if(in_array('operatori_id', $this->dataDescription['desc']['ci']) && !isset($this->fields['operatori_id']) && isset($_SESSION['LOG_INFO']['UID'])) {
      $this->operatori_id = $_SESSION['LOG_INFO']['UID'];
    }

    if($opzioni['debug']) {
      $this->log($opzioni);
      $this->log($this);
    }

    $result = false;

// valuto se devo fare un update o un nuovo salvataggio controllando la chiave primaria,
// o valutando nelle opzioni il force di un nuovo inserimento

    if((!isset($this->fields[$this->dataDescription['desc']['cp']]) || !$this->fields[$this->dataDescription['desc']['cp']]) || $opzioni['forceInsert']) {

      // compongo query di insert dei dati indipendenti dalla lingua
      $result = $this->insertCI($opzioni);

      if($this->dataDescription['table_langs']) { // multilingua
        if($this->opts['forceAllLang']) {
          foreach($this->opts['langs'] as $l) {
            if($result) {
              $opzioni['lang'] = $l;
              $result          = $this->insertCD($opzioni);
            }
          }
        } else {
          $opzioni['lang'] = $this->opts['lang'];
          $result          = $this->insertCD($opzioni);
        }
      }

    } else {
      // compongo query di update

      // potrei fare update non su tutti i campi
      if($opzioni['fields'] === false) {
        $opzioni['fields'][] = $this->dataDescription['desc']['cp'];
        foreach($this->dataDescription['desc']['ci'] as $field) {
          $opzioni['fields'][] = $field;
        }
      }

      // potrei dover fare l'uppdate solo delle lingue, quindi valuto se è il caso di farlo per i campi
      // generali, contando i fields sui quali sto lavorando.

      $result = true;

      $this->lastInsertedId = $this->fields[$this->dataDescription['desc']['cp']];

      if(count($opzioni['fields'])) {
        $result = $this->updateCI($opzioni);
      }

      if($result) {

        if($this->dataDescription['table_langs']) { // multilingua

          if($opzioni['langFields'] === false) {
            foreach($this->dataDescription['desc']['cd'] as $field) {
              if($field != 'lingua') {
                $opzioni['langFields'][] = $field;
              }
            }
          }

          if(count($opzioni['langFields'])) {
            // controllo come ho estratto l'oggetto, se ho estratto solo una lingua salvo solo quella.
            if($this->opts['forceAllLang']) {
              foreach($this->opts['langs'] as $l) {
                if($result) {
                  if(isset($this->fields[$l]) && count($this->fields[$l])) {
                    if(!isset($this->fields[$l]['id']) || !$this->fields[$l]['id'] || $opzioni['forceInsert']) {
                      $opzioni['lang'] = $l;
                      $result          = $this->insertCD($opzioni);
                    } else {
                      $opzioni['lang'] = $l;
                      $result          = $this->updateCD($opzioni);
                    }
                  }
                }
              }
            } else {
              $l = $this->opts['lang'];
              if(isset($this->fields[$l]) && count($this->fields[$l])) {
                if(!isset($this->fields[$l]['id']) || !$this->fields[$l]['id'] || $opzioni['forceInsert']) {
                  $opzioni['lang'] = $l;
                  $result          = $this->insertCD($opzioni);
                } else {
                  $opzioni['lang'] = $l;
                  $result          = $this->updateCD($opzioni);
                }
              }
            }
          }
        }
      }
    }

    if($this->opts['logActions'] && isset($campiModificati)) {
      if(!$logTXT) {
        $logTXT = 'Creato record ('.$this->dataDescription['table'].':'.$this->fields['id'].')';
        $logTXT .= "\n".implode(",\n", $campiModificati);
      }

      $LoggerObj->addLine(['text' => $logTXT]);
    }

// sposto i files che attendevano un id per l'oggetto
    if($result) {

      $justSaved = [];

      if(count($this->filesToMove)) {

        $dir_to_delete = [];

        foreach($this->filesToMove as $file) {

          $dest = str_replace(DOC_ROOT.REL_ROOT.UPLOAD.$this->dataDescription['table'].'/temp/', '', $file);

          $dest = explode('/', $dest);

          $fileName = array_shift($dest);

          $dest = $this->generaPercorso($this->fields['id']).implode('/', $dest);

          $justSaved[] = $dest;

          if(!is_dir(dirname($dest))) {
            $this->mkdir(dirname($dest));
          }

          rename($file, $dest);

          $dir_to_delete[] = DOC_ROOT.REL_ROOT.UPLOAD.$this->dataDescription['table'].'/temp/'.$fileName.'/';

        }
        $dir_to_delete = array_unique($dir_to_delete);

        foreach($dir_to_delete as $dir) {
          Utility::emptydir($dir);
        }

      }

      $this->filesToDelete = array_diff($this->filesToDelete, $justSaved);

      if(count($this->filesToDelete)) {
        foreach($this->filesToDelete as $f) {
          @unlink($f);
        }
      }

      if($this->opts['files'] && is_array($this->opts['files']) && count($this->opts['files'])) {
        if(count($this->dataDescription['desc']['cd'])) {
          foreach($this->opts['files'] as $fileField) {
            if(in_array($fileField, $this->dataDescription['desc']['cd'])) {
              foreach($this->opts['langs'] as $l) {
                $this->fields['fileData'][$fileField][$l] = $this->getFileData($fileField, $this->fields, $l);
              }
            }
          }
        }
        foreach($this->opts['files'] as $fileField) {
          if(in_array($fileField, $this->dataDescription['desc']['ci'])) {
            $this->fields['fileData'][$fileField] = $this->getFileData($fileField, $this->fields, false);
          }
        }
      }


    }

    if($this->allegatiAbilitati()) {

      $options                  = [];
      $options['tableFilename'] = 'allegati';

      $AllegatiObj = new Allegati($options);

      $AllegatiObj->rescanRewrite();

    }

    return $result;

  }

  private
  function insertCI($opts = null) {

    $opzioni                = [];
    $opzioni['fields']      = false;
    $opzioni['langFields']  = false;
    $opzioni['debug']       = false;
    $opzioni['forceInsert'] = false;

    if($opts) {
      $opzioni = $this->array_replace_recursive($opzioni, $opts);
    }

    if($opzioni['debug']) {
      $this->log($opzioni);
    }

    $sql = 'INSERT INTO '.$this->dataDescription['table'].' ('.$this->dataDescription['desc']['cp'].', '.implode(', ', $this->dataDescription['desc']['ci']).')';
    $sql .= ' VALUES ';
    $sql .= '(';

    $data = [];

    if(!isset($this->fields[$this->dataDescription['desc']['cp']]) || !$this->fields[$this->dataDescription['desc']['cp']]) {
      $data[] = 'NULL';
    } else {
      $data[] = $this->fields[$this->dataDescription['desc']['cp']];
    }

    foreach($this->dataDescription['desc']['ci'] as $field) {
      if(!isset($this->fields[$field])) {
        $this->fields[$field] = '';
      }
      if(is_object($this->fields[$field]) || is_array($this->fields[$field])) {
        $this->log('Campo passato alla query non valido'."\n".print_r($this->fields[$field], true), ['level' => 'error', 'dieAfterError' => true]);
      } else {
        $data[] = '"'.$this->realEscape($this->fields[$field]).'"';
      }
    }

    $sql .= implode(', ', $data);
    $sql .= ')';

    if($opzioni['debug']) {
      $this->log($sql);
    }

    $this->sql = $sql;

    $res = $this->doQuery($sql);

    if(!$res) {
      $this->log($sql);
      $this->log($this->lastError);
      $result = false;
    } else {
      if($opzioni['forceInsert']) {
        $this->lastInsertedId = $this->fields[$this->dataDescription['desc']['cp']];
      } else {
        $this->fields[$this->dataDescription['desc']['cp']] = $this->lastInsertedId;
      }
      $result = 1;
    }

    return $result;

  }

  private
  function updateCI($opts = null) {

    $opzioni          = [];
    $opzioni['lang']  = false;
    $opzioni['debug'] = false;

    if($opts) {
      $opzioni = $this->array_replace_recursive($opzioni, $opts);
    }

    $sql = 'UPDATE '.$this->dataDescription['table'].' SET ';

    $updates = [];

    foreach($opzioni['fields'] as $f) {
      $updates[] = $f.' = "'.$this->realEscape($this->fields[$f]).'"';
    }

    $sql .= implode(', ', $updates);
    $sql .= ' WHERE '.$this->dataDescription['desc']['cp'].' = "'.$this->fields[$this->dataDescription['desc']['cp']].'"';

    $this->sql = $sql;

    if($opzioni['debug']) {
      $this->log($sql);
    }

    $res = $this->doQuery($sql);

    if(!$res) {
      Utility::pre($sql);
      $this->log($this->lastError);
      $result = false;
    } else {
      $result = 2;
    }

    return $result;

  }

  private
  function insertCD($opts = null) {

    $opzioni          = [];
    $opzioni['lang']  = false;
    $opzioni['debug'] = false;

    if($opts) {
      $opzioni = $this->array_replace_recursive($opzioni, $opts);
    }

    if(!$opzioni['lang']) {
      $opzioni['lang'] = $this->opts['lang'];
    }

    if($opzioni['debug']) {
      $this->log($opzioni);
    }

    // check

    $sql = 'SELECT count('.$this->dataDescription['table'].'_'.$this->dataDescription['desc']['cp'].') FROM '.$this->dataDescription['table_langs'].' WHERE '.$this->dataDescription['table'].'_'.$this->dataDescription['desc']['cp'].' ="'.$this->lastInsertedId.'" AND lingua ="'.$opzioni['lang'].'"';
    $res = $this->doQuery($sql);
    $row = mysqli_fetch_array($res);

    $sql = 'INSERT INTO '.$this->dataDescription['table_langs'].' (';
    $sql .= 'id, lingua, '.$this->dataDescription['table'].'_'.$this->dataDescription['desc']['cp'].', '.implode(', ', $this->dataDescription['desc']['cd']).') VALUES (';

    $sql_fields = [];

    $sql_fields[] = 'NULL';
    $sql_fields[] = '"'.$opzioni['lang'].'"';
    $sql_fields[] = '"'.$this->lastInsertedId.'"';

    foreach($this->dataDescription['desc']['cd'] as $f) {
      if(!isset($this->fields[$opzioni['lang']][$f])) {
        $this->fields[$opzioni['lang']][$f] = '';
      }
      $sql_fields[] = '"'.$this->realEscape($this->fields[$opzioni['lang']][$f]).'"';
    }
    $sql .= implode(', ', $sql_fields).')';

    if($opzioni['debug']) {
      $this->log($sql);
    }

    $this->sql = $sql;

    if($row[0]) {
      $this->log("ATTENZIONE!!!! STO CERCANDO QUESTO MALEDETTO BACO, SE TI CAPITA COPIA E INCOLLA TUTTO IL TESTO E CERCA DI RICORDARE COSA STAVI FACENDO PRIMA DI ARRIVARE QUI:"."\n".$sql, ['level' => 'error', 'dieAfterError' => true]);
    }


    $res = $this->doQuery($sql);

    if($res) {
      if($opzioni['debug']) {
        $this->log('InsertCD: Inserimento riuscito');
      }

      return 1;
    } else {
      if($opzioni['debug']) {
        $this->log('InsertCD: Inserimento fallito');
      }

      return false;
    }

  }

  private
  function updateCD($opts = null) {

    $opzioni          = [];
    $opzioni['lang']  = false;
    $opzioni['debug'] = false;

    if($opts) {
      $opzioni = $this->array_replace_recursive($opzioni, $opts);
    }

    if(!$opzioni['lang']) {
      $opzioni['lang'] = $this->opts['lang'];
    }

    if($opzioni['debug']) {
      $this->log($opzioni);
    }

    $sql          = 'UPDATE '.$this->dataDescription['table_langs'].' SET ';
    $sql_fields   = [];
    $sql_fields[] = 'lingua="'.$opzioni['lang'].'"';
    $sql_fields[] = $this->dataDescription['table'].'_'.$this->dataDescription['desc']['cp'].'="'.$this->lastInsertedId.'"';
    foreach($opzioni['langFields'] as $f) {
      $sql_fields[] = $f.'="'.$this->realEscape($this->fields[$opzioni['lang']][$f]).'"';
    }
    $sql .= implode(', ', $sql_fields);
    $sql .= ' WHERE id ="'.$this->fields[$opzioni['lang']]['id'].'"';

    $this->sql = $sql;

    if($opzioni['debug']) {
      $this->log($sql);
    }

    $res = $this->doQuery($sql);

    if(!$res) {
      $this->log($this->lastError);
      $result = false;
    } else {
      $result = 2;
    }

    return $result;

  }

  /**
   * Questo metodo cicla il recordset ricevuto dal db e effettua tutto il post processing sull'oggetto in modo da caricare files, impostare i giusti fields ecc.
   * E' possibile estendere questo metodo tenendo presente però che dovremo controllare manualmente se questo è stato richiamato tramite getById o getList e da li decidere
   * se ritornare un'array di oggetti (anche nel caso in cui sia uno solo l'oggetto ritornato) o l'oggetto stesso.
   * Questa operazione si esegue controllando la proprietà: $this->getById.
   *
   * @return class
   */
  protected function fillresults() {

    if($this->resultSet) {

      $result = [];

      $k = 0;
      while($row = mysqli_fetch_assoc($this->resultSet)) {
        if($this->opts['debug']) {
          $this->log($row);
        }
        foreach($row as $chiave => $valore) {
          if($chiave == $this->dataDescription['desc']['cp']) {
            $result[$k][$chiave] = $valore;
          } elseif(in_array($chiave, $this->dataDescription['desc']['ci'])) {
            $result[$k][$chiave] = $valore;
          } elseif(preg_match_all('/^(.*)_([a-z]{2})$/', $chiave, $m) && (in_array($m[1][0], $this->dataDescription['desc']['cd']) || $m[1][0] == 'id')) {
            $result[$k][$m[2][0]][$m[1][0]] = $valore;
          } else {
            if(strpos($chiave, '.') !== false) {
              list($grp, $chiave) = explode('.', $chiave);
              if(!isset($result[$k][$grp])) {
                $result[$k][$grp] = [];
              }
              $result[$k][$grp][$chiave] = $valore;
            } else {
              $result[$k][$chiave] = $valore;
            }
          }
        }
        if($this->opts['debug']) {
          $this->log($result[$k]);
        }

        // estrazione informazioni sugli eventuali files allegati
        if(is_array($this->opts['files']) && count($this->opts['files'])) {
          foreach($this->opts['files'] as $fileField) {
            if(in_array($fileField, $this->dataDescription['desc']['ci'])) {
              $result[$k]['fileData'][$fileField] = $this->getFileData($fileField, $result[$k], false);
            } elseif(in_array($fileField, $this->dataDescription['desc']['cd'])) {
              foreach($this->opts['langs'] as $l) {
                $result[$k]['fileData'][$fileField][$l] = $this->getFileData($fileField, $result[$k], $l);
              }

            }
          }
        }
        $k++;
      }

      // unsetto il resultset che non mi serve più
      unset($this->resultSet);

      $class = get_class($this);

      $res = [];

      foreach($result as $val) {

        $opts = $this->opts;
        if(isset($opts['forceAllLangList'])) {
          $opts['forceAllLang'] = $opts['forceAllLangList'];
        }
        $tmp         = new $class($opts);
        $tmp->fields = $val;

        $tmp->formatData();

        $res[] = $tmp;
      }
      $debug_backtrace = debug_backtrace();


      if(count($res) == 1 && $this->getById) {
        return $res[0];
      } else {
        return $res;
      }

    } else {
      $this->log("Errore Query:\n".$this->sql."\n\nMysql Errore:\n".$this->lastError, ['level' => 'error', 'dieAfterError' => true]);
    }
  }

  protected
  function fillraw() {

    if($this->resultSet) {

      $result = [];

      while($row = mysqli_fetch_assoc($this->resultSet)) {
        $result[] = $row;
      }

      return $result;

    }

    return false;

  }


  /**
   * Dato un campo di riferimento che contenga un valore 0|1 con il metodo toggle cambiamo il valore senza preoccuparci dello stato precedente
   * (operazione compiuta ad esempio in admin quando accendiamo e spegnamo la visualizzazione di un record).
   * <code>
   * $opts['field'] = 'nomedelcampo';
   * </code>
   * @param array $opts Opzioni passabili
   * @return boolean Risultato del metodo di salvataggio.
   */
  public
  function toggle($opts = null) {

    if(!$opts) {
      $this->log("Configurazione per il metodo toggle non fornita".$this->lastError, ['level' => 'error', 'dieAfterError' => true]);
    }
    $this->$opts['field'] = intval(!(bool)$this->$opts['field']);

    return $this->save();
  }

  /**
   * Metodo per la cancellazione del record dal database rappresentato dall'istanza dell'oggetto su cui stiamo lavorando.
   *
   * Es. di cancellazioine del record con id 1.
   * <code>
   * $obj = new Dba($opzioni);
   * $obj = $obj->getById(1);
   * $obj->delete();
   * </code>
   *
   * I passaggi di cancellazione sono:
   *
   * 1. cancellazione di eventuali files allegati, il tutto avviene tramite la cancellazione della directory autogenerata per l'id in esame.
   * 2. cancellazione di eventuali versioni di lingua (attraverso il controllo dell'esistenza della tabella di lingua per l'oggetto).
   * 3. cancellazione di eventuali allegati su tabella esterna allegati.
   * 4. cancellazione del record principale.
   *
   * L'unica opzione accettata è
   * <code>
   * $opts['debug'] = false; [true|false] per abilitare il debug
   * </code>
   * @param array $opts opzioni
   */
  function delete($opts = null) {

    $opzioni          = [];
    $opzioni['debug'] = 0;

    if($opts) {
      $opzioni = $this->array_replace_recursive($opzioni, $opts);
    }

    $dir = $this->generaPercorso($this->fields[$this->dataDescription['desc']['cp']]);

    if(is_dir($dir)) {
      if($opzioni['debug']) {
        $this->log('Cancello '.$dir);
      } else {
        Utility::emptydir($dir);
      }
    }

    if($this->opts['logActions']) {
      $options                  = [];
      $options['tableFilename'] = 'logs';
      $LoggerObj                = new Logger($options);

      $logTXT = 'Record ('.$this->dataDescription['table'].':'.$this->fields['id'].') eliminato';

      $LoggerObj->addLine(['text' => $logTXT]);

    }

    if($this->dataDescription['table_langs']) {
      $sql = 'DELETE FROM '.$this->dataDescription['table_langs'].' WHERE '.$this->dataDescription['table'].'_id = "'.$this->fields[$this->dataDescription['desc']['cp']].'"';
      if($opzioni['debug']) {
        $this->log($sql);
      } else {
        $this->sql = $sql;
        $this->doQuery($sql);
      }
    }

    if($this->allegatiAbilitati()) {
      $lista = $this->getAllegati();
      foreach($lista as $allegato) {
        $allegato->delete();
      }
    }

    $sql = 'DELETE FROM '.$this->dataDescription['table'].' WHERE id = "'.$this->fields[$this->dataDescription['desc']['cp']].'"';
    if($opzioni['debug']) {
      $this->log($sql);
    } else {
      $this->sql = $sql;
      $this->doQuery($sql);
    }

  }

  /**
   * Metodo che riempie le varie proprietà necessarie a descrivere un'immagine allegata
   */
  protected function getFileData($fileField, $record, $lang = false) {

    if($this->opts['debug']) {
      $dbgStr = 'getFileData(';
      $dbgStr .= "\n";
      $dbgStr .= '$fileField:'.$fileField;
      $dbgStr .= "\n";
      $dbgStr .= '$record:'.print_r($record, true);
      $dbgStr .= "\n";
      $dbgStr .= '$lang:'.$lang;
      $dbgStr .= ")\n";
      $this->log($dbgStr);
    }

    $basename = false;

    if($lang) {
      if(isset($record[$lang]) && isset($record[$lang][$fileField])) {
        $basename = $record[$lang][$fileField];
      }
    } else {
      if(isset($record[$fileField])) {
        $basename = $record[$fileField];
      }
    }

    $data = [];

    if($basename) {

      $path = $this->generaPercorso($record['id']);

      if($lang) {
        $path .= 'cd/'.$lang.'/';
      } else {
        $path .= 'ci/';
      }

      $data['exists'] = 0;

      if($basename && $this->dataDescription['types'][$fileField] == 'image' && $this->isImage($basename)) {

        $path .= 'images/';

        if($this->opts['debug']) {
          $this->log($path);
        }

        list($nomeFile, $ext) = explode('.', $basename);
        $files = glob($path.$nomeFile.'*.'.$ext);

        if($files && is_array($files) && count($files)) {

          $data['ext']      = $ext;
          $data['versioni'] = [];

          foreach($files as $k => $versione) {

            $record         = [];
            $record['path'] = $path;

            $re = '/'.preg_quote($nomeFile, '/').'(-([^\-]){1})?\-([^\-]+)\-([\d]+)\.'.$ext.'$/';

            preg_match_all($re, basename($versione), $m);

            if($this->opts['debug']) {
              $this->log($versione);
              $this->log($nomeFile);
              $this->log($path);
              $this->log($re);
              $this->log($m);
              $this->log($this->opts['imgSettings'][$fileField]);
            }

            $record['path'] .= basename($versione);
            $record['exists'] = false;

            $include = true;

            if(isset($this->opts['imgSettings']) && isset($this->opts['imgSettings'][$fileField])) {
              $include = false;
              foreach($this->opts['imgSettings'][$fileField] as $datiForniti) {
                if($datiForniti['tipo'] != 'none') {
                  if($this->opts['debug']) {
                    $this->log('Confronto le dimensioni fornite ('.$datiForniti['dimensione'].') con quelle del nome del file ('.$m[3][0].')'.($datiForniti['dimensione'] == $m[3][0]));
                  }
                  if($datiForniti['dimensione'] == $m[3][0]) {
                    $include = true;
                  }
                } else {
                  $include = true;
                }
              }
            }

            if($include) {

              if(file_exists($record['path'])) {

                if($this->opts['debug']) {
                  $this->log('Includo il file "'.$record['path'].'"');
                }

                $data['exists'] = 1;
                if(isset($m[2][0])) {
                  $record['type'] = $m[2][0];
                } else {
                  $record['type'] = 'c';
                }
                $record['exists']   = true;
                $record['rel_path'] = str_replace(DOC_ROOT, '', $record['path']);
                $record['url']      = str_replace(DOC_ROOT, HOST, $record['path']);
                $record['imgData']  = getimagesize($record['path']);
              }
              if(!isset($m[4][0])) {
                $data['versioni'][$k] = $record;
              } else {
                $data['versioni'][$m[4][0]] = $record;
              }

              ksort($data['versioni']);

            } else {
              if($this->opts['debug']) {
                $this->log('Scarto il file "'.$record['path'].'"');
              }
            }

          }
        }
      } else {
        $path .= 'files/';
        $path .= $basename;
        $data['path'] = $path;
        if(file_exists($data['path'])) {
          $data['exists']   = 1;
          $data['ext']      = explode('.', $basename);
          $data['ext']      = array_pop($data['ext']);
          $data['rel_path'] = str_replace(DOC_ROOT, '', $path);
          $data['url']      = str_replace(DOC_ROOT, HOST, $path);
        }
      }

    } else {

      $data['exists'] = 0;

    }

    return $data;

  }

  private
  function deleteFile($field, $lang = false, $debug = false) {

    //$debug = true;
    if($debug) {
      $str = '';
      $str .= '$field:'.$field;
      $str .= "\n";
      $str .= '$lang:'.$lang;
      $str .= "\n";
    }
    if($lang) {
      if(isset($this->fields[$lang][$field]) && $this->fields[$lang][$field] && in_array($field, $this->opts['files'])) {
        if($this->isImage($this->fields[$lang][$field]) && isset($this->opts['imgSettings'][$field])) {
          if($debug) {
            $str .= 'il campo contiene un\'immagine';
            $str .= "\n";
          }
          if($this->fields['fileData'][$field][$lang]['exists']) {
            foreach($this->fields['fileData'][$field][$lang]['versioni'] as $v) {
              if($debug) {
                $str .= 'Aggiungo il file "'.$v['path'].'"';
                $str .= "\n";
              }
              $this->filesToDelete[] = $v['path'];
            }
          }
        } else {
          if($debug) {
            $str .= 'il campo contiene un file generico';
            $str .= "\n";
          }
          if($this->fields['fileData'][$field]['exists']) {
            if($debug) {
              $str .= 'Aggiungo il file "'.$this->fields['fileData'][$field][$lang]['path'].'"';
              $str .= "\n";
            }
            $this->filesToDelete[] = $this->fields['fileData'][$field][$lang]['path'];
          }
        }
      }
      $this->fields[$lang][$field] = '';
    } else {
      if(isset($this->fields[$field]) && $this->fields[$field] && in_array($field, $this->opts['files'])) {
        if($this->isImage($this->fields[$field]) && isset($this->opts['imgSettings'][$field])) {
          if($debug) {
            $str .= 'il campo contiene un\'immagine';
            $str .= "\n";
          }
          if(isset($this->fields['fileData'][$field]['versioni'])) {
            foreach($this->fields['fileData'][$field]['versioni'] as $v) {
              if($debug) {
                $str .= 'Aggiungo il file "'.$v['path'].'"';
                $str .= "\n";
              }
              $this->filesToDelete[] = $v['path'];
            }
          }
        } else {
          if($debug) {
            $str .= 'il campo contiene un file generico';
            $str .= "\n";
          }
          if(isset($this->fields['fileData'][$field]) && $this->fields['fileData'][$field]['exists']) {
            if($debug) {
              $str .= 'Aggiungo il file "'.$this->fields['fileData'][$field]['path'].'"';
              $str .= "\n";
            }
            $this->filesToDelete[] = $this->fields['fileData'][$field]['path'];
          }
        }
      }
      $this->fields[$field] = '';
    }
    if($debug) {
      $this->log($str);
    }
  }

  private
  function mkdir($dir) {

    if(!file_exists($dir)) {
      mkdir($dir, 0777, true);
    }
  }

  /**
   * @param string $fileName
   * @return boolean Ritorna vero se l'estensione del file è quella di un'immagine
   */
  private
  function isImage($fileName) {

    $ext = explode('.', $fileName);
    $ext = array_pop($ext);
    $ext = strtolower($ext);

    return (in_array($ext, ['png', 'jpg', 'jpeg', 'gif']));
  }

  /**
   * metodo di utilità per l'aggiunta dei settaggi di configurazione per la gestione delle immagini.
   *
   * Alcuni esempi di settaggi:
   *
   * es. 1 immagine di dimensioni esatte.
   * <code>
   *   $Obj = new Dba($options);
   *
   *   $imgSetting               = array();
   *   $imgSetting['dimensione'] = '72x72';
   *   $imgSetting['tipo']       = 'exact';
   *
   *   $imgSettings[] = $imgSetting;
   *
   *   $Obj->setImgSettings($imgSettings, 'nomedelcampo');
   * </code>
   *
   * es. 2 Due versioni per lo stesso campo, la seconda versione usa il metodo crop.
   * <code>
   *   $Obj = new Dba($options);
   *
   *   $imgSetting               = array();
   *   $imgSetting['dimensione'] = '72x72';
   *   $imgSetting['tipo']       = 'crop';
   *   $imgSetting['options']    = (vedere documentazione classe Image.Class.php->crop per le opzioni passabili)
   *
   *   $imgSettings[] = $imgSetting;
   *
   *   $Obj->setImgSettings($imgSettings, 'nomedelcampo');
   * </code>
   *
   * @param array $settings
   * @param string $field Nome del campo a cui applicare le regole.
   */
  public
  function setImgSettings($settings, $field) {

    $this->opts['imgSettings'][$field] = $settings;
  }

  public
  function generaPercorso($id) {

    $percorso = DOC_ROOT.REL_ROOT.UPLOAD.$this->dataDescription['table'].'/';
    if($id < 1000) {
      $percorso .= '00/'.str_pad($id, 3, '0', STR_PAD_LEFT).'/';
    } else {
      $percorso .= str_pad(intval($id / 1000), 2, '0', STR_PAD_LEFT).'/'.str_pad(($id % 1000), 3, '0', STR_PAD_LEFT).'/';
    }

    return $percorso;
  }

  private
  function saveFile($path, $name, $field, $lang = false) {

    if($path) {

      if(!$name) {
        $name = basename($path);
      }

      $name = explode('.', $name);
      $ext  = array_pop($name);
      $ext  = strtolower($ext);
      $name = implode('.', $name);

      if(in_array($ext, $this->opts['acceptedExt']) &&
        !in_array($ext, $this->dangerousExtensions) &&
        (($this->dataDescription['types'][$field] == 'image' && $this->isImage($name.'.'.$ext))) || ($this->dataDescription['types'][$field] != 'image')
      ) {

        $name = Utility::sanitize($name).'.'.$ext;

        $dir = DOC_ROOT.REL_ROOT.UPLOAD.$this->dataDescription['table'].'/temp/';

        $dir .= $name.'/';

        if(!is_dir($dir)) {
          $this->mkdir($dir);
        }

        if(strpos($path, '/tmp/') !== false) {
          move_uploaded_file($path, $dir.$name);
        } else {
          if(!file_exists($path)) {
            $this->log('Il file di origine "'.$path.'" non esiste', ['level' => 'error', 'dieAfterError' => true]);
          }
          if($this->opts['moveFiles']) {
            rename($path, $dir.$name);
          } else {
            copy($path, $dir.$name);
          }
//          $this->log($path.' -> '.$dir.$name);
        }

        $inserimento = true;

        if($lang) {
          $dirDest = $dir.'cd/'.$lang.'/';
        } else {
          $dirDest = $dir.'ci/';
        }

        if(isset($this->fields[$this->dataDescription['desc']['cp']]) && $this->fields[$this->dataDescription['desc']['cp']]) {
          if($lang) {
            $this->deleteFile($field, $lang);
          } else {
            $this->deleteFile($field);
          }
        }

        if($this->isImage($name) && isset($this->opts['imgSettings'][$field]) && count($this->opts['imgSettings'][$field])) {
          $dirDest .= 'images/';
          if(!is_dir($dirDest)) {
            $this->mkdir($dirDest);
          }

          if(count($this->opts['imgSettings'])) {

            list($resizedName, $ext) = explode('.', $name);

            foreach($this->opts['imgSettings'][$field] as $k => $versione) {

              switch($versione['tipo']) {

                case 'crop':
                  list($w, $h) = explode('x', $versione['dimensione']);
                  $dim_data = [];
                  if(isset($versione['options'])) {
                    $dim_data = $versione['options'];
                  }
                  $dim_data['width']  = $w;
                  $dim_data['height'] = $h;

                  $dest = $dirDest.$resizedName.'-c-'.$versione['dimensione'].'-'.$k.'.'.$ext;

                  $this->filesToMove[] = $dest;

                  Image::crop($dir.$name, $dest, $dim_data);
                  break;

                case 'thumbnail':
                  list($w, $h) = explode('x', $versione['dimensione']);
                  $quality = 100;
                  if(isset($versione['options']) && isset($versione['options']['quality']) && is_numeric($versione['options']['quality'])) {
                    $quality = $versione['options']['quality'];
                  }

                  $dest                = $dirDest.$resizedName.'-t-'.$versione['dimensione'].'-'.$k.'.'.$ext;
                  $this->filesToMove[] = $dest;

                  Image::thumbnail($dirDest, $dir.$name, $w, $h, basename($dest), $quality);
                  break;

                case 'none':
                  $dest = $dirDest.$resizedName.'-originale-'.$k.'.'.$ext;
                  copy($dir.$name, $dest);
                  $this->filesToMove[] = $dest;
                  break;

                case 'exact':

                  list($w, $h) = explode('x', $versione['dimensione']);
                  list($wOriginale, $hOriginale) = getimagesize($dir.$name);

                  if($w == $wOriginale && $h == $hOriginale) {
                    $dest = $dirDest.$resizedName.'-'.$versione['dimensione'].'-'.$k.'.'.$ext;

                    copy($dir.$name, $dest);

                    $this->filesToMove[] = $dest;

                  } else {
                    // riferimento a labels

                    if(isset($this->labels[$field]) && $this->labels[$field]) {
                      $lbl = $this->labels[$field];
                    } else {
                      $lbl = $field;
                    }
                    if($lang) {
                      $lbl .= ' ('.$lang.')';
                    }
                    $this->wrongFields[$field] = 1;
                    $this->errori[]            = sprintf('Campo "%s" non valido, non sono rispettate le dimensioni richieste ('.$versione['dimensione'].')', $lbl);
                  }
                  break;

              }

            }

            if($lang) {
              $this->fields[$lang][$field] = $resizedName.'.'.$ext;
            } else {
              $this->fields[$field] = $resizedName.'.'.$ext;
            }

          } else {
            $dest = $dirDest.$name;
            rename($dir.$name, $dest);
            $this->filesToMove[] = $dest;
            if($lang) {
              $this->fields[$lang][$field] = $name;
            } else {
              $this->fields[$field] = $name;
            }
          }

        } else {
          $dirDest .= 'files/';
          if(!is_dir($dirDest)) {
            $this->mkdir($dirDest);
          }

          $dest = $dirDest.$name;
          copy($dir.$name, $dest);
          $this->filesToMove[] = $dest;

          if($lang) {
            $this->fields[$lang][$field] = $name;
          } else {
            $this->fields[$field] = $name;
          }

        }

      } else {
        if(isset($this->labels[$field]) && $this->labels[$field]) {
          $lbl = $this->labels[$field];
        } else {
          $lbl = $field;
        }
        if($lang) {
          $lbl .= ' ('.$lang.')';
        }
        $this->wrongFields[$field] = 1;
        $this->errori[]            = sprintf('Campo "%s" non valido, l\'estensione del file "%s" non è tra quelle accettate (%s) oppure è compresa tra quelle pericolose (%s)', $lbl, $name.'.'.$ext, implode(',', $this->opts['acceptedExt']), implode(',', $this->dangerousExtensions));
      }

    } else {
      if($lang) {
        $this->deleteFile($field, $lang);
      } else {
        $this->deleteFile($field);
      }
    }

    //$this->log($this->fields);

  }

  /**
   * metodo utilizzato per la connessione al database in base ai parametri ricevuti in costruzione oggetto
   */
  private function connectdb() {

    if(defined('PERSISTENT_CONNECTION') && PERSISTENT_CONNECTION) {
      $this->db_conn = $GLOBALS['DB_CONNECTION'];
    } else {
      $this->db_conn = @mysqli_connect($this->opts['db']['host'], $this->opts['db']['user'], $this->opts['db']['pass'], $this->opts['db']['name']);
      if($this->db_conn === false) {
        $this->log($this->opts['db']['host'].': Connessione al server DB fallita'.mysqli_connect_error(), ['level' => 'error', 'dieAfterError' => true]);
      }
    }
  }

  /**
   * metodo utilizzato per la chiusura della connessione al database.
   */
  private function closedb($overridePersistentConnection = false) {

    if(!defined('PERSISTENT_CONNECTION') || !PERSISTENT_CONNECTION || $overridePersistentConnection) {
      mysqli_close($this->db_conn);
      $this->db_conn = false;
    }
  }

  /**
   * Esegue una query sfruttando la connessione al db passata nel costruttore dell'oggetto, controllando la validità
   * del risultato e riempiendo il campo della chiave primaria in caso si tratti di un inserimento.
   * Può essere utilizzato anche per eseguire query manualmente.
   *
   * @param string $sql Una query sql valida
   * @return recordset $res Ritorna il record set risultante dalla query eseguita.
   */
  public function doQuery($sql, $cache = true) {

    if(!isset($GLOBALS['queryCache'])) {
      $GLOBALS['queryCache'] = [];
    }

    $doCache = strpos($sql, 'SELECT') === 0 && $cache;

    if(!$doCache || !isset($GLOBALS['queryCache'][$sql]) || (defined('DO_QUERY_CACHE') && !DO_QUERY_CACHE)) {

      $this->lastError = false;
      $this->connectdb();
      $timeStart = microtime(true);
      $res       = mysqli_query($this->db_conn, $sql);
      if($doCache) {
        $GLOBALS['queryCache'][$sql] = $res;
      }
      $timeEnd = microtime(true);
      $this->logQuery($sql, $timeEnd - $timeStart);
      if(!$res) {
        $this->lastError = mysqli_error($this->db_conn);
        // log delle query via mail se non sono in locale
        //
        if(0 && (isset($_SERVER['SERVER_ADDR']) && $_SERVER['SERVER_ADDR'] != '127.0.0.1')) {
          require(LIB.'phpmailer/phpmailer.configurator.php');
          $phpmailer->Subject = HOST.' - Errore Query';
          $phpmailer->addAddress('gianiaz@gmail.com');
          $debug_array = debug_backtrace();
          $debug_array = array_reverse($debug_array);
          $str         = '';
          foreach($debug_array as $da) {
            $str .= "File :".$da['file'];
            $str .= "\n";
            $str .= "Linea :".$da['line'];
            $str .= "\n";
          }
          $str .= 'Errore nella seguente query:';
          $str .= "\n";
          $str .= $sql;
          $str .= "\n";
          $str .= 'Errore:';
          $str .= "\n";
          $str .= $this->lastError;
          if(defined('SET_EMAIL_SENDER')) {
            $phpmailer->From   = SET_EMAIL_SENDER;
            $phpmailer->Sender = SET_EMAIL_SENDER;
          }
          if(defined('SET_SENDER_NAME')) {
            $phpmailer->FromName = SET_SENDER_NAME;
          }
          $phpmailer->IsHTML(false);
          $phpmailer->Body = $str;
          if($phpmailer->Send()) {
            $this->erroreQueryNotificato = true;
          } else {
            $this->log($phpmailer->ErrorInfo);
            if(isset($GLOBALS['operator']) && $GLOBALS['operator'] && $GLOBALS['operator']->fields['super_admin']) {
              echo $str;
            }
          }
        }
      } else {
        if(strpos($sql, 'INSERT') === 0 && (!$this->dataDescription['table_langs'] || strpos($sql, $this->dataDescription['table_langs']) === false)) {
          $this->lastInsertedId = mysqli_insert_id($this->db_conn);
        }
      }
      $this->closedb();
    } else {
      $res = $GLOBALS['queryCache'][$sql];
      mysqli_data_seek($res, 0);
    }

    return $res;
  }

  public
  function isSqlErrorNotified() {

    return $this->erroreQueryNotificato;
  }

  private function logQuery($sql, $duration) {

    $dbb = debug_backtrace();
    array_pop($dbb);
    $dbb = array_reverse($dbb);
    $str = '';
    foreach($dbb as $dbb_) {
      $str .= str_replace(DOC_ROOT, '', @$dbb_['file']).', '.@$dbb_['line'];
      $str .= "\n";
    }

    if(isset($GLOBALS['smarty']) && $GLOBALS['smarty']) {
      $GLOBALS['smarty']->queries[] = [$str.$sql, $duration];
      /*
      if(!isset($GLOBALS['queries'][$sql])) {
        $GLOBALS['queries'][$sql] = 0;
      }
      $GLOBALS['queries'][$sql]++;
      */
    }

  }

  /**
   * Controllo di validità sull'oggetto
   *
   * @return boolean vero se l'oggetto è valido (la proprietà errori è vuota)
   */
  public
  function isValid() {

    return !(bool)count($this->errori);
  }

  /**
   * Ritorna una stringa contenente l'elenco degli errori per l'oggetto, in genere si usa dopo aver controllato se l'oggetto è valido:
   *
   * <code>
   * $obj = new Dba($options);
   * $obj->proprieta1 = 'valore';
   * $obj->proprieta2 = 'valore2';
   *
   * if($obj->isValid()) {
   *   // posso salvare
   * } else {
   *   // reperisco gli errori
   *   $opzioni          = array();
   *   $opzioni['glue']  = '&lt;br /&gt;';
   *   $obj->getErrors($opzioni); // potrebbe ritoranre:
   *                              // proprieta1 non valida
   *                              // &lt;br /&gt;
   *                              // proprieta2 non valida
   * }
   * </code>
   *
   * <code>
   * $opts['glue'] = 'stringa'; // false, oppure stringa utilizzata per l'implode dell'array degli errori. se passo false ottengo un array di errori
   * </code>
   * @param array $opts array delle opzioni
   * @return string errori
   */
  public
  function getErrors($opts = null) {

    $opzioni          = [];
    $opzioni['glue']  = '<br />';
    $opzioni['debug'] = 0;

    if($opts) {
      $opzioni = $this->array_replace_recursive($opzioni, $opts);
    }

    if($opzioni['debug']) {
      $this->log($opzioni);
      $this->log($this->errori);
    }

    if($opts['glue'] === false) {
      return $this->errori;
    } else {
      return implode($opzioni['glue'], $this->errori);
    }


  }

  /**
   * Metodo per resettare le regole di validazione, utile per manipolare eventuali regole inserite nel file di configurazione per la validazione.
   *
   * @param string $varName Proprietà per cui resettare le regole di validazione
   */
  public
  function resetRules($varName) {

    if($varName == '__ALL__') {
      foreach($this->restrizioni as $k => $v) {
        $this->restrizioni[$k] = [];
      }

    } else {
      $this->restrizioni[$varName] = [];
    }

  }

  /**
   * Metodo per l'aggiunta di una regola di validazione all'oggetto in lavorazione.
   *
   * Per comprendere la validazione accedere alla documentazione relativa ai metodi privati, e cercare tutti metodi che hanno nome ...Rule()
   *
   * <code>
   * $opzioni['field'] = 'nomecampo';        // nome del campo a cui applcare la regola
   * $opzioni['rule']  = 'nomedellaregola';  // nome del metodo di validazione da richiamare, se vogliamo assegnare la regola NumericRule(), passare in questo campo "Numeric".
   * $opzioni['args']  = array();            // array degli argomenti per la regola
   * </code>
   *
   * @param array $opzioni Opzioni per la configurazione
   */
  function addRule($opzioni) {

    if(!isset($opzioni['field']) || !$opzioni['field']) {
      $this->log('Campo a cui assegnare la restrizione non ricevuto', ['level' => 'error', 'dieAfterError' => true]);
    }

    if(!isset($opzioni['rule']) || !$opzioni['rule']) {
      $this->log('Regola di restrizione non ricevuta', ['level' => 'error', 'dieAfterError' => true]);
    }

    $restrizione = [];

    $restrizione['regola'] = $opzioni['rule'];
    if(isset($opzioni['args']) && is_array($opzioni['args'])) {
      $restrizione['args'] = $opzioni['args'];
    } else {
      $restrizione['args'] = [];
    }

    if(!isset($this->restrizioni[$opzioni['field']])) {
      $this->restrizioni[$opzioni['field']] = [];
    }
    $this->restrizioni[$opzioni['field']][] = $restrizione;
  }

  /**
   * Metodo interno che controlla l'oggetto in base alle regole di validazione assegnate.
   *
   * @param string $varName Nome del campo da validare
   * @param mixed $varValue Valore che stiamo assegnando
   * @param string $lang Codice di lingua
   * @return boolean risultato del controllo sul campo
   */
  private
  function check($varName, $varValue, $lang = false) {

    if(isset($this->restrizioni[$varName])) {

      foreach($this->restrizioni[$varName] as $k => $restrizione) {
        if(method_exists($this, $restrizione['regola'].'Rule')) {

          $dbgStr = '';

          $dbgStr .= '$varName: '.$varName;
          $dbgStr .= "\n";
          $dbgStr .= '$varValue: '.print_r($varValue, true);
          $dbgStr .= "\n";
          $dbgStr .= 'k: '.$k;
          $dbgStr .= "\n";
          $dbgStr .= '$restrizione[\'regola\']: '.$restrizione['regola'].'Rule';


          // ritorno true se il campo non è obbligatorio e se e il valore è nullo
          if($k == 0 && $restrizione['regola'] != 'Obbligatorio' && !$varValue) {
            return true;
          }

          if(!isset($restrizione['args'])) {
            $restrizione['args'] = null;
          }
          $args['LANG'] = '';
          if($lang) {
            $args['LANG'] = ' ('.$lang.')';
          }
          $args['NOME']  = $varName;
          $args['FIELD'] = $varName;
          if(isset($this->labels[$varName]) && $this->labels[$varName]) {
            $args['NOME'] = $this->labels[$varName];
          }
          $args['VAL']  = $varValue;
          $args['ARGS'] = $restrizione['args'];

          if($this->opts['debug']) {
            $this->log($restrizione['regola'].'Rule'.':'."\n".print_r($args, true));
          }

          if(!call_user_func_array([$this, $restrizione['regola'].'Rule'], [$args])) {
            return false;
          }
        } else {
          $this->log('Il controllo "'.$restrizione['regola'].'" NON esiste', ['level' => 'error', 'dieAfterError' => true]);
        }
      }

      return true;
    } else {
      return true;
    }

  }

  /**
   * Regola per l'obbligatorietà del campo passato.
   * Ritorna true o false, e nel caso di false riempie l'array degli errori con un messaggio.
   * @return boolean
   */
  private
  function ObbligatorioRule() {

    $parametri = func_get_args();

    $parametri = array_pop($parametri);
    $opts      = [];
    if($parametri['ARGS']) {
      $opts = $this->array_replace_recursive($opts, $parametri['ARGS']);
    }

    if($parametri['VAL']) {
      return true;
    } else {
      $lang  = preg_replace('/[^a-z]/', '', $parametri['LANG']);
      $field = $parametri['FIELD'];
      if($lang) {
        $field .= '_'.$lang;
      }
      $this->wrongFields[$field] = 1;
      $this->errori[$field]      = sprintf('Campo "%s" obbligatorio', $parametri['NOME'].$parametri['LANG']);

      return false;
    }
  }

  /**
   * Regola per il controllo della presenza del valore passato in un array fornito tramite la regola di validazione.
   * Per creare una regola di validazione usare la seguente sintassi (in questo caso verifichiamo che il valore passato sia 0 o 1):
   * <code>
   * $restrizione           = array();
   * $restrizione['regola'] = 'InArray';
   * $restrizione['args']   = array('accepted' => array('0', '1'));
   * $restrizioni['NOMECAMPO'][] = $restrizione;
   * </code>
   * Ritorna true o false, e nel caso di false riempie l'array degli errori con un messaggio.
   * @return boolean
   */
  private
  function InArrayRule() {

    $parametri = func_get_args();
    $parametri = array_pop($parametri);

    $opts             = [];
    $opts['accepted'] = [];

    if($parametri['ARGS']) {
      $opts = $this->array_replace_recursive($opts, $parametri['ARGS']);
    }

    if(in_array($parametri['VAL'], $opts['accepted'])) {
      return true;
    } else {
      $lang  = preg_replace('/[^a-z]/', '', $parametri['LANG']);
      $field = $parametri['FIELD'];
      if($lang) {
        $field .= '_'.$lang;
      }
      $this->wrongFields[$field] = 1;
      $this->errori[$field]      = sprintf('Campo "%s" non valido, perchè non è tra i valori accettati ('.implode(', ', $opts['accepted']).')', $parametri['NOME']);

      return false;
    }

  }

  /**
   * Regola per il controllo di validità numerica.
   * Ritorna true o false, e nel caso di false riempie l'array degli errori con un messaggio.
   * @return boolean
   */
  private
  function NumericRule() {

    $parametri = func_get_args();
    $parametri = array_pop($parametri);
    if(is_numeric($parametri['VAL'])) {
      return true;
    } else {
      $lang  = preg_replace('/[^a-z]/', '', $parametri['LANG']);
      $field = $parametri['FIELD'];
      if($lang) {
        $field .= '_'.$lang;
      }
      $this->wrongFields[$field] = 1;
      $this->errori[$field]      = sprintf('Campo "%s" non valido, richiesto valore numerico, fornito:"'.$parametri['VAL'].'"', $parametri['NOME'].$parametri['LANG']);

      return false;
    }
  }

  /**
   * Controllo di unicità di un campo in base ai parametri passati:
   * <code>
   * $opts['table']      = '';   // tabella in cui controllare se il valore è già presente
   * $opts['escludi']    = 0;    // valore da escludere nella ricerca
   * $opts['confronto']  = 'id'; // nome del campo per il controllo del valore di esclusione
   * $opts['query_part'] = '';   // eventuale parte di query da aggiungere
   * $opts['join']       = array(); // record join:
   * $opts['join']['tbl']       = 'nometabella'; // tabella con cui effettuare la join
   * $opts['join']['on1']       = 'campo1'; // campo di unione per la join
   * $opts['join']['operatore'] = '='; // operatore di confronto per la join
   * $opts['join']['on2']       = 'nometabella'; // secondo campo di unione
   *
   * $opts['errore']     = false; // eventuale errore personalizzato.
   * </code>
   *
   * @return boolean
   */
  private function UnicoRule() {

    $parametri = func_get_args();
    $parametri = array_pop($parametri);

    $opts['table']      = '';
    $opts['confronto']  = 'id';
    $opts['escludi']    = 0;
    $opts['query_part'] = '';
    $opts['join']       = [];
    $opts['errore']     = false;
    $opts['debug']      = false;

    if($parametri['ARGS']) {
      $opts = $this->array_replace_recursive($opts, $parametri['ARGS']);
    }

    $sql = 'SELECT count('.$opts['confronto'].') FROM '.$opts['table'];

    if(count($opts['join'])) {
      $sql .= ' LEFT JOIN '.$opts['join']['tbl'].' ON '.$opts['table'].'.'.$opts['join']['on1'].' '.$opts['join']['operatore'].' '.$opts['join']['on2'];
    }

    $sql .= ' WHERE '.$parametri['FIELD'].'="'.$this->realEscape($parametri['VAL']).'" && '.$opts['confronto'].' != "'.$this->realEscape($opts['escludi']).'" '.$opts['query_part'].' LIMIT 1';

    if($opts['debug']) {
      $this->log($opts);
      $this->log($sql);
    }
    $res = $this->doQuery($sql);

    $row = mysqli_fetch_row($res);

    if(!$row[0]) {
      return true;
    } else {
      $lang  = preg_replace('/[^a-z]/', '', $parametri['LANG']);
      $field = $parametri['FIELD'];
      if($lang) {
        $field .= '_'.$lang;
      }
      $this->wrongFields[$field] = 1;

      if($opts['errore']) {
        $this->errori[$field] = sprintf($opts['errore'], $parametri['NOME'].$parametri['LANG']);
      } else {
        $this->errori[$field] = sprintf('Campo "%s" non valido, perchè già presente in database', $parametri['NOME'].$parametri['LANG']);
      }
    }

    return false;

  }

  /**
   * Regola per il controllo di validità numerica compresa tra due valori
   * Ritorna true o false, e nel caso di false riempie l'array degli errori con un messaggio.
   * @return boolean
   */
  private
  function NumRangeRule() {

    $parametri = func_get_args();
    $parametri = array_pop($parametri);

    $opts        = [];
    $opts['min'] = 0;
    $opts['max'] = 1000;

    if($parametri['ARGS']) {
      $opts = $this->array_replace_recursive($opts, $parametri['ARGS']);
    }

    if(is_numeric($parametri['VAL']) && $parametri['VAL'] >= $opts['min'] && $parametri['VAL'] <= $opts['max']) {
      return true;
    } else {
      $field = $parametri['FIELD'];
      if($parametri['LANG']) {
        $field .= '_'.$parametri['LANG'];
      }
      $this->wrongFields[$field] = 1;
      $this->errori[]            = sprintf('Campo "%s" non valido, richiesto valore compreso tra %d e %d', $parametri['NOME'].$parametri['LANG'], $opts['min'], $opts['max']);

      return false;
    }
  }

  /**
   * Controllo di validità su partita iva italiana
   * @return boolean
   */
  private
  function PIvaRule() {

    $parametri = func_get_args();
    $parametri = array_pop($parametri);

    $valid = true;

    if(!is_string($parametri['VAL']) || $parametri['VAL'] == '' || strlen($parametri['VAL']) != 11 || !preg_match("/^[0-9]+$/", $parametri['VAL'])) {
      $valid = false;
    }

    $s = 0;

    for($i = 0; $i <= 9; $i += 2)
      $s += ord($parametri['VAL'][$i]) - ord('0');
    for($i = 1; $i <= 9; $i += 2) {
      $c = 2 * (ord($parametri['VAL'][$i]) - ord('0'));
      if($c > 9) $c = $c - 9;
      $s += $c;
    }
    if((10 - $s % 10) % 10 != ord($parametri['VAL'][10]) - ord('0')) $valid = false;

    if(!$valid) {
      $lang  = preg_replace('/[^a-z]/', '', $parametri['LANG']);
      $field = $parametri['FIELD'];
      if($lang) {
        $field .= '_'.$lang;
      }
      $this->wrongFields[$field] = 1;
      $this->errori[]            = sprintf('Campo "%s" non valido, partita iva errata', $parametri['NOME'].$parametri['LANG']);

      return false;
    }

    return true;
  }

  /**
   * Controllo di validità su codice fiscale italiano
   * @return boolean
   */
  private
  function CodiceFiscaleRule() {

    $parametri = func_get_args();
    $parametri = array_pop($parametri);

    $valid = true;

    if(!is_string($parametri['VAL']) || $parametri['VAL'] == '') {
      $valid = false;
    }

    if(strlen($parametri['VAL']) != 16) {
      $valid = false;
    }

    $cf = strtoupper($parametri['VAL']);

    if(!preg_match("/^[A-Z0-9]+$/", $cf)) {
      $valid = false;
    }

    $s = 0;

    for($i = 1; $i <= 13; $i += 2) {
      $c = $cf[$i];
      if('0' <= $c && $c <= '9')
        $s += ord($c) - ord('0');
      else
        $s += ord($c) - ord('A');
    }

    for($i = 0; $i <= 14; $i += 2) {
      $c = $cf[$i];

      switch($c) {
        case '0':
          $s += 1;
          break;
        case '1':
          $s += 0;
          break;
        case '2':
          $s += 5;
          break;
        case '3':
          $s += 7;
          break;
        case '4':
          $s += 9;
          break;
        case '5':
          $s += 13;
          break;
        case '6':
          $s += 15;
          break;
        case '7':
          $s += 17;
          break;
        case '8':
          $s += 19;
          break;
        case '9':
          $s += 21;
          break;
        case 'A':
          $s += 1;
          break;
        case 'B':
          $s += 0;
          break;
        case 'C':
          $s += 5;
          break;
        case 'D':
          $s += 7;
          break;
        case 'E':
          $s += 9;
          break;
        case 'F':
          $s += 13;
          break;
        case 'G':
          $s += 15;
          break;
        case 'H':
          $s += 17;
          break;
        case 'I':
          $s += 19;
          break;
        case 'J':
          $s += 21;
          break;
        case 'K':
          $s += 2;
          break;
        case 'L':
          $s += 4;
          break;
        case 'M':
          $s += 18;
          break;
        case 'N':
          $s += 20;
          break;
        case 'O':
          $s += 11;
          break;
        case 'P':
          $s += 3;
          break;
        case 'Q':
          $s += 6;
          break;
        case 'R':
          $s += 8;
          break;
        case 'S':
          $s += 12;
          break;
        case 'T':
          $s += 14;
          break;
        case 'U':
          $s += 16;
          break;
        case 'V':
          $s += 10;
          break;
        case 'W':
          $s += 22;
          break;
        case 'X':
          $s += 25;
          break;
        case 'Y':
          $s += 24;
          break;
        case 'Z':
          $s += 23;
          break;
      }
    }
    if(chr($s % 26 + ord('A')) != $cf[15]) $valid = false;

    if(!$valid) {
      $lang  = preg_replace('/[^a-z]/', '', $parametri['LANG']);
      $field = $parametri['FIELD'];
      if($lang) {
        $field .= '_'.$lang;
      }
      $this->wrongFields[$field] = 1;
      $this->errori[]            = sprintf('Campo "%s" non valido, codice fiscale errato', $parametri['NOME'].$parametri['LANG']);

      return false;
    }

    return true;

  }

  /**
   * Controllo con espressione regolare sul valore passato, l'espressione regolare deve essere passata tramite argomento "regex".
   * @return boolean
   */
  private
  function PatternRule() {

    $parametri = func_get_args();
    $parametri = array_pop($parametri);

    $opts          = [];
    $opts['regex'] = false;

    if($parametri['ARGS']) {
      $opts = $this->array_replace_recursive($opts, $parametri['ARGS']);
    }

    if(!$opts['regex']) {
      $this->log('PatternRule chiamata con parametri errati', ['level' => 'error', 'dieAfterError' => true]);
    }

    $parametri['VAL'] = trim($parametri['VAL']);

    if(!preg_match($opts['regex'], $parametri['VAL'])) {
      $lang  = preg_replace('/[^a-z]/', '', $parametri['LANG']);
      $field = $parametri['FIELD'];
      if($lang) {
        $field .= '_'.$lang;
      }
      $this->wrongFields[$field] = 1;
      $this->errori[$field]      = sprintf('Campo "%s" non valido, non vengono soddisfatti i criteri richiesti', $parametri['NOME'].$parametri['LANG']);

      return false;
    } else {
      return true;
    }
  }

  /**
   * Regola che si avvale delle funzionalità di php5 per il controllo della validità sintattica di un indirizzo email passato
   * Ritorna true o false, e nel caso di false riempie l'array degli errori con un messaggio.
   * @return boolean
   */
  private
  function EmailRule() {

    $parametri = func_get_args();
    $parametri = array_pop($parametri);
    if(!filter_var($parametri['VAL'], FILTER_VALIDATE_EMAIL)) {
      $lang  = preg_replace('/[^a-z]/', '', $parametri['LANG']);
      $field = $parametri['FIELD'];
      if($lang) {
        $field .= '_'.$lang;
      }
      $this->wrongFields[$field] = 1;
      $this->errori[]            = sprintf('Campo "%s" non è un\'indirizzo email valido', $parametri['NOME'].$parametri['LANG']);

      return false;
    } else {
      return true;
    }
  }

  /**
   * Regola che si avvale delle funzionalità di php5 per il controllo della validità sintattica di un url passato
   * Ritorna true o false, e nel caso di false riempie l'array degli errori con un messaggio.
   * @return boolean
   */
  private
  function UrlRule() {

    $parametri = func_get_args();
    $parametri = array_pop($parametri);
    if(!filter_var($parametri['VAL'], FILTER_VALIDATE_URL)) {
      $lang  = preg_replace('/[^a-z]/', '', $parametri['LANG']);
      $field = $parametri['FIELD'];
      if($lang) {
        $field .= '_'.$lang;
      }
      $this->wrongFields[$field] = 1;
      $this->errori[]            = sprintf('Campo "%s" non è un\'indirizzo internet valido', $parametri['NOME'].$parametri['LANG']);

      return false;
    } else {
      return true;
    }
  }

  /**
   * Regola per il controllo di validità delle estensioni di un file caricato, il metodo ricerca nell'argomento 'extensions' le estensioni valide e confronta che l'estensione del nome del file passato sia presente nell'array
   * Ritorna true o false, e nel caso di false riempie l'array degli errori con un messaggio.
   * @return boolean
   */
  private
  function FileExtensionRule() {

    $parametri = func_get_args();
    $parametri = array_pop($parametri);

    $opts               = [];
    $opts['extensions'] = ['jpg', 'jpeg', 'png', 'gif'];

    if($parametri['ARGS']) {
      $opts = $this->array_replace_recursive($opts, $parametri['ARGS']);
    }

    if(!is_array($opts['extensions'])) {
      $this->log('FileExtensionRule chiamata con parametri errati', ['level' => 'error', 'dieAfterError' => true]);
    }

    if(!is_string($parametri['VAL'])) {
      $this->errori[$parametri['FIELD']] = sprintf('Campo "%s" non valido, non è una stringa', $parametri['NOME'].$parametri['LANG']);

      return false;
    } else {
      $extension = explode('.', $parametri['VAL']);
      $extension = array_pop($extension);
      $extension = strtolower($extension);

      if(in_array($extension, $opts['extensions'])) {
        return true;
      } else {
        $lang  = preg_replace('/[^a-z]/', '', $parametri['LANG']);
        $field = $parametri['FIELD'];
        if($lang) {
          $field .= '_'.$lang;
        }
        $this->wrongFields[$field] = 1;
        $this->errori[]            = sprintf('Campo "%s" non valido, l\'estensione non è soddifatta', $parametri['NOME'].$parametri['LANG']);

        return false;
      }
    }

  }

  /**
   * Regola per il controllo di validità di lunghezza di una stringa
   * Ritorna true o false, e nel caso di false riempie l'array degli errori con un messaggio.
   * @return boolean
   */
  private
  function StrRangeRule() {

    $parametri = func_get_args();
    $parametri = array_pop($parametri);

    $opts        = [];
    $opts['min'] = 0;
    $opts['max'] = 1000;

    if($parametri['ARGS']) {
      $opts = $this->array_replace_recursive($opts, $parametri['ARGS']);
    }

    if(is_string($parametri['VAL']) && $parametri['VAL'] && strlen($parametri['VAL']) >= $opts['min'] && strlen($parametri['VAL']) <= $opts['max']) {
      return true;
    } else {
      $lang  = preg_replace('/[^a-z]/', '', $parametri['LANG']);
      $field = $parametri['FIELD'];
      if($lang) {
        $field .= '_'.$lang;
      }
      $this->wrongFields[$field] = 1;
      $this->errori[]            = sprintf('Campo "%s" non valido, richiesta lunghezza compresa tra %d e %d caratteri', $parametri['NOME'].$parametri['LANG'], $opts['min'], $opts['max']);

      return false;
    }
  }

  /**
   * Ritorna un array contenente i nomi dei campi errati
   * @return array Array contenente i nomi dei campi che hanno scaturito un errore.
   */
  function getWrongFields() {

    return $this->wrongFields;
  }

  /**
   * Ritorna un array contenente le sigle delle lingue su cui sta lavorando l'oggetto.
   * @return array Contenente le sigle delle lingue su cui sta lavorando l'oggetto.
   */
  function getLangs() {

    return $this->opts['langs'];
  }

  protected
  function getLabels() {

    foreach($this->dataDescription['desc']['ci'] as $field) {
      $this->labels[$field] = Traduzioni::getLang($this->dataDescription['table'], strtoupper($field), 0, 0);
    }
    foreach($this->dataDescription['desc']['cd'] as $field) {
      $this->labels[$field] = Traduzioni::getLang($this->dataDescription['table'], strtoupper($field), 0, 0);
    }
  }

  /**
   * Funzione per aggiungere errori personalizzati durante la validazione di un oggetto che richiede controlli particolari non gestibili tramite le regole
   * previste per la validazione.
   * @param string $error Messaggio di errore
   * @param string $field Nome del campo, è facoltativo, ma se non valorizzato non apparirà nell'array restituito dal metodo getWrongFields
   */
  function addError($error, $field = false) {

    if($field) {
      if(is_array($field)) {
        foreach($field as $f) {
          $this->wrongFields[$f] = 1;
        }
      } else {
        $this->wrongFields[$field] = 1;
      }
    }
    if($field) {
      if(is_array($field)) {
        $this->errori[array_shift($field)] = $error;
      } else {
        $this->errori[$field] = $error;
      }
    } else {
      $this->errori[] = $error;
    }
  }

  /**
   * Metodo che verifica la presenza di alemeno una delle 2 seguenti costanti:
   * <br />
   * <br />
   * SET_$NOMETABELLA_IMG_ALLOWED
   * <br />
   * SET_$NOMETABELLA_FILES_ALLOWED
   * <br />
   * <br />
   * Dove $NOMETABELLA è il nome della tabella che l'oggetto rappresenta.
   * <br />
   * Se presenti valuta che almeno una delle 2 sia valorizzata a true, se si ritorna vero,
   * altrimenti ritorna false.
   * Se si vogliono abilitare gli allegati per un nuovo oggetto creare quindi la relativa costante nel pannello di ueppy3.
   * @return boolean Ritorna vero se sono stati abilitati gli allegati per l'oggetto.
   */
  public function allegatiAbilitati() {

    $costanti[] = 'SET_'.strtoupper($this->dataDescription['table']).'_IMG_ALLOWED';
    $costanti[] = 'SET_'.strtoupper($this->dataDescription['table']).'_FILES_ALLOWED';

    foreach($costanti as $c) {
      if(defined($c) && constant($c)) {
        return true;
      }
    }

    return false;
  }

  /**
   * La funzione dati alcuni parametri ritorna un array degli allegati all'oggetto.
   * Gli allegati vengono estratti dalla tabella allegati e i campi di collegamento presenti nella tabella allegati sono 2:
   * <br />
   * <strong>id_genitore</strong> deve essere uguale all'id del record rappresentato dall'oggetto
   * <br />
   * <strong>genitore</strong>    deve essere uguale alla tabella rappresentata dall'oggetto.
   * <br />
   * le opzioni possibili sono:
   * <code>
   * $opts['tableFilename'] = 'allegati'; // string, di default impostato ad "allegati" è possibile passare una diversa tabella per personalizzare in modo spinto il sistema.
   * $opts['forceAllLang'] = false; [true|false]  // boolean, se impostato a true reperisce tutte le lingue altrimenti esegue ricerche ed estrazione sulla sola lingua attuale
   * $opts['debug']        = false; [true|false]  // attiva il debug
   * $opts['countOnly']    = false; [true|false]  // passare true per ottenere solo il numero di record che soddisfano la ricerca
   * $opts['estensioni']   = false; [elencodiestensioni|img|notimg] // passare una stringa contenente le estensioni richieste separate da virgola es. 'pdf,txt'. Sono previste inoltre 2 parole chiave: img che corrisponde a 'jpg,jpeg,png,gif' e notimg che estrae tutto tranne le estensioni previste da img
   * $opts['quanti']       = 0;     [int] // quanti elementi estrarre, il default è 0 e indica di estrarre tutti gli elementi
   * $opts['start']        = 0;     [int] // record di partenza da cui estrarre $opts['quanti']
   * </code>
   * @param array $opts array delle opzioni
   * @return array di oggetti allegato
   */
  public
  function getAllegati($opts = null) {

    $opzioni                 = [];
    $opzioni['forceAllLang'] = false;   // boolean, se impostato a true reperisce tutte le lingue altrimenti esegue ricerche ed estrazione sulla sola lingua attuale
    $opzioni['debug']        = 0;       // debug
    $opzioni['countOnly']    = false;   // passare true per ottenere solo il numero di record che soddisfano la ricerca
    $opzioni['estensioni']   = false;
    $opzioni['quanti']       = 0;
    $opzioni['start']        = 0;

    if($opts) {
      $opzioni = $this->array_replace_recursive($opzioni, $opts);
    }

    if($opzioni['debug']) {
      $this->opts['debug'] = $opzioni['debug'];
      $this->log($opzioni);
    }

    $nomeFileType = 'file';

    $filters = [];

    $filter_record              = [];
    $filter_record['chiave']    = 'id_genitore';
    $filter_record['operatore'] = '=';
    $filter_record['valore']    = $this->fields['id'];

    $filters[] = $filter_record;

    $filter_record              = [];
    $filter_record['chiave']    = 'genitore';
    $filter_record['operatore'] = '=';
    $filter_record['valore']    = $this->dataDescription['table'];

    $filters[] = $filter_record;

    $options = [];

    if($opzioni['estensioni'] !== false) {
      $operator = 'IN';
      if($opzioni['estensioni'] == 'img') {
        $opzioni['estensioni'] = '"jpg","jpeg","png","gif"';
        $nomeFileType          = 'image';

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


        $NOME_COSTANTE = 'SET_'.strtoupper($this->dataDescription['table']).'_IMG_SIZE';

        if(defined($NOME_COSTANTE)) {
          $dimensioni = constant($NOME_COSTANTE);
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

      } elseif($opzioni['estensioni'] == 'notimg') {
        $opzioni['estensioni'] = '"jpg","jpeg","png","gif"';
        $operator              = 'NOT IN';
      }
      $filter_record              = [];
      $filter_record['chiave']    = 'estensione';
      $filter_record['operatore'] = $operator;
      $filter_record['valore']    = '('.$opzioni['estensioni'].')';

      $filters[] = $filter_record;
    }

    $options['tableFilename'] = $this->opts['tableAllegati'];

    $allegati                                       = new Allegati($options);
    $allegati->dataDescription['types']['nomefile'] = $nomeFileType;

    $opzioni['sortField'] = 'ordine';
    $opzioni['sortOrder'] = 'asc';
    $opzioni['filters']   = $filters;
    $opzioni['operatore'] = 'AND';

    $list = $allegati->getlist($opzioni);

    return $list;

  }

  /**
   * Funzione di utilità per unire l'array delle opzioni passate con quelle di default della classe
   * @param type $opts array delle opzioni
   */
  protected function array_replace_recursive($array1, $array2) {

    if(!is_array($array1)) {
      Utility::pre($array1);
      die;
    }
    if(!is_array($array2)) {
      Utility::pre($array2);
      die;
    }

    return array_replace_recursive($array1, $array2);
  }

  /**
   * Funzione per il debug della classe
   */
  protected
  function log($data, $opts = null) {

    $logOpts = ['file'          => false,
                'showBacktrace' => true,
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

    if($logOpts['showBacktrace']) {
      foreach($debug_array as $da) {
        $f = $da['file'];
        if(defined('DOC_ROOT')) {
          $f = str_replace(DOC_ROOT, '', $f);
        }
        $string .= $f.', '.$da['line'];
        $string .= $acapo;
      }
      $string .= $acapo;
    }

    if(is_array($data)) {
      if($logOpts['showBacktrace']) {
        $string .= "Tipo: Array";
        $string .= $acapo;
      }
      if(empty($data)) {
        if($logOpts['showBacktrace']) {
          $string .= "array vuoto";
          $string .= $acapo;
        }
      } else {
        if($logOpts['showBacktrace']) {
          $string .= "Numero Elementi :".count($data);
          $string .= $acapo;
        }
        $string .= print_r($data, true);
        $string .= $acapo;
      }
    } elseif(is_object($data)) {
      if($logOpts['showBacktrace']) {
        $string .= "Oggetto";
        $string .= $acapo;
      }
      $string .= print_r($data, true);
      $string .= $acapo;
    } elseif(is_bool($data)) {
      if($logOpts['showBacktrace']) {
        if($data) {
          $string .= "bool: true";
        } else {
          $string .= "bool: false";
        }
        $string .= $acapo;
      }
    } else {
      if($logOpts['showBacktrace']) {
        $string .= "Tipo: Stringa(".strlen($data).")";
        $string .= $acapo;
      }
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
      if(isset($GLOBALS['operator']) && $GLOBALS['operator'] && $GLOBALS['operator']->fields['super_admin']) {
        echo $string;
      } else {
        echo $string;
      }
    }

    if($logOpts['dieAfterError'] && $logOpts['level'] == 'error') {
      die();
    }

  }

  protected
  function realEscape($string) {

    if($string && !is_string($string) && !is_numeric($string)) {
      $this->log('Tipo di dato errato per query:'."\n".print_r($string, true), ['level' => 'error', 'dieAfterError' => true]);
    }
    $this->connectdb();
    $str = mysqli_real_escape_string($this->db_conn, $string);
    $this->closedb();

    return $str;
  }

  /**
   * Esegue una ricerca sui record dell'oggetto usando la sintassi MATCH AGAINST.
   * L'array delle opzioni richiede i seguenti dati:
   * <code>
   * $opts['debug']     = false; [true|false] Attiva il debug
   * $opts['srcFields'] = array();            Array contenente i campi sui quali effettuare la ricerca
   * $opts['start']     = false; [false|int]  valore di start da passare a limit
   * $opts['countOnly'] = false; [true|false] passare true per ottenere solo il numero di record che soddisfano la ricerca
   * $opts['quanti']    = false; [false|int]  valore di quantità da passare a limit
   * $opts['joins']     =
   * $opts['filters']   =
   * </code>
   */
  public function search($opts = null) {

    $opzioni              = [];
    $opzioni['srcFields'] = [];
    $opzioni['ricerca']   = '';
    $opzioni['start']     = false;
    $opzioni['quanti']    = false;
    $opzioni['debug']     = false;
    $opzioni['countOnly'] = false;
    $opzioni['raw']       = false;
    $opzioni['joins']     = [];
    $opzioni['filters']   = [];
    $opzioni['operatore'] = 'AND';

    if($opts) {
      $opzioni = $this->array_replace_recursive($opzioni, $opts);
    }

    if(!count($opzioni['srcFields']) || !$opzioni['ricerca']) {
      $this->log('Mi aspetto almeno un campo su cui effettuare la ricerca e una stringa da cercare, opzioni passate:'."\n".print_r($opts, true), ['level' => 'error', 'dieAfterError' => true]);
    }

    if($opzioni['debug']) {
      $this->log($opzioni);
    }

    $opzioni['fields'][] = $this->dataDescription['table'].'.'.$this->dataDescription['desc']['cp'];
    foreach($this->dataDescription['desc']['ci'] as $field) {
      $opzioni['fields'][] = $this->dataDescription['table'].'.'.$field;
    }
    if(isset($this->additionalFields) && is_array($this->additionalFields) && count($this->additionalFields)) {
      $opzioni['fields'] = array_merge($opzioni['fields'], $this->additionalFields);
    }


    $sql = 'SELECT ';
    $sql .= implode(', ', $opzioni['fields']);

    // se multilingua
    if($this->dataDescription['table_langs']) {

      foreach($this->dataDescription['desc']['cd'] as $field) {
        if($field != 'lingua') {
          $opzioni['langFields'][] = $this->dataDescription['table_langs'].'.'.$field.' as '.$field.'_'.$this->opts['lang'];
        }
      }
      $sql .= ', '.implode(', ', $opzioni['langFields']);
    }

    $matchFields = [];

    foreach($opzioni['srcFields'] as $field) {
      if(in_array($field, $this->dataDescription['desc']['ci'])) {
        $matchFields[] = $field;
      } elseif(in_array($field, $this->dataDescription['desc']['cd'])) {
        $matchFields[] = $this->dataDescription['table_langs'].'.'.$field;
      }
    }

    $sql .= ', MATCH('.implode(',', $matchFields).') AGAINST (\''.$this->realEscape($opzioni['ricerca']).'\' IN BOOLEAN MODE) as pertinenza';

    $sql .= ' FROM '.$this->dataDescription['table'];

    $where = [];

    // se multilingua
    if($this->dataDescription['table_langs']) {
      $sql .= ' LEFT JOIN '.$this->dataDescription['table_langs'].' ON '.$this->dataDescription['table_langs'].'.'.$this->dataDescription['table'].'_'.$this->dataDescription['desc']['cp'].' = '.$this->dataDescription['table'].'.'.$this->dataDescription['desc']['cp'];
      $where[] = $this->dataDescription['table_langs'].'.lingua = "'.$this->opts['lang'].'"';
    }

    // se presenti delle joins
    if(count($opzioni['joins'])) {

      $joins_strings = [];

      foreach($opzioni['joins'] as $j) {

        $js = ' LEFT JOIN '.$j['table'];
        if(isset($j['alias']) && $j['alias']) {
          $js .= ' as '.$j['alias'];
        }

        $js .= ' ON ';
        if(isset($j['alias']) && $j['alias']) {
          $js .= $j['alias'];
        } else {
          $js .= $j['table'];
        }
        $js .= '.'.$j['on1'];
        $js .= $j['operatore'];
        $js .= $j['on2'];
        $joins_string[] = $js;
      }

      $sql .= ' '.implode(' ', $joins_string);

      if($opzioni['debug']) {
        $this->log('Aggiungo group by per non duplicare i dati');
      }
      $opzioni['group_by'] = $this->dataDescription['table'].'.'.$this->dataDescription['desc']['cp'];

    }

    // parte di ricerca
    if($opzioni['filters']) {

      if($opzioni['debug']) {
        $this->log($opzioni['filters']);
      }

      $where_filter = [];
      foreach($opzioni['filters'] as $filterIter => $filter) {
        if(isset($filter['chiave']) && $filter['chiave'] && isset($filter['valore']) && isset($filter['operatore']) && in_array($filter['operatore'], $this->acceptedFilterOperator)) {
          // se la chiave di ricerca è nella tabella lingue
          if(in_array($filter['chiave'], $this->dataDescription['desc']['cd'])) {
            $filter['chiave'] = $this->dataDescription['table_langs'].'.'.$filter['chiave'];
          } elseif($filter['chiave'] == $this->dataDescription['desc']['cp']) {
            $filter['chiave'] = $this->dataDescription['table'].'.'.$filter['chiave'];
          } elseif(in_array($filter['chiave'], $this->dataDescription['desc']['ci'])) {
            $filter['chiave'] = $this->dataDescription['table'].'.'.$filter['chiave'];
          }

          if($filter['operatore'] == 'IN' || $filter['operatore'] == 'NOT IN' || (in_array($filter['operatore'], ['IS', 'IS NOT']) && $filter['valore'] === 'NULL')) {
            $where_filter[] = $filter['chiave'].' '.$filter['operatore'].' '.$filter['valore'];
          } else {
            $where_filter[] = $filter['chiave'].' '.$filter['operatore'].' "'.$this->realEscape($filter['valore']).'"';
          }
        } else {
          $str = 'Filter Errato:';
          $str .= "\n";
          $str .= print_r($filter, true);
          $this->log($str, ['level' => 'error', 'dieAfterError' => true]);
        }
      }

      if(is_array($opzioni['operatore'])) {
        $whereString = '(';
        foreach($opzioni['operatore'] as $gruppo) {
          if(is_array($gruppo)) {
            $gruppoWhere = [];
            for($i = 1; $i <= $gruppo['quanti']; $i++) {
              $gruppoWhere[] = array_shift($where_filter);
            }
            $whereString .= '('.implode(' '.$gruppo['subOperator'].' ', $gruppoWhere).')';
          } else {
            $whereString .= ' '.$gruppo.' ';
          }
        }
        $whereString .= ')';
        $where[] = $where;
        //$this->log($opzioni['operatore']);
      } else {
        $where[] = implode(' '.$opzioni['operatore'].' ', $where_filter);
      }
    }


    $where[] = 'MATCH('.implode(',', $matchFields).') AGAINST (\''.$this->realEscape($opzioni['ricerca']).'\' IN BOOLEAN MODE) ORDER BY pertinenza DESC';

    $sql .= ' WHERE '.implode(' AND ', $where);

    if($opzioni['countOnly']) {

      if($opzioni['debug']) {
        $this->log($sql);
      }

      $this->sql = $sql;

      $this->resultSet = $this->doQuery($sql);

      if(!$this->resultSet) {
        if($this->isSqlErrorNotified()) {
          $this->log('Si è verificato un\'errore nell\'interrogazione del database, l\'amministratore è stato avvisato', ['showBacktrace' => false, 'level' => 'error', 'dieAfterError' => true]);
        } else {
          $this->log($this->sql."\n".$this->lastError, ['level' => 'error', 'dieAfterError' => true]);
        }
      }

      $count = mysqli_num_rows($this->resultSet);

      return $count;

    } else {

      if($opzioni['quanti']) {
        $sql .= ' LIMIT '.$opzioni['start'].', '.$opzioni['quanti'];
      }

      if($opzioni['debug']) {
        $this->log($sql);
      }

      // salvo l'ultima query
      $this->sql = $sql;

      $this->resultSet = $this->doQuery($sql);

      if($opzioni['raw']) {
        return $this->fillraw();
      } else {
        return $this->fillresults();
      }

    }


  }

  /**
   * Esegue il dump della tabella rappresentata dall'oggetto secondo alcuni criteri di filtraggio.
   * Il metodo tenta di utilizzare mysqldump se ne ha i permessi, altrimenti compone la query utilizzando php.
   * Come prima riga di commento della stringa generata viene evidenziato il metodo utilizzato per il dump.
   * Il metodo accetta le seguenti opzioni:
   * <code>
   * $opts                    = array();
   * $opts['debug']           = false; [true|false] Attiva il debug
   * $opts['overwrite_table'] = false; [true|false] Se impostato a true viene aggiunta l'istruzione DROP TABLE prima del codice per la creazione
   * $opts['overwrite_data']  = false; [true|false] Se impostato a true viene utilizzata la sintassi REPLACE, altrimenti INSERT IGNORE INTO
   * $opts['dati']            = true;  [true|false] Se impostato a false viene creato il dump della sola struttura della tabella.
   * $opts['whereString']     = '';    [stringa di condizione] Se impostato vengono filtrati i dati, es.: "id>100"
   * $opts['filters']         = array(); // al momento non testato.
   * $opts['out']             = DOC_ROOT.REL_ROOT.UPLOAD.'temp/temp.sql'; [null|percorso] Se non impostato la classe ritorna una stringa contenente le istruzioni, altrimenti queste vengono salvate
   *                                                                                      sul file fornite nel percorso.
   * $opts['forcePhpMethod']  = false; [true|false] Se impostato a true la classe userà sempre il metodo php che è quello più dispendioso a livello di risorse ma che funziona sicuro su tutti i server,
   *                                                se impostato a false la classe cerca di usare sempre prima il metodo con mysqldump ricadendo nel metodo php nel caso non ci siano i permessi
   *                                                adeguati.
   * </code>
   * @param array $opts Array delle opzioni
   * @return mixed Bolean o String Se impostato il file di output ritorna true o false come esito, altrimenti ritorna la stringa sql
   */

  public function dump($opts = null) {

    $opzioni                    = [];
    $opzioni['debug']           = false;
    $opzioni['overwrite_table'] = 0;
    $opzioni['overwrite_data']  = 0;
    $opzioni['dati']            = 1;
    $opzioni['whereString']     = '';
    $opzioni['filters']         = [];
    $opzioni['outType']         = 'file';
    $opzioni['out']             = false;
    $opzioni['forcePhpMethod']  = false;

    if($opts) {
      $opzioni = $this->array_replace_recursive($opzioni, $opts);
    }

    if($opzioni['debug']) {
      $this->opts['debug'] = $opzioni['debug'];
      $this->log($opzioni);
    }

    exec('which mysqldump', $return);

    $phpMethod = false;

    if(count($return)) {

      $mysqldump = $return[0];

      if(!$opzioni['forcePhpMethod'] && @is_executable($mysqldump)) {

        $arguments = [];

        $arguments[] = '--skip-comments';
        $arguments[] = '--skip-opt';
        $arguments[] = '--create-options';
        $arguments[] = '--default-character-set=latin1';

        if($opzioni['overwrite_table']) {
          $arguments[] = '--add-drop-table';
        }

        if($opzioni['dati']) {
          if($opzioni['overwrite_data']) {
            $arguments[] = '--replace';
          } else {
            $arguments[] = '--insert-ignore';
          }
        } else {
          $arguments[] = '--no-data';
        }

        $arguments[] = '--lock-all-tables';

        $where = '';

        if($opzioni['whereString']) {
          $where = ' --where=\''.$opzioni['whereString'].'\' ';
        }

        $arguments = ' '.implode(' ', $arguments);

        $cmd = $mysqldump.$arguments.$where.' -u'.$this->opts['db']['user'].' -p'.$this->opts['db']['pass'].' '.$this->opts['db']['name'].' '.$this->dataDescription['table'];
        if($opzioni['debug']) {
          $this->log($cmd);
        }
        exec($cmd, $out);
        $out     = implode("\n", $out)."\n";
        $outLang = '';
        if($this->dataDescription['table_langs']) {
          if($opzioni['whereString']) {
            $where = ' --where=\''.$this->dataDescription['table'].'_id IN (SELECT id FROM '.$this->dataDescription['table'].' WHERE '.$opzioni['whereString'].')\'';
          }
          $cmd = $mysqldump.$arguments.$where.' -u'.$this->opts['db']['user'].' -p'.$this->opts['db']['pass'].' '.$this->opts['db']['name'].' '.$this->dataDescription['table_langs'];
          exec($cmd, $outLang);
          $outLang = implode("\n", $outLang);
        }
        $out .= $outLang;

        if($opzioni['out']) {
          if(is_writable(dirname($opzioni['out']))) {
            file_put_contents($opzioni['out'], $out);

            return true;
          } else {
            $this->log('Il file di destinazione fornito non è scrivibile:'.$opzioni['out'], ['level' => 'error', 'dieAfterError' => true]);
          }
        } else {
          return $out;
        }
      } else {
        $phpMethod = true;
      }

    }

    if($opzioni['debug']) {
      $this->log('mysqldump non trovato, procedo con metodo su base query php');
    }

    if($phpMethod) {

      $DUMP = '';

      $SQLString = '';
      $SQLString .= '-- Dump dell\'oggetto: '.$this->dataDescription['table'];
      $SQLString .= "\n";
      $SQLString .= "\n";
      $SQLString .= '-- Struttura ';
      if($this->dataDescription['table_langs']) {
        $SQLString .= 'tabelle';
      } else {
        $SQLString .= 'tabella';
      }
      $SQLString .= "\n";
      $SQLString .= "\n";

      $tablesToDump   = [];
      $tablesToDump[] = $this->dataDescription['table'];
      if($this->dataDescription['table_langs']) {
        $tablesToDump[] = $this->dataDescription['table_langs'];
      }

      foreach($tablesToDump as $tbl) {
        $res = $this->doQuery('SHOW CREATE TABLE '.$tbl);
        if($res) {
          $row = mysqli_fetch_row($res);
          if($opzioni['overwrite_table']) {
            $SQLString .= 'DROP TABLE IF EXISTS '.$tbl.';';
            if($opzioni['outType'] == 'db') {
              $conf            = [];
              $conf['db_host'] = $this->opts['db_host'];
              $conf['db_user'] = $this->opts['db_user'];
              $conf['db_pass'] = $this->opts['db_pass'];
              $conf['db_name'] = $opzioni['out'];
              $db2             = new Db(0, $conf);
              $db2->connect();
              $db2->doQuery($SQLString);
              $SQLString = '';
            }
            $SQLString .= "\n";
            $SQLString .= $row[1].';';
            $SQLString .= "\n";
            $SQLString .= "\n";
          } else {
            $SQLString .= str_replace('CREATE TABLE', 'CREATE TABLE IF NOT EXISTS', $row[1]).';'."\n\n";
          }

          if($opzioni['out']) {
            if($opzioni['outType'] == 'db') {
              $conf            = [];
              $conf['db_host'] = $this->opts['db']['host'];
              $conf['db_user'] = $this->opts['db']['user'];
              $conf['db_pass'] = $this->opts['db']['pass'];
              $conf['db_name'] = $opzioni['out'];
              $db2             = new Db(0, $conf);
              $db2->connect();
              $db2->doQuery($SQLString);
            } else {
              if(is_writable(dirname($opzioni['out']))) {
                file_put_contents($opzioni['out'], $SQLString);
              } else {
                $this->log('Il file di destinazione fornito non è scrivibile:'.$opzioni['out'], ['level' => 'error', 'dieAfterError' => true]);
              }
            }
            $SQLString = '';
          } else {
            $DUMP .= $SQLString."\n";
          }

        } else {
          $this->log($this->lastError);
        }
      }


      if($opzioni['dati']) {

        // clausola WHERE riempita con filtri richiesti
        $where = '';

        $sql = 'SELECT * FROM '.$this->dataDescription['table'];

        // parte di ricerca
        if($opzioni['filters'] && !$opzioni['whereString']) {

          if($opzioni['debug']) {
            $this->log($opzioni['filters']);
          }

          $where_filter = [];
          foreach($opzioni['filters'] as $filterIter => $filter) {
            if(isset($filter['chiave']) && $filter['chiave'] && isset($filter['valore']) && isset($filter['operatore']) && in_array($filter['operatore'], $this->acceptedFilterOperator)) {
              if($filter['operatore'] == 'IN' || $filter['operatore'] == 'NOT IN') {
                $where_filter[] = $filter['chiave'].' '.$filter['operatore'].' '.$filter['valore'];
              } else {
                $where_filter[] = $filter['chiave'].' '.$filter['operatore'].' "'.addslashes($filter['valore']).'"';
              }
            } else {
              $str = 'Filter Errato:';
              $str .= "\n";
              $str .= print_r($filter, true);
              $this->log($str, ['level' => 'error', 'dieAfterError' => true]);
            }
          }

          if(is_array($opzioni['operatore'])) {
            $where .= '(';
            foreach($opzioni['operatore'] as $gruppo) {
              if(is_array($gruppo)) {
                $gruppoWhere = [];
                for($i = 1; $i <= $gruppo['quanti']; $i++) {
                  $gruppoWhere[] = array_shift($where_filter);
                }
                $where .= '('.implode(' '.$gruppo['subOperator'].' ', $gruppoWhere).')';
              } else {
                $where .= ' '.$gruppo.' ';
              }
            }
            $where .= ')';
            $this->log($opzioni['operatore']);
          } else {
            $where .= implode(' '.$opzioni['operatore'].' ', $where_filter);
          }
        } elseif($opzioni['whereString']) {
          $where = $opzioni['whereString'];
        }

        if($where) {
          $sql .= ' WHERE ';
          $sql .= $where;
        }

        if($opzioni['debug']) {
          $this->log($sql);
        }

        $res = $this->doQuery($sql);

        if(!$res) {
          Utility::pre($this->lastError);
          die;
        }

        while($row = mysqli_fetch_assoc($res)) {

          if(!$opzioni['overwrite_data']) {
            $SQLString = 'INSERT IGNORE INTO';
          } else {
            $SQLString = 'REPLACE INTO';
          }
          $SQLString .= ' `'.$this->dataDescription['table'].'` VALUES (';

          $fields = [];

          foreach($row as $field => $value) {
            $fields[] = '"'.addslashes($value).'"';
          }
          $SQLString .= implode(',', $fields).')';
          $SQLString .= ";\n";

          if($opzioni['out']) {
            if($opzioni['outType'] == 'db') {
              $conf            = [];
              $conf['db_host'] = $this->opts['db']['host'];
              $conf['db_user'] = $this->opts['db']['user'];
              $conf['db_pass'] = $this->opts['db']['pass'];
              $conf['db_name'] = $opzioni['out'];
              $db2             = new Db(0, $conf);
              $db2->connect();
              $db2->doQuery($SQLString);
            } else {
              file_put_contents($opzioni['out'], $SQLString, FILE_APPEND);
            }
            $SQLString = '';
          } else {
            $DUMP .= $SQLString."\n";
          }

        }

        if($this->dataDescription['table_langs']) {
          $sql = 'SELECT '.$this->dataDescription['table_langs'].'.* FROM '.$this->dataDescription['table_langs'].' LEFT JOIN '.$this->dataDescription['table'].' ON '.$this->dataDescription['table'].'.id='.$this->dataDescription['table'].'_langs.'.$this->dataDescription['table'].'_id';
          if($opzioni['whereString']) {
            $sql .= ' WHERE '.$this->dataDescription['table'].'.'.$opzioni['whereString'];
          }
          $res = $this->doQuery($sql);
          while($row = mysqli_fetch_assoc($res)) {
            if(!$opzioni['overwrite_data']) {
              $SQLString = 'INSERT IGNORE INTO';
            } else {
              $SQLString = 'REPLACE INTO';
            }
            $SQLString .= ' `'.$this->dataDescription['table_langs'].'` VALUES (';
            $fields = [];
            foreach($row as $field => $value) {
              $fields[] = '"'.addslashes($value).'"';
            }
            $SQLString .= implode(',', $fields).')';
            $SQLString .= ";\n";

            if($opzioni['out']) {
              if($opzioni['outType'] == 'db') {
                $conf            = [];
                $conf['db_host'] = $this->opts['db']['host'];
                $conf['db_user'] = $this->opts['db']['user'];
                $conf['db_pass'] = $this->opts['db']['pass'];
                $conf['db_name'] = $opzioni['out'];
                $db2             = new Db(0, $conf);
                if($db2->connect()) {
                  $db2->doQuery($SQLString);
                }
              } else {
                file_put_contents($opzioni['out'], $SQLString, FILE_APPEND);
              }
              $SQLString = '';
            } else {
              $DUMP .= $SQLString."\n";
            }
          }
        }
      }

      if(!$opzioni['out']) {
        return $DUMP;
      }
    }

  }

  /**
   * Esegue una serie di query poste su più righe e separate da ;
   * @param string $queries
   */
  function processQueries($queries) {

    $queries_sporche = explode(";\n", $queries);

    $queries = [];

    foreach($queries_sporche as $q) {
      $q = trim($q);
      if($q && strpos(trim($q), '/*') !== 0) {
        $queries[] = $q;
      }
    }

    foreach($queries as $q) {
      $this->doQuery($q);
    }
  }

  /**
   * Introdotto con il progetto minerva per cartelle cliniche
   */
  protected
  function checkModifyStatus() {

    clearstatcache();

    // prima controllo se lo sta modificando qualcun altro, controllo la directory sessions in cerca di file .sd,
    // per ognuno estraggo i dati e verifico che tipo di oggetto (dato dalla tabella) e id corrispondano a questo file, e poi
    // decido se l'id dell'operatore è lo stesso che sta editando (cioè è in sessione), se non è lo stesso, non creo il file in
    // sessione e setto readonly = true

    $sessionsFiles = glob(SESSIONS.'*.sd');

    $sessione = [];

    if($sessionsFiles && is_array($sessionsFiles)) {
      foreach($sessionsFiles as $sessionFile) {
        $id_operatore = basename($sessionFile);
        $id_operatore = explode('.', $id_operatore);
        $id_operatore = array_shift($id_operatore);
        if(filesize($sessionFile)) {
          $fp = fopen($sessionFile, "r");
          if(flock($fp, LOCK_EX)) {
            $data = fread($fp, filesize($sessionFile));
            if($data) {
              list($tbl, $id) = explode(':', $data);
            } else {
              $tbl = '';
              $id  = '';
            }
            if($tbl == $this->dataDescription['table']) {
              $record                  = [];
              $record['time']          = filemtime($sessionFile);
              $record['id_operatore']  = $id_operatore;
              $record['id']            = $id;
              $record['filename']      = $sessionFile;
              $sessione[$record['id']] = $record;
            }
            fclose($fp);
          }
        }
      }
    }

    // ho tutte le sessioni che stanno lavorando sulla tabella utilizzata in questa istanza, le sessioni hanno come indice l'id del record in lavorazione
    // ora devo verificare che:
    // 1. l'id in lavorazione sia presente o meno nell'elenco degli id in lavorazione presi dalla sessione
    // 2. se l'id è in lavorazione valutare se l'operatore in sessione è uguale o meno all'operatore registrato in modifica dell'oggetto.
    //
    //
    if(isset($sessione[$this->fields['id']])) {

      $lavorazione = $sessione[$this->fields['id']];

      if($lavorazione['id_operatore'] !== $_SESSION['LOG_INFO']['UID']) {
        $this->readonly    = true;
        $this->editingData = $lavorazione;
        $this->getModifyData();
      }

    } else {
      // non è in lavorazione da nessuno, indico che lo sto lavorando io.
      $this->writeSessionFile();
    }

  }

  public
  function writeSessionFile() {

    $sessionFileFilename = SESSIONS.$_SESSION['LOG_INFO']['UID'].'.sd';
    $data[]              = $this->dataDescription['table'];
    $data[]              = $this->fields['id'];
    $fp                  = fopen($sessionFileFilename, "w");
    if(flock($fp, LOCK_EX)) {
      fwrite($fp, implode(':', $data));
      fclose($fp);
    }
  }

  public
  function getModifyData() {


    $options                  = [];
    $options['tableFilename'] = 'operatori';

    $o = new Operatore($options);
    $o = $o->getById($this->editingData['id_operatore']);

    $str = 'Oggetto in modifica da parte di &quot;'.$o->fields['nomecompleto'].'&quot; dalle ore '.date('H:i:s', $this->editingData['time']).' del '.date('d-m-Y', $this->editingData['time']);

    return $str;

  }

  public function addField($field) {

    $this->additionalFields[] = $field;
  }

  /**
   * Es.
   *
   * $array_eliminazione :Array
   * (
   *     [0] => fojprn46u3s3uvfqmc8h5bnlf4
   *     [1] => ldpf62iap6a01m1buaadd6rfv0
   *     [2] => rs8l4libgf998vlqkekl22jqf5
   *     [3] => 48nohcjlo3rcf9spmgv14u6ea5
   *     [4] => kn2mun33ofm3iiob601gs7cdn5
   * )
   *
   * $array_eliminazione = array_map('Dba::enclose', $array_eliminazione);
   *
   * $array_eliminazione :Array
   * (
   *     [0] => "fojprn46u3s3uvfqmc8h5bnlf4"
   *     [1] => "ldpf62iap6a01m1buaadd6rfv0"
   *     [2] => "rs8l4libgf998vlqkekl22jqf5"
   *     [3] => "48nohcjlo3rcf9spmgv14u6ea5"
   *     [4] => "kn2mun33ofm3iiob601gs7cdn5"
   * )
   *
   *  $filters                    = array();
   *
   *  $filter_record              = array();
   *  $filter_record['chiave']    = 'session_id';
   *  $filter_record['operatore'] = 'IN';
   *  $filter_record['valore']    = '('.implode(',', $array_eliminazione).')';
   *  $filters[]                  = $filter_record;
   *
   */

  static function enclose($elem) {

    return '"'.$elem.'"';
  }

  public function ajaxResponse($opts = null) {

    $opzioni                  = [];
    $opzioni['formattedData'] = true;
    $opzioni['expose']        = 'all';
    $opzioni['debug']         = false;

    if($opts) {
      $opzioni = $this->array_replace_recursive($opzioni, $opts);
    }

    $this->formatData();

    $ajaxResult = [];

    foreach($this->fields as $k => $data) {
      if(is_array($data)) {
        if($k == 'fileData') {
          $ajaxResult['immagini'] = [];
          foreach($data as $fieldImmagine => $datiImmagineMultiLang) {
            // questo campo c'è di sicuro solo se non è un campo multilingua
            if(isset($datiImmagineMultiLang['exists'])) {
              $img             = [];
              $img['field']    = $fieldImmagine;
              $img['lang']     = '';
              $img['file']     = '';
              $img['basename'] = '';
              if($datiImmagineMultiLang['exists']) {
                $img['file']     = $datiImmagineMultiLang['versioni'][0]['rel_path'];
                $img['basename'] = basename($img['file']);
              }
              $ajaxResult['immagini'][] = $img;

            } else { // multilingua
              foreach($datiImmagineMultiLang as $lingua => $datiImmagine) {
                $img          = [];
                $img['field'] = $fieldImmagine;
                $img['lang']  = $lingua;
                $img['file']  = '';
                if($datiImmagine['exists']) {
                  $img['file'] = $datiImmagine['versioni'][0]['rel_path'];
                }
                $ajaxResult['immagini'][] = $img;
              }

            }
          }
        } else {
          $lang = $k;
          foreach($data as $field => $value) {
            if($opzioni['expose'] == 'all' || in_array($field, $opzioni['expose'])) {
              $ajaxResult[$field.'_'.$lang] = $value;
            }
          }
        }

      } else {
        if($opzioni['expose'] == 'all' || in_array($k, $opzioni['expose'])) {
          if(isset($this->additionalData[$k]) && $opzioni['formattedData']) {
            $ajaxResult[$k] = $this->additionalData[$k];
          } else {
            $ajaxResult[$k] = $data;
          }
        }
      }
    }

    return $ajaxResult;
  }

  static function xssSanitize($input) {

    $search = [
      '@<script[^>]*?>.*?</script>@si',   // Strip out javascript
      '@<[\/\!]*?[^<>]*?>@si',            // Strip out HTML tags
      '@<style[^>]*?>.*?</style>@siU',    // Strip style tags properly
      '@<![\s\S]*?–[ \t\n\r]*>@'         // Strip multi-line comments
    ];
    $output = preg_replace($search, '', $input);

    return $output;
  }

  public function toArray() {

    $data = [];

    $data = $this->fields;
    if(isset($this->fields[ACTUAL_LANGUAGE])) {
      foreach($this->fields[ACTUAL_LANGUAGE] as $k => $v) {
        if(!isset($data[$k])) {
          $data[$k] = $v;
        } else {
          $data[$k.'_'.ACTUAL_LANGUAGE] = $v;
        }
      }
    }

    if(isset($this->additionalData)) {
      $data['formattedData'] = $this->additionalData;
    }


    return $data;
  }

  function __clone() {

    $this->id = 0;
  }

  protected function formatData() {

    foreach($this->dataDescription['types'] as $field => $type) {
      if(isset($this->fields[$field])) {
        if($type == 'datetime') {
          $t                            = new Time($this->fields[$field]);
          $this->additionalData[$field] = $t->format(Traduzioni::getLang('cal', 'DATETIME_FORMAT'));
          continue;
        }
        if($type == 'date') {
          $t                            = new Time($this->fields[$field]);
          $this->additionalData[$field] = $t->format(Traduzioni::getLang('cal', 'DATE_FORMAT'));
          continue;
        }
        if(strpos($type, 'decimal') === 0) {
          $this->additionalData[$field] = number_format($this->fields[$field], 2, DEC_POINT, '');
          continue;
        }
      }
    }

  }

}