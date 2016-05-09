<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

//mappings
$mKunde = array(
    'cKundenNr',
    'cAnrede',
    'cTitel',
    'cVorname',
    'cNachname',
    'cFirma',
    'cStrasse',
    'cAdressZusatz',
    'cPLZ',
    'cOrt',
    'cLand',
    'cTel',
    'cMobil',
    'cFax',
    'cMail',
    'cUSTID',
    'cWWW',
    'cSperre',
    'dGeburtstag',
    'fRabatt',
    'cBundesland',
    'cZusatz'
);

$mKategorie = array(
    'cName',
    'cSeo',
    'cBeschreibung',
    'nSort'
);

$mKategorieSprache = array(
    'cName',
    'cSeo',
    'cBeschreibung',
    'cMetaDescription',
    'cMetaKeywords',
    'cTitleTag',
);

$mKategorieKundengruppe = array(
    'fRabatt'
);

$mKategorieAttribut = array(
    'cName',
    'cWert'
);

$mKategorieSichtbarkeit = array();

$mLieferadresse = array(
    'cFirma',
    'cLand',
    'cNachname',
    'cOrt',
    'cPLZ',
    'cStrasse',
    'cTel',
    'cTitel',
    'cVorname',
    'cAdressZusatz',
    'cZusatz',
    'cAnrede'
);

$mFirma = array(
    'cName',
    'cUnternehmer',
    'cStrasse',
    'cPLZ',
    'cOrt',
    'cLand',
    'cTel',
    'cFax',
    'cEMail',
    'cWWW',
    'cBLZ',
    'cKontoNr',
    'cBank',
    'cUSTID',
    'cSteuerNr',
    'cIBAN',
    'cBIC',
    'cKontoinhaber'
);

$mHersteller = array(
    'cName',
    'cSeo',
    'cHomepage',
    'nSortNr'
);

$mHerstellerSprache = array(
    'cMetaTitle',
    'cMetaKeywords',
    'cMetaDescription',
    'cBeschreibung'
);

//
$mHerstellerSpracheSeo = array(
    'cSeo'
);

$mLieferstatus = array(
    'cName'
);

$mXsellgruppe = array(
    'cName',
    'cBeschreibung'
);

$mEinheit = array(
    'cName'
);

$mKundengruppe = array(
    'cName',
    'fRabatt',
    'cStandard',
    'cShopLogin',
    'nNettoPreise'
);

$mKundengruppensprache = array(
    'cName'
);

$mKundengruppenattribut = array(
    'cName',
    'cWert'
);

$mSprache = array(
    'cNameEnglisch',
    'cNameDeutsch',
    'cISO',
    'cStandard',
    'cShopStandard',
    'cWawiStandard'
);

$mWaehrung = array(
    'cName',
    'cNameHTML',
    'fFaktor',
    'cISO',
    'cStandard',
    'cVorBetrag',
    'cTrennzeichenCent',
    'cTrennzeichenTausend'
);

$mArtikel = array(
    'cArtNr',
    'cName',
    'cHAN',
    'cSeo',
    'cAnmerkung',
    'cBeschreibung',
    'fLagerbestand',
    'fMwSt',
    'fMindestbestellmenge',
    'fLieferantenlagerbestand',
    'fLieferzeit',
    'fStandardpreisNetto', 
    'cBarcode',
    'cTopArtikel',
    'fGewicht',
    'fArtikelgewicht',
    'cNeu',
    'cKurzBeschreibung',
    'fUVP',
    'cLagerBeachten',
    'cLagerKleinerNull',
    'cLagerVariation',
    'cTeilbar',
    'fAbnahmeintervall',
    'cVPE',
    'fVPEWert',
    'cVPEEinheit',
    'nSort',
    'cSuchbegriffe',
    'dErstellt',
    'dErscheinungsdatum',
    'cSerie',
    'cISBN',
    'cASIN',
    'cUNNummer',
    'cGefahrnr',
    'kVersandklasse',
    'nIstVater',
    'kVaterArtikel',
    'kEigenschaftKombi',
    'kVPEEinheit',
    'kStueckliste',
    'kWarengruppe',
    'cTaric',
    'cUPC',
    'cHerkunftsland',
    'cEPID',
    'fZulauf',
    'dZulaufDatum',
    'dMHD',
    'kMassEinheit',
    'kGrundPreisEinheit',
    'fMassMenge',
    'fGrundpreisMenge',
    'fBreite',
    'fHoehe',
    'fLaenge',
    'nLiefertageWennAusverkauft',
    'nAutomatischeLiefertageberechnung',
    'nBearbeitungszeit'
);

