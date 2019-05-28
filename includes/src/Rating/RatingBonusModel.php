<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Rating;

use DateTime;
use Exception;
use JTL\Model\DataAttribute;
use JTL\Model\DataModel;

/**
 * Class RatingBonusModel
 *
 * @property int      $id
 * @property int      $ratingID
 * @property int      $customerID
 * @property float    $bonus
 * @property DateTime $date
 */
final class RatingBonusModel extends DataModel
{
    /**
     * @return string
     * @see DataModel::getTableName()
     */
    public function getTableName(): string
    {
        return 'tbewertungguthabenbonus';
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
     */
    public function getAttributes(): array
    {
        static $attr = null;

        if ($attr === null) {
            $attr               = [];
            $attr['id']         = DataAttribute::create('kBewertungGuthabenBonus', 'int', null, false, true);
            $attr['ratingID']   = DataAttribute::create('kBewertung', 'int', null, false);
            $attr['customerID'] = DataAttribute::create('kKunde', 'int', null, false);
            $attr['bonus']      = DataAttribute::create('fGuthabenBonus', 'double', null, false);
            $attr['date']       = DataAttribute::create('dDatum', 'datetime', null, false);
        }

        return $attr;
    }
}
