<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class OverlayHelper
 */
class OverlayHelper
{
    /**
     *  get overlays (images) from template folder (original) and create for each valid image the corresponding files
     * (sizes) and data (default settings in tsuchspecialoverlaysprache)
     * @return bool
     */
    public static function loadOverlaysFromTemplateFolder(): bool
    {
        require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'suchspecialoverlay_inc.php';

        $template        = Template::getInstance()->getName();
        $dir             = PFAD_ROOT . PFAD_TEMPLATES . $template . PFAD_OVERLAY_TEMPLATE . 'original';
        if (!is_dir($dir)) {
            return false;
        }
        $overlayInFolder = scandir($dir, 1);
        $db              = Shop::Container()->getDB();

        foreach ($overlayInFolder as $overlay) {
            $overlayParts = explode('_', $overlay);
            if (count($overlayParts) === 3 && $overlayParts[0] === 'overlay') {
                $filePath = $dir . '/' . $overlay;
                $lang     = (int)$overlayParts[1];
                $type     = (int)substr($overlayParts[2], 0, strpos($overlayParts[2], '.'));

                $defaultOverlay = $db->queryPrepared("
                SELECT *
                  FROM tsuchspecialoverlaysprache
                  WHERE cTemplate = 'default'
                    AND kSprache = :lang
                    AND kSuchspecialOverlay = :type",
                    [
                        'lang' => $lang,
                        'type' => $type
                    ],
                    \DB\ReturnType::SINGLE_OBJECT
                );
                if (!empty($defaultOverlay)) {
                    $overlayExists = $db->queryPrepared('
                    SELECT kSuchspecialOverlay
                      FROM tsuchspecialoverlaysprache
                      WHERE cTemplate = :template
                        AND kSprache = :lang
                        AND kSuchspecialOverlay = :type',
                        [
                            'lang'     => $lang,
                            'type'     => $type,
                            'template' => $template
                        ],
                        \DB\ReturnType::SINGLE_OBJECT
                    );
                    if (empty($overlayExists)) {
                        speicherEinstellung(
                            $type,
                            (array)$defaultOverlay,
                            [
                                'type'     => mime_content_type($filePath),
                                'tmp_name' => $filePath,
                                'name'     => $overlay
                            ],
                            $lang
                        );
                    }
                }
            }
        }

        return true;
    }
}
