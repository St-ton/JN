<?php declare(strict_types=1);

namespace JTL\Backend\Upgrade;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use JsonException;
use JTL\DB\DbInterface;
use stdClass;

/**
 * Class ReleaseDownloader
 * @package JTL\Backend\Upgrade
 * @since 5.3.0
 */
class ReleaseDownloader
{
    private const MAX_REQUESTS = 10;

    private const CHECK_INTERVAL_HOURS = 24;

    public const API_URL = 'http://localhost:8080/versions.json';

    private Collection $releases;

    public function __construct(private readonly DbInterface $db)
    {
        $this->update();
        $this->releases = \collect($this->getReleaseData()->data ?? [])->mapInto(Release::class)
            ->sort(static function (Release $a, Release $b) {
                return $a->version->greaterThan($b->version) ? 1 : -1;
            });
    }

    private function checkUpdate(): bool
    {
        return ($lastItem = $this->getReleaseData()) === null
            || (\time() - \strtotime($lastItem->timestamp)) / (60 * 60) > self::CHECK_INTERVAL_HOURS;
    }

    /**
     * @throws GuzzleException
     */
    private function getReleaseData(bool $retry = false): ?stdClass
    {
        $data = $this->db->getSingleObject(
            'SELECT * FROM releases
                WHERE returnCode = 200
                ORDER BY id DESC
                LIMIT 1'
        );
        if ($data === null) {
            if ($retry === true) {
                return null;
            }
            $this->update(true);

            return $this->getReleaseData(true);
        }
        try {
            $obj             = new stdClass();
            $obj->data       = (array)\json_decode($data->data ?? '', false, 512, \JSON_THROW_ON_ERROR);
            $obj->timestamp  = $data->timestamp;
            $obj->returnCode = $data->returnCode;
        } catch (JsonException) {
            $obj = null;
        }

        return $obj;
    }

    /**
     * @throws GuzzleException
     */
    private function update(bool $force = false): int
    {
        if (!$force && !$this->checkUpdate()) {
            return 0;
        }
        $res = (new Client())->get(self::API_URL);
        $this->housekeeping();

        return $this->db->insert('releases', (object)[
            'data'       => (string)$res->getBody(),
            'returnCode' => $res->getStatusCode()
        ]);
    }

    private function housekeeping(): int
    {
        return $this->db->getAffectedRows(
            'DELETE a 
                FROM releases AS a 
                JOIN ( 
                    SELECT id 
                        FROM releases 
                        ORDER BY timestamp DESC 
                        LIMIT 99999 OFFSET :max) AS b
                ON a.id = b.id',
            ['max' => self::MAX_REQUESTS]
        );
    }

    public function getReleases(?string $channel = null): Collection
    {
        $channel = \strtolower($channel ?? Channels::getActiveChannel($this->db));

        return $this->releases->filter(static function (Release $item) use ($channel) {
            return \strtolower($item->channel) === $channel;
        });
    }

    public function getReleaseByID(int $id): ?Release
    {
        return $this->releases->first(static function (Release $release) use ($id) {
            return $release->id === $id;
        });
    }

    public function getReleasyByVersionString(string $version): ?Release
    {
        return $this->releases->first(static function (Release $release) use ($version) {
            return (string)$release->version === $version;
        });
    }
}
