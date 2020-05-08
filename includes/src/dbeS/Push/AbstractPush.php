<?php

namespace JTL\dbeS\Push;

use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\dbeS\Mapper;
use JTL\Helpers\Text;
use JTL\XML;
use Psr\Log\LoggerInterface;
use ZipArchive;

/**
 * Class AbstractPush
 * @package JTL\dbeS\Push
 */
abstract class AbstractPush
{
    protected const XML_FILE = 'data.xml';

    /**
     * @var DbInterface
     */
    protected $db;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var JTLCacheInterface
     */
    protected $cache;

    /**
     * @var Mapper
     */
    protected $mapper;

    /**
     * Products constructor.
     * @param DbInterface       $db
     * @param JTLCacheInterface $cache
     * @param LoggerInterface   $logger
     */
    public function __construct(DbInterface $db, JTLCacheInterface $cache, LoggerInterface $logger)
    {
        $this->db     = $db;
        $this->cache  = $cache;
        $this->logger = $logger;
        $this->mapper = new Mapper();
    }

    /**
     * @return array|string
     */
    abstract public function getData();

    /**
     * @param array $arr
     * @param array $excludes
     * @return array
     */
    protected function buildAttributes(&$arr, $excludes = []): array
    {
        $attributes = [];
        if (!\is_array($arr)) {
            return $attributes;
        }
        foreach (\array_keys($arr) as $key) {
            if (!\in_array($key, $excludes, true) && $key[0] === 'k') {
                $attributes[$key] = $arr[$key];
                unset($arr[$key]);
            }
        }

        return $attributes;
    }

    /**
     * @param string $zip
     * @param object|array $xml
     * @param string $wawiVersion
     */
    public function zipRedirect($zip, $xml, string $wawiVersion): void
    {
        $xmlfile       = \fopen(\PFAD_SYNC_TMP . self::XML_FILE, 'w');
        $serializedXML = $wawiVersion === 'unknown'
            ? strtr(Text::convertISO(XML::serialize($xml)), "\0", ' ')
            : XML::serialize($xml);
        \fwrite($xmlfile, $serializedXML);
        \fclose($xmlfile);
        if (\file_exists(\PFAD_SYNC_TMP . self::XML_FILE)) {
            $archive = new ZipArchive();
            if ($archive->open(\PFAD_SYNC_TMP . $zip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== false
                && $archive->addFile(\PFAD_SYNC_TMP . self::XML_FILE, self::XML_FILE)
            ) {
                $archive->close();
                \readfile(\PFAD_SYNC_TMP . $zip);
                exit;
            }
            $archive->close();
            \syncException($archive->getStatusString());
        }
    }
}
