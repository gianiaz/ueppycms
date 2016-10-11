<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (24/05/16, 14.23)                                                                      **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
Namespace Ueppy\core;

use Ueppy\core\Dba;
use Ueppy\utils\Utility;
use Ueppy\utils\Zip;
use Ueppy\core\Ueppy;

class Backup extends Dba {

  function __construct($opts = null) {

    parent::__construct($opts);

    $this->fields['directories_all'] = 1;
    $this->fields['tabelle_all']     = 1;
    $this->fields['id']              = 0;

  }


  /**
   * Ridefinisce il metodo fillresults aggiungendo le proprietà relative alle immagini,
   * e alla categoria genitore
   *
   * @return array $this->lista_risultati
   */
  function fillresults() {

    $results = parent::fillresults();

    $getById = false;

    if(!is_array($results)) {
      $getById = true;
      $results = [$results];
    }


    $arr = [];


    foreach($results as $obj) {

      $obj->additionalData['profile']          = [];
      $obj->additionalData['profile']['email'] = false;
      $obj->additionalData['profile']['ftp']   = false;

      if($obj->fields['email']) {
        $options                  = [];
        $options['tableFilename'] = 'backup_data';

        $emailProfileObj = new Dba($options);

        $emailProfileObj                         = $emailProfileObj->getById($obj->fields['email']);
        $obj->additionalData['profile']['email'] = $emailProfileObj->fields['email'];
      }
      if($obj->fields['ftp']) {

        $options                  = [];
        $options['tableFilename'] = 'backup_data';

        $ftp_profile = new Dba($options);

        $ftp_profile = $ftp_profile->getById($obj->fields['ftp']);

        $obj->additionalData['profile']['ftp']             = [];
        $obj->additionalData['profile']['ftp']['ftp_host'] = $ftp_profile->fields['ftp_ip'];
        $obj->additionalData['profile']['ftp']['ftp_wd']   = $ftp_profile->fields['ftp_wd'];
        $obj->additionalData['profile']['ftp']['ftp_user'] = $ftp_profile->fields['ftp_user'];
        $obj->additionalData['profile']['ftp']['ftp_pwd']  = $ftp_profile->fields['ftp_pwd'];

      }

      if($obj->fields['tabelle'] == '*') {
        $obj->additionalData['tabelle_all'] = 1;
        $obj->additionalData['tabelle']     = [];
      } else {
        $obj->additionalData['tabelle']     = explode(',', $obj->fields['tabelle']);
        $obj->additionalData['tabelle_all'] = 0;
      }
      if($obj->fields['directories'] == '*') {
        $obj->additionalData['directories_all'] = 1;
        $obj->additionalData['directories']     = [];
      } else {
        $obj->additionalData['directories']     = explode(',', $obj->fields['directories']);
        $obj->additionalData['directories_all'] = 0;
      }

      $arr[] = $obj;

    }

    if($getById) {
      return $arr[0];
    } else {
      return $arr;
    }

  }

  function sendMail($file, $debug = 0) {

    if($this->fields['email']) {

      $opzioni                = [];
      $opzioni['emailCode']   = 'BACKUP';
      $opzioni['to']          = [$this->additionalData['profile']['email']];
      $opzioni['attachments'] = [$file];
      $opzioni['replace']     = ['NOME_BCK' => $this->fields['nome']];

      Ueppy::sendMail($opzioni);
    }

  }

  function sendFtp($file, $debug = 1) {

    if($this->fields['ftp']) {

      $ftp_host = '';
      $ftp_port = 21;

      if(strpos(':', $this->additionalData['profile']['ftp']['ftp_host'])) {
        list($ftp_host, $ftp_port) = explode(':', $this->additionalData['profile']['ftp']['ftp_host']);
      } else {
        $ftp_host = $this->additionalData['profile']['ftp']['ftp_host'];
      }

      $conn_id = ftp_connect($ftp_host, $ftp_port);

      if(!$conn_id) {
        UTility::pre('Connessione al server:'.$ftp_port.' alla porta '.$ftp_port.' fallita');
      } else {
        $login_result = ftp_login($conn_id, $this->additionalData['profile']['ftp']['ftp_user'], $this->additionalData['profile']['ftp']['ftp_pwd']);
        if(!$login_result) {
          UTility::pre('Login fallito con l\'utente '.$this->additionalData['profile']['ftp']['ftp_user']);
        } else {
          $res = ftp_put($conn_id, $this->additionalData['profile']['ftp']['ftp_wd'].basename($file), $file, FTP_BINARY);
          if(!$res) {
            UTility::pre('Upload Fallito');
          }
        }
        ftp_close($conn_id);
      }

    }

  }

