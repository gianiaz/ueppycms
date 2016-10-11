{***************}
{** v. 1.00   **}
{***************}
{** CHANGELOG **}
{**************************************************************************************************}
{** v.1.00 (14/05/2016)                                                                          **}
{** - Versione stabile                                                                           **}
{**                                                                                              **}
{**************************************************************************************************}
{** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **}
{** copyright: Ueppy s.r.l                                                                       **}
{**************************************************************************************************}

{* ELENCO ELEMENTI *}
{if $act eq ""}
  <div class="dataTable_wrapper">
    <table class="table table-striped table-bordered table-hover" id="dataTable" data-commenti="{$SETTINGS.PAGINE_COMMENTI}">
    </table>
  </div>
{/if}

{* GESTIONE DEL SEO *}
{if $act eq "seo"}
  <div class="dataTable_wrapper">
    <table class="table table-striped table-bordered table-hover" id="dataTable"
           data-commenti="{$SETTINGS.PAGINE_COMMENTI}">
    </table>
  </div>
{/if}

{* ELENCO COMMENTI *}
{if $act eq "comments"}
  <div class="dataTable_wrapper">
    <table class="table table-striped table-bordered table-hover" id="dataTable"
           data-menu_id="{$menu_id}">
    </table>
  </div>
  <div id="formEditCommento">


    <form id="ajaxForm2"
          action="{make_url params="cmd/$cmd/act/save_commento"}"
          method="post">

      <input type="hidden" name="id" value="" id="commenti_id"/>

      <div class="row">

        <div class="col-lg-5">
          {* NOME *}
          {ueppy_form_field required=false inp_id="nome" inp_name="nome"  etichetta="$module_name.NOME" help=true type="text" inp_value=$Obj->fields.nome}

        </div>
        <div class="col-lg-5">
          {* EMAIL *}
          {ueppy_form_field required=false inp_id="email" inp_name="email"  etichetta="$module_name.EMAIL" help=true type="text" inp_value=$Obj->fields.email}
        </div>
        <div class="col-lg-2">
          {* valido *}
          {ueppy_form_field required=false inp_name=valido inp_id=valido etichetta="$module_name.VALIDO" help=true type="select" inp_options=$validoOptions inp_value=$Obj->fields.valido}
        </div>
      </div>


      {* COMMENTO *}
      {ueppy_form_field required=false inp_id="commento" inp_name="commento" etichetta="$module_name.COMMENTO" help=true type="textarea" inp_value=$Obj->fields.commento}


    </form>
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

    {if $operator->isMedium()}
      <div class="row">
        <div class="col-lg-3">
          {* SELECT ATTIVAZIONE *}
          {ueppy_form_field required=false inp_name=attivo inp_id=attivo etichetta="default.ABILITAZIONE" help=true type="select" inp_options=$abilitazioni inp_value=$Obj->additionalData.menu->fields.attivo}
        </div>
        <div class="col-lg-3">
          {* DATA PUBBLICAZIONE - APPARE SOLO SE SELEZIONATO IL VALORE PROGRAMMATO NEL SELECT ATTIVAZIONE *}
          {ueppy_form_field inp_class="data" lbl_id=pubdatecont required=false inp_name="pubdate" inp_id="pubdate" etichetta="$module_name.DATE" help=true type="text" inp_value=$Obj->additionalData.menu->additionalData.pubdate}
        </div>
      </div>
    {/if}

    {if $operator->isAdvanced()}
      <div class="row">
        <div class="col-lg-3">
          {* SELECT TEMPLATE *}
          {ueppy_form_field required=true inp_name=template inp_id=template etichetta="$module_name.TEMPLATE" help=true type="select" inp_options=$files_di_template inp_value=$Obj->additionalData.menu->fields.template}
        </div>
        <div class="col-lg-3">
          {* LIVELLO DELLA PAGINA *}
          {ueppy_form_field required=true inp_name="posizione" inp_id="posizione" etichetta="$module_name.POSIZIONE" help=true type="select" inp_options=$list_posizione inp_value=$Obj->additionalData.menu->fields.level}
        </div>
        <div class="col-lg-3">
          {* E' UNA CATEGORIA? *}
          {* is_category *}
          {ueppy_form_field required=false inp_name=is_category inp_id=is_category etichetta="$module_name.IS_CATEGORY" help=true type="select" inp_options=$is_categoryOptions inp_value=$Obj->additionalData.menu->fields.is_category}
        </div>

      </div>
      <div class="row">
        <div class="col-lg-3">
          {* SELECT ROBOTS *}
          {ueppy_form_field required=false inp_name="robots" inp_id="robots" etichetta="$module_name.ROBOTS" help=true type="select" inp_options=$robots_options inp_value=$Obj->additionalData.menu->fields.robots}
        </div>
        <div class="col-lg-3">
          {if $SETTINGS.PAGINE_COMMENTI}
            {* commenti *}
            {ueppy_form_field required=false inp_name=commenti inp_id=commenti etichetta="$module_name.COMMENTI" help=true type="select" inp_options=$commentiOptions inp_value=$Obj->fields.commenti}
          {/if}
        </div>
      </div>
    {/if}

    <div class="row">
      <div class="col-lg-3">
        <div class="form-group">
          <i class="fa fa-question-circle cmsHelp" data-toggle="tooltip" data-placement="top"
             data-title="{getLang module=$module_name key="GENITORE_HELP"}"></i>
          <label class="control-label">{getLang module=$module_name key="GENITORE"}</label>
          <button class="btn btn-default" id="cambiagenitore">
            <i class="fa fa-pencil"></i> {$genitore.label}</button>
        </div>

        <div id="parentUL">
        </div>

      </div>
      {if $operator->isAdvanced()}
        <div class="col-lg-3">
          {if count($lista_gruppi)}
            {ueppy_form_field help=true force_container=true etichetta="$module_name.PERMESSI_GRUPPI" type="checkbox" inp_options=$lista_gruppi inp_value=$Obj->additionalData.auth}
          {/if}
        </div>
      {/if}
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
                <div class="col-lg-4">
                  {* TITOLO *}
                  {ueppy_form_field required=true inp_id="dicitura_$sigla_lingua" inp_name="$sigla_lingua[dicitura]"  etichetta="$module_name.DICITURA" help=true type="text" inp_value=$Obj->additionalData.menu->fields.$sigla_lingua.dicitura}
                </div>
                {if $operator->isMedium()}
                  <div class="col-lg-2">
                    {* TITOLO_BREVE *}
                    {ueppy_form_field required=false inp_id="titolo_breve_$sigla_lingua" inp_name="$sigla_lingua[titolo_breve]"  etichetta="$module_name.TITOLOBREVE" help=true type="text" inp_value=$Obj->additionalData.menu->fields.$sigla_lingua.titolo_breve}
                  </div>
                {/if}
                <div class="col-lg-4">
                  {* SOTTOTITOLO *}
                  {ueppy_form_field required=false inp_id="sottotitolo_$sigla_lingua" inp_name="$sigla_lingua[sottotitolo]" etichetta="$module_name.SOTTOTITOLO" help=true type="text" inp_value=$Obj->fields.$sigla_lingua.sottotitolo}
                </div>
              </div>
              {if $operator->isAdvanced()}
                <div class="row">
                  <div class="col-lg-4">
                    {* TITLE *}
                    {ueppy_form_field required=false readonly=$readonly inp_id="htmltitle_$sigla_lingua" inp_name="$sigla_lingua[htmltitle]" etichetta="default.HTMLTITLE" help=true type="text" inp_value=$Obj->additionalData['menu']->fields.$sigla_lingua.htmltitle}
                  </div>
                  <div class="col-lg-4">
                    {* DESCRIPTION *}
                    {ueppy_form_field required=false readonly=$readonly inp_id="description_$sigla_lingua" inp_name="$sigla_lingua[description]"  etichetta="default.DESCRIPTION" help=true type="text" inp_value=$Obj->additionalData['menu']->fields.$sigla_lingua.description}
                  </div>
                  {if $operator->isAdvanced()}
                    <div class="col-lg-4">
                      {* HREF *}
                      {ueppy_form_field readonly=$readonly required=false inp_id="href_$sigla_lingua" inp_class="restricted replacespace" inp_rel="[a-z0-9\-_]" inp_name="$sigla_lingua[href]"  etichetta="default.HREF" help=true type="text" inp_value=$Obj->additionalData['menu']->fields.$sigla_lingua.href}
                      <a class="lnkpg" href="{$links.$sigla_lingua}"
                         target="_blank">{$links.$sigla_lingua}</a>
                    </div>
                  {/if}
                </div>
              {/if}


              {* TESTO *}
              {ueppy_form_field required=false inp_id="testo_$sigla_lingua" inp_class="mce" inp_name="$sigla_lingua[testo]"  etichetta="$module_name.TESTO" help=true type="textarea" inp_value=$Obj->fields.$sigla_lingua.testo}

              {if $SETTINGS.NUMERO_IMMAGINI_PAGINE}
                <div class="row">
                  <div class="col-lg-6">
                    {ueppy_form_field debug=0 help=true level=$operator->fields.level lbl_class="img_choice_label" etichetta="$module_name.IMG1" type="imgedit" inp_id="img0_$sigla_lingua" inp_name="$sigla_lingua[img0]" inp_alt_value=$Obj->additionalData.menu->fields.$sigla_lingua.img0_alt inp_title_value=$Obj->additionalData.menu->fields.$sigla_lingua.img0_title inp_value=$Obj->additionalData.menu->fields.fileData.img0.$sigla_lingua.versioni.0.url}

                  </div>
                  {if $SETTINGS.NUMERO_IMMAGINI_PAGINE eq 2}
                    <div class="col-lg-6">
                      {ueppy_form_field debug=0 help=true level=$operator->fields.level lbl_class="img_choice_label" etichetta="$module_name.IMG2" type="imgedit" inp_id="img1_$sigla_lingua" inp_name="$sigla_lingua[img1]" inp_alt_value=$Obj->additionalData.menu->fields.$sigla_lingua.img1_alt inp_title_value=$Obj->additionalData.menu->fields.$sigla_lingua.img1_title inp_value=$Obj->additionalData.menu->fields.fileData.img1.$sigla_lingua.versioni.0.url}

                    </div>
                  {/if}
                </div>
              {/if}

            </div>
          {/foreach}
        </div>
      </div>
    </div>

    {include file="generic/snippets/footer-buttons.tpl"}

  </form>
{/if}