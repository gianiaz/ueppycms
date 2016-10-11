{if $JS.dist}
  {* IL JAVASCRIPT COMPRESSO *}
  {foreach item=javascriptFile from=$JS.dist}<script type="text/javascript" src="{$javascriptFile}"></script>
  {/foreach}
{else}
  {* IL JAVASCRIPT NON COMPRESSO *}
  {foreach item=javascriptFile from=$JS.generali}<script type="text/javascript" src="{$javascriptFile}"></script>
  {/foreach}
  {* IL JAVASCRIPT CHE NON SARA MAI COMPRESSO *}
  {foreach item=javascriptFile from=$JS.pagina}<script type="text/javascript" src="{$javascriptFile}"></script>
  {/foreach}
{/if}

{if $SUPERADMIN}
  {$debugBarRenderer->render()}
{/if}

</body>
</html>