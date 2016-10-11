/*****************/
/***ueppy3.1.00***/
/*****************/
/**  CHANGELOG  **/
/**************************************************************************************************/
/** v.3.1.00 (03/03/15, 10.50)                                                                   **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/

Utility = {};

Utility.log = function(data, options) {

  var opts = {
    level     : 'info',
    trace     : false,
    groupTitle: 'TRACE'
  };

  if(typeof(options) !== 'undefined') {
    opts = $.extend({}, opts, options);
  }

  if(debug && typeof(console) != 'undefined') {

    if(opts.trace) {
      console.group(opts.groupTitle);
      console.trace();
    }

    switch(opts.level) {
      case 'error':
        console.error(data);
        break;
      case 'warn':
        console.warn(data);
        break;
      default:
        console.info(data);

    }
    if(opts.trace) {
      console.groupEnd();
    }
  }
}

Utility.getLang = function(module, key) {
  if(typeof(Lang[module]) == 'undefined') {
    var str = 'Lang Module ' + module + ' not found (' + module + "." + key + ')';
    Utility.log(str, {'level': 'error'});
    Utility.createLangEntry(module, key);
    return module + "." + key;
  } else {
    if(typeof(Lang[module][key]) == 'undefined') {
      var str = 'Key ' + key + ' not found in module ' + module;
      Utility.log(str, {'level': 'error'});
      Utility.createLangEntry(module, key);
      return module + "." + key;
    } else {
      if(Lang[module][key] != '') {
        return Lang[module][key];
      } else {
        return module + "." + key;
      }
    }
  }

}

Utility.createLangEntry = function(module, key) {

  var data = {
    module: module,
    key   : key
  };

  $.ajax({
    type    : "POST",
    url     : main_host + "ajax/GENERIC.CREATEJSLANGENTRY",
    data    : data,
    dataType: "json"
  });

}

Utility.templateCache = {};
Utility.template = function(options) {

  var opts = {
    name           : '',
    jqObj          : false,
    path           : '',
    callback       : '',
    elem           : false,
    typeofinjection: 'html', // append, prepend, html
    data           : false
  };

  opts = $.extend({}, opts, options);

  if(typeof(Handlebars) == 'undefined') {
    alert('Handlebars non incluso, includere il file /public_html/lib/handlebars.js');
  } else {
    if(typeof(Utility.templateCache[opts.name]) == 'undefined') {
      if(opts.jqObj) {
        Utility.templateCache[opts.name] = Handlebars.compile(opts.jqObj.text());
        var mixedReturn = Utility.templateCache[opts.name];
        if(opts.data) {
          mixedReturn = Utility.templateCache[opts.name](opts.data);
        }
        return mixedReturn;
      } else {
        $.ajax({
          dataType: 'html',
          method  : 'GET',
          url     : opts.path + opts.name + '.jstpl'
        }).done(function(data) {
          Utility.templateCache[opts.name] = Handlebars.compile(data);
          if(opts.data) {
            markup = Utility.templateCache[opts.name](opts.data);
            if(opts.elem) {
              switch(opts.typeofinjection) {
                case 'replace':
                  opts.elem.replaceWith(markup);
                  break;
                case 'html':
                  opts.elem.html(markup);
                  break;
                case 'append':
                  $(markup).appendTo(opts.elem);
                  break;
                case 'prepend':
                  $(markup).prependTo(opts.elem);
                  break;
              }
              if(opts.callback) {
                opts.callback(markup);
              }
            } else {
              if(opts.callback) {
                opts.callback(markup);
              }
            }
          } else {
            if(opts.callback) {
              opts.callback(Utility.templateCache[opts.name]);
            }
          }
        });
      }
    } else {
      if(opts.data) {
        markup = Utility.templateCache[opts.name](opts.data);
        if(opts.elem) {
          switch(opts.typeofinjection) {
            case 'replace':
              opts.elem.replaceWith(markup);
              break;
            case 'html':
              opts.elem.html(markup);
              break;
            case 'append':
              $(markup).appendTo(opts.elem);
              break;
            case 'prepend':
              $(markup).prependTo(opts.elem);
              break;
          }
          if(opts.callback) {
            opts.callback(markup);
          }
        } else {
          if(opts.jqObj) {
            //Utility.log(opts.data, {trace: true});
            return markup;
          } else {
            if(opts.callback) {
              if(opts.data) {
                opts.callback(Utility.templateCache[opts.name](opts.data));
              } else {
                opts.callback(Utility.templateCache[opts.name]);
              }
            }
          }
        }
      } else {
        if(opts.jqObj) {
          return Utility.templateCache[opts.name];
        } else {
          if(opts.callback) {
            opts.callback(Utility.templateCache[opts.name]);
          }
        }
      }
    }
  }
};

Utility.alert = function(options) {
  var opts = {
    title   : Utility.getLang('base', 'WARNING'),
    message : 'Messaggio vuoto',
    size    : '',
    type    : 'danger',
    btnlbl  : 'OK',
    callback: false
  };

  opts = $.extend({}, opts, options);

  opts.message = html_entity_decode(opts.message, 'ENT_QUOTES', 'UTF-8');

  var opzioniTemplateCallback = function(markup) {
    //Utility.log(markup, {'trace': true});
    var $modal = $(markup);
    $modal.appendTo($('body'));
    $modal.modal('show');
    $modal.on('hidden.bs.modal', function(e) {
      $modal.remove();
      if(typeof opts.callback === 'function') {
        opts.callback();
      }
    });
  }

  if(opts.size) {
    opts.size = ' ' + opts.size;
  }

  var data = {
    'title'  : opts.title,
    'message': opts.message,
    'close'  : opts.btnlbl,
    'type'   : opts.type,
    'size'   : opts.size
  };

  if(typeof($('<div></div>').modal) != 'undefined') {
    var opzioniTemplate = {
      name    : 'alert',
      path    : main_host + '/public/common/',
      callback: opzioniTemplateCallback,
      data    : data
    };

    Utility.template(opzioniTemplate);
  } else {
    alert('C\'è qualcosa che non va con bootstrap...:' + "\n\n" + opts.message);
  }

}


Utility.getUrlParam = function(strParamNumber, rel_root) {

  href = location.href.replace(location.protocol + '//' + location.host, '');

  if(href.charAt(0) == '/') {
    href = href.substr(1);
  }

  if(href.charAt(href.length - 1) == '/') {
    href = href.substr(0, href.length - 1);
  }

  href = href.split('/');

  strParamNumber = parseInt(strParamNumber, 10);

  if(href[0] == 'admin') {
    href.shift();
  }

  if(typeof(href[strParamNumber]) == 'undefined') {
    return null;
  } else {
    return href[strParamNumber];
  }

  return href[strParam];

}

Utility.lockScreenPre = function() {

}

Utility.unlockScreenPost = function() {

}

Utility.lockScreen = function(options) {

  var opts = {
    message       : 'Messaggio vuoto',
    zindex        : 1000,
    bgcolor       : '#000000',
    opacity       : '0.6',
    messageWidth  : '50%',
    textColor     : false,
    classes       : 'alert alert-info',
    messageBgColor: 'trasparent',
    type          : 'wait',
    icons         : ['fa-refresh fa-spin'],
  };

  var setOption = function(key, value) {
    opts[key] = value;
  }

  Utility.unlockScreen();


  opts = $.extend({}, opts, options);

  $('body').css({'overflow': 'hidden'});

  $('<div class="overlay"></div>').css({
    'position'        : 'absolute',
    'background-color': opts.bgcolor,
    'opacity'         : opts.opacity,
    'width'           : '100%',
    'height'          : '100%',
    'top'             : $(window).scrollTop(),
    'left'            : 0,
    'z-index'         : opts.zindex
  }).appendTo('body');


  var m = '<p>';
  for(var i = 0; i < opts.icons.length; i++) {
    var icon = opts.icons[i];
    m += '<i class= "fa ' + icon + '" style = "margin-right:20px;"></i>';
  }
  m += opts.message + '</p>';

  $('<div class="message">' + m + '</div>').appendTo('body');

  var $message = $('.message');

  $message.css({'width': opts.messageWidth});
  if(opts.classes) {
    $message.addClass(opts.classes);
  }

  var wW = $(window).width();
  var mW = $message.width();

  var wH = $(window).height();
  var mH = $message.height();

  $message.css({
    'position'        : 'absolute',
    'text-align'      : 'center',
    'background-color': opts.messageBgColor,
    'left'            : (wW - mW) / 2,
    'top'             : (wH - mH) / 2 + $(window).scrollTop(),
    //'top'             : (wH - mH) / 2,
    'z-index'         : parseInt(opts.zindex, 10) + 1
  });

  if(opts.textColor) {
    $message.css({
      'color': opts.textColor
    });
  }
  Utility.lockScreenPre();
}


Utility.unlockScreen = function() {
  $('.message').remove();
  $('.overlay').remove();
  $('body').css({'overflow': 'auto'});
  Utility.unlockScreenPost();
}

Utility.leadingZeros = function(numero) {
  numero = parseInt(numero, 10);
  return str_pad(numero, 2, '0', 'STR_PAD_LEFT');
}

Utility.loadScript = function(url, callback) {

  var script = document.createElement("script")
  script.type = "text/javascript";

  if(script.readyState) {  //IE
    script.onreadystatechange = function() {
      if(script.readyState == "loaded" ||
        script.readyState == "complete") {
        script.onreadystatechange = null;
        if(typeof(callback) == 'function') {
          callback();
        }
      }
    };
  } else {  //Others
    script.onload = function() {
      if(typeof(callback) == 'function') {
        callback();
      }
    };
  }

  script.src = url;
  document.getElementsByTagName("head")[0].appendChild(script);
}

Utility.formattaValuta = function(val, options) {

  var opts = {
    DEC_POINT    : Utility.getLang('base', 'DEC_POINT'),
    THOUSANDS_SEP: Utility.getLang('base', 'THOUSANDS_SEP'),
    showThousands: false,
    decimals     : 2
  };

  opts = $.extend({}, opts, options);

  return number_format(val, opts.decimals, opts.DEC_POINT, (opts.showThousands) ? opts.THOUSANDS_SEP : '');

}


Utility.confirm = function(options) {

  var opts = {
    title    : Utility.getLang('base', 'WARNING'),
    message  : 'Messaggio vuoto',
    okLbl    : Utility.getLang('base', 'OK'),
    cancelLbl: Utility.getLang('base', 'CANCEL'),
    size     : '', // modal-lg
    type     : 'danger',
    onOk     : false,
    onCancel : false
  };

  opts = $.extend({}, opts, options);

  opts.message = html_entity_decode(opts.message, 'ENT_QUOTES', 'UTF-8');

  var opzioniTemplateCallback = function(markup) {
    var $modal = $(markup);
    $modal.appendTo('body');
    $modal.modal({
      backdrop: 'static',
      keyboard: false
    });
    $modal.modal('show');
    $modal.on('hidden.bs.modal', function(e) {
      $(this).data('bs.modal', null);
      /*
      if(typeof opts.onCancel === 'function') {
        opts.onCancel($modal);
      }*/
    });

    $modal.find('.btn-confirm').on('click', function(e) {
      $modal.modal('hide');
      $(this).data('bs.modal', null);
      if(typeof opts.onOk === 'function') {
        opts.onOk($modal);
      }
    });

    $modal.find('.btn-cancel').on('click', function(e) {
      if(typeof opts.onCancel === 'function') {
        opts.onCancel($modal);
      }
    });
  }

  if(opts.size) {
    opts.size = ' ' + opts.size;
  }

  var data = {
    'title'  : opts.title,
    'message': opts.message,
    'CANCEL' : opts.cancelLbl,
    'OK'     : opts.okLbl,
    'size'   : opts.size,
    'type'   : opts.type
  };

  var opzioniTemplate = {
    name    : 'confirm',
    path    : main_host + '/public/common/',
    callback: opzioniTemplateCallback,
    data    : data
  };

  Utility.template(opzioniTemplate);

}

