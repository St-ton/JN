<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Mail\Validator;

use Exception;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Language\LanguageHelper;
use JTL\Mail\Hydrator\HydratorInterface;
use JTL\Mail\Renderer\RendererInterface;
use JTL\Mail\Template\TemplateFactory;

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
    public function __construct(
        DbInterface $db,
        TemplateFactory $factory,
        RendererInterface $renderer,
        HydratorInterface $hydrator
    ) {
        $this->db       = $db;
        $this->factory  = $factory;
        $this->hydrator = $hydrator;
        $this->renderer = $renderer;
    }

    /**
     *
     */
    public function checkAll(): void
    {
        $items = $this->db->query(
            'SELECT cModulId AS id, kPlugin AS pluginID FROM temailvorlage',
            ReturnType::ARRAY_OF_OBJECTS
        );

        foreach ($items as $template) {
            $this->checkSyntax($template->id, (int)$template->pluginID);
        }
    }

    /**
     * @param string $templateID
     * @param int    $pluginID
     * @return string
     */
    public function checkSyntax(string $templateID, int $pluginID = 0): string
    {
        if ($pluginID > 0) {
            $templateID = 'kPlugin_' . $pluginID . '_' . $templateID;
        }
        $template = $this->factory->getTemplate($templateID);
        if ($template === null) {
            return '';
        }

        foreach (LanguageHelper::getAllLanguages() as $lang) {
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
                $model->setActive(false);
                $model->save();

                return $e->getMessage();
            }
        }

        return '';
    }
}
