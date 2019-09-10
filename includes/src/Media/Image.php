<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Media;

use Exception;
use Intervention\Image\Constraint;
use Intervention\Image\ImageManager;
use JTL\Catalog\Category\Kategorie;
use JTL\Catalog\Hersteller;
use JTL\DB\ReturnType;
use JTL\News\Category;
use JTL\News\Item;
use JTL\Shop;

/**
 * Class Image
 * @package JTL\Media
 */
class Image
{
    /**
     * Image types
     */
    public const TYPE_PRODUCT         = 'product';
    public const TYPE_CATEGORY        = 'category';
    public const TYPE_CONFIGGROUP     = 'configgroup';
    public const TYPE_VARIATION       = 'variation';
    public const TYPE_MANUFACTURER    = 'manufacturer';
    public const TYPE_ATTRIBUTE       = 'attribute';
    public const TYPE_ATTRIBUTE_VALUE = 'attributevalue';
    public const TYPE_NEWS            = 'news';
    public const TYPE_NEWSCATEGORY    = 'newscategory';
    public const TYPE_CHARACTERISTIC  = 'characteristic';

    /**
     * Image sizes
     */
    public const SIZE_XS = 'xs';
    public const SIZE_SM = 'sm';
    public const SIZE_MD = 'md';
    public const SIZE_LG = 'lg';

    /**
     * Image type map
     *
     * @var array
     */
    private static $typeMapper = [
        'artikel'      => self::TYPE_PRODUCT,
        'produkte'     => self::TYPE_PRODUCT,
        'kategorien'   => self::TYPE_CATEGORY,
        'kategorie'    => self::TYPE_CATEGORY,
        'konfigurator' => self::TYPE_CONFIGGROUP,
        'variationen'  => self::TYPE_VARIATION,
        'hersteller'   => self::TYPE_MANUFACTURER,
        'merkmale'     => self::TYPE_ATTRIBUTE,
        'merkmalwerte' => self::TYPE_ATTRIBUTE_VALUE
    ];

    /**
     * Image size map
     *
     * @var array
     */
    private static $sizeMapper = [
        'mini'   => self::SIZE_XS,
        'klein'  => self::SIZE_SM,
        'normal' => self::SIZE_MD,
        'gross'  => self::SIZE_LG
    ];

    /**
     * Image size map
     *
     * @var array
     */
    private static $positionMapper = [
        'oben'         => 'top',
        'oben-rechts'  => 'top-right',
        'rechts'       => 'right',
        'unten-rechts' => 'bottom-right',
        'unten'        => 'bottom',
        'unten-links'  => 'bottom-left',
        'links'        => 'left',
        'oben-links'   => 'top-left',
        'zentriert'    => 'center'
    ];

    /**
     * Image settings
     *
     * @var array
     */
    private static $settings;

    /**
     *  Global image settings
     *
     * @return array
     */
    public static function getSettings(): array
    {
        if (self::$settings !== null) {
            return self::$settings;
        }
        $settings = Shop::getSettings([\CONF_BILDER]);
        $settings = \array_shift($settings);
        $branding = self::getBranding();

        self::$settings = [
            'background' => $settings['bilder_hintergrundfarbe'],
            'container'  => $settings['container_verwenden'] === 'Y',
            'format'     => \mb_convert_case($settings['bilder_dateiformat'], \MB_CASE_LOWER),
            'scale'      => $settings['bilder_skalieren'] === 'Y',
            'quality'    => (int)$settings['bilder_jpg_quali'],
            'branding'   => $branding[self::TYPE_PRODUCT] ?? null,
            'size'       => [
                self::SIZE_XS => [
                    'width'  => (int)$settings['bilder_artikel_mini_breite'],
                    'height' => (int)$settings['bilder_artikel_mini_hoehe']
                ],
                self::SIZE_SM => [
                    'width'  => (int)$settings['bilder_artikel_klein_breite'],
                    'height' => (int)$settings['bilder_artikel_klein_hoehe']
                ],
                self::SIZE_MD => [
                    'width'  => (int)$settings['bilder_artikel_normal_breite'],
                    'height' => (int)$settings['bilder_artikel_normal_hoehe']
                ],
                self::SIZE_LG => [
                    'width'  => (int)$settings['bilder_artikel_gross_breite'],
                    'height' => (int)$settings['bilder_artikel_gross_hoehe']
                ]
            ],
            'naming'     => [
                self::TYPE_PRODUCT   => (int)$settings['bilder_artikel_namen'],
                self::TYPE_CATEGORY  => (int)$settings['bilder_kategorie_namen'],
                self::TYPE_VARIATION => (int)$settings['bilder_variation_namen']
            ]
        ];

        return self::$settings;
    }

