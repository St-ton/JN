<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\GeneralObject;
use Helpers\Request;

/**
 * @param array            $params
 * @param Smarty\JTLSmarty $smarty
 * @return string
 */
function includeMailTemplate($params, $smarty)
{
    if (isset($params['template'], $params['type'])
        && ($params['type'] === 'text' || $params['type'] === 'plain' || $params['type'] === 'html')
        && $smarty->getTemplateVars('int_lang') !== null
    ) {
        $res            = null;
        $currenLanguage = null;
        $vorlage        = Shop::Container()->getDB()->select(
            'temailvorlageoriginal',
            'cDateiname',
            $params['template']
        );
        if (isset($vorlage->kEmailvorlage) && $vorlage->kEmailvorlage > 0) {
            $row            = 'cContentText';
            $currenLanguage = $smarty->getTemplateVars('int_lang');
            if ($params['type'] === 'html') {
                $row = 'cContentHtml';
            }
            $res = Shop::Container()->getDB()->query(
                'SELECT ' . $row . ' AS content
                    FROM temailvorlagesprache
                    WHERE kSprache = ' . (int)$currenLanguage->kSprache .
                ' AND kEmailvorlage = ' . (int)$vorlage->kEmailvorlage,
                \DB\ReturnType::SINGLE_OBJECT
            );
        }
        if (isset($res->content)) {
            if ($params['type'] === 'plain') {
                $params['type'] = 'text';
            }

            return $smarty->fetch('db:' . $params['type'] . '_' .
                $vorlage->kEmailvorlage . '_' . $currenLanguage->kSprache);
        }
    }

    return '';
}

/**
 * @param string        $ModulId
 * @param stdClass      $Object
 * @param null|stdClass $mail
 * @return null|bool|stdClass
 */
