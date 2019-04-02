<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Alert\Alert;
use JTL\Customer\Kunde;
use JTL\DB\ReturnType;
use JTL\Helpers\Form;
use JTL\Helpers\Text;
use JTL\Mail\Mail\Mail;
use JTL\Mail\Mailer;
use JTL\Shop;
use JTL\Sprache;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('IMPORT_CUSTOMER_VIEW', true, true);
/** @global \JTL\Smarty\JTLSmarty $smarty */
require_once PFAD_ROOT . PFAD_DBES . 'seo.php';

if (isset($_POST['kundenimport'], $_FILES['csv']['tmp_name'])
    && (int)$_POST['kundenimport'] === 1
    && $_FILES['csv']
    && Form::validateToken()
    && mb_strlen($_FILES['csv']['tmp_name']) > 0
) {
    $delimiter = getCsvDelimiter($_FILES['csv']['tmp_name']);
    $file      = fopen($_FILES['csv']['tmp_name'], 'r');
    if ($file !== false) {
        $format   = [
            'cPasswort',
            'cAnrede',
            'cTitel',
            'cVorname',
            'cNachname',
            'cFirma',
            'cStrasse',
            'cHausnummer',
            'cAdressZusatz',
            'cPLZ',
            'cOrt',
            'cBundesland',
            'cLand',
            'cTel',
            'cMobil',
            'cFax',
            'cMail',
            'cUSTID',
            'cWWW',
            'fGuthaben',
            'cNewsletter',
            'dGeburtstag',
            'fRabatt',
            'cHerkunft',
            'dErstellt',
            'cAktiv'
        ];
        $row      = 0;
        $fmt      = [];
        $formatId = -1;
        $notice   = '';
        while ($data = fgetcsv($file, 2000, $delimiter, '"')) {
            if ($row === 0) {
                $notice .= __('checkHead');
                $fmt     = checkformat($data, $format);
                if ($fmt === -1) {
                    $notice .= __('errorFormatNotFound');
                    break;
                }
                $notice .= '<br /><br />' . __('importPending') . '<br />';
            } else {
                $notice .= '<br />' . __('row') . $row . ': ' . processImport($fmt, $data);
            }

            $row++;
        }
        Shop::Container()->getAlertService()->addAlert(Alert::TYPE_NOTE, $notice, 'importNotice');
        fclose($file);
    }
}

$smarty->assign('sprachen', Sprache::getAllLanguages())
       ->assign('kundengruppen', Shop::Container()->getDB()->query(
           'SELECT * FROM tkundengruppe ORDER BY cName',
           ReturnType::ARRAY_OF_OBJECTS
       ))
       ->assign('step', $step ?? null)
       ->display('kundenimport.tpl');


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

    return mb_substr(implode('', $dummy), 0, $length);
}

/**
 * @param array $data
 * @param array $format
 * @return array|int
 */
function checkformat($data, $format)
{
    $fmt = [];
    $cnt = count($data);
    for ($i = 0; $i < $cnt; $i++) {
        if (in_array($data[$i], $format, true)) {
            $fmt[$i] = $data[$i];
        } else {
            $fmt[$i] = '';
        }
    }

    if ((int)$_POST['PasswortGenerieren'] !== 1) {
        if (!in_array('cPasswort', $fmt, true) || !in_array('cMail', $fmt, true)) {
            return -1;
        }
    } elseif (!in_array('cMail', $fmt, true)) {
        return -1;
    }

    return $fmt;
}

/**
 * @param array $fmt
 * @param array $data
 * @return string
 */
