<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\Form;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('IMPORT_CUSTOMER_VIEW', true, true);
/** @global Smarty\JTLSmarty $smarty */
require_once PFAD_ROOT . PFAD_DBES . 'seo.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'tools.Global.php';

$format = [
    'cPasswort', 'cAnrede', 'cTitel', 'cVorname', 'cNachname', 'cFirma',
    'cStrasse', 'cHausnummer', 'cAdressZusatz', 'cPLZ', 'cOrt', 'cBundesland',
    'cLand', 'cTel', 'cMobil', 'cFax', 'cMail', 'cUSTID', 'cWWW', 'fGuthaben',
    'cNewsletter', 'dGeburtstag', 'fRabatt', 'cHerkunft', 'dErstellt', 'cAktiv'
];

if (isset($_POST['kundenimport'], $_FILES['csv']['tmp_name'])
    && (int)$_POST['kundenimport'] === 1
    && $_FILES['csv']
    && Form::validateToken()
    && strlen($_FILES['csv']['tmp_name']) > 0
) {
    $delimiter = getCsvDelimiter($_FILES['csv']['tmp_name']);
    $file      = fopen($_FILES['csv']['tmp_name'], 'r');
    if ($file !== false) {
        $row      = 0;
        $fmt      = [];
        $formatId = -1;
        $hinweis  = '';
        while ($data = fgetcsv($file, 2000, $delimiter, '"')) {
            if ($row === 0) {
                $hinweis .= __('checkHead');
                $fmt      = checkformat($data);
                if ($fmt === -1) {
                    $hinweis .= __('errorFormatNotFound');
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
       ->assign('step', $step ?? null)
       ->assign('hinweis', $hinweis ?? null)
       ->display('kundenimport.tpl');


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
 * @param array $data
 * @return array|int
 */
function checkformat($data)
{
    $fmt = [];
    $cnt = count($data);
    for ($i = 0; $i < $cnt; $i++) {
        if (in_array($data[$i], $GLOBALS['format'], true)) {
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
    if (StringHandler::filterEmailAddress($kunde->cMail) === false) {
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
    if ($kunde->cAnrede === 'f' || strtolower($kunde->cAnrede) === 'frau') {
        $kunde->cAnrede = 'w';
    }
    if ($kunde->cAnrede === 'h' || strtolower($kunde->cAnrede) === 'herr') {
        $kunde->cAnrede = 'm';
    }
    if ($kunde->cNewsletter == 0 || $kunde->cNewsletter == 'NULL') {
        $kunde->cNewsletter = 'N';
    }
    if ($kunde->cNewsletter == 1) {
        $kunde->cNewsletter = 'Y';
    }

    if (empty($kunde->cLand)) {
        if (isset($_SESSION['kundenimport']['cLand']) && strlen($_SESSION['kundenimport']['cLand']) > 0) {
            $kunde->cLand = $_SESSION['kundenimport']['cLand'];
        } else {
            $oRes = Shop::Container()->getDB()->query(
                "SELECT cWert AS cLand 
                    FROM teinstellungen 
                    WHERE cName = 'kundenregistrierung_standardland'",
                \DB\ReturnType::SINGLE_OBJECT
            );
            if (is_object($oRes) && isset($oRes->cLand) && strlen($oRes->cLand) > 0) {
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
    $oTMP              = new stdClass();
    $oTMP->cNachname   = $kunde->cNachname;
    $oTMP->cFirma      = $kunde->cFirma;
    $oTMP->cStrasse    = $kunde->cStrasse;
    $oTMP->cHausnummer = $kunde->cHausnummer;
    if ($kunde->insertInDB()) {
        if ((int)$_POST['PasswortGenerieren'] === 1) {
            $kunde->cPasswortKlartext = $cPasswortKlartext;
            $kunde->cNachname         = $oTMP->cNachname;
            $kunde->cFirma            = $oTMP->cFirma;
            $kunde->cStrasse          = $oTMP->cStrasse;
            $kunde->cHausnummer       = $oTMP->cHausnummer;
            $obj                      = new stdClass();
            $obj->tkunde              = $kunde;
            sendeMail(MAILTEMPLATE_ACCOUNTERSTELLUNG_DURCH_BETREIBER, $obj);
        }

        return __('importRecord') . $kunde->cVorname . ' ' . $kunde->cNachname;
    }

    return __('errorImportRecord');
}