function sendeMail($ModulId, $Object, $mail = null)
{
    $db           = Shop::Container()->getDB();
    $mailTPL = null;
    $bodyHtml     = '';
    if (!is_object($mail)) {
        $mail = new stdClass();
    }
    $config        = Shop::getSettings([
        CONF_EMAILS,
        CONF_ZAHLUNGSARTEN,
        CONF_GLOBAL,
        CONF_KAUFABWICKLUNG,
        CONF_KONTAKTFORMULAR,
        CONF_ARTIKELDETAILS,
        CONF_TRUSTEDSHOPS
    ]);
    $absender_name = $config['emails']['email_master_absender_name'];
    $absender_mail = $config['emails']['email_master_absender'];
    $kopie         = '';
    $mailSmarty    = new \Smarty\JTLSmarty(true, \Smarty\ContextType::MAIL);
    $mailSmarty->registerResource('db', new \Smarty\SmartyResourceNiceDB($db, \Smarty\ContextType::MAIL))
               ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'includeMailTemplate', 'includeMailTemplate')
               ->setCaching(0)
               ->setDebugging(0)
               ->setCompileDir(PFAD_ROOT . PFAD_COMPILEDIR)
               ->setTemplateDir(PFAD_ROOT . PFAD_EMAILTEMPLATES);
    if (MAILTEMPLATE_USE_SECURITY) {
        $mailSmarty->activateBackendSecurityMode();
    }
    if (!isset($Object->tkunde)) {
        $Object->tkunde = new stdClass();
    }
    if (!isset($Object->tkunde->kKundengruppe) || !$Object->tkunde->kKundengruppe) {
        $Object->tkunde->kKundengruppe = Kundengruppe::getDefaultGroupID();
    }
    $Object->tfirma        = $db->query('SELECT * FROM tfirma', \DB\ReturnType::SINGLE_OBJECT);
    $Object->tkundengruppe = $db->select(
        'tkundengruppe',
        'kKundengruppe',
        (int)$Object->tkunde->kKundengruppe
    );
    if (isset($Object->tkunde->kSprache) && $Object->tkunde->kSprache > 0) {
        $kundengruppensprache = $db->select(
            'tkundengruppensprache',
            'kKundengruppe',
            (int)$Object->tkunde->kKundengruppe,
            'kSprache',
            (int)$Object->tkunde->kSprache
        );
        if (isset($kundengruppensprache->cName) && $kundengruppensprache->cName !== $Object->tkundengruppe->cName) {
            $Object->tkundengruppe->cName = $kundengruppensprache->cName;
        }
    }
    if (isset($_SESSION['currentLanguage']->kSprache)) {
        $lang = $_SESSION['currentLanguage'];
    } else {
        if (isset($Object->tkunde->kSprache) && $Object->tkunde->kSprache > 0) {
            $lang = $db->select('tsprache', 'kSprache', (int)$Object->tkunde->kSprache);
        }
        if (isset($Object->NewsletterEmpfaenger->kSprache) && $Object->NewsletterEmpfaenger->kSprache > 0) {
            $lang = $db->select(
                'tsprache',
                'kSprache',
                $Object->NewsletterEmpfaenger->kSprache
            );
        }
        if (empty($lang)) {
            $lang = isset($_SESSION['kSprache'])
                ? $db->select('tsprache', 'kSprache', $_SESSION['kSprache'])
                : $db->select('tsprache', 'cShopStandard', 'Y');
        }
    }
    $oKunde = lokalisiereKunde($lang, $Object->tkunde);

    $mailSmarty->assign('int_lang', $lang)//assign the current language for includeMailTemplate()
               ->assign('Firma', $Object->tfirma)
               ->assign('Kunde', $oKunde)
               ->assign('Kundengruppe', $Object->tkundengruppe)
               ->assign('NettoPreise', $Object->tkundengruppe->nNettoPreise)
               ->assign('ShopLogoURL', Shop::getLogo(true))
               ->assign('ShopURL', Shop::getURL());

    $AGB                   = new stdClass();
    $WRB                   = new stdClass();
    $WRBForm               = new stdClass();
    $DSE                   = new stdClass();
    $oAGBWRB               = $db->select(
        'ttext',
        ['kSprache', 'kKundengruppe'],
        [(int)$lang->kSprache, (int)$Object->tkunde->kKundengruppe]
    );
    $AGB->cContentText     = $oAGBWRB->cAGBContentText ?? '';
    $AGB->cContentHtml     = $oAGBWRB->cAGBContentHtml ?? '';
    $WRB->cContentText     = $oAGBWRB->cWRBContentText ?? '';
    $WRB->cContentHtml     = $oAGBWRB->cWRBContentHtml ?? '';
    $DSE->cContentText     = $oAGBWRB->cDSEContentText ?? '';
    $DSE->cContentHtml     = $oAGBWRB->cDSEContentHtml ?? '';
    $WRBForm->cContentHtml = $oAGBWRB->cWRBFormContentHtml ?? '';
    $WRBForm->cContentText = $oAGBWRB->cWRBFormContentText ?? '';

    $mailSmarty->assign('AGB', $AGB)
               ->assign('WRB', $WRB)
               ->assign('DSE', $DSE)
               ->assign('WRBForm', $WRBForm)
               ->assign('IP', StringHandler::htmlentities(StringHandler::filterXSS(Request::getRealIP())));

    $Object = lokalisiereInhalt($Object);
    // ModulId von einer Plugin Emailvorlage vorhanden?
    $cTable        = 'temailvorlage';
    $cTableSprache = 'temailvorlagesprache';
    $cTableSetting = 'temailvorlageeinstellungen';
    $cSQLWhere     = " cModulId = '" . $ModulId . "'";
    if (strpos($ModulId, 'kPlugin') !== false) {
        [$cPlugin, $kPlugin, $cModulId] = explode('_', $ModulId);
        $cTable                         = 'tpluginemailvorlage';
        $cTableSprache                  = 'tpluginemailvorlagesprache';
        $cTableSetting                  = 'tpluginemailvorlageeinstellungen';
        $cSQLWhere                      = ' kPlugin = ' . $kPlugin . " AND cModulId = '" . $cModulId . "'";
        $mailSmarty->assign('oPluginMail', $Object);
    }

    $mailTPL = $db->query(
        'SELECT *
            FROM ' . $cTable . '
            WHERE ' . $cSQLWhere,
        \DB\ReturnType::SINGLE_OBJECT
    );
    // Email aktiv?
    if (isset($mailTPL->cAktiv) && $mailTPL->cAktiv === 'N') {
        Shop::Container()->getLogService()->notice('Emailvorlage mit der ModulId ' . $ModulId . ' ist deaktiviert!');

        return false;
    }
    // Emailvorlageneinstellungen laden
    if (isset($mailTPL->kEmailvorlage) && $mailTPL->kEmailvorlage > 0) {
        $mailTPL->oEinstellung_arr = $db->selectAll(
            $cTableSetting,
            'kEmailvorlage',
            $mailTPL->kEmailvorlage
        );
        // Assoc bauen
        if (is_array($mailTPL->oEinstellung_arr) && count($mailTPL->oEinstellung_arr) > 0) {
            $mailTPL->oEinstellungAssoc_arr = [];
            foreach ($mailTPL->oEinstellung_arr as $oEinstellung) {
                $mailTPL->oEinstellungAssoc_arr[$oEinstellung->cKey] = $oEinstellung->cValue;
            }
        }
    }

    if (!isset($mailTPL->kEmailvorlage) || (int)$mailTPL->kEmailvorlage === 0) {
        Shop::Container()->getLogService()->error(
            'Keine Emailvorlage mit der ModulId ' . $ModulId .
            ' vorhanden oder diese Emailvorlage ist nicht aktiv!'
        );

        return false;
    }
    $mail->kEmailvorlage = $mailTPL->kEmailvorlage;

    $localization      = $db->select(
        $cTableSprache,
        ['kEmailvorlage', 'kSprache'],
        [(int)$mailTPL->kEmailvorlage, (int)$lang->kSprache]
    );
    $mailTPL->cBetreff = injectSubject($Object, $localization->cBetreff ?? null);
    if (isset($mailTPL->oEinstellungAssoc_arr['cEmailSenderName'])) {
        $absender_name = $mailTPL->oEinstellungAssoc_arr['cEmailSenderName'];
    }
    if (isset($mailTPL->oEinstellungAssoc_arr['cEmailOut'])) {
        $absender_mail = $mailTPL->oEinstellungAssoc_arr['cEmailOut'];
    }
    if (isset($mailTPL->oEinstellungAssoc_arr['cEmailCopyTo'])) {
        $kopie = $mailTPL->oEinstellungAssoc_arr['cEmailCopyTo'];
    }
    switch ($ModulId) {
        case MAILTEMPLATE_GUTSCHEIN:
            $mailSmarty->assign('Gutschein', $Object->tgutschein);
            break;

        case MAILTEMPLATE_BESTELLBESTAETIGUNG:
            $mailSmarty->assign('Bestellung', $Object->tbestellung)
                       ->assign('Verfuegbarkeit_arr', $Object->cVerfuegbarkeit_arr ?? null)
                       ->assign('oTrustedShopsBewertenButton', null);
            if (isset($Object->tbestellung->Zahlungsart->cModulId)
                && strlen($Object->tbestellung->Zahlungsart->cModulId) > 0
            ) {
                $cModulId         = $Object->tbestellung->Zahlungsart->cModulId;
                $oZahlungsartConf = $db->queryPrepared(
                    'SELECT tzahlungsartsprache.*
                        FROM tzahlungsartsprache
                        JOIN tzahlungsart
                            ON tzahlungsart.kZahlungsart = tzahlungsartsprache.kZahlungsart
                            AND tzahlungsart.cModulId = :module
                        WHERE tzahlungsartsprache.cISOSprache = :iso',
                    ['module' => $cModulId, 'iso' => $lang->cISO],
                    \DB\ReturnType::SINGLE_OBJECT
                );
                if (isset($oZahlungsartConf->kZahlungsart) && $oZahlungsartConf->kZahlungsart > 0) {
                    $mailSmarty->assign('Zahlungsart', $oZahlungsartConf);
                }
            }
            if ($config['trustedshops']['trustedshops_kundenbewertung_anzeigen'] === 'Y') {
                $langID = $_SESSION['cISOSprache'] ?? 'ger'; //workaround for testmails from backend

                $oTrustedShops                = new TrustedShops(-1, StringHandler::convertISO2ISO639($langID));
                $oTrustedShopsKundenbewertung = $oTrustedShops->holeKundenbewertungsstatus(
                    StringHandler::convertISO2ISO639($langID)
                );
                if ($oTrustedShopsKundenbewertung !== false
                    && strlen($oTrustedShopsKundenbewertung->cTSID) > 0
                    && $oTrustedShopsKundenbewertung->nStatus == 1
                ) {
                    $mailSmarty->assign('oTrustedShopsBewertenButton', TrustedShops::getRatingButton(
                        $Object->tbestellung->oRechnungsadresse->cMail,
                        $Object->tbestellung->cBestellNr
                    ));
                }
            }

            break;

        case MAILTEMPLATE_BESTELLUNG_AKTUALISIERT:
            $mailSmarty->assign('Bestellung', $Object->tbestellung);
            // Zahlungsart Einstellungen
            if (isset($Object->tbestellung->Zahlungsart->cModulId)
                && strlen($Object->tbestellung->Zahlungsart->cModulId) > 0
            ) {
                $cModulId         = $Object->tbestellung->Zahlungsart->cModulId;
                $oZahlungsartConf = $db->queryPrepared(
                    'SELECT tzahlungsartsprache.*
                        FROM tzahlungsartsprache
                        JOIN tzahlungsart
                            ON tzahlungsart.kZahlungsart = tzahlungsartsprache.kZahlungsart
                            AND tzahlungsart.cModulId = :module
                        WHERE tzahlungsartsprache.cISOSprache = :iso',
                    ['module' => $cModulId, 'iso' => $lang->cISO],
                    \DB\ReturnType::SINGLE_OBJECT
                );

                if (isset($oZahlungsartConf->kZahlungsart) && $oZahlungsartConf->kZahlungsart > 0) {
                    $mailSmarty->assign('Zahlungsart', $oZahlungsartConf);
                }
            }
            if ($config['trustedshops']['trustedshops_kundenbewertung_anzeigen'] === 'Y') {
                $oTrustedShops                = new TrustedShops(
                    -1,
                    StringHandler::convertISO2ISO639($_SESSION['cISOSprache'])
                );
                $oTrustedShopsKundenbewertung = $oTrustedShops->holeKundenbewertungsstatus(
                    StringHandler::convertISO2ISO639($_SESSION['cISOSprache'])
                );
                if (strlen($oTrustedShopsKundenbewertung->cTSID) > 0 && $oTrustedShopsKundenbewertung->nStatus == 1) {
                    $mailSmarty->assign('oTrustedShopsBewertenButton', TrustedShops::getRatingButton(
                        $Object->tbestellung->oRechnungsadresse->cMail,
                        $Object->tbestellung->cBestellNr
                    ));
                }
            }

            break;

        case MAILTEMPLATE_PASSWORT_VERGESSEN:
            $mailSmarty->assign('passwordResetLink', $Object->passwordResetLink)
                       ->assign('Neues_Passwort', $Object->neues_passwort);
            break;

        case MAILTEMPLATE_ADMINLOGIN_PASSWORT_VERGESSEN:
            $mailSmarty->assign('passwordResetLink', $Object->passwordResetLink);
            break;

        case MAILTEMPLATE_BESTELLUNG_BEZAHLT:
        case MAILTEMPLATE_BESTELLUNG_STORNO:
        case MAILTEMPLATE_BESTELLUNG_RESTORNO:
            $mailSmarty->assign('Bestellung', $Object->tbestellung);
            break;

        case MAILTEMPLATE_BESTELLUNG_TEILVERSANDT:
        case MAILTEMPLATE_BESTELLUNG_VERSANDT:
            $mailSmarty->assign('Bestellung', $Object->tbestellung);
            if ($config['trustedshops']['trustedshops_kundenbewertung_anzeigen'] === 'Y') {
                $oTrustedShops                = new TrustedShops(
                    -1,
                    StringHandler::convertISO2ISO639($_SESSION['cISOSprache'])
                );
                $oTrustedShopsKundenbewertung = $oTrustedShops->holeKundenbewertungsstatus(
                    StringHandler::convertISO2ISO639($_SESSION['cISOSprache'])
                );
                if (strlen($oTrustedShopsKundenbewertung->cTSID) > 0 && $oTrustedShopsKundenbewertung->nStatus == 1) {
                    $mailSmarty->assign('oTrustedShopsBewertenButton', TrustedShops::getRatingButton(
                        $Object->tbestellung->oRechnungsadresse->cMail,
                        $Object->tbestellung->cBestellNr
                    ));
                }
            }

            break;

        case MAILTEMPLATE_NEUKUNDENREGISTRIERUNG:
        case MAILTEMPLATE_ACCOUNTERSTELLUNG_DURCH_BETREIBER:
        case MAILTEMPLATE_KUNDENACCOUNT_GELOESCHT:
        case MAILTEMPLATE_KUNDENGRUPPE_ZUWEISEN:
            break;

        case MAILTEMPLATE_KUPON:
            $mailSmarty->assign('Kupon', $Object->tkupon)
                       ->assign('couponTypes', Kupon::getCouponTypes());
            break;

        case MAILTEMPLATE_KONTAKTFORMULAR:
            if (isset($config['kontakt']['kontakt_absender_name'])) {
                $absender_name = $config['kontakt']['kontakt_absender_name'];
            }
            if (isset($config['kontakt']['kontakt_absender_mail'])) {
                $absender_mail = $config['kontakt']['kontakt_absender_mail'];
            }
            $mailSmarty->assign('Nachricht', $Object->tnachricht);
            break;

        case MAILTEMPLATE_PRODUKTANFRAGE:
            if (isset($config['artikeldetails']['produktfrage_absender_name'])) {
                $absender_name = $config['artikeldetails']['produktfrage_absender_name'];
            }
            if (isset($config['artikeldetails']['produktfrage_absender_mail'])) {
                $absender_mail = $config['artikeldetails']['produktfrage_absender_mail'];
            }
            $mailSmarty->assign('Nachricht', $Object->tnachricht)
                       ->assign('Artikel', $Object->tartikel);
            break;

        case MAILTEMPLATE_PRODUKT_WIEDER_VERFUEGBAR:
            $mailSmarty->assign('Benachrichtigung', $Object->tverfuegbarkeitsbenachrichtigung)
                       ->assign('Artikel', $Object->tartikel);
            break;

        case MAILTEMPLATE_WUNSCHLISTE:
            $mailSmarty->assign('Wunschliste', $Object->twunschliste);
            break;

        case MAILTEMPLATE_BEWERTUNGERINNERUNG:
            $mailSmarty->assign('Bestellung', $Object->tbestellung);
            if ($config['trustedshops']['trustedshops_kundenbewertung_anzeigen'] === 'Y') {
                $oTrustedShops                = new TrustedShops(
                    -1,
                    StringHandler::convertISO2ISO639($_SESSION['cISOSprache'])
                );
                $oTrustedShopsKundenbewertung = $oTrustedShops->holeKundenbewertungsstatus(
                    StringHandler::convertISO2ISO639($_SESSION['cISOSprache'])
                );
                if (strlen($oTrustedShopsKundenbewertung->cTSID) > 0 && $oTrustedShopsKundenbewertung->nStatus == 1) {
                    $mailSmarty->assign('oTrustedShopsBewertenButton', TrustedShops::getRatingButton(
                        $Object->tbestellung->oRechnungsadresse->cMail,
                        $Object->tbestellung->cBestellNr
                    ));
                }
            }
            break;

        case MAILTEMPLATE_NEWSLETTERANMELDEN:
            $mailSmarty->assign('NewsletterEmpfaenger', $Object->NewsletterEmpfaenger);
            break;

        case MAILTEMPLATE_KUNDENWERBENKUNDEN:
            $mailSmarty->assign('Neukunde', $Object->oNeukunde)
                       ->assign('Bestandskunde', $Object->oBestandskunde);
            break;

        case MAILTEMPLATE_KUNDENWERBENKUNDENBONI:
            $mailSmarty->assign('BestandskundenBoni', $Object->BestandskundenBoni)
                       ->assign('Neukunde', $Object->oNeukunde)
                       ->assign('Bestandskunde', $Object->oBestandskunde);
            break;

        case MAILTEMPLATE_STATUSEMAIL:
            $Object->mail->toName   = $Object->tfirma->cName . ' ' . $Object->cIntervall;
            $mailTPL->cBetreff = $Object->tfirma->cName . ' ' . $Object->cIntervall;
            $mailSmarty->assign('oMailObjekt', $Object);
            break;

        case MAILTEMPLATE_CHECKBOX_SHOPBETREIBER:
            $mailSmarty->assign('oCheckBox', $Object->oCheckBox)
                       ->assign('oKunde', $Object->oKunde)
                       ->assign('cAnzeigeOrt', $Object->cAnzeigeOrt)
                       ->assign('oSprache', $lang);
            if (empty($Object->oKunde->cVorname) && empty($Object->oKunde->cNachname)) {
                $subjectLineCustomer = $Object->oKunde->cMail;
            } else {
                $subjectLineCustomer = $Object->oKunde->cVorname . ' ' . $Object->oKunde->cNachname;
            }
            $mailTPL->cBetreff = $Object->oCheckBox->cName .
                ' - ' . $subjectLineCustomer;
            break;
        case MAILTEMPLATE_BEWERTUNG_GUTHABEN:
            $waehrung = $db->select('twaehrung', 'cStandard', 'Y');

            $Object->oBewertungGuthabenBonus->fGuthabenBonusLocalized = Preise::getLocalizedPriceString(
                $Object->oBewertungGuthabenBonus->fGuthabenBonus,
                $waehrung,
                false
            );
            $mailSmarty->assign('oKunde', $Object->tkunde)
                       ->assign('oBewertungGuthabenBonus', $Object->oBewertungGuthabenBonus);
            break;
    }

    $mailSmarty->assign('Einstellungen', $config);

    $pluginBody = isset($mailTPL->kPlugin) && $mailTPL->kPlugin > 0 ? '_' . $mailTPL->kPlugin : '';

    executeHook(HOOK_MAILTOOLS_INC_SWITCH, [
        'mailsmarty'    => &$mailSmarty,
        'mail'          => &$mail,
        'kEmailvorlage' => $mailTPL->kEmailvorlage,
        'kSprache'      => $lang->kSprache,
        'cPluginBody'   => $pluginBody,
        'Emailvorlage'  => $mailTPL
    ]);
    if ($mailTPL->cMailTyp === 'text/html' || $mailTPL->cMailTyp === 'html') {
        $bodyHtml = $mailSmarty->fetch('db:html_' . $mailTPL->kEmailvorlage . '_' . $lang->kSprache . $pluginBody);
    }
    $bodyText = $mailSmarty->fetch('db:text_' . $mailTPL->kEmailvorlage . '_' . $lang->kSprache . $pluginBody);
    // AKZ, AGB und WRB anhängen falls eingestellt
    if ((int)$mailTPL->nAKZ === 1) {
        $akzHtml = $mailSmarty->fetch('db:html_core_jtl_anbieterkennzeichnung_' . $lang->kSprache . $pluginBody);
        $akzText = $mailSmarty->fetch('db:text_core_jtl_anbieterkennzeichnung_' . $lang->kSprache . $pluginBody);

        if (strlen($bodyHtml) > 0) {
            $bodyHtml .= '<br /><br />' . $akzHtml;
        }
        $bodyText .= "\n\n" . $akzText;
    }
    if ((int)$mailTPL->nWRB === 1) {
        $heading = Shop::Lang()->get('wrb');
        if (strlen($bodyHtml) > 0) {
            $bodyHtml .= '<br /><br /><h3>' . $heading . '</h3>' . $WRB->cContentHtml;
        }
        $bodyText .= "\n\n" . $heading . "\n\n" . $WRB->cContentText;
    }
    if ((int)$mailTPL->nWRBForm === 1) {
        $heading = Shop::Lang()->get('wrbform');
        if (strlen($bodyHtml) > 0) {
            $bodyHtml .= '<br /><br /><h3>' . $heading . '</h3>' . $WRBForm->cContentHtml;
        }
        $bodyText .= "\n\n" . $heading . "\n\n" . $WRBForm->cContentText;
    }
    if ((int)$mailTPL->nAGB === 1) {
        $heading = Shop::Lang()->get('agb');
        if (strlen($bodyHtml) > 0) {
            $bodyHtml .= '<br /><br /><h3>' . $heading . '</h3>' . $AGB->cContentHtml;
        }
        $bodyText .= "\n\n" . $heading . "\n\n" . $AGB->cContentText;
    }
    if ((int)$mailTPL->nDSE === 1) {
        $heading = 'Datenschutzerklärung';//Shop::Lang()->get('agb');
        if (strlen($bodyHtml) > 0) {
            $bodyHtml .= '<br /><br /><h3>' . $heading . '</h3>' . $DSE->cContentHtml;
        }
        $bodyText .= "\n\n" . $heading . "\n\n" . $DSE->cContentText;
    }
    if (isset($Object->tkunde->cMail)) {
        $mail->toEmail = $Object->tkunde->cMail;
        $mail->toName  = $Object->tkunde->cVorname . ' ' . $Object->tkunde->cNachname;
    } elseif (isset($Object->NewsletterEmpfaenger->cEmail) && strlen($Object->NewsletterEmpfaenger->cEmail) > 0) {
        $mail->toEmail = $Object->NewsletterEmpfaenger->cEmail;
    }
    //some mail servers seem to have problems with very long lines - wordwrap() if necessary
    $hasLongLines = false;
    foreach (preg_split('/((\r?\n)|(\r\n?))/', $bodyHtml) as $line) {
        if (strlen($line) > 987) {
            $hasLongLines = true;
            break;
        }
    }
    if ($hasLongLines) {
        $bodyHtml = wordwrap($bodyHtml, 900);
    }
    $hasLongLines = false;
    foreach (preg_split('/((\r?\n)|(\r\n?))/', $bodyText) as $line) {
        if (strlen($line) > 987) {
            $hasLongLines = true;
            break;
        }
    }
    if ($hasLongLines) {
        $bodyText = wordwrap($bodyText, 900);
    }

    $mail->fromEmail     = $absender_mail;
    $mail->fromName      = $absender_name;
    $mail->replyToEmail  = $absender_mail;
    $mail->replyToName   = $absender_name;
    $mail->subject       = StringHandler::htmlentitydecode($mailTPL->cBetreff);
    $mail->bodyText      = $bodyText;
    $mail->bodyHtml      = $bodyHtml;
    $mail->lang          = $lang->cISO;
    $mail->methode       = $config['emails']['email_methode'];
    $mail->sendmail_pfad = $config['emails']['email_sendmail_pfad'];
    $mail->smtp_hostname = $config['emails']['email_smtp_hostname'];
    $mail->smtp_port     = $config['emails']['email_smtp_port'];
    $mail->smtp_auth     = $config['emails']['email_smtp_auth'];
    $mail->smtp_user     = $config['emails']['email_smtp_user'];
    $mail->smtp_pass     = $config['emails']['email_smtp_pass'];
    $mail->SMTPSecure    = $config['emails']['email_smtp_verschluesselung'];
    $mail->SMTPAutoTLS   = !empty($mail->SMTPSecure);

    $mailSmarty->assign('absender_name', $absender_name)
               ->assign('absender_mail', $absender_mail);
    if (isset($Object->mail->fromEmail)) {
        $mail->fromEmail = $Object->mail->fromEmail;
    }
    if (isset($Object->mail->fromName)) {
        $mail->fromName = $Object->mail->fromName;
    }
    if (isset($Object->mail->toEmail)) {
        $mail->toEmail = $Object->mail->toEmail;
    }
    if (isset($Object->mail->toName)) {
        $mail->toName = $Object->mail->toName;
    }
    if (isset($Object->mail->replyToEmail)) {
        $mail->replyToEmail = $Object->mail->replyToEmail;
    }
    if (isset($Object->mail->replyToName)) {
        $mail->replyToName = $Object->mail->replyToName;
    }
    if (isset($localization->cPDFS) && strlen($localization->cPDFS) > 0) {
        $mail->cPDFS_arr = getPDFAttachments($localization->cPDFS, $localization->cDateiname);
    }
    executeHook(HOOK_MAILTOOLS_SENDEMAIL_ENDE, [
        'mailsmarty'    => &$mailSmarty,
        'mail'          => &$mail,
        'kEmailvorlage' => $mailTPL->kEmailvorlage,
        'kSprache'      => $lang->kSprache,
        'cPluginBody'   => $pluginBody,
        'Emailvorlage'  => $mailTPL
    ]);

    verschickeMail($mail);

    if ($kopie) {
        $copyAddresses = StringHandler::parseSSK($kopie);
        foreach ($copyAddresses as $copyAddress) {
            $mail->toEmail      = $copyAddress;
            $mail->toName       = $copyAddress;
            $mail->fromEmail    = $absender_mail;
            $mail->fromName     = $absender_name;
            $mail->replyToEmail = $Object->tkunde->cMail;
            $mail->replyToName  = $Object->tkunde->cVorname . ' ' . $Object->tkunde->cNachname;
            verschickeMail($mail);
        }
    }
    // Kopie Plugin
    if (isset($Object->oKopie, $Object->oKopie->cToMail) && strlen($Object->oKopie->cToMail) > 0) {
        $mail->toEmail      = $Object->oKopie->cToMail;
        $mail->toName       = $Object->oKopie->cToMail;
        $mail->fromEmail    = $absender_mail;
        $mail->fromName     = $absender_name;
        $mail->replyToEmail = $Object->tkunde->cMail;
        $mail->replyToName  = $Object->tkunde->cVorname . ' ' . $Object->tkunde->cNachname;
        verschickeMail($mail);
    }

    return $mail;
}

