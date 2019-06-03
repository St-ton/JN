<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\CheckBox;
use JTL\Helpers\Text;
use JTL\Shop;

/**
 * @param array $post
 * @param array $languages
 * @return array
 */
function plausiCheckBox($post, $languages)
{
    $plausi = [];
    if (!is_array($languages) || count($languages) === 0) {
        $plausi['oSprache_arr'] = 1;

        return $plausi;
    }
    if (is_array($post) && count($post) > 0) {
        if (!isset($post['cName']) || mb_strlen($post['cName']) === 0) {
            $plausi['cName'] = 1;
        }
        $bText = false;
        foreach ($languages as $oSprache) {
            if (mb_strlen($post['cText_' . $oSprache->cISO]) > 0) {
                $bText = true;
                break;
            }
        }
        if (!$bText) {
            $plausi['cText'] = 1;
        }
        $bLink = true;
        if ((int)$post['nLink'] === 1) {
            $bLink = false;
            if (isset($post['kLink']) && (int)$post['kLink'] > 0) {
                $bLink = true;
            }
        }
        if (!$bLink) {
            $plausi['kLink'] = 1;
        }
        if (!is_array($post['cAnzeigeOrt']) || count($post['cAnzeigeOrt']) === 0) {
            $plausi['cAnzeigeOrt'] = 1;
        } else {
            foreach ($post['cAnzeigeOrt'] as $cAnzeigeOrt) {
                if ((int)$cAnzeigeOrt === 3 && $post['kCheckBoxFunktion'] == 1) {
                    $plausi['cAnzeigeOrt'] = 2;
                }
            }
        }
        if (!isset($post['nPflicht']) || mb_strlen($post['nPflicht']) === 0) {
            $plausi['nPflicht'] = 1;
        }
        if (!isset($post['nAktiv']) || mb_strlen($post['nAktiv']) === 0) {
            $plausi['nAktiv'] = 1;
        }
        if (!isset($post['nLogging']) || mb_strlen($post['nLogging']) === 0) {
            $plausi['nLogging'] = 1;
        }
        if (!isset($post['nSort']) || (int)$post['nSort'] === 0) {
            $plausi['nSort'] = 1;
        }
        if (!is_array($post['kKundengruppe']) || count($post['kKundengruppe']) === 0) {
            $plausi['kKundengruppe'] = 1;
        }
    }

    return $plausi;
}

/**
 * @param array $post
 * @param array $languages
 * @return CheckBox
 */
function speicherCheckBox($post, $languages)
{
    if (isset($post['kCheckBox']) && (int)$post['kCheckBox'] > 0) {
        $checkBox = new CheckBox((int)$post['kCheckBox']);
        $checkBox->deleteCheckBox([(int)$post['kCheckBox']]);
    } else {
        $checkBox = new CheckBox();
    }
    $checkBox->kLink = 0;
    if ((int)$post['nLink'] === 1) {
        $checkBox->kLink = (int)$post['kLink'];
    }
    $checkBox->kCheckBoxFunktion = (int)$post['kCheckBoxFunktion'];
    $checkBox->cName             = htmlspecialchars($post['cName'], ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
    $checkBox->cKundengruppe     = Text::createSSK($post['kKundengruppe']);
    $checkBox->cAnzeigeOrt       = Text::createSSK($post['cAnzeigeOrt']);
    $checkBox->nAktiv            = 0;
    if ($post['nAktiv'] === 'Y') {
        $checkBox->nAktiv = 1;
    }
    $checkBox->nPflicht = 0;
    $checkBox->nLogging = 0;
    if ($post['nLogging'] === 'Y') {
        $checkBox->nLogging = 1;
    }
    if ($post['nPflicht'] === 'Y') {
        $checkBox->nPflicht = 1;
    }
    $checkBox->nSort     = (int)$post['nSort'];
    $checkBox->dErstellt = 'NOW()';
    $texts               = [];
    $descr               = [];
    foreach ($languages as $language) {
        $texts[$language->cISO] = str_replace('"', '&quot;', $post['cText_' . $language->cISO]);
        $descr[$language->cISO] = str_replace('"', '&quot;', $post['cBeschreibung_' . $language->cISO]);
    }

    $checkBox->insertDB($texts, $descr);
    Shop::Container()->getCache()->flushTags(['checkbox']);

    return $checkBox;
}
