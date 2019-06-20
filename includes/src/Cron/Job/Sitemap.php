<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Cron\Job;

use JTL\Cron\Job;
use JTL\Cron\JobInterface;
use JTL\Cron\QueueEntry;
use JTL\Customer\Kundengruppe;
use JTL\Language\LanguageHelper;
use JTL\Shop;
use JTL\Sitemap\Config\DefaultConfig;
use JTL\Sitemap\Export;
use JTL\Sitemap\ItemRenderers\DefaultRenderer;
use JTL\Sitemap\SchemaRenderers\DefaultSchemaRenderer;
use JTL\Sprache;
use stdClass;

/**
 * Class Sitemap
 * @package JTL\Cron\Job
 */
final class Sitemap extends Job
{
    /**
     * @inheritdoc
     */
    public function hydrate($data)
    {
        parent::hydrate($data);
        if (\JOBQUEUE_LIMIT_M_SITEMAP_ITEMS > 0) {
            $this->setLimit(\JOBQUEUE_LIMIT_M_SITEMAP_ITEMS);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function start(QueueEntry $queueEntry): JobInterface
    {
        parent::start($queueEntry);
        $config       = Shop::getSettings([\CONF_GLOBAL, \CONF_SITEMAP]);
        $exportConfig = new DefaultConfig(
            $this->db,
            $config,
            Shop::getURL() . '/',
            Shop::getImageBaseURL()
        );
        $exporter     = new Export(
            $this->db,
            $this->logger,
            new DefaultRenderer(),
            new DefaultSchemaRenderer(),
            $config
        );
        $exporter->generate(
            [Kundengruppe::getDefaultGroupID()],
            LanguageHelper::getAllLanguages(),
            $exportConfig->getFactories()
        );
        $this->setFinished($finished);

        return $this;
    }
}
