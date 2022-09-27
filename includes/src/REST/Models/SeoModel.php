<?php declare(strict_types=1);

namespace JTL\REST\Models;

use Exception;
use JTL\Model\DataAttribute;
use JTL\Model\DataModel;

/**
 * Class SeoModel
 *
 * @property string $cSeo
 * @property string $slug
 * @property string $cKey
 * @property string $type
 * @property int    $kKey
 * @property int    $id
 * @property int    $kSprache
 * @property int    $langID
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

    public function getKeyName(bool $realName = false): string
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function getAttributes(): array
    {
        static $attributes = null;

        if ($attributes === null) {
            $attributes           = [];
            $attributes['slug']   = DataAttribute::create('cSeo', 'varchar', null, false);
            $attributes['type']   = DataAttribute::create('cKey', 'varchar', null, false);
            $attributes['id']     = DataAttribute::create('kKey', 'int', null, false);
            $attributes['langID'] = DataAttribute::create('kSprache', 'tinyint');
        }

        return $attributes;
    }
}
