<?php declare(strict_types=1);

namespace JTL\REST\Models;

use DateTime;
use Exception;
use JTL\Model\DataAttribute;
use JTL\Model\DataModel;
use JTL\Model\ModelHelper;

/**
 * Class CustomerModel
 * @OA\Schema(
 *     title="Customer model",
 *     description="Customer model",
 * )
 * @package JTL\REST\Models
 * @property int      $kKunde
 * @method int getKKunde()
 * @method void setKKunde(int $value)
 * @property int      $kKundengruppe
 * @method int getKKundengruppe()
 * @method void setKKundengruppe(int $value)
 * @property int      $kSprache
 * @method int getKSprache()
 * @method void setKSprache(int $value)
 * @property string   $cKundenNr
 * @method string getCKundenNr()
 * @method void setCKundenNr(string $value)
 * @property string   $cPasswort
 * @method string getCPasswort()
 * @method void setCPasswort(string $value)
 * @property string   $cAnrede
 * @method string getCAnrede()
 * @method void setCAnrede(string $value)
 * @property string   $cTitel
 * @method string getCTitel()
 * @method void setCTitel(string $value)
 * @property string   $cVorname
 * @method string getCVorname()
 * @method void setCVorname(string $value)
 * @property string   $cNachname
 * @method string getCNachname()
 * @method void setCNachname(string $value)
 * @property string   $cFirma
 * @method string getCFirma()
 * @method void setCFirma(string $value)
 * @property string   $cZusatz
 * @method string getCZusatz()
 * @method void setCZusatz(string $value)
 * @property string   $cStrasse
 * @method string getCStrasse()
 * @method void setCStrasse(string $value)
 * @property string   $cHausnummer
 * @method string getCHausnummer()
 * @method void setCHausnummer(string $value)
 * @property string   $cAdressZusatz
 * @method string getCAdressZusatz()
 * @method void setCAdressZusatz(string $value)
 * @property string   $cPLZ
 * @method string getCPLZ()
 * @method void setCPLZ(string $value)
 * @property string   $cOrt
 * @method string getCOrt()
 * @method void setCOrt(string $value)
 * @property string   $cBundesland
 * @method string getCBundesland()
 * @method void setCBundesland(string $value)
 * @property string   $cLand
 * @method string getCLand()
 * @method void setCLand(string $value)
 * @property string   $cTel
 * @method string getCTel()
 * @method void setCTel(string $value)
 * @property string   $cMobil
 * @method string getCMobil()
 * @method void setCMobil(string $value)
 * @property string   $cFax
 * @method string getCFax()
 * @method void setCFax(string $value)
 * @property string   $cMail
 * @method string getCMail()
 * @method void setCMail(string $value)
 * @property string   $cUSTID
 * @method string getCUSTID()
 * @method void setCUSTID(string $value)
 * @property string   $cWWW
 * @method string getCWWW()
 * @method void setCWWW(string $value)
 * @property string   $cSperre
 * @method string getCSperre()
 * @method void setCSperre(string $value)
 * @property float    $fGuthaben
 * @method float getFGuthaben()
 * @method void setFGuthaben(float $value)
 * @property string   $cNewsletter
 * @method string getCNewsletter()
 * @method void setCNewsletter(string $value)
 * @property DateTime $dGeburtstag
 * @method DateTime getDGeburtstag()
 * @method void setDGeburtstag(DateTime $value)
 * @property float    $fRabatt
 * @method float getFRabatt()
 * @method void setFRabatt(float $value)
 * @property string   $cHerkunft
 * @method string getCHerkunft()
 * @method void setCHerkunft(string $value)
 * @property DateTime $dErstellt
 * @method DateTime getDErstellt()
 * @method void setDErstellt(DateTime $value)
 * @property DateTime $dVeraendert
 * @method DateTime getDVeraendert()
 * @method void setDVeraendert(DateTime $value)
 * @property string   $cAktiv
 * @method string getCAktiv()
 * @method void setCAktiv(string $value)
 * @property string   $cAbgeholt
 * @method string getCAbgeholt()
 * @method void setCAbgeholt(string $value)
 * @property int      $nRegistriert
 * @method int getNRegistriert()
 * @method void setNRegistriert(int $value)
 * @property int      $nLoginversuche
 * @method int getNLoginversuche()
 * @method void setNLoginversuche(int $value)
 */