/**
 * @param string $cEmail
 * @return bool
 */
function pruefeGlobaleEmailBlacklist($cEmail)
{
    $oEmailBlacklist = Shop::Container()->getDB()->select('temailblacklist', 'cEmail', $cEmail);

    if (isset($oEmailBlacklist->cEmail) && strlen($oEmailBlacklist->cEmail) > 0) {
        $oEmailBlacklistBlock                = new stdClass();
        $oEmailBlacklistBlock->cEmail        = $oEmailBlacklist->cEmail;
        $oEmailBlacklistBlock->dLetzterBlock = 'NOW()';

        Shop::Container()->getDB()->insert('temailblacklistblock', $oEmailBlacklistBlock);

        return true;
    }

    return false;
}

/**
 * @param object $mail
 */
function verschickeMail($mail)
{
    $kEmailvorlage = null;
    if (isset($mail->kEmailvorlage)) {
        if ((int)$mail->kEmailvorlage > 0) {
            $kEmailvorlage = (int)$mail->kEmailvorlage;
        }
        unset($mail->kEmailvorlage);
    }

    // EmailBlacklist beachten
    $Emailconfig = Shop::getSettings([CONF_EMAILBLACKLIST]);
    if ($Emailconfig['emailblacklist']['blacklist_benutzen'] === 'Y' && pruefeGlobaleEmailBlacklist($mail->toEmail)) {
        return;
    }
    // BodyText encoden
    $mail->bodyText  = StringHandler::htmlentitydecode(str_replace('&euro;', 'EUR', $mail->bodyText), ENT_NOQUOTES);
    $mail->cFehler   = '';
    $GLOBALS['mail'] = $mail; // Plugin Work Around

    $bSent = false;
    if (!$mail->methode) {
        SendNiceMailReply(
            $mail->fromName,
            $mail->fromEmail,
            $mail->fromEmail,
            $mail->toEmail,
            $mail->subject,
            $mail->bodyText,
            $mail->bodyHtml
        );
    } else {
        //phpmailer
        $phpmailer = new \PHPMailer\PHPMailer\PHPMailer();
        $lang      = ($mail->lang === 'DE' || $mail->lang === 'ger') ? 'de' : 'end';
        $phpmailer->setLanguage($lang, PFAD_ROOT . PFAD_PHPMAILER . 'language/');
        $phpmailer->CharSet = JTL_CHARSET;
        $phpmailer->Timeout = SOCKET_TIMEOUT;
        $phpmailer->Sender  = $mail->fromEmail;
        $phpmailer->setFrom($mail->fromEmail, $mail->fromName);
        $phpmailer->addAddress($mail->toEmail, (!empty($mail->toName) ? $mail->toName : ''));
        $phpmailer->addReplyTo($mail->replyToEmail, $mail->replyToName);
        $phpmailer->Subject = $mail->subject;

        switch ($mail->methode) {
            case 'mail':
                $phpmailer->isMail();
                break;
            case 'sendmail':
                $phpmailer->isSendmail();
                $phpmailer->Sendmail = $mail->sendmail_pfad;
                break;
            case 'qmail':
                $phpmailer->isQmail();
                break;
            case 'smtp':
                $phpmailer->isSMTP();
                $phpmailer->Host          = $mail->smtp_hostname;
                $phpmailer->Port          = $mail->smtp_port;
                $phpmailer->SMTPKeepAlive = true;
                $phpmailer->SMTPAuth      = $mail->smtp_auth;
                $phpmailer->Username      = $mail->smtp_user;
                $phpmailer->Password      = $mail->smtp_pass;
                $phpmailer->SMTPSecure    = $mail->SMTPSecure;
                $phpmailer->SMTPAutoTLS   = $mail->SMTPAutoTLS
                    ?? (empty($mail->SMTPSecure)
                        ? false
                        : true);
                break;
        }
        if ($mail->bodyHtml) {
            $phpmailer->isHTML(true);
            $phpmailer->Body    = $mail->bodyHtml;
            $phpmailer->AltBody = $mail->bodyText;
        } else {
            $phpmailer->isHTML(false);
            $phpmailer->Body = $mail->bodyText;
        }

        if (isset($mail->cPDFS_arr) && count($mail->cPDFS_arr) > 0) {
            $cUploadVerzeichnis = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_EMAILPDFS;

            foreach ($mail->cPDFS_arr as $i => $cPDFS) {
                $phpmailer->addAttachment(
                    $cUploadVerzeichnis . $cPDFS->fileName,
                    $cPDFS->publicName . '.pdf',
                    'base64',
                    'application/pdf'
                );
            }
        }
        if (isset($mail->oAttachment_arr) && count($mail->oAttachment_arr) > 0) {
            foreach ($mail->oAttachment_arr as $oAttachment) {
                if (empty($oAttachment->cEncoding)) {
                    $oAttachment->cEncoding = 'base64';
                }
                if (empty($oAttachment->cType)) {
                    $oAttachment->cType = 'application/octet-stream';
                }
                $phpmailer->addAttachment(
                    $oAttachment->cFilePath,
                    $oAttachment->cName,
                    $oAttachment->cEncoding,
                    $oAttachment->cType
                );
            }
        }

        $bSent         = $phpmailer->send();
        $mail->cFehler = $phpmailer->ErrorInfo;
    }
    if ($bSent) {
        $oEmailhistory = new Emailhistory();
        if ($kEmailvorlage !== null) {
            $oEmailhistory->setEmailvorlage($kEmailvorlage);
        }
        $oEmailhistory->setSubject($mail->subject)
                      ->setFromName($mail->fromName)
                      ->setFromEmail($mail->fromEmail)
                      ->setToName($mail->toName ?? '')
                      ->setToEmail($mail->toEmail)
                      ->setSent('NOW()')
                      ->save();
    } else {
        Shop::Container()->getLogService()->error('Email konnte nicht versendet werden! Fehler: ' . $mail->cFehler);
    }

    executeHook(HOOK_MAILTOOLS_VERSCHICKEMAIL_GESENDET);
}

