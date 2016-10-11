<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (08/06/16, 10.26)                                                                     **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
// il nome del namespace comincia sempre con Ueppy\controllers\$cmd, dove $cmd è il nome della directory
namespace Ueppy\controllers\pagina;

use Ueppy\utils\Utility;

// la classe si deve chiamare cmd (con la prima maiuscola) + "Controller"
// quindi da : pagina  -> PaginaController
// deve estendere sempre la classe omonima nel namespace \Ueppy\front.
// i metodi che possono essere implementati sono quelli pubblici,
// il cui nome è composto da out + "act" con la prima maiuscola)
// quindi se la route action è "read" il metodo che verrà chiamato
// sara outRead.
class PaginaController extends \Ueppy\front\PaginaController {

  function outRead() {

    $data = parent::outRead();

    // Utility::pre($data['SMARTY']); // tutti i dati ritornati a smarty.
    // Utility::pre($data['OBJ']); // la classe riempita con i dati che arrivano dal db

    // posso post-processare il sottotitolo semplicemente riscrivendo la variabile.
    // $data['SMARTY']['sottotitolo'] = $data['SMARTY']['sottotitolo'];

    return $data;

  }


}