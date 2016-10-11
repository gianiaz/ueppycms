{***************|
{** v .1.00   **|
{***************|
{** CHANGELOG **|
{**************************************************************************************************|
{** v. 1.00 (08/10/2015)                                                                         **|
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

{if $act eq "sort"}
  <form name="ajaxForm" id="ajaxForm"
        action="{make_url params="cmd/$cmd/act/saveorder"}"
        method="post">
    <input type="hidden" id="neworder" name="neworder" value="{$ordine}"/>

    <ul class="sort">
      {foreach item=nome key=id from=$grp_list}
        <li data-id="{$id}">
          {$nome}
        </li>
      {/foreach}
    </ul>

    {include file="generic/snippets/footer-buttons.tpl"}
  </form>
{/if}

{* FORM DI INSERIMENT/MODIFICA RECORD *}
{if $act eq "new"}
  <form name="ajaxForm" id="ajaxForm" method="post"
        action="{make_url params="cmd/$cmd/act/insert"}">

    <input id="id" type="hidden" name="id" value="{$Obj->fields.id}"/>

    <div class="row">
      <div class="col-lg-4">
        {* NOMECOMPLETO *}
        {ueppy_form_field required=true  inp_id=nome inp_name=nome  etichetta="$module_name.NOME" help=true type="text" inp_value="`$Obj->fields.nome`"}
      </div>
    </div>
    <div class="row">
      <div class="col-lg-2">
        {* attivo *}
        {ueppy_form_field debug=0 required=false inp_name=attivo inp_id=attivo etichetta="default.ATTIVA_SUBITO" help=true type="select" inp_options=$attivoOptions inp_value=$Obj->fields.attivo}
      </div>
    </div>

    {include file="generic/snippets/footer-buttons.tpl"}

  </form>
{/if}

{* FORM PER LA GESTIONE DEI PERMESSI  *}
{if $act eq "permessi"}
  <form name="ajaxForm" id="ajaxForm" method="post"
        action="{make_url params="cmd/$cmd/act/savepermissions" lang=$ACTUAL_LANGUAGE}">

    <input type="hidden" id="id" value="{$Obj->fields.id}" name="id"/>

    {if $operator->isAdmin()}
      <div class="row">
        <div class="col-lg-6">
          <label class="permitAll control-label lblmnu"
                 for="permessi_list">{getLang module=$module_name key=PERMIT_ALL}
          </label>
          <input id="permessi_list" type="checkbox" value="1" name="all"
                  {if $Obj->fields.all_elements}
                    checked="checked"
                  {/if}
          />
        </div>
      </div>
    {/if}

    {foreach item=ramo from=$menuPermessi}
      <div class="panel panel-default">
        <div class="panel-heading">{$ramo->fields.$ACTUAL_LANGUAGE.dicitura}</div>

        <div class="panel-body">

          {if $ramo->additionalData.childs}
            {foreach item=nodo from=$ramo->additionalData.childs}
              <div class="col-lg-4">
                <label for="perm{$nodo->fields.id}" class="lblmnu">
                  {$nodo->fields.$ACTUAL_LANGUAGE.dicitura}
                </label>
                <input id="perm{$nodo->fields.id}" class="singlePermission" type="checkbox" name="permessi[]"
                       value="{$nodo->fields.id}"{if in_array($nodo->fields.id, $permessi)} checked="checked"{/if}{if $Obj->fields.all_elements} disabled{/if}/>
              </div>
            {/foreach}
          {/if}
        </div>
      </div>
    {/foreach}

    {include file="generic/snippets/footer-buttons.tpl"}

  </form>
{/if}
