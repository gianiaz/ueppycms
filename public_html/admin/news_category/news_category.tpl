{***************}
{** v.1.00    **}
{***************}
{** CHANGELOG **}
{**************************************************************************************************}
{** v.1.00 (03/05/2016)                                                                          **}
{** - Versione stabile                                                                           **}
{**                                                                                              **}
{**************************************************************************************************}
{** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **}
{** copyright: Ueppy s.r.l                                                                       **}
{**************************************************************************************************}

{* FORM DI INSERIMENT/MODIFICA RECORD *}
{if $act eq "new"}
  <form id="ajaxForm" name="ajaxForm"
        action="{make_url params="cmd/$cmd/act/insert"}"
        method="post"
        enctype="multipart/form-data">

    <input type="hidden" id="id" name="id" value="{$Obj->fields.id}"/>

    <div class="col-lg-3">
      {* attivo *}
      {ueppy_form_field required=false inp_name=attivo inp_id=attivo etichetta="default.ATTIVA_SUBITO" help=true type="select" inp_options=$attivoOptions inp_value=$Obj->fields.attivo}
    </div>

    {if $operator->isAdvanced()}
      <div class="col-lg-3">
        {* predefinita *}
        {ueppy_form_field required=false inp_name=predefinita inp_id=predefinita etichetta="$module_name.PREDEFINITA" help=true type="select" inp_options=$attivoOptions inp_value=$Obj->fields.predefinita}
      </div>
      <div class="col-lg-3">
        {ueppy_form_field required=false inp_name=template inp_id=template etichetta="$module_name.TEMPLATE" help=true type="select" inp_options=$files_di_template inp_value="`$Obj->fields.template`"}
      </div>
      <div class="col-lg-3">
        {if count($lista_gruppi)}
          {ueppy_form_field help=true force_container=true etichetta="$module_name.PERMESSI_GRUPPI" type="checkbox" inp_options=$lista_gruppi inp_value=$Obj->additionalData.auth}
        {/if}
      </div>
    {/if}

    <div class="clearfix"></div>

    <div class="panel with-nav-tabs panel-primary">
      <div class="panel-heading">
        <ul class="nav nav-tabs" role="tablist">
          {foreach item=lang_estesa key=sigla_lingua name="lingueIter" from=$lingue}
            <li{if $smarty.foreach.lingueIter.first} class="active"{/if}><a
                      href="#scheda_{$sigla_lingua}" data-toggle="tab"><span>{$lang_estesa}</span></a></li>
          {/foreach}
        </ul>
      </div>
      <div class="panel-body">
        <div class="tab-content">
          {foreach item=lang_estesa key=sigla_lingua name="lingueIter" from=$lingue}
            <div id="scheda_{$sigla_lingua}" class="tab-pane{if $smarty.foreach.lingueIter.first} active{/if}"
                 role="tabpanel">

              <div class="row">
                <div class="col-lg-6">
                  {* name *}
                  {ueppy_form_field required="false" inp_id="name_$sigla_lingua" inp_name="$sigla_lingua[name]"  etichetta="$module_name.NAME" help=true type="text" inp_value="`$Obj->fields.$sigla_lingua.name`"}
                </div>
              </div>

              {if $operator->isAdvanced()}
                <div class="row">
                  <div class="col-lg-4">
                    {* TITLE *}
                    {ueppy_form_field required=false readonly=$readonly inp_id="htmltitle_$sigla_lingua" inp_name="$sigla_lingua[htmltitle]" etichetta="default.HTMLTITLE" help=true type="text" inp_value="`$Obj->fields.$sigla_lingua.htmltitle`"}
                  </div>
                  <div class="col-lg-4">
                    {* DESCRIPTION *}
                    {ueppy_form_field required=false readonly=$readonly inp_id="description_$sigla_lingua" inp_name="$sigla_lingua[description]"  etichetta="default.DESCRIPTION" help=true type="text" inp_value="`$Obj->fields.$sigla_lingua.description`"}
                  </div>
                  {if $operator->isAdvanced()}
                    <div class="col-lg-4">
                      {* HREF *}
                      {ueppy_form_field readonly=$readonly required=false inp_id="href_$sigla_lingua" inp_class="restricted replacespace" inp_rel="[a-z0-9\-_]" inp_name="$sigla_lingua[href]"  etichetta="default.HREF" help=true type="text" inp_value="`$Obj->fields.$sigla_lingua.href`"}
                      <a class="lnkpg" href="{$links.$ACTUAL_LANGUAGE}"
                         target="_blank">{$links.$ACTUAL_LANGUAGE}</a>
                    </div>
                  {/if}
                </div>
              {/if}

              {* TESTO *}
              {ueppy_form_field readonly=$readonly required=false inp_id="testo_$sigla_lingua" inp_class="mce" inp_name="$sigla_lingua[testo]" etichetta="$module_name.TESTO" help=true type="textarea" inp_value="`$Obj->fields.$sigla_lingua.testo`"}


            </div>
          {/foreach}
        </div>
      </div>
    </div>
    {include file="generic/snippets/footer-buttons.tpl"}

  </form>
{/if}

{* LISTA DEI RECORD PRESENTI *}
{if $act eq ""}
  <div class="dataTable_wrapper">
    <table class="table table-striped table-bordered table-hover" id="dataTable">
    </table>
  </div>
{/if}

{* ELENCO PER GESTIRE L'ORDINE *}
{if $act eq "sort"}
  <form name="ajaxForm" id="ajaxForm"
        action="{make_url params="cmd/$cmd/act/savesort"}"
        method="post">
    <input type="hidden" id="neworder" name="neworder" value="{$ordine}"/>

    <ul class="sort">
      {foreach item=categoria key=id from=$list}
        <li data-id="{$id}">
          {$categoria}
        </li>
      {/foreach}
    </ul>
    {include file="generic/snippets/footer-buttons.tpl"}

  </form>
{/if}
