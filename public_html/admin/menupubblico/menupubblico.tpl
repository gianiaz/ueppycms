{***************}
{** v.1.00    **}
{***************}
{** CHANGELOG **}
{**************************************************************************************************}
{** v.1.00 (11/05/2016)                                                                          **}
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
    <input type="hidden" name="id" id="id" value="{$Obj->fields.id}"/>
    <input type="hidden" name="genitore" id="genitore" value="{$genitore.id}"/>

    {if $operator->isSuperAdmin()}
      <div class="row">

        <div class="col-lg-3">
          {* nomefile *}
          {ueppy_form_field required=true debug=0 inp_data_id=$Obj->fields.id inp_data_prot=scorr inp_id=nomefile inp_name=nomefile  etichetta="$module_name.NOMEFILE" help=true type="text" inp_value=$Obj->fields.nomefile}
        </div>
        <div class="col-lg-3">
          {* attivo *}
          {ueppy_form_field required=false inp_name=attivo inp_id=attivo etichetta="default.ATTIVA_SUBITO" help=true type="select" inp_options=$attivoOptions inp_value=$Obj->fields.attivo}

        </div>
      </div>
    {/if}

    <div class="row">
      <div class="col-lg-3">
        {* LIVELLO DELLA PAGINA *}
        {ueppy_form_field required=true inp_name=level inp_id=level etichetta="$module_name.LEVEL" help=true type="select" inp_options=$list_posizione inp_value="`$Obj->fields.level`"}

      </div>
      {if $operator->isSuperAdmin()}
        <div class="col-lg-3">
          {* SELECT TEMPLATE *}
          {ueppy_form_field required=false inp_name=template inp_id=template etichetta="$module_name.TEMPLATE" help=true type="select" inp_options=$files_di_template inp_value=$Obj->fields.template}
        </div>
      {/if}
      {if $operator->isAdvanced()}
        <div class="col-lg-3">
          {ueppy_form_field required=false inp_name=robots inp_id=robots etichetta="$module_name.ROBOTS" help=true type="select" inp_options=$robots_options inp_value="`$Obj->fields.robots`"}
        </div>
      {/if}
    </div>

    {if $operator->isSuperAdmin()}
      <div class="form-group">
        <i class="fa fa-question-circle cmsHelp" data-toggle="tooltip" data-placement="top"
           data-title="{getLang module=$module_name key="GENITORE_HELP"}"></i>
        <label class="control-label">{getLang module=$module_name key="GENITORE"}</label>
        <button class="btn btn-default" id="cambiagenitore">
          <i class="fa fa-pencil"></i> {$genitore.label}</button>
      </div>
      <div id="parentUL">
      </div>
    {/if}

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

                <div class="col-lg-6">
                  {* TITOLO *}
                  {ueppy_form_field required=true inp_id="dicitura_$sigla_lingua" inp_name="$sigla_lingua[dicitura]"  etichetta="$module_name.DICITURA" help=true type="text" inp_value=$Obj->fields.$sigla_lingua.dicitura}
                </div>

                {if $operator->isMedium()}
                  <div class="col-lg-6">
                    {* TITOLO_BREVE *}
                    {ueppy_form_field required=false inp_id="titolo_breve_$sigla_lingua" inp_name="$sigla_lingua[titolo_breve]"  etichetta="$module_name.TITOLO_BREVE" help=true type="text" inp_value=$Obj->fields.$sigla_lingua.titolo_breve}
                  </div>
                {/if}
              </div>

              {if $operator->isAdvanced()}
                <div class="row">
                  <div class="col-lg-4">
                    {* DESCRIPTION *}
                    {ueppy_form_field required=false inp_id="description_$sigla_lingua" inp_name="$sigla_lingua[description]" etichetta="default.DESCRIPTION" help=true type="text" inp_value=$Obj->fields.$sigla_lingua.description}
                  </div>
                  <div class="col-lg-4">
                    {* TITLE *}
                    {ueppy_form_field required=false inp_id="htmltitle_$sigla_lingua" inp_name="$sigla_lingua[htmltitle]"  etichetta="default.HTMLTITLE" help=true type="text" inp_value=$Obj->fields.$sigla_lingua.htmltitle}
                  </div>
                  <div class="col-lg-4">
                    {* HREF *}
                    {ueppy_form_field lbl_class="hreflbl" required=false inp_id="href_$sigla_lingua" inp_class="restricted replacespace" inp_rel="[a-z0-9\-_]" inp_name="$sigla_lingua[href]"  etichetta="default.HREF" help=true type="text" inp_value=$Obj->fields.$sigla_lingua.href}
                  </div>
                </div>
              {/if}

              {ueppy_form_field debug=0 help=true level=$operator->fields.level etichetta="$module_name.IMG_DEFAULT" type="imgedit" inp_id="img0_$sigla_lingua" inp_name="$sigla_lingua[img0]" inp_alt_value=$Obj->fields.$sigla_lingua.img0_alt inp_title_value=$Obj->fields.$sigla_lingua.img0_title inp_value=$Obj->fields.fileData.img0.$sigla_lingua.versioni.0.url}

              {ueppy_form_field debug=0 help=true level=$operator->fields.level etichetta="$module_name.IMG_ROLLOVER" type="imgedit" inp_id="img1_$sigla_lingua" inp_name="$sigla_lingua[img1]" inp_alt_value=$Obj->fields.$sigla_lingua.img1_alt inp_title_value=$Obj->fields.$sigla_lingua.img1_title inp_value=$Obj->fields.fileData.img1.$sigla_lingua.versioni.0.url}
            </div>
          {/foreach}
        </div>
      </div>

    </div>
    {include file="generic/snippets/footer-buttons.tpl"}
  </form>
{/if}

{* GESTIONE DEL SEO *}
{if $act eq "seo"}
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

    {foreach item=group from=$data}
      <div class="panel-menu panel panel-default">
        <div class="panel-heading">
          <h3 class="panel-title">{$group.level}</h3>
        </div>
        <div class="panel-body" data-group="{$group.group}">
          <div class="dd">
            {$group.tree}
          </div>
        </div>
      </div>
    {/foreach}

    {include file="generic/snippets/footer-buttons.tpl"}
  </form>
{/if}