$mArtikelQuickSync = array(
    'fLagerbestand'
);

$mPreise = array(
    'fVKNetto' => 0,
    'nAnzahl1' => 0,
    'nAnzahl2' => 0,
    'nAnzahl3' => 0,
    'nAnzahl4' => 0,
    'nAnzahl5' => 0,
    'fPreis1'  => 0,
    'fPreis2'  => 0,
    'fPreis3'  => 0,
    'fPreis4'  => 0,
    'fPreis5'  => 0
);

$mPreis = array(
    'tpreisdetail'
);

$mPreisDetail = array(
    'nAnzahlAb',
    'fNettoPreis'
);

$mArtikelSonderpreis = array(
    'cAktiv'     => 'Y',
    'dStart'     => null,
    'nAnzahl'    => 0,
    'nIstAnzahl' => 0,
    'nIstDatum'  => 0,
    'dEnde'      => '0000-00-00'
);

$mSonderpreise = array(
    'fNettoPreis'
);

$mKategorieArtikel = array();

$mArtikelSprache = array(
    'cName',
    'cSeo',
    'cBeschreibung',
    'cKurzBeschreibung'
);

$mArtikelAttribut = array(
    'cName',
    'cWert'
);

$mAttribut = array(
    'cName',
    'cStringWert',
    'cTextWert',
    'nSort'
);

$mAttributSprache = array(
    'cName',
    'cStringWert',
    'cTextWert'
);

$mEigenschaftsichtbarkeit = array();

$mEigenschaft = array(
    'cName',
    'cTyp',
    'cWaehlbar',
    'nSort'
);

$mEigenschaftSprache = array(
    'cName'
);

$mEigenschaftWert = array(
    'cName'          => null,
    'fAufpreisNetto' => 0,
    'fGewichtDiff'   => 0,
    'cArtNr'         => 0,
    'nSort'          => 0,
    'fLagerbestand'  => 0,
    'fPackeinheit'   => 0
);

$mArtikelSichtbarkeit = array();

$mEigenschaftWertSprache = array(
    'cName'
);

$mEigenschaftWertAufpreis = array(
    'fAufpreisNetto'
);

$mEigenschaftWertSichtbarkeit = array();

$mXSell = array();

$mArtikelPict = array(
    'cPfad',
    'nNr'
);

$mtArtikelPict = array(
    'kArtikel',
    'nNr'
);

$mKategoriePict = array(
    'cPfad',
    'cType'
);

$mKonfiggruppePict = array(
    'cPfad',
    'cType'
);

$mEigenschaftWertPict = array(
    'cPfad',
    'cType'
);

$mDelEigenschaftWertPict = array(
    'kEigenschaftWert'
);

$mBestellung = array(
    'dVersandt',
    'cIdentCode',
    'cVersandInfo',
    'dBezahltDatum',
    'cSendeEMail',
    'nKomplettAusgeliefert',
    'cLogistik',
    'cLogistikURL',
    'fGuthaben',
    'fGesamtsumme',
    'cKommentar',
    'cBestellNr',
    'cZahlungsartName',
    'fWaehrungsFaktor',
    'cBezahlt',
    'cPUIZahlungsdaten'
);

$mGutschein = array(
    'fWert',
    'cGrund'
);

$mSteuerzone = array(
    'cName'
);

$mSteuerzoneland = array(
    'cISO'
);

$mWarengruppe = array(
    'cName'
);

$mWarenlager = array(
    'cName',
    'cKuerzel',
    'cLagerTyp',
    'cBeschreibung',
    'cStrasse',
    'cPLZ',
    'cOrt',
    'cLand',
    'nFulfillment'
);

$mMasseinheit = array(
    'cCode',
);

$mMasseinheitsprache = array(
    'cName',
);

$mArtikelWarenlager = array(
    'fBestand',
    'fZulauf',
    'dZulaufDatum'
);