Utility.modalForm = function(options) {

  var opts = {
    TITLE   : '[NON SETTATO]',
    SAVE    : Utility.getLang('base', 'SAVE'),
    CANCEL  : Utility.getLang('base', 'CANCEL'),
    ICON    : 'floppy-o',
    content : '',
    size    : '', // modal-lg modal-sm
    onSave  : false,
    onCancel: false,
    callback: false
  };

  opts = $.extend({}, opts, options);

  if(opts.size) {
    opts.size = ' ' + opts.size;
  }

  var opzioniTemplateCallback = function(markup) {


    var $modal = $(markup);
    if(typeof opts.callback === 'function') {
      $modal.on('shown.bs.modal', function(e) {
        opts.callback($modal);
      });
    }

    $modal.appendTo('body');
    $modal.modal('show');
    $modal.on('hidden.bs.modal', function(e) {
      $(this).data('bs.modal', null);
    });

    $modal.find('.btn-confirm').on('click', function(e) {
      if(typeof opts.onSave === 'function') {
        opts.onSave($modal);
      } else {
        $(this).data('bs.modal', null);
      }
    });

    $modal.find('.btn-cancel').on('click', function(e) {
      if(typeof opts.onCancel === 'function') {
        opts.onCancel($modal);
      } else {
        $(this).modal('hide');
      }
    });


  }

  var data = {
    'TITLE'  : opts.TITLE,
    'SAVE'   : opts.SAVE,
    'CANCEL' : opts.CANCEL,
    'ICON'   : opts.ICON,
    'size'   : opts.size,
    'content': opts.content
  };

  var opzioniTemplate = {
    name    : 'modalForm',
    path    : main_host + '/admin/generic/snippets/',
    callback: opzioniTemplateCallback,
    data    : data
  };

  Utility.template(opzioniTemplate);

}