function processImport($fmt, $data)
{
    $kunde                = new Kunde();
    $kunde->kKundengruppe = (int)$_POST['kKundengruppe'];
    $kunde->kSprache      = (int)$_POST['kSprache'];
    $kunde->cAbgeholt     = 'Y';
    $kunde->cSperre       = 'N';
    $kunde->cAktiv        = 'Y';
    $kunde->nRegistriert  = 1;
    $kunde->dErstellt     = 'NOW()';
    $cnt                  = count($data);
    for ($i = 0; $i < $cnt; $i++) {
        if (!empty($fmt[$i])) {
            $kunde->{$fmt[$i]} = $data[$i];
        }
    }
    if (Text::filterEmailAddress($kunde->cMail) === false) {
        return __('errorInvalidEmail');
    }
    if ((int)$_POST['PasswortGenerieren'] !== 1
        && (!$kunde->cPasswort || $kunde->cPasswort === 'd41d8cd98f00b204e9800998ecf8427e')
    ) {
        return __('errorNoPassword');
    }
    if (!$kunde->cNachname) {
        return __('errorNoSurname');
    }

    $old_mail = Shop::Container()->getDB()->select('tkunde', 'cMail', $kunde->cMail);
    if (isset($old_mail->kKunde) && $old_mail->kKunde > 0) {
        return sprintf(__('errorEmailDuplicate'), $kunde->cMail);
    }
    if ($kunde->cAnrede === 'f' || mb_convert_case($kunde->cAnrede, MB_CASE_LOWER) === 'frau') {
        $kunde->cAnrede = 'w';
    }
    if ($kunde->cAnrede === 'h' || mb_convert_case($kunde->cAnrede, MB_CASE_LOWER) === 'herr') {
        $kunde->cAnrede = 'm';
    }
    if ($kunde->cNewsletter == 0 || $kunde->cNewsletter == 'NULL') {
        $kunde->cNewsletter = 'N';
    }
    if ($kunde->cNewsletter == 1) {
        $kunde->cNewsletter = 'Y';
    }

    if (empty($kunde->cLand)) {
        if (isset($_SESSION['kundenimport']['cLand']) && mb_strlen($_SESSION['kundenimport']['cLand']) > 0) {
            $kunde->cLand = $_SESSION['kundenimport']['cLand'];
        } else {
            $oRes = Shop::Container()->getDB()->query(
                "SELECT cWert AS cLand 
                    FROM teinstellungen 
                    WHERE cName = 'kundenregistrierung_standardland'",
                ReturnType::SINGLE_OBJECT
            );
            if (is_object($oRes) && isset($oRes->cLand) && mb_strlen($oRes->cLand) > 0) {
                $_SESSION['kundenimport']['cLand'] = $oRes->cLand;
                $kunde->cLand                      = $oRes->cLand;
            }
        }
    }
    $cPasswortKlartext = '';
    if ((int)$_POST['PasswortGenerieren'] === 1) {
        $cPasswortKlartext = Shop::Container()->getPasswordService()->generate(PASSWORD_DEFAULT_LENGTH);
        $kunde->cPasswort  = Shop::Container()->getPasswordService()->hash($cPasswortKlartext);
    }
    $tmp              = new stdClass();
    $tmp->cNachname   = $kunde->cNachname;
    $tmp->cFirma      = $kunde->cFirma;
    $tmp->cStrasse    = $kunde->cStrasse;
    $tmp->cHausnummer = $kunde->cHausnummer;
    if ($kunde->insertInDB()) {
        if ((int)$_POST['PasswortGenerieren'] === 1) {
            $kunde->cPasswortKlartext = $cPasswortKlartext;
            $kunde->cNachname         = $tmp->cNachname;
            $kunde->cFirma            = $tmp->cFirma;
            $kunde->cStrasse          = $tmp->cStrasse;
            $kunde->cHausnummer       = $tmp->cHausnummer;
            $obj                      = new stdClass();
            $obj->tkunde              = $kunde;
            $mailer                   = Shop::Container()->get(Mailer::class);
            $mail                     = new Mail();
            $mailer->send($mail->createFromTemplateID(MAILTEMPLATE_ACCOUNTERSTELLUNG_DURCH_BETREIBER, $obj));
        }

        return __('importRecord') . $kunde->cVorname . ' ' . $kunde->cNachname;
    }

    return __('errorImportRecord');
}
