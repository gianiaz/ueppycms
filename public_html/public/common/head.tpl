<!DOCTYPE html>
<html lang="{$ACTUAL_LANGUAGE}">
<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="{$DESCRIPTION}" />

  <title>{$TITLE}</title>
  <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
  <meta name="robots" content="index,follow" />

  {foreach item=foglioDiStile from=$CSS.generali}
    <link rel="stylesheet" type="text/css" href="{$foglioDiStile}" />
  {/foreach}
  {foreach item=foglioDiStile from=$CSS.pagina}
    <link rel="stylesheet" type="text/css" href="{$foglioDiStile}" />
  {/foreach}

  {if $openGraphData}
    {foreach item=value key=type from=$openGraphData}
      <meta property="og:{$type}" content="{$value}" />
    {/foreach}

  {/if}

  {if $RSS}
    <link rel="alternate" type="application/rss+xml" href="{$RSS}" />{/if}
  {if $SETTINGS.GOOGLE_SITE_VERIFICATION}
    <meta name="google-site-verification" content="{$SETTINGS.GOOGLE_SITE_VERIFICATION}" />{/if}
  {if $SUPERADMIN}
    {$debugBarRenderer->renderHead()}
  {/if}
  {if $SETTINGS.IUBENDA_SITE_ID && $IUBENDA_POLICY_ID}
    <script type="text/javascript">
      var iubendaSiteId = {$SETTINGS.IUBENDA_SITE_ID};
      var iubendaPolicyId = {$IUBENDA_POLICY_ID};
    </script>
  {/if}
  {if $SETTINGS.URCHIN_GOOGLE_CODE}
    <script>
      {literal}
      (function(i, s, o, g, r, a, m) {
        i['GoogleAnalyticsObject'] = r;
        i[r] = i[r] || function() {
                  (i[r].q = i[r].q || []).push(arguments)
                }, i[r].l = 1 * new Date();
        a = s.createElement(o),
                m = s.getElementsByTagName(o)[0];
        a.async = 1;
        a.src = g;
        m.parentNode.insertBefore(a, m)
      })(window, document, 'script', 'https://www.google-analytics.com/analytics.js', 'ga');
      {/literal}
      ga('create', '{$SETTINGS.URCHIN_GOOGLE_CODE}', 'auto');
      {if $SETTINGS.ENABLE_ECOMMERCE}
      ga('require', 'ec');
      ga('set', '&cu', 'EUR');
      {/if}
      ga('set', 'anonymizeIp', true)
      ga('send', 'pageview');
    </script>
  {/if}
</head>
<body>