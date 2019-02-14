<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Helpers\Request;

$kKonfigPos            = Request::verifyGPCDataInt('ek');
$kKategorie            = Request::verifyGPCDataInt('k');
$kArtikel              = Request::verifyGPCDataInt('a');
$kVariKindArtikel      = Request::verifyGPCDataInt('a2');
$kSeite                = Request::verifyGPCDataInt('s');
$kLink                 = Request::verifyGPCDataInt('s');
$kHersteller           = Request::verifyGPCDataInt('h');
$kSuchanfrage          = Request::verifyGPCDataInt('l');
$kMerkmalWert          = Request::verifyGPCDataInt('m');
$kTag                  = Request::verifyGPCDataInt('t');
$kSuchspecial          = Request::verifyGPCDataInt('q');
$kNews                 = Request::verifyGPCDataInt('n');
$kNewsMonatsUebersicht = Request::verifyGPCDataInt('nm');
$kNewsKategorie        = Request::verifyGPCDataInt('nk');
$kUmfrage              = Request::verifyGPCDataInt('u');
// filter
$nBewertungSterneFilter = Request::verifyGPCDataInt('bf');
$cPreisspannenFilter    = Request::verifyGPDataString('pf');
$kHerstellerFilter      = Request::verifyGPCDataInt('hf');
$kKategorieFilter       = Request::verifyGPCDataInt('kf');
$kSuchspecialFilter     = Request::verifyGPCDataInt('qf');
$kSuchFilter            = Request::verifyGPCDataInt('sf');
// Erweiterte Artikelübersicht Darstellung
$nDarstellung = Request::verifyGPCDataInt('ed');
$nSortierung  = Request::verifyGPCDataInt('sortierreihenfolge');
$nSort        = Request::verifyGPCDataInt('Sortierung');

$show            = Request::verifyGPCDataInt('show');
$vergleichsliste = Request::verifyGPCDataInt('vla');
$bFileNotFound   = false;
$cCanonicalURL   = '';
$is404           = false;