    /**
     * Convert old size naming
     *
     * @param string     $size
     * @param bool|false $flip
     * @return null
     */
    public static function mapSize($size, $flip = false)
    {
        $size   = \mb_convert_case($size, \MB_CASE_LOWER);
        $mapper = $flip ? \array_flip(self::$sizeMapper) : self::$sizeMapper;

        return $mapper[$size] ?? null;
    }

    /**
     * Convert old type naming
     *
     * @param string     $type
     * @param bool|false $flip
     * @return null
     */
    public static function mapType($type, $flip = false)
    {
        $type   = \mb_convert_case($type, \MB_CASE_LOWER);
        $mapper = $flip ? \array_flip(self::$typeMapper) : self::$typeMapper;

        return $mapper[$type] ?? null;
    }

    /**
     * Convert old position naming
     *
     * @param string     $position
     * @param bool|false $flip
     * @return null
     */
    public static function mapPosition($position, $flip = false)
    {
        $position = \mb_convert_case($position, \MB_CASE_LOWER);
        $mapper   = $flip ? \array_flip(self::$positionMapper) : self::$positionMapper;

        return $mapper[$position] ?? null;
    }

    /**
     * Convert old branding naming
     *
     * @return array
     */
    private static function getBranding(): array
    {
        $branding    = [];
        $brandingTmp = Shop::Container()->getDB()->query(
            'SELECT tbranding.cBildKategorie AS type, 
            tbrandingeinstellung.cPosition AS position, tbrandingeinstellung.cBrandingBild AS path,
            tbrandingeinstellung.dTransparenz AS transparency, tbrandingeinstellung.dGroesse AS size
                FROM tbrandingeinstellung
                INNER JOIN tbranding 
                    ON tbrandingeinstellung.kBranding = tbranding.kBranding
                WHERE tbrandingeinstellung.nAktiv = 1',
            ReturnType::ARRAY_OF_OBJECTS
        );

        foreach ($brandingTmp as $b) {
            $b->size            = (int)$b->size;
            $b->transparency    = (int)$b->transparency;
            $b->type            = self::mapType($b->type);
            $b->position        = self::mapPosition($b->position);
            $b->path            = \PFAD_ROOT . \PFAD_BRANDINGBILDER . $b->path;
            $branding[$b->type] = $b;
        }

        return $branding;
    }

    /**
     * @param string $filepath
     * @return int|string
     */
    public static function getMimeType(string $filepath)
    {
        $type = self::getImageType($filepath);

        return $type !== null
            ? \image_type_to_mime_type($type)
            : \IMAGETYPE_JPEG;
    }

    /**
     * @param string $filepath
     * @return int|null
     */
    public static function getImageType(string $filepath)
    {
        if (\function_exists('exif_imagetype')) {
            return \exif_imagetype($filepath);
        }
        $info = \getimagesize($filepath);

        return \is_array($info) && isset($info['type'])
            ? $info['type']
            : null;
    }

    /**
     * @param string $type
     * @param object $mixed
     * @return string
     */
    public static function getCustomName(string $type, $mixed): string
    {
        $result   = '';
        $settings = self::getSettings();

        switch ($type) {
            case self::TYPE_PRODUCT:
                switch ($settings['naming']['product']) {
                    case 0:
                        $result = $mixed->kArtikel;
                        break;
                    case 1:
                        $result = $mixed->cArtNr;
                        break;
                    case 2:
                        $result = empty($mixed->cSeo) ? $mixed->cName : $mixed->cSeo;
                        break;
                    case 3:
                        $result = \sprintf('%s_%s', $mixed->cArtNr, empty($mixed->cSeo) ? $mixed->cName : $mixed->cSeo);
                        break;
                    case 4:
                        $result = $mixed->cBarcode;
                        break;
                }
                break;
            case self::TYPE_CATEGORY:
            case self::TYPE_MANUFACTURER:
                $result = empty($mixed->cSeo) ? $mixed->cName : $mixed->cSeo;
                break;
            case self::TYPE_NEWS:
            case self::TYPE_NEWSCATEGORY:
                $result = $mixed->title;
                break;
            case self::TYPE_VARIATION:
            default:
                // todo..
                break;
        }

        return empty($result) ? 'image' : self::getCleanFilename($result);
    }

