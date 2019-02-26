<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Extensions;

use JTL\Nice;
use JTL\Shop;
use JTL\Sprache;
use stdClass;

/**
 * Class Konfigitemsprache
 * @package JTL\Extensions
 */
class Konfigitemsprache
{
    /**
     * @var int
     */
    protected $kKonfigitem;

    /**
     * @var int
     */
    protected $kSprache;

    /**
     * @var string
     */
    protected $cName = '';

    /**
     * @var string
     */
    protected $cBeschreibung = '';

    /**
     * Konfigitemsprache constructor.
     * @param int $kKonfigitem
     * @param int $kSprache
     */
    public function __construct(int $kKonfigitem = 0, int $kSprache = 0)
    {
        if ($kKonfigitem > 0 && $kSprache > 0) {
            $this->loadFromDB($kKonfigitem, $kSprache);
        }
    }

    /**
     * @return bool
     */
    public static function checkLicense(): bool
    {
        return Nice::getInstance()->checkErweiterung(\SHOP_ERWEITERUNG_KONFIGURATOR);
    }

    /**
     * Loads database member into class member
     *
     * @param int $kKonfigitem
     * @param int $kSprache
     */
    private function loadFromDB(int $kKonfigitem = 0, int $kSprache = 0): void
    {
        if (!self::checkLicense()) {
            return;
        }
        $item            = Shop::Container()->getDB()->select(
            'tkonfigitemsprache',
            'kKonfigitem',
            $kKonfigitem,
            'kSprache',
            $kSprache
        );
        $defaultLanguage = Sprache::getDefaultLanguage();
        if ($item !== null && empty($item->cName)) {
            $localized   = Shop::Container()->getDB()->select(
                'tkonfigitemsprache',
                'kKonfigitem',
                $kKonfigitem,
                'kSprache',
                (int)$defaultLanguage->kSprache,
                null,
                null,
                false,
                'cName'
            );
            $item->cName = $localized->cName;
        }
        if ($item !== null && empty($item->cBeschreibung)) {
            $localized           = Shop::Container()->getDB()->select(
                'tkonfigitemsprache',
                'kKonfigitem',
                $kKonfigitem,
                'kSprache',
                (int)$defaultLanguage->kSprache,
                null,
                null,
                false,
                'cBeschreibung'
            );
            $item->cBeschreibung = $localized->cBeschreibung;
        }

        if (isset($item->kKonfigitem, $item->kSprache) && $item->kKonfigitem > 0 && $item->kSprache > 0) {
            foreach (\array_keys(\get_object_vars($item)) as $member) {
                $this->$member = $item->$member;
            }
        }
    }

    /**
     * @param bool $bPrim
     * @return bool|int
     */
    public function save(bool $bPrim = true)
    {
        $ins = new stdClass();
        foreach (\array_keys(\get_object_vars($this)) as $member) {
            $ins->$member = $this->$member;
        }
        unset($ins->kKonfigitem, $ins->kSprache);

        $kPrim = Shop::Container()->getDB()->insert('tkonfigitemsprache', $ins);

        if ($kPrim > 0) {
            return $bPrim ? $kPrim : true;
        }

        return false;
    }

    /**
     * @return int
     */
    public function update(): int
    {
        $upd                = new stdClass();
        $upd->cName         = $this->getName();
        $upd->cBeschreibung = $this->getBeschreibung();

        return Shop::Container()->getDB()->update(
            'tkonfigitemsprache',
            ['kKonfigitem', 'kSprache'],
            [$this->getKonfigitem(), $this->getSprache()],
            $upd
        );
    }

    /**
     * @return int
     */
    public function delete(): int
    {
        return Shop::Container()->getDB()->delete(
            'tkonfigitemsprache',
            ['kKonfigitem', 'kSprache'],
            [(int)$this->kKonfigitem, (int)$this->kSprache]
        );
    }

    /**
     * @param int $kKonfigitem
     * @return $this
     */
    public function setKonfigitem(int $kKonfigitem): self
    {
        $this->kKonfigitem = $kKonfigitem;

        return $this;
    }

    /**
     * @param int $kSprache
     * @return $this
     */
    public function setSprache(int $kSprache): self
    {
        $this->kSprache = $kSprache;

        return $this;
    }

    /**
     * @param string $cName
     * @return $this
     */
    public function setName(string $cName): self
    {
        $this->cName = Shop::Container()->getDB()->escape($cName);

        return $this;
    }

    /**
     * @param string $cBeschreibung
     * @return $this
     */
    public function setBeschreibung(string $cBeschreibung): self
    {
        $this->cBeschreibung = Shop::Container()->getDB()->escape($cBeschreibung);

        return $this;
    }

    /**
     * @return int
     */
    public function getKonfigitem(): int
    {
        return (int)$this->kKonfigitem;
    }

    /**
     * @return int
     */
    public function getSprache(): int
    {
        return (int)$this->kSprache;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->cName;
    }

    /**
     * @return string
     */
    public function getBeschreibung(): string
    {
        return $this->cBeschreibung;
    }
}
