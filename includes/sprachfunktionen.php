<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param Warenkorb $warenkorb
 * @return string
 */
function lang_warenkorb_warenkorbEnthaeltXArtikel($warenkorb)
{
    if ($warenkorb === null) {
        return '';
    }
    if ($warenkorb->hatTeilbareArtikel()) {
        $nPositionen = $warenkorb->gibAnzahlPositionenExt([C_WARENKORBPOS_TYP_ARTIKEL]);
        $ret         = Shop::Lang()->get('yourbasketcontains', 'checkout') . ' ' . $nPositionen . ' ';
        if ($nPositionen === 1) {
            $ret .= Shop::Lang()->get('position');
        } else {
            $ret .= Shop::Lang()->get('positions');
        }

        return $ret;
    }
    $nArtikel = $warenkorb instanceof Warenkorb
        ? $warenkorb->gibAnzahlArtikelExt([C_WARENKORBPOS_TYP_ARTIKEL])
        : 0;
    $nArtikel = str_replace('.', ',', $nArtikel);
    if ($nArtikel == 1) {
        return Shop::Lang()->get('yourbasketcontains', 'checkout') . ' ' .
            $nArtikel . ' ' . Shop::Lang()->get('product');
    }
    if ($nArtikel > 1) {
        return Shop::Lang()->get('yourbasketcontains', 'checkout') . ' ' .
            $nArtikel . ' ' . Shop::Lang()->get('products');
    }
    if ($nArtikel == 0) {
        return Shop::Lang()->get('emptybasket', 'checkout');
    }

    return '';
}

/**
 * @param Warenkorb $warenkorb
 * @return string,
 */
function lang_warenkorb_warenkorbLabel($warenkorb)
{
    $cLabel = Shop::Lang()->get('basket', 'checkout');
    if ($warenkorb !== null) {
        $cLabel .= ' (' . Preise::getLocalizedPriceString(
            $warenkorb->gibGesamtsummeWarenExt(
                [C_WARENKORBPOS_TYP_ARTIKEL],
                !Session::CustomerGroup()->isMerchant()
            )) . ')';
    }

    return $cLabel;
}

/**
 * @param Warenkorb $warenkorb
 * @return string
 */
function lang_warenkorb_bestellungEnthaeltXArtikel($warenkorb)
{
    $ret = Shop::Lang()->get('yourordercontains', 'checkout') . ' ' . count($warenkorb->PositionenArr) . ' ';
    if (count($warenkorb->PositionenArr) === 1) {
        $ret .= Shop::Lang()->get('position');
    } else {
        $ret .= Shop::Lang()->get('positions');
    }
    $positionCount = !empty($warenkorb->kWarenkorb)
        ? $warenkorb->gibAnzahlArtikelExt([C_WARENKORBPOS_TYP_ARTIKEL])
        : 0;

    return $ret . ' ' . Shop::Lang()->get('with') . ' ' . lang_warenkorb_Artikelanzahl($positionCount);
}

/**
 * @param int $anzahlArtikel
 * @return string
 */
function lang_warenkorb_Artikelanzahl($anzahlArtikel)
{
    return $anzahlArtikel == 1
        ? ($anzahlArtikel . ' ' . Shop::Lang()->get('product'))
        : ($anzahlArtikel . ' ' . Shop::Lang()->get('products'));
}

/**
 * @param int $laenge
 * @return string
 */
function lang_passwortlaenge($laenge)
{
    return $laenge . ' ' . Shop::Lang()->get('min', 'characters') . '!';
}

/**
 * @param int  $ust
 * @param bool $netto
 * @return string
 */
function lang_steuerposition($ust, $netto)
{
    if ($ust == (int)$ust) {
        $ust = (int)$ust;
    }
    return $netto
        ? Shop::Lang()->get('plus', 'productDetails') . ' ' . $ust . '% ' . Shop::Lang()->get('vat', 'productDetails')
        : Shop::Lang()->get('incl', 'productDetails') . ' ' . $ust . '% ' . Shop::Lang()->get('vat', 'productDetails');
}

/**
 * @param string $suchausdruck
 * @param int    $anzahl
 * @return string
 */
function lang_suche_mindestanzahl($suchausdruck, $anzahl)
{
    return Shop::Lang()->get('expressionHasTo') . ' ' .
        $anzahl . ' ' .
        Shop::Lang()->get('characters') . '<br />' .
        Shop::Lang()->get('yourSearch') . ': ' . $suchausdruck;
}

/**
 * @param int $status
 * @return mixed
 */
function lang_bestellstatus($status)
{
    switch ($status) {
        case BESTELLUNG_STATUS_OFFEN:
            return Shop::Lang()->get('statusPending', 'order');
        case BESTELLUNG_STATUS_IN_BEARBEITUNG:
            return Shop::Lang()->get('statusProcessing', 'order');
        case BESTELLUNG_STATUS_BEZAHLT:
            return Shop::Lang()->get('statusPaid', 'order');
        case BESTELLUNG_STATUS_VERSANDT:
            return Shop::Lang()->get('statusShipped', 'order');
        case BESTELLUNG_STATUS_STORNO:
            return Shop::Lang()->get('statusCancelled', 'order');
        case BESTELLUNG_STATUS_TEILVERSANDT:
            return Shop::Lang()->get('statusPartialShipped', 'order');
        default:
            return '';
    }
}

/**
 * @param Artikel   $Artikel
 * @param int|float $beabsichtigteKaufmenge
 * @param int       $kKonfigitem
 * @return string
 */
function lang_mindestbestellmenge($Artikel, $beabsichtigteKaufmenge, $kKonfigitem = 0)
{
    if ($Artikel->cEinheit) {
        $Artikel->cEinheit = ' ' . $Artikel->cEinheit;
    }
    $cName = $Artikel->cName;
    if ((int)$kKonfigitem > 0 && class_exists('Konfigitem')) {
        $cName = (new Konfigitem($kKonfigitem))->getName();
    }

    return Shop::Lang()->get('product') . ' &quot;' . $cName . '&quot; ' .
        Shop::Lang()->get('hasMbm', 'messages') . ' (' .
        $Artikel->fMindestbestellmenge . $Artikel->cEinheit . '). ' .
        Shop::Lang()->get('yourQuantity', 'messages') . ' ' .
        (float)$beabsichtigteKaufmenge . $Artikel->cEinheit . '.';
}
