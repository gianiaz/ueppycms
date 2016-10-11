{***************|
{** v .1.00   **|
{***************|
{** CHANGELOG **|
{**************************************************************************************************|
{** v. 1.00 (24/05/2016)                                                                         **|
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

{* LISTA DEI BACKUP EFFETTUATI *}
{if $act eq "list_archives"}
  <div class="dataTable_wrapper">
    <table class="table table-striped table-bordered table-hover" id="dataTable" data-backup_id="{$selezione}">
    </table>
  </div>
  <div id="modal">
    <form name="ajaxForm2" id="ajaxForm2" method="post" action="{make_url params="cmd/$cmd/act/upload" lang=$ACTUAL_LANGUAGE}" enctype="multipart/form-data">
      <input type="hidden" name="id" id="id" value="{$selezione}"/>

      {* FTP USER *}
      {ueppy_form_field required=false inp_id=backup_file inp_name=backup_file etichetta="$module_name.BACKUP_FILE" help=true type="file" inp_value=""}

    </form>
  </div>
{/if}

{* LISTA DEI PROFILI FTP PRESENTI *}
{if $act eq "profiliftp"}
  <div class="dataTable_wrapper">
    <table class="table table-striped table-bordered table-hover" id="dataTable">
    </table>
  </div>
  <div id="modal">

    <form name="ajaxForm2" id="ajaxForm2" method="post" action="{make_url params="cmd/$cmd/act/save_profile" lang=$ACTUAL_LANGUAGE}">

      <input type="hidden" name="id" id="id" value=""/>

      <div class="row">
        <div class="col-lg-12">
          {* NOME PROFILO *}
          {ueppy_form_field required=true inp_id=profile_name inp_name=profile_name etichetta="$module_name.PROFILE_NAME" help=true type="text" inp_value="`$Obj->fields.profile_name`"}
        </div>
      </div>
      <div class="row">
        <div class="col-lg-6">
          {* FTP USER *}
          {ueppy_form_field required=false inp_id=ftp_user inp_name=ftp_user etichetta="$module_name.FTP_USER" help=true type="text" inp_value="`$Obj->fields.ftp_user`"}
        </div>
        <div class="col-lg-6">
          {* FTP PASSWORD *}
          {ueppy_form_field required=false inp_id=ftp_pwd inp_name=ftp_pwd etichetta="$module_name.FTP_PWD" help=true type="text" inp_value="`$Obj->fields.ftp_pwd`"}
        </div>
      </div>
      <div class="row">
        <div class="col-lg-6">
          {* FTP HOST *}
          {ueppy_form_field required=false inp_id=ftp_ip inp_name=ftp_ip etichetta="$module_name.FTP_IP" help=true type="text" inp_value="`$Obj->fields.ftp_ip`"}
        </div>
        <div class="col-lg-6">
          {* FTP WD *}
          {ueppy_form_field required=false inp_id=ftp_wd inp_name=ftp_wd etichetta="$module_name.FTP_WD" help=true type="text" inp_value="`$Obj->fields.ftp_wd`"}
        </div>
      </div>

      <div class="row">
        <div class="col-lg-12">
          {* EMAIL *}
          {ueppy_form_field required=false inp_id=email inp_name=email etichetta="$module_name.EMAIL" help=true type="text" inp_value="`$Obj->fields.email`"}
        </div>
      </div>
    </form>

  </div>
{/if}

{if $act eq "new"}
  <form name="ajaxForm" id="ajaxForm" method="post" action="{make_url params="cmd/$cmd/act/insert"}">

    <input type="hidden" name="id" id="id" value="{$Obj->fields.id}"/>

    <div class="row">
      <div class="col-lg-4">
        {* NOME *}
        {ueppy_form_field required=true inp_id=nome inp_name=nome etichetta="$module_name.NOME" help=true type="text" inp_value="`$Obj->fields.nome`"}
      </div>
      <div class="col-lg-4">
        {* SELECT PROFILI EMAIL *}
        {ueppy_form_field required=false inp_name=email inp_id=email etichetta="$module_name.PROFILO_EMAIL" help=true type="select" inp_options=$profili_email inp_value="`$Obj->fields.email`"}
        <div class="clear"></div>
      </div>
      <div class="col-lg-4">
        {* SELECT PROFILI FTP *}
        {ueppy_form_field required=false inp_name=ftp inp_id=ftp etichetta="$module_name.PROFILO_FTP" help=true type="select" inp_options=$profili_ftp inp_value="`$Obj->fields.ftp`"}
        <div class="clear"></div>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-3">
        {* SELECT SCELTA DIRECTORIES *}
        {ueppy_form_field required=false inp_name=directories_all inp_id=directories_all etichetta="$module_name.SCELTA_DIRECTORY" help=true type="select" inp_options=$filtri_attivi inp_value=$Obj->additionalData.directories_all}
      </div>
      <div class="col-lg-9 selezione_directory">
        {ueppy_form_field help=true lbl_class="radiolabel" etichetta="$module_name.DIRECTORIES" type="checkbox" inp_options=$directories inp_value=$Obj->additionalData.directories}
      </div>
      <div class="clearfix"></div>
    </div>

    <div class="row">
      <div class="col-lg-3">
        {* SELECT SCELTA TABELLE *}
        {ueppy_form_field required=false inp_name=tabelle_all inp_id=tabelle_all etichetta="$module_name.SCELTA_TABELLE" help=true type="select" inp_options=$filtri_attivi inp_value="`$Obj->additionalData.tabelle_all`"}
      </div>
      <div class="col-lg-9 selezione_tabelle">
        {ueppy_form_field help=true lbl_class="radiolabel" etichetta="$module_name.TABELLE" type="checkbox" inp_options=$tabelle inp_value=$Obj->additionalData.tabelle}
      </div>
      <div class="clearfix"></div>
    </div>

    <div class="row">
      <div class="col-lg-3">
        {* SELECT SCELTA CRON *}
        {ueppy_form_field required=false inp_name=cron inp_id=cron etichetta="$module_name.CRON_ATTIVO" help=true type="select" inp_options=$cron_attivo inp_value="`$Obj->fields.cron`"}
      </div>
      <div class="col-lg-3">
        {* CRON_H *}
        {ueppy_form_field required=false inp_id=cron_h inp_name=cron_h etichetta="$module_name.CRON_H" help=true type="text" inp_value="`$Obj->fields.cron_h`"}
      </div>
      <div class="col-lg-3">
        {* CRON_DOM *}
        {ueppy_form_field required=false inp_id=cron_dom inp_name=cron_dom etichetta="$module_name.CRON_DOM" help=true type="text" inp_value="`$Obj->fields.cron_dom`"}
      </div>
      <div class="col-lg-3">
        {* CRON_DOW *}
        {ueppy_form_field required=false inp_id=cron_dow inp_name=cron_dow etichetta="$module_name.CRON_DOW" help=true type="text" inp_value="`$Obj->fields.cron_dow`"}
      </div>
    </div>

    {include file="generic/snippets/footer-buttons.tpl"}

  </form>
{/if}