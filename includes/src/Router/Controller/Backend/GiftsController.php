<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Permissions;
use JTL\Catalog\Product\Artikel;
use JTL\Helpers\Request;
use JTL\Pagination\Pagination;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class GiftsController
 * @package JTL\Router\Controller\Backend
 */
class GiftsController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions(Permissions::MODULE_GIFT_VIEW);
        $this->getText->loadAdminLocale('pages/gratisgeschenk');

        $settingsIDs = [
            'configgroup_10_gifts',
            'sonstiges_gratisgeschenk_nutzen',
            'sonstiges_gratisgeschenk_anzahl',
            'sonstiges_gratisgeschenk_sortierung'
        ];

        if (Request::verifyGPCDataInt('einstellungen') === 1) {
            $this->alertService->addSuccess(
                $this->saveAdminSettings($settingsIDs, $_POST, [\CACHING_GROUP_OPTION], true),
                'saveSettings'
            );
        }
        $paginationActive  = (new Pagination('aktiv'))
            ->setItemCount($this->getActiveCount())
            ->assemble();
        $paginationCommon  = (new Pagination('haeufig'))
            ->setItemCount($this->getCommonCount())
            ->assemble();
        $paginationLast100 = (new Pagination('letzte100'))
            ->setItemCount($this->getRecentCount())
            ->assemble();
        $this->getAdminSectionSettings($settingsIDs, true);

        return $smarty->assign('oPagiAktiv', $paginationActive)
            ->assign('oPagiHaeufig', $paginationCommon)
            ->assign('oPagiLetzte100', $paginationLast100)
            ->assign('route', $this->route)
            ->assign(
                'oAktiveGeschenk_arr',
                $this->getActive(' LIMIT ' . $paginationActive->getLimitSQL())
            )
            ->assign(
                'oHaeufigGeschenk_arr',
                $this->getCommon(' LIMIT ' . $paginationCommon->getLimitSQL())
            )
            ->assign(
                'oLetzten100Geschenk_arr',
                $this->getRecent100(' LIMIT ' . $paginationLast100->getLimitSQL())
            )
            ->getResponse('gratisgeschenk.tpl');
    }
    /**
     * @param string $sql
     * @return array
     * @former holeAktiveGeschenke()
     */
    private function getActive(string $sql): array
    {
        $res = [];
        if (\mb_strlen($sql) < 1) {
            return $res;
        }
        $data = $this->db->getInts(
            'SELECT kArtikel
                FROM tartikelattribut
                WHERE cName = :atr
                ORDER BY CAST(cWert AS SIGNED) DESC ' . $sql,
            'kArtikel',
            ['atr' => \ART_ATTRIBUT_GRATISGESCHENKAB]
        );

        $options                            = Artikel::getDefaultOptions();
        $options->nKeinLagerbestandBeachten = 1;
        foreach ($data as $productID) {
            $product = new Artikel($this->db);
            $product->fuelleArtikel($productID, $options, 0, 0, true);
            if ($product->kArtikel > 0) {
                $res[] = $product;
            }
        }

        return $res;
    }

    /**
     * @param string $sql
     * @return array
     * @former holeHaeufigeGeschenke()
     */
    private function getCommon(string $sql): array
    {
        $res = [];
        if (\mb_strlen($sql) < 1) {
            return $res;
        }
        $data = $this->db->getObjects(
            'SELECT tgratisgeschenk.kArtikel, COUNT(*) AS nAnzahl, 
            MAX(tbestellung.dErstellt) AS lastOrdered, AVG(tbestellung.fGesamtsumme) AS avgOrderValue
                FROM tgratisgeschenk
                LEFT JOIN tbestellung
                    ON tbestellung.kWarenkorb = tgratisgeschenk.kWarenkorb
                GROUP BY tgratisgeschenk.kArtikel
                ORDER BY nAnzahl DESC, lastOrdered DESC ' . $sql
        );

        $options                            = Artikel::getDefaultOptions();
        $options->nKeinLagerbestandBeachten = 1;
        foreach ($data as $item) {
            $product = new Artikel($this->db);
            $product->fuelleArtikel((int)$item->kArtikel, $options, 0, 0, true);
            if ($product->kArtikel > 0) {
                $product->nGGAnzahl = (int)$item->nAnzahl;
                $res[]              = (object)[
                    'artikel'       => $product,
                    'lastOrdered'   => \date_format(\date_create($item->lastOrdered), 'd.m.Y H:i:s'),
                    'avgOrderValue' => $item->avgOrderValue
                ];
            }
        }

        return $res;
    }

    /**
     * @param string $sql
     * @return array
     * @former holeLetzten100Geschenke()
     */
    private function getRecent100(string $sql): array
    {
        $res = [];
        if (\mb_strlen($sql) < 1) {
            return $res;
        }
        $data                               = $this->db->getObjects(
            'SELECT tgratisgeschenk.*, tbestellung.dErstellt AS orderCreated, tbestellung.fGesamtsumme
            FROM tgratisgeschenk
              LEFT JOIN tbestellung 
                  ON tbestellung.kWarenkorb = tgratisgeschenk.kWarenkorb
            ORDER BY tbestellung.dErstellt DESC ' . $sql
        );
        $options                            = Artikel::getDefaultOptions();
        $options->nKeinLagerbestandBeachten = 1;
        foreach ($data as $item) {
            $product = new Artikel($this->db);
            $product->fuelleArtikel((int)$item->kArtikel, $options, 0, 0, true);
            if ($product->kArtikel > 0) {
                $product->nGGAnzahl = (int)$item->nAnzahl;
                $res[]              = (object)[
                    'artikel'      => $product,
                    'orderCreated' => \date_format(\date_create($item->orderCreated), 'd.m.Y H:i:s'),
                    'orderValue'   => $item->fGesamtsumme
                ];
            }
        }

        return $res;
    }

    /**
     * @return int
     * @former gibAnzahlAktiverGeschenke()
     */
    private function getActiveCount(): int
    {
        return $this->db->getSingleInt(
            'SELECT COUNT(*) AS cnt
                FROM tartikelattribut
                WHERE cName = :nm',
            'cnt',
            ['nm' => \ART_ATTRIBUT_GRATISGESCHENKAB]
        );
    }

    /**
     * @return int
     * @former gibAnzahlHaeufigGekaufteGeschenke()
     */
    private function getCommonCount(): int
    {
        return $this->db->getSingleInt(
            'SELECT COUNT(DISTINCT(kArtikel)) AS cnt
                FROM twarenkorbpos
                WHERE nPosTyp = :tp',
            'cnt',
            ['tp' => \C_WARENKORBPOS_TYP_GRATISGESCHENK]
        );
    }

    /**
     * @return int
     * @former gibAnzahlLetzten100Geschenke()
     */
    private function getRecentCount(): int
    {
        return $this->db->getSingleInt(
            'SELECT COUNT(*) AS cnt
                FROM twarenkorbpos
                WHERE nPosTyp = :tp
                LIMIT 100',
            'cnt',
            ['tp' => \C_WARENKORBPOS_TYP_GRATISGESCHENK]
        );
    }
}
