<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/************************************************************************************************/
/** v.1.00 (11/05/2016)                                                                        **/
/** - Versione stabile                                                                         **/
/**                                                                                            **/
/************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                    **/
/** copyright: Ueppy s.r.l                                                                     **/
/************************************************************************************************/
namespace Ueppy\utils;

class Zip extends \ZipArchive {

  var $list_extracted = [];

  var $file_count = 0;

  function listdir($path, $exclude_dir = []) {

    $content    = [];
    $conta_file = count(Utility::glob2($path, '*'));
    if($conta_file == 0) {
      $content[] = str_replace('\\', '/', $path);

      return $content;
    } else {
      $dir = dir($path);
      while($item = $dir->read()) {
        if(in_array($item, [".", ".."]))
          continue;
        $file = realpath($path."/".$item);
        if(is_dir($file)) {
          $content = array_merge($content, $this->listdir($file, $exclude_dir));
        } else {
          $file    = str_replace('\\', '/', $file);
          $escluso = false;
          foreach($exclude_dir as $dir_esclusa) {
            if(strpos(dirname($file), $dir_esclusa) === 0) {
              $escluso = true;
            }
          }
          if(!$escluso) {
            $content[] = $file;
          }
        }
      }

      return $content;
    }
  }

  function open($filename, $param = '') {

    $this->file_name = $filename;
    if($param) {
      return parent::open($filename, $param);
    } else {
      return parent::open($filename);
    }
  }


  function addEntryToZip($path, $base = '', $create_empty_dirs = 1) {

    if(is_dir($path) && $create_empty_dirs) {
      $this->addEmptyDir(str_replace($base, '', $path));
    } elseif(is_file($path)) {
      $contents = file_get_contents($path);
      if($contents === false) {
        return false;
      }

      return $this->addFromString(str_replace($base, '', $path), $contents);
    }
  }

  function addDir($dir, $base = "", $exclude_dir = [], $create_empty_dirs = 1) {

    if(!file_exists($dir) || !is_dir($dir)) {
      throw new Exception($dir." non è una directory!");
    }
    $list = $this->listdir($dir, $exclude_dir);
    foreach($list as $file) {
      $this->addEntryToZip($file, $base, $create_empty_dirs);
      $this->file_count++;
    }

  }

  function extractToDir($dir, $remove = 0, $debug = 0) {

    $list_extracted = [];
    if($debug) {
      Utility::pre($dir);
    }
    for($i = 0; $i < $this->numFiles; $i++) {
      $file = $this->statIndex($i);
      if($file['size']) {
        if($debug) {
          Utility::pre($file['name']);
        }
        $this->extractTo($dir, $file['name']);
        $list_extracted[] = $file['name'];
      }
    }

    if($remove) {
      $file_to_remove = $this->file_name;
      $this->close();
      unlink($file_to_remove);
    }

    return $list_extracted;
  }


}

?>