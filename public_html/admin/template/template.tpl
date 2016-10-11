{***************|
{** v.1.00    **|
{***************|
{** CHANGELOG **|
{**************************************************************************************************|
{** v.1.00 (08/06/16, 15.54)                                                                     **|
{**************************************************************************************************|
{** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **|
{** copyright: Ueppy s.r.l                                                                       **|
{**************************************************************************************************}
{if $operator->isSuperAdmin()}
  <div class="row">
    <div class="col-lg-10">
      <div class="panel panel-primary">
        <div class="panel-heading">
          <h3 class="panel-title ">{getLang module="template" key="MODULI_DISPONIBILI"}</h3>
        </div>
        <div class="panel-body panel-disponibili">
          {getLang module=$module_name key="MODULI_DISPONIBILI_INSTRUCTIONS" htmlallowed="true"}
        </div>
      </div>

      <div class="panel panel-primary">
        <div class="panel-heading">
          <h3 class="panel-title ">{getLang module="template" key="BLOCCHI_DISPONIBILI"}</h3>
        </div>
        <div class="panel-body panel-blocchi">
          {getLang module=$module_name key="BLOCCHI_INSTRUCTIONS" htmlallowed="true"}
        </div>
      </div>
    </div>

    <div class="col-lg-2">
      <div class="panel panel-primary">
        <div class="panel-heading">
          <h3 class="panel-title ">{getLang module="template" key="SELEZIONA_TEMPLATE"}</h3>
        </div>
        <div class="panel-body">
          <div class="list-group">
            <div class="list-group-item list-group-item-success">
              <strong>Comuni</strong></div>
            {foreach item=templateLabel key=template from=$defaults}
              <a href="#" class="list-group-item" data-template="{$template}">{$templateLabel}</a>
            {/foreach}
            <div role="separator" class="divider"></div>
            <div class="list-group-item list-group-item-success">
              <strong>Sezioni</strong></div>
            {foreach item=templateLabel key=template from=$sezioni}
              <a href="#" class="list-group-item" data-template="{$template}">{$templateLabel}</a>
            {/foreach}
          </div>
        </div>
      </div>
    </div>
  </div>
  {include file="generic/snippets/footer-buttons.tpl"}
  <script type="text/x-handlebars-template" id="avail-template">
    <div class="avail{literal}{{#if dyn}} dyn{{/if}}{/literal}" data-duplicabile="{literal}{{info.duplicabile}}{/literal}" data-nome="{literal}{{nome}}{/literal}" data-dyn="{literal}{{dyn}}{/literal}">
      <i class="btn btn-success fa fa-arrows-alt anchor"></i>
      <span class="name">{literal}{{info.nome}}{/literal}</span>
      <button class="btn btn-info infoModulo" data-info="{literal}{{info.info}}{/literal}" data-author="{literal}{{info.author}}{/literal}" data-duplicabile="{literal}{{duplicabile}}{/literal}">
        <i class="fa fa-info"></i></button>
    </div>
  </script>
  <script type="text/x-handlebars-template" id="widget-template">
    <div data-widget="{literal}{{nome}}{/literal}" data-istanza="{literal}{{db.istanza}}{/literal}" data-duplicabile="{literal}{{info.duplicabile}}{/literal}" data-id="{literal}{{db.id}}{/literal}" class="widget{literal}{{#if main}} main{{/if}}{{#if dyn}} dyn{{/if}}{/literal}" data-dyn="{literal}{{dyn}}{/literal}">
      <i class="btn btn-success fa fa-arrows-alt anchor"></i>
      <span class="name">{literal}{{info.nome}}{/literal}</span>

      {literal}{{#if viste}}{/literal}
      <div class="vista">
        <span>{literal}{{VISTA_DA_UTILIZARE}}{/literal}</span>
        <select class="form-control view">
          {literal}{{#each viste}}{/literal}
          <option{literal}{{#if this.selected}} selected{{/if}}{/literal} value="{literal}{{this.label}}{/literal}">{literal}{{this.label}}{/literal}</option>
          {literal}{{/each}}{/literal}
        </select>
      </div>
      {literal}{{/if}}{/literal}
      <button class="btn btn-warning settingsModulo{literal}
      {{#if disableSettings}} disabled{{/if}}{/literal}" {literal}{{#if disableSettings}} disabled{{/if}}{/literal}>
      <i class="fa fa-gear"></i></button>
      <button class="btn btn-info infoModulo" data-info="{literal}{{info.info}}{/literal}" data-author="{literal}{{info.author}}{/literal}">
        <i class="fa fa-info"></i>
      </button>
      <button class="btn btn-danger delModulo{literal}{{#if main}} disabled{{/if}}{/literal}"
              {literal}{{#if main}} disabled{{/if}}{/literal}><i class="fa fa-trash"></i></button>
    </div>
  </script>
  <script type=text/x-handlebars-template" id="blocco-template">
    <div class="panel panel-blocco {literal}{{#if principale}}panel-success{{else}}panel-info{{/if}}{/literal}" data-nome="{literal}{{nome}}{/literal}" data-principale="{literal}{{#if principale}}1{{else}}0{{/if}}{/literal}">
      <div class="panel-heading">
        <h3 class="panel-title ">{literal}{{nome}} - {{typeDescr}}{/literal}</h3>
      </div>
      <div class="panel-body panel-blocchi connectedSortable">
        {literal}{{{markup}}}{/literal}
      </div>
    </div>
  </script>
  <script type="text/x-handlebars" id="text-template">
    <div class="col-lg-4">
      <div class="form-group">
        <i class="fa fa-question-circle cmsHelp text-info" data-toggle="tooltip" data-placement="right" data-title="{literal}{{help}}{/literal}"></i><label class="control-label" for="{literal}{{var}}{/literal}">{literal}{{label}}{/literal}</label>
        <input id="{literal}{{var}}{/literal}" name="{literal}{{var}}{/literal}" value="{literal}{{default}}{/literal}" type="text" class="form-control"/>
      </div>
    </div>
  </script>
  <script type="text/x-handlebars" id="boolean-template">
    <div class="col-lg-4">
      <div class="form-group">
        <i class="fa fa-question-circle cmsHelp text-info" data-toggle="tooltip" data-placement="right" data-title="{literal}{{help}}{/literal}"></i><label class="control-label" for="{literal}{{var}}{/literal}">{literal}{{label}}{/literal}</label>
        <select name="{literal}{{var}}{/literal}" id="{literal}{{var}}{/literal}" class="form-control">
          <option value="0">{literal}{{NO}}{/literal}</option>
          <option value="1">{literal}{{SI}}{/literal}</option>
        </select>
      </div>
    </div>
  </script>
  <script type="text/x-handlebars" id="selectcopy-template">
    <div class="col-lg-4">
      <a href="#" id="copyFrom" class="btn btn-success btn-block"><i class="fa fa-clone"></i>&nbsp;{getLang module=$module_name key=COPIA_DA}</a>
    </div>
    <div class="col-lg-4">
      <div class="form-group">
        <i class="fa fa-question-circle cmsHelp text-info" data-toggle="tooltip" data-placement="right" data-title="{getLang module=$module_name key=SORGENTE_CONFIGURAZIONE_HELP}"></i><label class="control-label" for="copy">{getLang module=$module_name key=SORGENTE_CONFIGURAZIONE}</label>
        <select id="copy" class="form-control">
          {literal}{{#each data}}{/literal}
          <option value="{literal}{{value}}{/literal}">{literal}{{label}}{/literal}</option>
          {literal}{{/each}}{/literal}
        </select>
      </div>
    </div>
    <div class="clearfix"></div>
  </script>
  <script type="text/x-handlebars" id="text-ml-template">
    <div class="form-group">
      <i class="fa fa-question-circle cmsHelp text-info" data-toggle="tooltip" data-placement="right" data-title="{literal}{{help}}{/literal}"></i><label class="control-label" for="{literal}{{var}}{/literal}">{literal}{{label}}{/literal}</label>
      <input id="{literal}{{var}}{/literal}_{literal}{{lingua}}{/literal}" name="{literal}{{lingua}}{/literal}[{literal}{{var}}{/literal}]" value="{literal}{{value}}{/literal}" type="text" class="form-control"/>
    </div>
  </script>
  <script type="text/x-handlebars" id="textarea-ml-template">
    <div class="form-group" style="z-index:10000000">
      <i class="fa fa-question-circle cmsHelp text-info" data-toggle="tooltip" data-placement="right" data-title="{literal}{{help}}{/literal}"></i><label class="control-label" for="{literal}{{var}}{/literal}">{literal}{{label}}{/literal}</label>
      <textarea class="form-control mce" id="{literal}{{var}}{/literal}_{literal}{{lingua}}{/literal}" name="{literal}{{lingua}}{/literal}[{literal}{{var}}{/literal}]">{literal}{{value}}{/literal}</textarea>
    </div>
  </script>
  <script type="text/x-handlebars" id="ml-template">
    <div class="panel with-nav-tabs panel-default">
      <div class="panel-heading">
        <ul class="nav nav-tabs" role="tablist">
          {foreach item=lang_estesa key=sigla_lingua name="lingueIter" from=$lingue}
            <li{if $smarty.foreach.lingueIter.first} class="active"{/if}><a
                      href="#scheda_{$sigla_lingua}" data-toggle="tab"><span>{$lang_estesa}</span></a>
            </li>
          {/foreach}
        </ul>
      </div>
      <div class="panel-body">
        <div class="tab-content">
          {foreach item=lang_estesa key=sigla_lingua name="lingueIter" from=$lingue}
            <div id="scheda_{$sigla_lingua}" data-lingua="{$sigla_lingua}" class="tab-lang tab-pane{if $smarty.foreach.lingueIter.first} active{/if}"
                 role="tabpanel">
            </div>
          {/foreach}
        </div>
      </div>
    </div>
  </script>
  <div id="modal">
    <form id="ajaxForm2" name="ajaxForm2" method="post" action="{make_url params="cmd/$cmd/act/save_config" lang=$ACTUAL_LANGUAGE}">

    </form>
  </div>
  <div id="modal2">
    <form id="ajaxForm3" name="ajaxForm3" method="post" action="{make_url params="cmd/$cmd/act/save_widget" lang=$ACTUAL_LANGUAGE}">

      <input type="hidden" name="id" id="id" value="0"/>

      {* NOME *}
      {ueppy_form_field required=false inp_id="nome" inp_name="nome"  etichetta="$module_name.NOME" help=true type="text" inp_value=$Obj->fields.nome}

    </form>
  </div>
  <div class="divider"></div>
{/if}

<h2 class="text-primary wdH2">{getLang module=$module_name key="WIDGET_DINAMICI"}</h2>

<button class="btn btn-info" id="newWidget"><i class="fa fa-plus"></i> {getLang module=$module_name key="NUOVO_WIDGET"}
</button>

<div id="wd" class="dataTable_wrapper">
  <table class="table table-striped table-bordered table-hover" id="dataTable">
  </table>
</div>
