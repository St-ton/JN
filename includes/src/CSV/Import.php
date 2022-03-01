<?php declare(strict_types=1);

namespace JTL\CSV;

use InvalidArgumentException;
use JTL\DB\DbInterface;
use JTL\Helpers\Request;
use JTL\Helpers\URL;
use JTL\Language\LanguageHelper;
use stdClass;
use TypeError;

/**
 * Class Import
 * @package JTL\CSV
 */
class Import
{
    public const TYPE_TRUNCATE_BEFORE = 0;

    public const TYPE_OVERWRITE_EXISTING = 1;

    public const TYPE_INSERT_NEW = 2;

    /**
     * @var string[]
     */
    private array $errors = [];

    /**
     * @var DbInterface
     */
    private DbInterface $db;

    /**
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
        $this->db = $db;
    }

    /**
     * If the "Import CSV" button was clicked with the id $importerId, try to insert entries from the CSV file uploaded
     * into to the table $target or call a function for each row to be imported. Call this function before you read the
     * data from the table again! Make sure, the CSV contains all important fields to form a valid row in your
     * DB-table!
     * Missing fields in the CSV will be set to the DB-tables default value if your DB is configured so.
     *
     * @param string          $id
     * @param string|callable $target - either target table name or callback function that takes an object to be
     *     imported
     * @param string[]        $fields - array of names of the fields in the order they appear in one data row. If and
     *     only if this array is empty, a header line of field names is expected, otherwise not.
     * @param string|null     $delim - delimiter character or null to guess it from the first row
     * @param int             $importType -
     *      0 = clear table, then import (careful!!! again: this will clear the table denoted by $target)
     *      1 = insert new, overwrite existing
     *      2 = insert only non-existing
     * @return int - -1 if importer-id-mismatch / 0 on success / >1 import error count
     * @throws TypeError
     * @throws InvalidArgumentException
     */
    public function handleCsvImportAction(
        string $id,
        $target,
        array $fields = [],
        ?string $delim = null,
        int $importType = self::TYPE_INSERT_NEW
    ): int {
        if (!\is_string($target) && !\is_callable($target)) {
            throw new TypeError('Argument $target must be either a string or a callable');
        }
        if (isset($_REQUEST['importType'])) {
            $importType = Request::verifyGPCDataInt('importType');
        }
        if ($importType !== 0 && $importType !== 1 && $importType !== 2) {
            throw new InvalidArgumentException('$importType must be 0, 1 or 2');
        }
        $csvFilename = $_FILES['csvfile']['tmp_name'] ?? null;
        if ($csvFilename === null) {
            throw new InvalidArgumentException(\__('No input file provided.'));
        }
        $csvMime = $_FILES['csvfile']['type'] ?? null;
        $allowed = [
            'application/vnd.ms-excel',
            'text/csv',
            'application/csv',
            'application/vnd.msexcel'
        ];
        if (!\in_array($csvMime, $allowed, true)) {
            $this->errors[] = \__('csvImportInvalidMime');

            return 1;
        }

        $delim             = $delim ?? $this->getCsvDelimiter($csvFilename);
        $fs                = \fopen($_FILES['csvfile']['tmp_name'], 'rb');
        $errorCount        = 0;
        $importDeleteDone  = false;
        $oldRedirectFormat = false;
        $defLanguage       = LanguageHelper::getDefaultLanguage();
        $rowIndex          = 2;
        if (\count($fields) === 0) {
            $fields = \fgetcsv($fs, 0, $delim);
        }
        $articleNoPresent = false;
        $destUrlPresent   = false;
        foreach ($fields as &$field) {
            if ($field === 'sourceurl') {
                $field             = 'cFromUrl';
                $oldRedirectFormat = true;
            } elseif ($field === 'destinationurl') {
                $field             = 'cToUrl';
                $oldRedirectFormat = true;
                $destUrlPresent    = true;
            } elseif ($field === 'articlenumber') {
                $field             = 'cArtNr';
                $oldRedirectFormat = true;
                $articleNoPresent  = true;
            } elseif ($field === 'languageiso') {
                $field             = 'cIso';
                $oldRedirectFormat = true;
            }
        }
        unset($field);

        if ($oldRedirectFormat) {
            if ($destUrlPresent === false && $articleNoPresent === false) {
                $this->errors[] = \__('csvImportNoArtNrOrDestUrl');

                return 1;
            }

            if ($destUrlPresent === true && $articleNoPresent === true) {
                $this->errors[] = \__('csvImportArtNrAndDestUrlError');
            }
        }
        if ($importType === 0 && \is_string($target)) {
            $this->db->query('TRUNCATE ' . $target);
        }
        while (($row = \fgetcsv($fs, 0, $delim)) !== false) {
            $obj = new stdClass();
            foreach ($fields as $i => $field) {
                $obj->$field = $row[$i];
            }
            if ($oldRedirectFormat) {
                $parsed = \parse_url($obj->cFromUrl);
                $from   = $parsed['path'];
                if (isset($parsed['query'])) {
                    $from .= '?' . $parsed['query'];
                }
                $obj->cFromUrl = $from;
            }
            if ($articleNoPresent) {
                $obj->cToUrl = $this->getArtNrUrl($obj->cArtNr, $obj->cIso ?? $defLanguage->cISO);
                if (empty($obj->cToUrl)) {
                    ++$errorCount;
                    $this->errors[] = \sprintf(\__('csvImportArtNrNotFound'), $obj->cArtNr);
                    continue;
                }
                unset($obj->cArtNr, $obj->cIso);
            }
            if (\is_callable($target)) {
                if ($target($obj, $importDeleteDone, $importType) === false) {
                    ++$errorCount;
                }
            } else { // is_string($target)
                $table = $target;
                if ($importType === 1) {
                    $this->db->delete($target, $fields, $row);
                }
                if ($this->db->insert($table, $obj) === 0) {
                    ++$errorCount;
                    $this->errors[] = \sprintf(\__('csvImportSaveError'), $rowIndex);
                }
            }

            ++$rowIndex;
        }

        return $errorCount;
    }

