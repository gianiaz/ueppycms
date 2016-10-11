{*****************}
{***ueppy3.1.00***}
{*****************}
{**  CHANGELOG  **}
{**************************************************************************************************}
{** v.3.1.00 (01/01/2013)                                                                        **}
{** - Versione stabile                                                                           **}
{**                                                                                              **}
{**************************************************************************************************}
{** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **}
{** copyright: Ueppy s.r.l                                                                       **}
{**************************************************************************************************}
{* TEMPLATE INDEX *}
<script type="text/x-handlebars-template" id="index-template">

  <a href="#" id="toggleAllegati" class="btn btn-info btn-block">{getLang module="allegati" key=NASCONDI_ALLEGATI}</a>

  <div class="hideShowAllegati">

    <h4 class="allegatiTitle text-info">{getLang module="allegati" key="TITLE_ALLEGATI"}</h4>

    <div class="listContainer panel panel-default">
      <table class="allegati table table-striped table-responsive">
      </table>
    </div>
    <div class="text-info">{literal}{{count}}{/literal} {getLang module="allegati" key="CONTA_ALLEGATI"}</div>

  </div>
</script>

{* TEMPLATE ALLEGATO *}
<script type="text/x-handlebars-template" id="allegato-template">
  <td class="icon {literal}{{tipo}}{/literal}">
    <i class="fa fa-{literal}{{fa}}{/literal}"
       title="{getLang module="allegati" key="TIPO_FILE"} {literal}{{estensione}}{/literal}"></i>

    <div class="fileDetail">
      <div class="frame" style="background-image:url({literal}{{thumb}}{/literal});"></div>
      <div class="dataText">
        <div class="rowData">
          <span class="lbl">{getLang module="allegati" key="NOME"}</span>
          <span class="value">{literal}{{nomefile}}{/literal}</span>
        </div>
        <div class="rowData">
          <span class="lbl">{getLang module="allegati" key="TIME"}</span>
          <span class="value">{literal}{{time}}{/literal}</span>
        </div>
        <div class="rowData">
          <span class="value">{literal}{{{meta}}}{/literal}</span>
        </div>
      </div>
    </div>
  </td>
  <td class="nomefile">{literal}{{nomefile}}{/literal}</td>
  <td class="time">{literal}{{time}}{/literal}</td>
  <td class="editAllegato"><i class="fa fa-edit"></i></td>
  <td class="deleteAllegato"><i class="fa fa-trash"></i></td>
</script>

{* TEMPLATE ALLEGATO - SOLA LETTURA *}
<script type="text/x-handlebars-template" id="allegatoreadonly-template">

  <td class="icon {literal}{{tipo}}{/literal}">
    <i class="fa fa-{literal}{{fa}}{/literal}"
       title="{getLang module="allegati" key="TIPO_FILE"} {literal}{{estensione}}{/literal}"></i>
    <div class="fileDetail">
      <div class="frame" style="background-image:url({literal}{{thumb}}{/literal});"></div>
      <div class="dataText">
        <div class="rowData">
          <span class="lbl">{getLang module="allegati" key="NOME"}</span>
          <span class="value">{literal}{{nomefile}}{/literal}</span>
        </div>
        <div class="rowData">
          <span class="lbl">{getLang module="allegati" key="TIME"}</span>
          <span class="value">{literal}{{time}}{/literal}</span>
        </div>
        <div class="rowData">
          <span class="value">{literal}{{{meta}}}{/literal}</span>
        </div>
      </div>
    </div>
  </td>
  <td class="nomefile readonly">{literal}{{nomefile}}{/literal}</td>
  <td class="time readonly">{literal}{{time}}{/literal}</td>
</script>


{* TEMPLATE STATUS ZONE *}
<script type="text/x-handlebars-template" id="statuszone-template">
  <div class="fermo">
    {getLang module="allegati" key="FILE_IN_UPLOAD"}&nbsp;<span class="filesTotali"></span>
    <br/>

    <div class="percentualeHolder">
      <div class="percentuale"></div>
    </div>
  </div>
</script>

{* TEMPLATE DROPZONE *}
<script type="text/x-handlebars-template" id="dropzone-template">
  {getLang module="allegati" key="DROP_HERE"}
</script>

{* TEMPLATE DROPZONE - OLD BROWSER *}
<script type="text/x-handlebars-template" id="uploadzoneoldbrowser-template">
  {getLang module="allegati" key="CHOOSE_FILE"}
  <form name="oldbrowserupload" method="post" action="{literal}{{urlRoot}}{/literal}">
    <input type="hidden" name="_method" value="upload"/>
    <input type="hidden" name="id_genitore" value="{literal}{{id_genitoreModel}}{/literal}"/>
    <input type="hidden" name="genitore" value="{literal}{{genitoreModel}}{/literal}"/>
    <input type="file" name="file[]"/>
  </form>
