<?php declare(strict_types=1);

namespace JTL\Template\Admin;

use DirectoryIterator;
use Illuminate\Support\Collection;
use JTL\DB\DbInterface;
use JTL\Plugin\InstallCode;
use JTL\Shop;
use JTL\Template\Admin\Validation\ValidatorInterface;
use JTL\Template\Model;
use JTL\XMLParser;

/**
 * Class Listing
 * @package JTL\Template\Admin
 */
final class Listing
{
    private const TEMPLATE_DIR = \PFAD_ROOT . \PFAD_TEMPLATES;

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var Collection
     */
    private $items;

    /**
     * Listing constructor.
     * @param DbInterface        $db
     * @param ValidatorInterface $validator
     */
    public function __construct(
        DbInterface $db,
        ValidatorInterface $validator
    )
    {
        $this->db        = $db;
        $this->validator = $validator;
        $this->items     = new Collection();
    }

    /**
     * @return Collection
     * @former gibAllePlugins()
     */
    public function getAll(): Collection
    {
        $parser = new XMLParser();
        $this->parseTemplateDir($parser, self::TEMPLATE_DIR);
        $this->sort();

        return $this->items;
    }

    /**
     * @return Model
     * @throws \Exception
     */
    private function getActiveTemplate(): Model
    {
        return Model::loadByAttributes(['eTyp' => 'standard'], $this->db);
    }

    /**
     * @param XMLParser $parser
     * @param string    $templateDir
     * @return Collection
     */
    private function parseTemplateDir(XMLParser $parser, string $templateDir): Collection
    {
        $validator = $this->validator;

        if (!\is_dir($templateDir)) {
            return $this->items;
        }
        $active  = $this->getActiveTemplate();
        $gettext = Shop::Container()->getGetText();
        foreach (new DirectoryIterator($templateDir) as $fileinfo) {
            if ($fileinfo->isDot() || !$fileinfo->isDir()) {
                continue;
            }
            $dir  = $fileinfo->getBasename();
            $info = $fileinfo->getPathname() . '/' . \TEMPLATE_XML;
            if (!\file_exists($info)) {
                continue;
            }
            $xml                 = $parser->parse($info);
            $code                = $validator->validateByPath($templateDir . $dir);
            $xml['cVerzeichnis'] = $dir;
            $xml['cFehlercode']  = $code;
            $item                = new ListingItem();
            $item->parseXML($xml);
            $item->setPath($templateDir . $dir);
            $item->setActive($item->getName() === $active->getName());

            $gettext->loadTemplateItemLocale('base', $item);
            $msgid = $item->getFramework() . '_desc';
            $desc  = __($msgid);
            if ($desc !== $msgid) {
                $item->setDescription($desc);
            } else {
                $item->setDescription(__($item->getDescription()));
            }
            $item->setAuthor(__($item->getAuthor()));
            $item->setName(__($item->getName()));

            if ($code === InstallCode::OK) {
                $item->setAvailable(true);
                $item->setHasError(false);
            }

            $this->items[] = $item;
        }

        return $this->items;
    }

    /**
     *
     */
    private function sort(): void
    {
        $this->items = $this->items->sortBy(static function (ListingItem $item) {
            return \mb_convert_case($item->getName(), \MB_CASE_LOWER);
        });
    }
}
