<?php declare(strict_types=1);

namespace JTL\REST\Models;

use DateTime;
use Exception;
use JTL\Model\DataAttribute;
use JTL\Model\DataModel;
use JTL\Model\ModelHelper;

/**
 * Class ProductPropertyLocalizationModel
 * @OA\Schema(
 *     title="Product property localization model",
 *     description="Product property localization model",
 * )
 *
 * @package JTL\REST\Models
 * @property int $kEigenschaft
 * @method int getKEigenschaft()
 * @method void setKEigenschaft(int $value)
 * @property int $kSprache
 * @method int getKSprache()
 * @method void setKSprache(int $value)
 * @property string $cName
 * @method string getCName()
 * @method void setCName(string $value)
 */
final class ProductPropertyLocalizationModel extends DataModel
{
    /**
     * @OA\Property(
     *   property="propertyID",
     *   type="int",
     *   example=1,
     *   description="The property ID"
     * )
     * @OA\Property(
     *   property="languageID",
     *   type="int",
     *   example=1,
     *   description="The language ID"
     * )
     * @OA\Property(
     *   property="name",
     *   type="string",
     *   example="Example",
     *   description="The name"
     * )
     */

    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'teigenschaftsprache';
    }

    /**
     * Setting of keyname is not supported!
     * Call will always throw an Exception with code ERR_DATABASE!
     * @inheritdoc
     */
    public function setKeyName($keyName): void
    {
        throw new Exception(__METHOD__ . ': setting of keyname is not supported', self::ERR_DATABASE);
    }

    /**
     * @inheritdoc
     */
    protected function onRegisterHandlers(): void
    {
        parent::onRegisterHandlers();
    }

    /**
    * @inheritdoc
    */
    public function getAttributes(): array
    {
        static $attributes = null;
        if ($attributes !== null) {
            return $attributes;
        }
        $attributes               = [];
        $attributes['propertyID'] = DataAttribute::create('kEigenschaft', 'int', self::cast('0', 'int'), false, true);
        $attributes['languageID'] = DataAttribute::create('kSprache', 'int', self::cast('0', 'int'), false, true);
        $attributes['name']       = DataAttribute::create('cName', 'varchar');

        return $attributes;
    }
}
