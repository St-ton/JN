<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\ExtensionData;

use function Functional\first;
use function Functional\group;
use function Functional\reindex;

/**
 * Class MailTemplates
 * @package Plugin\ExtensionData
 */
class MailTemplates
{
    /**
     * @var array
     */
    private $templates = [];

    /**
     * @var array
     */
    private $templatesAssoc = [];

    /**
     * @param array $data
     * @return $this
     */
    public function load(array $data): self
    {
        $grouped   = group($data, function ($e) {
            return $e->kEmailvorlage;
        });
        $templates = [];
        foreach ($grouped as $template) {
            $first                = clone first($template);
            $first->kEmailvorlage = (int)$first->kEmailvorlage;
            $first->kPlugin       = (int)$first->kPlugin;
            $first->nAKZ          = (int)$first->nAKZ;
            $first->nAGB          = (int)$first->nAGB;
            $first->nWRB          = (int)$first->nWRB;
            $first->nWRBForm      = (int)$first->nWRBForm;
            $first->nDSE          = (int)$first->nDSE;
            unset($first->cContentHtml, $first->cContentText, $first->kSprache, $first->cBetreff, $first->cPDFS);
            $first->oPluginEmailvorlageSprache_arr = [];
            foreach ($template as $item) {
                $localized                               = new \stdClass();
                $localized->kEmailvorlage                = (int)$item->kEmailvorlage;
                $localized->kSprache                     = (int)$item->kSprache;
                $localized->cBetreff                     = $item->cBetreff;
                $localized->cContentHtml                 = $item->cContentHtml;
                $localized->cContentText                 = $item->cContentText;
                $localized->cPDFS                        = $item->cPDFS;
                $localized->cDateiname                   = $item->cDateiname;
                $first->oPluginEmailvorlageSprache_arr[] = $localized;
            }
            $templates[] = $first;
        }
        $this->templates      = $templates;
        $this->templatesAssoc = reindex($templates, function ($item) {
            return $item->cModulId;
        });

        return $this;
    }

    /**
     * @return array
     */
    public function getTemplates(): array
    {
        return $this->templates;
    }

    /**
     * @param array $templates
     */
    public function setTemplates(array $templates): void
    {
        $this->templates = $templates;
    }

    /**
     * @return array
     */
    public function getTemplatesAssoc(): array
    {
        return $this->templatesAssoc;
    }

    /**
     * @param array $templatesAssoc
     */
    public function setTemplatesAssoc(array $templatesAssoc): void
    {
        $this->templatesAssoc = $templatesAssoc;
    }
}
