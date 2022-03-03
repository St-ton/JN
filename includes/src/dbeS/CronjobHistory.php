<?php declare(strict_types=1);

namespace JTL\dbeS;

/**
 * Class CronjobHistory
 * @package JTL\dbeS
 */
class CronjobHistory
{
    /**
     * @var string
     */
    public string $cExportformat;

    /**
     * @var string
     */
    public string $cDateiname;

    /**
     * @var int
     */
    public int $nDone;

    /**
     * @var string
     */
    public string $cLastStartDate;

    /**
     * @param string $name
     * @param string $fileName
     * @param int    $done
     * @param string $lastStartDate
     */
    public function __construct(string $name, string $fileName, int $done, string $lastStartDate)
    {
        $this->cExportformat  = $name;
        $this->cDateiname     = $fileName;
        $this->nDone          = $done;
        $this->cLastStartDate = $lastStartDate;
    }
}
