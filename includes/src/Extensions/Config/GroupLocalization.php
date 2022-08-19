<?php declare(strict_types=1);

namespace JTL\Extensions\Config;

use JsonSerializable;
use JTL\Helpers\Text;
use JTL\Shop;

/**
 * Class GroupLocalization
 * @package JTL\Extensions\Config
 */
class GroupLocalization implements JsonSerializable
{
    /**
     * @var int|null
     */
    protected ?int $kKonfiggruppe = null;

    /**
     * @var int|null
     */
    protected ?int $kSprache = null;

    /**
     * @var string|null
     */
    protected ?string $cName = null;

    /**
     * @var string|null
     */
    protected ?string $cBeschreibung = null;

    /**
     * GroupLocalization constructor.
     * @param int $groupID
     * @param int $languageID
     */
    public function __construct(int $groupID = 0, int $languageID = 0)
    {
        if ($groupID > 0 && $languageID > 0) {
            $this->loadFromDB($groupID, $languageID);
        }
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return Text::utf8_convert_recursive([
            'cName'         => $this->cName,
            'cBeschreibung' => $this->cBeschreibung
        ]);
    }

    /**
     * Loads database member into class member
     *
     * @param int $groupID primarykey
     * @param int $languageID primarykey
     */
    private function loadFromDB(int $groupID = 0, int $languageID = 0): void
    {
        $item = Shop::Container()->getDB()->select(
            'tkonfiggruppesprache',
            'kKonfiggruppe',
            $groupID,
            'kSprache',
            $languageID
        );
        if ($item !== null && $item->kKonfiggruppe > 0 && $item->kSprache > 0) {
            $this->kSprache      = (int)$item->kSprache;
            $this->kKonfiggruppe = (int)$item->kKonfiggruppe;
            $this->cName         = $item->cName;
            $this->cBeschreibung = $item->cBeschreibung;
        }
    }

    /**
     * @param int $groupID
     * @return $this
     */
    public function setKonfiggruppe(int $groupID): self
    {
        $this->kKonfiggruppe = $groupID;

        return $this;
    }

    /**
     * @param int $languageID
     * @return $this
     */
    public function setSprache(int $languageID): self
    {
        $this->kSprache = $languageID;

        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->cName = $name;

        return $this;
    }

    /**
     * @param string $description
     * @return $this
     */
    public function setBeschreibung(string $description): self
    {
        $this->cBeschreibung = $description;

        return $this;
    }

    /**
     * @return int
     */
    public function getKonfiggruppe(): int
    {
        return $this->kKonfiggruppe ?? 0;
    }

    /**
     * @return int
     */
    public function getSprache(): int
    {
        return $this->kSprache ?? 0;
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
        return \mb_strlen($this->cBeschreibung ?? '') > 0;
    }
}
