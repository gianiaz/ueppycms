{***************|
{** v.1.00    **|
{***************|
{** CHANGELOG **|
{**************************************************************************************************|
{** v.1.00 (06/07/16, 16.48)                                                                     **|
{**************************************************************************************************|
{** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **|
{** copyright: Ueppy s.r.l                                                                       **|
{**************************************************************************************************}

</div>
{foreach item=javascriptFile from=$JS.generali} {* IL JAVASCRIPT NON COMPRESSO *}
  <script type="text/javascript" src="{$javascriptFile}"></script>{/foreach}
{foreach item=javascriptFile from=$JS.pagina} {* IL JAVASCRIPT CHE NON SARA MAI COMPRESSO *}
  <script type="text/javascript" src="{$javascriptFile}"></script>{/foreach}

</body>
</html>