  function doBackup($opts = null) {

    $opzioni                    = [];
    $opzioni['filename_prefix'] = '';

    if($opts) {
      $opzioni = $this->array_replace_recursive($opzioni, $opts);
    }

    $tabelle     = [];
    $directories = [];

    if($this->additionalData['tabelle_all']) {

      $tables = glob(TABLES_DIR.'*.table.php');

      $tabelle = [];

      foreach($tables as $tbl) {
        $tbl       = str_replace('.table.php', '', basename($tbl));
        $tabelle[] = $tbl;
      }

    } else {
      $tabelle = $this->additionalData['tabelle'];
    }

    if($this->additionalData['directories_all']) {

      $dirs = glob(DOC_ROOT.REL_ROOT.UPLOAD.'*', GLOB_ONLYDIR);
      foreach($dirs as $d) {
        $directories[] = basename($d);
      }

    } else {
      $directories = $this->additionalData['directories'];
    }

    $DIR = DOC_ROOT.REL_ROOT.BACKUP_DIR;

    $DIR .= $this->fields['id'].'/';

    $DIR .= 'temp/';

    if(is_dir($DIR)) {
      Utility::emptydir($DIR);
    }
    Utility::mkdirp($DIR);

    if(count($tabelle)) {

      if(!is_dir($DIR.'sql/')) {
        Utility::mkdirp($DIR.'sql/');
      }

      foreach($tabelle as $tbl) {

        $options                  = [];
        $options['tableFilename'] = $tbl;

        $dump = new Dba($options);

        $opzioniDump                    = [];
        $opzioniDump['debug']           = 0;
        $opzioniDump['out']             = $DIR.'sql/'.$tbl.'.dump';
        $opzioniDump['overwrite_table'] = 1;
        $opzioniDump['overwrite_data']  = 1;
        $opzioniDump['dati']            = 1;

        $dump->dump($opzioniDump);
      }

    }

    if(count($directories)) {
      if(!is_dir($DIR.'files/')) {
        Utility::mkdirp($DIR.'files/');
      }
      foreach($directories as $d) {
        Utility::copyRecursive(DOC_ROOT.REL_ROOT.UPLOAD.basename($d), $DIR.'files/'.basename($d));
      }
    }

    $zip = new Zip();

    $filename = DOC_ROOT.REL_ROOT.BACKUP_DIR.$this->fields['id'].'/'.$opzioni['filename_prefix'].date('d-m-Y_H-i').'.zip';

    $zip->open($filename, \ZipArchive::CREATE);

    if(is_dir($DIR.'files/')) {
      $zip->addDir($DIR.'files/', $DIR);
    }
    if(is_dir($DIR.'sql/')) {
      $zip->addDir($DIR.'sql/', $DIR);
    }

    $zip->close();

    return $filename;

  }

  function deleteArchive($nome) {

    $DIR = DOC_ROOT.REL_ROOT.BACKUP_DIR;

    $DIR .= $this->fields['id'].'/';

    $File = $DIR.basename($nome);

    if(file_exists($File)) {
      unlink($File);
    }

  }

  function restoreArchive($filename) {

    $DIR = DOC_ROOT.REL_ROOT.BACKUP_DIR;
    $DIR .= $this->fields['id'].'/';
    $filename = $DIR.basename($filename);

    if(file_exists($filename)) {

      $opts                    = [];
      $opts['filename_prefix'] = 'salvataggio-automatico-';
      $this->doBackup($opts);

      Utility::emptydir($DIR.'temp/');
      Utility::mkdirp($DIR.'temp/');

      $zip = new Zip();
      $zip->open($filename);
      $zip->extractToDir($DIR.'temp/');

      // recupero db
      if(is_dir($DIR.'temp/sql')) {
        $ser = glob($DIR.'temp/sql/*.dump');
        foreach($ser as $dump) {
          $options                  = [];
          $options['tableFilename'] = str_replace('.dump', '', basename($dump));
          $options['debug']         = 0;
          $dba                      = new Dba($options);
          $dba->processQueries(file_get_contents($dump));
        }
      }

      if(is_dir($DIR.'temp/files')) {
        $dirs = glob($DIR.'temp/files/*', GLOB_ONLYDIR);
        foreach($dirs as $d) {
          $FROM = $d;
          $TO   = DOC_ROOT.REL_ROOT.UPLOAD.basename($d);
          if(is_dir($TO)) {
            Utility::emptydir($TO, 0);
          }
          Utility::copyRecursive($FROM, $TO);
        }
      }

      /** RECUPERO FS - FINE **/

      return true;
    }
  }

  function cancellabile() {

    $DIR = DOC_ROOT.REL_ROOT.BACKUP_DIR;
    $DIR .= $this->fields['id'].'/';
    $zip = glob($DIR.'*.zip');

    if($zip && is_array($zip) && count($zip)) {
      return false;
    }

    return true;

  }
}