$mArtikelAbnahme = array(
    'fMindestabnahme',
    'fIntervall'
);

$mSteuerklasse = array(
    'cName',
    'cStandard'
);

$mSteuersatz = array(
    'fSteuersatz',
    'nPrio'
);

$mEigenschaftWertAbhaengigkeit = array();

$mVersandklasse = array(
    'cName'
);

$mMerkmal = array(
    'nSort',
    'cName',
    'nGlobal',
    'cTyp'
);

$mMerkmalSprache = array(
    'cName'
);

$mMerkmalWert = array(
    'nSort'
);

$mMerkmalWertSprache = array(
    'cWert',
    'cSeo',
    'cMetaTitle',
    'cMetaKeywords',
    'cMetaDescription',
    'cBeschreibung'
);

$mMediendatei = array(
    'cPfad',
    'cURL',
    'cTyp',
    'nSort'
);

$mStueckliste = array(
    'fAnzahl'
);

$mMediendateisprache = array(
    'cName',
    'cBeschreibung'
);

$mMediendateiattribut = array(
    'cName',
    'cWert'
);

$mArtikelUpload = array(
    'nTyp',
    'cName',
    'cBeschreibung',
    'cDateiTyp',
    'nPflicht'
);

$mArtikelUploadSprache = array(
    'cName',
    'cBeschreibung'
);

$mArtikelkonfiggruppe = array(
    'nSort'
);

$mHerstellerBild = array(
    'cPfad',
    'cType'
);

$mMerkmalWertBild = array(
    'cPfad',
    'cType'
);

$mMerkmalBild = array(
    'cPfad',
    'cType'
);

$mEigenschaftKombiWert = array();

$mRechnungsadresse = array(
    'cAnrede',
    'cTitel',
    'cVorname',
    'cNachname',
    'cFirma',
    'cStrasse',
    'cAdressZusatz',
    'cPLZ',
    'cOrt',
    'cBundesland',
    'cLand',
    'cTel',
    'cMobil',
    'cFax',
    'cUSTID',
    'cWWW',
    'cMail',
    'cZusatz'
);

$mWarenkorbpos = array(
    'cUnique',
    'cName',
    'cLieferstatus',
    'cArtNr',
    'cEinheit',
    'fPreisEinzelNetto',
    'fPreis',
    'fMwSt',
    'nAnzahl',
    'nPosTyp',
    'cHinweis'
);

$mWarenkorbposeigenschaft = array(
    'cEigenschaftName',
    'cEigenschaftWertName',
    'cFreifeldWert',
    'fAufpreis'
);

$mDownload = array(
    'cID',
    'cPfad',
    'cPfadVorschau',
    'nAnzahl',
    'nTage',
    'dErstellt',
    'nSort',
);

$mDownloadSprache = array(
    'cName',
    'cBeschreibung'
);

$mKonfigSprache = array(
    'cName',
    'cBeschreibung'
);

$mKonfigGruppe = array(
    'nMin',
    'nMax',
    'nTyp',
    'nSort',
    'cKommentar'
);

$mKonfigItem = array(
    'nPosTyp',
    'bSelektiert',
    'bEmpfohlen',
    'bName',
    'bPreis',
    'bRabatt',
    'bZuschlag',
    'fMin',
    'fMax',
    'fInitial',
    'bIgnoreMultiplier',
    'nSort'
);

$mKonfigItemPreis = array(
    'kKundengruppe',
    'kSteuerklasse',
    'fPreis',
    'nTyp'
);

$mLieferschein = array(
    'kLieferschein',
    'kInetBestellung',
    'cLieferscheinNr',
    'cHinweis',
    'nFulfillment',
    'nStatus',
    'dErstellt',
    'bEmailVerschickt'
);

$mLieferscheinpos = array(
    'kLieferscheinPos',
    'kLieferschein',
    'kBestellPos',
    'kWarenlager',
    'fAnzahl'
);

$mLieferscheinposinfo = array(
    'kLieferscheinPos',
    'dMHD',
    'cChargeNr',
    'cSeriennummer'
);

$mVersand = array(
    'kVersand',
    'kLieferschein',
    'cLogistik',
    'cLogistikURL',
    'cIdentCode',
    'cHinweis',
    'dErstellt'
);
