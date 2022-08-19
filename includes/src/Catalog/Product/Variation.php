<?php declare(strict_types=1);

namespace JTL\Catalog\Product;

use stdClass;

/**
 * Class Variation
 * @package JTL\Catalog\Product
 */
class Variation
{
    /**
     * @var array
     */
    public array $Werte = [];

    /**
     * @var int
     */
    public int $kEigenschaft;

    /**
     * @var int
     */
    public int $kArtikel;

    /**
     * @var string
     */
    public string $cWaehlbar;

    /**
     * @var string
     */
    public string $cTyp;

    /**
     * @var int
     */
    public int $nSort;

    /**
     * @var string
     */
    public string $cName;

    /**
     * @var int
     */
    public int $nLieferbareVariationswerte = 0;

    /**
     * @param stdClass $data
     */
    public function init(stdClass $data): void
    {
        $this->kEigenschaft = (int)$data->kEigenschaft;
        $this->kArtikel     = (int)$data->kArtikel;
        $this->cWaehlbar    = $data->cWaehlbar;
        $this->cTyp         = $data->cTyp;
        $this->nSort        = (int)$data->nSort;
        $this->cName        = empty($data->cName_teigenschaftsprache)
            ? $data->cName
            : $data->cName_teigenschaftsprache;
        if ($data->cTyp === 'FREIFELD' || $data->cTyp === 'PFLICHT-FREIFELD') {
            $this->nLieferbareVariationswerte = 1;
        }
    }
}
