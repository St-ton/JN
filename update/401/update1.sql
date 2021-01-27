UPDATE `teinstellungenconf` SET `cBeschreibung`='Welche Darstellung soll in der Artikel&uuml;bersicht standardm&auml;&szlig;ig angezeigt werden? Achtung: Evo unterst&uuml;tzt nur Liste und Galerie.' WHERE `kEinstellungenConf`='1326';
UPDATE `teinstellungenconf` SET `cBeschreibung`='Dieser Hinweis wird Besuchern angezeigt, wenn der Shop im Wartungsmodus ist. Achtung: Im Evo-Template steuern Sie diesen Text &uuml;ber die Sprachvariable maintenanceModeActive.' WHERE `kEinstellungenConf`='175';
UPDATE `teinstellungen` SET `cWert`='40' WHERE `kEinstellungenSektion`=9 AND `cName`='bilder_artikel_mini_breite' AND (`cWert` = 0 OR `cWert` IS NULL);
UPDATE `teinstellungen` SET `cWert`='40' WHERE `kEinstellungenSektion`=9 AND `cName`='bilder_artikel_mini_hoehe' AND  (`cWert` = 0 OR `cWert` IS NULL);
UPDATE `teinstellungenconf` SET `cBeschreibung`='Cachet ganze Seiten. Achtung: Nur aktivieren, wenn Sie wissen, was Sie tun!' WHERE `kEinstellungenConf`='1562';
UPDATE `teinstellungenconf` SET `cBeschreibung`='Zeigt via HTTP-Header JTL-Cached an, ob Seiten im Cache sind oder nicht.' WHERE `kEinstellungenConf`='1563';
ALTER TABLE `tbestellung` ADD  `cPUIZahlungsdaten` TEXT NULL DEFAULT NULL;
UPDATE tzahlungsart SET cName = 'SOFORT �berweisung' WHERE cName = 'sofort�berweisung.de';
UPDATE tzahlungsart SET cAnbieter = 'SOFORT �berweisung' WHERE cAnbieter = 'sofort�berweisung.de';
UPDATE tzahlungsartsprache SET cName = 'SOFORT �berweisung' WHERE cName = 'sofort�berweisung.de';
UPDATE tzahlungsartsprache SET cName = 'SOFORT Banking' WHERE cName = 'DIRECTebanking.com';
DELETE FROM tzahlungsart WHERE cModulId = 'za_dresdnercetelem_jtl';
DELETE FROM tboxvorlage WHERE kBoxvorlage = 6;
DELETE FROM tboxen WHERE kBoxvorlage = 6;
DELETE FROM teinstellungen WHERE cName = 'artikeldetails_finanzierung_anzeigen';
DELETE FROM teinstellungenconf WHERE cWertName = 'artikeldetails_finanzierung_anzeigen';
ALTER TABLE `tartikelabnahme` CHANGE `fIntervall` `fIntervall` DOUBLE NULL DEFAULT '0';
UPDATE `tzahlungsart` SET `cZusatzschrittTemplate` = 'checkout/modules/billpay/zusatzschritt.tpl', `cName` = 'BillPay Rechnung' WHERE `cModulId` = 'za_billpay_invoice_jtl';
UPDATE `tzahlungsart` SET `cZusatzschrittTemplate` = 'checkout/modules/billpay/zusatzschritt.tpl', `cName` = 'BillPay Lastschrift' WHERE `cModulId` = 'za_billpay_direct_debit_jtl';
UPDATE `tzahlungsart` SET `cZusatzschrittTemplate` = 'checkout/modules/billpay/zusatzschritt.tpl', `cName` = 'BillPay Ratenkauf' WHERE `cModulId` = 'za_billpay_rate_payment_jtl';
UPDATE `tzahlungsart` SET `cZusatzschrittTemplate` = 'checkout/modules/billpay/zusatzschritt.tpl', `cName` = 'BillPay PayLater Ratenkauf' WHERE `cModulId` = 'za_billpay_paylater_jtl';
ALTER TABLE `tbestellung` DROP `nZahlungsTyp`;
INSERT INTO `teinstellungenconf` (`kEinstellungenSektion`, `cName`, `cBeschreibung`, `cWertName`, `cInputTyp`, `cModulId`, `nSort`, `nStandardAnzeigen`, `nModul`, `cConf`) VALUES ('100', 'Anzahl Bestellungen n�tig', 'Nur Kunden, die min. soviele Bestellungen bereits durchgef�hrt haben, k�nnen diese Zahlungsart nutzen.', 'zahlungsart_billpay_invoice_min_bestellungen', 'number', 'za_billpay_invoice_jtl', '2000', '1', '0', 'Y');
INSERT INTO `teinstellungenconf` (`kEinstellungenSektion`, `cName`, `cBeschreibung`, `cWertName`, `cInputTyp`, `cModulId`, `nSort`, `nStandardAnzeigen`, `nModul`, `cConf`) VALUES ('100', 'Mindestbestellwert', 'Erst ab diesem Bestellwert kann diese Zahlungsart genutzt werden.', 'zahlungsart_billpay_invoice_min', 'number', 'za_billpay_invoice_jtl', '2000', '1', '0', 'Y');
INSERT INTO `teinstellungenconf` (`kEinstellungenSektion`, `cName`, `cBeschreibung`, `cWertName`, `cInputTyp`, `cModulId`, `nSort`, `nStandardAnzeigen`, `nModul`, `cConf`) VALUES ('100', 'Maximaler Bestellwert', 'Nur bis zu diesem Bestellwert wird diese Zahlungsart angeboten. (einschliesslich)', 'zahlungsart_billpay_invoice_max', 'number', 'za_billpay_invoice_jtl', '2000', '1', '0', 'Y');
INSERT INTO `teinstellungenconf` (`kEinstellungenSektion`, `cName`, `cBeschreibung`, `cWertName`, `cInputTyp`, `cModulId`, `nSort`, `nStandardAnzeigen`, `nModul`, `cConf`) VALUES ('100', 'Anzahl Bestellungen n�tig', 'Nur Kunden, die min. soviele Bestellungen bereits durchgef�hrt haben, k�nnen diese Zahlungsart nutzen.', 'zahlungsart_billpay_direct_debit_min_bestellungen', 'number', 'za_billpay_direct_debit_jtl', '2000', '1', '0', 'Y');
INSERT INTO `teinstellungenconf` (`kEinstellungenSektion`, `cName`, `cBeschreibung`, `cWertName`, `cInputTyp`, `cModulId`, `nSort`, `nStandardAnzeigen`, `nModul`, `cConf`) VALUES ('100', 'Mindestbestellwert', 'Erst ab diesem Bestellwert kann diese Zahlungsart genutzt werden.', 'zahlungsart_billpay_direct_debit_min', 'number', 'za_billpay_direct_debit_jtl', '2000', '1', '0', 'Y');
INSERT INTO `teinstellungenconf` (`kEinstellungenSektion`, `cName`, `cBeschreibung`, `cWertName`, `cInputTyp`, `cModulId`, `nSort`, `nStandardAnzeigen`, `nModul`, `cConf`) VALUES ('100', 'Maximaler Bestellwert', 'Nur bis zu diesem Bestellwert wird diese Zahlungsart angeboten. (einschliesslich)', 'zahlungsart_billpay_direct_debit_max', 'number', 'za_billpay_direct_debit_jtl', '2000', '1', '0', 'Y');
INSERT INTO `teinstellungenconf` (`kEinstellungenSektion`, `cName`, `cBeschreibung`, `cWertName`, `cInputTyp`, `cModulId`, `nSort`, `nStandardAnzeigen`, `nModul`, `cConf`) VALUES ('100', 'Anzahl Bestellungen n�tig', 'Nur Kunden, die min. soviele Bestellungen bereits durchgef�hrt haben, k�nnen diese Zahlungsart nutzen.', 'zahlungsart_billpay_rate_payment_min_bestellungen', 'number', 'za_billpay_rate_payment_jtl', '2000', '1', '0', 'Y');
INSERT INTO `teinstellungenconf` (`kEinstellungenSektion`, `cName`, `cBeschreibung`, `cWertName`, `cInputTyp`, `cModulId`, `nSort`, `nStandardAnzeigen`, `nModul`, `cConf`) VALUES ('100', 'Mindestbestellwert', 'Erst ab diesem Bestellwert kann diese Zahlungsart genutzt werden.', 'zahlungsart_billpay_rate_payment_min', 'number', 'za_billpay_rate_payment_jtl', '2000', '1', '0', 'Y');
INSERT INTO `teinstellungenconf` (`kEinstellungenSektion`, `cName`, `cBeschreibung`, `cWertName`, `cInputTyp`, `cModulId`, `nSort`, `nStandardAnzeigen`, `nModul`, `cConf`) VALUES ('100', 'Maximaler Bestellwert', 'Nur bis zu diesem Bestellwert wird diese Zahlungsart angeboten. (einschliesslich)', 'zahlungsart_billpay_rate_payment_max', 'number', 'za_billpay_rate_payment_jtl', '2000', '1', '0', 'Y');
INSERT INTO `teinstellungenconf` (`kEinstellungenSektion`, `cName`, `cBeschreibung`, `cWertName`, `cInputTyp`, `cModulId`, `nSort`, `nStandardAnzeigen`, `nModul`, `cConf`) VALUES ('100', 'Anzahl Bestellungen n�tig', 'Nur Kunden, die min. soviele Bestellungen bereits durchgef�hrt haben, k�nnen diese Zahlungsart nutzen.', 'zahlungsart_billpay_paylater_min_bestellungen', 'number', 'za_billpay_paylater_jtl', '2000', '1', '0', 'Y');
INSERT INTO `teinstellungenconf` (`kEinstellungenSektion`, `cName`, `cBeschreibung`, `cWertName`, `cInputTyp`, `cModulId`, `nSort`, `nStandardAnzeigen`, `nModul`, `cConf`) VALUES ('100', 'Mindestbestellwert', 'Erst ab diesem Bestellwert kann diese Zahlungsart genutzt werden.', 'zahlungsart_billpay_paylater_min', 'number', 'za_billpay_paylater_jtl', '2000', '1', '0', 'Y');
INSERT INTO `teinstellungenconf` (`kEinstellungenSektion`, `cName`, `cBeschreibung`, `cWertName`, `cInputTyp`, `cModulId`, `nSort`, `nStandardAnzeigen`, `nModul`, `cConf`) VALUES ('100', 'Maximaler Bestellwert', 'Nur bis zu diesem Bestellwert wird diese Zahlungsart angeboten. (einschliesslich)', 'zahlungsart_billpay_paylater_max', 'number', 'za_billpay_paylater_jtl', '2000', '1', '0', 'Y');
INSERT INTO `teinstellungen` (`kEinstellungenSektion`, `cName`, `cWert`, `cModulId`) VALUES ('100', 'zahlungsart_billpay_invoice_min_bestellungen', '0', 'za_billpay_invoice_jtl');
INSERT INTO `teinstellungen` (`kEinstellungenSektion`, `cName`, `cWert`, `cModulId`) VALUES ('100', 'zahlungsart_billpay_invoice_min', '0', 'za_billpay_invoice_jtl');
INSERT INTO `teinstellungen` (`kEinstellungenSektion`, `cName`, `cWert`, `cModulId`) VALUES ('100', 'zahlungsart_billpay_invoice_max', '1000', 'za_billpay_invoice_jtl');
INSERT INTO `teinstellungen` (`kEinstellungenSektion`, `cName`, `cWert`, `cModulId`) VALUES ('100', 'zahlungsart_billpay_direct_debit_min_bestellungen', '0', 'za_billpay_direct_debit_jtl');
INSERT INTO `teinstellungen` (`kEinstellungenSektion`, `cName`, `cWert`, `cModulId`) VALUES ('100', 'zahlungsart_billpay_direct_debit_min', '0', 'za_billpay_direct_debit_jtl');
INSERT INTO `teinstellungen` (`kEinstellungenSektion`, `cName`, `cWert`, `cModulId`) VALUES ('100', 'zahlungsart_billpay_direct_debit_max', '1000', 'za_billpay_direct_debit_jtl');
INSERT INTO `teinstellungen` (`kEinstellungenSektion`, `cName`, `cWert`, `cModulId`) VALUES ('100', 'zahlungsart_billpay_rate_payment_min_bestellungen', '0', 'za_billpay_rate_payment_jtl');
INSERT INTO `teinstellungen` (`kEinstellungenSektion`, `cName`, `cWert`, `cModulId`) VALUES ('100', 'zahlungsart_billpay_rate_payment_min', '100', 'za_billpay_rate_payment_jtl');
INSERT INTO `teinstellungen` (`kEinstellungenSektion`, `cName`, `cWert`, `cModulId`) VALUES ('100', 'zahlungsart_billpay_rate_payment_max', '1000', 'za_billpay_rate_payment_jtl');
INSERT INTO `teinstellungen` (`kEinstellungenSektion`, `cName`, `cWert`, `cModulId`) VALUES ('100', 'zahlungsart_billpay_paylater_min_bestellungen', '0', 'za_billpay_paylater_jtl');
INSERT INTO `teinstellungen` (`kEinstellungenSektion`, `cName`, `cWert`, `cModulId`) VALUES ('100', 'zahlungsart_billpay_paylater_min', '120', 'za_billpay_paylater_jtl');
INSERT INTO `teinstellungen` (`kEinstellungenSektion`, `cName`, `cWert`, `cModulId`) VALUES ('100', 'zahlungsart_billpay_paylater_max', '1400', 'za_billpay_paylater_jtl');
ALTER TABLE `tattributsprache` CHANGE `cName` `cName` VARCHAR(255);
ALTER TABLE `tzahlungsinfo` CHANGE `cIBAN` `cIBAN` varchar(255) COLLATE 'latin1_swedish_ci' NULL AFTER `cKontoNr`, CHANGE `cBIC` `cBIC` varchar(255) COLLATE 'latin1_swedish_ci' NULL AFTER `cIBAN`, ADD `cVerwendungszweck` varchar(255) COLLATE 'latin1_swedish_ci' NULL;
ALTER TABLE `tattribut` CHANGE `cName` `cName` VARCHAR(255);
ALTER TABLE `tkategoriepict` ADD INDEX( `kKategorie`);
INSERT INTO `tsprachwerte` (`kSprachISO`, `kSprachsektion`, `cName`, `cWert`, `cStandard`, `bSystem`) VALUES ('1', '1', 'notAvailableInSelection', 'In der Auswahl nicht verf�gbar', 'In der Auswahl nicht verf�gbar', '1'), ('2', '1', 'notAvailableInSelection', 'Not available in selected options', 'Not available in selected options', '1');
ALTER TABLE `tkategorie` ADD COLUMN `lft` INT NOT NULL DEFAULT 0, ADD COLUMN `rght` INT NOT NULL DEFAULT 0;