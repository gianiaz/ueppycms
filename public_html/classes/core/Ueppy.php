<?php
/***************/
/** v.1.02    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.02 (08/07/2016, 14.41)                                                                   **/
/** - Aggiunta la possibilità di fornire un reply-to personalizzato                              **/
/**                                                                                              **/
/** v.1.01 (09/11/2015, 14.58)                                                                   **/
/** - Aggiuntp namespace per autoloading                                                         **/
/**                                                                                              **/
/** v.1.00 (29/10/15, 9.32)                                                                      **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
namespace Ueppy\core;

use Ueppy\core\EmailTemplates;
use Ueppy\utils\Utility;
use Ueppy\utils\Logger;

class Ueppy {

  static function sendMail($opts = null) {

    $opzioni                = [];
    $opzioni['emailCode']   = 'NESSUNO';
    $opzioni['replace']     = ['HOST' => HOST.REL_ROOT, 'NOME_SITO' => 'Sito '.str_replace('http://', '', HOST)];
    $opzioni['from']        = SET_EMAIL_SENDER;
    $opzioni['fromName']    = SET_SENDER_NAME;
    $opzioni['to']          = [];
    $opzioni['cc']          = [];
    $opzioni['bcc']         = [];
    $opzioni['replyTo']     = [SET_EMAIL_SENDER];
    $opzioni['smtpUser']    = SET_SMTP_USER;
    $opzioni['smtpPass']    = SET_SMTP_PASSWORD;
    $opzioni['smtpHost']    = SET_SMTP_HOST;
    $opzioni['smtpSSL']     = SET_SMTP_SSL;
    $opzioni['attachments'] = [];
    $opzioni['deepLogger']  = false;
    $opzioni['debug']       = 0;

    $re = '/\{([A-Z_]+)\}/';

    if($opts) {
      $opzioni = array_replace_recursive($opzioni, $opts);
    }

    $errore = false;
    $logs   = [];

    $db = debug_backtrace();

    $logs[] = date('H:i:s').' - Richiesta ricevuta da '.str_replace(DOC_ROOT, '', $db[0]['file'].'('.$db[0]['line'].')');

    if($opzioni['emailCode'] && $opzioni['to']) {

      $options                  = [];
      $options['tableFilename'] = 'emails';

      $emailObj = new EmailTemplates($options);
      $emailObj = $emailObj->getByKey($opzioni['emailCode']);

      if($emailObj) {

        $subject = $emailObj->fields['oggetto'];
        $testo   = $emailObj->fields['testo'];


        $chiavi = [];

        if(preg_match_all($re, $subject, $matches)) {
          $chiavi = array_merge($chiavi, $matches[1]);
        }

        if(preg_match_all($re, $testo, $matches)) {
          $chiavi = array_merge($chiavi, $matches[1]);
        }

        $logs[] = date('H:i:s').' - Chiavi trovate: '.implode(',', $chiavi);

        $replace = [];

        foreach($chiavi as $chiave) {
          $replace[$chiave] = '[NON RICEVUTO]';
        }

        $replace = array_replace_recursive($replace, $opzioni['replace']);

        $searchKey  = [];
        $replaceKey = [];

        foreach($replace as $key => $replace) {
          $searchKey[]  = '{'.$key.'}';
          $replaceKey[] = $replace;
        }

        $subject = str_replace($searchKey, $replaceKey, $subject);
        $testo   = str_replace($searchKey, $replaceKey, $testo);

        $isSmtp = false;

        if($opzioni['smtpHost']) {

          $isSmtp   = true;
          $smtpAuth = false;

          $smtpHost = $opzioni['smtpHost'];
          $smtpPort = 25;

          if(strpos($smtpHost, ':')) {
            list($smtpHost, $smtpPort) = explode(':', $smtpHost);
          }

          if($opzioni['smtpUser'] && $opzioni['smtpPass']) {
            $smtpAuth = true;
          }


        }

        if($isSmtp) {

          $log = 'L\'email verrà spedita tramite il server smtp '.$smtpHost.':'.$smtpPort;

          if($opzioni['smtpSSL']) {
            $log .= ' usando SSL';
          }

          if($smtpAuth) {
            $log .= ' autenticandosi con username "'.$opzioni['smtpUser'].'" e password '.substr_replace($opzioni['smtpPass'], '***', 0, 3);
          }

          $logs[] = $log;

        } else {
          $logs[] = date('H:i:s').' - L\'email viene spedita con la funzione mail();';
        }

        $to = $opzioni['to'];

        if(defined('SET_EMAIL_DEBUG') && SET_EMAIL_DEBUG) {
          $to    = array_map('trim', explode(';', SET_EMAIL_DEBUG));
          $testo = '<strong>DEBUG, destinatari in produzione: '.print_r($opzioni['to'], true).'</strong><br />'.$testo;
        }
        
        if(function_exists('mb_internal_encoding') && ((int)ini_get('mbstring.func_overload')) & 2) {
          $mbEncoding = mb_internal_encoding();
          mb_internal_encoding('ASCII');
        }

        $logs[] = date('H:i:s').' - Invio la mail con oggetto "'.$subject.'" a '.print_r($opzioni['to'], true);

        $message = \Swift_Message::newInstance()
          ->setSubject($subject)
          ->setFrom([$opzioni['from'] => $opzioni['fromName']])
          ->setTo($to)
          ->setReplyTo($opzioni['replyTo'])
          ->setBody($testo, 'text/html')
          ->addPart(strip_tags($testo), 'text/plain');


        $headers = $message->getHeaders();

        $headers->addTextHeader('X-Generator', 'Ueppy CMS');

        if($opzioni['cc']) {
          $message->setCc($opzioni['cc']);
        }

        if($opzioni['bcc']) {
          $message->setBcc($opzioni['bcc']);
        }

        foreach($opzioni['attachments'] as $file) {
          if(file_exists($file)) {
            $message->attach(\Swift_Attachment::fromPath($file));
            $logs[] = date('H:i:s').' - Il file "'.$file.'" è stato aggiunto';
          } else {
            $logs[] = date('H:i:s').' - Il file "'.$file.'" non esiste e non è stato aggiunto';
          }
        }

        if($isSmtp) {
          $transport = \Swift_SmtpTransport::newInstance($smtpHost, $smtpPort);
          if($smtpAuth) {
            $transport->setUsername($opzioni['smtpUser'])->setPassword($opzioni['smtpPass']);
          }
        } else {
          $transport = \Swift_MailTransport::newInstance();
        }

        $mailer = \Swift_Mailer::newInstance($transport);

        if($isSmtp && $opzioni['deepLogger']) {
          $logs[] = date('H:i:s').' - Aggiungo il logging del server SMTP';

          // To use the ArrayLogger
          $logger = new Swift_Plugins_Loggers_ArrayLogger();
          $mailer->registerPlugin(new Swift_Plugins_LoggerPlugin($logger));

        }

        $result = $mailer->send($message, $failures);

        if(!$result) {
          $logs[] = date('H:i:s').' - Problemi nell\'invio, '.print_r($failures, true);
          $logs[] = date('H:i:s').' - Vengono accodati i log di SWIFTMAILER';
          $logs[] = $logger->dump();
        } else {
          $logs[] = date('H:i:s').' - Messaggio spedito';
        }

        if(isset($mbEncoding)) {
          mb_internal_encoding($mbEncoding);
        }

      } else {

        if(!$opts['emailCode']) {
          $logs[] = date('H:i:s').' - Errore: Template email con chiave "'.$opzioni['emailCode'].'" non trovato';
        }

        if(!$opts['to']) {
          $logs[] = date('H:i:s').' - Errore: Destinatari non forniti';
        }

        $errore = true;
      }


    } else {
      $logs[] = date('H:i:s').' - Configurazione fornita errata '.print_r($opzioni, true);
      $errore = true;
    }

    if($errore || $opzioni['debug']) {
      Utility::pre($opzioni);
      Utility::pre(implode("\n", $logs));
    }

    $options                   = [];
    $options['tableFilename']  = 'logs';
    $options['autoLoadLabels'] = false;
    $LOGGER                    = new Logger($options);

    $LOGGER->addLine(['text' => implode("\n", $logs), 'azione' => 'SENDMAIL']);

    if($errore) {
      return false;
    }

    return $result;

  }

  static function loadAllegatiResources() {

    // SORTABLE JQUERY-UI
    $GLOBALS['pathJS'][] = DOC_ROOT.REL_ROOT.LIB.'html.sortable.min.js';

    /** DIPENDENZE BACKBONE **/
    $GLOBALS['pathJS'][] = DOC_ROOT.REL_ROOT.LIB.'modernizr.js';
    $GLOBALS['pathJS'][] = DOC_ROOT.REL_ROOT.LIB.'underscore-min.js';
    $GLOBALS['pathJS'][] = DOC_ROOT.REL_ROOT.LIB.'backbone-min.js';

    /** IMAGE AREA SELECT **/
    $GLOBALS['pathJS'][]  = DOC_ROOT.REL_ROOT.'bower_components/imgareaselect/jquery.imgareaselect.dev.js';
    $GLOBALS['pathCSS'][] = DOC_ROOT.REL_ROOT.'bower_components/imgareaselect/distfiles/css/imgareaselect-animated.css';

    /** PLUGIN ALLEGATI **/
    $GLOBALS['pathJS'][]  = DOC_ROOT.REL_ROOT.ADMIN_DIR.'generic/plugins/fileAllegati.js';

    $GLOBALS['pathJS'][] = DOC_ROOT.REL_ROOT.'bower_components/clipboard/dist/clipboard.min.js';
  }

  static function includeTinymce() {

    /* TINYMCE */
    $GLOBALS['pathJS'][] = DOC_ROOT.REL_ROOT.'bower_components/tinymce/tinymce.min.js';

    if(isset($GLOBALS['operator']) && $GLOBALS['operator'] && $GLOBALS['operator']->level >= 20) {
      Ueppy::includeCodeMirror();
    }

  }

  static function includeCodeMirror() {

    /** CODEMIRROR **/
    $GLOBALS['pathJS'][] = DOC_ROOT.REL_ROOT.'bower_components/codemirror/lib/codemirror.js';
    $GLOBALS['pathJS'][] = DOC_ROOT.REL_ROOT.'bower_components/codemirror/mode/xml/xml.js';
    $GLOBALS['pathJS'][] = DOC_ROOT.REL_ROOT.'bower_components/codemirror/mode/javascript/javascript.js';
    $GLOBALS['pathJS'][] = DOC_ROOT.REL_ROOT.'bower_components/codemirror/mode/css/css.js';
    $GLOBALS['pathJS'][] = DOC_ROOT.REL_ROOT.'bower_components/codemirror/mode/htmlmixed/htmlmixed.js';

    $GLOBALS['pathCSS'][] = DOC_ROOT.REL_ROOT.'bower_components/codemirror/lib/codemirror.css';
    $GLOBALS['pathCSS'][] = DOC_ROOT.REL_ROOT.'bower_components/codemirror/theme/dracula.css';

  }

  static function startTime($name, $description) {

    if(!isset($GLOBALS['viewManangerObj']->controller->DEBUGBAR)) {
      Utility::pre('test');
      die;
    }

    if($GLOBALS['viewManangerObj']->controller->DEBUGBAR) {
      $GLOBALS['viewManangerObj']->controller->DEBUGBAR['time']->startMeasure($name, $description);
    }
  }

  static function endTime($name) {

    if($GLOBALS['viewManangerObj']->controller->DEBUGBAR) {
      $GLOBALS['viewManangerObj']->controller->DEBUGBAR['time']->stopMeasure($name);
    }
  }

  static function info($message, $trace = true) {

    if($GLOBALS['viewManangerObj']->controller->DEBUGBAR) {

      if($trace) {

        $debug_array = debug_backtrace();
        $debug_array = array_reverse($debug_array);

        $string = [];
        foreach($debug_array as $da) {
          $f = $da['file'];
          if(defined('DOC_ROOT')) {
            $f = str_replace(DOC_ROOT, '', $f);
          }
          $string[] = $f.', '.$da['line'];
        }

        $GLOBALS['viewManangerObj']->controller->DEBUGBAR['messages']->info($string);

      }

      $GLOBALS['viewManangerObj']->controller->DEBUGBAR['messages']->info($message);

    }
  }

  static function error($message, $trace = true) {

    if(isset($GLOBALS['DEBUGBAR']) && $GLOBALS['DEBUGBAR']) {

      if($trace) {

        $debug_array = debug_backtrace();
        $debug_array = array_reverse($debug_array);

        $string = [];
        foreach($debug_array as $da) {
          $f = $da['file'];
          if(defined('DOC_ROOT')) {
            $f = str_replace(DOC_ROOT, '', $f);
          }
          $string[] = $f.', '.$da['line'];
        }

        $GLOBALS['DEBUGBAR']['messages']->info($string);

      }

      $GLOBALS['DEBUGBAR']['messages']->error($message);

    }
  }

  static function obfuscate($address) {

    $address       = strtolower($address);
    $coded         = "";
    $unmixedkey    = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789.@";
    $inprogresskey = $unmixedkey;
    $mixedkey      = "";
    $unshuffled    = strlen($unmixedkey);
    for($i = 0; $i <= strlen($unmixedkey); $i++) {
      $ranpos   = rand(0, $unshuffled - 1);
      $nextchar = @$inprogresskey{$ranpos};
      $mixedkey .= $nextchar;
      $before        = substr($inprogresskey, 0, $ranpos);
      $after         = substr($inprogresskey, $ranpos + 1, $unshuffled - ($ranpos + 1));
      $inprogresskey = $before.''.$after;
      $unshuffled -= 1;
    }
    $cipher = $mixedkey;

    $shift = strlen($address);

    $txt = "<script type=\"text/javascript\" language=\"javascript\">\n";
    for($j = 0; $j < strlen($address); $j++) {
      if(strpos($cipher, $address{$j}) == -1) {
        $chr = $address{$j};
        $coded .= $address{$j};
      } else {
        $chr = (strpos($cipher, $address{$j}) + $shift) % strlen($cipher);
        $coded .= $cipher{$chr};
      }
    }


    $txt .= "\ncoded = \"".$coded."\"\n".
      "  key = \"".$cipher."\"\n".
      "  shift=coded.length\n".
      "  link=\"\"\n".
      "  for (i=0; i<coded.length; i++) {\n".
      "    if (key.indexOf(coded.charAt(i))==-1) {\n".
      "      ltr = coded.charAt(i)\n".
      "      link += (ltr)\n".
      "    }\n".
      "    else {     \n".
      "      ltr = (key.indexOf(coded.charAt(i))-
shift+key.length) % key.length\n".
      "      link += (key.charAt(ltr))\n".
      "    }\n".
      "  }\n".
      "document.write(\"<a href='mailto:\"+link+\"'>\"+link+\"</a>\")\n".
      "\n".
      "//-"."->\n".
      "<"."/script><noscript>N/A".
      "<"."/noscript>";

    return $txt;


  }

}