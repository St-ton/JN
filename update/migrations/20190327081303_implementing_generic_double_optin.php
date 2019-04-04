<?php
/**
 * implementing generic double optin
 *
 * @author Clemens Rudolph
 * @created Wed, 27 Mar 2019 08:13:03 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Migration
 *
 * Available methods:
 * execute            - returns affected rows
 * fetchOne           - single fetched object
 * fetchAll           - array of fetched objects
 * fetchArray         - array of fetched assoc arrays
 * dropColumn         - drops a column if exists
 * setLocalization    - add localization
 * removeLocalization - remove localization
 * setConfig          - add / update config property
 * removeConfig       - remove config property
 */
class Migration_20190327081303 extends Migration implements IMigration
{
    protected $author      = 'Clemens Rudolph';
    protected $description = 'implementing generic double optin';
    protected $kEmailvorlage;

    public function up()
    {
        // create the optin tables (incl history)
        $this->execute("CREATE TABLE IF NOT EXISTS toptin(
            kOptin int(10) NOT NULL AUTO_INCREMENT COMMENT 'internal table key',
            kOptinCode varchar(256) NOT NULL DEFAULT '' COMMENT 'main opt-in code',
            kOptinType int(10) NOT NULL DEFAULT 0 COMMENT 'the constant for that optin, from defines_inc.php',
            cMail varchar(256) NOT NULL DEFAULT '' COMMENT 'customer mail address',
            cRefData varchar(1024) DEFAULT NULL COMMENT 'additional reference data (e.g. text output in emails)',
            dCreated datetime DEFAULT NULL COMMENT 'opt-in created',
            dActivated datetime DEFAULT NULL COMMENT 'time of activation',
            PRIMARY KEY (kOptin),
            UNIQUE KEY (kOptinCode)
        )
        ENGINE = innodb");
        $this->execute("CREATE TABLE IF NOT EXISTS toptinhistory(
            kOptinHistory int(10) NOT NULL auto_increment COMMENT 'internal table key',
            kOptin varchar(256) DEFAULT NULL COMMENT 'main OptInCode, derived from table toptin',
            kOptinType int(10) NOT NULL DEFAULT 0 COMMENT 'the constant for that optin, from defines_inc.php',
            cMail varchar(256) NOT NULL DEFAULT '' COMMENT 'customer mail address',
            cRefData varchar(1024) DEFAULT NULL COMMENT 'additional reference data (e.g. text output in emails)',
            dCreated datetime DEFAULT NULL COMMENT 'time of opt-in creation',
            dActivated datetime DEFAULT NULL COMMENT 'time of opt-in activation',
            dDeActivated datetime DEFAULT NULL COMMENT 'time of de-activation (moved to this place)',
            PRIMARY KEY (kOptinHistory),
            INDEX (kOptin)
        )
        ENGINE = innodb");

        // create messages
        $this->setLocalization('ger', 'errorMessages', 'optinCodeUnknown', 'Der übergebene Bestätigungscode ist nicht bekannt.');
        $this->setLocalization('eng', 'errorMessages', 'optinCodeUnknown', 'The given confirmation code is unknown.');
        $this->setLocalization('ger', 'errorMessages', 'optinActionUnknown', 'Unbekannte Aktion angefordert.');
        $this->setLocalization('eng', 'errorMessages', 'optinActionUnknown', 'Unknown action requested.');
        //
        $this->setLocalization('ger', 'messages', 'availAgainOptinCreated',
          'Vielen Dank, Ihre Daten haben wir erhalten. Wir haben Ihnen eine E-Mail
           mit einem Freischaltcode zugeschickt.
           Bitte klicken Sie auf diesen Link in der E-Mail,
           um informiert zu werden, sobald der Artikel wieder verfügbar ist.');
        //
        $this->setLocalization('ger', 'messages', 'optinSucceded', 'Ihre Freischaltung ist erfolgt.');
        $this->setLocalization('eng', 'messages', 'optinSucceded', 'Your confirmation was successfull.');
        $this->setLocalization('ger', 'messages', 'optinSuccededAgain', 'Ihre Freischaltung ist bereits erfolgt.');
        $this->setLocalization('eng', 'messages', 'optinSuccededAgain', 'Your confirmation is already active.');
        $this->setLocalization('ger', 'messages', 'optinCanceled', 'Freischaltung wurde aufgehoben.');
        $this->setLocalization('eng', 'messages', 'optinCanceled', 'Your confirmation was canceled.');
        $this->setLocalization('ger', 'messages', 'optinRemoved', 'Ihr Freischaltantrag wurde entfernt.');
        $this->setLocalization('eng', 'messages', 'optinRemoved', 'Your activation request has been removed.');

        // create new optin email templates (`temailvorlage`, `temailvorlageoriginal`)
        $this->execute("INSERT INTO temailvorlageoriginal(
                cName,
                cBeschreibung,
                cMailTyp,
                cModulId,
                cDateiname,
                cAktiv,
                nAKZ,
                nAGB,
                nWRB
            ) VALUE (
                'Benachrichtigung, wenn Produkt wieder verf&uuml;gbar (Double Opt-in Anfrage)',
                '',
                'text/html',
                'core_jtl_verfuegbarkeitsbenachrichtigung_optin',
                'produkt_wieder_verfuegbar_optin',
                'Y',
                0,
                0,
                0
            )"
        );
        $this->execute("INSERT INTO temailvorlage(
                cName,
                cBeschreibung,
                cMailTyp,
                cModulId,
                cDateiname,
                cAktiv,
                nAKZ,
                nAGB,
                nWRB
            ) VALUE (
                'Benachrichtigung, wenn Produkt wieder verf&uuml;gbar (Double Opt-in Anfrage)',
                '',
                'text/html',
                'core_jtl_verfuegbarkeitsbenachrichtigung_optin',
                'produkt_wieder_verfuegbar_optin',
                'Y',
                0,
                0,
                0
            )"
        );
        $this->kEmailvorlage = $this->fetchOne('SELECT last_insert_id() AS last_insert_id')->last_insert_id;