final class CustomerModel extends DataModel
{
    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'tkunde';
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
        $this->registerGetter('dGeburtstag', static function ($value, $default) {
            return ModelHelper::fromStrToDate($value, $default);
        });
        $this->registerSetter('dGeburtstag', static function ($value) {
            return ModelHelper::fromDateToStr($value);
        });
        $this->registerGetter('dErstellt', static function ($value, $default) {
            return ModelHelper::fromStrToDate($value, $default);
        });
        $this->registerSetter('dErstellt', static function ($value) {
            return ModelHelper::fromDateToStr($value);
        });
        $this->registerGetter('dVeraendert', static function ($value, $default) {
            return ModelHelper::fromStrToDateTime($value, $default);
        });
        $this->registerSetter('dVeraendert', static function ($value) {
            return ModelHelper::fromDateTimeToStr($value);
        });
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
        $attributes                      = [];
        $attributes['id']                = DataAttribute::create('kKunde', 'int', null, false, true);
        $attributes['customerGroupID']   = DataAttribute::create('kKundengruppe', 'int', self::cast('0', 'int'), false);
        $attributes['languageID']        = DataAttribute::create('kSprache', 'int', self::cast('0', 'int'), false);
        $attributes['customerNO']        = DataAttribute::create('cKundenNr', 'varchar');
        $attributes['password']          = DataAttribute::create('cPasswort', 'varchar');
        $attributes['salutation']        = DataAttribute::create(
            'cAnrede',
            'varchar',
            self::cast('', 'varchar'),
            false
        );
        $attributes['title']             = DataAttribute::create('cTitel', 'varchar');
        $attributes['firstname']         = DataAttribute::create(
            'cVorname',
            'varchar',
            self::cast('', 'varchar'),
            false
        );
        $attributes['surname']           = DataAttribute::create(
            'cNachname',
            'varchar',
            self::cast('', 'varchar'),
            false
        );
        $attributes['company']           = DataAttribute::create('cFirma', 'varchar');
        $attributes['additional']        = DataAttribute::create('cZusatz', 'varchar');
        $attributes['street']            = DataAttribute::create(
            'cStrasse',
            'varchar',
            self::cast('', 'varchar'),
            false
        );
        $attributes['streetNO']          = DataAttribute::create('cHausnummer', 'varchar', null, false);
        $attributes['additionalAddress'] = DataAttribute::create('cAdressZusatz', 'varchar');
        $attributes['zip']               = DataAttribute::create('cPLZ', 'varchar', self::cast('', 'varchar'), false);
        $attributes['city']              = DataAttribute::create('cOrt', 'varchar', self::cast('', 'varchar'), false);
        $attributes['state']             = DataAttribute::create('
        cBundesland', 'varchar', self::cast('', 'varchar'), false);
        $attributes['country']           = DataAttribute::create('cLand', 'varchar', null, false);
        $attributes['tel']               = DataAttribute::create('cTel', 'varchar');
        $attributes['mobile']            = DataAttribute::create('cMobil', 'varchar');
        $attributes['fax']               = DataAttribute::create('cFax', 'varchar');
        $attributes['mail']              = DataAttribute::create('cMail', 'varchar', self::cast('', 'varchar'), false);
        $attributes['ustidnr']           = DataAttribute::create('cUSTID', 'varchar');
        $attributes['www']               = DataAttribute::create('cWWW', 'varchar');
        $attributes['locked']            = DataAttribute::create(
            'cSperre',
            'varchar',
            self::cast('N', 'varchar'),
            false
        );
        $attributes['balance']           = DataAttribute::create(
            'fGuthaben',
            'double',
            self::cast('0.00', 'double'),
            false
        );
        $attributes['newsletter']        = DataAttribute::create('cNewsletter', 'char', self::cast('', 'char'), false);
        $attributes['birthday']          = DataAttribute::create('dGeburtstag', 'date');
        $attributes['discount']          = DataAttribute::create(
            'fRabatt',
            'double',
            self::cast('0.00', 'double'),
            false
        );
        $attributes['origin']            = DataAttribute::create(
            'cHerkunft',
            'varchar',
            self::cast('', 'varchar'),
            false
        );
        $attributes['created']           = DataAttribute::create('dErstellt', 'date');
        $attributes['modified']          = DataAttribute::create('dVeraendert', 'datetime', null, false);
        $attributes['active']            = DataAttribute::create('cAktiv', 'char', self::cast('Y', 'char'), false);
        $attributes['fetched']           = DataAttribute::create('cAbgeholt', 'char', self::cast('N', 'char'), false);
        $attributes['registered']        = DataAttribute::create('nRegistriert', 'tinyint', null, false);
        $attributes['loginAttempts']     = DataAttribute::create(
            'nLoginversuche',
            'int',
            self::cast('0', 'int'),
            false
        );

        return $attributes;
    }
}
