Ueppy = {};

Ueppy.showFormErrors = function(errors, wrongs) {

  Utility.log(errors);
  Utility.log(wrongs);

  var reLingua = new RegExp('_(' + sigleLingua.join('|') + ')$');

  var errori = [];
  var erroriLingua = {};
  for(var i in lingue) {
    erroriLingua[i] = [];
  }

  for(var i = 0; i < wrongs.length; i++) {
    var wrong = wrongs[i];
    var $formGroup = $('#' + wrong).closest('.form-group');
    $formGroup.addClass('has-error');
    var isLingua = reLingua.exec(wrong);
    if(isLingua) {
      erroriLingua[isLingua[1]].push(errors[wrong]);
    } else {
      errori.push(errors[wrong]);
    }
  }

  var msg = '';
  if(errori.length) {
    for(i in errors) {
      msg += errors[i] + '<br />';
    }
  }
  for(i in erroriLingua) {
    if(erroriLingua[i].length) {
      msg += '<br />';
      msg += '<strong>' + Utility.getLang('base', 'SCHEDA') + ' ' + lingue[i] + ':</strong><br />';
      msg += erroriLingua[i].join('<br />');
    }
  }
  
  PNotify.removeAll();
  new PNotify({
    title: Utility.getLang('base', 'ERRORE_SALVATAGGIO'),
    text : msg,
    type : 'error',
    width: '350px'
  });

}

Ueppy.saveSuccess = function(dati) {

  $('.has-error').removeClass('has-error');

  unsaved = false;

  PNotify.removeAll();

  new PNotify({
    title   : Utility.getLang('base', 'SALVATAGGIO_AVVENUTO'),
    text    : '',
    type    : 'success',
    nonblock: {
      nonblock        : true,
      nonblock_opacity: .2
    }
  });

  $('#allegati').data('id_genitore', dati.id);
  Ueppy.inizializzaPluginAllegati();

  for(var i in dati) {
    if(typeof dati[i] == 'string' || typeof dati[i] == 'number') {
      if($('#' + i).length == 0) {
        Utility.log('$(#' + i + ') non presente', {'level': 'warn', 'trace': true});
      }
      if(!$('#' + i).attr('type') || ($('#' + i).attr('type') != 'password' && $('#' + i).attr('type') != 'file')) {
        $('#' + i).val(dati[i]);
      }
    } else {
      Utility.log(typeof dati[i]);
    }
  }

  // aggiorno le immagini
  if(typeof(dati.immagini) != 'undefined') {
    for(i = 0; i < dati.immagini.length; i++) {
      var img = dati.immagini[i];
      Ueppy.updateImgField(img);
    }
  }

}

Ueppy.updateImgField = function(img) {

  var imgDefault = {
    'field'   : '',
    'imgUrl'  : '',
    'basename': '',
    'lang'    : false
  }

  var img = $.extend({}, imgDefault, img);

  // nome del panel =
  var panelName = 'filePanel' + img.field;
  if(img.lang) {
    panelName += '_' + img.lang;
  }

  var $panel = $(panelName);
  var $fileFeedback = $panel.find('.fileFeedback');
  var $imgTag = $panel.find('.img-responsive');
  var $preview = $panel.find('.preview');
  $fileFeedback.val(img.basename);

  $imgTag.attr('src', img.imgUrl);

  if(img.basename != '') {
    $preview.show();
  } else {
    $preview.hide();
  }

}

//tinymce

Ueppy.tinyMCEOpts = false;

Ueppy.tinyMCEOpts = {};
Ueppy.tinyMCEOpts['0'] = {
  menubar           : false,
  language          : $('#ACTUAL_LANGUAGE').val(),
  language_url      : '/lib/tinymce/langs/it.js',
  width             : '100%',
  element_format    : 'html',
  relative_urls     : false,
  menubar           : false,
  remove_script_host: true,
  image_dimensions  : false,
  height            : 300,
  toolbar1          : "undo redo | bold italic | bullist numlist | link",
  plugins           : 'paste link',
  paste_postprocess : function(plugin, args) {
    var sConf = {
      elements  : [
        'a', 'br', 'em', 'li', 'ol', 'p', 'strong', 'ul', 'span'
      ],
      attributes: {
        '__ALL__': ['class'],
        a        : ['href', 'title', 'target']
      }
    }
    var s = new Sanitize(sConf);
    $(args.node).find('meta').remove();
    $(args.node).html($('<div>').append(s.clean_node(args.node)).html());
    console.log($(args.node).html());
  }
};