/**
 * @param object $Object
 * @param string        $subject
 * @return mixed
 */
function injectSubject($Object, $subject)
{
    $a     = [];
    $b     = [];
    $keys1 = array_keys(get_object_vars($Object));
    if (!is_array($keys1)) {
        return $subject;
    }
    foreach ($keys1 as $obj) {
        if (is_object($Object->$obj) && is_array(get_object_vars($Object->$obj))) {
            $keys2 = array_keys(get_object_vars($Object->$obj));
            if (is_array($keys2)) {
                foreach ($keys2 as $member) {
                    if ($member{0} !== 'k'
                        && !is_array($Object->$obj->$member)
                        && !is_object($Object->$obj->$member)
                    ) {
                        $a[] = '#' . strtolower(substr($obj, 1)) . '.' . strtolower(substr($member, 1)) . '#';
                        $b[] = $Object->$obj->$member;
                    }
                }
            }
        }
    }

    return str_replace($a, $b, $subject);
}

/**
 * @param object $Object
 * @return mixed
 */
function lokalisiereInhalt($Object)
{
    if (isset($Object->tgutschein->fWert) && $Object->tgutschein->fWert != 0) {
        $Object->tgutschein->cLocalizedWert = Preise::getLocalizedPriceString($Object->tgutschein->fWert, null, false);
    }

    return $Object;
}

