<?php declare(strict_types=1);

namespace JTL\License;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\License\Struct\ExsLicense;
use stdClass;

/**
 * Class Manager
 * @package JTL\License
 */
class Manager
{
    private const MAX_REQUESTS = 10;

    /**
     * @var DbInterface
     */
    private $db;

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
     * @return int
     * @throws RequestException $e
     */
    public function update(): int
    {
        $res = $this->client->request(
            'POST',
            'https://license.jtl-test.de/v1/exs',
            [
                'body'    => \json_encode(['locale' => 'de_DE']),
                'headers' => [
                    'Accept' => 'application/json'
                ],
                'verify'  => false
            ]
        );

        $this->housekeeping();

        return $this->db->insert(
            'licenses',
            (object)['data' => (string)$res->getBody(), 'returnCode' => $res->getStatusCode()]
        );
    }

    /**
     * @return stdClass|null
     */
    public function getLicenseData(): ?stdClass
    {
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
        $json             = \json_decode($data->data);
        $json->timestamp  = $data->timestamp;
        $json->returnCode = $data->returnCode;

        return $json === null || !isset($json->extensions) ? null : $json;
    }

    /**
     * @param string $pluginID
     * @return ExsLicense|null
     */
    public function getLicenseForPluginID(string $pluginID): ?ExsLicense
    {
        $data = $this->getLicenseData();
        if ($data === null) {
            return null;
        }
        foreach ($data->extensions as $extension) {
            if ($extension->id === 'jtl_paypal_shop5') {
                $extension->id = 'jtl_paypal';
            }
            if ($extension->id === $pluginID) {
                $esxLicense = new ExsLicense($extension);
                $esxLicense->setQueryDate($data->timestamp);
                $esxLicense->setState(ExsLicense::STATE_ACTIVE);
                if ($esxLicense->getType() === ExsLicense::TYPE_PLUGIN) {
                    $installed = $this->db->select('tplugin', 'cPluginID', $esxLicense->getID());
                    $esxLicense->setIsInstalled($installed !== null);
                }

                return $esxLicense;
            }
        }

        return null;
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
