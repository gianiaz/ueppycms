{* ELENCO ELEMENTI *}
{if $act eq ""}
  <div class="dataTable_wrapper">
    <table class="table table-striped table-bordered table-hover" id="dataTable" data-commenti="{$SETTINGS.PAGINE_COMMENTI}">
    </table>
  </div>
{/if}

{* FORM DI INSERIMENT/MODIFICA RECORD *}
{if $act eq "new"}
  <form data-readonly="{$readonly}" id="ajaxForm" name="ajaxForm"
        action="{make_url params="cmd/$cmd/act/insert"}"
        method="post"
        enctype="multipart/form-data">

    <input type="hidden" id="id" name="id" value="{$Obj->id}"/>
    <input type="hidden" name="genitore" id="genitore" value="{$genitore.id}"/>


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

              {* testo *}
              {ueppy_form_field required="false" inp_class="mce" inp_id="testo_$sigla_lingua" inp_name="$sigla_lingua[testo]"  etichetta="$module_name.TESTO" help=true type="textarea" inp_value="`$Obj->fields.$sigla_lingua.testo`"}
            </div>
          {/foreach}
        </div>
      </div>
    </div>


    {include file="generic/snippets/footer-buttons.tpl"}

  </form>
{/if}