/**
 * @param object $sprache
 * @param Kunde  $kunde
 * @return mixed
 */
function lokalisiereKunde($sprache, $kunde)
{
    if (Shop::Lang()->gibISO() !== $sprache->cISO) {
        Shop::Lang()->setzeSprache($sprache->cISO);
    }
    // Anrede mappen
    if (isset($kunde->cAnrede)) {
        if ($kunde->cAnrede === 'w') {
            $kunde->cAnredeLocalized = Shop::Lang()->get('salutationW');
        } elseif ($kunde->cAnrede === 'm') {
            $kunde->cAnredeLocalized = Shop::Lang()->get('salutationM');
        } else {
            $kunde->cAnredeLocalized = Shop::Lang()->get('salutationGeneral');
        }
    }
    $kunde = GeneralObject::deepCopy($kunde);
    if (isset($kunde->cLand)) {
        $cISOLand = $kunde->cLand;
        $sel_var  = 'cDeutsch';
        if (strtolower($sprache->cISO) !== 'ger') {
            $sel_var = 'cEnglisch';
        }
        $land = Shop::Container()->getDB()->select(
            'tland',
            'cISO',
            $kunde->cLand,
            null,
            null,
            null,
            null,
            false,
            $sel_var . ' AS cName, cISO'
        );
        if (isset($land->cName)) {
            $kunde->cLand = $land->cName;
        }
    }
    if (isset($_SESSION['Kunde'], $cISOLand)) {
        $_SESSION['Kunde']->cLand = $cISOLand;
    }

    return $kunde;
}

