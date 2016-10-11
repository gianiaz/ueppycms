{if $paginatore && $paginatore.numero_pagine > 1}
  <ul id="paginazione" class="paginatore">
    <li class="{if !$paginatore.prev}disabled {/if}arrow">
      <a href="{if $paginatore.prev}{$paginatore.prev}{else}#{/if}"><span class="glyphicon glyphicon-chevron-left"></span></a>
    </li>
    {foreach item="url" key="page" from=$paginatore.pagine}
      <li{if $page eq $paginatore.pag} class="thisitem"{/if}><a href="{$url}">{$page}</a></li>
    {/foreach}
    <li class="arrow {if !$paginatore.next} disabled{/if}">
      <a href="{if $paginatore.next}{$paginatore.next}{else}#{/if}" aria-label="{getLang module=default key=PAGINA_SUCCESSIVA}">
        <span class="glyphicon glyphicon-chevron-right"></span>
      </a>
    </li>
  </ul>
{/if}