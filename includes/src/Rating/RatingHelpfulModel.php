<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Rating;

use Exception;
use JTL\Model\DataAttribute;
use JTL\Model\DataModel;

/**
 * Class RatingHelpfulModel
 * @package JTL\Rating
 * @property int $id
 * @property int $ratingID
 * @property int $customerID
 * @property int $rating
 */
final class RatingHelpfulModel extends DataModel
{
    /**
     * @return string
     * @see DataModel::getTableName()
     */
    public function getTableName(): string
    {
        return 'tbewertunghilfreich';
    }

    /**
     * Setting of keyname is not supported!!!
     * Call will always throw an Exception with code ERR_DATABASE!
     * @param string $keyName
     * @throws Exception
     * @see IDataModel::setKeyName()
     */
    public function setKeyName($keyName): void
    {
        throw new Exception(__METHOD__ . ': setting of keyname is not supported', self::ERR_DATABASE);
    }

    /**
     * @return DataAttribute[]
     * @see IDataModel::getAttributes()
     *
     */
    public function getAttributes(): array
    {
        static $attr = null;

        if ($attr === null) {
            $attr               = [];
            $attr['id']         = DataAttribute::create('kBewertungHilfreich', 'int', null, false, true);
            $attr['ratingID']   = DataAttribute::create('kBewertung', 'int', self::cast('0', 'int'), false);
            $attr['customerID'] = DataAttribute::create('kKunde', 'int', self::cast('0', 'int'), false);
            $attr['rating']     = DataAttribute::create('nBewertung', 'int', null, false);
        }

        return $attr;
    }
}
