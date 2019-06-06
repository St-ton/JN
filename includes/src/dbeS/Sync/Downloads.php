<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\dbeS\Sync;

use JTL\dbeS\Starter;
use JTL\Extensions\Download;

/**
 * Class Downloads
 * @package JTL\dbeS\Sync
 */
final class Downloads extends AbstractSync
{
    /**
     * @param Starter $starter
     * @return mixed|null
     */
    public function handle(Starter $starter)
    {
        foreach ($starter->getXML() as $i => $item) {
            [$file, $xml] = [\key($item), \reset($item)];
            if (\strpos($file, 'del_download.xml') !== false) {
                $this->handleDeletes($xml);
            } else {
                $this->handleInserts($xml);
            }
        }

        return null;
    }

    /**
     * @param array $xml
     */
    private function handleDeletes(array $xml): void
    {
        if (!\is_array($xml['del_downloads']['kDownload'])) {
            $xml['del_downloads']['kDownload'] = [$xml['del_downloads']['kDownload']];
        }
        foreach ($xml['del_downloads']['kDownload'] as $downloadID) {
            if ((int)$downloadID > 0) {
                $this->delete($downloadID);
            }
        }
    }

    /**
     * @param array $xml
     */
    private function handleInserts(array $xml): void
    {
        $downloads = $this->mapper->mapArray($xml['tDownloads'], 'tDownload', 'mDownload');
        if (isset($xml['tDownloads']['tDownload attr']) && \is_array($xml['tDownloads']['tDownload attr'])) {
            if ($downloads[0]->kDownload > 0) {
                $this->handleDownload($xml['tDownloads']['tDownload'], $downloads[0]);
            }
        } else {
            foreach ($downloads as $i => $download) {
                if ($download->kDownload > 0) {
                    $this->handleDownload($xml['tDownloads']['tDownload'][$i], $download);
                }
            }
        }
    }

    /**
     * @param array  $xml
     * @param object $download
     */
    private function handleDownload(array $xml, $download): void
    {
        $localized = $this->mapper->mapArray($xml, 'tDownloadSprache', 'mDownloadSprache');
        if (\count($localized) > 0) {
            $this->upsert('tdownload', [$download], 'kDownload');
            foreach ($localized as $item) {
                $item->kDownload = $download->kDownload;
                $this->upsert('tdownloadsprache', [$item], 'kDownload', 'kSprache');
            }
        }
    }

    /**
     * @param int $id
     */
    private function delete(int $id): void
    {
        if ($id > 0 && Download::checkLicense()) {
            $download = new Download($id);
            $rows     = $download->delete();
            $this->logger->debug($rows . ' Downloads geloescht');
        }
    }
}
