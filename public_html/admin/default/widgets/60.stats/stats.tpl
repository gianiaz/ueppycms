{***************|
{** v.1.00    **|
{***************|
{** CHANGELOG **|
{**************************************************************************************************|
{** v.1.00 (12/07/16, 17.00)                                                                     **|
{**************************************************************************************************|
{** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **|
{** copyright: Ueppy s.r.l                                                                       **|
{**************************************************************************************************}

{if $widgetsData.stats}
<div class="col-lg-{$widgetsData.stats.size}" id="container-stats" data-periodo="{$widgetsData.stats.periodo.mese}">
  <div class="panel panel-widget panel-primary">
    <div class="panel-heading">
      <i class="fa fa-{$widgetsData.stats.icon}"></i> {$widgetsData.stats.title} (<span class="periodo">{$widgetsData.stats.periodo.start} - {$widgetsData.stats.periodo.end}</span>)
    </div>
    <div class="panel-body panel-stats">
      <div class="col-md-3" id="pageviews">
      </div>
      <div class="col-md-3">
        <div id="stats-utenti">
        </div>
        <div id="sistemioperativi">
        </div>
        <div id="devices">
        </div>
      </div>
      <div class="col-md-6">
        <div id="geo">
        </div>
        <div id="stats-chiavi">
        </div>
      </div>


    </div>

    <div class="panel-footer">
      <div class="row">
        <div class="col-lg-1"><a href="#" class="btn btn-primary prev-month"><i class="fa fa-caret-left"></i></a></div>
        <div class="col-lg-10" style="text-align:center">
          <div class="btn btn-default btn-display">{$widgetsData.stats.periodoString}</div>
        </div>
        <div class="col-lg-1 text-right"><a href="#" class="btn btn-primary next-month disabled"><i class="fa fa-caret-right"></i></a>
        </div>
      </div>
    </div>

  </div>
{/if}