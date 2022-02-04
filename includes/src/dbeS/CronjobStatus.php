<?php declare(strict_types=1);

namespace JTL\dbeS;

/**
 * Class CronjobStatus
 * @package JTL\dbeS
 */
class CronjobStatus
{
    /**
     * @var int
     */
    public int $kCron;

    /**
     * @var string
     */
    public string $cExportformat;

    /**
     * @var string
     */
    public string $cStartDate;

    /**
     * @var int
     */
    public int $nRepeat;

    /**
     * @var int
     */
    public int $nDone;

    /**
     * @var int
     */
    public int $nOverall;

    /**
     * @var string|null
     */
    public ?string $cLastStartDate;

    /**
     * @var string|null
     */
    public ?string $cNextStartDate;

    /**
     * @param int         $kCron
     * @param string      $cExportformat
     * @param string      $cStartDate
     * @param int         $frequency
     * @param int         $nDone
     * @param int         $nOverall
     * @param string|null $cLastStartDate
     * @param string|null $cNextStartDate
     */
    public function __construct(
        int $kCron,
        string $cExportformat,
        string $cStartDate,
        int $frequency,
        int $nDone,
        int $nOverall,
        ?string $cLastStartDate,
        ?string $cNextStartDate
    ) {
        $this->kCron          = $kCron;
        $this->cExportformat  = $cExportformat;
        $this->cStartDate     = $cStartDate;
        $this->nRepeat        = $frequency;
        $this->nDone          = $nDone;
        $this->nOverall       = $nOverall;
        $this->cLastStartDate = $cLastStartDate;
        $this->cNextStartDate = $cNextStartDate;
    }
}
