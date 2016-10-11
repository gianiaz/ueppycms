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

    <div class="row">
      <div class="col-lg-4">
        {* FILENAME *}
        {ueppy_form_field required=true inp_id=filename inp_name=filename  etichetta="$module_name.FILENAME" help=true type="text" inp_value=$file.filename}
      </div>
      <div class="col-lg-6">
        {* NOME *}
        {ueppy_form_field required=true inp_id=nome inp_name=nome  etichetta="$module_name.NOME" help=true type="text" inp_value=$file.nome}
      </div>
    </div>
    <div class="row">
      <div class="col-lg-10">
        {* DESCRIZIONE *}
        {ueppy_form_field required=true inp_id=descrizione inp_name=descrizione  etichetta="$module_name.DESC" help=true type="text" inp_value=$file.descrizione}
      </div>
    </div>

    {* TESTO *}
    {ueppy_form_field required=false inp_id=contentTpl inp_class="mce" inp_name=content  etichetta="$module_name.CONTENT" help=true type="textarea" inp_value=$file.content}

    {include file="generic/snippets/footer-buttons.tpl"}

  </form>
{/if}