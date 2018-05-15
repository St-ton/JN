<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Link;

use DB\DbInterface;
use DB\ReturnType;
use function Functional\flatten;
use function Functional\map;
use Tightenco\Collect\Support\Collection;

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
    private static $mapping = ['cLocalizedName' => 'Name'];

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
        if (count($groupLanguages) === 0) {
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
            $this->names[$langID]         = $groupLanguage->cName;
            $this->languageCode[$langID] = $groupLanguage->cISOSprache;
            $this->template              = $groupLanguage->template;
        }
        $this->links = (new LinkList($this->db))->createLinks(map(flatten($this->db->queryPrepared(
            'SELECT kLink
                FROM tlink
                WHERE kLinkgruppe = :lgid',
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
    public function setNames(array $names)
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
    public function setID(int $id)
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
    public function setLinks(Collection $links)
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
    public function setTemplate(string $template)
    {
        $this->template = $template;
    }

    /**
     * @inheritdoc
     */
    public function filterLinks (callable $func): Collection
    {
        $this->links = $this->links->filter($func);

        return $this->links;
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        $res       = get_object_vars($this);
        $res['db'] = '*truncated*';

        return $res;
    }
}
