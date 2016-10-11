{if $act eq "read"}
  {if $widgetData.sottotitolo}<h2>{$widgetData.sottotitolo}</h2>{/if}

  {* ELENCO IMMAGINI ALLEGATE *}
  {if $widgetData.allegati.immagini}
    <div id="galleriaPagina">
      <ul>
        {foreach item=img name="immagini" from=$widgetData.allegati.immagini}
          <li>
          {if $img.versioni.1}
          <a href="{$img.versioni.1.path}"{if $img.title} title="{$img.title}"{/if}>
            {/if}
            <img class="imgSld" src="{$img.versioni.0.url}" alt="{$img.alt}"/>
            {if $img.versioni.1}
          </a>
          </li>
        {/if}
        {/foreach}
      </ul>
      <div id="galleriaPager"></div>
    </div>
  {/if}

  {$widgetData.testo}

  {* ELENCO FILES ALLEGATI *}
  {if $widgetData.allegati.files}
    <div class="filesAllegati">
      {foreach item=file from=$widgetData.allegati.files}
        <a href="{$file.path}" title="{$file.title}">
          {$file.nomefile}
        </a>
        ({$file.descrizione})
      {/foreach}
    </div>
  {/if}

  {if $SETTINGS.PAGINA_COME_CAT}
    {if $widgetData.pagine_figlie}
      {foreach item=figlia from=$widgetData.pagine_figlie}
        <div class="row sottopagina">
          <div class="col-md-2 preview">
            <img class="img-responsive img-thumbnail" src="{$figlia.img.url}" alt="{$figlia.img.alt}"{if $figla.img.title} title="{$figlia.img_title}"{/if}/>
          </div>
          <div class="col-md-10 testo">
            <h4>{$figlia.titolo}</h4>
            <p>{$figlia.intro}</p>
            <a href="{$figlia.link}"
               title="{getLang module="pagina" key="VAI_A"} {$figla.titolo_breve}">{getLang module="default" key="CONTINUA"}</a>
          </div>
        </div>
      {/foreach}
      {include file="common/paginatore.tpl"}
    {/if}
  {/if}
{/if}

{if $act eq "list"}
  {if $widgetData.pagine_figlie}
    {foreach item=figlia from=$widgetData.pagine_figlie}
      <div class="row sottopagina">
        <div class="col-md-2 preview">
          <img class="img-responsive img-thumbnail" src="{$figlia.img.url}" alt="{$figlia.img.alt}"{if $figla.img.title} title="{$figlia.img_title}"{/if}/>
        </div>
        <div class="col-md-10 testo">
          <h4>{$figlia.titolo}</h4>
          <p>{$figlia.intro}</p>
          <a href="{$figlia.url}"
             title="{getLang module="pagina" key="VAI_A"} {$figla.titolo_breve}">{getLang module="default" key="CONTINUA"}</a>
        </div>
      </div>
    {/foreach}
    {include file="common/paginatore.tpl"}
  {/if}
{/if}