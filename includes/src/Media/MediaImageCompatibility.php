<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Media;

use JTL\DB\ReturnType;
use JTL\Shop;

/**
 * Class MediaImageCompatibility
 * @package JTL\Media
 */
class MediaImageCompatibility implements IMedia
{
    public const REGEX = '/^bilder\/produkte\/(?P<size>mini|klein|normal|gross)' .
    '\/(?P<path>(?P<name>[a-zA-Z0-9\-_]+)\.(?P<ext>jpg|jpeg|png|gif))$/';

    /**
     * @param string $request
     * @return bool
     */
    public function isValid(string $request): bool
    {
        return \in_array(\IMAGE_COMPATIBILITY_LEVEL, [1, 2], true) && $this->parse($request) !== null;
    }

    /**
     * @param string $request
     * @return mixed
     */
    public function handle(string $request)
    {
        $req      = $this->parse($request);
        $path     = \mb_convert_case($req['path'], \MB_CASE_LOWER);
        $fallback = Shop::Container()->getDB()->executeQueryPrepared(
            'SELECT h.kArtikel, h.nNr, a.cSeo, a.cName, a.cArtNr, a.cBarcode 
                FROM tartikelpicthistory h 
                INNER JOIN tartikel a 
                  ON h.kArtikel = a.kArtikel 
                  WHERE LOWER(h.cPfad) = :path',
            ['path' => $path],
            ReturnType::SINGLE_OBJECT
        );

        if (\is_object($fallback)) {
            $req['number'] = (int)$fallback->nNr;
        } elseif (\IMAGE_COMPATIBILITY_LEVEL === 2) {
            $name = $req['name'];
            // remove number
            if (\preg_match('/^(.*)_b?(\d+)$/', $name, $matches)) {
                $name          = $matches[1];
                $req['number'] = (int)$matches[2];
            }

            $productNumber = $barcode = $seo = $name;
            // remove concat
            $exploded = \explode('_', $name, 2);
            if (\count($exploded) === 2) {
                $productNumber = $exploded[0];
                $barcode       = $seo = $name = $exploded[1];
            }
            // replace vowel mutation
            $name          = \str_replace('-', ' ', $this->replaceVowelMutation($name));
            $productNumber = $this->replaceVowelMutation($productNumber);
            $barcode       = $this->replaceVowelMutation($barcode);

            $fallback = Shop::Container()->getDB()->queryPrepared(
                'SELECT a.kArtikel, a.cSeo, a.cName, a.cArtNr, a.cBarcode 
                    FROM tartikel a 
                    WHERE LOWER(a.cName) = :nm 
                        OR LOWER(a.cSeo) = :seo 
                        OR LOWER(a.cBarcode) = :bar 
                        OR LOWER(a.cArtNr) = :artno',
                [
                    'nm'    => \mb_convert_case(Shop::Container()->getDB()->escape($name), \MB_CASE_LOWER),
                    'seo'   => \mb_convert_case(Shop::Container()->getDB()->escape($seo), \MB_CASE_LOWER),
                    'bar'   => \mb_convert_case(Shop::Container()->getDB()->escape($barcode), \MB_CASE_LOWER),
                    'artno' => \mb_convert_case(Shop::Container()->getDB()->escape($productNumber), \MB_CASE_LOWER)
                ],
                ReturnType::SINGLE_OBJECT
            );
        }

        if (\is_object($fallback) && (int)$fallback->kArtikel > 0) {
            $number   = $req['number'] ?? 1;
            $thumbUrl = Shop::getImageBaseURL() .
                MediaImage::getThumb(
                    Image::TYPE_PRODUCT,
                    $fallback->kArtikel,
                    $fallback,
                    Image::mapSize($req['size']),
                    $number
                );

            \http_response_code(301);
            \header('Location: ' . $thumbUrl);
            exit;
        }

        return false;
    }

    /**
     * @param string $str
     * @return string
     */
    private function replaceVowelMutation($str): string
    {
        $src = ['ä', 'ö', 'ü', 'ß', 'Ä', 'Ö', 'Ü'];
        $rpl = ['ae', 'oe', 'ue', 'ss', 'AE', 'OE', 'UE'];

        return \str_replace($rpl, $src, $str);
    }

    /**
     * @param string $request
     * @return array|null
     */
    private function parse($request): ?array
    {
        if (!\is_string($request) || $request === '') {
            return null;
        }

        if (\mb_strpos($request, '/') === 0) {
            $request = \mb_substr($request, 1);
        }

        return \preg_match(self::REGEX, $request, $matches)
            ? \array_intersect_key($matches, \array_flip(\array_filter(\array_keys($matches), '\is_string')))
            : null;
    }
}
