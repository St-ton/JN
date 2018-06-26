<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

//mainword
$kKonfigPos            = RequestHelper::verifyGPCDataInt('ek');
$kKategorie            = RequestHelper::verifyGPCDataInt('k');
$kArtikel              = RequestHelper::verifyGPCDataInt('a');
$kVariKindArtikel      = RequestHelper::verifyGPCDataInt('a2');
$kSeite                = RequestHelper::verifyGPCDataInt('s');
$kLink                 = RequestHelper::verifyGPCDataInt('s');
$kHersteller           = RequestHelper::verifyGPCDataInt('h');
$kSuchanfrage          = RequestHelper::verifyGPCDataInt('l');
$kMerkmalWert          = RequestHelper::verifyGPCDataInt('m');
$kTag                  = RequestHelper::verifyGPCDataInt('t');
$kSuchspecial          = RequestHelper::verifyGPCDataInt('q');
$kNews                 = RequestHelper::verifyGPCDataInt('n');
$kNewsMonatsUebersicht = RequestHelper::verifyGPCDataInt('nm');
$kNewsKategorie        = RequestHelper::verifyGPCDataInt('nk');
$kUmfrage              = RequestHelper::verifyGPCDataInt('u');
//filter
$nBewertungSterneFilter = RequestHelper::verifyGPCDataInt('bf');
$cPreisspannenFilter    = RequestHelper::verifyGPDataString('pf');
$kHerstellerFilter      = RequestHelper::verifyGPCDataInt('hf');
$kKategorieFilter       = RequestHelper::verifyGPCDataInt('kf');
$kSuchspecialFilter     = RequestHelper::verifyGPCDataInt('qf');
$kSuchFilter            = RequestHelper::verifyGPCDataInt('sf');
// Erweiterte Artikelübersicht Darstellung
$nDarstellung = RequestHelper::verifyGPCDataInt('ed');
$nSortierung  = RequestHelper::verifyGPCDataInt('sortierreihenfolge');
$nSort        = RequestHelper::verifyGPCDataInt('Sortierung');

$show            = RequestHelper::verifyGPCDataInt('show');
$vergleichsliste = RequestHelper::verifyGPCDataInt('vla');
$bFileNotFound   = false;
$cCanonicalURL   = '';
$is404           = false;
