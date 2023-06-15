<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Notification;
use JTL\Backend\NotificationEntry;
use JTL\Backend\Permissions;
use JTL\Backend\Settings\Manager;
use JTL\Backend\Settings\SectionFactory;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Router\Route;
use JTL\Shop;
use JTL\Shopsetting;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class SearchConfigController
 * @package JTL\Router\Controller\Backend
 */
class SearchConfigController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions(Permissions::SETTINGS_ARTICLEOVERVIEW_VIEW);
        $this->getText->loadAdminLocale('pages/sucheinstellungen');
        $this->getText->loadAdminLocale('pages/einstellungen');

        $sectionID        = \CONF_ARTIKELUEBERSICHT;
        $conf             = Shop::getSettings([$sectionID]);
        $standardwaehrung = $this->db->select('twaehrung', 'cStandard', 'Y');
        $mysqlVersion     = $this->db->getSingleObject(
            "SHOW VARIABLES LIKE 'innodb_version'"
        )->Value ?? '5.6';
        $step             = 'einstellungen bearbeiten';
        $createIndex      = false;
        $sectionFactory   = new SectionFactory();
        $settingManager   = new Manager($this->db, $smarty, $this->account, $this->getText, $this->alertService);

        if (Request::postInt('einstellungen_bearbeiten') === 1 && Form::validateToken()) {
            $sucheFulltext = \in_array(Request::postVar('suche_fulltext', []), ['Y', 'B'], true);
            if ($sucheFulltext) {
                if (\version_compare($mysqlVersion, '5.6', '<')) {
                    /*
                     * Volltextindizes werden von MySQL mit InnoDB erst ab Version 5.6 unterstützt.
                     * Since MariaDB 10.0, the default InnoDB implementation is based on InnoDB from MySQL 5.6.
                     * Since MariaDB 10.3.7 and later, the InnoDB implementation has diverged substantially from the
                     * InnoDB in MySQL and the InnoDB Version is no longer reported.
                     */
                    $_POST['suche_fulltext'] = 'N';
                    $this->alertService->addError(\__('errorFulltextSearchMYSQL'), 'errorFulltextSearchMYSQL');
                } else {
                    // Bei Volltextsuche die Mindeswortlänge an den DB-Parameter anpassen
                    $currentVal = $this->db->getSingleObject('SELECT @@ft_min_word_len AS ft_min_word_len');
                    if (($currentVal->ft_min_word_len ?? $_POST['suche_min_zeichen']) !== $_POST['suche_min_zeichen']) {
                        $_POST['suche_min_zeichen'] = $currentVal->ft_min_word_len;
                        $this->alertService->addWarning(\__('errorFulltextSearchMinLen'), 'errorFulltextSearchMinLen');
                    }
                }
            }

            $shopSettings = Shopsetting::getInstance();
            $this->saveAdminSectionSettings($sectionID, $_POST);

            $this->cache->flushTags(
                [\CACHING_GROUP_OPTION, \CACHING_GROUP_CORE, \CACHING_GROUP_ARTICLE, \CACHING_GROUP_CATEGORY]
            );
            $shopSettings->reset();

            $fulltextChanged = false;
            foreach ([
                         'suche_fulltext',
                         'suche_prio_name',
                         'suche_prio_suchbegriffe',
                         'suche_prio_artikelnummer',
                         'suche_prio_kurzbeschreibung',
                         'suche_prio_beschreibung',
                         'suche_prio_ean',
                         'suche_prio_isbn',
                         'suche_prio_han',
                         'suche_prio_anmerkung'
                     ] as $sucheParam) {
                if (isset($_POST[$sucheParam]) && ($_POST[$sucheParam] != $conf['artikeluebersicht'][$sucheParam])) {
                    $fulltextChanged = true;
                    break;
                }
            }
            if ($fulltextChanged) {
                $createIndex = $sucheFulltext ? 'Y' : 'N';
            }

            if ($sucheFulltext && $fulltextChanged) {
                $this->alertService->addSuccess(\__('successSearchActivate'), 'successSearchActivate');
            } elseif ($fulltextChanged) {
                $this->alertService->addSuccess(\__('successSearchDeactivate'), 'successSearchDeactivate');
            }

            $conf = Shop::getSettings([$sectionID]);
        }

        $section = $sectionFactory->getSection($sectionID, $settingManager);
        $section->load();
        if ($conf['artikeluebersicht']['suche_fulltext'] !== 'N'
            && (!$this->db->getSingleObject("SHOW INDEX FROM tartikel WHERE KEY_NAME = 'idx_tartikel_fulltext'")
                || !$this->db->getSingleObject(
                    "SHOW INDEX FROM tartikelsprache WHERE KEY_NAME = 'idx_tartikelsprache_fulltext'"
                ))
        ) {
            $this->alertService->addError(
                \__('errorCreateTime') .
                '<a href="' . $this->baseURL . $this->route
                . '" title="Aktualisieren"><i class="alert-danger fa fa-refresh"></i></a>',
                'errorCreateTime'
            );
            Notification::getInstance($this->db)->add(
                NotificationEntry::TYPE_WARNING,
                \__('indexCreate'),
                $this->baseURL . '/' . Route::SEARCHCONFIG
            );
        }
        $this->getAdminSectionSettings(\CONF_ARTIKELUEBERSICHT);

        return $smarty->assign('kEinstellungenSektion', $sectionID)
            ->assign('sections', [$section])
            ->assign('cPrefURL', $smarty->getConfigVars('prefURL' . $sectionID))
            ->assign('step', $step)
            ->assign('supportFulltext', \version_compare($mysqlVersion, '5.6', '>='))
            ->assign('createIndex', $createIndex)
            ->assign('waehrung', $standardwaehrung->cName ?? '')
            ->assign('route', $this->route)
            ->getResponse('sucheinstellungen.tpl');
    }
}
