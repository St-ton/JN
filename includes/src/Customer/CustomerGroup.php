<?php declare(strict_types=1);

namespace JTL\Customer;

use InvalidArgumentException;
use JTL\MagicCompatibilityTrait;
use JTL\Session\Frontend;
use JTL\Shop;
use stdClass;

/**
 * Class CustomerGroup
 * @package JTL\Customer
 */
class CustomerGroup
{
    use MagicCompatibilityTrait;

    /**
     * @var int
     */
    protected int $id = 0;

    /**
     * @var string
     */
    protected string $name;

    /**
     * @var float
     */
    protected float $discount = 0.0;

    /**
     * @var string
     */
    protected string $default;

    /**
     * @var string
     */
    protected string $cShopLogin;

    /**
     * @var int
     */
    protected int $isMerchant = 0;

    /**
     * @var int
     */
    protected int $mayViewPrices = 1;

    /**
     * @var int
     */
    protected int $mayViewCategories = 1;

    /**
     * @var int
     */
    protected int $languageID = 0;

    /**
     * @var array|null
     */
    protected ?array $Attribute = null;

    /**
     * @var string|null
     */
    private ?string $nameLocalized = null;

    /**
     * @var array
     */
    protected static array $mapping = [
        'kKundengruppe'              => 'ID',
        'kSprache'                   => 'LanguageID',
        'nNettoPreise'               => 'IsMerchant',
        'darfPreiseSehen'            => 'MayViewPrices',
        'darfArtikelKategorienSehen' => 'MayViewCategories',
        'cName'                      => 'Name',
        'cStandard'                  => 'Default',
        'fRabatt'                    => 'Discount',
        'cNameLocalized'             => 'nameLocalized'
    ];

    /**
     * CustomerGroup constructor.
     * @param int $id
     */
    public function __construct(int $id = 0)
    {
        if ($id > 0) {
            $this->loadFromDB($id);
        }
    }

