<?php declare(strict_types=1);

namespace JTL\License;

use DateTime;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\License\Struct\ExsLicense;
use JTL\Shop;
use JTLShop\SemVer\Version;
use stdClass;

/**
 * Class Manager
 * @package JTL\License
 */
class Manager
{
    private const MAX_REQUESTS = 10;

    private const CHECK_INTERVAL_HOURS = 4;

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var Client
     */
    private $client;

    /**
     * Manager constructor.
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
        $this->db     = $db;
        $this->client = new Client();
    }

    /**
     * @return bool - true if data should be updated
     */
    private function checkUpdate(): bool
    {
        $lastItem = $this->getLicenseData();
        if ($lastItem === null) {
            return true;
        }

        return (\time() - \strtotime($lastItem->timestamp)) / (60 * 60) > self::CHECK_INTERVAL_HOURS;
    }

    /**
     * @param bool $force
     * @return int
     * @throws RequestException $e
     */
    public function update(bool $force = false): int
    {
        if (!$force && !$this->checkUpdate()) {
            return 0;
        }
        $res = $this->client->request(
            'POST',
            'https://license.jtl-test.de/v1/exs',
            [
                'headers' => ['Accept' => 'application/json'],
                'verify'  => true
            ]
        );
        $this->housekeeping();

        return $this->db->insert(
            'licenses',
            (object)['data' => (string)$res->getBody(), 'returnCode' => $res->getStatusCode()]
        );
    }

    /**
     * @return stdClass
     */
    private function getLocalTestData(): stdClass
    {
        $obj             = \json_decode(\file_get_contents(\PFAD_ROOT . 'getLicenses.json'));
        $dt              = new DateTime();
        $obj->timestamp  = $dt->format('y-m-d H:i:s');
        $obj->returnCode = 200;

        return $obj;
    }

    /**
     * @return stdClass|null
     */
    public function getLicenseData(): ?stdClass
    {
        if (true) {
            return $this->getLocalTestData();
        }
        $data = $this->db->query(
            'SELECT * FROM licenses
                WHERE returnCode = 200
                ORDER BY id DESC
                LIMIT 1',
            ReturnType::SINGLE_OBJECT
        );
        if ($data === false) {
            return null;
        }
        $obj             = \json_decode($data->data);
        $obj->timestamp  = $data->timestamp;
        $obj->returnCode = $data->returnCode;

        return $obj === null || !isset($obj->extensions) ? null : $obj;
    }

    /**
     * @param string $pluginID
     * @return ExsLicense|null
     */
    public function getLicenseForPluginID(string $pluginID): ?ExsLicense
    {
        return (new Mapper($this->db, $this))->getCollection()->getForPluginID($pluginID);
    }

    /**
     * @return int
     */
    private function housekeeping(): int
    {
        return $this->db->queryPrepared(
            'DELETE a 
                FROM licenses AS a 
                JOIN ( 
                    SELECT id 
                        FROM licenses 
                        ORDER BY timestamp DESC 
                        LIMIT 99999 OFFSET :max) AS b
                ON a.id = b.id',
            ['max' => self::MAX_REQUESTS],
            ReturnType::AFFECTED_ROWS
        );
    }
}
