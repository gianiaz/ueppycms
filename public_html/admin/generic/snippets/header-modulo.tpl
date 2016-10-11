{***************|
{** v.1.00    **|
{***************|
{** CHANGELOG **|
{**************************************************************************************************|
{** v.1.00 (19/05/16, 15.33)                                                                     **|
{**************************************************************************************************|
{** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **|
{** copyright: Ueppy s.r.l                                                                       **|
{**************************************************************************************************}
<div id="page-wrapper">
  <div class="row">
    <div class="col-lg-12">
      <div class="page-header">
        <div class="actions">
          {foreach item=button from=$headerButtons}
            <a
            {foreach key=attribute item=attribute_value from=$button.attributes}{$attribute}="{$attribute_value}"{/foreach}>
            {if $button.icon}<i class="fa fa-{$button.icon}"></i> {/if}{if $button.text}{$button.text}{/if}{if $button.icon2} <i class="fa fa-{$button.icon2}"></i>{/if}
            </a>
          {/foreach}
        </div>
        <h1 class="titleModule text-brand">{$titoloSezione}</h1>
        <div class="clearfix"></div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="panel panel-default">
      <div class="panel-body">
