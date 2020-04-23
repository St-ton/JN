<?php declare(strict_types=1);

namespace JTL\License;

use InvalidArgumentException;
use JTL\License\Exception\ApiResultCodeException;
use JTL\License\Exception\DownloadValidationException;
use JTL\License\Exception\FilePermissionException;
use JTL\Shop;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Client;
use JTL\License\Struct\Release;

/**
 * Class Downloader
 * @package JTL\License
 */
class Downloader
{
    /**
     * @param Release $available
     * @return ResponseInterface|string
     * @throws DownloadValidationException
     */
    public function downloadRelease(Release $available): string
    {
        if (!$this->validateDownloadArchive($available)) {
            throw new DownloadValidationException('Could not validate archive');
        }
        $url = $available->getDownloadURL();

        return $this->downloadItemArchive($url, \basename($url));
    }

    /**
     * @param string $url
     * @param string $targetName
     * @return ResponseInterface
     * @throws string
     */
    private function downloadItemArchive(string $url, string $targetName): string
    {
        $fileName = \PFAD_ROOT . \PFAD_DBES_TMP . \basename($targetName);
        $resource = \fopen($fileName, 'w+');
        if ($resource === false) {
            throw new FilePermissionException('Cannot open file ' . $fileName);
        }
        $client = new Client();
        $res    = $client->request('GET', $url, ['sink' => $resource]);
        if ($res->getStatusCode() !== 200) {
            throw new ApiResultCodeException('Did not get 200 OK result code form api but ' . $res->getStatusCode());
        }
        // @todo integrity validation

        return $fileName;
    }

    /**
     * @param Release $available
     * @return bool
     */
    private function validateDownloadArchive(Release $available): bool
    {
        if ($available->getDownloadURL() === null) {
            return false;
        }
        $parsed = \parse_url($available->getDownloadURL());
        if (!\is_array($parsed) || $parsed['scheme'] !== 'https') {
            return false;
        }
        // @todo: signature validation
        return true;
    }
}
