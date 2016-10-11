<?php
/***************/
/** v.1.02    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.02 (06/11/2015, 15.08)                                                                   **/
/** - Aggiunto namespace                                                                         **/
/**                                                                                              **/
/** v.1.01 (28/01/2013)                                                                          **/
/** - Aggiunto die dopo fallimento selezione db                                                  **/
/**                                                                                              **/
/** v.1.00                                                                                       **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
namespace Ueppy\core;

use Ueppy\utils\Utility;


class Db {

  var $db = "";

  var $gestisci_output;

  var $db_host   = "";
  var $db_user   = "";
  var $db_pass   = "";
  var $db_name   = "";
  var $new_connection;
  var $lastError = false;

  function __construct($gestisci_output = 1, $config = [], $new_connection = false, $debug = 0) {

    $debug_string = '';

    $this->gestisci_output = $gestisci_output;

    $this->new_connection = $new_connection;

    if(count($config)) {
      $this->db_host = $config['db_host'];
      $this->db_user = $config['db_user'];
      $this->db_pass = $config['db_pass'];
      if(isset($config['db_name'])) {
        $this->db_name = $config['db_name'];
      }
    } elseif(defined('DB_HOST') &&
      defined('DB_USER') &&
      defined('DB_PASS')
    ) {
      $this->db_host = DB_HOST;
      $this->db_pass = DB_PASS;
      $this->db_user = DB_USER;
      if(defined('DB_NAME')) {
        $this->db_name = DB_NAME;
      }

      $debug_string .= 'DB HOST:'.$this->db_host;
      $debug_string .= "\n";
      $debug_string .= 'DB NAME:'.$this->db_name;
      $debug_string .= "\n";
      $debug_string .= 'DB USER:'.$this->db_user;
      $debug_string .= "\n";
      $debug_string .= 'DB PASS:'.$this->db_pass;
      $debug_string .= "\n";


    } else {
      if($this->gestisci_output) {
        $err = 'File di configurazione o parametri non trovati';
        $debug_string .= $err;
        Utility::pre($err);
      } else {
        return false;
      }
    }

    if($debug) {
      Utility::pre($debug_string);
    }

  }

  /**
   * Stampa un errore in caso di connessione fallita e blocca l'esecuzione del php
   * In caso di connessione non ritorna nulla.
   */
  function connect($autoselect = true, $debug = 0) {

    $this->db = @mysqli_connect($this->db_host, $this->db_user, $this->db_pass);

    if($debug) {
      Utility::pre($this->db);
    }

    if($this->db == false) {

      if($this->gestisci_output || $debug) {

        Utility::pre('Connessione fallita');

      }

      return false;

    }

    if($this->db_name && $autoselect) {

      $result = $this->select($debug);

      if($result) {

        if($debug) {

          Utility::pre('Selezionato il database '.$this->db_name);

        }

      } else {

        if($debug) {

          Utility::pre('Errore nella selezione del database '.$this->db_name);

        }

      }

      if(!$this->gestisci_output) {

        return $result;

      }

    } else {

      return true;

    }

  }

  function select($debug = 0) {
    
    $selezione = mysqli_select_db($this->db, $this->db_name);

    if($selezione == false) {

      if($this->gestisci_output || $debug) {

        Utility::pre('Selezione del database '.$this->db_name.' fallita');
        // inutile andare avanti
        die;

      } else {

        return false;

      }

    } else {

      return true;

    }

  }

  function close($debug = 0) {

    if($debug) {

      Utility::pre('Connessione al database '.$this->db_name.' chiusa');

    }

    mysqli_close($this->db);

    $this->db = false;

  }

  function doQuery($sql, $close_after = true, $debug = 0) {

    if($debug) {

      Utility::pre('Sql:'.$sql);

    }

    $this->logQuery($sql);

    if(!$this->db) {
      $this->connect($debug);
    }

    $res = mysqli_query($this->db, $sql);


    if(!$res) {
      $this->lastError = mysqli_error($this->db);
    }

    if($close_after) {
      $this->close($debug);
    }

    return $res;

  }

  function logQuery($sql) {

    if(isset($GLOBALS['smarty']) && $GLOBALS['smarty']) {
      $GLOBALS['smarty']->queries[] = [$sql, 0];
    }

  }

  function dump($table, $opts = null) {

    $opzioni                    = [];
    $opzioni['debug']           = 0;
    $opzioni['overwrite_table'] = 1;
    $opzioni['overwrite_data']  = 0;
    $opzioni['dati']            = 1;
    $opzioni['whereString']     = '';
    $opzioni['filters']         = [];
    $opzioni['outType']         = 'file';
    $opzioni['out']             = false;
    $opzioni['forcePhp']        = true;

    if($opts) {
      $opzioni = array_replace_recursive($opzioni, $opts);
    }

    exec('which mysqldump', $return);

    $phpmethod = false;

    if(count($return)) {

      $mysqldump = $return[0];

      if(@is_executable($mysqldump) && !$opzioni['forcePhp']) {

        $arguments = [];

        $arguments[] = '--skip-comments';
        $arguments[] = '--skip-opt';
        $arguments[] = '--create-options';
//        $arguments[] = '--default-character-set=latin1';


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

        file_put_contents($opzioni['out'], '-- File generato con mysqldump il '.date('d-m-Y H:i:s'."\n"));


        $cmd = $mysqldump.$arguments.$where.' -u'.$this->db_user.' -p'.$this->db_pass.' '.$this->db_name.' '.$table;
        if($opzioni['out']) {
          if(is_writable(dirname($opzioni['out']))) {
            $cmd .= ' >> '.$opzioni['out'];
            exec($cmd);
            Utility::pre('Backup eseguito sul file :'.$opzioni['out']);
          } else {
            Utility::pre('Il file di destinazione fornito non è scrivibile:'.$opzioni['out'], ['level' => 'error', 'dieAfterError' => true]);
          }
        } else {
          exec($cmd);
        }
      } else {
        $phpmethod = true;
      }
    } else {
      $phpmethod = true;
    }

    if($phpmethod) {

      if($opzioni['debug'] && !$opzioni['forcePhp']) {
        Utility::pre('mysqldump non trovato, procedo con metodo su base query php');
      }

      if($opzioni['out']) {
        file_put_contents($opzioni['out'], '-- File generato con php il '.date('d-m-Y H:i:s'."\n"));
      }

      $SQLString = '';

      $res = $this->doQuery('SHOW CREATE TABLE '.$table, false);
      $row = mysqli_fetch_row($res);
      if($opzioni['overwrite_table']) {
        $SQLString .= 'DROP TABLE IF EXISTS '.$table.';';
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
          $conf['db_host'] = $this->db_host;
          $conf['db_user'] = $this->db_user;
          $conf['db_pass'] = $this->db_pass;
          $conf['db_name'] = $opzioni['out'];
          $db2             = new Db(0, $conf);
          $db2->connect();
          $db2->doQuery($SQLString);
        } else {
          file_put_contents($opzioni['out'], $SQLString, FILE_APPEND);
        }
        $SQLString = '';
      }


      if($opzioni['dati']) {

        // clausola WHERE riempita con filtri richiesti
        $where = '';

        $sql = 'SELECT * FROM '.$row[0];

        // parte di ricerca
        if($opzioni['filters'] && !$opzioni['whereString']) {

          if($opzioni['debug']) {
            Utility::pre($opzioni['filters']);
          }

          $where_filter = [];
          foreach($opzioni['filters'] as $filterIter => $filter) {
            if(isset($filter['chiave']) && $filter['chiave'] && isset($filter['valore']) && isset($filter['operatore']) && in_array($filter['operatore'], $acceptedFilterOperator)) {
              if($filter['operatore'] == 'IN' || $filter['operatore'] == 'NOT IN') {
                $where_filter[] = $filter['chiave'].' '.$filter['operatore'].' '.$filter['valore'];
              } else {
                $where_filter[] = $filter['chiave'].' '.$filter['operatore'].' "'.mysqli_real_escape_string($db->db, $filter['valore']).'"';
              }
            } else {
              $str = 'Filter Errato:';
              $str .= "\n";
              $str .= print_r($filter, true);
              Utility::pre($str, ['level' => 'error', 'dieAfterError' => true]);
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
          Utility::pre($sql);
        }

        $res = $this->doQuery($sql, false);

        $iii = 0;

        $SQLString .= 'INSERT IGNORE INTO';
        $SQLString .= ' `'.$row[0].'` VALUES ';

        while($riga = mysqli_fetch_assoc($res)) {

          if($iii === 10000) {

            $SQLString .= ";\n";
            file_put_contents($opzioni['out'], $SQLString, FILE_APPEND);

            if(!$opzioni['overwrite_data']) {
              $SQLString = 'INSERT IGNORE INTO';
            } else {
              $SQLString = 'REPLACE INTO';
            }
            $SQLString .= ' `'.$row[0].'` VALUES (';

            $iii = 0;

          } else {
            if($iii !== 0) {
              $SQLString .= ', (';
            } else {
              $SQLString .= '(';
            }
          }

          $iii++;

          $fields = [];

          foreach($riga as $field => $value) {
            $fields[] = '"'.mysqli_real_escape_string($this->db, $value).'"';
          }

          $SQLString .= implode(',', $fields).')';

        }

        $SQLString .= ';';


      }

      if($opzioni['out']) {
        if($opzioni['outType'] == 'db') {
          $conf            = [];
          $conf['db_host'] = $this->db_host;
          $conf['db_user'] = $this->db_user;
          $conf['db_pass'] = $this->db_pass;
          $conf['db_name'] = $opzioni['out'];
          $db2             = new Db(0, $conf);
          $db2->connect();
          $db2->doQuery($SQLString);
        } else {
          file_put_contents($opzioni['out'], $SQLString, FILE_APPEND);
        }
      } else {
        return $SQLString;
      }


    }

  }

}