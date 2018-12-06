<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Link;

use DB\DbInterface;
use DB\ReturnType;
use Tightenco\Collect\Support\Collection;
use function Functional\flatten;
use function Functional\map;

/**
 * Class LinkGroup
 * @package Link
 */
final class LinkGroup implements LinkGroupInterface
{
    use \MagicCompatibilityTrait;

    /**
     * @var array
     */
    public static $mapping = [
        'cLocalizedName' => 'Name',
        'Links'          => 'Links'
    ];

    /**
     * @var array
     */
    private $names = [];

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $template;

    /**
     * @var bool
     */
    private $isSpecial = true;

    /**
     * @var array
     */
    private $languageID = [];

    /**
     * @var array
     */
    private $languageCode = [];

    /**
     * @var Collection
     */
    private $links;

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * LinkGroup constructor.
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
        $this->db    = $db;
        $this->links = new Collection();
    }

    /**
     * @inheritdoc
     */
    public function load(int $id): LinkGroupInterface
    {
        $this->id       = $id;
        $groupLanguages = $this->db->queryPrepared(
            'SELECT tlinkgruppesprache.*, tlinkgruppe.cTemplatename AS template, tsprache.kSprache 
                FROM tlinkgruppe
                JOIN tlinkgruppesprache
					ON tlinkgruppe.kLinkgruppe = tlinkgruppesprache.kLinkgruppe
                JOIN tsprache 
                    ON tsprache.cISO = tlinkgruppesprache.cISOSprache
                WHERE tlinkgruppe.kLinkgruppe = :lgid',
            ['lgid' => $this->id],
            ReturnType::ARRAY_OF_OBJECTS
        );
        if (\count($groupLanguages) === 0) {
            return $this;
        }

        return $this->map($groupLanguages);
    }

    /**
     * @inheritdoc
     */
    public function map(array $groupLanguages): LinkGroupInterface
    {
        foreach ($groupLanguages as $groupLanguage) {
            $langID                      = (int)$groupLanguage->kSprache;
            $this->languageID[]          = $langID;
            $this->names[$langID]        = $groupLanguage->cName;
            $this->languageCode[$langID] = $groupLanguage->cISOSprache;
            $this->template              = $groupLanguage->template;
        }
        $this->links = (new LinkList($this->db))->createLinks(map(flatten($this->db->queryPrepared(
            'SELECT kLink
                FROM tlink
                JOIN tlinkgroupassociations a 
                    ON tlink.kLink = a.linkID
                WHERE a.linkGroupID = :lgid
                ORDER BY tlink.nSort, tlink.cName',
            ['lgid' => $this->id],
            ReturnType::ARRAY_OF_ASSOC_ARRAYS
        )), function ($e) {
            return (int)$e;
        }));

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getName(int $idx = null): string
    {
        $idx = $idx ?? \Shop::getLanguageID();

        return $this->names[$idx] ?? '';
    }

    /**
     * @inheritdoc
     */
    public function getNames(): array
    {
        return $this->names;
    }

    /**
     * @inheritdoc
     */
    public function setNames(array $names): void
    {
        $this->names = $names;
    }

    /**
     * @inheritdoc
     */
    public function getID(): int
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function setID(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @inheritdoc
     */
    public function getLinks(): Collection
    {
        return $this->links;
    }

    /**
     * @inheritdoc
     */
    public function setLinks(Collection $links): void
    {
        $this->links = $links;
    }

    /**
     * @inheritdoc
     */
    public function getTemplate(): string
    {
        return $this->template;
    }

    /**
     * @inheritdoc
     */
    public function setTemplate(string $template): void
    {
        $this->template = $template;
    }

    /**
     * @inheritdoc
     */
    public function filterLinks(callable $func): Collection
    {
        $this->links = $this->links->filter($func);

        return $this->links;
    }

    /**
     * @inheritdoc
     */
    public function getLanguageID(): array
    {
        return $this->languageID;
    }

    /**
     * @inheritdoc
     */
    public function setLanguageID(array $languageID): void
    {
        $this->languageID = $languageID;
    }

    /**
     * @inheritdoc
     */
    public function getLanguageCode(): array
    {
        return $this->languageCode;
    }

    /**
     * @inheritdoc
     */
    public function setLanguageCode(array $languageCode): void
    {
        $this->languageCode = $languageCode;
    }

    /**
     * @inheritdoc
     */
    public function isSpecial(): bool
    {
        return $this->isSpecial;
    }

    /**
     * @inheritdoc
     */
    public function setIsSpecial(bool $isSpecial): void
    {
        $this->isSpecial = $isSpecial;
    }

    /**
     * @param int $langID
     * @return bool
     */
    public function isAvailableInLanguage(int $langID): bool
    {
        return \in_array($langID, $this->languageID, true);
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        $res       = \get_object_vars($this);
        $res['db'] = '*truncated*';

        return $res;
    }
}
