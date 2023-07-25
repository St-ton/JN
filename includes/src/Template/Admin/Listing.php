<?php declare(strict_types=1);

namespace JTL\Template\Admin;

use DirectoryIterator;
use Exception;
use Illuminate\Support\Collection;
use JTL\DB\DbInterface;
use JTL\Shop;
use JTL\Template\Admin\Validation\TemplateValidator;
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
     * @var Collection
     */
    private Collection $items;

    /**
     * Listing constructor.
     * @param DbInterface        $db
     * @param ValidatorInterface $validator
     */
    public function __construct(private readonly DbInterface $db, private readonly ValidatorInterface $validator)
    {
        $this->items = new Collection();
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
     * @throws Exception
     */
    private function getActiveTemplate(): Model
    {
        return Model::loadByAttributes(['type' => 'standard'], $this->db);
    }

    /**
     * @return Model|null
     * @throws Exception
     */
    private function getPreviewTemplate(): ?Model
    {
        return Model::loadByAttributes(['type' => 'test'], $this->db);
    }

    /**
     * @param XMLParser $parser
     * @param string    $templateDir
     * @return Collection
     */
    private function parseTemplateDir(XMLParser $parser, string $templateDir): Collection
    {
        if (!\is_dir($templateDir)) {
            return $this->items;
        }
        $preview = null;
        try {
            $active = $this->getActiveTemplate();
        } catch (Exception) {
            $active = new Model($this->db);
            $active->setTemplate('no-template');
        }
        try {
            $preview = $this->getPreviewTemplate()?->getTemplate();
        } catch (Exception) {
        }
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
            $code                = $this->validator->validate($templateDir . $dir, $xml);
            $xml['cVerzeichnis'] = $dir;
            $xml['cFehlercode']  = $code;
            $item                = new ListingItem();
            $item->parseXML($xml, $code);
            $item->setPath($templateDir . $dir);
            $item->setActive($item->getDir() === $active->getTemplate());
            $item->setIsPreview($preview !== null && $item->getDir() === $preview);

            $gettext->loadTemplateItemLocale('base', $item);
            $msgid = $item->getFramework() . '_desc';
            $desc  = \__($msgid);
            if ($desc !== $msgid) {
                $item->setDescription($desc);
            } else {
                $item->setDescription(\__($item->getDescription()));
            }
            $item->setAuthor(\__($item->getAuthor()));
            $item->setName(\__($item->getName()));
            if ($code === TemplateValidator::RES_OK) {
                $item->setAvailable(true);
                $item->setHasError(false);
            } else {
                $item->setAvailable(false);
                $item->setHasError(true);
                $item->setErrorCode($code);
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
        $this->items = $this->items->sortBy(static function (ListingItem $item): string {
            return \mb_convert_case($item->getName(), \MB_CASE_LOWER);
        });
    }
}
