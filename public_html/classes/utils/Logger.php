<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (27/05/16, 15.29)                                                                      **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/

namespace Ueppy\utils;

use Ueppy\core\Dba;

class Logger extends Dba {

  private $doLog = true;

  function __construct($opts = null) {

    if(isset($opts['doLog'])) {
      $this->doLog = $opts['doLog'];
    }

    parent::__construct($opts);
  }

  function addLine($opts = null) {

    if($this->doLog) {

      $logOpts = ['autore' => false,
                  'pop'    => true];


      if($opts) {
        $logOpts = $this->array_replace_recursive($logOpts, $opts);
      }

      if($logOpts['text']) {

        $debug_backtrace = debug_backtrace();

        $bt = [];
        foreach($debug_backtrace as $hop) {
          $str = str_replace(DOC_ROOT.REL_ROOT, '', $hop['file']).' ('.$hop['line'].')';
          if(isset($hop['class'])) {
            $str .= ' - '.$hop['class'].'::'.$hop['function'];
          }
          $bt[] = $str;
        }

        $bt = array_reverse($bt);
        if($logOpts['pop']) {
          array_pop($bt);
        }

        $bt = implode("\n", $bt);

        $logger = new Logger($this->opts);

        $logger->file = $bt;

        if($logOpts['autore']) {
          $logger->autore = $logOpts['autore'];
        } else {
          if(isset($_SESSION['LOG_INFO']['UID']) && isset($GLOBALS['operator']) && isset($GLOBALS['operator']->fields['nomecompleto']) && $GLOBALS['operator']->fields['nomecompleto']) {
            $logger->autore = $GLOBALS['operator']->fields['nomecompleto'];
          } else {
            $logger->autore = 'UeppyCMS';
          }
        }
        $logger->text = $logOpts['text'];

        $opts                = [];
        $opts['forceInsert'] = true;
        $opts['debug']       = 0;
        $logger->save($opts);
      }

    }

  }

  function svuota() {

    $filename = 'logs.';

    $sql = 'SELECT created_at FROM logs ORDER by created_at ASC LIMIT 0, 1';

    $res = $this->doQuery($sql);

    if(mysqli_num_rows($res)) {

      $row = mysqli_fetch_row($res);

      $t = new Time($row[0]);

      $filename .= $t->format('dmy-His');

      $start = date('d/m/Y H:i:s');

      $sql = 'SELECT created_at FROM logs ORDER by created_at DESC LIMIT 0, 1';

      $res = $this->doQuery($sql);

      $row = mysqli_fetch_row($res);

      $t = new Time($row[0]);

      $filename .= '-'.$t->format('dmy-His');

      $end = date('d/m/Y H:i:s');

      $filenameTxt = $filename.'.txt';

      $sql = 'SELECT * FROM logs ORDER BY created_at ASC';

      $path = DOC_ROOT.REL_ROOT.'/../logs/';

      $path = realpath($path).'/';


      file_put_contents($path.$filenameTxt, '-- LOGS DAL '.$start.' AL '.$end.' --');

      $res = $this->doQuery($sql);

      while($row = mysqli_fetch_row($res)) {

        $t = new Time($row[4]);

        $riga = "\n--------------\n";
        $riga .= $t->format('d-m-Y H:i:s');
        $riga .= "\n";
        $riga .= $row[1];
        $riga .= "\n";
        $riga .= $row[2];
        $riga .= "\n";
        $riga .= $row[3];
        $riga .= "\n--------------\n";

        file_put_contents($path.$filenameTxt, $riga, FILE_APPEND);

      }

      $query = 'TRUNCATE '.$this->dataDescription['table'];
      $this->doQuery($query);

      // ora zippo il tutto
      $zip = new Zip();
      $zip->open($path.$filename.'.zip', \ZIPARCHIVE::CREATE);
      $zip->addEntryToZip($path.$filenameTxt, $path);
      $zip->close();

      unlink($path.$filenameTxt);

    }
  }

}


?>