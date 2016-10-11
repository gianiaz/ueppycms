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

{* LISTA DEI RECORD PRESENTI *}
{if $act eq ""}
  <div class="dataTable_wrapper">
    <table class="table table-striped table-bordered table-hover" id="dataTable">
    </table>
  </div>
{/if}

{* FORM DI INSERIMENT/MODIFICA RECORD *}
{if $act eq "new"}
  <form id="ajaxForm" name="ajaxForm"
        action="{make_url params="cmd/$cmd/act/insert"}"
        method="post"
        enctype="multipart/form-data">

    <input type="hidden" id="id" name="id" value="{$Obj->fields.id}"/>
    <input type="hidden" id="chiaviDisponibili" value="{$Obj->fields.chiavi}"/>

    {if $operator->isGod()}
      {assign var=readonly value=0}
    {else}
      {assign var=readonly value=1}
    {/if}
    <div class="row">
      <div class="col-lg-5">
        {* NOME *}
        {ueppy_form_field readonly=$readonly required=false inp_id="nome" inp_name="nome"  etichetta="$module_name.NOME" help=true type="text" inp_value=$Obj->fields.nome}
      </div>
    </div>
    <div class="row">
      <div class="col-lg-5">
        {* DESCRIZIONE *}
        {ueppy_form_field readonly=$readonly required=false inp_id="descrizione" inp_name="descrizione"  etichetta="$module_name.DESCRIZIONE" help=true type="textarea" inp_value=$Obj->fields.descrizione}
      </div>
      {if $operator->isGod()}
        <div class="col-lg-7">
          {* CHIAVI *}
          {ueppy_form_field required=false inp_data_filter="[a-zA-Z,_]" inp_id="chiavi" inp_name="chiavi"  etichetta="$module_name.CHIAVI" help=true type="text" inp_value=$Obj->fields.chiavi}
        </div>
      {/if}
    </div>

    {* OGGETTO *}
    {ueppy_form_field required=true inp_id=oggetto inp_name=oggetto  etichetta="$module_name.OGGETTO" help=true type="text" inp_value="`$Obj->fields.oggetto`"}

    {* TESTO *}
    {ueppy_form_field inp_id=testo inp_class="mce" inp_name=testo etichetta="$module_name.BODY" help=true type="textarea" inp_value="`$Obj->additionalData.testo`"}

    {include file="generic/snippets/footer-buttons.tpl"}

  </form>
{/if}