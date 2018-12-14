<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Helpers;

use DB\ReturnType;
use Shop;

/**
 * Class Overlay
 * @package Helpers
 * @since 5.0.0
 */
class Overlay
{
    /**
     *  get overlays (images) from template folder (original) and create for each valid image the corresponding files
     * (sizes) and data (default settings in tsuchspecialoverlaysprache)
     * example filename: overlay_1_7.jpg | 1 -> overlay language, 7 -> overlay type
     * @param string $template
     * @return bool
     */
    public static function loadOverlaysFromTemplateFolder(string $template): bool
    {
        require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'suchspecialoverlay_inc.php';

        $dir = PFAD_ROOT . PFAD_TEMPLATES . $template . PFAD_OVERLAY_TEMPLATE . 'original';
        if (!\is_dir($dir)) {
            return false;
        }
        $overlayInFolder = \scandir($dir, 1);
        $db              = Shop::Container()->getDB();

        foreach ($overlayInFolder as $overlay) {
            $overlayParts = \explode('_', $overlay);
            if (\count($overlayParts) === 3 && $overlayParts[0] === 'overlay') {
                $filePath = $dir . '/' . $overlay;
                $lang     = (int)$overlayParts[1];
                $type     = (int)\substr($overlayParts[2], 0, \strpos($overlayParts[2], '.'));
                if ($lang === 0 || $type === 0) {
                    continue;
                }
                $defaultOverlay = $db->queryPrepared("
                    SELECT *
                      FROM tsuchspecialoverlaysprache
                      WHERE kSprache = :lang
                        AND kSuchspecialOverlay = :type
                        AND cTemplate IN (:templateName, 'default')
                      ORDER BY FIELD(cTemplate, :templateName, 'default')
                      LIMIT 1",
                    [
                        'lang'         => $lang,
                        'type'         => $type,
                        'templateName' => $template
                    ],
                    ReturnType::SINGLE_OBJECT
                );
                //use default settings for new overlays
                if (!empty($defaultOverlay) && $defaultOverlay->cTemplate !== $template) {
                    speicherEinstellung(
                        $type,
                        (array)$defaultOverlay,
                        [
                            'type'     => \mime_content_type($filePath),
                            'tmp_name' => $filePath,
                            'name'     => $overlay
                        ],
                        $lang,
                        $template
                    );
                }
            }
        }

        return true;
    }
}
