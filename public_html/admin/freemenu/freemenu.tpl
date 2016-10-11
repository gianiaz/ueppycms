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

{* LISTA DEGLI STILI DISPONIBILI *}
{if $act eq "stili"}
  <div class="dataTable_wrapper">
    <table class="table table-striped table-bordered table-hover" id="dataTable">
    </table>
  </div>
  <div id="new_stile">
    <form name="ajaxForm2" id="ajaxForm2" action="{make_url params="cmd/$cmd/act/save_style"}" method="post">
      <input type="hidden" name="id" id="id_style" value=""/>
      <div class="col-lg-12">
        <div class="row">
          {* NOME *}
          {ueppy_form_field required=true inp_id=nome inp_name=nome  etichetta="$module_name.NOME" help=true type="text" inp_value=""}
        </div>
        <div class="row">
          {* TEXTAREA *}
          {ueppy_form_field lbl_class="noheight" required=true inp_id=markup inp_name=markup  etichetta="$module_name.TESTO" help=true type="textarea" inp_value=""}
        </div>
      </div>
    </form>
    <div class="clearfix"></div>
  </div>
{/if}

{* FORM DI INSERIMENT/MODIFICA RECORD *}
{if $act eq "new"}
  <form id="ajaxForm" name="ajaxForm"
        action="{make_url params="cmd/$cmd/act/insert"}"
        method="post"
        enctype="multipart/form-data">

    <input type="hidden" id="id" name="id" value="{$Obj->fields.id}"/>

    <div class="row">
      <div class="col-lg-5">
        {* NOME *}
        {ueppy_form_field required=true inp_id=nome inp_name=nome  etichetta="$module_name.NOME" help=true type="text" inp_value=$Obj->fields.nome}
      </div>
      <div class="col-lg-5">
        {* STILE *}
        {ueppy_form_field required=true inp_id=freemenu_styles_id inp_name=freemenu_styles_id  etichetta="$module_name.STILE" help=true type="select" inp_options=$stili_options inp_value=$Obj->fields.freemenu_styles_id}
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
                <div class="col-lg-5">
                  {* TITOLO *}
                  {ueppy_form_field required=false inp_id="titolo_$sigla_lingua" inp_name="$sigla_lingua[titolo]"  etichetta="$module_name.TITOLO" help=true type="text" inp_value="`$Obj->fields.$sigla_lingua.titolo`"}
                </div>
              </div>

              <button class="plus btn btn-primary">
                <i class="fa fa-plus"></i> {getLang module=$module_name key="ADD_LINE"}</button>

              <textarea class="links" name="{$sigla_lingua}[links]"></textarea>
              <table class="table table-responsive">
                <thead>
                <tr>
                  <th>{getLang module=$module_name key="LINK"}</th>
                  <th>{getLang module=$module_name key="TESTO"}</th>
                  <th>{getLang module=$module_name key="NOFOLLOW"}</th>
                  <th>{getLang module=$module_name key="BLANK"}</th>
                </tr>
                </thead>
                <tbody>
                {if $Obj->additionalData.links.$sigla_lingua}
                  {foreach item=riga from=$Obj->additionalData.links.$sigla_lingua}
                    <tr>
                      <td><input class="form-control" type="text" name="link[]" value="{$riga.url}"/></td>
                      <td>
                        <input class="form-control" type="text" name="testo[]" value="{$riga.label|htmlentities:"3":"UTF-8"}"/>
                      </td>
                      <td><input type="checkbox" name="nofollow[]" value="1"{if $riga.nofollow} checked="true"{/if} />
                      </td>
                      <td><input type="checkbox" name="blank[]" value="1"{if $riga.blank} checked="true"{/if} />
                      </td>
                      <td>
                        <button class="btn btn-danger deleteRow">elimina</button>
                      </td>
                    </tr>
                  {/foreach}
                {else}
                  <tr>
                    <td><input class="form-control" type="text" name="link[]" value=""/></td>
                    <td><input class="form-control" type="text" name="testo[]" value=""/></td>
                    <td><input type="checkbox" name="nofollow[]" value="1"/></td>
                    <td><input type="checkbox" name="blank[]" value="1"/></td>
                  </tr>
                {/if}
                </tbody>
              </table>


            </div>
          {/foreach}
        </div>
      </div>
    </div>

    {include file="generic/snippets/footer-buttons.tpl"}

  </form>
  <table class="rowTpl">
    <tr>
      <td><input class="form-control" type="text" name="link[]" value=""/></td>
      <td><input class="form-control" type="text" name="testo[]" value=""/></td>
    </tr>
  </table>
{/if}