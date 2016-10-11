<?php
/*****************/
/***ueppy3.1.02***/
/*****************/
/**  CHANGELOG  **/
/**************************************************************************************************/
/** v.3.1.02 (24/10/2013)                                                                        **/
/** - Implementata esportazione con risparmio sul numero di query inserite.                      **/
/**                                                                                              **/
/** v.3.1.01 (03/04/2013)                                                                        **/
/** - Aggiunta cancellazione della directory export prima di cominciare a scrivere,              **/
/**   per evitare strane sovrapposizioni di files a causa di precedenti esportazioni da altri    **/
/**   progettti.                                                                                 **/
/**                                                                                              **/
/** v.3.1.00 (03/04/2013)                                                                        **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
require_once('auth.php');
use Ueppy\core\Db;
use Ueppy\utils\Utility;

$acceptedFilterOperator = ['<', '>', '=', '<=', '>=', '!=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN', 'REGEXP', 'REGEXP BINARY'];
set_time_limit(600);
$db = new Db();
$db->connect();

function enclose($elem) {

  return '"'.addSlashes(trim($elem)).'"';
}

$query = "SHOW TABLES FROM ".DB_NAME;

if(isset($_GET['tables']) && $_GET['tables']) {

  $table = explode(',', $_GET['tables']);

  $tables = implode(',', array_map('enclose', $table));

  $query .= ' WHERE Tables_in_'.DB_NAME.' IN ('.$tables.')';

}

$tables = [];

$result = mysqli_query($db->db, $query);

Utility::emptydir(DOC_ROOT.REL_ROOT.UPLOAD.'export');
Utility::mkdirp(DOC_ROOT.REL_ROOT.UPLOAD.'export');

if($result) {

  while($row = mysqli_fetch_row($result)) {

    $opzioni                    = [];
    $opzioni['debug']           = 0;
    $opzioni['overwrite_table'] = 1;
    $opzioni['overwrite_data']  = 0;
    $opzioni['dati']            = 1;
    $opzioni['whereString']     = '';
    $opzioni['filters']         = [];
    $opzioni['out']             = DOC_ROOT.REL_ROOT.UPLOAD.'export/'.$row[0].'.sql';
    $opzioni['forcePhp']        = true;


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


        if(is_writable(dirname($opzioni['out']))) {
          $cmd = $mysqldump.$arguments.$where.' -u'.DB_USER.' -p'.DB_PASS.' '.DB_NAME.' '.$row[0].' >> '.$opzioni['out'];
          exec($cmd);
          Utility::pre('Backup eseguito sul file :'.$opzioni['out']);
        } else {
          Utility::pre('Il file di destinazione fornito non è scrivibile:'.$opzioni['out'], ['level' => 'error', 'dieAfterError' => true]);
        }
      } else {
        $phpmethod = true;
      }
    } else {
      $phpmethod = true;
    }

    if($phpmethod) {

      if($opzioni['debug']) {
        Utility::pre('mysqldump non trovato, procedo con metodo su base query php');
      }

      file_put_contents($opzioni['out'], '-- File generato con php il '.date('d-m-Y H:i:s'."\n"));

      $SQLString = '';

      $tablesToDump   = [];
      $tablesToDump[] = $row[0];

      foreach($tablesToDump as $tbl) {
        $res = $db->doQuery('SHOW CREATE TABLE '.$tbl, false);
        $row = mysqli_fetch_row($res);
        if($opzioni['overwrite_table']) {
          $SQLString .= 'DROP TABLE IF EXISTS '.$tbl.';';
          $SQLString .= "\n";
          $SQLString .= $row[1].';';
          $SQLString .= "\n";
          $SQLString .= "\n";
        } else {
          $SQLString .= str_replace('CREATE TABLE', 'CREATE TABLE IF NOT EXISTS', $row[1]).';'."\n\n";
        }
      }

      file_put_contents($opzioni['out'], $SQLString, FILE_APPEND);

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
            Utility::pre($opzioni['operatore']);
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

        $res = $db->doQuery($sql, false);

        $iii = 0;

        $SQLString = 'INSERT IGNORE INTO';
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
            $fields[] = '"'.mysqli_real_escape_string($db->db, $value).'"';
          }

          $SQLString .= implode(',', $fields).')';

        }

        $SQLString .= ';';

        file_put_contents($opzioni['out'], $SQLString, FILE_APPEND);

      }
      Utility::pre('Backup eseguito sul file :'.$opzioni['out']);
    }

  }

} else {
  Utility::pre('Errore nell\'estrazione delle tabelle');
}
?>