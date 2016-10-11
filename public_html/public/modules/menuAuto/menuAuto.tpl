{if $widgetData.list}
  {if $widgetData.title}<h2>{if $widgetData.link}
    <a href="{$widgetData.link}">{/if}{$widgetData.title}{if $widgetData.link}</a>{/if}</h2>{/if}
  <ul class="nav navbar-nav navbar-right">
    {foreach item=nodo name="menu_ul" from=$widgetData.list}
    <li{if $nodo.classe} class="{$nodo.classe}"{/if}>
      {if $nodo.img}
        <a href="{$nodo.url}" title="{$nodo.dicitura}"><img src="{$nodo.img}" alt="{$nodo.dicitura}" /></a>
        </li>
      {else}
        <a href="{$nodo.url}" title="{$nodo.dicitura}">{$nodo.dicitura}</a>
      {/if}
      {if !$nodo.childs}
        </li>
      {/if}
      {if $nodo.childs}
        <ul class="sub-menu">
          {foreach item=child from=$nodo.childs}
            <li><a href="{$child.url}">{$child.dicitura}</a></li>
          {/foreach}
        </ul>
        </li>
      {/if}
    {/foreach}
  </ul>
{/if}