Ueppy.tinyMCEOpts['10'] = {
  language             : $('#ACTUAL_LANGUAGE').val(),
  language_url         : '/lib/tinymce/langs/it.js',
  width                : '100%',
  element_format       : 'html',
  relative_urls        : false,
  remove_script_host   : true,
  height               : '300',
  toolbar              : "undo redo | bold italic | bullist numlist | link image ",
  image_dimensions     : false,
  paste_postprocess    : function(plugin, args) {
    var sConf = {
      elements  : [
        'a', 'br', 'em', 'li', 'ol', 'p', 'strong', 'ul', 'span', 'img'
      ],
      attributes: {
        '__ALL__': ['class'],
        a        : ['href', 'title', 'target'],
        img      : ['alt', 'src', 'title']
      }
    }
    var s = new Sanitize(sConf);
    $(args.node).find('meta').remove();
    $(args.node).html($('<div>').append(s.clean_node(args.node)).html());
    console.log($(args.node).html());
  },
  file_browser_callback: function(field_name, url, type, win) {
    Ueppy.tinyMCEOpenFileManager(field_name, url, type, win);
  },
  menubar              : 'edit insert format table',
  style_formats        : [
    {
      title: 'Inlinea', items: [
      {title: 'Bold', inline: 'b', icon: 'bold'},
      {title: 'Italic', inline: 'i', icon: 'italic'},
      {title: 'Underline', inline: 'u', icon: 'underline'},
      {title: 'Strikethrough', inline: 'strike', icon: 'strikethrough'}
    ]
    },
    {
      title: 'Immagini', items: [
      {title: 'Left', selector: 'img', styles: {'float': 'left', 'margin': '0 10px 10px 0'}, icon: 'alignleft'},
      {title: 'Right', selector: 'img', styles: {'float': 'right', 'margin': '0 0 10px 10px'}, icon: 'alignright'},
    ]
    },
    {
      title: 'Classi speciali', items: [
      {title: 'Lighbox', selector: 'a', 'classes': 'lightbox'},
      {title: 'No Spam', selector: 'a', 'classes': 'nspm'}
    ]
    }
  ],
  importcss_append     : true,
  importcss_file_filter: "style_formati.css",
  plugins              : [
    "advlist autolink link image lists charmap print preview hr anchor pagebreak",
    "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
    "save table contextmenu directionality emoticons template paste textcolor importcss"
  ]
};
Ueppy.tinyMCEOpts['20'] = {
  language               : $('#ACTUAL_LANGUAGE').val(),
  language_url           : '/lib/tinymce/langs/it.js',
  width                  : '100%',
  relative_urls          : false,
  paste_data_images      : false,
  remove_script_host     : true,
  height                 : '300',
  image_advtab           : true,
  image_dimensions       : false,
  menubar                : 'edit insert format table tools',
  toolbar                : "undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist | link image",
  file_browser_callback  : function(field_name, url, type, win) {
    Ueppy.tinyMCEOpenFileManager(field_name, url, type, win);
  },
  style_formats          : [
    {
      title: 'Intestazioni', items: [
      {title: 'h1', block: 'h1'},
      {title: 'h2', block: 'h2'},
      {title: 'h3', block: 'h3'},
      {title: 'h4', block: 'h4'},
      {title: 'h5', block: 'h5'},
      {title: 'h6', block: 'h6'}
    ]
    },
    {
      title: 'Inlinea', items: [
      {title: 'Bold', inline: 'b', icon: 'bold'},
      {title: 'Italic', inline: 'i', icon: 'italic'},
      {title: 'Underline', inline: 'span', styles: {textDecoration: 'underline'}, icon: 'underline'},
      {title: 'Strikethrough', inline: 'span', styles: {textDecoration: 'line-through'}, icon: 'strikethrough'},
      {title: 'Superscript', inline: 'sup', icon: 'superscript'},
      {title: 'Subscript', inline: 'sub', icon: 'subscript'},
      {title: 'Code', inline: 'code', icon: 'code'},
    ]
    },
    {
      title: 'Blocchi', items: [
      {title: 'Paragraph', block: 'p'},
      {title: 'Blockquote', block: 'blockquote'},
      {title: 'Div', block: 'div'},
      {title: 'Pre', block: 'pre'}
    ]
    },

    {
      title: 'Allineamento', items: [
      {title: 'Left', block: 'div', styles: {textAlign: 'left'}, icon: 'alignleft'},
      {title: 'Center', block: 'div', styles: {textAlign: 'center'}, icon: 'aligncenter'},
      {title: 'Right', block: 'div', styles: {textAlign: 'right'}, icon: 'alignright'},
      {title: 'Justify', block: 'div', styles: {textAlign: 'justify'}, icon: 'alignjustify'},
      {title: 'Clear', block: 'div', styles: {clear: 'both'}}
    ]
    },
    {
      title: 'Immagini', items: [
      {title: 'Left', selector: 'img', styles: {'float': 'left', 'margin': '0 10px 10px 0'}, icon: 'alignleft'},
      {title: 'Right', selector: 'img', styles: {'float': 'right', 'margin': '0 0 10px 10px'}, icon: 'alignright'},
    ]
    },
    {
      title: 'Classi speciali', items: [
      {title: 'Lighbox', selector: 'a', 'classes': 'lightbox'},
      {title: 'No Spam', selector: 'a', 'classes': 'nspm'}
    ]
    }
  ],
  importcss_append       : true,
  importcss_file_filter  : "style_formati.css",
  extended_valid_elements: "div[class|id|style]",
  plugins                : [
    "advlist autolink link image lists charmap print preview hr anchor pagebreak",
    "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
    "save table contextmenu directionality emoticons template paste textcolor importcss"
  ],
  external_plugins       : {
    "code": "/lib/tinymce/plugins/code/plugin.min.js"
  }

};

