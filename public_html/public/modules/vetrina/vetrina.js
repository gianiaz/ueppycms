/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (13/06/16, 18.42)                                                                      **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
function avviaSliderVetrina() {

  $('.continue').on()
  var $sliderHome = $('#sliderHome');
  var $sliderHomeUL = $sliderHome.find('ul');

  $sliderHome.find('.continue').on('click', function(e) {
    var evt = {
      'categoria': 'Ecommerce',
      'azione'   : 'Click',
      'etichetta': 'Vetrina Home page'
    };
    Utility.registerEvent(evt);
  });

  if($sliderHomeUL.length) {
    $('<a id="sliderHomePrev"><span class="glyphicon glyphicon-chevron-left"></span></a><a id="sliderHomeNext"><span class="glyphicon glyphicon-chevron-right"></span></a>').appendTo($sliderHome.find('.main'));
    $sliderHomeUL.cycle({
      timeout          : 4000,
      fx               : 'fade',
      pager            : '#sliderHomePager',
      next             : '#sliderHomeNext',
      prev             : '#sliderHomePrev',
      delay            : 0,
      speed            : 1000,
      pause            : true,
      cleartypeNoBg    : true,
      pauseOnPagerHover: true
    });
    $('#sliderHomePager a').html('');
  }
}