<?php
/*****************/
/***ueppy3.1.02***/
/*****************/
/**  CHANGELOG  **/
/**************************************************************************************************/
/** v.3.1.02 (31/07/2013)                                                                        **/
/** - Bugfix su data di esecuzione registrata.                                                   **/
/**                                                                                              **/
/** v.3.1.01 (08/07/2013)                                                                        **/
/** - Introdotta la possibilità di bloccare l'esecuzione da configurazione                       **/
/** - Suddivisa l'impostazione di debug dall'eventuale flag per stampare output                  **/
/**                                                                                              **/
/** v.3.1.00 (01/01/2013)                                                                        **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/

$module_name       = basename(__FILE__);
$execute           = 1;
$debug             = 0;
$debug_out         = 1;
$CRONTAB_FREQUENCY = 1800;

// questo passa sopra ad eventuali impostazioni precedenti.
if($globalDebug) {
  $debug = 1;
}

$debugString = '';

$start = microtime(true);

if($debug_out) {
  $debugString .= "\n";
  $debugString .= $indent.'------ '.$module_name.' - INIZIO ------';
  $debugString .= "\n";
  $debugString .= "\n";
  $indent = "\t\t";
}

if($execute) {


  if(!defined('SET_PAGINE_CRONTAB_FREQUENCY')) {
    $CRONTAB_FREQUENCY = 3600;
  } else {
    $CRONTAB_FREQUENCY = 60 * SET_PAGINE_CRONTAB_FREQUENCY;
  }

  // controllo l'ultima esecuzione, se non è stato eseguito nel tempo previsto rieseguo.

  $options                  = array();
  $options['tableFilename'] = 'registro_crontab';

  $last_execution = new Dba($options);

  $filters                    = array();

  $filter_record              = array();
  $filter_record['chiave']    = 'nome_modulo';
  $filter_record['operatore'] = '=';
  $filter_record['valore']    = $module_name;
  $filters[]                  = $filter_record;

  $opts                 = array();
  $opts['start']        = '0';
  $opts['quanti']       = '1';
  $opts['countOnly']    = false;
  $opts['filters']      = $filters;
  $opts['operatore']    = 'AND';

  $list = $last_execution->getlist($opts);

  if(!count($list)) {
    $options                  = array();
    $options['tableFilename'] = 'registro_crontab';
    $last_execution                = new Dba($options);
    $last_execution->nome_modulo   = $module_name;
    $last_execution->last_executed = 0;
  } else {
    $last_execution = $list[0];
  }

  if($debug_out) {
    $debugString .= $indent.'Ultima esecuzione: ';
    if($last_execution->fields['last_executed']) {
      $debugString .= date('d/m/Y', $last_execution->fields['last_executed']).' alle ore '.date('H:i:s', $last_execution->fields['last_executed']);
    } else {
      $debugString .= ' MAI';
    }
    $debugString .= "\n";
    $debugString .= "\n";
  }

  if(($CRONTAB_FREQUENCY && $last_execution->fields['last_executed'] < time()-$CRONTAB_FREQUENCY) || $debug) {

    $last_execution->last_executed = mktime(date('H'), date('i'), 0, date('m'), date('d'), date('Y'));

    $last_execution->save();

    $query = "SELECT COUNT(id) FROM menu WHERE attivo = -1 AND pubdate <= '".date('Y-m-d')."'";

    require_once(CLASSES.'Db.Class.php');

    $db = new Db();
    $db->connect();
    $res = $db->doQuery($query);
    $row = mysqli_fetch_row($res);

    if($row[0]) {
      $LOGGER->addLine(array('text' => 'Attivate '.$row[0].' pagine schedulate', 'azione' => 'CRON:'.strtoupper($module_name)));
    }

    $query = "UPDATE menu SET attivo = 1 WHERE attivo = -1 AND pubdate <= '".date('Y-m-d')."'";

    if($debug_out) {
      $debugString .= $indent.'Lancio la query: ';
      $debugString .= "\n";
      $debugString .= "\n";
      $debugString .= $indent.$query;
      $debugString .= "\n";
      $debugString .= "\n";
    }

    require_once(CLASSES.'Db.Class.php');

    $db = new Db();
    $db->connect();
    $db->doQuery($query);

  }

} else {
  if($debug_out) {
    $debugString .= $indent.'Non eseguo, valore di execute impostato a 0';
    $debugString .= "\n";
  }
}

$end = microtime(true);

if($debug_out) {

  $tempoEsecuzione = ($end - $start);
  if($tempoEsecuzione > 0.5) {
    $debugString .= "\n";
    $debugString .= $indent."TEMPO DI ESECUZIONE: ".round($tempoEsecuzione, 2);
  }
  $indent = "\t";
  $debugString .= "\n";
  $debugString .= $indent.'------ '.$module_name.' - FINE ------';
  $debugString .= "\n";
  $debugString .= "\n";

  echo $debugString;

}
?>
