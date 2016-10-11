/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (27/05/16, 15.51)                                                                      **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/

Backbone.emulateHTTP = true; // Use _method parameter rather than using DELETE and PUT methods
Backbone.emulateJSON = true; // Send data to server via parameter rather than via request content

(function() {

  var fileAllegati = {};

  window.fileAllegati = fileAllegati;

  /**
   * Funzione per la compilazione del template a partire da un id dato.
   * La funzione compila il contenuto del tag script con type text/x-handlebars-template e con id
   * $name-template
   *
   * nel file tpl saranno presenti variabili di questo tipo {{variabile}} che verranno rimpiazzate con la rispettiva
   * variabile passata al template.
   */
  var template = function(name) {
    return Handlebars.compile($('#' + name + '-template').html());
  };

  fileAllegati.ias = false; // conterrà l'istanza del plugin image area select
  fileAllegati.debug = false; // impostabile a true nel costruttore per
                              // avere la stampa in console di tutto quello che accade

  fileAllegati.DD = true;  // impostato a false nel costruttore in caso non sia supportato
                           // l'upload tramite drag & drop

  fileAllegati.urlRoot = "/backbone.php?cmd=BACKBONE"; // url di base a cui fare le richieste ajax

  /**
   * funzione di logging, stampa in console solo se impostato il debug a true
   */
  fileAllegati.log = function(str) {
    if(fileAllegati.debug) {
      if(typeof(console) != 'undefined') {
        console.info(str);
      }
    }
  }

  fileAllegati.lingue = false;

  // modello per il file allegato.
  fileAllegati.Allegato = Backbone.Model.extend({
    // dati di default
    defaults  : {
      nomefile   : '',
      hash       : '',
      id_genitore: 0,
      genitore   : '',
      size       : 0,
      thumb      : '',
      time       : 205996800,
      ordine     : 0,
      estensione : 'dat',
      options    : '',
      descrizione: {},
      title      : {},
      versioni   : {},
      alt        : {}
    },
    // metodo costruttore
    initialize: function() {
      this.on('all', function(e) {
        fileAllegati.log('--- EVENTO ---' + e);
      }, this);

    },
    //ritorna true se il file è un'immagine
    isAnImage : function() {
      return ($.inArray(this.get('estensione'), ['jpg', 'png', 'gif', 'jpeg']) != -1);
    },
    // funzione che costruisce l'url a cui effettuare richieste di elenco, creazione ecc
    url       : function() {
      var base = fileAllegati.urlRoot || (this.collection && this.collection.url) || "/";
      if(this.isNew()) return base;
      return base + "&id=" + encodeURIComponent(this.id);
    }
  });


  // collection di files allegati
  fileAllegati.Allegati = Backbone.Collection.extend({
    // modello di riferimento (l'allegato)
    model     : fileAllegati.Allegato,
    // nel costruttore imposto i valori relativi al genitore della collezione (quindi id e tipo di oggetto
    initialize: function(options) {
      this.id_genitoreModel = options.id_genitore;
      this.genitoreModel = options.genitore;
    },
    // url a cui effettuare le richieste
    url       : function() {
      var base = fileAllegati.urlRoot;
      base += '&id_genitore=' + this.id_genitoreModel + '&genitore=' + this.genitoreModel;
      return base;
    }
  });

  // vista principale, da qui parte tutto.
  fileAllegati.Index = Backbone.View.extend({
    // template da compilare
    template      : template('index'),
    // classe dell'elemento contentire (questo)
    className     : 'mainAllegatiContainer',
    // costruttore, istanzio la collezione e ne estraggo gli elementi
    // imposto inoltre id genitore e tipologia di genitore
    initialize    : function(options) {
      fileAllegati.log('fileAllegati.Index.initialize');

      this.readOnly = parseInt(this.options.allegatiOptions.readonly, 10);
      this.loaded = false;

      // estraggo gli allegati eventualmente già caricati.
      this.filesAllegati = new fileAllegati.Allegati(this.options.allegatiOptions);
      this.filesAllegati.on('all', this.render, this);
      this.filesAllegati.fetch();

      this.showFilesCallback = false;

      if(typeof this.options.allegatiOptions.soglia) {
        this.soglia = this.options.allegatiOptions.soglia;
      }
      if(typeof this.options.allegatiOptions.showFilesCallback === 'function') {
        this.showFilesCallback = this.options.allegatiOptions.showFilesCallback;
      }

      this.id_genitoreModel = this.options.allegatiOptions.id_genitore;
      this.genitoreModel = this.options.allegatiOptions.genitore;

    },
    // ritorna il numero di elementi della collezione
    count         : function() {
      fileAllegati.log('fileAllegati.Index.count');
      return this.filesAllegati.length;
    },
    // funzione da richiamare per mostrare a video la vista
    render        : function() {
      fileAllegati.log('fileAllegati.Index.render');

      // compilo il template passando come contesto la vista stessa, quindi se
      // nel template c'è la variabile {{count}} questa verrà rimpiazzata con il valore
      // ritornato dalla funzione count.
      // this.$el contiene l'elemento stesso, il suo html viene riempito con il risultato della
      // compilazione del template
      this.$el.html(this.template(this));

      if(this.readOnly === 0) {

        // istanzio e aggiungo l'area preposta a mostrare lo stato di upload dei files.
        statusZone = new fileAllegati.Index.statusZone({collection: this.filesAllegati});
        this.$el.find('.allegatiTitle').after(statusZone.render().el);

        // decido se mostrare la drop zone o l'upload classico in base alla variabile DD
        // impostata in fase di boot dell'applicazione
        if(fileAllegati.DD) {
          // interfaccia con drag & drop
          dropZone = new fileAllegati.Index.dropZone({
            collection      : this.filesAllegati,
            callback        : this.showFilesCallback,
            p               : statusZone,
            id_genitoreModel: this.id_genitoreModel,
            genitoreModel   : this.genitoreModel
          });
          this.$el.find('.allegatiTitle').after(dropZone.render().el);
        } else {
          // interfaccia classica
          uploadZoneOldBrowser = new fileAllegati.Index.uploadZoneOldBrowser({
            collection      : this.filesAllegati,
            callback        : this.showFilesCallback,
            p               : statusZone,
            id_genitoreModel: this.id_genitoreModel,
            genitoreModel   : this.genitoreModel,
            'forced'        : this.options.allegatiOptions.uploadClassico
          });
          this.$el.find('.allegatiTitle').after(uploadZoneOldBrowser.render().el);
        }

      }

      // svuoto gli allegati prima di riempirli nuovamente.
      this.$('.allegati').empty();

      // per ogni file allegato chiamo il metodo addAllegato, passando come contesto this
      // in modo che dentro al metodo potrò accedere ai dati della vista richiamando sempre this.
      this.filesAllegati.each(this.addAllegato, this);

      // coloro le righe della tabella
      this.zebratable();

      // istanzio la variabile view per poter accedere ai suoi metodi/proprietà nella funzione di onstop.
      var view = this;

      if(this.readOnly === 0) {
        // applico il sortable per permettere l'ordinamento delle righe della tabella.
        this.$('.allegati').find('tbody').sortable({
          placeholder: '<tr><td colspan="5"></td></tr>'
        }).bind('sortupdate', function(e, ui) {
          view.onSortableStop(event, ui)
        });
      }
      // il metodo rendere ritorna sempre la view.
      return this;
    },
    // metodo richiamato all'onstop dell'ordinamento delle righe della tabella
    onSortableStop: function(event, ui) {
      // ricoloro le righe della tabella
      this.zebratable();
      // salvo il nuovo ordine su database
      this.saveSortOrder();
    },
    // meotdo per il salvataggio dell'ordine delle righe della tabella.
    saveSortOrder : function() {
      // raccolgo gli id dei modelli in ordine
      ids = [];
      this.$el.find('.allegati  tr').each(function() {
        ids.push($(this).attr('data-id'));
      });

      // eseguo una chiamata ajax passando gli id nell'ordine in cui devono essere salvati.
      data = 'order=' + ids.join(',') + '&_method=SAVESORT';
      $.ajax({
        type    : "POST",
        url     : fileAllegati.urlRoot,
        data    : data,
        dataType: "json",
        success : function(returned_data) {
          // se ci sono errori mostro l'errore
          if(parseInt(returned_data.result) != 1) {
            Utility.alert({'message': returned_data.error});
          }
        }
      });
    },
    // metood che applica classi diverse alle righe pari e alle dispari per permetterne
    // la diversa colorazione
    zebratable    : function() {
      this.$('.allegati').find('tr').removeClass('dispari').removeClass('pari');
      this.$('.allegati').find('tr:even').addClass('dispari');
      this.$('.allegati').find('tr:odd').addClass('pari');

      if(this.showFilesCallback !== false) {
        this.showFilesCallback();
      }
      var $mainContainer = this.$('.allegati').closest('.mainAllegatiContainer');
      var H = $mainContainer.parent().outerHeight();
      if(this.soglia > 0 && H > 0 && !this.loaded) {
        this.loaded = true;
        Utility.log('Loaded: ' + this.loaded, {trace: true});
        var $mainContainer = this.$('.allegati').closest('.mainAllegatiContainer');
        Utility.log('H:' + H);
        if(H > this.soglia) {
          $mainContainer.find('.hideShowAllegati').hide();
          $mainContainer.parent().find('#toggleAllegati').text(Utility.getLang('allegati', 'GESTISCI_ALLEGATI'));
        }
      }

    },
    // metodo che prende in ingresso un modello allegato, lo renderizza e lo appende
    // al contenitore con classe ".allegati".
    addAllegato   : function(allegato) {
      fileAllegati.log('fileAllegati.Index.addAllegato');
      var view = new fileAllegati.Index.Allegato({model: allegato, readOnly: this.readOnly});
      this.$('.allegati').append(view.render().el);
    }

  });

  // vista che gestisce l'upload in modalità classica
  fileAllegati.Index.uploadZoneOldBrowser = Backbone.View.extend({
    initialize: function(options) {
      // messaggio che segnala il metodo alternativo di upload
      this.$el.attr('hint', Utility.getLang('allegati', 'DRAG_DROP_CON_BROWSER_MODERNI'));
      // progress bar
      this.p = options.p;
      // id del genitore
      this.id_genitoreModel = options.id_genitoreModel;
      // tipo di genitore
      this.genitoreModel = options.genitoreModel;
      // se forzato l'upload classico non mostro l'hint questa var arriva nel costruttore per poi verifcarla
      this.forced = options.forced;
    },
    // classname dell'elemento contenitore (div)
    className : 'dropZone',
    // template usato per il render dell'interfaccia
    template  : template('uploadzoneoldbrowser'),
    // proprietà necessaria al template per comporre il form
    urlRoot   : function() {
      return fileAllegati.urlRoot
    },
    // funzione che compila e inietta nel dom l'html generato.
    render    : function() {
      this.$el.html(this.template(this));
      return this;
    },
    // eventi da ascoltare
    events    : {
      'change input': 'doSubmit', // evento scaturito alla scelta del file
      'hover'       : 'showHint'
    },// all'hover mostro il tooltip
    // metodo che si avvale del widget tooltip di jquery.ui per mostrare il suggerimento
    // all'utilizzo di un browser moderno.
    showHint  : function() {
      if(!this.forced) {
        this.$el.tooltip({
          items  : '[hint]',
          content: function() {
            var element = $(this);
            fileAllegati.log(element);
            if(element.is('[hint]')) {
              return element.attr('hint');
            }
          }
        });
      }
    },
    // metodo che gestisce l'upload del file, il file viene caricato
    // grazie all'ausilio del plugin jquery.form.js e in risposta
    // otteniamo un elenco di files caricati (nel caso specifico 1 perchè
    // la visuale classica non permette upload multipli).
    // i files sono posizionati in una directory temporanea, e vengono spostati
    // solo nel momento in cui chiamiamo il metodo create della collection.
    // l'invididuazione univoca del file avviene tramite la proprietà hash.
    doSubmit  : function() {
      // il form che contiene il campo input
      $f = $(this.$el.find('form'));
      // la vista attuale
      var view = this;

      // la progress bar
      var p = this.p;
      // setto il numero di files in upload a 1.
      p.setNumeroFiles(1);
      // mostro un falso progress in quanto il browser che usa questa interfaccia
      // non supporta xhr2
      p.fakeProgress();

      // compongo le opzioni per l'ajaxsubmit
      options = {};
      // all'onsuccess creo i files.
      options.success = function(returned_data) {
        if(returned_data.files.length > 0) {
          file = returned_data.files[0];
          t = new Date();
          m = {
            hash       : file.hash,
            nomefile   : file.nomefile,
            time       : t.getTime() / 1000,
            estensione : file.estensione,
            id_genitore: view.id_genitoreModel,
            genitore   : view.genitoreModel
          };
          fileAllegati.log(m);
          view.collection.create(m);
        } else {
          $f.find('input[type=file]').replaceWith('<input type="file" name="file[]">');
          Utility.alert({'message': returned_data.error});
        }
      };
      options.type = 'POST';
      options.url = $f.attr('action');
      options.dataType = 'json';
      $f.ajaxSubmit(options);
    }
  });

  // vista che gestisce l'area dove verranno droppati i files.
  fileAllegati.Index.dropZone = Backbone.View.extend({
    // inizializzazione importo proprietà del genitore e progressbar.
    initialize: function(options) {
      fileAllegati.log(options);
      this.p = options.p;
      this.id_genitoreModel = options.id_genitoreModel;
      this.genitoreModel = options.genitoreModel;
      // predispongo l'interfaccia per gestire l'upload dei files con d&d
      // devo vietare al browser di comportarsi nel modo di default caricando i file
      // droppati.
      $(document).bind('drop dragover', function(e) {
        e.preventDefault();
      });
    },
    // template della dropzone
    template  : template('dropzone'),
    // nome della classe del contenitore (div)
    className : 'dropZone',
    // funzione che compila e inietta nel dom l'html generato.
    render    : function() {
      this.$el.html(this.template);
      return this;
    },
    // eventi da ascoltare, il valore rappresenta il nome del metodo da richiamare
    events    : {
      "dragover" : "dragOver", // file draggato e in hover sull'area sensibile
      "dragleave": "dragLeave",// file draggato spostato fuori dall'area sensibile
      "drop"     : "manageDrop"// file droppato nell'area
    },
    // all'hover applico la classe di hover
    dragOver  : function() {
      this.$el.addClass('hoverDropZone');
    },
    // allo spostamento fuorid all'area sensibile rimuovo la classe.
    dragLeave : function() {
      this.$el.removeClass('hoverDropZone');
    },
    // metodo che gestisce l'upload
    manageDrop: function(event) {

      event.preventDefault && event.preventDefault();
      // rimuovo la classe di over
      this.$el.removeClass('hoverDropZone');

      // vista da utilizzare nelle funzioni di callback.
      var view = this;

      // elenco dei files
      var files = event.originalEvent.dataTransfer.files;

      // creazione dell'oggetto formdata, posso appendere coppie chiave/valore di parametri da passare
      var formData = new FormData();

      // appendo al form i files droppati
      for(var i = 0; i < files.length; i++) {
        formData.append('file[' + i + ']', files[i]);
      }

      // appendo il metodo...
      formData.append('_method', 'upload')
      // ..il genitore..
      formData.append('id_genitore', this.id_genitoreModel);
      // ... e il tipo di genitore
      formData.append('genitore', this.genitoreModel);

      // aggiorno la progressbar impostando il numero di files in coda.
      var p = this.p;
      p.setNumeroFiles(files.length);

      // ora posso postare i dati tramite ajax (usa il protocollo xhr2)
      var xhr = new XMLHttpRequest();

      // rimango in ascolto del progresso di upload
      xhr.upload.onprogress = function(event) {
        fileAllegati.log('fileAllegati.Index.dropZone.manageDrop:xhr.upload.onprogress');
        if(event.lengthComputable) {
          // ogni volta che è richiamato l'onprogress aggiorno la progressbar.
          p.setPercentuale(event.loaded / event.total * 100 | 0);
        }
      };

      // evento scaricato al termine dell'upload
      xhr.onload = function() {
        fileAllegati.log('fileAllegati.Index.dropZone.manageDrop:xhr.onload');
        // per sicurezza imposto la percentuale a 100 per non rimanere bloccato al 99.
        p.setPercentuale(100);
        // se lo stato è 200 posso gestire la risposta, altrimenti
        // qualcosa è andato storto nell'upload
        if(xhr.status === 200) {
          ret_data = JSON.parse(xhr.responseText);
          // per ognuno dei files caricati chiamo il metodo create utilizzando la collection
          for(i = 0; i < ret_data.files.length; i++) {
            var file = ret_data.files[i];
            t = new Date();
            fileAllegati.log(file);
            m = {
              hash       : file.hash,
              nomefile   : file.nomefile,
              time       : t.getTime() / 1000,
              estensione : file.estensione,
              id_genitore: view.id_genitoreModel,
              genitore   : view.genitoreModel
            };
            view.collection.create(m, {
              wait: true, success: view.showFilesCallback, error: function(model, xhr) {
                Utility.alert({'message': xhr.responseText});
              }
            });
          }
          // se c'è qualche warning lo mostro (es. limite di allegati
          // raggiunto o estensioni errate)
          if(parseInt(ret_data.result) != 1) {
            Utility.alert({'message': ret_data.error});
          }
        }
      };

      // apro la chiamata ajax di tipo post.
      xhr.open('POST', fileAllegati.urlRoot);
      // e invio formdata
      xhr.send(formData);
      return false;
    }
  });

  // vista per la gestione dell'avanzamento dell'upload
  fileAllegati.Index.statusZone = Backbone.View.extend({
    className     : 'statuszone',
    // costruttore, alla creazione imposto la percentuale a zero
    initialize    : function() {
      this.perc = 0;
    },
    template      : template('statuszone'),
    // funzione che compila e inietta nel dom l'html generato.
    render        : function() {
      this.$el.html(this.template);
      // dopo la creazione nascondo la status zone, deve essere mostrata
      // solo in caso di upload in corso.
      this.$el.find('div:first').hide();
      return this;
    },
    // imposta il numero dei file in upload, quando richiamato mostra lo status
    setNumeroFiles: function(num) {
      this.$el.find('div:first').show();
      this.$el.find('.filesTotali').html(num);
    },
    // imposta la percentulae, una volta arrivato a 100 nascondo lo status
    setPercentuale: function(num) {
      fileAllegati.log('fileAllegati.Index.statusZone.setPercentuale(' + num + ')');
      this.perc = num;
      leftVal = 100 - num;
      this.$el.find('.percentuale').css('left', '-' + leftVal + '%');
      if(num == 100) {
        this.$el.find('div:first').fadeOut(500, function() {
          $(this).find('.percentuale').css('left', '-100%');
        });
      }
    },
    // metodo che mostra una barra di upload fittizia, aggiorna aggiungendo il 10%
    // ad ogni chiamata (ogni mezzo secondo).
    fakeProgress  : function() {
      view = this;
      this.timeout = setInterval(function() {
        view.perc = view.perc + 10;
        if(view.perc > 100) {
          view.perc = 0;
        }
        leftVal = 100 - view.perc;
        view.$el.find('.percentuale').css('left', '-' + leftVal + '%');
      }, 500);
    }
  });

  // vista dedicata a mostrare la riga rappresentante l'allegato
  fileAllegati.Index.Allegato = Backbone.View.extend({
    // tag da creare
    tagName    : 'tr',
    // classe
    className  : 'allegato',
    // template da utilizzare
    template   : function(contesto) {
      if(this.options.readOnly == 1) {
        compiled = template('allegatoreadonly');
      } else {
        compiled = template('allegato')
      }
      return compiled(contesto);
    },
    // assegna all'attributo data-id il valore di id reperito dal model
    initialize : function() {
      //alert();
      this.$el.attr({'data-id': this.model.get('id')});
    },
    // eventi da ascoltare, il valore rappresenta il nome del metodo da richiamare
    events     : {
      'mouseover .icon'      : 'showData', // mouseover sull'icona dell'estensione
      'mouseout .icon'       : 'hideData', // mouseout  "      "    "      "
      'click .editAllegato'  : 'edit',     // click sul blocchetto per le modifiche
      'click .deleteAllegato': 'elimina'   // click sul cestino
    },
    // funzione che compila e inietta nel dom l'html generato.
    render     : function() {
      fileAllegati.log('fileAllegati.Index.Allegato.render');
      this.$el.html(this.template(this));
      return this;
    },
    // metodo che rimaneggia il nome del file per troncarlo se troppo lungo
    nomefile   : function() {
      var nomefile = this.model.get('nomefile');
      if(nomefile.length > 20) {
        pz = nomefile.split('.');
        nomefile = pz[0];
        ext = pz[1];
        nomefile = nomefile.substring(0, 14) + '...' + pz[1];
      }
      return nomefile;
    },
    // ritorna l'id del genitore
    id_genitore: function() {
      return this.model.get('id_genitore');
    },
    // ritorna le descrizioni del file
    descrizioni: function() {
      d = this.model.get('descrizione');
      ret = '';
      for(i in fileAllegati.lingue) {
        ret += fileAllegati.lingue[i] + ': ' + d[i];
        ret += '<br />';
      }
      return ret;
    },
    // ritorna una stringa contenente title e alt (se si tratta di un'immagine)
    meta       : function() {
      title = this.model.get('title');
      ret = '<span class="lbl">TITLE</span>';
      ret += '<br />';
      for(i in fileAllegati.lingue) {
        ret += fileAllegati.lingue[i] + ': ' + title[i];
        ret += '<br />';
      }
      if(this.model.isAnImage()) {
        alt = this.model.get('alt');
        ret += '<span class="lbl">ALT</span>';
        ret += '<br />';
        for(i in fileAllegati.lingue) {
          ret += fileAllegati.lingue[i] + ': ' + alt[i];
          ret += '<br />';
        }
      }
      return ret;
    },
    // ritorna l'estensione del modello
    estensione : function() {
      return this.model.get('estensione');
    },
    fa         : function() {
      return this.model.get('fa');
    },
    // ritorna l'url dell'icona che rappresenta il file
    thumb      : function() {
      return this.model.get('thumb');
    },
    // formatta e ritorna la data di upload del file
    time       : function() {
      d = new Date();
      d.setTime(this.model.get('time') * 1000);
      day = d.getDate();
      if(day < 10) {
        day = '0' + day;
      }
      month = d.getMonth() + 1;
      if(month < 10) {
        month = '0' + month;
      }

      hour = d.getHours();
      if(hour < 10) {
        hour = '0' + hour;
      }
      minutes = d.getMinutes();
      if(minutes < 10) {
        minutes = '0' + minutes;
      }

      return day + '/' + month + '/' + d.getFullYear() + ' ' + hour + ':' + minutes;
    },
    // metodo richiamato all'hover dell'icona, mostra un sommario dei dati del file.
    showData   : function() {
      var icon_pos = this.$el.find('.icon').position();
      var fileDetail = this.$el.find('.fileDetail');
      fileDetail.css({'top': icon_pos.top + 'px', 'left': +icon_pos.left + 20}).stop().fadeIn('slow');
    },
    // nascondi le info quando ci spostiamo dall'icona del file.
    hideData   : function() {
      fileDetail = this.$el.find('.fileDetail').stop().hide();
    },
    tipo       : function() {
      if(this.model.isAnImage()) {
        return 'image';
      } else {
        return 'file';
      }
    },
    // edit del file, a seconda di quale tipo di file vogliamo modificare carica
    // la finestra per le immagini o per i files.
    edit       : function(e) {
      e.preventDefault();
      if(this.model.isAnImage()) {
        // vista della modale dedicata alla gestione dei metadati dell'immagine
        modaleImmagine = new fileAllegati.Index.modaleImmagine({collection: this.filesAllegati, model: this.model});
        this.$el.append(modaleImmagine.render().el);
        // oltre ad appendere l'html devo chiamare il metodo show che si occupa
        // di rendere l'html una finestra modale
        modaleImmagine.show();
      } else {
        // vista della modale dedicata alla gestione dei metadati del file allegato.
        modaleFile = new fileAllegati.Index.modaleFile({collection: this.filesAllegati, model: this.model});
        this.$el.append(modaleFile.render().el);
        // oltre ad appendere l'html devo chiamare il metodo show che si occupa
        // di rendere l'html una finestra modale g
        modaleFile.show();
      }
    },
    // metodo per l'eliminazione dei file
    elimina    : function() {
      var view = this;

      var opts = {
        title    : Utility.getLang('allegati', 'ELIMINA_FILE'),
        message  : sprintf(Utility.getLang('allegati', 'VUOI_CANCELLARE'), this.model.get('nomefile')),
        okLbl    : Utility.getLang('allegati', 'ELIMINA'),
        cancelLbl: Utility.getLang('allegati', 'ANNULLA'),
        type     : 'info',
        onOk     : function() {
          view.model.destroy();
        }
      };

      Utility.confirm(opts);

    }
  });

  // vista dedicata alla gestione della finestra modale che gestisce i metadati di un file allegato.
  fileAllegati.Index.modaleFile = Backbone.View.extend({
    className     : 'modale',
    // riscrivo la funzione che gestisce il template per poter rimaneggiare
    // i dati al di fuori della vista
    template      : function(context) {
      // creo un oggetto data da usare per compilare il template
      var descrizione = context.model.get('descrizione');
      var data = {};
      data.descrizione = {};
      for(i in descrizione) {
        data.descrizione[i] = descrizione[i];
      }
      title = context.model.get('title');
      data.title = {};
      for(i in title) {
        data.title[i] = title[i];
      }

      data.TITLE = Utility.getLang('allegati', 'MODIFICA_FILE') + ': ' + context.model.get('nomefile')
      data.CLOSE = Utility.getLang('allegati', 'CLOSE');
      data.SAVE = Utility.getLang('allegati', 'SAVE');

      compiled = template('modalefile');
      return compiled(data);
    },
    // eventi da ascoltare, il valore rappresenta il nome del metodo da richiamare
    events        : {
      'submit form': 'doSubmit'
    },
    initialize    : function(options) {
      this.model = options.model;
    },
    // al render tolgo eventuale html modale presente
    render        : function() {
      $('.modale').remove();
      this.$el.html(this.template(this));
      return this;
    },
    // richiama il metodo per la generazione delle tabs in caso di multilingua
    // avvalendosi del widget ui.tabs di jquery-ui
    tabify        : function() {
      var numeroLingue = 0;
      for(i in fileAllegati.lingue) {
        numeroLingue++;
      }
      if(numeroLingue > 1) {
        this.$el.find('.allegatiTabs').tabs();
      } else {
        this.$el.find('.allegatiTabs > ul').css({'display': 'none'});
      }
    },
    // metodo che gestisce la modifica dei metadati
    // prelevandoli dai campi del form
    doSubmit      : function(e) {
      e.preventDefault();
      d = {};
      t = {};
      for(i in fileAllegati.lingue) {
        d[i] = '';
        t[i] = this.$el.find('#AllegatiTitle_' + i).val();
      }
      // setto i valori reperiti
      this.model.set({
        descrizione: d,
        title      : t
      });

      // disabilito i bottoni di controllo della finestra prima di
      // fare la chiamata ajax
      this.disableButtons();

      // salvo e all'onsuccess chiamo il metodo modelSaved

      var view = this;
      this.model.save(this.model, {
        success: function(model, response) {
          view.modelSaved(model, response);
        }
      });
    },
    // funzioni di helper per la disabilitazione dei pulsanti della finestra
    // prima del salvataggio
    disableButtons: function() {
      this.$el.find('.btn').addClass('disabled');
      $('#modaleFile .modal-header').find('button').hide();
    },
    // all'avvenuto salvataggio chido la finestra modale.
    modelSaved    : function(model, response) {
      $('#modaleFile').modal('hide');
    },
    show          : function() {

      var view = this;

      this.$el.appendTo('body').find('.modal').modal();

      $('#modaleFile').on('hidden.bs.modal', function(e) {
        $('#modaleFile').data('bs.modal', null);
        $('body > .modale').remove();
      });

      this.$el.find('#saveFile').on('click', function() {
        view.$el.find('form').submit();
      });

      $('#modaleFile').find('.cmsHelp').tooltip();


    }
  });

  // vista dedicata alla gestione della finestra modale e del suo rispettivo form
  fileAllegati.Index.modaleImmagine = Backbone.View.extend({
    className          : 'modale',
    // riscrivo la funzione che gestisce il template per poter rimaneggiare
    // i dati al di fuori della vista
    template           : function(context) {
      descrizione = context.model.get('descrizione');
      var data = {};
      data.descrizione = {};
      for(i in descrizione) {
        data.descrizione[i] = descrizione[i];
      }
      title = context.model.get('title');
      data.title = {};
      for(i in title) {
        data.title[i] = title[i];
      }
      alt = context.model.get('alt');
      data.alt = {};
      for(i in alt) {
        data.alt[i] = alt[i];
      }

      versioni = context.model.get('versioni');
      data.originale = versioni[1]['path'];
      data.originaleW = versioni[1]['width'];
      data.originaleH = versioni[1]['height'];
      data.TITLE = Utility.getLang('allegati', 'MODIFICA_FILE') + ': ' + context.model.get('nomefile')
      data.CLOSE = Utility.getLang('allegati', 'CLOSE');
      data.SAVE = Utility.getLang('allegati', 'SAVE');

      Utility.log(data);

      compiled = template('modaleimmagine');
      return compiled(data);
    },
    // eventi da ascoltare, il valore rappresenta il nome del metodo da richiamare
    events             : {
      'submit form': 'doSubmit'
    },
    initialize         : function(options) {
      this.model = options.model;
    },
    // funzione di render
    render             : function() {
      // imposto l'html
      this.$el.html(this.template(this));
      // per ogni versione...
      versioni = this.model.get('versioni');
      for(i = 0; i < versioni.length; i++) {
        if(i > 1) {
          v = versioni[i];
          data = {};
          data.data = v;
          data.data.versione = i;
          // ... chiamo il metodo che crea la thumbnail
          this.addVersioneImmagine(data);
        }
      }
      this.setUpVersioni();

      return this;
    },
    // imposto il funzionamento dei bottoni di versione e della clipboard
    setUpVersioni      : function() {
      // richiamo ui.button sui link per il fotoritocco dell'immagine

      var view = this;
      var copied = false;

      //copyImageUrl
      /*
      setTimeout(function() {
        view.$el.find('.copyImageUrl').css('z-index', parseInt(view.$el.closest('.modal').css('z-index')) + 1);
        $(".copyImageUrl").zclip({
          path      : 'http://ueppy3/lib/zero-clipboard/ZeroClipboard.swf',
          copy      : function() {
            copied = $(this).attr('href');
            if(copied.indexOf('?') !== -1) {
              copied = copied.split('?').shift();
            }
            return copied;
          },
          clickAfter: false,
          afterCopy : function() {
          }
        });
        view.$el.find('.copyImageUrl').button('enable');
      }, 300);
      $('#zc3').zclip('show');
      */
    },
    // metodo helper che richiama la vista che genera il riquadro
    //  con thumbnail per l'edit dell'immagine
    addVersioneImmagine: function(data) {
      Utility.log(data);
      var view = new fileAllegati.Index.VersioneImmagine(data);
      this.$('.controlliImmagine').append(view.render().el);
    },
    // funzioni di helper per la disabilitazione dei pulsanti della finestra
    // prima del salvataggio
    disableButtons     : function() {
      $('#modaleImmagine .modal-header').find('button').remove();
      $('#modaleImmagine').find('button').prop('disabled', 'true');
    },
    // metodo che gestisce il submit del form
    doSubmit           : function(e) {
      e.preventDefault();
      var d = {};
      var t = {};
      var a = {};

      for(var i in fileAllegati.lingue) {
        d[i] = '';
        t[i] = this.$el.find('#AllegatiTitle_' + i).val();
        a[i] = this.$el.find('#AllegatiAlt_' + i).val();
      }
      this.model.set({
        descrizione: d,
        title      : t,
        alt        : a
      });
      fileAllegati.log(this.model);
      // disabilito i bottoni prima di fare la chiamata ajax
      this.disableButtons();
      var view = this;
      this.model.save(this.model, {
        success: function(model, response) {
          view.modelSaved(model, response);
        }
      });
    },
    // all'avvenuto salvataggio chido la finestra modale.
    modelSaved         : function(model, response) {
      $('#modaleImmagine').modal('hide');
    },
    show               : function() {
      var view = this;

      this.$el.appendTo('body').find('.modal').modal();

      $('#modaleImmagine').on('hidden.bs.modal', function(e) {
        $('#modaleImmagine').data('bs.modal', null);
        $('body > .modale').remove();
      });

      this.$el.find('#saveImmagine').on('click', function() {
        view.$el.find('form').submit();
      });

      $('#modaleImmagine').find('.cmsHelp').tooltip();

    }
  });

  // vista delle versioni delle immagini da caricare
  fileAllegati.Index.VersioneImmagine = Backbone.View.extend({
    // nome della classe
    className      : 'versione',
    // template
    template       : template('immaginemodificabile'),
    // eventi da ascoltare, il valore rappresenta il nome del metodo da richiamare
    events         : {
      'click .editImage'   : 'fotoRitocca',  // click sul bottone di modifica dell'immagine
      'click .copyImageUrl': 'copyToClipBoard' // click sul bottone di modifica dell'immagine
    },
    copyToClipBoard: function(e) {
    },
    // metodo che gestisce la creazione dei parametri necessari alla creazione della
    // vista per il fotoritocco.
    fotoRitocca    : function(e) {
      e.preventDefault();

      var d = {};
      // dati per l'immagine risultante del fotoritocco
      d.result = {};
      d.result.path = this.data.path;
      d.result.width = this.data.width;
      d.result.height = this.data.height;

      // dati relativi alla versione originale dell'immagine
      d.originale = this.$el.closest('form').find('.originale').val();
      d.originaleW = this.$el.closest('form').find('.originaleW').val();
      d.originaleH = this.$el.closest('form').find('.originaleH').val();

      // istanza della vista per il fotoritocco
      var view = new fileAllegati.Index.modaleFotoRitocco(d);
      $(fileAllegati.container).append(view.render().el);
      // richiamo del metodo che mostrerà la modale per il fotoritocco
      view.show();
    },
    initialize     : function(options) {
      this.data = options.data;
      console.log(this.data);
    },
    render         : function() {
      this.$el.html(this.template(this));
      return this;
    },
    // metodo che compone il percorso alla thumb.
    // appendo Math.random() per ovviare al problema delle immagini in cache,
    // voglio essere sicuro di vedere sempre l'ultima versione generata.
    path           : function() {
      return this.data.path + '?' + Math.random();
    },
    path_no_rand   : function() {
      return this.data.path;
    },
    niceUrl        : function() {
      return this.data.niceUrl;
    },
    dimensioni     : function() {
      return this.data.dimensioni;
    },
    versione       : function() {
      return this.data.versione;
    },
    // metodo di aiuto che imposta margini e dimensioni per la thumbnail
    // che rappresenta la versione che stiamo visualizzando
    style          : function() {
      var style = '';
      if(this.data.width > 100 || this.data.height > 100) {
        if(this.data.width > this.data.height) {
          newW = 100;
          newH = parseInt(this.data.height / this.data.width * newW);
        } else {
          newH = 100;
          newW = parseInt(this.data.width / this.data.height * newH);
        }
        style += 'width:' + newW + 'px;height:' + newH + 'px;';
      } else {
        newW = this.data.width;
        newH = this.data.height;
      }
      topMargin = 0;
      leftMargin = 0;
      if(newW != 100) {
        leftMargin = parseInt((100 - newW) / 2);
        style += 'margin-left:' + leftMargin + 'px;';
      }
      if(newH != 100) {
        topMargin = parseInt((100 - newH) / 2);
        style += 'margin-top:' + topMargin + 'px;margin-bottom:' + topMargin + 'px';
      }
      if(style) {
        style = ' style="' + style + '" ';
      }
      return style;
    }
  });

  // vista dedicata alla gestione della finestra modale per il fotoritocco delle immagini
  fileAllegati.Index.modaleFotoRitocco = Backbone.View.extend({
    className     : 'modaleFotoRitocco',
    // eventi da ascoltare, il valore rappresenta il nome del metodo da richiamare
    events        : {
      'click .genera': 'generaImmagine', // bottone per il ritaglio
      'click .fx a'  : 'fx'              // bottoni degli effetti
    },
    // costruttore, reperisco i dati dell'immagine risultante e
    // dell'originale. Li uso per calcolare la scala da applicare alle
    // 2 immagini e per centrarle nella finestra
    initialize    : function(options) {

      this.result = options.result;

      this.result.path += '?' + Math.random();

      // massimo ingombro delle 2 immagini
      this.options.width = 720;
      this.options.heightResult = 220;
      this.options.heightOriginale = 300;

      // dati dell'immagine originale.
      this.originale = {};
      this.originale.path = options.originale + '?' + Math.random();
      this.originale.width = options.originaleW;
      this.originale.height = options.originaleH;

      // dati dell'immagine risultante.
      this.result.width = parseInt(this.result.width);
      this.result.height = parseInt(this.result.height);

      // determina delle dimensioni di visualizzazione della foto risultato.
      this.wResult = this.result.width;
      this.hResult = this.result.height;

      if(this.result.width > this.options.width || this.result.height > this.options.heightResult) {
        if(this.result.width > this.result.height) {
          this.wResult = this.options.width;
          this.hResult = (this.result.height / this.options.width) * this.wResult;
          if(this.hResult > this.options.heightResult) {
            this.hResult = this.options.heightResult;
            this.wResult = (this.result.width / this.result.height) * this.hResult;
          }
        } else {
          this.hResult = this.options.heightResult;
          this.wResult = (this.result.width / this.result.height) * this.hResult;
          if(this.wResult > this.options.width) {
            this.wResult = this.options.width;
            this.hResult = (this.result.height / this.result.width) * this.wResult;
          }
        }
      }

      // margine sinistro e top dell'immagine risultante
      this.lResult = 0;
      this.tResult = 0;

      if(this.wResult < this.options.width) {
        this.lResult = (this.options.width - this.wResult) / 2;
      }
      if(this.hResult < this.options.heightResult) {
        this.tResult = (this.options.heightResult - this.hResult) / 2;
      }

      // determina delle dimensioni di visualizzazione della foto su cui lavorare
      this.originale.width = parseInt(this.originale.width);
      this.originale.height = parseInt(this.originale.height);
      this.wOriginale = this.originale.width;
      this.hOriginale = this.originale.height;

      if(this.wOriginale > this.options.width || this.hOriginale > this.options.heightOriginale) {
        if(this.wOriginale > this.hOriginale) {
          this.wOriginale = parseInt(this.options.width);
          this.hOriginale = (this.originale.height / this.originale.width) * this.wOriginale;
          if(this.hOriginale > this.options.heightOriginale) {
            this.hOriginale = this.options.heightOriginale;
            this.wOriginale = (this.originale.width / this.originale.height) * this.hOriginale;
          }
        } else {
          this.hOriginale = parseInt(this.options.heightOriginale);
          this.wOriginale = (this.originale.width / this.originale.height) * this.hOriginale;
          if(this.wOriginale > this.options.width) {
            this.wOriginale = this.options.width;
            this.hOriginale = (this.originale.height / this.originale.width) * this.wOriginale;
          }
        }
      }

      // margine sinistro e top dell'originale
      this.lOriginale = 0;
      this.tOriginale = 0;

      if(this.wOriginale < this.options.width) {
        this.lOriginale = (this.options.width - this.wOriginale) / 2;
      }
      if(this.hOriginale < this.options.heightOriginale) {
        this.tOriginale = (this.options.heightOriginale - this.hOriginale) / 2;
      }
    },
    // metodi per il ritorno delle dimensioni al template che poi mostrerà
    // la finestra
    wResult       : function() {
      return this.wResult;
    },
    hResult       : function() {
      return this.hResult;
    },
    lResult       : function() {
      return this.lResult;
    },
    tResult       : function() {
      return this.tResult;
    },
    wOriginale    : function() {
      return this.wOriginale;
    },
    hOriginale    : function() {
      return this.hOriginale;
    },
    tOriginale    : function() {
      return this.tOriginale;
    },
    lOriginale    : function() {
      return this.lOriginale;
    },
    template      : template('modaleImgEffects'),
    render        : function() {
      $('.modaleFotoRitocco').remove();

      var data = this;
      data.CLOSE = Utility.getLang('allegati', 'CLOSE');
      data.TITLE = Utility.getLang('allegati', 'FOTORITOCCO') + this.result.path.split('/').pop().split('?').shift()
      this.$el.html(this.template(data));
      return this;
    },
    // metodo richiamato per gli effetti, al click di un bottone di effetti
    // viene reperita la prima classe dell'elenco delle classi assegnate al
    // bottone, questa verrà usata come parametro per creare la richiesta
    // ajax che andrà a generare la nuova versione
    fx            : function(e) {
      e.preventDefault();
      $target = $(e.currentTarget);
      effetto = $target.attr('class').split(' ').shift().toUpperCase();

      // genero i parametri.
      params = [];

      params.push('_method=' + effetto);
      params.push('path=' + this.result.path.split('?').shift());

      data = params.join('&');

      // disabilito i bottoni prima della chiamata
      this.disableButtons();

      var view = this;

      // compongo la richiesta, nel risultato reimposto l'src della versione
      // risultato e della thumbnail presente nella finestra sottostante.
      $.ajax({
        type    : "POST",
        url     : fileAllegati.urlRoot,
        data    : data,
        dataType: "json",
        success : function(returned_data) {
          // riabilito i bottoni
          view.enableButtons();
          if(parseInt(returned_data.result) != 1) {
            Utility.alert({'message': returned_data.error});
          } else {
            // reimposto l'src
            src = view.$el.find('.risultato > img').attr('src').split('?').shift();
            view.$el.find('.risultato > img').attr('src', src + '?' + Math.random());
            $('.versione:eq(' + returned_data.versione + ') img').attr('src', src + '?' + Math.random());
          }
        }
      });
    },
    // utilità per la disabilitazione dei bottoni
    disableButtons: function() {
      this.$el.find('.btn').addClass('disabled');
      $('#modaleImmagineFX .modal-header').find('button').hide();
    },
    // utilità per la riabilitazione dei bottoni
    enableButtons : function() {
      $('#modaleImmagineFX .modal-header').find('button').show();
      this.$el.find('.btn').removeClass('disabled');
    },
    // metodo per il ritaglio dell'immagine
    generaImmagine: function() {
      // reperisco la selecion dall'istanza del plugin ImageAreaSelect
      selection = fileAllegati.ias.getSelection();
      fileAllegati.log(selection);

      // se non ho selezionato nulla mi fermo
      if(selection.x1 == selection.x2 || selection.y1 == selection.y2) {
        Utility.alert({'message': Utility.getLang('allegati', 'SELEZIONA_QUALCOSA')});
      } else {
        // altrimenti controllo le dimensioni, per immagini originali grandi
        // c'è il rischio di avere immagini + piccole di quelle impostate.
        opts = fileAllegati.ias.getOptions();
        if(selection.width < opts.minWidth) {
          selection.width = parseInt(opts.minWidth);
        }
        if(selection.height < opts.minHeight) {
          selection.height = parseInt(opts.minHeight);
        }

        if(selection.x1 + selection.width > opts.imageWidth) {
          selection.x1 = opts.imageWidth - selection.width;
        }
        selection.x2 = selection.x1 + selection.width;

        if(selection.y1 + selection.height > opts.imageHeight) {
          selection.y1 = opts.imageHeight - selection.height;
        }
        selection.y2 = selection.y1 + selection.height;

        // compongo i parametri passando dimensioni, offset url originale e url risultante.
        params = [];
        for(i in selection) {
          params.push(i + '=' + selection[i]);
        }
        params.push('path=' + this.result.path.split('?').shift());
        params.push('originale=' + this.originale.path.split('?').shift());

        var view = this;

        data = '&_method=GENERAIMG&' + params.join('&');

        // disabilito i bottoni
        this.disableButtons();

        $.ajax({
          type    : "POST",
          url     : fileAllegati.urlRoot,
          data    : data,
          dataType: "json",
          success : function(returned_data) {
            view.enableButtons();
            if(parseInt(returned_data.result) != 1) {
              Utility.alert({'message': returned_data.error});
            } else {
              // reimposto l'src
              view.$el.find('.risultato > img').attr('src', view.$el.find('.risultato > img').attr('src') + '?' + Math.random());
              newUrl = $('.versione:eq(' + returned_data.versione + ') img').attr('src').split('?').shift();
              $('.versione:eq(' + returned_data.versione + ') img').attr('src', newUrl + '?' + Math.random());
            }
          }
        });
      }
    },
    // mostro il dialog
    show          : function() {

      this.$el.appendTo('body').find('#modaleImmagineFX').modal();

      $('#modaleImmagineFX').on('hidden.bs.modal', function(e) {
        if(fileAllegati.ias) {
          fileAllegati.ias.remove();
        }
      });

      $('#modaleImmagineFX').find('.modal-fotoRitocco').css({'width': this.options.width + 25 + 'px'});

      // opzioni per imageareaselect
      opts = {
        handles    : true,
        instance   : true,
        aspectRatio: this.wResult + ':' + this.hResult,
        imageHeight: this.originale.height,
        imageWidth : this.originale.width,
        minWidth   : this.result.width,
        minHeight  : this.result.height,
        onSelectEnd: function(img, selection) {
          // aggiusto la selezione per le dimensioni minime
          if(selection.width < this.minWidth) {
            selection.width = this.minWidth;
          }
          if(selection.height < this.minHeight) {
            selection.height = this.minHeight;
          }
          if(selection.x1 + selection.width > this.imageWidth) {
            selection.x1 = this.imageWidth - selection.width;
          }
          selection.x2 = selection.x1 + selection.width;

          if(selection.y1 + selection.height > this.imageHeight) {
            selection.y1 = this.imageHeight - selection.height;
          }
          selection.y2 = selection.y1 + selection.height;
          fileAllegati.ias.setSelection(selection.x1, selection.y1, selection.x2, selection.y2);
        }
      };

      fileAllegati.log(opts);

      // lancio il plugin imageareaselect
      fileAllegati.ias = $('img#operaSuImmagine').imgAreaSelect(opts);

    }
  });


  // metodo di inizializzazione dell'ambaradan
  fileAllegati.boot = function(container, allegatiOptions) {
    Utility.log(allegatiOptions, {'trace': true});
    container = $(container);
    fileAllegati.debug = allegatiOptions.debug;
    // controllo del supporto al drag&drop
    fileAllegati.DD = (!!(window.File && window.FileList && window.FileReader) || !!window.FormData);
    if(typeof(allegatiOptions.uploadClassico) != 'undefined' && parseInt(allegatiOptions.uploadClassico) == 1) {
      fileAllegati.DD = false;
    }
    // debug vecchio browser fileAllegati.DD = false;
    fileAllegati.lingue = allegatiOptions.lingue;
    fileAllegati.log(fileAllegati.lingue);

    var index = new fileAllegati.Index({allegatiOptions: allegatiOptions});
    $(container).empty();
    $(container).append(index.render().el);

    var clipboard = new Clipboard('.copyImageUrl');

    clipboard.on('success', function(e) {
      Utility.alert({
        'type'   : 'info',
        'title'  : Utility.getLang('allegati', 'INDIRIZZO_IMMAGINE'),
        'message': Utility.getLang('allegati', 'COPIATO_IN_APPUNTI') + '<br /><br /> ' + e.text
      });

      e.clearSelection();
    });

    $(document).on('click', '#toggleAllegati', function() {
      var $container = $('.allegati').closest('.mainAllegatiContainer');
      Utility.log($container);
      Utility.log('Visibile: ' + $container.find('.hideShowAllegati:visible').length);
      if($container.find('.hideShowAllegati:visible').length) {
        $container.find('.hideShowAllegati').hide();
        $container.parent().find('#toggleAllegati').text(Utility.getLang('allegati', 'GESTISCI_ALLEGATI'));
      } else {
        $container.find('.hideShowAllegati').show();
        $container.parent().find('#toggleAllegati').text(Utility.getLang('allegati', 'NASCONDI_ALLEGATI'));
      }
    });

  }

})();