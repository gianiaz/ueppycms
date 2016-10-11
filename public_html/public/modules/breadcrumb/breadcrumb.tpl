<div id="breadcrumb">
  {foreach item=briciola name="bcIter" from=$widgetData}
    {if $smarty.foreach.bcIter.last}
      <strong>{$briciola.label}</strong>
    {else}
      <a href="{$briciola.url}">{$briciola.label}</a> &gt;
    {/if}
  {/foreach}
</div>