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
    scroll();
  }, 3000);

});

function scroll() {
  $('.panel-rss').scrollTo('li:eq(1)', 1000, {
    onAfter: function() {
      $('.panel-rss').find('li:first').appendTo($('.panel-rss ul'));
      $('.panel-rss').scrollTop(0);
      setTimeout(function() {
        scroll();
      }, 3000);
    }
  });
}