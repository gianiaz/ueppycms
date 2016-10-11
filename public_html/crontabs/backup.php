<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (24/05/16, 17.29)                                                                      **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
use Ueppy\core\Backup;

$start = microtime(true);

// estraggo i backup da creare:

$filters = [];

$filter_record              = [];
$filter_record['chiave']    = 'cron';
$filter_record['operatore'] = '=';
$filter_record['valore']    = '1';

$filters[] = $filter_record;

// che abbia ora = * o ad adesso

$filter_record              = [];
$filter_record['chiave']    = 'cron_h';
$filter_record['operatore'] = 'IN';
$filter_record['valore']    = '("*","'.intval(date('H')).'")';

$filters[] = $filter_record;

// che abbia giorno = * o a oggi
$filter_record              = [];
$filter_record['chiave']    = 'cron_dom';
$filter_record['operatore'] = 'IN';
$filter_record['valore']    = '("*","'.intval(date('d')).'")';

$filters[] = $filter_record;

// che abbia giorno = * o a oggi
$filter_record              = [];
$filter_record['chiave']    = 'cron_dow';
$filter_record['operatore'] = 'IN';
$filter_record['valore']    = '("*","'.intval(date('N')).'")';

$filters[] = $filter_record;

$options                  = [];
$options['tableFilename'] = 'backup';

$b = new Backup($options);

$opts              = [];
$opts['filters']   = $filters;
$opts['operatore'] = 'AND';
$opts['debug']     = 0;

$backups = $b->getlist($opts);

foreach($backups as $b) {

  $LOGGER->addLine(['text' => 'Eseguo backup con nome:'.$b->fields['nome'], 'pop' => false]);

  $opzioni                    = [];
  $opzioni['filename_prefix'] = 'crontab-';

  $filename = $b->doBackup($opzioni);
  $b->sendMail($filename);
  $b->sendFtp($filename);

}
$end = microtime(true);
$LOGGER->addLine(['text' => 'Tempo di esecuzione:'.round(($end - $start), 4).'sec.', 'pop' => false]);