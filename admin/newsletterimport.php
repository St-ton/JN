<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/admininclude.php';

$oAccount->permission('IMPORT_NEWSLETTER_RECEIVER_VIEW', true, true);

require_once PFAD_ROOT . PFAD_DBES . 'seo.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';

//jtl2
$format  = array('cAnrede', 'cVorname', 'cNachname', 'cEmail');
$hinweis = '';
$fehler  = '';

if (isset($_POST['newsletterimport']) && intval($_POST['newsletterimport']) === 1 && $_FILES['csv'] && validateToken()) {
    if (isset($_FILES['csv']['tmp_name']) && strlen($_FILES['csv']['tmp_name']) > 0) {
        $file = fopen($_FILES['csv']['tmp_name'], 'r');
        if ($file !== false) {
            $row      = 0;
            $formatId = -1;
            while ($data = fgetcsv($file, 2000, ';', '"')) {
                if ($row == 0) {
                    $hinweis .= 'Checke Kopfzeile ...';
                    $fmt = checkformat($data);
                    if ($fmt == -1) {
                        $fehler = 'Format nicht erkannt!';
                        break;
                    } else {
                        $hinweis .= '<br /><br />Importiere...<br />';
                    }
                } else {
                    $hinweis .= '<br />Zeile ' . $row . ': ' . processImport($fmt, $data);
                }

                $row++;
            }
            fclose($file);
        }
    }
}

$smarty->assign('sprachen', gibAlleSprachen())
       ->assign('kundengruppen', Shop::DB()->query("SELECT * FROM tkundengruppe ORDER BY cName", 2))
       ->assign('hinweis', $hinweis)
       ->assign('fehler', $fehler)
       ->assign('waehrung', (isset($standardwaehrung->cName) ? $standardwaehrung->cName : null))
       ->display('newsletterimport.tpl');

/**
 * Class NewsletterEmpfaenger
 */
class NewsletterEmpfaenger
{
    public $cAnrede;
    public $cEmail;
    public $cVorname;
    public $cNachname;
    public $kKunde = 0;
    public $kSprache;
    public $cOptCode;
    public $cLoeschCode;
    public $dEingetragen;
    public $nAktiv = 1;
}

/**
 * @param int $length
 * @param int $myseed
 * @return string
 */
function generatePW($length = 8, $myseed = 1)
{
    $dummy = array_merge(range('0', '9'), range('a', 'z'), range('A', 'Z'));
    mt_srand((double) microtime() * 1000000 * $myseed);
    for ($i = 1; $i <= (count($dummy) * 2); $i++) {
        $swap         = mt_rand(0, count($dummy) - 1);
        $tmp          = $dummy[$swap];
        $dummy[$swap] = $dummy[0];
        $dummy[0]     = $tmp;
    }

    return substr(implode('', $dummy), 0, $length);
}

/**
 * @param $cMail
 * @return bool
 */
function pruefeNLEBlacklist($cMail)
{
    $oNEB = Shop::DB()->query(
        "SELECT cMail
              FROM tnewsletterempfaengerblacklist
              WHERE cMail = '" . StringHandler::filterXSS(strip_tags($cMail)) . "'", 1
    );
    if (isset($oNEB->cMail) && strlen($oNEB->cMail) > 0) {
        return true;
    }

    return false;
}

/**
 * @param $data
 * @return array|int
 */
function checkformat($data)
{
    $fmt = array();
    $cnt = count($data);
    for ($i = 0; $i < $cnt; $i++) {
        // jtl-shop/issues#296
        if (!empty($data[$i]) && in_array($data[$i], $GLOBALS['format'])) {
            $fmt[$i] = $data[$i];
        }
    }
    if (!in_array('cEmail', $fmt)) {
        return -1;
    }

    return $fmt;
}

/**
 * OptCode erstellen und ueberpruefen
 * Werte fuer $dbfeld 'cOptCode','cLoeschCode'
 *
 * @param $dbfeld
 * @param $email
 * @return string
 */
function create_NewsletterCode($dbfeld, $email)
{
    $CodeNeu = md5($email . time() . rand(123, 456));
    while (!unique_NewsletterCode($dbfeld, $CodeNeu)) {
        $CodeNeu = md5($email . time() . rand(123, 456));
    }

    return $CodeNeu;
}

/**
 * @param $dbfeld
 * @param $code
 * @return bool
 */
function unique_NewsletterCode($dbfeld, $code)
{
    $res = Shop::DB()->query("SELECT * FROM tnewsletterempfaenger WHERE " . $dbfeld . "='" . $code . "'", 1);
    if ($res->kNewsletterEmpfaenger > 0) {
        return false;
    }

    return true;
}

/**
 * @param $fmt
 * @param $data
 * @return string
 */
