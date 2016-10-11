{***************|
{** v.1.00    **|
{***************|
{** CHANGELOG **|
{**************************************************************************************************|
{** v.1.00 (15/06/16, 18.04)                                                                     **|
{**************************************************************************************************|
{** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **|
{** copyright: Ueppy s.r.l                                                                       **|
{**************************************************************************************************}
{if $widgetData.title}<h2>{$widgetData.title}</h2>{/if}
{foreach item=diapositiva name="vetrinastatica" from=$widgetData.items}
  <div class="post-preview">
    <a href="{$diapositiva.url}">
      <h2 class="post-title">
        {$diapositiva.titolo}
      </h2>
      <h3 class="post-subtitle">
        {$diapositiva.testo}
      </h3>
    </a>
  </div>
  <hr>
{/foreach}
<div class="clrlf"></div>
