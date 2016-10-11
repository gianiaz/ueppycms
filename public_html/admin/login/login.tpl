<div class="container text-center title">
  <p>{getLang module=$module_name key="ESEGUI_LOGIN"}</p>
</div>
<div class="container">
  <div class="center-block form">
    <img src="/images/logo.png" alt="./" class="center-block img-responsive">
    <form role="form" method="post" action="{make_url params="cmd/login/act/entra"}" data-category="{$SETTINGS.CATEGORIA_SITO}">
      <div class="form-group">
        <input type="text" required class="form-control" name="user" placeholder="{getLang module=$module_name key=USERNAME}">
      </div>
      <div class="form-group">
        <input type="password" required class="form-control" placeholder="{getLang module=$module_name key=PASSWORD}" name="password">
      </div>
      <div class="form-group">
        <input class="magic-checkbox" type="checkbox" name="remember" value="1" id="remember">
        <label for="remember">{getLang module="login" key="REMEMBER"}</label>
      </div>
      <button type="submit" class="btn btn-default form-control">{getLang module="login" key="LOGIN"}</button>
    </form>
    <a href="http://www.ueppy.com" target="_blank"><img src="/images/ueppy.png" alt="Ueppy CMS" class="center-block img-responsive"></a>
  </div>
</div>
<div class="footer">
  <div class="container">
    <div class="col-md-8">
      <p>&copy; Copyright 2016 Ueppy S.r.l. - Via Farnese 16, Pico
        <br><a href="http://www.ueppy.com/contattaci/" target="_blank">Ueppy.com</a>
    </div>
    <div class="col-md-2">
      {* <img src="/images/info.png" alt="./" class="img-responsive pull-right info"> *}
    </div>
    <div class="col-md-2">
      <img src="/images/phone.png" alt="./" class="img-responsive">
    </div>
  </div>
</div>
