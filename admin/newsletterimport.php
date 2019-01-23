<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\Form;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('IMPORT_NEWSLETTER_RECEIVER_VIEW', true, true);
/** @global Smarty\JTLSmarty $smarty */
require_once PFAD_ROOT . PFAD_DBES . 'seo.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';

//jtl2
$format  = ['cAnrede', 'cVorname', 'cNachname', 'cEmail'];
$hinweis = '';
$fehler  = '';

if (isset($_POST['newsletterimport'], $_FILES['csv']['tmp_name'])
    && (int)$_POST['newsletterimport'] === 1
    && Form::validateToken()
    && strlen($_FILES['csv']['tmp_name']) > 0
) {
    $file = fopen($_FILES['csv']['tmp_name'], 'r');
    if ($file !== false) {
        $row      = 0;
        $formatId = -1;
        $fmt      = [];
        while ($data = fgetcsv($file, 2000, ';', '"')) {
            if ($row === 0) {
                $hinweis .= __('checkHead');
                $fmt      = checkformat($data);
                if ($fmt === -1) {
                    $fehler = __('errorFormatUnknown');
                    break;
                }
                $hinweis .= '<br /><br />' . __('importPending') . '<br />';
            } else {
                $hinweis .= '<br />' . __('row') . $row . ': ' . processImport($fmt, $data);
            }
            $row++;
        }
        fclose($file);
    }
}

$smarty->assign('sprachen', Sprache::getAllLanguages())
       ->assign('kundengruppen', Shop::Container()->getDB()->query(
           'SELECT * FROM tkundengruppe ORDER BY cName',
           \DB\ReturnType::ARRAY_OF_OBJECTS
       ))
       ->assign('hinweis', $hinweis)
       ->assign('fehler', $fehler)
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
    mt_srand((double)microtime() * 1000000 * $myseed);
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
    $oNEB = Shop::Container()->getDB()->select(
        'tnewsletterempfaengerblacklist',
        'cMail',
        StringHandler::filterXSS(strip_tags($cMail))
    );

    return !empty($oNEB->cMail);
}

/**
 * @param array $data
 * @return array|int
 */
function checkformat($data)
{
    $fmt = [];
    $cnt = count($data);
    for ($i = 0; $i < $cnt; $i++) {
        // jtl-shop/issues#296
        if (!empty($data[$i]) && in_array($data[$i], $GLOBALS['format'], true)) {
            $fmt[$i] = $data[$i];
        }
    }
    if (!in_array('cEmail', $fmt, true)) {
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
    $res = Shop::Container()->getDB()->select('tnewsletterempfaenger', $dbfeld, $code);

    return !(isset($res->kNewsletterEmpfaenger) && $res->kNewsletterEmpfaenger > 0);
}

/**
 * @param $fmt
 * @param $data
 * @return string
 */
function processImport($fmt, $data)
{
    $recipient = new NewsletterEmpfaenger();
    $cnt       = count($fmt); // only columns that have no empty header jtl-shop/issues#296
    for ($i = 0; $i < $cnt; $i++) {
        if (!empty($fmt[$i])) {
            $recipient->{$fmt[$i]} = $data[$i];
        }
    }

    if (StringHandler::filterEmailAddress($recipient->cEmail) === false) {
        return sprintf(__('errorEmailInvalid'), $recipient->cEmail);
    }
    if (pruefeNLEBlacklist($recipient->cEmail)) {
        return __('errorEmailInvalidBlacklist');
    }
    if (!$recipient->cNachname) {
        return __('errorSurnameMissing');
    }

    $oldMail = Shop::Container()->getDB()->select('tnewsletterempfaenger', 'cEmail', $recipient->cEmail);
    if (isset($oldMail->kNewsletterEmpfaenger) && $oldMail->kNewsletterEmpfaenger > 0) {
        return sprintf(__('errorEmailExists'), $recipient->cEmail);
    }

    if ($recipient->cAnrede === 'f') {
        $recipient->cAnrede = 'Frau';
    }
    if ($recipient->cAnrede === 'm' || $recipient->cAnrede === 'h') {
        $recipient->cAnrede = 'Herr';
    }
    $recipient->cOptCode     = create_NewsletterCode('cOptCode', $recipient->cEmail);
    $recipient->cLoeschCode  = create_NewsletterCode('cLoeschCode', $recipient->cEmail);
    $recipient->dEingetragen = 'NOW()';
    $recipient->kSprache     = $_POST['kSprache'];
    $recipient->kKunde       = 0;

    $KundenDaten = Shop::Container()->getDB()->select('tkunde', 'cMail', $recipient->cEmail);
    if ($KundenDaten->kKunde > 0) {
        $recipient->kKunde   = $KundenDaten->kKunde;
        $recipient->kSprache = $KundenDaten->kSprache;
    }
    $ins               = new stdClass();
    $ins->cAnrede      = $recipient->cAnrede;
    $ins->cVorname     = $recipient->cVorname;
    $ins->cNachname    = $recipient->cNachname;
    $ins->kKunde       = $recipient->kKunde;
    $ins->cEmail       = $recipient->cEmail;
    $ins->dEingetragen = $recipient->dEingetragen;
    $ins->kSprache     = $recipient->kSprache;
    $ins->cOptCode     = $recipient->cOptCode;
    $ins->cLoeschCode  = $recipient->cLoeschCode;
    $ins->nAktiv       = $recipient->nAktiv;
    if (Shop::Container()->getDB()->insert('tnewsletterempfaenger', $ins)) {
        $ins               = new stdClass();
        $ins->cAnrede      = $recipient->cAnrede;
        $ins->cVorname     = $recipient->cVorname;
        $ins->cNachname    = $recipient->cNachname;
        $ins->kKunde       = $recipient->kKunde;
        $ins->cEmail       = $recipient->cEmail;
        $ins->dEingetragen = $recipient->dEingetragen;
        $ins->kSprache     = $recipient->kSprache;
        $ins->cOptCode     = $recipient->cOptCode;
        $ins->cLoeschCode  = $recipient->cLoeschCode;
        $ins->cAktion      = 'Daten-Import';
        $res               = Shop::Container()->getDB()->insert('tnewsletterempfaengerhistory', $ins);
        if ($res) {
            return __('successImport') .
                $recipient->cVorname . ' ' .
                $recipient->cNachname;
        }
    }

    return __('errorImportRow');
}
