{***************|
{** v.1.00    **|
{***************|
{** CHANGELOG **|
{**************************************************************************************************|
{** v.1.00 (21/06/16, 11.28)                                                                     **|
{**************************************************************************************************|
{** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **|
{** copyright: Ueppy s.r.l                                                                       **|
{**************************************************************************************************}

<form data-readonly="{$readonly}" id="ajaxForm" name="ajaxForm"
      action="{make_url params="cmd/$cmd/act/insert"}"
      method="post"
      enctype="multipart/form-data">

  <div class="row">
    <div class="col-lg-3">
      {ueppy_form_field required=true inp_name=lingue inp_id=lingue etichetta="default.LINGUA_SEO" help=true type="select" inp_options=$lingue inp_value="$ACTUAL_LANGUAGE"}
    </div>
    <div class="col-lg-12" id="dynamic">

    </div>
  </div>


  {include file="generic/snippets/footer-buttons.tpl"}

  <script type="text/x-handlebars" id="grp-template">

    <div class="panel panel-primary">
      <div class="panel-heading">{literal}{{title}}{/literal}</div>
      <div class="panel-body">
        {literal}{{{content}}}{/literal}
      </div>
    </div>

  </script>

  <script type="text/x-handlebars-template" id="meta-template">

    <div class="case">
      <strong>{literal}{{title}}{/literal}</strong>
      <div class="clearfix"></div>

      <div class="col-lg-6">
        <div class="row">
          <div class="form-group">
            <i class="fa fa-question-circle cmsHelp text-info" data-toggle="tooltip" data-placement="right" data-title="{literal}{{HTMLTITLE_HELP}}{/literal}"></i><label class="control-label" for="{literal}{{var}}{/literal}">{literal}{{HTMLTITLE_LABEL}}{/literal}</label>
            <input id="{literal}{{sezione}}{/literal}_{literal}{{act}}{/literal}_htmltitle" name="meta[{literal}{{sezione}}{/literal}][{literal}{{act}}{/literal}][htmltitle]" value="{literal}{{htmltitle}}{/literal}" type="text" class="form-control"/>
          </div>
        </div>
        <div class="row">
          <div class="form-group">
            <i class="fa fa-question-circle cmsHelp text-info" data-toggle="tooltip" data-placement="right" data-title="{literal}{{DESCRIPTION_HELP}}{/literal}"></i><label class="control-label" for="{literal}{{var}}{/literal}">{literal}{{DESCRIPTION_LABEL}}{/literal}</label>
            <input id="{literal}{{sezione}}{/literal}_{literal}{{act}}{/literal}_description" name="meta[{literal}{{sezione}}{/literal}][{literal}{{act}}{/literal}][description]" value="{literal}{{description}}{/literal}" type="text" class="form-control"/>
          </div>
        </div>
      </div>
      <div class="col-lg-4 pulsantiera">
        {literal}{{#each vars}}{/literal}
        <a class="btn btn-primary help" href="{literal}{{this.var}}{/literal}" data-toggle="tooltip" data-placement="top" data-title="{literal}{{this.help}}{/literal}">{literal}{{this.var}}{/literal}</a>
        {literal}{{/each}}{/literal}
      </div>
    </div>
    <div class="clearfix"></div>
  </script>