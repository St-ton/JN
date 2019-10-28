<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Boxes\Items;

use JTL\DB\ReturnType;
use JTL\Helpers\URL;
use JTL\Session\Frontend;
use JTL\Shop;

/**
 * Class Poll
 * @package JTL\Boxes\Items
 */
final class Poll extends AbstractBox
{
    /**
     * Poll constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->addMapping('oUmfrage_arr', 'Items');
        $sql       = ($conf = $this->config['umfrage']['umfrage_box_anzahl']) > 0
            ? ' LIMIT ' . (int)$conf
            : '';
        $langID    = Shop::getLanguageID();
        $cacheID   = 'bu_' . $langID . '_' . Frontend::getCustomerGroup()->getID() . \md5($sql);
        $cacheTags = [\CACHING_GROUP_BOX, \CACHING_GROUP_CORE];
        $cached    = true;
        if (($polls = Shop::Container()->getCache()->get($cacheID)) === false) {
            $cached = false;
            $polls  = Shop::Container()->getDB()->queryPrepared(
                "SELECT tumfrage.kUmfrage, tumfrage.kSprache, tumfrage.kKupon, tumfrage.cKundengruppe, 
                tumfrage.cName, tumfrage.cBeschreibung, tumfrage.fGuthaben, tumfrage.nBonuspunkte, 
                tumfrage.nAktiv, tumfrage.dGueltigVon, tumfrage.dGueltigBis, tumfrage.dErstellt, tseo.cSeo,
                DATE_FORMAT(tumfrage.dGueltigVon, '%d.%m.%Y  %H:%i') AS dGueltigVon_de,
                DATE_FORMAT(tumfrage.dGueltigBis, '%d.%m.%Y  %H:%i') AS dGueltigBis_de, 
                COUNT(tumfragefrage.kUmfrageFrage) AS nAnzahlFragen
                    FROM tumfrage
                    JOIN tumfragefrage 
                        ON tumfragefrage.kUmfrage = tumfrage.kUmfrage
                    LEFT JOIN tseo 
                        ON tseo.cKey = 'kUmfrage'
                        AND tseo.kKey = tumfrage.kUmfrage
                        AND tseo.kSprache = :lid
                    WHERE tumfrage.nAktiv = 1
                        AND tumfrage.kSprache = :lid
                        AND (cKundengruppe LIKE '%;-1;%' 
                            OR FIND_IN_SET(':cid', REPLACE(cKundengruppe, ';', ',')) > 0)
                        AND NOW() BETWEEN dGueltigVon AND COALESCE(dGueltigBis, NOW())
                    GROUP BY tumfrage.kUmfrage
                    ORDER BY tumfrage.dGueltigVon DESC" . $sql,
                ['lid' => $langID, 'cid' => Frontend::getCustomerGroup()->getID()],
                ReturnType::ARRAY_OF_OBJECTS
            );
            Shop::Container()->getCache()->set($cacheID, $polls, $cacheTags);
        }
        foreach ($polls as $poll) {
            $poll->cURL     = URL::buildURL($poll, \URLART_UMFRAGE);
            $poll->cURLFull = URL::buildURL($poll, \URLART_UMFRAGE, true);
        }
        $this->setItems($polls);
        $this->setShow(\count($polls) > 0);
        \executeHook(\HOOK_BOXEN_INC_UMFRAGE, [
            'box'        => $this,
            'cache_tags' => &$cacheTags,
            'cached'     => $cached
        ]);
    }
}
