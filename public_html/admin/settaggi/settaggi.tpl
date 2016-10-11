{*****************}
{***ueppy3.1.00***}
{*****************}
{**  CHANGELOG  **}
{**************************************************************************************************}
{** v.3.1.00 (01/01/2013)                                                                        **}
{** - Versione stabile                                                                           **}
{**                                                                                              **}
{**************************************************************************************************}
{** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **}
{** copyright: Ueppy s.r.l                                                                       **}
{**************************************************************************************************}
{if $act eq ""}
  <form name="ajaxForm" id="ajaxForm" method="post"
        action="{make_url params="cmd/$cmd/act/save"}">

    <div class="row">

      <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
        {foreach item="grp" key=id_gruppo name="iterGRP" from=$gruppi}
          {if count($settings.$id_gruppo)}
            <div class="panel panel-default">
              <div class="panel-heading" role="tab" id="headingOne">
                <span>{$grp.descrizione}</span>
                <h4 class="panel-title">
                  <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse{$id_gruppo}"
                     aria-expanded="true"
                     aria-controls="collapse{$id_gruppo}">
                    {$grp.nome}
                  </a>
                </h4>
              </div>

              <div id="collapse{$id_gruppo}"
                   class="panel-collapse collapse"
                   role="tabpanel" aria-labelledby="heading{$id_gruppo}">
                <div class="panel-body">
                  {foreach item=settaggio from=$settings.$id_gruppo}
                    {if $operator->isGod()}
                      <a class="btn btn-primary editbtn" id="e{$settaggio.id}"><i class="fa fa-pencil"></i></a>
                      <a class="btn btn-danger delbtn" id="d{$settaggio.id}"><i class="fa fa-trash"></i></a>
                    {/if}
                    <div class="form-group">
                      <label>{$settaggio.chiave_ext}</label>
                      {if $settaggio.type eq "text"}
                        <input class="form-control" id="{$settaggio.chiave}" type="text"
                               name="{$settaggio.chiave}"
                               value="{$settaggio.valore}"/>
                      {else}
                        <input id="{$settaggio.chiave}" type="checkbox" value="1"
                               name="{$settaggio.chiave}"{if $settaggio.valore} checked="checked"{/if}/>
                      {/if}
                      <p class="help-block">{$settaggio.descrizione}</p>
                    </div>
                  {/foreach}
                </div>
              </div>
            </div>
          {/if}
        {/foreach}
      </div>
    </div>

    {include file="generic/snippets/footer-buttons.tpl"}

  </form>
  <script type="text/x-handlebars-template" id="form">

    <form name="ajaxForm2" id="ajaxForm2" method="post"
          action="{make_url params="cmd/$cmd/act/save_key"}">
      <input type="hidden" name="id_entry" id="id_entry" value=""/>

      {* SELECT ATTIVAZIONE *}
      {ueppy_form_field required=false inp_name=gruppo inp_id=gruppo etichetta="$module_name.GRUPPO" help=true type="select" inp_options=$gruppi_opt inp_value=""}
      <div class="clear"></div>

      {* TIPO INPUT *}
      {ueppy_form_field required=false inp_name=type inp_id=type etichetta="$module_name.TYPE" help=true type="select" inp_options=$tipo_input inp_value=""}
      <div class="clear"></div>

      {* CHIAVE *}
      {ueppy_form_field required=true inp_name=chiave inp_class="restricted" inp_rel="[A-Z]|_" inp_id=chiave etichetta="$module_name.CHIAVE" help=true type="text" inp_value=""}

      {* CHIAVE_EXT *}
      {ueppy_form_field required=true inp_name=chiave_ext inp_id=chiave_ext etichetta="$module_name.CHIAVE_EXT" help=true type="text" inp_value=""}

      {* VALORE TESTUALE *}
      {ueppy_form_field required=true inp_name=valore_testuale inp_id=valore_testuale etichetta="$module_name.VALORE" help=true type="text" inp_value="" container="div#testuale.form-group"}

      {* valore_booleano *}
      {ueppy_form_field required=false inp_name=valore_booleano etichetta="$module_name.VALORE_BOOLEANO" help=true type="select" inp_options=$val_options inp_value=""  container="div#booleano.form-group"}

      {* DESCRIZIONE *}
      {ueppy_form_field required=true inp_name=descrizione inp_id=descrizione etichetta="$module_name.DESCRIZIONE" help=true type="textarea" inp_value=""}

      {* CHECKBOX SUPERADMIN *}
      {ueppy_form_field help=true  etichetta="$module_name.SUPER_ADMIN" type="checkbox" inp_options=$sa_options inp_value=""}
      <div class="clear"></div>
    </form>
  </script>
{/if}