<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param array $cPost_arr
 * @param array $oSprache_arr
 * @return array
 */
function plausiCheckBox($cPost_arr, $oSprache_arr)
{
    $plausi = [];
    if (!is_array($oSprache_arr) || count($oSprache_arr) === 0) {
        $plausi['oSprache_arr'] = 1;

        return $plausi;
    }
    if (is_array($cPost_arr) && count($cPost_arr) > 0) {
        if (!isset($cPost_arr['cName']) || mb_strlen($cPost_arr['cName']) === 0) {
            $plausi['cName'] = 1;
        }
        $bText = false;
        foreach ($oSprache_arr as $oSprache) {
            if (mb_strlen($cPost_arr['cText_' . $oSprache->cISO]) > 0) {
                $bText = true;
                break;
            }
        }
        if (!$bText) {
            $plausi['cText'] = 1;
        }
        $bLink = true;
        if ((int)$cPost_arr['nLink'] === 1) {
            $bLink = false;
            if (isset($cPost_arr['kLink']) && (int)$cPost_arr['kLink'] > 0) {
                $bLink = true;
            }
        }
        if (!$bLink) {
            $plausi['kLink'] = 1;
        }
        if (!is_array($cPost_arr['cAnzeigeOrt']) || count($cPost_arr['cAnzeigeOrt']) === 0) {
            $plausi['cAnzeigeOrt'] = 1;
        } else {
            foreach ($cPost_arr['cAnzeigeOrt'] as $cAnzeigeOrt) {
                if ((int)$cAnzeigeOrt === 3 && $cPost_arr['kCheckBoxFunktion'] == 1) {
                    $plausi['cAnzeigeOrt'] = 2;
                }
            }
        }
        if (!isset($cPost_arr['nPflicht']) || mb_strlen($cPost_arr['nPflicht']) === 0) {
            $plausi['nPflicht'] = 1;
        }
        if (!isset($cPost_arr['nAktiv']) || mb_strlen($cPost_arr['nAktiv']) === 0) {
            $plausi['nAktiv'] = 1;
        }
        if (!isset($cPost_arr['nLogging']) || mb_strlen($cPost_arr['nLogging']) === 0) {
            $plausi['nLogging'] = 1;
        }
        if (!isset($cPost_arr['nSort']) || (int)$cPost_arr['nSort'] === 0) {
            $plausi['nSort'] = 1;
        }
        if (!is_array($cPost_arr['kKundengruppe']) || count($cPost_arr['kKundengruppe']) === 0) {
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
    $checkBox->cKundengruppe     = StringHandler::createSSK($post['kKundengruppe']);
    $checkBox->cAnzeigeOrt       = StringHandler::createSSK($post['cAnzeigeOrt']);
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
