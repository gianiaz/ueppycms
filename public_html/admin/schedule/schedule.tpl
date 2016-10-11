{***************|
{** v.1.00    **|
{***************|
{** CHANGELOG **|
{**************************************************************************************************|
{** v.1.00 (21/05/16, 15.03)                                                                     **|
{**************************************************************************************************|
{** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **|
{** copyright: Ueppy s.r.l                                                                       **|
{**************************************************************************************************}
{* ELENCO ELEMENTI *}
{if $act eq ""}
  <div class="dataTable_wrapper">
    <table class="table table-striped table-bordered table-hover" id="dataTable" data-commenti="{$SETTINGS.PAGINE_COMMENTI}">
    </table>
  </div>
{/if}

{* FORM DI INSERIMENT/MODIFICA RECORD *}
{if $act eq "new"}
  {if $Obj->allegatiAbilitati()}
    {include file="generic/plugins/fileAllegati.tpl"}
  {/if}
  <form data-readonly="{$readonly}" id="ajaxForm" name="ajaxForm"
        action="{make_url params="cmd/$cmd/act/insert"}"
        method="post"
        enctype="multipart/form-data">

    <input type="hidden" id="id" name="id" value="{$Obj->id}"/>
    <input type="hidden" name="genitore" id="genitore" value="{$genitore.id}"/>

    <div class="row">

      <div class="col-lg-4">
        {* comando *}
        {ueppy_form_field required=false inp_name=comando inp_id=comando etichetta="$module_name.CRONTABS" help=true type="select" inp_options=$crontabs inp_value=$Obj->fields.comando}
      </div>
      <div class="col-lg-2">
        {* attivo *}
        {ueppy_form_field required=false inp_name=attivo inp_id=attivo etichetta="default.ATTIVA_SUBITO" help=true type="select" inp_options=$attivoOptions inp_value=$Obj->fields.attivo}
      </div>


    </div>

    <div class="row">
      <div class="col-lg-2">
        {* giorno_del_mese *}
        {ueppy_form_field required=false inp_name=giorno_del_mese inp_id=giorno_del_mese etichetta="$module_name.GIORNO_DEL_MESE" help=true type="select" inp_options=$giorno_del_mese inp_value=$Obj->additionalData.giorno}
      </div>
      <div class="col-lg-2">
        {* giorni_del_mese *}
        {ueppy_form_field required=false inp_multiple="multiple" inp_name="giorni_del_mese[]" inp_id=giorni_del_mese etichetta="$module_name.GIORNI_DEL_MESE" help=true type="select" inp_options=$giorni_del_mese inp_value=$Obj->additionalData.giorni}
      </div>
    </div>

    <div class="row">
      <div class="col-lg-2">
        {* ora_del_giorno *}
        {ueppy_form_field required=false inp_name=ora_del_giorno inp_id=ora_del_giorno etichetta="$module_name.ORA_DEL_GIORNP" help=true type="select" inp_options=$ora_del_giorno inp_value=$Obj->additionalData.ora}
      </div>
      <div class="col-lg-2">
        {* ore_del_giorno *}
        {ueppy_form_field required=false inp_multiple="multiple" inp_name="ore_del_giorno[]" inp_id=ore_del_giorno etichetta="$module_name.ORE_DEL_GIORNO" help=true type="select" inp_options=$ore_del_giorno inp_value=$Obj->additionalData.ore}
      </div>
      <div class="col-lg-2">
        {* minuto_dell_ora *}
        {ueppy_form_field required=false inp_name=minuto_dell_ora inp_id=minuto_dell_ora etichetta="$module_name.MINUTO_DELL_ORA" help=true type="select" inp_options=$minuto_dell_ora inp_value=$Obj->additionalData.minuto}
      </div>
      <div class="col-lg-2">
        {* minuti_dell_ora *}
        {ueppy_form_field required=false inp_multiple="multiple" inp_name="minuti_dell_ora[]" inp_id=minuti_dell_ora etichetta="$module_name.MINUTI_DELL_ORA" help=true type="select" inp_options=$minuti_dell_ora inp_value=$Obj->additionalData.minuti}
      </div>
    </div>

    {include file="generic/snippets/footer-buttons.tpl"}

  </form>
{/if}


{if $act eq "logs"}
  <div class="dataTable_wrapper">
    <table class="table table-striped table-bordered table-hover" id="dataTable">
    </table>
  </div>
{/if}