</script>

{* TEMPLATE METADATA FILES *}
<script type="text/x-handlebars-template" id="modalefile-template">

  <div id="modaleFile" class="modal fade">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
          <h4 class="modal-title alert-info" style="background:none">{literal}{{TITLE}}{/literal}</h4>
        </div>
        <div class="modal-body">
          <form name="allegatoFileDetail" class="allegatoFileDetail">
            <div class="allegatiTabs">
              <ul class="nav nav-tabs" role="tablist">
                {foreach item=lang_estesa name="cicloLingue" key=sigla_lingua from=$lingue}
                  <li role="presentation"{if $smarty.foreach.cicloLingue.first} class="active"{/if}>
                    <a role="tab" data-toggle="tab" href="#allegati_{$sigla_lingua}">{$lang_estesa}</a>
                  </li>
                {/foreach}
              </ul>
              <div class="tab-content">
                {foreach item=lang_estesa name="cicloLingue" key=sigla_lingua from=$lingue}
                  <div role="tabpanel" class="tab-pane{if $smarty.foreach.cicloLingue.first} active{/if}"
                       id="allegati_{$sigla_lingua}">

                    <div class="form-group">
                      <i class="fa fa-question-circle cmsHelp" data-toggle="tooltip" data-placement="top"
                         data-title="{getLang module="allegati" key="TITLE_HELP"}"></i>
                      <label class="control-label req"
                             for="AllegatiTitle_{$sigla_lingua}">{getLang module="allegati" key="TITLE"}</label>
                      <input id="AllegatiTitle_{$sigla_lingua}" name="{$sigla_lingua}[title]"
                             value="{literal}{{{/literal}title.{$sigla_lingua}{literal}}}{/literal}" type="text"
                             required
                             class="form-control"/>
                    </div>

                  </div>
                {/foreach}
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn"
                  data-dismiss="modal">{literal}{{CLOSE}}{/literal}</button>
          <button id="saveFile" type="button" class="btn">{literal}{{SAVE}}{/literal}</button>
        </div>
      </div>
    </div>
  </div>

</script>

{* TEMPLATE METADATA IMMAGINI *}
<script type="text/x-handlebars-template" id="modaleimmagine-template">

  <div id="modaleImmagine" class="modal fade">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
          <h4 class="modal-title alert-info" style="background:none">{literal}{{TITLE}}{/literal}</h4>
        </div>
        <div class="modal-body">
          <form name="allegatoFileDetail" class="allegatoFileDetail">
            <input type="hidden" class="originale" value="{literal}{{originale}}{/literal}"/>
            <input type="hidden" class="originaleW" value="{literal}{{originaleW}}{/literal}"/>
            <input type="hidden" class="originaleH" value="{literal}{{originaleH}}{/literal}"/>

            <div class="controlliImmagine">
            </div>
            <div class="clearfix"></div>
            <div class="allegatiTabs">
              <ul class="nav nav-tabs" role="tablist">
                {foreach item=lang_estesa name="cicloLingue" key=sigla_lingua from=$lingue}
                  <li role="presentation"{if $smarty.foreach.cicloLingue.first} class="active"{/if}>
                    <a role="tab" data-toggle="tab" href="#allegati_{$sigla_lingua}">{$lang_estesa}</a>
                  </li>
                {/foreach}
              </ul>
              <div class="tab-content">
                {foreach item=lang_estesa name="cicloLingue" key=sigla_lingua from=$lingue}
                  <div role="tabpanel" class="tab-pane{if $smarty.foreach.cicloLingue.first} active{/if}"
                       id="allegati_{$sigla_lingua}">

                    {* alt *}
                    <div class="form-group">
                      <i class="fa fa-question-circle cmsHelp" data-toggle="tooltip" data-placement="top"
                         data-title="{getLang module="allegati" key="ALT_HELP"}"></i>
                      <label class="control-label req"
                             for="AllegatiAlt_{$sigla_lingua}">{getLang module="allegati" key="ALT"}</label>
                      <input id="AllegatiAlt_{$sigla_lingua}" name="{$sigla_lingua}[alt]"
                             value="{literal}{{{/literal}alt.{$sigla_lingua}{literal}}}{/literal}" type="text" required
                             class="form-control"/>
                    </div>

                    <div class="form-group">
                      <i class="fa fa-question-circle cmsHelp" data-toggle="tooltip" data-placement="top"
                         data-title="{getLang module="allegati" key="TITLE_HELP"}"></i>
                      <label class="control-label req"
                             for="AllegatiTitle_{$sigla_lingua}">{getLang module="allegati" key="TITLE"}</label>
                      <input id="AllegatiTitle_{$sigla_lingua}" name="{$sigla_lingua}[title]"
                             value="{literal}{{{/literal}title.{$sigla_lingua}{literal}}}{/literal}" type="text"
                             required
                             class="form-control"/>
                    </div>

                  </div>
                {/foreach}
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn"
                  data-dismiss="modal">{literal}{{CLOSE}}{/literal}</button>
          <button id="saveImmagine" type="button" class="btn">{literal}{{SAVE}}{/literal}</button>
        </div>
      </div>
    </div>
  </div>

