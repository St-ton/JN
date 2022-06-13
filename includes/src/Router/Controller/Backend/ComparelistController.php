<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Permissions;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Pagination\Pagination;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

/**
 * Class ComparelistController
 * @package JTL\Router\Controller\Backend
 */
class ComparelistController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions(Permissions::MODULE_COMPARELIST_VIEW);
        $this->getText->loadAdminLocale('pages/vergleichsliste');
        $this->getText->loadConfigLocales(true, true);

        if (!isset($_SESSION['Vergleichsliste'])) {
            $_SESSION['Vergleichsliste'] = new stdClass();
        }
        $_SESSION['Vergleichsliste']->nZeitFilter = 1;
        $_SESSION['Vergleichsliste']->nAnzahl     = 10;
        if (Request::postInt('zeitfilter') === 1) {
            $_SESSION['Vergleichsliste']->nZeitFilter = Request::postInt('nZeitFilter');
            $_SESSION['Vergleichsliste']->nAnzahl     = Request::postInt('nAnzahl');
        }

        if ((Request::postInt('einstellungen') === 1 || Request::postVar('resetSetting') !== null)
            && Form::validateToken()
        ) {
            $this->saveAdminSectionSettings(\CONF_VERGLEICHSLISTE, $_POST);
        }

        $listCount  = (int)$this->db->getSingleObject(
            'SELECT COUNT(*) AS cnt
                FROM tvergleichsliste'
        )->cnt;
        $pagination = (new Pagination())
            ->setItemCount($listCount)
            ->assemble();
        $last20     = $this->db->getObjects(
            "SELECT kVergleichsliste, DATE_FORMAT(dDate, '%d.%m.%Y  %H:%i') AS Datum
                FROM tvergleichsliste
                ORDER BY dDate DESC
                LIMIT " . $pagination->getLimitSQL()
        );

        if (\count($last20) > 0) {
            foreach ($last20 as $list) {
                $list->oLetzten20VergleichslistePos_arr = $this->db->selectAll(
                    'tvergleichslistepos',
                    'kVergleichsliste',
                    (int)$list->kVergleichsliste,
                    'kArtikel, cArtikelName'
                );
            }
        }
        $topComparisons = $this->db->getObjects(
            'SELECT tvergleichsliste.dDate, tvergleichslistepos.kArtikel, 
                tvergleichslistepos.cArtikelName, COUNT(tvergleichslistepos.kArtikel) AS nAnzahl
                FROM tvergleichsliste
                JOIN tvergleichslistepos 
                    ON tvergleichsliste.kVergleichsliste = tvergleichslistepos.kVergleichsliste
                WHERE DATE_SUB(NOW(), INTERVAL :ds DAY)  < tvergleichsliste.dDate
                GROUP BY tvergleichslistepos.kArtikel
                ORDER BY nAnzahl DESC
                LIMIT :lmt',
            [
                'ds' => (int)$_SESSION['Vergleichsliste']->nZeitFilter,
                'lmt' => (int)$_SESSION['Vergleichsliste']->nAnzahl
            ]
        );
        if (\count($topComparisons) > 0) {
            $this->createDiagram($topComparisons);
        }
        $this->getAdminSectionSettings(\CONF_VERGLEICHSLISTE);

        return $smarty->assign('Letzten20Vergleiche', $last20)
            ->assign('TopVergleiche', $topComparisons)
            ->assign('pagination', $pagination)
            ->assign('route', $this->route)
            ->getResponse('vergleichsliste.tpl');
    }

    /**
     * @param array $topCompareLists
     * @former erstelleDiagrammTopVergleiche()
     */
    private function createDiagram(array $topCompareLists): void
    {
        unset($_SESSION['oGraphData_arr'], $_SESSION['nYmax'], $_SESSION['nDiagrammTyp']);
        $graphData = [];
        if (\count($topCompareLists) === 0) {
            return;
        }
        $yMax                     = []; // Y-Achsen Werte um spaeter den Max Wert zu erlangen
        $_SESSION['nDiagrammTyp'] = 4;

        foreach ($topCompareLists as $i => $list) {
            $top               = new stdClass();
            $top->nAnzahl      = $list->nAnzahl;
            $top->cArtikelName = $this->checkName($list->cArtikelName);
            $graphData[]       = $top;
            $yMax[]            = $list->nAnzahl;
            unset($top);

            if ($i >= (int)$_SESSION['Vergleichsliste']->nAnzahl) {
                break;
            }
        }
        // Naechst hoehere Zahl berechnen fuer die Y-Balkenbeschriftung
        if (\count($yMax) > 0) {
            $fMax = (float)\max($yMax);
            if ($fMax > 10) {
                $temp  = 10 ** \floor(\log10($fMax));
                $nYmax = \ceil($fMax / $temp) * $temp;
            } else {
                $nYmax = 10;
            }
            $_SESSION['nYmax'] = $nYmax;
        }

        $_SESSION['oGraphData_arr'] = $graphData;
    }

    /**
     * Hilfsfunktion zur Regulierung der X-Achsen-Werte
     *
     * @param string $name
     * @return string
     */
    private function checkName(string $name): string
    {
        $name = \stripslashes(\trim(\str_replace([';', '_', '#', '%', '$', ':', '"'], '', $name)));
        if (\mb_strlen($name) > 20) {
            $name = \mb_substr($name, 0, 20) . '...';
        }

        return $name;
    }
}
