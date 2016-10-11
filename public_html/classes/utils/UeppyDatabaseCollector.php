<?php
namespace Ueppy\utils;

use DebugBar\DataCollector\AssetProvider;
use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;
use DebugBar\DebugBarException;

class UeppyDatabaseCollector extends DataCollector implements Renderable, AssetProvider {

  protected $smarty;

  public function __construct($smarty) {

    $this->smarty = $smarty;
  }

  public function collect() {

    $queries       = [];
    $totalExecTime = 0;
    foreach($this->smarty->queries as $q) {
      list($query, $duration) = $q;
      $queries[] = [
        'sql'          => $query,
        'duration'     => $duration,
        'duration_str' => $this->formatDuration($duration)
      ];
      $totalExecTime += $duration;
    }

    return [
      'nb_statements'            => count($queries),
      'accumulated_duration'     => $totalExecTime,
      'accumulated_duration_str' => $this->formatDuration($totalExecTime),
      'statements'               => $queries
    ];
  }

  public function getName() {

    return 'ueppydb';
  }

  public function getWidgets() {

    return [
      "database"       => [
        "icon"    => "arrow-right",
        "widget"  => "PhpDebugBar.Widgets.SQLQueriesWidget",
        "map"     => "ueppydb",
        "default" => "[]"
      ],
      "database:badge" => [
        "map"     => "ueppydb.nb_statements",
        "default" => 0
      ]
    ];
  }

  public function getAssets() {

    return [
      'css' => 'widgets/sqlqueries/widget.css',
      'js'  => 'widgets/sqlqueries/widget.js'
    ];
  }
}