/**
 * @param object        $oSprache
 * @param Lieferadresse $oLieferadresse
 * @return object
 */
function lokalisiereLieferadresse($oSprache, $oLieferadresse)
{
    $langRow = (strtolower($oSprache->cISO) === 'ger') ? 'cDeutsch' : 'cEnglisch';
    $land    = Shop::Container()->getDB()->select(
        'tland',
        'cISO',
        $oLieferadresse->cLand,
        null,
        null,
        null,
        null,
        false,
        $langRow . ' AS cName, cISO'
    );
    if (!empty($land->cName)) {
        $oLieferadresse->cLand = $land->cName;
    }

    return $oLieferadresse;
}

/**
 * @deprecated since 4.05.2 - use getPDFAttachments instead
 * This function produces inconsistency between attachment and name if one or more attachment doesnt exist!
 * @param string $cPDF
 * @return array
 */
function bauePDFArrayZumVeschicken($cPDF)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $cPDFTMP_arr        = explode(';', $cPDF);
    $cPDF_arr           = [];
    $cUploadVerzeichnis = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_EMAILPDFS;
    foreach ($cPDFTMP_arr as $cPDFTMP) {
        if (strlen($cPDFTMP) > 0 && file_exists($cUploadVerzeichnis . $cPDFTMP)) {
            $cPDF_arr[] = $cPDFTMP;
        }
    }

    return $cPDF_arr;
}

