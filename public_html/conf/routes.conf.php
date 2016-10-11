<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (20/06/16, 9.15)                                                                     **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
$routes            = [];
$routes['default'] = 'home';

// ROUTE 404
$route['cmd']        = 'notFound';
$route['testGroups'] = [];

$testingGroup           = [];
$testingGroup['regex']  = '/^notFound\/$/';
$testingGroup['params'] = false;
$testingGroup['act']    = false;

$route['testGroups'][] = $testingGroup;

$routes['routes'][] = $route;

// ROUTE UTENTE
$route['cmd']        = 'utente';
$route['testGroups'] = [];

$testingGroup           = [];
$testingGroup['regex']  = '/^utente\/(?:([a-zA-Z\-]+)\/)?$/';
$testingGroup['params'] = ['url', 'act'];
$testingGroup['act']    = '';

$route['testGroups'][] = $testingGroup;

$routes['routes'][] = $route;

// ROUTE ORDINI
$route['cmd']        = 'orders';
$route['testGroups'] = [];

$testingGroup           = [];
$testingGroup['regex']  = '/^orders\/$/';
$testingGroup['params'] = ['url'];
$testingGroup['act']    = '';

$route['testGroups'][] = $testingGroup;

$testingGroup           = [];
$testingGroup['regex']  = '/^orders\/(pay)-([\d]+)\/$/';
$testingGroup['params'] = ['url', 'act', 'ordini_id'];
$testingGroup['act']    = '';

$route['testGroups'][] = $testingGroup;

$testingGroup           = [];
$testingGroup['regex']  = '/^orders\/fattura-([\d]+).pdf$/';
$testingGroup['params'] = ['url', 'ordine_id'];
$testingGroup['act']    = 'fattura';

$route['testGroups'][] = $testingGroup;

$routes['routes'][] = $route;

// ROUTE AJAX
$route['cmd']        = 'ajax';
$route['testGroups'] = [];

$testingGroup           = [];
$testingGroup['regex']  = '/^ajax\/((?:[A-Z|_|\-]+).(?:[\A-Z|_|\-])+)\/?$/';
$testingGroup['params'] = ['url', 'module'];
$testingGroup['act']    = false;

$route['testGroups'][] = $testingGroup;

$routes['routes'][] = $route;

// ROUTE CARRELLO
$route['cmd']        = 'carrello';
$route['testGroups'] = [];

$testingGroup           = [];
$testingGroup['regex']  = '/^carrello\/$/';
$testingGroup['params'] = ['url'];
$testingGroup['act']    = false;

$route['testGroups'][] = $testingGroup;

$routes['routes'][] = $route;

// ROUTE PREVENTIVO
$route['cmd']        = 'preventivo';
$route['testGroups'] = [];

$testingGroup           = [];
$testingGroup['regex']  = '/^preventivo\/$/';
$testingGroup['params'] = ['url'];
$testingGroup['act']    = false;

$route['testGroups'][] = $testingGroup;

$testingGroup           = [];
$testingGroup['regex']  = '/^preventivo\/confermato\/$/';
$testingGroup['params'] = ['url'];
$testingGroup['act']    = 'confermato';

$route['testGroups'][] = $testingGroup;

$routes['routes'][] = $route;

// ROUTE CHECKOUT
$route['cmd']        = 'checkout';
$route['testGroups'] = [];

$testingGroup           = [];
$testingGroup['regex']  = '/^checkout\/$/';
$testingGroup['params'] = ['url'];
$testingGroup['act']    = false;

$route['testGroups'][] = $testingGroup;

$routes['routes'][] = $route;

// ROUTE FASTCHECKOUT
$route['cmd']        = 'fastcheckout';
$route['testGroups'] = [];

$testingGroup           = [];
$testingGroup['regex']  = '/^fastcheckout\/$/';
$testingGroup['params'] = ['url'];
$testingGroup['act']    = false;

$route['testGroups'][] = $testingGroup;

$routes['routes'][] = $route;

// ROUTE PAGAMENTI
$route['cmd']        = 'pagamenti';
$route['testGroups'] = [];

$testingGroup           = [];
$testingGroup['regex']  = '/^pagamenti\/(?:([a-zA-Z\-]+)\/)?$/';
$testingGroup['params'] = ['url', 'act'];
$testingGroup['act']    = false;

$route['testGroups'][] = $testingGroup;

$routes['routes'][] = $route;

// CATALOGO
$route               = [];
$route['cmd']        = 'catalogo';
$route['testGroups'] = [];

$testingGroup           = [];
$testingGroup['regex']  = '/^catalogo\/$/';
$testingGroup['params'] = ['url'];
$testingGroup['act']    = 'root';

$route['testGroups'][] = $testingGroup;

$testingGroup           = [];
$testingGroup['regex']  = '/^catalogo\/([\d]+)-([^\/]+)\/pag([\d]+).html$/';
$testingGroup['params'] = ['url', 'id', 'href', 'pag'];
$testingGroup['act']    = 'list';

