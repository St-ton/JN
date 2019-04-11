<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Mail\Validator;

use Exception;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Mail\Hydrator\HydratorInterface;
use JTL\Mail\Renderer\RendererInterface;
use JTL\Mail\Template\TemplateFactory;
use JTL\Shop;
use JTL\Sprache;

/**
 * Class SyntaxChecker
 * @package JTL\Mail\Validator
 */
final class SyntaxChecker
{
    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var RendererInterface
     */
    private $renderer;

    /**
     * @var HydratorInterface
     */
    private $hydrator;

    /**
     * @var TemplateFactory
     */
    private $factory;

    /**
     * SyntaxChecker constructor.
     * @param DbInterface       $db
     * @param TemplateFactory   $factory
     * @param RendererInterface $renderer
     * @param HydratorInterface $hydrator
     */
    public function __construct(DbInterface $db, TemplateFactory $factory, RendererInterface $renderer, HydratorInterface $hydrator)
    {
        $this->db       = $db;
        $this->factory  = $factory;
        $this->hydrator = $hydrator;
        $this->renderer = $renderer;
    }

//    /**
//     * @param bool $error
//     * @param bool $force
//     * @param int $pluginID
//     */
//    public function updateError(bool $error = true, bool $force = false, int $pluginID = 0): void
//    {
//        if (Shop::getShopDatabaseVersion()->getMajor() < 5) {
//            return;
//        }
//        $upd              = new \stdClass();
//        $upd->nFehlerhaft = (int)$error;
//        if (!$force) {
//            $upd->cAktiv = $error ? 'N' : 'Y';
//        }
//        $res = $this->db->update(
//            $pluginID > 0 ? 'tpluginemailvorlage' : 'temailvorlage',
//            'kEmailvorlage',
//            $this->kEmailvorlage,
//            $upd
//        );
//        if ($res !== -1) {
//            $_SESSION['emailSyntaxErrorCount'] = (int)Shop::Container()->getDB()->query(
//                    'SELECT COUNT(*) AS cnt FROM temailvorlage WHERE nFehlerhaft = 1',
//                    ReturnType::SINGLE_OBJECT
//                )->cnt
//                + (int)Shop::Container()->getDB()->query(
//                    'SELECT COUNT(*) AS cnt FROM tpluginemailvorlage WHERE nFehlerhaft = 1',
//                    ReturnType::SINGLE_OBJECT
//                )->cnt;
//        }
//    }

    public function checkAll()
    {
        $items = \array_merge(
            $this->db->query(
                'SELECT cModulId AS id, 0 AS pluginID FROM temailvorlage',
                ReturnType::ARRAY_OF_OBJECTS
            ),
            $this->db->query(
                'SELECT cModulId AS id, kPlugin AS pluginID FROM tpluginemailvorlage',
                ReturnType::ARRAY_OF_OBJECTS
            )
        );

        foreach ($items as $template) {
            $this->checkSyntax($template->id, (int)$template->pluginID);
        }
    }

    public function checkSyntax(string $templateID, int $pluginID = 0): string
    {
        if ($pluginID > 0) {
            $templateID = 'kPlugin_' . $pluginID . '_' . $templateID;
        }
        $template = $this->factory->getTemplate($templateID);
        if ($template === null) {
            echo $templateID;
            return 'null!';
        }

        foreach (Sprache::getAllLanguages() as $lang) {
            $template->load($lang->kSprache, 1);
            $model = $template->getModel();
            if ($model === null) {
                continue;
            }
            try {
                $this->hydrator->hydrate(null, $lang);
                $id = $model->getID() . '_' . $lang->kSprache;
                $this->renderer->renderHTML($id);
                $this->renderer->renderText($id);
            } catch (Exception $e) {
                $model->setHasError(true);
                // @todo: save to DB

                return $e->getMessage();
            }
        }

        return '';
    }
}
