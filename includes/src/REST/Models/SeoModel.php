<?php declare(strict_types=1);

namespace JTL\REST\Models;

use Exception;
use JTL\Model\DataAttribute;
use JTL\Model\DataModel;

/**
 * Class SeoModel
 * @OA\Schema(
 *     title="Tax rate model",
 *     description="Tax rate model",
 * )
 * @property string $cSeo
 * @property string $slug
 * @property string $cKey
 * @property string $type
 * @property int    $kKey
 * @property int    $id
 * @property int    $kSprache
 * @property int    $languageID
 * @method string getSlug()
 * @method string getType()
 * @method int    getId()
 * @method int    getLanguageId()
 */
final class SeoModel extends DataModel
{
    public const TYPE_MANUFACTURER = 'kHersteller';

    public const TYPE_PRODUCT = 'kArtikel';

    public const TYPE_CATEGORY = 'kKategorie';

    public const TYPE_LINK = 'kLink';

    public const TYPE_TAG = 'kTag';

    public const TYPE_SURVEY = 'kUmfrage';

    public const TYPE_SEARCH_QUERY = 'kSuchanfrage';

    public const TYPE_ATTRIBUTE_VALUE = 'kMerkmalWert';

    public const TYPE_NEWS = 'kNews';

    public const TYPE_NEWS_CATEGORY = 'kNewsKategorie';

    public const TYPE_NEWS_MONTH = 'kNewsMonatsUebersicht';

    /**
     * @OA\Property(
     *   property="slug",
     *   type="string",
     *   example="example-item",
     *   description="The item's URL slug"
     * )
     * @OA\Property(
     *   property="type",
     *   type="string",
     *   example="kArtikel",
     *   description="The item's type"
     * )
     * @OA\Property(
     *   property="id",
     *   type="int",
     *   example=1,
     *   description="The items primary key value"
     * )
     * @OA\Property(
     *   property="languageID",
     *   type="int",
     *   example=1,
     *   description="The language ID"
     * )
     */

    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'tseo';
    }

    /**
     * @inheritdoc
     */
    public function setKeyName($keyName): void
    {
        throw new Exception(__METHOD__ . ': setting of keyname is not supported', self::ERR_DATABASE);
    }

    /**
     * @inheritdoc
     */
    public function getAttributes(): array
    {
        static $attributes = null;

        if ($attributes === null) {
            $attributes               = [];
            $attributes['slug']       = DataAttribute::create('cSeo', 'varchar', null, false);
            $attributes['type']       = DataAttribute::create('cKey', 'varchar', null, false);
            $attributes['id']         = DataAttribute::create('kKey', 'int', null, false);
            $attributes['languageID'] = DataAttribute::create('kSprache', 'tinyint');
        }

        return $attributes;
    }
}