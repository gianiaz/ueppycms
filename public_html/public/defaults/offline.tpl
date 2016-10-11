<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <meta http-equiv="Content-Language" content="it"/>
  <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sito in manutenzione</title>
  <link href="/public/defaults/bootstrap.min.css" rel="stylesheet" type="text/css"/>

  <!--[if lt IE 9]>
  <script src="js/html5shiv.min.js"></script>
  <script src="js/respond.min.js"></script>
  <![endif]-->

  <link href="https://fonts.googleapis.com/css?family=Raleway:400,600" rel="stylesheet" type="text/css"/>
  <link rel="stylesheet" href="/public/defaults/offline.css" type="text/css"/>
  <script src="/admin/generic/dist.min.js" type="text/javascript"></script>

  <script>
    {literal}
    $(function() {
      var category = $('body').data('category');
      var url = 'http://4.ueppybox.com/bg-login.jpg?category=' + category + '&r=' + Math.random() + '\'';
      $('body').css({'background-image': 'url("' + url + '")'});
    });
    {/literal}
  </script>

</head>

<body data-category="{$SETTINGS.CATEGORIA_SITO}">
<div class="container text-center title">
</div>
<div class="container">
  <div class="center-block form offli">
    <p class="text-center">{getLang module="default" key="MESSAGGE_OFFLINE_HTML" htmlallowed=1}</p>
    <hr>
    <a href="http://www.ueppy.com" target="_blank"><img src="/images/ueppy-o.png" alt="Ueppy CMS" class="img-responsive center-block"></a>
  </div>
</div>
<div class="footer">
  <div class="container">
    <div class="col-md-8">
      <p>&copy; Copyright 2016 Ueppy S.r.l. - Via Farnese 16, Pico
        <br>
        <a target="_blank" href="http://www.ueppy.com/contattaci/">Ueppy.com</a></p>
    </div>
    <div class="col-md-2">
    </div>
    <div class="col-md-2">
      <img src="/images/phone.png" alt="./" class="img-responsive">
    </div>
  </div>
</div>
</body>
</html>