    /**
     * @param string $filename
     * @return string
     */
    public static function getCleanFilename(string $filename): string
    {
        $filename = \mb_convert_case($filename, \MB_CASE_LOWER);
        $source   = ['.', ' ', '/', 'ä', 'ö', 'ü', 'ß'];
        $replace  = ['-', '-', '-', 'ae', 'oe', 'ue', 'ss'];
        $filename = \str_replace($source, $replace, $filename);

        return \preg_replace('/[^a-zA-Z0-9\.\-_]/', '', $filename);
    }

    /**
     * @param MediaImageRequest $req
     * @param bool              $streamOutput
     * @throws Exception
     */
    public static function render(MediaImageRequest $req, bool $streamOutput = false): void
    {
        $rawPath = $req->getRaw(true);
        if (!\is_file($rawPath)) {
            throw new Exception(\sprintf('Image "%s" does not exist', $rawPath));
        }
        $containerDim = $req->getSize();
        $maxWidth     = $containerDim->getWidth();
        $maxHeight    = $containerDim->getHeight();
        $settings     = self::getSettings();
        $background   = $req->getExt() !== 'jpeg' ? 'rgba(0,0,0,0)' : $settings['background'];
        $thumbnail    = $req->getThumb(null, true);
        $directory    = \pathinfo($thumbnail, \PATHINFO_DIRNAME);
        if (!\is_dir($directory) && !\mkdir($directory, 0777, true)) {
            $error = \error_get_last();
            if (empty($error)) {
                $error = 'Unable to create directory ' . $directory;
            }
            throw new Exception(\is_array($error) ? $error['message'] : $error);
        }
        $manager = new ImageManager(['driver' => self::getImageDriver()]);
        $img     = $manager->make($rawPath);

        // image optimizations
        $img->blur(1);
        if (self::getImageDriver() === 'imagick') {
            $img->getCore()->setColorspace(\Imagick::COLORSPACE_RGB);
            $img->getCore()->transformImageColorspace(\Imagick::COLORSPACE_RGB);
            $img->getCore()->stripImage();
        }

        if ($settings['scale'] === true || $img->getWidth() > $maxWidth || $img->getHeight() > $maxHeight) {
            $img->resize($maxWidth, $maxHeight, function (Constraint $constraint) {
                $constraint->aspectRatio();
            });
        }
        if ($settings['container'] === true) {
            $img->resizeCanvas($maxWidth, $maxHeight, 'center', false, $background);
        }
        if (isset($settings['branding']) && $req->getSize()->getType() === self::SIZE_LG) {
            $branding  = $settings['branding'];
            $watermark = $manager->make($branding->path);
            if ($branding->size > 0) {
                $brandWidth  = \round(($img->getWidth() * $branding->size) / 100.0);
                $brandHeight = \round(($brandWidth / $watermark->getWidth()) * $watermark->getHeight());
                $newWidth    = \min($watermark->getWidth(), $brandWidth);
                $newHeight   = \min($watermark->getHeight(), $brandHeight);
                $watermark->resize($newWidth, $newHeight, function (Constraint $constraint) {
                    $constraint->aspectRatio();
                });
                $watermark->opacity($branding->transparency);
                $img->insert($watermark, $branding->position, 10, 10);
            }
        }
        \executeHook(\HOOK_IMAGE_RENDER, [
            'image'    => $img,
            'settings' => $settings,
            'path'     => $thumbnail
        ]);
        if ($settings['format'] === 'jpg') {
            $img->interlace(true);
        }
        $img->save($thumbnail, $settings['quality']);

        if ($streamOutput) {
            echo $img->response($req->getExt());
        }
    }

    /**
     * @return string
     */
    public static function getImageDriver(): string
    {
        return \extension_loaded('imagick') ? 'imagick' : 'gd';
    }
}
