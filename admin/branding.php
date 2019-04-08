<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Shop;
use JTL\Media\MediaImage;
use JTL\DB\ReturnType;
use JTL\Alert\Alert;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('DISPLAY_BRANDING_VIEW', true, true);
/** @global \JTL\Smarty\JTLSmarty $smarty */
$step        = 'branding_uebersicht';
$alertHelper = Shop::Container()->getAlertService();

if (Request::verifyGPCDataInt('branding') === 1) {
    $step = 'branding_detail';
    if (isset($_POST['speicher_einstellung'])
        && (int)$_POST['speicher_einstellung'] === 1
        && Form::validateToken()
    ) {
        if (speicherEinstellung(Request::verifyGPCDataInt('kBranding'), $_POST, $_FILES)) {
            $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successConfigSave'), 'successConfigSave');
        } else {
            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorFillRequired'), 'errorFillRequired');
        }
    }
    // Hole bestimmtes branding
    if (Request::verifyGPCDataInt('kBranding') > 0) {
        $smarty->assign('oBranding', gibBranding(Request::verifyGPCDataInt('kBranding')));
    }
} else {
    $smarty->assign('oBranding', gibBranding(1));
}

$smarty->assign('cRnd', time())
       ->assign('oBranding_arr', gibBrandings())
       ->assign('PFAD_BRANDINGBILDER', PFAD_BRANDINGBILDER)
       ->assign('step', $step)
       ->display('branding.tpl');

/**
 * @return mixed
 */
function gibBrandings()
{
    return Shop::Container()->getDB()->selectAll('tbranding', [], [], '*', 'cBildKategorie');
}

/**
 * @param int $kBranding
 * @return mixed
 */
function gibBranding(int $kBranding)
{
    return Shop::Container()->getDB()->queryPrepared(
        'SELECT tbranding.*, tbranding.kBranding AS kBrandingTMP, tbrandingeinstellung.*
            FROM tbranding
            LEFT JOIN tbrandingeinstellung 
                ON tbrandingeinstellung.kBranding = tbranding.kBranding
            WHERE tbranding.kBranding = :bid
            GROUP BY tbranding.kBranding',
        ['bid' => $kBranding],
        ReturnType::SINGLE_OBJECT
    );
}

/**
 * @param int   $kBranding
 * @param array $cPost_arr
 * @param array $cFiles_arr
 * @return bool
 */
function speicherEinstellung(int $kBranding, $cPost_arr, $cFiles_arr)
{
    $oBrandingEinstellung               = new stdClass();
    $oBrandingEinstellung->kBranding    = $kBranding;
    $oBrandingEinstellung->cPosition    = $cPost_arr['cPosition'];
    $oBrandingEinstellung->nAktiv       = $cPost_arr['nAktiv'];
    $oBrandingEinstellung->dTransparenz = $cPost_arr['dTransparenz'];
    $oBrandingEinstellung->dGroesse     = $cPost_arr['dGroesse'];

    if (mb_strlen($cFiles_arr['cBrandingBild']['name']) > 0) {
        $oBrandingEinstellung->cBrandingBild = 'kBranding_' . $kBranding .
            mappeFileTyp($cFiles_arr['cBrandingBild']['type']);
    } else {
        $oBrandingEinstellungTMP             = Shop::Container()->getDB()->select(
            'tbrandingeinstellung',
            'kBranding',
            $kBranding
        );
        $oBrandingEinstellung->cBrandingBild = !empty($oBrandingEinstellungTMP->cBrandingBild)
            ? $oBrandingEinstellungTMP->cBrandingBild
            : '';
    }

    if ($oBrandingEinstellung->kBranding > 0
        && mb_strlen($oBrandingEinstellung->cPosition) > 0
        && mb_strlen($oBrandingEinstellung->cBrandingBild) > 0
    ) {
        // Alte Einstellung loeschen
        Shop::Container()->getDB()->delete('tbrandingeinstellung', 'kBranding', $kBranding);

        if (mb_strlen($cFiles_arr['cBrandingBild']['name']) > 0) {
            loescheBrandingBild($oBrandingEinstellung->kBranding);
            speicherBrandingBild($cFiles_arr, $oBrandingEinstellung->kBranding);
        }

        Shop::Container()->getDB()->insert('tbrandingeinstellung', $oBrandingEinstellung);
        MediaImage::clearCache('product');

        return true;
    }

    return false;
}

/**
 * @param array $cFiles_arr
 * @param int   $kBranding
 * @return bool
 * @todo: make size (2097152) configurable?
 */
function speicherBrandingBild($cFiles_arr, int $kBranding)
{
    if (($cFiles_arr['cBrandingBild']['type'] === 'image/jpeg'
        || $cFiles_arr['cBrandingBild']['type'] === 'image/pjpeg'
        || $cFiles_arr['cBrandingBild']['type'] === 'image/gif'
        || $cFiles_arr['cBrandingBild']['type'] === 'image/png'
        || $cFiles_arr['cBrandingBild']['type'] === 'image/bmp'
    ) && $cFiles_arr['cBrandingBild']['size'] <= 2097152
    ) {
        $cUploadDatei = PFAD_ROOT . PFAD_BRANDINGBILDER . 'kBranding_' .
            $kBranding . mappeFileTyp($cFiles_arr['cBrandingBild']['type']);

        return move_uploaded_file($cFiles_arr['cBrandingBild']['tmp_name'], $cUploadDatei);
    }

    return false;
}

/**
 * @param int $kBranding
 */
function loescheBrandingBild(int $kBranding)
{
    if (file_exists(PFAD_ROOT . PFAD_BRANDINGBILDER . 'kBranding_' . $kBranding . '.jpg')) {
        @unlink(PFAD_ROOT . PFAD_BRANDINGBILDER . 'kBranding_' . $kBranding . '.jpg');
    } elseif (file_exists(PFAD_ROOT . PFAD_BRANDINGBILDER . 'kBranding_' . $kBranding . '.png')) {
        @unlink(PFAD_ROOT . PFAD_BRANDINGBILDER . 'kBranding_' . $kBranding . '.png');
    } elseif (file_exists(PFAD_ROOT . PFAD_BRANDINGBILDER . 'kBranding_' . $kBranding . '.gif')) {
        @unlink(PFAD_ROOT . PFAD_BRANDINGBILDER . 'kBranding_' . $kBranding . '.gif');
    } elseif (file_exists(PFAD_ROOT . PFAD_BRANDINGBILDER . 'kBranding_' . $kBranding . '.bmp')) {
        @unlink(PFAD_ROOT . PFAD_BRANDINGBILDER . 'kBranding_' . $kBranding . '.bmp');
    }
}

/**
 * @param string $cTyp
 * @return string
 */
function mappeFileTyp($cTyp)
{
    switch ($cTyp) {
        case 'image/jpeg':
            return '.jpg';
        case 'image/pjpeg':
            return '.jpg';
        case 'image/gif':
            return '.gif';
        case 'image/png':
            return '.png';
        case 'image/bmp':
            return '.bmp';
        default:
            return '.jpg';
    }
}