$route['testGroups'][] = $testingGroup;

$testingGroup           = [];
$testingGroup['regex']  = '/^catalogo\/([\d]+)-([^\/]+)\/$/';
$testingGroup['params'] = ['url', 'id', 'href'];
$testingGroup['act']    = 'list';

$route['testGroups'][] = $testingGroup;

$routes['routes'][] = $route;

// PRODOTTI
$route               = [];
$route['cmd']        = 'prodotti';
$route['testGroups'] = [];

$testingGroup           = [];
$testingGroup['regex']  = '/^catalogo\/([\d]+)-([^\/]+).html$/';
$testingGroup['params'] = ['url', 'id', 'href', 'pag'];
$testingGroup['act']    = 'read';

$route['testGroups'][] = $testingGroup;

$routes['routes'][] = $route;

// VOUCHERS
$route               = [];
$route['cmd']        = 'voucher';
$route['testGroups'] = [];

$testingGroup           = [];
$testingGroup['regex']  = '/^make-a-gift\/([\d]+)-([^\/]+)\/$/';
$testingGroup['params'] = ['url', 'id', 'operatore'];
$testingGroup['act']    = '';

$route['testGroups'][] = $testingGroup;

$testingGroup           = [];
$testingGroup['regex']  = '/^make-a-gift\/([\d]+)-([^\/]+)\/acquista\.html$/';
$testingGroup['params'] = ['url', 'id', 'operatore'];
$testingGroup['act']    = 'acquista';

$route['testGroups'][] = $testingGroup;

$testingGroup           = [];
$testingGroup['regex']  = '/^make-a-gift\/([\d]+)-([^\/]+)\/anteprima\.html$/';
$testingGroup['params'] = ['url', 'id', 'operatore'];
$testingGroup['act']    = 'anteprima';

$route['testGroups'][] = $testingGroup;

$testingGroup           = [];
$testingGroup['regex']  = '/^make-a-gift\/([\d]+)-([^\/]+)\/test-voucher\.html$/';
$testingGroup['params'] = ['url', 'id', 'operatore'];
$testingGroup['act']    = 'testvoucher';

$route['testGroups'][] = $testingGroup;

$testingGroup           = [];
$testingGroup['regex']  = '/^make-a-gift\/([\d]+)-([^\/]+)\/bonifico\.html$/';
$testingGroup['params'] = ['url', 'id', 'operatore'];
$testingGroup['act']    = 'bonifico';

$route['testGroups'][] = $testingGroup;

$testingGroup           = [];
$testingGroup['regex']  = '/^make-a-gift\/([\d]+)-([^\/]+)\/paypal\.html$/';
$testingGroup['params'] = ['url', 'id', 'operatore'];
$testingGroup['act']    = 'paypal';

$route['testGroups'][] = $testingGroup;

$testingGroup           = [];
$testingGroup['regex']  = '/^make-a-gift\/([\d]+)-([^\/]+)\/paypal\-ok.html$/';
$testingGroup['params'] = ['url', 'id', 'operatore'];
$testingGroup['act']    = 'paypalok';

$route['testGroups'][] = $testingGroup;

$testingGroup           = [];
$testingGroup['regex']  = '/^make-a-gift\/([\d]+)-([^\/]+)\/paypal\-ko.html$/';
$testingGroup['params'] = ['url', 'id', 'operatore'];
$testingGroup['act']    = 'paypalko';

$route['testGroups'][] = $testingGroup;

$testingGroup           = [];
$testingGroup['regex']  = '/^make-a-gift\/([\d]+)-([^\/]+)\/sella\-ok.html$/';
$testingGroup['params'] = ['url', 'id', 'operatore'];
$testingGroup['act']    = 'sellaok';

$route['testGroups'][] = $testingGroup;

$testingGroup           = [];
$testingGroup['regex']  = '/^make-a-gift\/([\d]+)-([^\/]+)\/sella\-ko.html$/';
$testingGroup['params'] = ['url', 'id', 'operatore'];
$testingGroup['act']    = 'sellako';

$route['testGroups'][] = $testingGroup;


$testingGroup           = [];
$testingGroup['regex']  = '/^make-a-gift\/([\d]+)-([^\/]+)\/sella.html$/';
$testingGroup['params'] = ['url', 'id', 'operatore'];
$testingGroup['act']    = 'sella';

$route['testGroups'][] = $testingGroup;

$routes['routes'][] = $route;

// CERCA
$route               = [];
$route['cmd']        = 'cerca';
$route['testGroups'] = [];

$testingGroup           = [];
$testingGroup['regex']  = '/^cerca\/$/';
$testingGroup['params'] = ['url'];
$testingGroup['act']    = '';

$route['testGroups'][] = $testingGroup;

$routes['routes'][] = $route;


