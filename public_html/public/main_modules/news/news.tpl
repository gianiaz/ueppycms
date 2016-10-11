{if $act eq "list"}
  {if $widgetData.news}
    {foreach item=news from=$widgetData.news}
      <div class="post-preview">
        <a href="{$news.url}">
          <h2 class="post-title">
            {$news.titolo}
          </h2>
          <h3 class="post-subtitle">
            {$news.intro}
          </h3>
        </a>
      </div>
      <hr>
    {/foreach}
    {include file="common/paginatore.tpl"}
    </div>
    </div>
  {else}
    {* NON CI SONO NEWS *}
  {/if}

{/if}
{******************************}
{** ELENCO DELLE NEWS - FINE **}
{******************************}

{**********************************}
{** LETTURA DELLA NEWS - INIZIO  **}
{**********************************}
{if $act eq "read"}

  {* ELENCO IMMAGINI ALLEGATE *}
  {if $widgetData.allegati.immagini}
    <div id="galleriaPagina">
      <ul>
        {foreach item=img name="immagini" from=$widgetData.allegati.immagini}
          <li>
          {if $img.versioni.1}
          <a href="{$img.versioni.1.url}"{if $img.title} title="{$img.title}"{/if}>
            {/if}
            <img class="imgSld" src="{$img.versioni.0.url}" alt="{$img.alt}" />
            {if $img.versioni.1}
          </a>
          </li>
        {/if}
        {/foreach}
      </ul>
      <div id="galleriaPager"></div>
    </div>
  {/if}
  <span class="author">{$widgetData.autore}</span>
  , {$widgetData.data} {$widgetData.ora}

  {if $SETTINGS.ENABLE_CAT_NEWS}
    <p>
      {getLang module="news" key="CATEGORIE"}
      {foreach item=cat name=iterazioneCategorie from=$widgetData.categorie}
        <a href="{$cat.link}">{$cat.nome}</a>{if !$smarty.foreach.iterazioneCategorie.last}, {/if}
      {/foreach}
    </p>
  {/if}

  {if $widgetData.tags}
    <p>
      {getLang module="news" key="TAGS"}
      {foreach item=tag name=iterazioneTags from=$widgetData.tags}
        <a href="{$tag.link}">{$tag.tag}</a>{if !$smarty.foreach.iterazioneTags.last}, {/if}
      {/foreach}
    </p>
  {/if}
  {$widgetData.testo}


  {* ELENCO FILES ALLEGATI *}
  {if $widgetData.allegati.files}
    <div class="filesAllegati">
      {foreach item=file from=$news.allegati.files}
        <a href="{$file.path}" title="{$file.title}">
          {$file.nomefile}
        </a>
        ({$file.descrizione})
      {/foreach}
    </div>
  {/if}
  </div>
{/if}
