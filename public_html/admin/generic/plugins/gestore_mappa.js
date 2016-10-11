/*****************/
/***ueppy3.1.00***/
/*****************/
/**  CHANGELOG  **/
/**************************************************************************************************/
/** v.3.1.01 (01/04/2014)                                                                        **/
/** - Aggiustamenti pignoli nel codice                                                           **/
/**                                                                                              **/
/** v.3.1.00 (01/01/2013)                                                                        **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/

map = false;

$(window).load(function() {

  $('#centerAddr').button();
  $('#centerCoords').button();

  $('#mapTrigger').show().button({'icons':{'primary':'ui-icon-image'}});

  $('#mapTrigger').click(function() {

    buttons = {};

    buttons[Utility.getLang('base', 'CLOSE')] = function() {
                                                   $('#mapZoom').val(map.getZoom());
                                                   $('#mapcontainer').dialog('destroy');
                                                   $('#mapcontainer').appendTo($('form').get(0));
                                                   if(typeof(mapCallback) == 'function') {
                                                     mapCallback();
                                                   }
                                                 };

    $('#mapcontainer').dialog({
      title: Utility.getLang('mappa', 'POSTIZIONA_MARCATORE'),
      modal : true,
      overlay: {
        backgroundColor: '#000',
        opacity: 0.5
      },
      buttons: buttons,
      close: function(event, ui) { $('#mapZoom').val(map.getZoom());
                                   $('#mapcontainer').dialog('destroy');
                                   $('#mapcontainer').appendTo($('form').get(0));
                                   if(typeof(mapCallback) == 'function') {
                                     mapCallback();
                                   }
                                 },
      position: new Array('center', 20, 'center', 0) ,
      width: '740px'
    });

    mapZoom = parseInt($('#mapZoom').val(), 10);
    if(mapZoom === 0) {
      mapZoom = 10;
    }

    mapLng = parseFloat($('#mapLng').val(), 10);
    if(mapLng === 0) {
      mapLng = 12.4942486;
    }

    mapLat = parseFloat($('#mapLat').val(), 10);
    if(mapLat === 0) {
      mapLat = 41.8905198;
    }

    center = new google.maps.LatLng(mapLat, mapLng);

    var myOptions = {
        zoom: mapZoom,
        mapTypeId: google.maps.MapTypeId.ROADMAP
    };

    map = new google.maps.Map($('#mapcontainer .mappa').get(0), myOptions);
    map.setCenter(center);
    var marker = new google.maps.Marker({
          position: center,
          draggable: true,
          map: map
    });
    google.maps.event.addListener(marker, 'dragend', function() {
      center = marker.getPosition();
      setLatLng(center);
      map.setCenter(center);
    });

    geocoder = new google.maps.Geocoder();

    $('#centerAddr').click(function() {
      var address = $('#mapAddress').val();
      geocoder.geocode( { 'address': address}, function(results, status) {
        if (status == google.maps.GeocoderStatus.OK) {
          center = results[0].geometry.location;
          setLatLng(center);
          map.setCenter(center);
          marker.setPosition(center);
        } else {
          modal_dialog(Utility.getLang('base', 'WARNING'), Utility.getLang('mappa', 'GEOCODE_FALLITO')+"\n"+status);
        }
      });
    });

    $('#centerCoords').click(function() {
      center = new google.maps.LatLng($('#mapLat').val(), $('#mapLng').val());
      map.setCenter(center);
      marker.setPosition(center);
    });

    campi = $('#mapAddressFields').val().split(' ');

    var addressValue = [];

    for(i = 0; i < campi.length; i++) {
      if(campi[i][0] == '#' || campi[i][0] == '.') {
        addressValue.push($(campi[i]).val());
      } else {
        addressValue.push(campi[i]);
      }
    }

    addressValue = addressValue.join(' ');

    $('#mapAddress').val(addressValue);


  });

});

function setLatLng(latlng) {

  lat = parseInt(latlng.lat()*10000000000, 10)/10000000000;
  lng = parseInt(latlng.lng()*10000000000, 10)/10000000000;

  $('#mapLat').val(lat);
  $('#mapLng').val(lng);

}