<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Extensions;

/**
 * Class Konfiggruppesprache
 *
 * @package Extensions
 */
class Konfiggruppesprache implements \JsonSerializable
{
    /**
     * @var int
     */
    protected $kKonfiggruppe;

    /**
     * @var int
     */
    protected $kSprache;

    /**
     * @var string
     */
    protected $cName;

    /**
     * @var string
     */
    protected $cBeschreibung;

    /**
     * Constructor
     *
     * @param int $kKonfiggruppe
     * @param int $kSprache
     */
    public function __construct(int $kKonfiggruppe = 0, int $kSprache = 0)
    {
        if ($kKonfiggruppe > 0 && $kSprache > 0) {
            $this->loadFromDB($kKonfiggruppe, $kSprache);
        }
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @return array|object|string
     */
    public function jsonSerialize()
    {
        return \StringHandler::utf8_convert_recursive([
            'cName'         => $this->cName,
            'cBeschreibung' => $this->cBeschreibung
        ]);
    }

    /**
     * Loads database member into class member
     *
     * @param int $kKonfiggruppe primarykey
     * @param int $kSprache primarykey
     */
    private function loadFromDB(int $kKonfiggruppe = 0, int $kSprache = 0): void
    {
        $item = \Shop::Container()->getDB()->select(
            'tkonfiggruppesprache',
            'kKonfiggruppe',
            $kKonfiggruppe,
            'kSprache',
            $kSprache
        );
        if (isset($item->kKonfiggruppe, $item->kSprache) && $item->kKonfiggruppe > 0 && $item->kSprache > 0) {
            foreach (\array_keys(\get_object_vars($item)) as $member) {
                $this->$member = $item->$member;
            }
            $this->kSprache      = (int)$this->kSprache;
            $this->kKonfiggruppe = (int)$this->kKonfiggruppe;
        }
    }

    /**
     * @param bool $bPrim
     * @return bool|int
     */
    public function save(bool $bPrim = true)
    {
        $ins = new \stdClass();
        foreach (\array_keys(\get_object_vars($this)) as $member) {
            $ins->$member = $this->$member;
        }
        unset($ins->kKonfiggruppe, $ins->kSprache);

        $kPrim = \Shop::Container()->getDB()->insert('tkonfiggruppesprache', $ins);

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
        $upd                = new \stdClass();
        $upd->kSprache      = $this->getSprache();
        $upd->cName         = $this->getName();
        $upd->cBeschreibung = $this->getBeschreibung();

        return \Shop::Container()->getDB()->update(
            'tkonfiggruppesprache',
            ['kKonfiggruppe', 'kSprache'],
            [$this->getKonfiggruppe(), $this->getSprache()],
            $upd
        );
    }

    /**
     * @return int
     */
    public function delete(): int
    {
        return \Shop::Container()->getDB()->delete(
            'tkonfiggruppesprache',
            ['kKonfiggruppe', 'kSprache'],
            [(int)$this->kKonfiggruppe, (int)$this->kSprache]
        );
    }

    /**
     * @param int $kKonfiggruppe
     * @return $this
     */
    public function setKonfiggruppe(int $kKonfiggruppe): self
    {
        $this->kKonfiggruppe = $kKonfiggruppe;

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
    public function setName($cName): self
    {
        $this->cName = \Shop::Container()->getDB()->escape($cName);

        return $this;
    }

    /**
     * @param string $cBeschreibung
     * @return $this
     */
    public function setBeschreibung($cBeschreibung): self
    {
        $this->cBeschreibung = \Shop::Container()->getDB()->escape($cBeschreibung);

        return $this;
    }

    /**
     * @return int
     */
    public function getKonfiggruppe(): int
    {
        return (int)$this->kKonfiggruppe;
    }

    /**
     * @return int
     */
    public function getSprache(): int
    {
        return (int)$this->kSprache;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->cName;
    }

    /**
     * @return string|null
     */
    public function getBeschreibung(): ?string
    {
        return $this->cBeschreibung;
    }

    /**
     * @return bool
     */
    public function hatBeschreibung(): bool
    {
        return \strlen($this->cBeschreibung) > 0;
    }
}
