<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (26/05/16, 10.24)                                                                      **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
namespace ueppy\utils;

class Ftp {

  private $host;
  private $user;
  private $pass;
  private $port;
  private $conn_id;
  private $out;


  function __construct($host, $user, $pass, $port = 21, $out = true, $debug = false) {

    $this->host = $host;
    $this->user = $user;
    $this->pass = $pass;
    $this->port = $port;
    $this->out  = $out;
    if($debug) {
      $str = '';
      $str .= 'Host :'.$host.':'.$port;
      $str .= "\n";
      $str .= 'User :'.$user;
      $str .= "\n";
      $str .= 'Pass :'.$pass;
      Utility::pre($str);
    }
  }

  function connect() {

    $this->conn_id = @ftp_connect($this->host, $this->port);
    if(!$this->conn_id && $this->out) {
      die('Connessione all\'host '.$this->host.':'.$this->port + ' FALLITA');
    }

    return $this->conn_id;
  }

  function login() {

    $login_result = @ftp_login($this->conn_id, $this->user, $this->pass);
    if(!$login_result && $this->out) {
      die('Login fallito con lo username '.$this->user);
    }

    return $login_result;
  }

  function cd($dir) {

    $changed_dir = @ftp_chdir($this->conn_id, $dir);
    if(!$changed_dir && $this->out) {
      die('Cambio directory fallito ('.$dir.')');
    }

    return $changed_dir;
  }

  function file_exists($filename) {

    return (-1 != ftp_size($this->conn_id, $filename));
  }

  function rename($old_file, $new_file) {

    return ftp_rename($this->conn_id, $old_file, $new_file);
  }

  protected function stripEmpty($var) {

    if($var) return $var;
  }

  function getlist($dir, $recursive = 0) {

    $list = array_filter(ftp_rawlist($this->conn_id, $dir, $recursive), [$this, 'stripEmpty']);

    $directories = [];
    $files       = [];
    $directory   = $dir;
    foreach($list as $node) {
      $split = preg_split('[ ]', $node, 9, PREG_SPLIT_NO_EMPTY);
      // primo caso, sono nell'elenco e ho incontrato una directory
      if($split[0][0] == 'd') {
        if(!in_array($split[8], ['.', '..', 'cgi-bin'])) {
          $directories [] = $directory.'/'.$split[8];
        }
      } elseif($split[0][0] == '/') { // secondo caso, l'elenco è finito e ho incontrato una nuova directory di cui verrà mostrato l'elenco
        $directory = explode(':', $split[0]);
        $directory = array_shift($directory);
      } else { // terzo caso, sono in un elenco, e ho incontrato un file
        if(!in_array($split[8], ['.', '..'])) {
          $files[] = $directory.'/'.$split[8];
        }
      }
    }

    return [$directories, $files];
  }

  function deleteContent($directory) {

    list($directories, $files) = $this->getlist($directory, true);
    foreach($files as $file) {
      ftp_delete($this->conn_id, $file);
    }
    $directories = array_reverse($directories);
    foreach($directories as $dir) {
      if(!@ftp_rmdir($this->conn_id, $dir)) {
        Utility::pre($dir);
      }
    }
  }

  /**
   * Carica un file via ftp nella directory corrente (per spostarsi in una directory vedi metodo "cd")
   * @param  string $name Nome del file di destinazione
   * @param  string $from Percorso locale del file
   * @param  string $type Tipo di upload (default FTP_BINARY)
   * @return boolean      Risultato dell'operazione
   */
  function upload($name, $from, $type = FTP_BINARY) {

    $res = @ftp_put($this->conn_id, $name, $from, $type);
    if(!$res && $this->out) {
      die('Upload del file '.$from.' fallito');
    }

    return $res;
  }

  function close() {

    ftp_close($this->conn_id);
  }

  function mkdir($dir) {

    $res = @ftp_mkdir($this->conn_id, $dir);
    if(!$res && $this->out) {
      die('Creazione directory '.$dir.' fallita');
    }

    return $res;
  }

  function pwd() {

    return ftp_pwd($this->conn_id);
  }

  function file_get_contents($remote_file, $mode = FTP_BINARY, $resume_pos = null) {

    $pipes = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
    if($pipes === false) return false;
    if(!stream_set_blocking($pipes[1], 0)) {
      fclose($pipes[0]);
      fclose($pipes[1]);

      return false;
    }
    $fail = false;
    $data = '';
    if(is_null($resume_pos)) {
      $ret = ftp_nb_fget($this->conn_id, $pipes[0], $remote_file, $mode);
    } else {
      $ret = ftp_nb_fget($this->conn_id, $pipes[0], $remote_file, $mode, $resume_pos);
    }
    while($ret == FTP_MOREDATA) {
      while(!$fail && !feof($pipes[1])) {
        $r = fread($pipes[1], 8192);
        if($r === '') break;
        if($r === false) {
          $fail = true;
          break;
        }
        $data .= $r;
      }
      $ret = ftp_nb_continue($this->conn_id);
    }
    while(!$fail && !feof($pipes[1])) {
      $r = fread($pipes[1], 8192);
      if($r === '') break;
      if($r === false) {
        $fail = true;
        break;
      }
      $data .= $r;
    }
    fclose($pipes[0]);
    fclose($pipes[1]);
    if($fail || $ret != FTP_FINISHED) return false;

    return $data;
  }

}

?>