// WISHLIST
$route               = [];
$route['cmd']        = 'wishlist';
$route['testGroups'] = [];

$testingGroup           = [];
$testingGroup['regex']  = '/^wishlist\/$/';
$testingGroup['params'] = ['url'];
$testingGroup['act']    = '';

$route['testGroups'][] = $testingGroup;

$routes['routes'][] = $route;

// NEWS
$route               = [];
$route['cmd']        = 'news';
$route['testGroups'] = [];

$testingGroup           = [];
$testingGroup['regex']  = '/^news\/$/';
$testingGroup['params'] = ['url'];
$testingGroup['act']    = 'list';

$route['testGroups'][] = $testingGroup;

$testingGroup           = [];
$testingGroup['regex']  = '/^news\/pagina([\d]+).html$/';
$testingGroup['params'] = ['url', 'pag'];
$testingGroup['act']    = 'list';

$route['testGroups'][] = $testingGroup;

$testingGroup           = [];
$testingGroup['regex']  = '/^news\/([^\/]+)\/$/';
$testingGroup['params'] = ['url', 'category'];
$testingGroup['act']    = 'list';

$route['testGroups'][] = $testingGroup;

$testingGroup           = [];
$testingGroup['regex']  = '/^news\/([^\/]+)\/pagina([\d]+).html$/';
$testingGroup['params'] = ['url', 'category', 'pag'];
$testingGroup['act']    = 'list';

$route['testGroups'][] = $testingGroup;

$testingGroup               = [];
$testingGroup['regex']      = '/^news\/tag\/([^\/]+)\/$/';
$testingGroup['params']     = ['url', 'tag'];
$testingGroup['act']        = 'list';
$testingGroup['noSanitize'] = ['tag'];

$route['testGroups'][] = $testingGroup;

$testingGroup               = [];
$testingGroup['regex']      = '/^news\/tag\/([^\/]+)\/pagina([\d]+).html$/';
$testingGroup['params']     = ['url', 'tag', 'pag'];
$testingGroup['act']        = 'list';
$testingGroup['noSanitize'] = ['tag'];

$route['testGroups'][] = $testingGroup;

$testingGroup           = [];
$testingGroup['regex']  = '/^news\/([^\/]+)\/([^.]+).html$/';
$testingGroup['params'] = ['url', 'category', 'href'];
$testingGroup['act']    = 'read';

$route['testGroups'][] = $testingGroup;

$testingGroup           = [];
$testingGroup['regex']  = '/^news\/([^.]+).html$/';
$testingGroup['params'] = ['url', 'href'];
$testingGroup['act']    = 'read';

$route['testGroups'][] = $testingGroup;

$routes['routes'][] = $route;

$route               = [];
$route['cmd']        = 'api';
$route['testGroups'] = [];

$testingGroup           = [];
$testingGroup['regex']  = '/^api\/([\d|\.]+)\/([^\/]+)\/(?:([^\/]+)\/)?$/';
$testingGroup['params'] = ['url', 'version', 'object', 'action'];
$testingGroup['act']    = '';

$route['testGroups'][] = $testingGroup;

$testingGroup           = [];
$testingGroup['regex']  = '/^test-api\/$/';
$testingGroup['params'] = ['url'];
$testingGroup['act']    = 'test';

$route['testGroups'][] = $testingGroup;

$routes['routes'][] = $route;

// --------------------------------------------------------------------------------- //
//      TUTTE LE EVENTUALI NUOVE REGOLE VANNO INSERITE PRIMA DI QUESTO PUNTO!!!      //
// --------------------------------------------------------------------------------- //

// PAGINE
$route               = [];
$route['cmd']        = 'pagina';
$route['testGroups'] = [];

$testingGroup           = [];
$testingGroup['regex']  = '/^(?:([^\/]+)\/)+pag([\d]).html$/';
$testingGroup['params'] = ['url', 'href', 'pagina'];
$testingGroup['act']    = 'list';

$route['testGroups'][] = $testingGroup;

$testingGroup           = [];
$testingGroup['regex']  = '/^(?:([^\/]+)\/)*(?:([^\/]+)\/)+$/';
$testingGroup['params'] = ['url', 'genitore', 'href'];
$testingGroup['act']    = 'list';

$route['testGroups'][] = $testingGroup;

$testingGroup           = [];
$testingGroup['regex']  = '/^^(?:([^\/]+)\/)*([^.]+).html$/';
$testingGroup['params'] = ['url', 'genitore', 'href'];
$testingGroup['act']    = 'read';

$route['testGroups'][] = $testingGroup;

$routes['routes'][] = $route;

$route        = [];
$route['cmd'] = 'home';

$testingGroup           = [];
$testingGroup['regex']  = '/^$/';
$testingGroup['params'] = [];
$testingGroup['act']    = '';

$route['testGroups'][] = $testingGroup;

$routes['routes'][] = $route;

$routes['default'] = 'notFound';
