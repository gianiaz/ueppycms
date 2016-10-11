<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (23/05/16, 19.00)                                                                     **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
use Ueppy\utils\Utility;
use Ueppy\utils\Zip;
use Ueppy\core\Traduzioni;

if(basename($_SERVER['SCRIPT_FILENAME']) == 'crontab.php') {
  $debug = false;

  $PATHTraduzioni = DOC_ROOT.REL_ROOT.UPLOAD.'traduzioni/';

// update nazioni
  $URLversione        = SET_TRADUZIONI_UPDATE_URL.'traduzioni.version';
  $PATHversioneLocale = $PATHTraduzioni.'traduzioni.version';
  $versioneLocale     = 1;

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_URL, $URLversione);
  $versioneOnline = trim(curl_exec($ch));


  if(file_exists($PATHversioneLocale)) {
    $versioneLocale = trim(file_get_contents($PATHversioneLocale));
  }

  if($debug) {
    echo 'Versione online presente: '.$versioneOnline."\n";
    echo 'Versione locale: '.$versioneLocale."\n";
  }

  if(version_compare($versioneOnline, $versioneLocale)) {

    $URLZip  = SET_TRADUZIONI_UPDATE_URL.'traduzioni.'.$versioneOnline.'.zip';
    $PATHZip = $PATHTraduzioni.'traduzioni.'.$versioneOnline.'.zip';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $URLZip);
    $response = curl_exec($ch);

    file_put_contents($PATHZip, $response);

    $zip = new Zip();
    if($zip->open($PATHZip)) {
      $zip->extractTo($PATHTraduzioni);
      $zip->close();

      $options                  = [];
      $options['tableFilename'] = 'traduzioni_ueppy';
      $options['forceAllLang']  = 1;
      $options['langs']         = ['it', 'en', 'de', 'fr', 'ru', 'es'];

      $TraduzioniObj = new Traduzioni($options);

      $out = $TraduzioniObj->updateFromJson($debug);

      $LOGGER->addLine(['text' => $out, 'pop' => 'false']);

      if(!$debug) {
        $TraduzioniObj->export();

        $LOGGER->addLine(['text' => 'Aggiornate le traduzioni alla versione '.$versioneOnline, 'pop' => 'false']);

      }

    }

  }
}