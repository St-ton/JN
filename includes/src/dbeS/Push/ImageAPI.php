<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\dbeS\Push;

use JTL\DB\ReturnType;
use JTL\Helpers\Request;
use JTL\Media\Image;
use JTL\Media\Image\Product;
use JTL\Shop;
use stdClass;

/**
 * Class ImageAPI
 * @package JTL\dbeS\Push
 */
final class ImageAPI extends AbstractPush
{
    /**
     * @inheritdoc
     */
    public function getData()
    {
        $productID = Request::verifyGPCDataInt('a');
        $imageNo   = Request::verifyGPCDataInt('n');
        $url       = Request::verifyGPCDataInt('url');
        $size      = Request::verifyGPCDataInt('s');
        if ($productID <= 0 || $imageNo <= 0 || $size <= 0) {
            return;
        }
        $cgroup   = $this->db->select('tkundengruppe', 'cStandard', 'Y');
        $cgroupID = (int)($cgroup->kKundengruppe ?? 0);
        if ($cgroupID === 0) {
            return;
        }
        foreach ($this->getProductImages($productID, $imageNo, $cgroupID) as $productImage) {
            $image = Product::getThumb(
                Image::TYPE_PRODUCT,
                (int)$productImage->kArtikel,
                $productImage,
                $this->getSizePath($size),
                (int)$productImage->nNr
            );
            if (!\file_exists($image)) {
                Product::cacheImage(Product::toRequest($image));
            }
            if ($url === 1) {
                echo Shop::getURL() . '/' . $image . "<br/>\n";
            } else {
                $this->displayImage(\PFAD_ROOT . $image);
            }
        }
    }

    /**
     * @param string $imagePath
     */
    private function displayImage(string $imagePath): void
    {
        $mime = $this->getMimeType($imagePath);
        if ($mime !== null) {
            \header('Content-type: ' . $mime);
            \readfile($imagePath);
        }
    }

    /**
     * @param int $productID
     * @param int $imageNo
     * @param int $cgroupID
     * @return stdClass[]
     */
    private function getProductImages(int $productID, int $imageNo, int $cgroupID): array
    {
        $noQry = $productID === $imageNo
            ? ''
            : ' AND tartikelpict.nNr = ' . $imageNo;

        return $this->db->queryPrepared(
            'SELECT tartikelpict.cPfad, tartikelpict.kArtikel, tartikel.cSeo, tartikelpict.nNr
                FROM tartikelpict
                JOIN tartikel
                    ON tartikel.kArtikel = tartikelpict.kArtikel
                LEFT JOIN tartikelsichtbarkeit
                    ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                    AND tartikelsichtbarkeit.kKundengruppe = :cgid
                WHERE tartikelsichtbarkeit.kArtikel IS NULL
                    AND tartikel.kArtikel = :pid' . $noQry,
            ['cgid' => $cgroupID, 'pid' => $productID],
            ReturnType::ARRAY_OF_OBJECTS
        );
    }

    /**
     * @param string $imagePath
     * @return string|null
     */
    private function getMimeType($imagePath): ?string
    {
        $data = \getimagesize($imagePath);

        return $data['mime'] ?? null;
    }

    /**
     * @param int $size
     * @return string|null
     */
    private function getSizePath(int $size): ?string
    {
        switch ($size) {
            case 1:
                $res = Image::SIZE_LG;
                break;
            case 2:
                $res = Image::SIZE_MD;
                break;
            case 3:
                $res = Image::SIZE_SM;
                break;
            case 4:
                $res = Image::SIZE_XS;
                break;
            default:
                $res = null;
                break;
        }

        return $res;
    }
}