/**
 * @param string $cPDFs
 * @param string $cNames
 *
 * @return stdClass[]
 */
function getPDFAttachments($cPDFs, $cNames)
{
    $result      = [];
    $cPDFs_arr   = StringHandler::parseSSK(trim($cPDFs, ";\t\n\r\0"));
    $cNames_arr  = StringHandler::parseSSK(trim($cNames, ";\t\n\r\0"));
    $cUploadPath = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_EMAILPDFS;

    if (is_array($cPDFs_arr)) {
        foreach ($cPDFs_arr as $key => $pdfFile) {
            if (!empty($pdfFile) && file_exists($cUploadPath . $pdfFile)) {
                $result[] = (object)[
                    'fileName'   => $pdfFile,
                    'publicName' => $cNames_arr[$key] ?? $pdfFile,
                ];
            }
        }
    }

    return $result;
}

/**
 * @param string $cDateiname
 * @return array
 * @deprecated since 4.05 - use getPDFAttachments instead
 */
function baueDateinameArrayZumVeschicken($cDateiname)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $cDateinameTMP_arr = explode(';', $cDateiname);
    $cDateiname_arr    = [];
    if (count($cDateinameTMP_arr) > 0) {
        foreach ($cDateinameTMP_arr as $cDateinameTMP) {
            if (strlen($cDateinameTMP) > 0) {
                $cDateiname_arr[] = $cDateinameTMP;
            }
        }
    }

    return $cDateiname_arr;
}