        // define text and insert them as new email templates
        $optin_cContentHtml_de = <<<'DEHTML'
{includeMailTemplate template=header type=html}

{if isset($Kunde->kKunde) && $Kunde->kKunde > 0}
    Sehr {if $Kunde->cAnrede=="w"}geehrte Frau{else}geehrter Herr{/if} {$Kunde->cNachname},<br>
    <br>
{elseif isset($NewsletterEmpfaenger->cNachname)}
    Sehr {if $NewsletterEmpfaenger->cAnrede=="w"}geehrte Frau{else}geehrter Herr{/if} {$NewsletterEmpfaenger->cNachname},<br>
    <br>
{else}
	Sehr geeherte Kundin, sehr geehrter Kunde,<br>
{/if}
<br>
Bitte klicken Sie den folgenden Freischalt-Link<br>
<a href="{$Optin->activationURL}">{$Optin->activationURL}</a>,<br>
<br>
um von uns informiert zu werden, sobald der Artikel<br>
<b>{$Artikel->cName}</b><br>
wieder verfügbar ist.<br>
<br>
Wenn Sie sich von dieser Benachrichtigungsfunktion abmelden möchten,<br>
klicken Sie bitte den folgenden Link an:<br>
<a href="{$Optin->deactivationURL}">{$Optin->deactivationURL}</a>,<br>
<br>
<br>
Mit freundlichem Gruß,<br>
Ihr Team von {$Firma->cName}

{includeMailTemplate template=footer type=html}
DEHTML;

        $optin_cContentText_de = <<<'DEPLAIN'
{includeMailTemplate template=header type=plain}

{if isset($Kunde->kKunde) && $Kunde->kKunde > 0}
    Sehr {if $Kunde->cAnrede=="w"}geehrte Frau{else}geehrter Herr{/if} {$Kunde->cNachname},
{elseif isset($NewsletterEmpfaenger->cNachname)}
    Sehr {if $NewsletterEmpfaenger->cAnrede=="w"}geehrte Frau{else}geehrter Herr{/if} {$NewsletterEmpfaenger->cNachname},
{else}
	Sehr geeherte Kundin, sehr geehrter Kunde,
{/if}
Bitte nutzen Sie den folgenden Freischalt-Link
{$Optin->activationURL}
den Sie in Ihren Browser einfügen können, um von uns informiert zu werden, sobald
"{$Artikel->cName}" wieder verfügbar ist.

Wenn Sie sich von dieser Benachrichtigungsfunktion abmelden möchten,
folgen Sie bitte dem folgenden Link mit Ihrem Browser an:
{$Optin->deactivationURL}


Mit freundlichem Gruß,
Ihr Team von {$Firma->cName}

{includeMailTemplate template=footer type=plain}
DEPLAIN;

