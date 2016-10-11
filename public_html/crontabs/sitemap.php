<?php
/*****************/
/***ueppy3.1.03***/
/*****************/
/**  CHANGELOG  **/
/**************************************************************************************************/
/** v.3.1.03 (25/03/2015)                                                                        **/
/** - Bugfix su siti multilingua.                                                                **/
/**                                                                                              **/
/** v.3.1.02 (31/07/2013)                                                                        **/
/** - Bugfix su data di esecuzione registrata.                                                   **/
/** - Bugfix, la frequenza di aggiornamento personalizzata faceva riferimento alla costante del  **/
/**   dello script di backup.                                                                    **/
/** - Aggiornata la frequenza di esecuzione di default a 10 gg                                   **/
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
$CRONTAB_FREQUENCY = 864000;

// questo passa sopra ad eventuali impostazioni precedenti.
if($globalDebug) {
  $debug = 1;
}

$start = microtime(true);

$debugString = '';

if($debug_out) {
  $debugString .= "\n";
  $debugString .= $indent.'------ '.$module_name.' - INIZIO ------';
  $debugString .= "\n";
  $debugString .= "\n";
  $indent = "\t\t";
}

if($execute) {

  if(defined('SET_SITEMAP_CRONTAB_FREQUENCY')) {
    $CRONTAB_FREQUENCY = 60 * SET_SITEMAP_CRONTAB_FREQUENCY;
  }

  // controllo l'ultima esecuzione, se non è stato eseguito nel tempo previsto rieseguo.

  $options                  = [];
  $options['tableFilename'] = 'registro_crontab';

  $last_execution = new Dba($options);

  $filters = [];

  $filter_record              = [];
  $filter_record['chiave']    = 'nome_modulo';
  $filter_record['operatore'] = '=';
  $filter_record['valore']    = $module_name;
  $filters[]                  = $filter_record;

  $opts              = [];
  $opts['start']     = '0';
  $opts['quanti']    = '1';
  $opts['countOnly'] = false;
  $opts['filters']   = $filters;
  $opts['operatore'] = 'AND';

  $list = $last_execution->getlist($opts);

  if(!count($list)) {
    $options                       = [];
    $options['tableFilename']      = 'registro_crontab';
    $last_execution                = new Dba($options);
    $last_execution->nome_modulo   = $module_name;
    $last_execution->last_executed = 0;
  } else {
    $last_execution = $list[0];
  }

  if(($CRONTAB_FREQUENCY && $last_execution->fields['last_executed'] < time() - $CRONTAB_FREQUENCY) || $debug) {

    $last_execution->last_executed = mktime(date('H'), date('i'), 0, date('m'), date('d'), date('Y'));

    $last_execution->save();
    if($debug_out) {
      $debugString .= $indent.'Ultima esecuzione: ';
      if($last_execution->fields['last_executed']) {
        $debugString .= date('d/m/Y', $last_execution->fields['last_executed']).' alle ore '.date('H:i:s', $last_execution->fields['last_executed']);
      } else {
        $debugString .= ' MAI';
      }
      $debugString .= "\n";
      $debugString .= "\n";

      require_once(CLASSES.'Db.Class.php');
      $file = DOC_ROOT.REL_ROOT.'sitemap.xml';

      $str = '<?xml version="1.0" encoding="UTF-8"?>';
      $str .= "\n";
      $str .= '<?xml-stylesheet type="text/xsl" href="sitemap.xsl"?>';
      $str .= "\n";
      $str .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
      $str .= "\n";
      file_put_contents($file, $str);

      $contaUrl = 0;

      function addSiteMapUrl($record, $file) {

        global $contaUrl;

        $str = "\t<url>\n";
        $str .= "\t\t<loc>".$record['loc']."</loc>\n";
        $str .= "\t\t<lastmod>".$record['lastmod']."</lastmod>\n";
        $str .= "\t\t<changefreq>".$record['changefreq']."</changefreq>\n";
        $str .= "\t\t<priority>".$record['priority']."</priority>\n";
        $str .= "\t</url>\n";

        $contaUrl++;

        file_put_contents($file, $str, FILE_APPEND);

      }

      // comincio con la creazione, prima le pagine principali

      require_once(CLASSES.'Utils.Class.php');
      require_once(CLASSES.'LinkManager.Class.php');

      /**
       * Gestore link, imposto i valori di default per la sezione admin,
       * cambierò solo il valore di extraparams nei diversi moduli a seconda dell'esigenza
       */
      $lm = new Linkmanager();

      $lm_opt                = [];
      $lm_opt['host']        = HOST;
      $lm_opt['root']        = REL_ROOT;
      $lm_opt['pagina']      = 'index.php';
      $lm_opt['extraparams'] = '';
      $lm_opt['lang']        = ACTUAL_LANGUAGE;


      // ESTRAGGO I MENU PRINCIPALI
      require_once(CLASSES.'Menu.Class.php');

      $filters = [];

      $filter_record              = [];
      $filter_record['chiave']    = 'level';
      $filter_record['operatore'] = '=';
      $filter_record['valore']    = '100';

      $filters[] = $filter_record;

      $filter_record              = [];
      $filter_record['chiave']    = 'nomefile';
      $filter_record['operatore'] = '!=';
      $filter_record['valore']    = 'pagina';

      $filters[] = $filter_record;

      $options                  = [];
      $options['tableFilename'] = 'menu';

      $Obj = new Dba($options);

      $opts                 = [];
      $opts['filters']      = $filters;
      $opts['forceAllLang'] = true;
      $opts['operatore']    = 'AND';

      $list = $Obj->getlist($opts);

      foreach($list as $Obj) {

        foreach($langs as $l) {

          $lm_opt['extraparams'] = 'cmd/'.$Obj->fields['nomefile'];
          $lm_opt['lang']        = $l;

          $record = [];

          $record['lastmod']    = date('Y-m-d', $Obj->fields['modified']);
          $record['loc']        = $lm->create($lm_opt);
          $record['changefreq'] = 'daily';
          $record['priority']   = '1.00';

          addSiteMapUrl($record, $file);

        }

      }


      // ESTRAGGO LE NEWS
      if(SET_ENABLE_CAT_NEWS) {

        foreach($langs as $l) {
          $lm_opt['lang'] = $l;

          // prima gli url delle categorie
          $sql = 'SELECT news_category_langs.href as href FROM news_category LEFT JOIN news_category_langs ON news_category.id = news_category_langs.news_category_id WHERE news_category_langs.lingua = "'.$l.'" AND news_category.attivo = 1';
          $db  = new Db();
          $res = $db->doQuery($sql);
          while($row = mysqli_fetch_assoc($res)) {
            $lm_opt['extraparams'] = 'cmd/news/';
            $lm_opt['extraparams'] .= 'cat/'.$row['href'];
            $record               = [];
            $record['lastmod']    = date('Y-m-d', $Obj->fields['modified']);
            $record['loc']        = $lm->create($lm_opt);
            $record['changefreq'] = 'monthly';
            $record['priority']   = '0.70';
            addSiteMapUrl($record, $file);
          }

          $sql = 'SELECT news_langs.news_id, news_langs.href, news_category_langs.href as cat FROM news LEFT JOIN news_langs ON news_langs.news_id = news.id AND news_langs.lingua = "'.$l.'" LEFT JOIN rel_news_category_news ON news.id = rel_news_category_news.id_news LEFT JOIN news_category_langs ON news_category_langs.news_category_id = rel_news_category_news.news_category_id AND news_category_langs.lingua = "'.$l.'" WHERE news.attivo = 1 AND news_langs.lingua_attiva = 1 AND rel_news_category_news.principale = 1';

          $db  = new Db();
          $res = $db->doQuery($sql);
          while($row = mysqli_fetch_assoc($res)) {
            if($row['news_id'] == 14) {
              $lm_opt['extraparams'] = 'cmd/news/';
              $lm_opt['extraparams'] .= 'act/read/href/'.$row['href'];
              if(SET_ENABLE_CAT_NEWS) {
                $lm_opt['extraparams'] .= '/cat/'.$row['cat'];
              }

              $record = [];

              $record['lastmod']    = date('Y-m-d', $Obj->fields['modified']);
              $record['loc']        = $lm->create($lm_opt);
              $record['changefreq'] = 'monthly';
              $record['priority']   = '0.70';
              addSiteMapUrl($record, $file);
            }
          }

        }
      } else {
        $sql = 'SELECT news_langs.href FROM news LEFT JOIN news_langs ON news_langs.news_id = news.id WHERE news_langs.lingua = "it" AND news.attivo = 1';
        $db  = new Db();
        $res = $db->doQuery($sql);
        while($row = mysqli_fetch_assoc($res)) {
          $lm_opt['extraparams'] = 'cmd/news/';
          $lm_opt['extraparams'] .= 'act/read/href/'.$row['href'];
          if(SET_ENABLE_CAT_NEWS) {
            $lm_opt['extraparams'] .= '/cat/'.$row['cat'];
          }

          $record = [];

          $record['lastmod']    = date('Y-m-d', $Obj->fields['modified']);
          $record['loc']        = $lm->create($lm_opt);
          $record['changefreq'] = 'monthly';
          $record['priority']   = '0.70';
          addSiteMapUrl($record, $file);
        }

      }


      // ESTRAGGO LE PAGINE ATTIVE
      require_once(CLASSES.'Menu.Class.php');

      $filters = [];

      $filter_record              = [];
      $filter_record['chiave']    = 'level';
      $filter_record['operatore'] = '>=';
      $filter_record['valore']    = '100';

      $filters[] = $filter_record;

      $filter_record              = [];
      $filter_record['chiave']    = 'nomefile';
      $filter_record['operatore'] = '=';
      $filter_record['valore']    = 'pagina';

      $filters[] = $filter_record;

      $filter_record              = [];
      $filter_record['chiave']    = 'attivo';
      $filter_record['operatore'] = '=';
      $filter_record['valore']    = '1';

      $filters[] = $filter_record;

      $options                  = [];
      $options['tableFilename'] = 'menu';

      $Obj = new Dba($options);

      $opts                 = [];
      $opts['filters']      = $filters;
      $opts['operatore']    = 'AND';
      $opts['forceAllLang'] = true;

      $list = $Obj->getlist($opts);

      foreach($list as $Obj) {

        foreach($langs as $l) {
          $lm_opt['lang']        = $l;
          $lm_opt['extraparams'] = 'cmd/'.$Obj->fields['nomefile'];
          $lm_opt['extraparams'] .= '/href/'.$Obj->fields[$l]['href'].'/parent/'.$Obj->fields['parent'];

          $record = [];

          $record['lastmod']    = date('Y-m-d', $Obj->fields['modified']);
          $record['loc']        = $lm->create($lm_opt);
          $record['changefreq'] = 'monthly';
          $record['priority']   = '0.60';

          addSiteMapUrl($record, $file);
        }

      }

      $debugString .= $indent.'Creata sitemap contenente '.$contaUrl.' urls'."\n";

      $str = '</urlset>';
      $str .= "\n";
      file_put_contents($file, $str, FILE_APPEND);

    }

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
    $debugString .= "\n";
  }

  $indent = "\t";

  $debugString .= "\n";
  $debugString .= $indent.'------ '.$module_name.' - FINE ------';
  $debugString .= "\n";
  $debugString .= "\n";

  echo $debugString;

}
?>