    /**
     * @return $this
     */
    public function loadDefaultGroup(): self
    {
        $item = Shop::Container()->getDB()->select('tkundengruppe', 'cStandard', 'Y');
        if ($item !== null) {
            $conf = Shop::getSettingValue(\CONF_GLOBAL, 'global_sichtbarkeit');
            $this->setID((int)$item->kKundengruppe)
                 ->setName($item->cName)
                 ->setDiscount($item->fRabatt)
                 ->setDefault($item->cStandard)
                 ->setShopLogin($item->cShopLogin)
                 ->setIsMerchant((int)$item->nNettoPreise);
            if ($this->isDefault()) {
                if ((int)$conf === 2) {
                    $this->mayViewPrices = 0;
                } elseif ((int)$conf === 3) {
                    $this->mayViewPrices     = 0;
                    $this->mayViewCategories = 0;
                }
            }
            $this->localize()->initAttributes();
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function localize(): self
    {
        if ($this->id > 0 && $this->languageID > 0) {
            $localized = Shop::Container()->getDB()->select(
                'tkundengruppensprache',
                'kKundengruppe',
                (int)$this->id,
                'kSprache',
                (int)$this->languageID
            );
            if (isset($localized->cName)) {
                $this->nameLocalized = $localized->cName;
            }
        }

        return $this;
    }

    /**
     * @param int $id
     * @return $this
     * @throws InvalidArgumentException
     */
    private function loadFromDB(int $id = 0): self
    {
        $item = Shop::Container()->getDB()->select('tkundengruppe', 'kKundengruppe', $id);
        if ($item === null) {
            throw new InvalidArgumentException('Cannot load customer group with id ' . $id);
        }
        $this->setID((int)$item->kKundengruppe)
             ->setName($item->cName)
             ->setDiscount($item->fRabatt)
             ->setDefault($item->cStandard)
             ->setShopLogin($item->cShopLogin)
             ->setIsMerchant((int)$item->nNettoPreise);

        return $this;
    }

    /**
     * @param bool $primary
     * @return bool|int
     */
    public function save(bool $primary = true)
    {
        $ins               = new stdClass();
        $ins->cName        = $this->name;
        $ins->fRabatt      = $this->discount;
        $ins->cStandard    = \mb_convert_case($this->default, \MB_CASE_UPPER);
        $ins->cShopLogin   = $this->cShopLogin;
        $ins->nNettoPreise = (int)$this->isMerchant;
        $kPrim             = Shop::Container()->getDB()->insert('tkundengruppe', $ins);
        if ($kPrim > 0) {
            return $primary ? $kPrim : true;
        }

        return false;
    }

    /**
     * @return int
     */
    public function update(): int
    {
        $upd               = new stdClass();
        $upd->cName        = $this->name;
        $upd->fRabatt      = $this->discount;
        $upd->cStandard    = $this->default;
        $upd->cShopLogin   = $this->cShopLogin;
        $upd->nNettoPreise = $this->isMerchant;

        return Shop::Container()->getDB()->update('tkundengruppe', 'kKundengruppe', (int)$this->id, $upd);
    }

    /**
     * @return int
     */
    public function delete(): int
    {
        return Shop::Container()->getDB()->delete('tkundengruppe', 'kKundengruppe', (int)$this->id);
    }

    /**
     * @return int
     */
    public function getID(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setID(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param float $discount
     * @return $this
     */
    public function setDiscount($discount): self
    {
        $this->discount = (float)$discount;

        return $this;
    }

    /**
     * @return float
     */
    public function getDiscount(): float
    {
        return $this->discount;
    }

    /**
     * @param string $default
     * @return $this
     */
    public function setDefault($default): self
    {
        $this->default = $default;

        return $this;
    }

    /**
     * @param string $cShopLogin
     * @return $this
     */
    public function setShopLogin($cShopLogin): self
    {
        $this->cShopLogin = $cShopLogin;

        return $this;
    }

    /**
     * @param int $nNettoPreise
     * @return $this
     */
    public function setNettoPreise($nNettoPreise): self
    {
        \trigger_error(__METHOD__ . ' is deprecated - use setIsMerchant() instead', \E_USER_DEPRECATED);

        return $this->setIsMerchant($nNettoPreise);
    }

    /**
     * @param int $is
     * @return $this
     */
    public function setIsMerchant(int $is): self
    {
        $this->isMerchant = $is;

        return $this;
    }

    /**
     * @param int $n
     * @return $this
     */
    public function setMayViewPrices(int $n): self
    {
        $this->mayViewPrices = $n;

        return $this;
    }

    /**
     * @return bool
     */
    public function mayViewPrices(): bool
    {
        return (int)$this->mayViewPrices === 1;
    }

    /**
     * @return int
     */
    public function getMayViewPrices(): int
    {
        return $this->mayViewPrices;
    }

    /**
     * @param int $n
     * @return $this
     */
    public function setMayViewCategories(int $n): self
    {
        $this->mayViewCategories = $n;

        return $this;
    }

    /**
     * @return int
     */
    public function getMayViewCategories(): int
    {
        return $this->mayViewCategories;
    }

    /**
     * @return bool
     */
    public function mayViewCategories(): bool
    {
        return (int)$this->mayViewCategories === 1;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getStandard(): ?string
    {
        \trigger_error(__METHOD__ . ' is deprecated - use getDefault() instead', \E_USER_DEPRECATED);

        return $this->getIsDefault();
    }

    /**
     * @return string|null
     */
    public function getIsDefault(): ?string
    {
        return $this->default;
    }

    /**
     * @return bool
     */
    public function isDefault(): bool
    {
        return $this->default === 'Y';
    }

    /**
     * @return string|null
     */
    public function getShopLogin(): ?string
    {
        return $this->cShopLogin;
    }

    /**
     * @return int
     */
    public function getIsMerchant(): int
    {
        return $this->isMerchant;
    }

    /**
     * @return bool
     */
    public function isMerchant(): bool
    {
        return $this->isMerchant > 0;
    }

    /**
     * @return int
     */
    public function getNettoPreise(): int
    {
        \trigger_error(__METHOD__ . ' is deprecated - use getIsMerchant() instead', \E_USER_DEPRECATED);

        return $this->getIsMerchant();
    }

    /**
     * Static helper
     *
     * @return CustomerGroup[]
     */
    public static function getGroups(): array
    {
        return Shop::Container()->getDB()->getCollection(
            'SELECT kKundengruppe AS id
                FROM tkundengruppe
                WHERE kKundengruppe > 0'
        )->map(static function ($e): self {
            return new self((int)$e->id);
        })->toArray();
    }

    /**
     * @return stdClass|false
     */
    public static function getDefault()
    {
        return Shop::Container()->getDB()->select('tkundengruppe', 'cStandard', 'Y');
    }

    /**
     * @return int
     */
    public function getLanguageID(): int
    {
        return $this->languageID;
    }

    /**
     * @param int $languageID
     * @return $this
     */
    public function setLanguageID($languageID): self
    {
        $this->languageID = (int)$languageID;

        return $this;
    }

    /**
     * @return int
     */
    public static function getCurrent(): int
    {
        $id = 0;
        if (isset($_SESSION['Kundengruppe']->kKundengruppe)) {
            $id = $_SESSION['Kundengruppe']->getID();
        } elseif (isset($_SESSION['Kunde']->kKundengruppe)) {
            $id = $_SESSION['Kunde']->kKundengruppe;
        }

        return (int)$id;
    }

    /**
     * @return int
     */
    public static function getDefaultGroupID(): int
    {
        if (isset($_SESSION['Kundengruppe'])
            && $_SESSION['Kundengruppe'] instanceof self
            && $_SESSION['Kundengruppe']->getID() > 0
        ) {
            return $_SESSION['Kundengruppe']->getID();
        }
        $customerGroup = self::getDefault();
        if (isset($customerGroup->kKundengruppe) && $customerGroup->kKundengruppe > 0) {
            return (int)$customerGroup->kKundengruppe;
        }

        return 0;
    }

    /**
     * @param int $id
     * @return CustomerGroup|stdClass
     */
    public static function reset(int $id)
    {
        if (isset($_SESSION['Kundengruppe'])
            && $_SESSION['Kundengruppe'] instanceof self
            && $_SESSION['Kundengruppe']->getID() === $id
        ) {
            return $_SESSION['Kundengruppe'];
        }
        $item = new stdClass();
        if (!$id) {
            $id = self::getDefaultGroupID();
        }
        if ($id > 0) {
            $item = new self($id);
            if ($item->getID() > 0 && !isset($_SESSION['Kundengruppe'])) {
                $item->setMayViewPrices(1)->setMayViewCategories(1);
                $conf = Shop::getSettingValue(\CONF_GLOBAL, 'global_sichtbarkeit');
                if ((int)$conf === 2) {
                    $item->setMayViewPrices(0);
                }
                if ((int)$conf === 3) {
                    $item->setMayViewPrices(0)->setMayViewCategories(0);
                }
                $_SESSION['Kundengruppe'] = $item->initAttributes();
            }
        }

        return $item;
    }

    /**
     * @param int $id
     * @return CustomerGroup
     */
    public static function getByID(int $id): self
    {
        $current = Frontend::getCustomerGroup();
        if ($current->getID() === $id) {
            return $current;
        }

        return new self($id);
    }

    /**
     * @return $this
     */
    public function initAttributes(): self
    {
        if ($this->id > 0) {
            $this->Attribute = [];
            $attributes      = Shop::Container()->getDB()->selectAll(
                'tkundengruppenattribut',
                'kKundengruppe',
                (int)$this->id
            );
            foreach ($attributes as $attribute) {
                $this->Attribute[\mb_convert_case($attribute->cName, \MB_CASE_LOWER)] = $attribute->cWert;
            }
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function hasAttributes(): bool
    {
        return $this->Attribute !== null;
    }

    /**
     * @param string $attributeName
     * @return mixed|null
     */
    public function getAttribute(string $attributeName)
    {
        return $this->Attribute[$attributeName] ?? null;
    }

    /**
     * @param int $id
     * @return null|string
     */
    public static function getNameByID(int $id): ?string
    {
        try {
            $cgroup = new self();
            $cgroup->loadFromDB($id);

            return $cgroup->getName();
        } catch (\Exception) {
            return null;
        }
    }
}
