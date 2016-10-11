/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (13/07/16, 10.06)                                                                     **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
var googleChartLoaded = false;
$(function() {

  $('#container-stats').find('.prev-month').on('click', function(e) {
    e.preventDefault();
    var data = moment($('#container-stats').data('periodo') + '-01').subtract(1, 'M');
    $('#container-stats').data('periodo', data.format('YYYY-MM'));

    var mese = data.format("MMMM");
    mese = mese[0].toUpperCase() + mese.substr(1);
    $('.btn-display').html(mese + ' ' + data.format("YYYY"));
    $('.next-month').removeClass('disabled');
    estraiStatistiche();
  });

  $('#container-stats').find('.next-month').on('click', function(e) {
    e.preventDefault();
    Utility.log($('#container-stats').data('periodo'));
    var data = moment($('#container-stats').data('periodo') + '-01').add(1, 'M');
    Utility.log(data.format('YYYY-MM'));
    $('#container-stats').data('periodo', data.format('YYYY-MM'));

    today = moment();

    if(today.format('M') == data.format('M') && today.format('YYYY') == data.format('YYYY')) {
      $('.next-month').addClass('disabled');
    }
    var mese = data.format("MMMM");
    mese = mese[0].toUpperCase() + mese.substr(1);
    $('.btn-display').html(mese + ' ' + data.format("YYYY"));
    estraiStatistiche();
  });

  if($('#container-stats').length) {
    estraiStatistiche();
  }
});

function estraiStatistiche() {

  $('<div class="statsOverlay"></div>').appendTo($('.panel-stats'));
  $('<div class="statsLoader text-primary"><i class="fa fa-circle-o-notch fa-spin"></i></div>').appendTo('.panel-stats');

  $('#sistemioperativi-chart').empty();
  var data = {'periodo': $('#container-stats').data('periodo')};
  Utility.log(data);
  $.ajax({
    type    : "POST",
    url     : main_host + 'admin/stats/',
    data    : data,
    dataType: "json",
    success : function(ret_data) {
      if(parseInt(ret_data.result, 10) == 1) {

        $('.periodo').html(ret_data.start + ' - ' + ret_data.end);


        $('.statsOverlay').remove();
        $('.statsLoader').remove();


        // CHIAVI DI RICERCA
        var data = {
          'keyword': ret_data.data.trafficSources.keyword,
          'CHIAVI' : Utility.getLang('stats', 'CHIAVI')
        };
        Utility.template({
          name           : 'stats-chiavi',
          path           : '/admin/stats/',
          elem           : $('#stats-chiavi'),
          typeofinjection: 'html', // append, prepend, html
          data           : data
        });

        // UTENTI
        var data = {
          'nuovi'        : ret_data.data.users.nuovi,
          'totali'       : ret_data.data.users.totali,
          'UTENTI'       : Utility.getLang('stats', 'UTENTI'),
          'UTENTI_TOTALI': Utility.getLang('stats', 'UTENTI_TOTALI'),
          'NUOVI'        : Utility.getLang('stats', 'NUOVI')
        };
        Utility.template({
          name           : 'stats-utenti',
          path           : '/admin/stats/',
          elem           : $('#stats-utenti'),
          typeofinjection: 'html', // append, prepend, html
          data           : data
        });

        // SISTEMI OPERATIVI
        var data = {
          'SISTEMI_OPERATIVI': Utility.getLang('stats', 'SISTEMI_OPERATIVI')
        };
        Utility.template({
          name           : 'stats-so',
          path           : '/admin/stats/',
          callback       : function() {
            Morris.Donut({
              element: 'sistemioperativi-chart',
              data   : ret_data.data.platforms.operatingSystem,
              resize : true
            });
          },
          elem           : $('#sistemioperativi'),
          typeofinjection: 'html', // append, prepend, html
          data           : data
        });

        // DEVICES
        var data = {
          'DEVICES': Utility.getLang('stats', 'DEVICES')
        };
        Utility.template({
          name           : 'stats-devices',
          path           : '/admin/stats/',
          callback       : function() {
            Morris.Donut({
              element: 'devices-chart',
              data   : ret_data.data.platforms.devices,
              resize : true
            });
          },
          elem           : $('#devices'),
          typeofinjection: 'html', // append, prepend, html
          data           : data
        });


        // PROVENIENZA GEOGRAFICA
        var data = {
          'PROVENIENZA_GEOGRAFICA': Utility.getLang('stats', 'PROVENIENZA_GEOGRAFICA')
        };
        Utility.template({
          name           : 'stats-geo',
          path           : '/admin/stats/',
          callback       : function() {

            if(!googleChartLoaded) {
              googleChartLoaded = true;
              google.charts.load('current', {'packages': ['geomap']});
              google.charts.setOnLoadCallback(function() {
                  var options = {};
                  options['dataMode'] = 'regions';

                  var container = document.getElementById('world');
                  var geomap = new google.visualization.GeoMap(container);

                  geomap.draw(google.visualization.arrayToDataTable(ret_data.data.geo.country), options);
                }
              );
            } else {
              var options = {};
              options['dataMode'] = 'regions';

              var container = document.getElementById('world');
              var geomap = new google.visualization.GeoMap(container);

              geomap.draw(google.visualization.arrayToDataTable(ret_data.data.geo.country), options);
            }

          },
          elem           : $('#geo'),
          typeofinjection: 'html', // append, prepend, html
          data           : data
        });


        // PAGINE VISTE
        var data = {
          'pages'     : ret_data.data.pageTracking,
          'PAGE_VIEWS': Utility.getLang('stats', 'PAGE_VIEWS')
        };
        Utility.template({
          name           : 'stats-pagetracking',
          path           : '/admin/stats/',
          elem           : $('#pageviews'),
          typeofinjection: 'html', // append, prepend, html
          data           : data
        });

      } else {
        Utility.alert({message: ret_data.error});
      }
    }
  });
}