{***************|
{** v.1.00    **|
{***************|
{** CHANGELOG **|
{*************************************************************|
{** v.1.00 (03/11/2015 12.15)                               **|
{** - Versione stabile                                      **|
{**                                                         **|
{*************************************************************|
{** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com> **|
{** copyright: Ueppy s.r.l                                  **|
{*************************************************************}

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
    <input type="hidden" name="id" id="id" value="{$Obj->fields.id}"/>

    <div class="row">

      <div class="col-lg-2">
        {if $readonly}
          {* SIGLA *}
          {ueppy_form_field readonly=$readonly inp_id=sigla inp_class="restricted" inp_rel="[a-z]" inp_name=sigla inp_maxlength="2" etichetta="$module_name.SIGLA" help=true type="text" inp_value="`$Obj->fields.sigla`"}
        {else}
          {* sigla *}
          {ueppy_form_field required=false inp_name=sigla inp_id=sigla etichetta="$module_name.SIGLA" help=true type="select" inp_options=$isoNations inp_value=$Obj->fields.sigla}

        {/if}
      </div>

      <div class="col-lg-4">
        {* ESTESA *}
        {ueppy_form_field required=true inp_id=estesa inp_name=estesa  etichetta="$module_name.ESTESA" help=true type="text" inp_value="`$Obj->fields.estesa`"}
      </div>
    </div>

    <div class="row">
      {* CHECKBOX ATTIVAZIONE ADMIN *}
      <div class="col-lg-3">
        {* attivo_admin *}
        {ueppy_form_field required=false inp_name=attivo_admin inp_id=attivo_admin etichetta="$module_name.ATTIVO_ADMIN" help=true type="select" inp_options=$attivo_options inp_value=$Obj->fields.attivo_admin}
      </div>
      <div class="col-lg-3">
        {* attivo_pubblico *}
        {ueppy_form_field required=false inp_name=attivo inp_id=attivo etichetta="$module_name.ATTIVO_PUBBLICO" help=true type="select" inp_options=$attivo_options inp_value=$Obj->additionalData.attivo}
      </div>
    </div>

    <div class="row">
      <div class="col-lg-6">
        {* IMG 0*}
        {ueppy_form_field level=$operator->fields.level debug=0 help=true lbl_class="img_choice_label" etichetta="$module_name.IMG_DEFAULT" type="imgedit" inp_id="img0" inp_name=img0 inp_value="`$Obj->fields.fileData.img0.versioni.0.url`"}
      </div>
    </div>


    {include file="generic/snippets/footer-buttons.tpl"}
  </form>
{/if}

