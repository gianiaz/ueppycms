{***************|
{** v.1.00    **|
{***************|
{** CHANGELOG **|
{**************************************************************************************************|
{** v.1.00 (19/05/16, 16.23)                                                                     **|
{**************************************************************************************************|
{** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **|
{** copyright: Ueppy s.r.l                                                                       **|
{**************************************************************************************************}
<div class="divider"></div>
{foreach item=button from=$footerButtons}
  <a
  {foreach key=attribute item=attribute_value from=$button.attributes}{$attribute}="{$attribute_value}"{/foreach}>
  {if $button.icon}<i class="fa fa-{$button.icon}"></i> {/if}{if $button.text}{$button.text}{/if}{if $button.icon2}
<i class="fa fa-{$button.icon2}"></i>{/if}
  </a>
{/foreach}