</script>

{* TEMPLATE VERSIONE IMMAGINE *}
<script type="text/x-handlebars-template" id="immaginemodificabile-template">
  <div class="imgFrame">
    <img{literal}{{{style}}}{/literal}src="{literal}{{path}}{/literal}" />
  </div>
  <br/>
  <a href="{literal}{{path}}{/literal}"
     class="btn btn-default editImage">{getLang module="allegati" key="EDIT"} {literal}{{dimensioni}}{/literal}</a>
  <br/>
  <a data-clipboard-text="{literal}{{path_no_rand}}{/literal}" class="btn btn-default copyImageUrl" id="zc{literal}{{versione}}{/literal}">{getLang module="allegati" key="COPY_IMAGE_URL"}</a>
  <a data-clipboard-text="{literal}{{niceUrl}}{/literal}" class="btn btn-default copyImageUrl" id="zcN{literal}{{versione}}{/literal}">{getLang module="allegati" key="COPY_NICE_URL"}</a>
</script>

{* TEMPLATE MODALE FOTORITOCCO *}
<script type="text/x-handlebars-template" id="modaleImgEffects-template">
  <div id="modaleImmagineFX" class="modal  fade">
    <div class="modal-dialog modal-fotoRitocco">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
          <h4 class="modal-title alert-info" style="background:none">{literal}{{TITLE}}{/literal}</h4>
        </div>
        <div class="modal-body">
          {getLang module="allegati" key="IMMAGINE_RISULTATO"}
          <div class="risultato">
            <img style="margin-left:{literal}{{lResult}}px{/literal};
                margin-top:{literal}{{tResult}}px{/literal};
                height:{literal}{{hResult}}px{/literal};
                width:{literal}{{wResult}}px{/literal};"
                 src="{literal}{{result.path}}{/literal}"/>
          </div>
          {getLang module="allegati" key="IMMAGINE_ORIGINALE"}
          <div class="immagineDaLavorare">
            <img id="operaSuImmagine" style="margin-left:{literal}{{lOriginale}}px{/literal};
                margin-top:{literal}{{tOriginale}}px{/literal};
                height:{literal}{{hOriginale}}px{/literal};
                width:{literal}{{wOriginale}}px{/literal};"
                 src="{literal}{{originale.path}}{/literal}"/>
          </div>
          <a class="genera btn btn-primary">{getLang module="allegati" key="GENERA"}</a>

          <div class="fx btn-group" role="group" aria-label="...">
            <a class="seppia btn btn-default">{getLang module="allegati" key="SEPPIA"}</a>
            <a class="bn btn btn-default">{getLang module="allegati" key="BN"}</a>
            <a class="scatter btn btn-default">{getLang module="allegati" key="SCATTER"}</a>
            <a class="pixelate btn btn-default">{getLang module="allegati" key="PIXELATE"}</a>
            <a class="emboss btn btn-default">{getLang module="allegati" key="EMBOSS"}</a>
            <a class="negative btn btn-default">{getLang module="allegati" key="NEGATIVE"}</a>
            <a class="reflect btn btn-default">{getLang module="allegati" key="REFLECT"}</a>
            <a class="interlace btn btn-default">{getLang module="allegati" key="INTERLACE"}</a>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn"
                  data-dismiss="modal">{literal}{{CLOSE}}{/literal}</button>
        </div>
      </div>
    </div>
  </div>
</script>

{**}
<script type="text/x-handlebars-template" id="modaleImgEffects1-template">
</script>

<div id="allegati" data-id_genitore="{$Obj->additionalData.md5}" data-genitore="{$Obj->dataDescription.table}" data-upload_classico="{$UPLOADCLASSICO}" data-readonly="{$READONLY}" class="panel panel-default"></div>

