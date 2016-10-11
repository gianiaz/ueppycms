{*********************}
{*/***ueppy3.1.01***/*}
{*********************}
{**     CHANGELOG   **}
{*************************************************************}
{** v.3.1.01                                                **}
{** - Rimosso link per la traduzione con google             **}
{**                                                         **}
{** v.3.1.00                                                **}
{** - Versione stabile                                      **}
{**                                                         **}
{*************************************************************}
{** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com> **}
{** copyright: Ueppy s.r.l                                  **}
{*************************************************************}

{* LISTA DEI RECORD PRESENTI *}
{if $act eq ""}
  <div class="clear"></div>
  {if $operator->isAdmin()}
    <div class="tblOptions">
      <label>
        <input type="checkbox" id="noncompilate" value="1"/> {getLang module=$module_name key="NON_COMPILATE"}
      </label>
    </div>
  {/if}
  <div class="dataTable_wrapper">
    <table class="table table-striped table-bordered table-hover" id="dataTable">
    </table>
  </div>
  <div id="modal">

    <form name="ajaxForm2" id="ajaxForm2" method="post"
          action="{make_url params="cmd/$cmd/act/insert"}">

      <input type="hidden" name="id" id="id" value=""/>

      {if $operator->fields.super_admin}
        <div class="row">
          {if SOURCE}
            <div class="col-md-6">
              {* scope *}
              {ueppy_form_field required=false inp_name=scope inp_id=scope etichetta="$module_name.SCOPE" help=true type="select" inp_options=$scopeOptions inp_value=$Obj->fields.scope}
            </div>
          {/if}
          <div class="col-md-6">
            {* CHIAVE *}
            {ueppy_form_field required=false inp_id="chiave" inp_name="chiave"  etichetta="$module_name.CHIAVE" help=true type="text" inp_value=""}
          </div>
        </div>
        <div class="row">
          <div class="col-md-4">
            {* sezione *}
            {ueppy_form_field required=false inp_name=sezione inp_id=sezione etichetta="$module_name.SEZIONE" help=true type="select" inp_options=$sezioneOptions inp_value=""}
          </div>
          <div class="col-md-4">
            {* MODULO *}
            {ueppy_form_field required=false inp_id="modulo" inp_name="modulo"  etichetta="$module_name.MODULO" help=true type="text" inp_value=""}
          </div>
          <div class="col-md-4">
            {* linguaggio *}
            {ueppy_form_field required=false inp_name=linguaggio inp_id=linguaggio etichetta="$module_name.LINGUAGGIO" help=true type="select" inp_options=$linguaggioOptions inp_value=""}
          </div>
        </div>
      {/if}
      <div class="row">
        {foreach item=lang_estesa name="ciclo" key=sigla_lingua from=$lingueTraduzioni}
        <div class="col-md-6">
          {* DICITURA *}
          {ueppy_form_field required=false inp_id="dicitura_{$sigla_lingua}" inp_name="{$sigla_lingua}[dicitura]"  etichetta="$module_name.DICITURA" customLabel="$lang_estesa" help=true type="textarea" inp_value=""}
        </div>
        {if $lang_estesa@iteration is div by 2}
      </div>
      <div class="row">
        {/if}
        {/foreach}
    </form>
  </div>
  </div>
{/if}