Utility.postData = function(options) {
  var opts = {
    action: '',
    method: 'post',
    data  : {}
  };

  opts = $.extend({}, opts, options);

  var html = '<form id="postDataForm" method="' + opts.method + '" action="' + opts.action + '">';

  for(var i in opts.data) {
    html += '<input type="hidden" name="' + i + '" value="' + opts.data[i] + '" />';
  }
  html += '</form>';

  $(html).appendTo('body').submit();

}

Utility.tabIndexInit = function() {
  $elementi = $(':input');
  var i = 0
  $elementi.each(function() {
    $(this).attr({'tabindex': i});
    i = i + 1;
  });
  i = $elementi.size()
  $elementi = $('a');
  $elementi.each(function() {
    $(this).attr({'tabindex': i});
    i++;
  });
}


/**
 * Passato l'evento del keyup la funzione verifica se il valore fornito è tra quelli
 * accettati
 *
 * @param event e
 * @param string allowed_chars contiene una regex sulla quale viene confrontato il carattere
 */
Utility.restricted = function(e, allowed_chars) {
  Utility.log('restricted(e, allowed_chars)');
  Utility.log('e:' + e);
  key = (e.charCode) ? e.charCode : e.which;

  Utility.log("key_code:" + key);

  if((key == null) || (key == 0) || (key == 8) || (key == 9) || (key == 13) || (key == 27)) return true;

  keychar = String.fromCharCode(key);
  Utility.log("keychar:" + keychar);

  Utility.log(allowed_chars + '{1}');

  re = new RegExp(allowed_chars + '{1}');

  if(re.test(keychar)) {
    if(debug) Utility.log('OK');
    return true;
  }

  if(debug) Utility.log('KO');
  return false;
}


Utility.isNumeric = function(n) {
  return !isNaN(parseFloat(n)) && isFinite(n);
}

Utility.registerEvent = function(options) {

  var event = {
    'categoria': '',
    'azione'   : '',
    'etichetta': '',
    'valore'   : null
  };

  if(typeof(options) !== 'undefined') {
    event = $.extend({}, event, options);
  }

  Utility.log(event);

  if(typeof(ga) != 'undefined') {

    ga('send', 'event', event.categoria, event.azione, event.etichetta, event.valore);

  } else {
    Utility.log('Non è stato incluso analytics');
  }

}