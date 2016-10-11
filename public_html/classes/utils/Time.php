<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (13/04/16, 10.37)                                                                     **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
namespace Ueppy\utils;

use Ueppy\core\Traduzioni;

class Time {

  var $d = null;

  function __construct($date = null) {

    if($date) {

      if(is_a($date, 'DateTime')) {

        $this->d = $date;

      } else {
        if(is_numeric($date)) {
          return $this->fromTimeStamp($date);

        } elseif(is_string($date)) {

          // formato mysql datetime yyyy-mm-aa HH:MM:SS
          $re = '/^[\d]{4}\-[\d]{2}\-[\d]{2}\s{1}[\d]{2}:[\d]{2}:[\d]{2}$/';
          if(preg_match($re, $date)) {
            return $this->fromMySqlDateTime($date);
          }
          // formato data mysql yyyy-mm-aa
          $re = '/^[\d]{4}\-[\d]{2}\-[\d]{2}$/';
          if(preg_match($re, $date)) {
            return $this->fromMySqlDate($date);
          }
          // formato data italiano gg/mm/aaaa HH:SS
          $re = '/^[\d]{2}\/[\d]{2}\/[\d]{4}\s{1}[\d]{2}\:[\d]{2}$/';
          if(preg_match($re, $date)) {
            return $this->fromItaDateTime($date);
          }

          // formato data italiano gg/mm/aaaa
          $re = '/^[\d]{2}\/[\d]{2}\/[\d]{4}$/';
          if(preg_match($re, $date)) {
            return $this->fromItaDate($date);
          }
        }

      }

    } else {
      $this->d = new \DateTime();
    }

    return $this;

  }

  public function fromItaDateTime($itaDateTime) {

    $this->d = \DateTime::createFromFormat('d/m/Y H:i', $itaDateTime);

    return $this;

  }

  public function fromItaDate($itaDate) {

    $this->d = \DateTime::createFromFormat('d/m/Y', $itaDate);

    return $this;

  }

  public function fromMySqlDateTime($mysqlDateTime) {

    if($mysqlDateTime != '0000-00-00 00:00:00') {
      $this->d = \DateTime::createFromFormat('Y-m-d H:i:s', $mysqlDateTime);
    } else {
      $this->d = null;
    }


    return $this;

  }

  public function fromMySqlDate($mysqlDate) {

    $this->d = \DateTime::createFromFormat('Y-m-d', $mysqlDate);

    return $this;

  }

  public function fromTimeStamp($timeStamp) {

    $this->d = new \DateTime();
    $this->d->setTimestamp($timeStamp);

    return $this;

  }

  function toMySqlDateTime() {

    return $this->format('Y-m-d H:i:s');
  }

  function toMySqlDate() {

    return $this->format('Y-m-d');
  }

  function toTimeStamp() {

    return $this->d->getTimestamp();
  }

  public function modify($string) {

    $this->d->modify($string);
  }

  /*
   * %a	An abbreviated textual representation of the day	Sun through Sat
   * %A	A full textual representation of the daySunday through Saturday
   * %d	Two-digit day of the month (with leading zeros)	01 to 31
   * %e Day of the month
   * Month
   * %b	Abbreviated month name, based on the locale	Jan through Dec
   * %B	Full month name, based on the locale	January through December
   * %m	Two digit representation of the month	01 (for January) through 12 (for December)
   * %n	Int representation of the month	1 (for January) through 12 (for December)
   * %y	Two digit representation of the year	Example: 09 for 2009, 79 for 1979
   * %Y	Four digit representation for the year	Example: 2038
   * Time	---	---
   * %H	Two digit representation of the hour in 24-hour format	00 through 23
   * %k	Two digit representation of the hour in 24-hour format, without zeros preceding single digits	0 through 23
   * %M	Two digit representation of the minute	00 through 59
   * %S	Two digit representation of the second	00 through 59
   */

  public function format($format = 'd/m/Y') {

    if(preg_match('/%{1,}/', $format)) {

      $result = $format;

      preg_match_all('/%([a-zA-Z]{1})/', $format, $m);

      foreach($m[1] as $letter) {
        switch($letter) {
          case 'd':
            $result = str_replace('%d', $this->format('d'), $result);
            break;
          case 'e':
            $result = str_replace('%e', $this->format('j'), $result);
            break;
          case 'a':
            $result = str_replace('%a', Traduzioni::getLang('cal', 'GIORNOSHORT_'.$this->format('N')), $result);
            break;
          case 'A':
            $result = str_replace('%A', Traduzioni::getLang('cal', 'GIORNO_'.$this->format('N')), $result);
            break;
          case 'b':
            $result = str_replace('%b', Traduzioni::getLang('cal', 'MESESHORT_'.$this->format('n')), $result);
            break;
          case 'B':
            $result = str_replace('%B', Traduzioni::getLang('cal', 'MESE_'.$this->format('n')), $result);
            break;
          case 'm':
            $result = str_replace('%m', $this->format('m'), $result);
            break;
          case 'n':
            $result = str_replace('%n', $this->format('n'), $result);
            break;
          case 'y':
            $result = str_replace('%y', $this->format('y'), $result);
            break;
          case 'Y':
            $result = str_replace('%Y', $this->format('Y'), $result);
            break;
          case 'H':
            $result = str_replace('%H', $this->format('H'), $result);
            break;
          case 'k':
            $result = str_replace('%k', $this->format('G'), $result);
            break;
          case 'M':
            $result = str_replace('%M', $this->format('i'), $result);
            break;
          case 's':
            $result = str_replace('%s', $this->format('s'), $result);
            break;
        }
      }

      return $result;
    } else {
      if($this->d) {
        return $this->d->format($format);
      } else {
        return '-';
      }
    }

  }

  static function formatDays($days) {

    $anni   = false;
    $mesi   = false;
    $giorni = false;

    if($days >= 365) {
      $anni = round($days / 365);
      $days = $days % 365;
    }

    if($days >= 30) {
      $mesi = round($days / 30);
      $days = $days % 30;
    }

    if($days >= 1) {
      $giorni = $days;
      $days   = 0;
    }

    $str = [];
    if($anni) {
      if($anni == 1) {
        $str[] = $anni.' '.Traduzioni::getLang('cal', 'ANNO');
      } else {
        $str[] = $anni.' '.Traduzioni::getLang('cal', 'ANNI');
      }
    }
    if($mesi) {
      if($mesi == 1) {
        $str[] = $mesi.' '.Traduzioni::getLang('cal', 'MESE');
      } else {
        $str[] = $mesi.' '.Traduzioni::getLang('cal', 'MESI');
      }
    }
    if($giorni) {
      if($giorni == 1) {
        $str[] = $giorni.' '.Traduzioni::getLang('cal', 'GIORNO');
      } else {
        $str[] = $giorni.' '.Traduzioni::getLang('cal', 'GIORNI');
      }
    }

    return implode(', ', $str);


  }

}