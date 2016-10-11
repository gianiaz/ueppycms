{***************|
{** v.1.00    **|
{***************|
{** CHANGELOG **|
{**************************************************************************************************|
{** v.1.00 (05/07/16, 8.35)                                                                     **|
{**************************************************************************************************|
{** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **|
{** copyright: Ueppy s.r.l                                                                       **|
{**************************************************************************************************}
<div class="col-lg-{$widgetsData.rss.size}">
  <div class="panel panel-widget panel-primary">
    <div class="panel-heading">
      <i class="fa fa-fw{$widgetsData.rss.icon}"></i> {$widgetsData.rss.title}
    </div>
    <div class="panel-body panel-rss">
      <ul class="rss-list">
        {foreach item=news from=$widgetsData.rss.items}
          <li>
            {$news.data} - <a target="_blank" href="{$news.url}">{$news.titolo}</a>
            <p>{$news.descrizione}</p>
            <hr/>
          </li>
        {/foreach}
      </ul>
    </div>
  </div>
</div>