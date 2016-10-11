{***************|
{*** v. 1.00 ***|
{***************|
{** CHANGELOG **|
{**************************************************************************************************|
{** v. 1.00                                                                                      **|
{** - Versione stabile                                                                           **|
{**                                                                                              **|
{**************************************************************************************************|
{** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **|
{** copyright: Ueppy s.r.l                                                                       **|
{**************************************************************************************************}

{* LISTA DEI RECORD PRESENTI *}
{if $act eq ""}
  <div class="dataTable_wrapper">
    <table class="table table-striped table-bordered table-hover" id="dataTable">
    </table>
  </div>
{/if}

{*------------------------------------*}
{* FORM DI INSERIMENT/MODIFICA RECORD *}
{*------------------------------------*}
{if $act eq "new"}
  <form name="ajaxForm" id="ajaxForm" method="post"
        action="{make_url params="cmd/$cmd/act/insert"}">
    <input id="id" type="hidden" name="id" value="{$Obj->fields.id}"/>

    {if $Obj->fields.id}
      {assign var="readonlyuser" value="1"}
      {assign var="requireduser" value="0"}
    {else}
      {assign var="readonlyuser" value="0"}
      {assign var="requireduser" value="1"}
    {/if}

    {if $operator->id eq $Obj->fields.id}
      {assign var="readonly" value="1"}
    {else}
      {assign var="readonly" value="0"}
    {/if}

    <div class="row">
      <div class="col-lg-4">
        {* NOMECOMPLETO *}
        {ueppy_form_field required=true inp_id=nomecompleto inp_name=nomecompleto  etichetta="$module_name.NOMECOMPLETO" help=true type="text" inp_value="`$Obj->fields.nomecompleto`"}
      </div>
      <div class="col-lg-4">
        {* EMAIL *}
        {ueppy_form_field required=true inp_id=email inp_name=email  etichetta="$module_name.EMAIL" help=true type="text" inp_value="`$Obj->fields.email`"}
      </div>
      <div class="col-lg-2">
        {* USERNAME *}
        {ueppy_form_field required=$requireduser readonly=$readonlyuser inp_id=username inp_name=username  etichetta="$module_name.USERNAME" help=true type="text" inp_value="`$Obj->fields.username`"}
      </div>
      <div class="col-lg-2">
        {* attivo *}
        {ueppy_form_field required=false inp_name=attivo inp_id=attivo etichetta="default.ATTIVA_SUBITO" help=true type="select" inp_options=$attivoOptions inp_value=$Obj->fields.attivo}
      </div>
    </div>

    {ueppy_form_field debug=0 help=true level=$operator->fields.level lbl_class="img_choice_label" etichetta="$module_name.AVATAR" type="imgedit" inp_id="avatar" inp_name=avatar inp_alt_value="-1" inp_title_value="-1" inp_value="`$Obj->fields.fileData.avatar.versioni.0.url`"}

    <div class="row">

      <div class="col-lg-3">
        {* SELECT GRUPPO *}
        {ueppy_form_field debug=0 readonly=$readonly required=true inp_name=gruppi_id inp_id=gruppi_id etichetta="$module_name.GRUPPO" help=true type="select" inp_options=$lista_gruppi_options inp_value=$Obj->fields.gruppi_id}
      </div>
      {if $operator->fields.super_admin}
        <div class="col-lg-3">
          {* super_admin *}
          {ueppy_form_field required=false inp_name=super_admin inp_id=super_admin etichetta="$module_name.SUPER_ADMIN" help=true type="select" inp_options=$super_adminOptions inp_value=$Obj->fields.super_admin}
        </div>
      {/if}

      <div class="col-lg-3">
        {* SELECT LEVEL *}
        {ueppy_form_field readonly=$readonly required=true inp_name=level inp_id=level etichetta="$module_name.LEVEL" help=true type="select" inp_options=$levels inp_value=$Obj->fields.level}

      </div>
    </div>

    <div class="row">
      <div class="col-lg-4">
        {* PASSWORD *}
        {ueppy_form_field required=$requireduser inp_id=passwd inp_name=passwd  etichetta="$module_name.PASSWD" help=true type="password" inp_value=""}

      </div>
      <div class="col-lg-4">
        {* PASSWORD *}
        {ueppy_form_field required=$requireduser inp_id=password_conferma inp_name=password_conferma  etichetta="$module_name.PASSWORD_CONFERMA" help=true type="password" inp_value=""}
      </div>
    </div>

    {include file="generic/snippets/footer-buttons.tpl"}

  </form>
{/if}