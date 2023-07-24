<?php declare(strict_types=1);

namespace JTL;

use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Customer\Visitor;
use JTL\Session\Frontend;
use stdClass;

/**
 * Class Campaign
 * @package JTL
 */
class Campaign
{
    /**
     * @var int
     */
    public int $kKampagne = 0;

    /**
     * @var string
     */
    public string $cName = '';

    /**
     * @var string
     */
    public string $cParameter = '';

    /**
     * @var string
     */
    public string $cWert = '';

    /**
     * @var int
     */
    public int $nDynamisch = 0;

    /**
     * @var int
     */
    public int $nAktiv = 0;

    /**
     * @var string
     */
    public string $dErstellt = '';

    /**
     * @var string
     */
    public string $dErstellt_DE = '';

    /**
     * @var int
     */
    public int $nInternal = 1;

    /**
     * Kampagne constructor.
     * @param int $id
     */
    public function __construct(int $id = 0)
    {
        if ($id > 0) {
            $this->loadFromDB($id);
        }
    }

    /**
     * @param int $id
     * @return $this
     */
    public function loadFromDB(int $id): self
    {
        $data = Shop::Container()->getDB()->getSingleObject(
            "SELECT tkampagne.*, DATE_FORMAT(tkampagne.dErstellt, '%d.%m.%Y %H:%i:%s') AS dErstellt_DE
                FROM tkampagne
                WHERE tkampagne.kKampagne = :cid",
            ['cid' => $id]
        );

        if ($data !== null && $data->kKampagne > 0) {
            $this->kKampagne    = (int)$data->kKampagne;
            $this->cName        = $data->cName;
            $this->cParameter   = $data->cParameter;
            $this->cWert        = $data->cWert;
            $this->nDynamisch   = (int)$data->nDynamisch;
            $this->nAktiv       = (int)$data->nAktiv;
            $this->dErstellt    = $data->dErstellt;
            $this->dErstellt_DE = $data->dErstellt_DE;
            $this->nInternal    = (int)$data->nInternal;
        }

        return $this;
    }

    /**
     * @return int
     */
    public function insertInDB(): int
    {
        $obj             = new stdClass();
        $obj->cName      = Text::filterXSS($this->cName);
        $obj->cParameter = Text::filterXSS($this->cParameter);
        $obj->cWert      = Text::filterXSS($this->cWert);
        $obj->nDynamisch = $this->nDynamisch;
        $obj->nAktiv     = $this->nAktiv;
        $obj->dErstellt  = $this->dErstellt;
        $this->kKampagne = Shop::Container()->getDB()->insert('tkampagne', $obj);
        if (\mb_convert_case($this->dErstellt, \MB_CASE_LOWER) === 'now()') {
            $this->dErstellt = \date_format(\date_create(), 'Y-m-d H:i:s');
        }
        $this->dErstellt_DE = \date_format(\date_create($this->dErstellt), 'd.m.Y H:i:s');

        return $this->kKampagne;
    }

    /**
     * @return int
     */
    public function updateInDB(): int
    {
        $obj             = new stdClass();
        $obj->cName      = Text::filterXSS($this->cName);
        $obj->cParameter = Text::filterXSS($this->cParameter);
        $obj->cWert      = Text::filterXSS($this->cWert);
        $obj->nDynamisch = $this->nDynamisch;
        $obj->nAktiv     = $this->nAktiv;
        $obj->dErstellt  = $this->dErstellt;
        $obj->kKampagne  = $this->kKampagne;

        $res = Shop::Container()->getDB()->update('tkampagne', 'kKampagne', $obj->kKampagne, $obj);
        if (\mb_convert_case($this->dErstellt, \MB_CASE_LOWER) === 'now()') {
            $this->dErstellt = \date_format(\date_create(), 'Y-m-d H:i:s');
        }
        $this->dErstellt_DE = \date_format(\date_create($this->dErstellt), 'd.m.Y H:i:s');

        return $res;
    }

    /**
     * @return bool
     */
    public function deleteInDB(): bool
    {
        if ($this->kKampagne <= 0) {
            return false;
        }
        // only external campaigns are deletable
        Shop::Container()->getDB()->queryPrepared(
            'DELETE tkampagne, tkampagnevorgang
                FROM tkampagne
                LEFT JOIN tkampagnevorgang 
                    ON tkampagnevorgang.kKampagne = tkampagne.kKampagne
                WHERE tkampagne.kKampagne = :cid AND tkampagne.nInternal = 0',
            ['cid' => $this->kKampagne]
        );

        return true;
    }

    /**
     * @return array
     */
    public static function getAvailable(): array
    {
        $cacheID = 'campaigns';
        if (($campaigns = Shop::Container()->getCache()->get($cacheID)) === false) {
            $campaigns = Shop::Container()->getDB()->selectAll(
                'tkampagne',
                'nAktiv',
                1,
                '*, DATE_FORMAT(dErstellt, \'%d.%m.%Y %H:%i:%s\') AS dErstellt_DE'
            );
            foreach ($campaigns as $campaign) {
                $campaign->kKampagne  = (int)$campaign->kKampagne;
                $campaign->nDynamisch = (int)$campaign->nDynamisch;
                $campaign->nAktiv     = (int)$campaign->nAktiv;
                $campaign->nInternal  = (int)$campaign->nInternal;
            }
            Shop::Container()->getCache()->set($cacheID, $campaigns, [\CACHING_GROUP_CORE]);
        }

        return $campaigns;
    }

