{***************}
{** v. 1.00   **}
{***************}
{** CHANGELOG **}
{**************************************************************************************************}
{** v.1.00 (20/05/2016)                                                                          **}
{** - Versione stabile                                                                           **}
{**                                                                                              **}
{**************************************************************************************************}
{** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **}
{** copyright: Ueppy s.r.l                                                                       **}
{**************************************************************************************************}

{* LISTA DEI RECORD PRESENTI *}
{if $act eq ""}
  <div class="dataTable_wrapper">
    <table class="table table-striped table-bordered table-hover" id="dataTable" data-commenti="{$SETTINGS.PAGINE_COMMENTI}">
    </table>
  </div>
{/if}

{* LISTA DEI RECORD PRESENTI *}
{if $act eq "settings"}
  <div class="dataTable_wrapper">
    <table class="table table-striped table-bordered table-hover" id="dataTable" data-commenti="{$SETTINGS.PAGINE_COMMENTI}">
    </table>
  </div>
  <div id="modal">
    <form id="ajaxForm2" name="ajaxForm2" method="post" action="{make_url params="cmd/vetrina/act/save_setting" lang=$ACTUAL_LANGUAGE}">
      <input type="hidden" name="id" id="id"/>

      {* GRUPPO *}
      {ueppy_form_field required=false inp_id="gruppo" inp_name="gruppo"  etichetta="$module_name.GRUPPO" help=true type="text" inp_value=$Obj->fields.gruppo}

      {* DIMENSIONI *}
      {ueppy_form_field required=false inp_id="dimensioni" inp_name="dimensioni"  etichetta="$module_name.DIMENSIONI" help=true type="text" inp_value=$Obj->fields.dimensioni}

    </form>
  </div>
{/if}

{if $act eq "new"}
  <form data-readonly="{$readonly}" id="ajaxForm" name="ajaxForm"
        action="{make_url params="cmd/$cmd/act/insert"}"
        method="post"
        enctype="multipart/form-data">

    <input type="hidden" id="id" name="id" value="{$Obj->fields.id}"/>

    <div class="row">

      <div class="col-lg-6">
        {* NOME *}
        {ueppy_form_field required=false inp_id="nome" inp_name="nome"  etichetta="$module_name.NOME" help=true type="text" inp_value=$Obj->fields.nome}
      </div>
      <div class="col-lg-2">
        {ueppy_form_field required=true inp_id=gruppo inp_name=gruppo etichetta="$module_name.GRUPPO" help=true type="text" inp_value="`$Obj->fields.gruppo`"}
      </div>

      <div class="col-lg-2">
        {* attivo *}
        {ueppy_form_field required=false inp_name=attivo inp_id=attivo etichetta="default.ATTIVA_SUBITO" help=true type="select" inp_options=$attivoOptions inp_value=$Obj->fields.attivo}
      </div>

    </div>

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
                  <div class="col-lg-12">
                    {* titolo *}
                    {ueppy_form_field required=false inp_id="titolo_$sigla_lingua" inp_name="$sigla_lingua[titolo]"  etichetta="$module_name.TITOLO" help=true type="text" inp_value="`$Obj->fields.$sigla_lingua.titolo`"}
                  </div>
                  <div class="col-lg-12">
                    {* sottotitolo *}
                    {ueppy_form_field required=false inp_id="sottotitolo_$sigla_lingua" inp_name="$sigla_lingua[sottotitolo]"  etichetta="$module_name.SOTTOTITOLO" help=true type="text" inp_value="`$Obj->fields.$sigla_lingua.sottotitolo`"}
                  </div>
                  <div class="col-lg-12">
                    {* url *}
                    {ueppy_form_field required=false inp_id="url_$sigla_lingua" inp_name="$sigla_lingua[url]"  etichetta="$module_name.URL" help=true type="text" inp_value="`$Obj->fields.$sigla_lingua.url`"}
                  </div>
                </div>
                <div class="col-lg-6">
                  {ueppy_form_field debug=0 help=true level=$operator->fields.level lbl_class="img_choice_label" etichetta="$module_name.IMG_DEFAULT" type="imgedit" inp_id="img_$sigla_lingua" inp_name="$sigla_lingua[img]" inp_alt_value=$Obj->fields.$sigla_lingua.img_alt inp_title_value="-1" inp_value=$Obj->fileData.img.$sigla_lingua.versioni.0.rel_path}
                </div>
              </div>


              {* testo *}
              {ueppy_form_field inp_class="mce" required=false inp_id="testo_$sigla_lingua" inp_name="$sigla_lingua[testo]"  etichetta="$module_name.TESTO" help=true type="textarea" inp_value="`$Obj->fields.$sigla_lingua.testo`"}


            </div>
          {/foreach}
        </div>
      </div>
    </div>

    {include file="generic/snippets/footer-buttons.tpl"}

  </form>
{/if}

{* ELENCO PER GESTIRE L'ORDINE DEL MENU *}
{if $act eq "sort"}
  <form name="ajaxForm" id="ajaxForm"
        action="{make_url params="cmd/$cmd/act/saveorder"}"
        method="post">
    <input type="hidden" id="neworder" name="neworder" value="{$ordine}"/>


    {foreach item=data from=$gruppi}
      <ul class="sort">
        {foreach item=item from=$data}
          <li data-id="{$item.id}">
            {$item.nome}
          </li>
        {/foreach}
      </ul>
    {/foreach}

    {include file="generic/snippets/footer-buttons.tpl"}
  </form>
{/if}
