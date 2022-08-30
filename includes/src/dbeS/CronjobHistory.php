<?php declare(strict_types=1);

namespace JTL\dbeS;

/**
 * Class CronjobHistory
 * @package JTL\dbeS
 */
class CronjobHistory
{
    /**
     * @param string $cExportformat
     * @param string $cDateiname
     * @param int    $nDone
     * @param string $cLastStartDate
     */
    public function __construct(
        public string $cExportformat,
        public string $cDateiname,
        public int    $nDone,
        public string $cLastStartDate
    ) {
    }
}
