{*
<div class="upymsg alert alert-{$widgetData.messaggio.type}">
  <h2>{$widgetData.messaggio.title}</h2>

  <p>{$widgetData.messaggio.msg}</p>

  {if $widgetData.messaggio.backUrl}
    <br/>
    <a href="{$widgetData.messaggio.backUrl}">{$widgetData.messaggio.back}</a>
  {/if}
</div>
*}

<div id="loginFrm" class="areaRiservata">
  <h3>{$PAGE_TITLE}</h3>
  <div class="boxR box2">
    <h4>{$widgetData.messaggio.title}</h4>
    <p class="descLogin">
      {$widgetData.messaggio.msg}
    </p>
    {if $widgetData.messaggio.backUrl}
      <a href="{$widgetData.messaggio.backUrl}">{$widgetData.messaggio.back}</a>
    {/if}
  </div>
</div>
<div class="clearfix"></div>