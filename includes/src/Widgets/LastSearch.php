<?php declare(strict_types=1);

namespace JTL\Widgets;

/**
 * Class LastSearch
 * @package JTL\Widgets
 */
class LastSearch extends AbstractWidget
{
    /**
     * @inheritdoc
     */
    public function init(): void
    {
        if (\method_exists($this, 'setPermission')) {
            $this->setPermission('MODULE_LIVESEARCH_VIEW');
        }

        $lastQueries = $this->getDB()->getObjects(
            'SELECT search.*, cIso FROM tsuchanfrage search
                    LEFT JOIN tsprache lang on search.kSprache = lang.kSprache
                ORDER BY dZuletztGesucht DESC 
                LIMIT 10'
        );
        $this->getSmarty()->assign('lastQueries', $lastQueries);
    }

    /**
     * @inheritDoc
     */
    public function getContent(): string
    {
        return $this->getSmarty()->fetch('tpl_inc/widgets/widgetLastSearch.tpl');
    }
}
