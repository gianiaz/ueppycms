<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (17/06/16, 15.42)                                                                      **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
$dir = DOC_ROOT.REL_ROOT.UPLOAD.'tinymcetpl/';

$desc = glob(DOC_ROOT.REL_ROOT.UPLOAD.'tinymcetpl/*.desc');

$out = 'templates = [';
$out .= "\n";

$righe = [];

if(is_array($desc)) {

  foreach($desc as $file) {

    $tpl = explode('.', basename($file));

    $tpl = dirname($file).'/'.array_shift($tpl).'.tp';

    $metadata = explode('§', file_get_contents($file));

    if(file_exists($tpl)) {

      $righe[] = '{title: "'.str_replace('"', '\"', $metadata[0]).'",url: "'.str_replace(DOC_ROOT, '', $tpl).'", description: "'.str_replace('"', '\"', $metadata[1]).'"}';

    }

  }

}
$out .= implode(",\n", $righe);
$out .= "]";
echo $out;
die;
