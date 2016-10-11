<!DOCTYPE html>
<html lang="en">
<head>

  <title>Pannello di controllo: {$titoloSezione}</title>

  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

  {foreach item=foglioDiStile from=$CSS.generali}
    <link rel="stylesheet" type="text/css" href="{$foglioDiStile}"/>
  {/foreach}

  {foreach item=foglioDiStile from=$CSS.pagina}
    <link rel="stylesheet" type="text/css" href="{$foglioDiStile}"/>
  {/foreach}

  <link rel="shortcut icon" href="/favicon.ico"/>

  <script type="text/javascript">
    var lvl = '{$operator->level}';
  </script>

</head>


<body{if $operator} data-superadmin="{$operator->super_admin}" data-level="{$operator->level}" data-god="{$operator->isGod()}"{/if}>


{if $cmd neq "login"}
<div id="wrapper">

  <input type="hidden" id="lingue_json" value='{$lingue_json}'/>
  <input type="hidden" id="ACTUAL_LANGUAGE" value='{$ACTUAL_LANGUAGE}'/>

  {include file="generic/menutop.tpl"}
{/if}