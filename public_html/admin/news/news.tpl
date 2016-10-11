{***************}
{** v. 1.00   **}
{***************}
{** CHANGELOG **}
{**************************************************************************************************}
{** v.1.00                                                                                       **}
{** - Versione stabile                                                                           **}
{**                                                                                              **}
{**************************************************************************************************}
{** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **}
{** copyright: Ueppy s.r.l                                                                       **}
{**************************************************************************************************}

{* GESTIONE DEL SEO *}
{if $act eq "seo"}
  <div class="dataTable_wrapper">
    <table class="table table-striped table-bordered table-hover" id="dataTable"
           data-categorie="{$SETTINGS.ENABLE_CAT_NEWS}" data-commenti="{$SETTINGS.NEWS_COMMENTI}">
    </table>
  </div>
{/if}

{* ELENCO ELEMENTI *}
{if $act eq ""}
  <div class="dataTable_wrapper">
    <table class="table table-striped table-bordered table-hover" id="dataTable"
           data-categorie="{$SETTINGS.ENABLE_CAT_NEWS}" data-commenti="{$SETTINGS.NEWS_COMMENTI}">
    </table>
  </div>
{/if}

{* ELENCO COMMENTI *}
{if $act eq "comments"}
  <div class="dataTable_wrapper">
    <table class="table table-striped table-bordered table-hover" id="dataTable"
           data-news_id="{$news_id}">
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

    <input type="hidden" id="id" name="id" value="{$Obj->fields.id}"/>

    {if $CAT_NEWS_AVAILABLE}
      <div class="row">
        <div class="col-lg-3">
          {ueppy_form_field
          lbl_data_demo="1"
          lbl_data_demo-step="5"
          lbl_data_demo-msg="$module_name.DEMO_CATEGORY"
          readonly=$readOnlyCat required=true inp_name=genitore inp_id=genitore etichetta="$module_name.CATEGORY" help=true type="select" inp_options=$cat_options_single inp_value="`$Obj->additionalData.genitore`"}
        </div>
        {if $operator->isAdvanced()}
          <div class="col-lg-3">
            {ueppy_form_field readonly=$readonly lbl_class="noheight" required=false inp_name="parents[]" inp_id=parents inp_multiple=true inp_size=5 etichetta="$module_name.PARENT" help=true type="select" inp_options=$cat_options inp_value=$Obj->additionalData.parents_id}
            {if $categorie_non_modificabili}
              <label>
                <a href="#" class="nht" data-help="{getLang module="news" key="CATEGORIE_AGGIUNTIVE"}"></a>
                <span class="txtlbl">{getLang module="news" key="ASSOCIATA_ANCHE"}</span>
                <span class="ueppy_ro">{$categorie_non_modificabili}</span>
              </label>
            {/if}
          </div>
        {/if}
      </div>
    {/if}

    {if $operator->isMedium()}
      <div class="row">
        <div class="col-lg-3">
          {* SELECT ATTIVAZIONE *}
          {ueppy_form_field readonly=$readonly required=false inp_name=stato inp_id=stato etichetta="default.ABILITAZIONE" help=true type="select" inp_options=$abilitazioni inp_value="`$Obj->fields.attivo`"}
        </div>
        <div class="col-lg-3">
          {* DATA *}
          {ueppy_form_field required=true readonly=$readonly inp_class="data" inp_name=attiva_dal inp_id=attiva_dal etichetta="$module_name.DATE" help=true type="text" inp_value=$Obj->additionalData.attiva_dal}
        </div>
      </div>
    {/if}

    {if $operator->isAdvanced()}
      <div class="row">
        <div class="col-lg-3">
          {* SELECT DISATTIVAZIONE *}
          {ueppy_form_field required=false readonly=$readonly  inp_name=disattivazione inp_id=disattivazione etichetta="$module_name.DISATTIVAZIONE" help=true type="select" inp_options=$scadenze inp_value=$Obj->additionalData.scadenza}
        </div>
        <div id="disattivazioneContainer" class="col-lg-3">
          {* DATA *}
          {ueppy_form_field readonly=$readonly required=true inp_class="data" inp_name=disattiva_dal inp_id=disattiva_dal etichetta="$module_name.DATE_DISATTIVAZIONE" help=true type="text" inp_value=$Obj->additionalData.disattiva_dal}
        </div>
      </div>
      <div class="row">
        <div class="col-lg-3">
          {* operatori_id *}
          {ueppy_form_field required=false inp_name=operatori_id inp_id=operatori_id etichetta="$module_name.OPERATORI_ID" help=true type="select" inp_options=$autori inp_value=$Obj->fields.operatori_id}
        </div>
      </div>
    {/if}

    {if $SETTINGS.NEWS_COMMENTI}
      <div class="row">
        <div class="col-lg-3">
          {* commenti *}
          {ueppy_form_field required=false inp_name=commenti inp_id=commenti etichetta="$module_name.COMMENTI" help=true type="select" inp_options=$commentiOptions inp_value=$Obj->fields.commenti}
        </div>
      </div>
    {/if}

    {if $operator->isMedium()}
      {if count($lingue) > 1}
        <div class="row">
          <div class="col-lg-12">
            {ueppy_form_field help=true readonly=$readonly lbl_class="radiolabel" etichetta="$module_name.ABILITA_LINGUE" type="checkbox" inp_options=$lista_lingue inp_value=$Obj->additionalData.lingue_attive}
          </div>
        </div>
      {else}
        <input type="hidden" name="lingua_attiva[]" value="{$ACTUAL_LANGUAGE}"/>
      {/if}
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

                  {if $sigla_lingua == "it"}
                    {assign var=demo value=1}
                  {else}
                    {assign var=demo value=0}
                  {/if}
                  {* TITOLO *}
                  {ueppy_form_field
                  lbl_data_demo=$demo
                  lbl_data_demo-step="6"
                  lbl_data_demo-msg="$module_name.DEMO_TITOLO"
                  readonly=$readonly required="true" inp_id="titolo_$sigla_lingua" inp_name="$sigla_lingua[titolo]"  etichetta="$module_name.TITOLO" help=true type="text" inp_value="`$Obj->fields.$sigla_lingua.titolo`"}
                </div>
              </div>
              <div class="row">
                <div class="col-lg-6">
                  {* INTRO *}
                  {ueppy_form_field readonly=$readonly inp_class="intro"  counter="`$SETTINGS.NEWS_INTRO_MAX_CHARS`" required=false inp_id="intro_$sigla_lingua" inp_name="$sigla_lingua[intro]"  etichetta="$module_name.INTRO" help=true type="textarea" inp_value="`$Obj->fields.$sigla_lingua.intro`"}
                </div>
              </div>

              {if $operator->isMedium()}
                <div class="row">
                  <div class="col-lg-6">
                    {ueppy_form_field readonly=$readonly required=false inp_name="$sigla_lingua[tags]" inp_id="tag_$sigla_lingua" etichetta="$module_name.TAGS" help=true type="text" inp_value="`$Obj->additionalData.tags.$sigla_lingua.string`"}
                  </div>
                  {if !$readonly}
                    <div class="col-lg-6">
                      <button id="tagPrecenti"
                              class="btn btn-default"><i
                                class="fa fa-arrow-down"></i> {getLang module=$module_name key="TAG_PRECEDENTI"}
                      </button>
                      <div class="elenco_tags">
                        {foreach item=tag key=id from=$tags.$sigla_lingua}
                          <span class="label label-primary" data-tag="{$tag.tag}">{$tag.tag} ({$tag.count}
                            )</span>
                        {/foreach}
                      </div>
                    </div>
                  {/if}

                </div>
              {/if}

              {if $operator->isAdvanced()}
                <div class="row">
                  <div class="col-lg-4">
                    {* TITLE *}
                    {ueppy_form_field required=false readonly=$readonly inp_id="htmltitle_$sigla_lingua" inp_name="$sigla_lingua[htmltitle]" etichetta="default.HTMLTITLE" help=true type="text" inp_value="`$Obj->fields.$sigla_lingua.htmltitle`"}
                  </div>
                  <div class="col-lg-4">
                    {* DESCRIPTION *}
                    {ueppy_form_field required=false readonly=$readonly inp_id="description_$sigla_lingua" inp_name="$sigla_lingua[description]"  etichetta="default.DESCRIPTION" help=true type="text" inp_value="`$Obj->fields.$sigla_lingua.description`"}
                  </div>
                  {if $operator->isAdvanced()}
                    <div class="col-lg-4">
                      {* HREF *}
                      {ueppy_form_field readonly=$readonly required=false inp_id="href_$sigla_lingua" inp_class="restricted replacespace" inp_rel="[a-z0-9\-_]" inp_name="$sigla_lingua[href]"  etichetta="default.HREF" help=true type="text" inp_value="`$Obj->fields.$sigla_lingua.href`"}
                      <a class="lnkpg" href="{$links.$sigla_lingua}"
                         target="_blank">{$links.$sigla_lingua}</a>
                    </div>
                  {/if}
                </div>
              {/if}

              {* TESTO *}
              {ueppy_form_field readonly=$readonly required=false inp_id="testo_$sigla_lingua" inp_class="mce" inp_name="$sigla_lingua[testo]" etichetta="$module_name.TESTO" help=true type="textarea" inp_value="`$Obj->fields.$sigla_lingua.testo`"}

            </div>
          {/foreach}
        </div>
      </div>
    </div>

    {include file="generic/snippets/footer-buttons.tpl"}

  </form>
{/if}