//lvl = 'BASE';
// opzioni di default di inizializzazione tinymce
if(typeof(lvl) == 'undefined') {
  lvl = 20;
}
Ueppy.tinyMCEOpts.opts = Ueppy.tinyMCEOpts[lvl];

Ueppy.tinyMCESetOpts = function(obj) {
  for(var i in obj) {
    if(i == 'main_host') {
      Ueppy.tinyMCEOpts.opts.templates = main_host + 'admin/tinymcetemplateslist/';
    } else {
      Ueppy.tinyMCEOpts.opts[i] = obj[i];
    }
  }
};
Ueppy.tinyMCEInit = function() {
  tinymce.init(Ueppy.tinyMCEOpts.opts);
}

Ueppy.tinyMCEOpenFileManager = function(field_name, url, type, win) {
  var w = window,
      d = document,
      e = d.documentElement,
      g = d.getElementsByTagName('body')[0],
      x = w.innerWidth || e.clientWidth || g.clientWidth,
      y = w.innerHeight || e.clientHeight || g.clientHeight;

  var cmsURL = main_host + 'lib/FM/index.html?&field_name=' + field_name + '&langCode=' + tinymce.settings.language;
  if(type == 'image') {
    cmsURL = cmsURL + "&type=images";
  }
  tinyMCE.activeEditor.windowManager.open({
    file          : cmsURL,
    title         : 'Filemanager',
    width         : x * 0.8,
    height        : y * 0.8,
    resizable     : "yes",
    close_previous: "no"
  });
}

Ueppy.startTour = function() {
  var steps = {};

  var defaultStep = {
    content        : '<p>Messaggio vuoto</p>',
    highlightTarget: true,
    nextButton     : true,
    target         : null,
    my             : 'bottom center',
    at             : 'top center'
  };

  $('[data-demo=1]').each(function() {

    var options = {};
    if($(this).data('demo-msg')) {
      options.content = '<p>' + html_entity_decode($(this).data('demo-msg'), 'ENT_QUOTES', 'UTF-8') + '</p>';
    }
    if($(this).data('demo-my')) {
      options.my = $(this).data('demo-my');
    }
    if($(this).data('demo-at')) {
      options.at = $(this).data('demo-at');
    }
    options.target = $(this);

    var step = $.extend({}, defaultStep, options);

    Utility.log(step);

    steps[$(this).data('demo-step')] = step;
  });


  var keys = [], k, i, len;

  for(k in steps) {
    if(steps.hasOwnProperty(k)) {
      keys.push(k);
    }
  }

  keys.sort();

  len = keys.length;

  var defSteps = [];
  for(i = 0; i < len; i++) {
    k = keys[i];
    defSteps.push(steps[k]);
  }

  Utility.log(defSteps);

  var tour = new Tourist.Tour({
    steps     : defSteps,
    tipClass  : 'Bootstrap',
    tipOptions: {showEffect: 'slidein'}
  });
  tour.start();
}

Ueppy.inizializzaPluginAllegati = function() {

  if($('#allegati').length) {

    var $allegati = $('#allegati');

    topAllegati = $allegati.offset().top;
    topTab = $(".with-nav-tabs:last").offset().top;

    sogliaAllegati = topTab - topAllegati;

    var allegatiOptions = {
      id_genitore   : $allegati.data('id_genitore'),
      genitore      : $allegati.data('genitore'),
      readonly      : $allegati.data('readonly'),
      uploadClassico: $allegati.data('upload_classico'),
      soglia        : sogliaAllegati
    };
    allegatiOptions.showFilesCallback = function() {
    };
    if(typeof(showFilesCallback) == 'function') {
      allegatiOptions.showFilesCallback = showFilesCallback;
    }
    allegatiOptions.debug = false;
    allegatiOptions.lingue = lingue;

    fileAllegati.boot($('#allegati'), allegatiOptions);
  }
}

Ueppy.millisToStr = function(milliseconds) {
  // TIP: to find current time in milliseconds, use:
  // var  current_time_milliseconds = new Date().getTime();

  function numberEnding(number) {
    return (number > 1) ? 'i' : 'o';
  }

  function numberEnding2(number) {
    return (number > 1) ? 'a' : 'e';
  }

  var temp = Math.floor(milliseconds / 1000);
  var years = Math.floor(temp / 31536000);
  if(years) {
    return years + ' ann' + numberEnding(years);
  }
  var days = Math.floor((temp %= 31536000) / 86400);
  if(days) {
    return days + ' giorn' + numberEnding(days);
  }
  var hours = Math.floor((temp %= 86400) / 3600);
  if(hours) {
    return hours + ' or' + numberEnding2(hours);
  }
  var minutes = Math.floor((temp %= 3600) / 60);
  if(minutes) {
    return minutes + ' minut' + numberEnding(minutes);
  }
  var seconds = temp % 60;
  if(seconds) {
    return seconds + ' second' + numberEnding(seconds);
  }
  return 'meno di un secondo'; //'just now' //or other string you like;
}