function processImport($fmt, $data)
{
    if (isset($oTMP) && is_object($oTMP)) {
        unset($oTMP);
    }
    unset($newsletterempfaenger);

    $newsletterempfaenger = new NewsletterEmpfaenger();
    $cnt                  = count($fmt); // only columns that have no empty header jtl-shop/issues#296
    for ($i = 0; $i < $cnt; $i++) {
        if (!empty($fmt[$i])) {
            $newsletterempfaenger->{$fmt[$i]} = $data[$i];
        }
    }

    if (!valid_email($newsletterempfaenger->cEmail)) {
        return "keine g&uuml;ltige Email ($newsletterempfaenger->cEmail)! &Uuml;bergehe diesen Datensatz.";
    }
    // NewsletterEmpfaengerBlacklist
    if (pruefeNLEBlacklist($newsletterempfaenger->cEmail)) {
        return "keine g&uuml;ltige Email ($newsletterempfaenger->cEmail)! Kunde hat sich auf die Blacklist setzen lassen! &Uuml;bergehe diesen Datensatz.";
    }

    if (!$newsletterempfaenger->cNachname) {
        return 'kein Nachname! &Uuml;bergehe diesen Datensatz.';
    }

    $old_mail = Shop::DB()->query("SELECT kNewsletterEmpfaenger FROM tnewsletterempfaenger WHERE cEmail = '" . $newsletterempfaenger->cEmail . "'", 1);
    if ($old_mail->kNewsletterEmpfaenger > 0) {
        return "Newsletterempf&auml;nger mit dieser Emailadresse bereits vorhanden: ($newsletterempfaenger->cEmail)! &Uuml;bergehe Datensatz.";
    }

    if ($newsletterempfaenger->cAnrede === 'f') {
        $newsletterempfaenger->cAnrede = 'Frau';
    }
    if ($newsletterempfaenger->cAnrede === 'm' || $newsletterempfaenger->cAnrede === 'h') {
        $newsletterempfaenger->cAnrede = 'Herr';
    }
    $newsletterempfaenger->cOptCode    = create_NewsletterCode('cOptCode', $newsletterempfaenger->cEmail);
    $newsletterempfaenger->cLoeschCode = create_NewsletterCode('cLoeschCode', $newsletterempfaenger->cEmail);
    // Datum  des Eintrags setzen
    $newsletterempfaenger->dEingetragen = 'now()';
    $newsletterempfaenger->kSprache     = $_POST['kSprache'];
    // Ist der Newsletterempfaenger registrierter Kunde?
    $newsletterempfaenger->kKunde = 0;
    $KundenDaten                  = Shop::DB()->query("SELECT * FROM tkunde WHERE cMail = '" . $newsletterempfaenger->cEmail . "'", 1);
    if ($KundenDaten->kKunde > 0) {
        $newsletterempfaenger->kKunde   = $KundenDaten->kKunde;
        $newsletterempfaenger->kSprache = $KundenDaten->kSprache;
    }
    $oTMP               = new stdClass();
    $oTMP->cAnrede      = $newsletterempfaenger->cAnrede;
    $oTMP->cVorname     = $newsletterempfaenger->cVorname;
    $oTMP->cNachname    = $newsletterempfaenger->cNachname;
    $oTMP->kKunde       = $newsletterempfaenger->kKunde;
    $oTMP->cEmail       = $newsletterempfaenger->cEmail;
    $oTMP->dEingetragen = $newsletterempfaenger->dEingetragen;
    $oTMP->kSprache     = $newsletterempfaenger->kSprache;
    $oTMP->cOptCode     = $newsletterempfaenger->cOptCode;
    $oTMP->cLoeschCode  = $newsletterempfaenger->cLoeschCode;
    $oTMP->nAktiv       = $newsletterempfaenger->nAktiv;
    // In DB schreiben
    if (Shop::DB()->insert('tnewsletterempfaenger', $oTMP)) {
        // NewsletterEmpfaengerHistory fuettern
        unset($oTMP);
        $oTMP               = new stdClass();
        $oTMP->cAnrede      = $newsletterempfaenger->cAnrede;
        $oTMP->cVorname     = $newsletterempfaenger->cVorname;
        $oTMP->cNachname    = $newsletterempfaenger->cNachname;
        $oTMP->kKunde       = $newsletterempfaenger->kKunde;
        $oTMP->cEmail       = $newsletterempfaenger->cEmail;
        $oTMP->dEingetragen = $newsletterempfaenger->dEingetragen;
        $oTMP->kSprache     = $newsletterempfaenger->kSprache;
        $oTMP->cOptCode     = $newsletterempfaenger->cOptCode;
        $oTMP->cLoeschCode  = $newsletterempfaenger->cLoeschCode;
        $oTMP->cAktion      = 'Daten-Import';
        $res                = Shop::DB()->insert('tnewsletterempfaengerhistory', $oTMP);
        if ($res) {
            return 'Datensatz OK. Importiere: ' . $newsletterempfaenger->cVorname . ' ' . $newsletterempfaenger->cNachname;
        }
    }

    return 'Fehler beim Import dieser Zeile!';
}
