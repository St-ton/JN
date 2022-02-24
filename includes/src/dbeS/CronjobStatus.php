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
    public $kCron;

    /**
     * @var string
     */
    public $cExportformat;

    /**
     * @var string
     */
    public $cStartDate;

    /**
     * @var int
     */
    public $nRepeat;

    /**
     * @var int
     */
    public $nDone;

    /**
     * @var int
     */
    public $nOverall;

    /**
     * @var string
     */
    public $cLastStartDate;

    /**
     * @var string
     */
    public $cNextStartDate;

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