/**
 * mail functions
 *
 * @param string $FromName
 * @param string $FromMail
 * @param string $ReplyAdresse
 * @param string $To
 * @param string $Subject
 * @param string $Text
 * @param string $Html
 * @return bool
 */
function SendNiceMailReply($FromName, $FromMail, $ReplyAdresse, $To, $Subject, $Text, $Html = '')
{
    //endl definieren
    $eol = "\n";
    if (stripos(PHP_OS, 'WIN') === 0) {
        $eol = "\r\n";
    } elseif (stripos(PHP_OS, 'MAC') === 0) {
        $eol = "\r";
    }

    $FromName = StringHandler::unhtmlentities($FromName);
    $FromMail = StringHandler::unhtmlentities($FromMail);
    $Subject  = StringHandler::unhtmlentities($Subject);
    $Text     = StringHandler::unhtmlentities($Text);

    $Text = $Text ?: 'Sorry, but you need an html mailer to read this mail.';

    if (empty($To)) {
        return false;
    }

    $mime_boundary = md5(time()) . '_jtlshop2';
    $headers       = '';

    if (strpos($To, 'freenet')) {
        $headers .= 'From: ' . strtolower($FromMail) . $eol;
    } else {
        $headers .= 'From: ' . $FromName . ' <' . strtolower($FromMail) . '>' . $eol;
    }

    $headers .= 'Reply-To: ' . strtolower($ReplyAdresse) . $eol;
    $headers .= 'MIME-Version: 1.0' . $eol;
    if (!$Html) {
        $headers .= 'Content-Type: text/plain; charset=' . JTL_CHARSET . $eol;
        $headers .= 'Content-Transfer-Encoding: 8bit' . $eol . $eol;
    }

    $Msg = $Text;
    if ($Html) {
        $Msg      = '';
        $headers .= 'Content-Type: multipart/alternative; boundary=' . $mime_boundary . $eol;

        # Text Version
        $Msg .= '--' . $mime_boundary . $eol;
        $Msg .= 'Content-Type: text/plain; charset=' . JTL_CHARSET . $eol;
        $Msg .= 'Content-Transfer-Encoding: 8bit' . $eol . $eol;
        $Msg .= $Text . $eol;

        # HTML Version
        $Msg .= '--' . $mime_boundary . $eol;
        $Msg .= 'Content-Type: text/html; charset=' . JTL_CHARSET . $eol;
        $Msg .= 'Content-Transfer-Encoding: 8bit' . $eol . $eol;
        $Msg .= $Html . $eol . $eol;

        # Finished
        $Msg .= '--' . $mime_boundary . '--' . $eol . $eol;
    }
    mail($To, encode_iso88591($Subject), $Msg, $headers);

    return true;
}

/**
 * @param string $string
 * @return string
 */
function encode_iso88591($string)
{
    $text = '=?' . JTL_CHARSET . '?Q?';
    $max  = strlen($string);
    for ($i = 0; $i < $max; $i++) {
        $val = ord($string[$i]);
        if ($val > 127 || $val === 63) {
            $val   = dechex($val);
            $text .= '=' . $val;
        } else {
            $text .= $string[$i];
        }
    }
    $text .= '?=';

    return $text;
}
