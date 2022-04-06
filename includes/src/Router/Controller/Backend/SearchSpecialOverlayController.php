<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Media\Image;
use JTL\Media\Image\Overlay;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use League\Route\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class SearchSpecialOverlayController
 * @package JTL\Router\Controller\Backend
 */
class SearchSpecialOverlayController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions('DISPLAY_ARTICLEOVERLAYS_VIEW');
        $this->getText->loadAdminLocale('pages/suchspecialoverlay');

        $step = 'suchspecialoverlay_uebersicht';
        $this->setzeSprache();
        $overlay = $this->gibSuchspecialOverlay(1);
        if (Request::verifyGPCDataInt('suchspecialoverlay') === 1) {
            $step = 'suchspecialoverlay_detail';
            $oID  = Request::verifyGPCDataInt('kSuchspecialOverlay');
            if (Request::postInt('speicher_einstellung') === 1
                && Form::validateToken()
                && $this->speicherEinstellung($oID, $_POST, $_FILES['cSuchspecialOverlayBild'])
            ) {
                $this->cache->flushTags([\CACHING_GROUP_OPTION, \CACHING_GROUP_ARTICLE]);
                $this->alertService->addSuccess(\__('successConfigSave'), 'successConfigSave');
            }
            if ($oID > 0) {
                $overlay = $this->gibSuchspecialOverlay($oID);
            }
        }
        $overlays = $this->gibAlleSuchspecialOverlays();
        $template = Shop::Container()->getTemplateService()->getActiveTemplate();
        if ($template->getName() === 'Evo'
            && $template->getAuthor() === 'JTL-Software-GmbH'
            && (int)$template->getVersion() >= 4
        ) {
            $smarty->assign('isDeprecated', true);
        }

        return $smarty->assign('cRnd', \time())
            ->assign('oSuchspecialOverlay', $overlay)
            ->assign('nMaxFileSize', \getMaxFileSize(\ini_get('upload_max_filesize')))
            ->assign('oSuchspecialOverlay_arr', $overlays)
            ->assign('nSuchspecialOverlayAnzahl', count($overlays) + 1)
            ->assign('step', $step)
            ->assign('path', $route->getPath())
            ->getResponse('suchspecialoverlay.tpl');
    }

    /**
     * @return Overlay[]
     */
    private function gibAlleSuchspecialOverlays(): array
    {
        $overlays = [];
        foreach ($this->db->getInts(
            'SELECT kSuchspecialOverlay FROM tsuchspecialoverlay',
            'kSuchspecialOverlay'
        ) as $type) {
            $overlays[] = Overlay::getInstance($type, (int)$_SESSION['editLanguageID']);
        }

        return $overlays;
    }

    /**
     * @param int $overlayID
     * @return Overlay
     */
    private function gibSuchspecialOverlay(int $overlayID): Overlay
    {
        return Overlay::getInstance($overlayID, (int)$_SESSION['editLanguageID']);
    }

    /**
     * @param int         $overlayID
     * @param array       $post
     * @param array       $files
     * @param int|null    $lang
     * @param string|null $template
     * @return bool
     */
    private function speicherEinstellung(
        int $overlayID,
        array $post,
        array $files,
        int $lang = null,
        string $template = null
    ): bool {
        $overlay = Overlay::getInstance(
            $overlayID,
            $lang ?? (int)$_SESSION['editLanguageID'],
            $template,
            false
        );

        if ($overlay->getType() <= 0) {
            $this->alertService->addError(\__('invalidOverlay'), 'invalidOverlay');
            return false;
        }
        $overlay->setActive((int)$post['nAktiv'])
            ->setTransparence((int)$post['nTransparenz'])
            ->setSize((int)$post['nGroesse'])
            ->setPosition((int)($post['nPosition'] ?? 0))
            ->setPriority((int)$post['nPrio']);

        if (mb_strlen($files['name']) > 0) {
            $template    = $template ?: Shop::Container()->getTemplateService()->getActiveTemplate()->getName();
            $overlayPath = PFAD_ROOT . \PFAD_TEMPLATES . $template . \PFAD_OVERLAY_TEMPLATE;
            if (!\is_writable($overlayPath)) {
                $this->alertService->addError(
                    \sprintf(\__('errorOverlayWritePermissions'), \PFAD_TEMPLATES . $template . \PFAD_OVERLAY_TEMPLATE),
                    'errorOverlayWritePermissions',
                    ['saveInSession' => true]
                );

                return false;
            }

            $this->loescheBild($overlay);
            $overlay->setImageName(
                Overlay::IMAGENAME_TEMPLATE . '_' . $overlay->getLanguage() . '_' . $overlay->getType() .
                $this->mappeFileTyp($files['type'])
            );
            $imageCreated = $this->speicherBild($files, $overlay);
        }
        if (!isset($imageCreated) || $imageCreated) {
            $overlay->save();
        } else {
            $this->alertService->addError(
                \__('errorFileUploadGeneral'),
                'errorFileUploadGeneral',
                ['saveInSession' => true]
            );

            return false;
        }

        return true;
    }

    /**
     * @param resource $dst_im
     * @param resource $src_im
     * @param int      $dst_x
     * @param int      $dst_y
     * @param int      $src_x
     * @param int      $src_y
     * @param int      $src_w
     * @param int      $src_h
     * @param int      $pct
     * @return bool
     */
    private function imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct): bool
    {
        if ($pct === null) {
            return false;
        }
        $pct /= 100;
        // Get image width and height
        $w = \imagesx($src_im);
        $h = \imagesy($src_im);
        // Turn alpha blending off
        \imagealphablending($src_im, false);

        $minalpha = 0;

        // loop through image pixels and modify alpha for each
        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                // get current alpha value (represents the TANSPARENCY!)
                $colorxy = \imagecolorat($src_im, $x, $y);
                $alpha   = ($colorxy >> 24) & 0xFF;
                // calculate new alpha
                if ($minalpha !== 127) {
                    $alpha = 127 + 127 * $pct * ($alpha - 127) / (127 - $minalpha);
                } else {
                    $alpha += 127 * $pct;
                }
                // get the color index with new alpha
                $alphacolorxy = \imagecolorallocatealpha(
                    $src_im,
                    ($colorxy >> 16) & 0xFF,
                    ($colorxy >> 8) & 0xFF,
                    $colorxy & 0xFF,
                    (int)$alpha
                );
                // set pixel with the new color + opacity
                if (!\imagesetpixel($src_im, $x, $y, $alphacolorxy)) {
                    return false;
                }
            }
        }

        return \imagecopy($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h);
    }

    /**
     * @param string $img
     * @param int    $width
     * @param int    $height
     * @return resource|null
     */
    private function imageload_alpha($img, int $width, int $height)
    {
        $imgInfo = \getimagesize($img);
        switch ($imgInfo[2]) {
            case 1:
                $im = \imagecreatefromgif($img);
                break;
            case 2:
                $im = \imagecreatefromjpeg($img);
                break;
            case 3:
                $im = \imagecreatefrompng($img);
                break;
            default:
                return null;
        }

        $new = \imagecreatetruecolor($width, $height);

        if ($imgInfo[2] == 1 || $imgInfo[2] == 3) {
            \imagealphablending($new, false);
            \imagesavealpha($new, true);
            $transparent = \imagecolorallocatealpha($new, 255, 255, 255, 127);
            \imagefilledrectangle($new, 0, 0, $width, $height, $transparent);
        }

        \imagecopyresampled($new, $im, 0, 0, 0, 0, $width, $height, $imgInfo[0], $imgInfo[1]);

        return $new;
    }

    /**
     * @param string $image
     * @param int    $width
     * @param int    $height
     * @param int    $transparency
     * @return resource
     */
    private function ladeOverlay($image, int $width, int $height, int $transparency)
    {
        $src = $this->imageload_alpha($image, $width, $height);
        if ($transparency > 0) {
            $new = \imagecreatetruecolor($width, $height);
            \imagealphablending($new, false);
            \imagesavealpha($new, true);
            $transparent = \imagecolorallocatealpha($new, 255, 255, 255, 127);
            \imagefilledrectangle($new, 0, 0, $width, $height, $transparent);
            \imagealphablending($new, true);
            \imagesavealpha($new, true);

            $this->imagecopymerge_alpha($new, $src, 0, 0, 0, 0, $width, $height, 100 - $transparency);

            return $new;
        }

        return $src;
    }

    /**
     * @param resource $im
     * @param string   $extension
     * @param string   $path
     * @param int      $quality
     * @return bool
     */
    private function speicherOverlay($im, string $extension, string $path, int $quality = 80): bool
    {
        if (!$extension || !$im) {
            return false;
        }
        switch ($extension) {
            case '.jpg':
                return \function_exists('imagejpeg') && \imagejpeg($im, $path, $quality);
            case '.png':
                return \function_exists('imagepng') && \imagepng($im, $path);
            case '.gif':
                return \function_exists('imagegif') && \imagegif($im, $path);
            case '.bmp':
                return \function_exists('imagewbmp') && \imagewbmp($im, $path);
            default:
                return false;
        }
    }

    /**
     * @param string $image
     * @param int    $size
     * @param int    $transparency
     * @param string $extension
     * @param string $path
     * @return bool
     */
    private function erstelleFixedOverlay(string $image, int $size, int $transparency, string $extension, string $path): bool
    {
        [$width, $height] = \getimagesize($image);
        $factor           = $size / $width;

        return $this->speicherOverlay($this->ladeOverlay($image, $size, (int)($height * $factor), $transparency), $extension, $path);
    }


    /**
     * @param array   $file
     * @param Overlay $overlay
     * @return bool
     */
    private function speicherBild(array $file, Overlay $overlay): bool
    {
        if (!Image::isImageUpload($file)) {
            return false;
        }
        $ext           = $this->mappeFileTyp($file['type']);
        $original      = $file['tmp_name'];
        $sizesToCreate = [
            ['size' => \IMAGE_SIZE_XS, 'factor' => 1],
            ['size' => \IMAGE_SIZE_SM, 'factor' => 2],
            ['size' => \IMAGE_SIZE_MD, 'factor' => 3],
            ['size' => \IMAGE_SIZE_LG, 'factor' => 4]
        ];

        foreach ($sizesToCreate as $sizeToCreate) {
            if (!\is_dir(PFAD_ROOT . $overlay->getPathSize($sizeToCreate['size']))) {
                \mkdir(PFAD_ROOT . $overlay->getPathSize($sizeToCreate['size']), 0755, true);
            }
            $imageCreated = $this->erstelleFixedOverlay(
                $original,
                $overlay->getSize() * $sizeToCreate['factor'],
                $overlay->getTransparance(),
                $ext,
                PFAD_ROOT . $overlay->getPathSize($sizeToCreate['size']) . $overlay->getImageName()
            );
            if (!$imageCreated) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param Overlay $overlay
     */
    private function loescheBild(Overlay $overlay): void
    {
        foreach ($overlay->getPathSizes() as $path) {
            $path = PFAD_ROOT . $path . $overlay->getImageName();
            if (\file_exists($path)) {
                @\unlink($path);
            }
        }
    }

    /**
     * @param string $type
     * @return string
     */
    private function mappeFileTyp(string $type): string
    {
        switch ($type) {
            case 'image/gif':
                return '.gif';
            case 'image/png':
            case 'image/x-png':
                return '.png';
            case 'image/bmp':
                return '.bmp';
            case 'image/jpg':
            case 'image/jpeg':
            case 'image/pjpeg':
            default:
                return '.jpg';
        }
    }
}
