{**********************}
{*/***ueppy3.1.0.0***/*}
{**********************}
{**     CHANGELOG    **}
{*************************************************************}
{** v.3.1.00                                                **}
{** - Versione stabile                                      **}
{**                                                         **}
{*************************************************************}
{** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com> **}
{** copyright: Ueppy s.r.l                                  **}
{*************************************************************}

{*---------------------------*}
{* LISTA DEI RECORD PRESENTI *}
{*---------------------------*}

{if $act eq ""}
  <form name="ajaxForm" id="ajaxForm"
        action="{make_url params="cmd/$cmd/act/insert"}"
        method="post">

    <textarea id="js" name="jsCode">{$testo}</textarea>

    {include file="generic/snippets/footer-buttons.tpl"}
  </form>
  <hr class="previewSep"/>
  <h2>{getLang module=$module_name key="PREVIEW"}</h2>
  <hr class="previewSep"/>
  <iframe id="preview" src="{$base_href}"></iframe>
{/if}