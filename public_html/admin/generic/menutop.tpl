<nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
  <div class="navbar-header">
    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
      <span class="sr-only">Toggle navigation</span>
      <span class="icon-bar"></span>
      <span class="icon-bar"></span>
      <span class="icon-bar"></span>
    </button>
    <img id="logoadm" src="/images/site/logo-admin.png"/>
  </div>
  <ul class="nav navbar-top-links navbar-right">
    <li class="dropdown">
      <a href="#" id="about">
        <i class="fa fa-info fa-fw"></i>
      </a>
    </li>
    <li class="dropdown">
      <a class="dropdown-toggle" data-toggle="dropdown" href="#">
        <i class="fa fa-user fa-fw"></i> <i class="fa fa-caret-down"></i>
      </a>
      <ul class="dropdown-menu dropdown-user">
        <li><a href="/admin/profilo/"><i class="fa fa-user fa-fw"></i> {getLang module="operatori" key="PROFILO"}</a>
        </li>
        {if $realOperator}
          <li>
            <a href="{make_url params="cmd/oldpriv" lang=$ACTUAL_LANGUAGE}"><i
                      class="fa fa-arrow-circle-o-left"></i> {getLang module=default key="RITORNA"} {$realOperator}</a>
          </li>
        {else}
          <li class="divider"></li>
          <li>
            <a href="{make_url params="cmd/logout" lang=$ACTUAL_LANGUAGE}">
              <i class="fa fa-sign-out fa-fw"></i> Logout [{$operator->nomecompleto}]
            </a>
          </li>
        {/if}
      </ul>
    </li>
  </ul>

  <div class="navbar-default sidebar" role="navigation">
    <div class="sidebar-nav navbar-collapse">
      <ul class="nav" id="side-menu">
        <li class="sidebar-search">
          <div class="input-group custom-search-form">
            <input type="text" class="form-control" placeholder="{getLang module="default" key=CERCA}">
                                <span class="input-group-btn">
                                <button id="srcMenu" class="btn btn-default" type="button">
                                  <i class="fa fa-search"></i>
                                </button>
                            </span>
          </div>
        </li>

        <li><a href="{make_url params=""}"><i class="fa fa-home"></i>&nbsp;Home</a></li>

        {foreach item=ramo from=$menu}
          <li>
            <a href="#"><i
                      class="fa fa-{$ramo->fields.$ACTUAL_LANGUAGE.keywords} fa-fw"></i>{*{$ramo->fields.id}*} {$ramo->fields.$ACTUAL_LANGUAGE.dicitura}
              <span
                      class="fa arrow"></span></a>
            {if $ramo->additionalData.childs}
              <ul class="nav nav-second-level">
                {foreach item=nodo name="childs" from=$ramo->additionalData.childs}
                  <li>
                    <a href="{make_url params="cmd/`$nodo->fields.nomefile`"}"><i
                              class="fa fa-{$nodo->fields.$ACTUAL_LANGUAGE.keywords}"></i>{*{$nodo->fields.id}*} {$nodo->fields.$ACTUAL_LANGUAGE.dicitura}
                    </a>
                  </li>
                {/foreach}
              </ul>
            {/if}
          </li>
        {/foreach}
      </ul>
    </div>

    <div class="panel panel-default">
      <div class="panel-heading">{getLang module="default" key="BENVENUTO"} {$operator->nomecompleto}</div>
      <div class="panel-body">
        <ul class="list-group">
          <li class="list-group-item">{getLang module="default" key="SITO"} {$smarty.server.SERVER_NAME}</li>
          <li class="list-group-item">{$today}</li>
          <li class="list-group-item" id="clockContainer"></li>
        </ul>
      </div>
    </div>
  </div>
</nav>