        $optin_cContentHtml_en = <<<'ENHTML'
{includeMailTemplate template=header type=html}

{if empty($Benachrichtigung->cVorname) && empty($Benachrichtigung->cNachname)}
Dear Customer,<br>
{else}
Dear{if !empty($Benachrichtigung->cVorname)} {$Benachrichtigung->cVorname}{/if}
{if !empty($Benachrichtigung->cNachname)} {$Benachrichtigung->cNachname}{/if},<br>
{/if}
<br>

Please use the following confirmation-Link<br>
<a href="{$Optin->activationURL}">{$Optin->activationURL}</a>,<br>
to get the information, if the article
<b>{$Artikel->cName}</b><br>
is available again.<br>
<br>
If you want to unsubscribe from this notification feature,<br>
please click the following link:<br>
<a href="{$Optin->deactivationURL}">{$Optin->deactivationURL}<a><br>
<br>
<br>
Yours sincerely,<br>
{$Einstellungen.global.global_shopname}

{includeMailTemplate template=footer type=html}
ENHTML;

        $optin_cContentText_en = <<<'ENPLAIN'
{includeMailTemplate template=header type=plain}

{if empty($Benachrichtigung->cVorname) && empty($Benachrichtigung->cNachname)}
Dear Customer,
{else}
Dear{if !empty($Benachrichtigung->cVorname)} {$Benachrichtigung->cVorname}{/if}
{if !empty($Benachrichtigung->cNachname)} {$Benachrichtigung->cNachname}{/if},
{/if}

Please use the following confirmation-Link, which you can insert into your browser,
to get the information, if the article
"{$Artikel->cName}"
is available again: {$Optin->activationURL}

If you want to unsubscribe from this notification feature,
please follow the following link with your browser:
{$Optin->deactivationURL}


Yours sincerely,
{$Einstellungen.global.global_shopname}

{includeMailTemplate template=footer type=plain}
ENPLAIN;

        $this->execute('INSERT INTO temailvorlagespracheoriginal(
                kEmailvorlage,
                kSprache,
                cBetreff,
                cContentHtml,
                cContentText,
                cDateiname
            ) VALUES (
                ' . $this->kEmailvorlage . ",
                1,
                'Bestätigung für Produktinformation: #artikel.name#',
                '" . $optin_cContentHtml_de . "',
                '" . $optin_cContentText_de . "',
                'produkt_wieder_verfuegbar_optin'
            ), (
                " . $this->kEmailvorlage . ",
                2,
                'Confirmation for product information: #artikel.name#',
                '" . $optin_cContentHtml_en . "',
                '" . $optin_cContentText_en . "',
                'produkt_wieder_verfuegbar_optin'
            )
        ");

        $this->execute('INSERT INTO temailvorlagesprache(
                kEmailvorlage,
                kSprache,
                cBetreff,
                cContentHtml,
                cContentText,
                cDateiname
            ) VALUES (
                ' . $this->kEmailvorlage . ",
                1,
                'Bestätigung für Produktinformation: #artikel.name#',
                '" . $optin_cContentHtml_de . "',
                '" . $optin_cContentText_de . "',
                'produkt_wieder_verfuegbar_optin'
            ), (
                " . $this->kEmailvorlage . ",
                2,
                'Confirmation for product information: #artikel.name#',
                '" . $optin_cContentHtml_en . "',
                '" . $optin_cContentText_en . "',
                'produkt_wieder_verfuegbar_optin'
            )
        ");

        // update current availablity mail templates
        $cContentHtml_de = <<<'DEHTML'
{includeMailTemplate template=header type=html}

{if !empty($Benachrichtigung->cNachname) || !empty($Benachrichtigung->cVorname)}
Hallo{if !empty($Benachrichtigung->cVorname)} {$Benachrichtigung->cVorname}{/if}
{if !empty($Benachrichtigung->cNachname)} {$Benachrichtigung->cNachname}{/if},<br>
{else}
Sehr geehrte Kundin, sehr geehrter Kunde,<br>
{/if}
<br>
wir freuen uns, Ihnen mitteilen zu dürfen, dass das Produkt {$Artikel->cName} ab sofort wieder bei uns erhältlich ist.<br>
<br>
Über diesen Link kommen Sie direkt zum Produkt in unserem Onlineshop: <a href="{$ShopURL}/{$Artikel->cURL}">{$Artikel->cName}</a><br>
<br>
Mit freundlichem Gruß,<br>
Ihr Team von {$Firma->cName}

{includeMailTemplate template=footer type=html}
DEHTML;

        $cContentText_de = <<<'DEPLAIN'
{includeMailTemplate template=header type=plain}

{if !empty($Benachrichtigung->cNachname) || !empty($Benachrichtigung->cVorname)}
Hallo{if !empty($Benachrichtigung->cVorname)} {$Benachrichtigung->cVorname}{/if}
{if !empty($Benachrichtigung->cNachname)} {$Benachrichtigung->cNachname}{/if},
{else}
Sehr geehrte Kundin, sehr geehrter Kunde,
{/if}

wir freuen uns, Ihnen mitteilen zu dürfen, dass das Produkt {$Artikel->cName} ab sofort wieder bei uns erhältlich ist.

Über diesen Link kommen Sie direkt zum Produkt in unserem Onlineshop: {$ShopURL}/{$Artikel->cURL}.

Mit freundlichem Gruß,
Ihr Team von {$Firma->cName}

{includeMailTemplate template=footer type=plain}
DEPLAIN;