    /**
     * @param object $campaign
     * @return bool
     */
    private static function validateStaticParams(object $campaign): bool
    {
        $full = Shop::getURL() . '/?' . $campaign->cParameter . '=' . $campaign->cWert;
        \parse_str(\parse_url($full, \PHP_URL_QUERY), $params);
        $ok = \count($params) > 0;
        foreach ($params as $param => $value) {
            if (!self::paramMatches(Request::verifyGPDataString($param), $value)) {
                $ok = false;
                break;
            }
        }

        return $ok;
    }

    /**
     * @param string $given
     * @param string $campaignValue
     * @return bool
     */
    private static function paramMatches($given, $campaignValue): bool
    {
        return \mb_convert_case($campaignValue, \MB_CASE_LOWER) === \mb_convert_case($given, \MB_CASE_LOWER);
    }

    /**
     * @former pruefeKampagnenParameter()
     */
    public static function checkCampaignParameters(): void
    {
        $visitorID = Frontend::get('oBesucher')->kBesucher ?? 0;
        if ($visitorID <= 0) {
            return;
        }
        $campaigns = self::getAvailable();
        if (\count($campaigns) === 0) {
            return;
        }
        $db       = Shop::Container()->getDB();
        $hit      = false;
        $referrer = Visitor::getReferer();
        foreach ($campaigns as $campaign) {
            // Wurde für die aktuelle Kampagne der Parameter via GET oder POST uebergeben?
            $given = Request::verifyGPDataString($campaign->cParameter);
            if ($given !== '' && ($campaign->nDynamisch === 1 || self::validateStaticParams($campaign))) {
                $hit = true;
                // wurde der HIT für diesen Besucher schon gezaehlt?
                $event = $db->select(
                    'tkampagnevorgang',
                    ['kKampagneDef', 'kKampagne', 'kKey', 'cCustomData'],
                    [
                        \KAMPAGNE_DEF_HIT,
                        $campaign->kKampagne,
                        $visitorID,
                        Text::filterXSS($_SERVER['REQUEST_URI']) . ';' . $referrer
                    ]
                );
                if ($event === null) {
                    $event               = new stdClass();
                    $event->kKampagne    = $campaign->kKampagne;
                    $event->kKampagneDef = \KAMPAGNE_DEF_HIT;
                    $event->kKey         = $visitorID;
                    $event->fWert        = 1.0;
                    $event->cParamWert   = $given;
                    $event->cCustomData  = Text::filterXSS($_SERVER['REQUEST_URI']) . ';' . $referrer;
                    if ($campaign->nDynamisch === 0) {
                        $event->cParamWert = $campaign->cWert;
                    }
                    $event->dErstellt = 'NOW()';
                    $db->insert('tkampagnevorgang', $event);
                    $_SESSION['Kampagnenbesucher'][$campaign->kKampagne]        = $campaign;
                    $_SESSION['Kampagnenbesucher'][$campaign->kKampagne]->cWert = $event->cParamWert;
                }
            }

            if (!$hit && \str_contains($_SERVER['HTTP_REFERER'] ?? '', '.google.')) {
                // Besucher kommt von Google und hat vorher keine Kampagne getroffen
                $event = $db->select(
                    'tkampagnevorgang',
                    ['kKampagneDef', 'kKampagne', 'kKey'],
                    [\KAMPAGNE_DEF_HIT, \KAMPAGNE_INTERN_GOOGLE, $visitorID]
                );
                if ($event === null) {
                    $campaign            = new self(\KAMPAGNE_INTERN_GOOGLE);
                    $event               = new stdClass();
                    $event->kKampagne    = \KAMPAGNE_INTERN_GOOGLE;
                    $event->kKampagneDef = \KAMPAGNE_DEF_HIT;
                    $event->kKey         = $visitorID;
                    $event->fWert        = 1.0;
                    $event->cParamWert   = $campaign->cWert;
                    $event->dErstellt    = 'NOW()';
                    if ($campaign->nDynamisch === 1) {
                        $event->cParamWert = $given;
                    }
                    $db->insert('tkampagnevorgang', $event);
                    $_SESSION['Kampagnenbesucher'][$campaign->kKampagne]        = $campaign;
                    $_SESSION['Kampagnenbesucher'][$campaign->kKampagne]->cWert = $event->cParamWert;
                }
            }
        }
    }

    /**
     * @param int         $id
     * @param int         $kKey
     * @param float       $fWert
     * @param string|null $customData
     * @return int
     * @former setzeKampagnenVorgang()
     */
    public static function setCampaignAction(int $id, int $kKey, $fWert, $customData = null): int
    {
        if ($id > 0 && $kKey > 0 && $fWert > 0 && isset($_SESSION['Kampagnenbesucher'])) {
            $events = [];
            if (!\is_array($_SESSION['Kampagnenbesucher'])) {
                $tmpCampaign = $_SESSION['Kampagnenbesucher'];
                unset($_SESSION['Kampagnenbesucher']);
                $_SESSION['Kampagnenbesucher'] = $tmpCampaign instanceof self
                    ? [$tmpCampaign->kKampagne => $tmpCampaign]
                    : [];
            }
            foreach ($_SESSION['Kampagnenbesucher'] as $campaign) {
                $event               = new stdClass();
                $event->kKampagne    = $campaign->kKampagne;
                $event->kKampagneDef = $id;
                $event->kKey         = $kKey;
                $event->fWert        = $fWert;
                $event->cParamWert   = $campaign->cWert;
                $event->dErstellt    = 'NOW()';

                if ($customData !== null) {
                    $event->cCustomData = \mb_substr($customData, 0, 255);
                }
                $events[] = $event;
            }

            return Shop::Container()->getDB()->insertBatch('tkampagnevorgang', $events);
        }

        return 0;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->cParameter === 'jtl'
            ? \__($this->cName)
            : $this->cName;
    }
}
