<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (13/07/16, 10.05)                                                                     **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
use Ueppy\utils\Utility;
use Ueppy\utils\Time;

$doCache = true;

if($operator) {
  if(defined('SET_GOOGLE_ANALYTICS_PROFILE_ID') && SET_GOOGLE_ANALYTICS_PROFILE_ID) {

    if(isset($_POST['periodo']) && preg_match_all('/^([\d]{4})\-([\d]{2})$/', $_POST['periodo'], $m)) {

      list($anno, $mese) = explode('-', $_POST['periodo']);

      $t_start = new \Ueppy\utils\Time($_POST['periodo'].'-01');

      $startDate = $t_start->format('Y-m-d');

      $cacheFile     = DOC_ROOT.REL_ROOT.UPLOAD.'cache/stats.'.$_POST['periodo'].'.json';
      $cacheLifeTime = 8640000; // 100 giorni, come dire che non viene mai aggiornato

      if($anno == date('Y') && $mese == date('m')) {
        // è il mese attuale, la data di fine è ieri.
        $t_end         = new \Ueppy\utils\Time(strtotime('-1day'));
        $endDate       = $t_end->format('Y-m-d');
        $cacheFile     = DOC_ROOT.REL_ROOT.UPLOAD.'cache/stats.daily.json';
        $cacheLifeTime = 86400;
      } else {
        $t_end   = new Time(mktime(0, 0, 0, $mese, date('t', strtotime($startDate)), $anno));
        $endDate = date($anno.'-'.$mese.'-t', strtotime($startDate));
      }

      $ajaxReturn['start'] = $t_start->format();
      $ajaxReturn['end']   = $t_end->format();

      if(!$doCache || !file_exists($cacheFile) || filemtime($cacheFile) < time() - $cacheLifeTime) {

        $service_account_email = 'statistiche-cms-ueppy@ueppy-stats.iam.gserviceaccount.com';
        $key_file_location     = DOC_ROOT.REL_ROOT.'../ueppy-stats-39811a8013c7.json';

        putenv('GOOGLE_APPLICATION_CREDENTIALS='.$key_file_location);

        $client = new Google_Client();
        $client->useApplicationDefaultCredentials();
        $client->addScope(Google_Service_Analytics::ANALYTICS_READONLY);
        $client->setSubject($service_account_email);

        $service = new Google_Service_Analytics($client);

        $ids = 'ga:'.SET_GOOGLE_ANALYTICS_PROFILE_ID; //your id

        $data                                = [];
        $data                                = [];
        $data['trafficSources']              = [];
        $data['trafficSources']['keyword']   = [];
        $data['users']                       = [];
        $data['users']['totali']             = 0;
        $data['users']['nuovi']              = 0;
        $data['pageTracking']                = [];
        $data['platform']                    = [];
        $data['platform']['operatingSystem'] = [];
        $data['geo']                         = [];
        $data['geo']['country']              = [];

        $metrics = 'ga:organicSearches';

        $optParams = [
          'dimensions'  => 'ga:keyword',
          'max-results' => '6',
          'sort'        => '-ga:organicSearches'
        ];

        $results = $service->data_ga->get($ids, $startDate, $endDate, $metrics, $optParams);


        foreach($results['rows'] as $item) {
          if($item[1]) {
            $record                              = [];
            $record['visite']                    = $item[1];
            $record['chiave']                    = $item[0];
            $data['trafficSources']['keyword'][] = $record;
          }
        }

        $metrics = 'ga:users,ga:newUsers';

        $optParams = [
          'max-results' => '10',
          'sort'        => '-ga:users'
        ];

        $results = $service->data_ga->get($ids, $startDate, $endDate, $metrics);

        $data['users']           = [];
        $data['users']['totali'] = $results['totalsForAllResults']['ga:users'];
        $data['users']['nuovi']  = $results['totalsForAllResults']['ga:newUsers'];

        // Page Tracking
        $metrics   = 'ga:pageviews';
        $optParams = [
          'max-results' => '12',
          'sort'        => '-ga:pageviews',
          'dimensions'  => 'ga:pagePath'
        ];

        $results = $service->data_ga->get($ids, $startDate, $endDate, $metrics, $optParams);


        foreach($results['rows'] as $item) {
          if($item[1]) {
            $record                 = [];
            $record['path']         = $item[0];
            $record['views']        = $item[1];
            $data['pageTracking'][] = $record;
          }
        }

        // Platforms
        $metrics   = 'ga:organicSearches';
        $optParams = [
          'max-results' => '10',
          'sort'        => '-ga:organicSearches',
          'dimensions'  => 'ga:operatingSystem'
        ];

        $results = $service->data_ga->get($ids, $startDate, $endDate, $metrics, $optParams);

        foreach($results['rows'] as $item) {
          if($item[1]) {
            $record                                 = [];
            $record['label']                        = $item[0];
            $record['value']                        = $item[1];
            $data['platforms']['operatingSystem'][] = $record;
          }
        }

        // Platforms
        $metrics   = 'ga:organicSearches';
        $optParams = [
          'max-results' => '10',
          'sort'        => '-ga:organicSearches',
          'dimensions'  => 'ga:deviceCategory'
        ];
        $results   = $service->data_ga->get($ids, $startDate, $endDate, $metrics, $optParams);

        foreach($results['rows'] as $item) {
          if($item[1]) {
            $record                         = [];
            $record['label']                = $item[0];
            $record['value']                = $item[1];
            $data['platforms']['devices'][] = $record;
          }
        }

        $metrics   = 'ga:organicSearches';
        $optParams = [
          'max-results' => '20',
          'sort'        => '-ga:organicSearches',
          'dimensions'  => 'ga:country'
        ];

        $results = $service->data_ga->get($ids, $startDate, $endDate, $metrics, $optParams);

        $data['geo']['country'][] = ['Nazione', 'Visite'];

        foreach($results['rows'] as $item) {
          if($item[1] && $item[0] != '(not set)') {
            $data['geo']['country'][] = [$item[0], intval($item[1])];
          }
        }

        file_put_contents($cacheFile, json_encode($data));

      } else {
        $data = json_decode(file_get_contents($cacheFile), true);
      }

//      Utility::pre($data['geo']);

      $ajaxReturn['result'] = 1;
      $ajaxReturn['data']   = $data;

    } else {
      $ajaxReturn['result'] = 0;
      if(isset($operator) && $operator->isSuperAdmin()) {
        $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' ('.__FILE__.','.__LINE__.')';
      } else {
        $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' (Error: '.__LINE__.')';
      }
    }

  } else {
    $ajaxReturn['result'] = 0;
    if(isset($operator) && $operator->isSuperAdmin()) {
      $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' ('.__FILE__.','.__LINE__.')';
    } else {
      $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' (Error: '.__LINE__.')';
    }
  }
}