        $cContentHtml_en = <<<'ENHTML'
{includeMailTemplate template=header type=html}

{if !empty($Benachrichtigung->cNachname) || !empty($Benachrichtigung->cVorname)}
Dear{if !empty($Benachrichtigung->cVorname)} {$Benachrichtigung->cVorname}{/if}
{if !empty($Benachrichtigung->cNachname)} {$Benachrichtigung->cNachname}{/if},<br>
{else}
Dear customer,<br>
{/if}
<br>
We\'re happy to inform you that our product {$Artikel->cName} is once again available in our online shop.<br>
<br>
Link to product: <a href="{$ShopURL}/{$Artikel->cURL}">{$ShopURL}/{$Artikel->cURL}</a><br>
<br>
Yours sincerely,<br>
{$Einstellungen.global.global_shopname}

{includeMailTemplate template=footer type=html}
ENHTML;

        $cContentText_en = <<<'ENPLAIN'
{includeMailTemplate template=header type=plain}

{if !empty($Benachrichtigung->cNachname) || !empty($Benachrichtigung->cVorname)}
Dear{if !empty($Benachrichtigung->cVorname)} {$Benachrichtigung->cVorname}{/if}
{if !empty($Benachrichtigung->cNachname)} {$Benachrichtigung->cNachname}{/if},
{else}
Dear customer,
{/if}

We\'re happy to inform you that our product {$Artikel->cName} is once again available in our online shop.

Link to product: {$ShopURL}/{$Artikel->cURL}

Yours sincerely,
{$Einstellungen.global.global_shopname}

{includeMailTemplate template=footer type=plain}
ENPLAIN;

        $this->execute("UPDATE temailvorlagespracheoriginal
            SET
                cContentHtml = '" . $cContentHtml_de . "',
                cContentText = '" . $cContentText_de . "' " .
            'WHERE
                kEmailvorlage = ' . $this->kEmailvorlage . ' ' .
                'AND kSprache = 1');


        $this->execute("UPDATE temailvorlagesprache
            SET
                cContentHtml = '" . $cContentHtml_en . "',
                cContentText = '" . $cContentText_en . "' " .
            'WHERE
                kEmailvorlage = ' . $this->kEmailvorlage . ' ' .
                'AND kSprache = 2');


        // add table comments
        $this->execute("ALTER TABLE temailvorlage MODIFY cDateiname
            varchar(255) DEFAULT '' NOT NULL COMMENT 'base file name in admin/mailtemplates/[ger|eng]/'");
        $this->execute("ALTER TABLE temailvorlage MODIFY cModulId
            varchar(255) COMMENT 'constant in includes/defines_inc.php'");
        $this->execute("ALTER TABLE temailvorlage MODIFY cBeschreibung
            text COMMENT 'for internal use'");
        $this->execute("ALTER TABLE temailvorlage MODIFY cName
            varchar(255) COMMENT 'is displayed in the backend'");
        $this->execute("ALTER TABLE temailvorlage MODIFY nDSE
            tinyint(3) NOT NULL DEFAULT 0 COMMENT 'append privatcy statement'");
    }

    public function down()
    {
        // remove the optin email templates
        $this->execute("DELETE FROM temailvorlageoriginal WHERE cModulId = 'core_jtl_verfuegbarkeitsbenachrichtigung_optin'");
        $this->execute("DELETE FROM temailvorlage WHERE cModulId = 'core_jtl_verfuegbarkeitsbenachrichtigung_optin'");
        $this->execute("DELETE FROM temailvorlagespracheoriginal WHERE cDateiname = 'produkt_wieder_verfuegbar_optin'");
        $this->execute("DELETE FROM temailvorlagesprache WHERE cDateiname = 'produkt_wieder_verfuegbar_optin'");

        $this->removeLocalization('optinRemoved');
        $this->removeLocalization('optinCanceled');
        $this->removeLocalization('optinSuccededAgain');
        $this->removeLocalization('optinSucceded');
        //
        $this->removeLocalization('availAgainOptinCreated');
        $this->removeLocalization('optinActionUnknown');
        $this->removeLocalization('optinCodeUnknown');

        // remove the optin tables
        $this->execute('DROP TABLE toptin');
        $this->execute('DROP TABLE toptinhistory');
    }
}