    /**
     * @param string $filename
     * @return string
     */
    protected function getCsvDelimiter(string $filename): string
    {
        $file      = \fopen($filename, 'rb');
        $firstLine = \fgets($file);

        foreach ([';', ',', '|', '\t'] as $delim) {
            if (mb_strpos($firstLine, $delim) !== false) {
                \fclose($file);

                return $delim;
            }
        }
        \fclose($file);

        return ';';
    }

    /**
     * @param string $artNo
     * @param string $iso
     * @return string|null
     */
    protected function getArtNrUrl(string $artNo, string $iso): ?string
    {
        if ($artNo === '') {
            return null;
        }

        $item = $this->db->getSingleObject(
            "SELECT tartikel.kArtikel, tseo.cSeo
                FROM tartikel
                LEFT JOIN tsprache
                    ON tsprache.cISO = :iso
                LEFT JOIN tseo
                    ON tseo.kKey = tartikel.kArtikel
                    AND tseo.cKey = 'kArtikel'
                    AND tseo.kSprache = tsprache.kSprache
                WHERE tartikel.cArtNr = :artno
                LIMIT 1",
            ['iso' => mb_convert_case($iso, MB_CASE_LOWER), 'artno' => $artNo]
        );

        return URL::buildURL($item, \URLART_ARTIKEL);
    }

    /**
     * @return string[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @param string[] $errors
     */
    public function setErrors(array $errors): void
    {
        $this->errors = $errors;
    }

    /**
     * @return DbInterface
     */
    public function getDB(): DbInterface
    {
        return $this->db;
    }

    /**
     * @param DbInterface $db
     */
    public function setDB(DbInterface $db): void
    {
        $this->db = $db;
    }
}
