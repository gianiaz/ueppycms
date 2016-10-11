/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (05/07/16, 9.01)                                                                     **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
$(function() {

  setTimeout(function() {
    scrollRssBenvenuto();
  }, 3000);

});

function scrollRssBenvenuto() {
  $('.panel-rssbenvenuto').scrollTo('li:eq(1)', 1000, {
    onAfter: function() {
      $('.panel-rssbenvenuto').find('li:first').appendTo($('.panel-rssbenvenuto ul'));
      $('.panel-rssbenvenuto').scrollTop(0);
      setTimeout(function() {
        scrollRssBenvenuto();
      }, 3000);
    }
  });
}