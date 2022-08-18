<?php declare(strict_types=1);

namespace JTL\Smarty;

use DateTime;
use JTL\Backend\Revision;
use JTL\Catalog\Currency;
use JTL\DB\DbInterface;
use JTL\Shop;
use JTL\Update\Updater;
use Smarty_Internal_Template;

/**
 * Class BackendPlugins
 * @package JTL\Smarty
 */
class BackendPlugins
{
    /**
     * @param DbInterface $db
     */
    public function __construct(private DbInterface $db)
    {
    }

    /**
     * @param array                    $params
     * @param Smarty_Internal_Template $smarty
     * @return string
     */
    public function getRevisions(array $params, Smarty_Internal_Template $smarty): string
    {
        return $smarty->assign('secondary', $params['secondary'] ?? false)
            ->assign('data', $params['data'] ?? null)
            ->assign('show', $params['show'])
            ->assign('revisions', (new Revision($this->db))->getRevisions($params['type'], (int)$params['key']))
            ->fetch('tpl_inc/revisions.tpl');
    }

    /**
     * @param array $params
     * @return string
     */
    public function getCurrencyConversionSmarty(array $params): string
    {
        $forceTax = !(isset($params['bSteuer']) && $params['bSteuer'] === false);
        if (!isset($params['fPreisBrutto'])) {
            $params['fPreisBrutto'] = 0;
        }
        if (!isset($params['fPreisNetto'])) {
            $params['fPreisNetto'] = 0;
        }
        if (!isset($params['cClass'])) {
            $params['cClass'] = '';
        }

        return Currency::getCurrencyConversion(
            $params['fPreisNetto'],
            $params['fPreisBrutto'],
            $params['cClass'],
            $forceTax
        );
    }

    /**
     * @param array $params
     * @return string
     */
    public function getCurrencyConversionTooltipButton(array $params): string
    {
        $placement = $params['placement'] ?? 'left';

        if (!isset($params['inputId'])) {
            return '';
        }
        $inputId = $params['inputId'];
        $button  = '<button type="button" class="btn btn-tooltip btn-link px-1" id="' .
            $inputId . 'Tooltip" data-html="true"';
        $button .= ' data-toggle="tooltip" data-placement="' . $placement . '">';
        $button .= '<i class="fa fa-eur"></i></button>';

        return $button;
    }

    /**
     * @param array                    $params
     * @param Smarty_Internal_Template $smarty
     */
    public function getCurrentPage(array $params, Smarty_Internal_Template $smarty): void
    {
        $path = $_SERVER['REQUEST_URI'];
        $page = \basename($path, '.php');
        if ($page === \rtrim(\PFAD_ADMIN, '/')) {
            $page = 'index';
        }

        if (isset($params['assign'])) {
            $smarty->assign($params['assign'], $page);
        }
    }

    /**
     * @param array                    $params
     * @param Smarty_Internal_Template $smarty
     * @return string
     */
    public function getHelpDesc(array $params, Smarty_Internal_Template $smarty): string
    {
        $placement    = $params['placement'] ?? 'left';
        $cID          = !empty($params['cID']) ? $params['cID'] : null;
        $iconQuestion = !empty($params['iconQuestion']);
        $description  = isset($params['cDesc'])
            ? \str_replace('"', '\'', $params['cDesc'])
            : null;

        return $smarty->assign('placement', $placement)
            ->assign('cID', $cID)
            ->assign('description', $description)
            ->assign('iconQuestion', $iconQuestion)
            ->fetch('tpl_inc/help_description.tpl');
    }

    /**
     * @param mixed $permissions
     * @return bool
     * @deprecated since 5.2.0
     */
    public function permission(string $permissions): bool
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        $ok = false;
        if (!isset($_SESSION['AdminAccount'])) {
            return false;
        }
        if ((int)$_SESSION['AdminAccount']->oGroup->kAdminlogingruppe === \ADMINGROUP) {
            $ok = true;
        } else {
            $orExpressions = \explode('|', $permissions);
            foreach ($orExpressions as $flag) {
                $ok = \in_array($flag, $_SESSION['AdminAccount']->oGroup->oPermission_arr, true);
                if ($ok) {
                    break;
                }
            }
        }

        return $ok;
    }

    /**
     * @param array                    $params
     * @param Smarty_Internal_Template $smarty
     * @return string
     */
    public function convertDate(array $params, Smarty_Internal_Template $smarty): string
    {
        if (isset($params['date']) && \mb_strlen($params['date']) > 0) {
            $dateTime = new DateTime($params['date']);
            if (isset($params['format']) && \mb_strlen($params['format']) > 1) {
                $date = $dateTime->format($params['format']);
            } else {
                $date = $dateTime->format('d.m.Y H:i:s');
            }

            if (isset($params['assign'])) {
                $smarty->assign($params['assign'], $date);
            } else {
                return $date;
            }
        }

        return '';
    }

    /**
     * Map marketplace categoryId to localized category name
     *
     * @param array                    $params
     * @param Smarty_Internal_Template $smarty
     */
    public function getExtensionCategory(array $params, Smarty_Internal_Template $smarty): void
    {
        if (!isset($params['cat'])) {
            return;
        }
        $catNames = [
            4  => 'Templates/Themes',
            5  => 'Sprachpakete',
            6  => 'Druckvorlagen',
            7  => 'Tools',
            8  => 'Marketing',
            9  => 'Zahlungsarten',
            10 => 'Import/Export',
            11 => 'SEO',
            12 => 'Auswertungen'
        ];
        $smarty->assign('catName', $catNames[$params['cat']] ?? null);
    }

    /**
     * @param array $params
     * @return string|null
     */
    public function formatVersion(array $params): ?string
    {
        if (!isset($params['value'])) {
            return null;
        }

        return \substr_replace((string)(int)$params['value'], '.', 1, 0);
    }

    /**
     * @param array $params
     * @return string
     */
    public function getAvatar(array $params): string
    {
        $url = ($params['account']->attributes['useAvatar']->cAttribValue ?? '') === 'Ux'
            ? $params['account']->attributes['useAvatarUpload']->cAttribValue
            : Shop::getAdminURL() . '/templates/bootstrap/gfx/avatar-default.svg';
        if (!(new Updater($this->db))->hasPendingUpdates()) {
            \executeHook(\HOOK_BACKEND_FUNCTIONS_GRAVATAR, [
                'url'          => &$url,
                'AdminAccount' => $_SESSION['AdminAccount']
            ]);
        }

        return $url;
    }

    /**
     * @param array                    $params
     * @param Smarty_Internal_Template $smarty
     * @return string
     */
    public function captchaMarkup(array $params, Smarty_Internal_Template $smarty): string
    {
        if ($params['getBody'] ?? false) {
            return Shop::Container()->getCaptchaService()->getBodyMarkup($smarty);
        }

        return Shop::Container()->getCaptchaService()->getHeadMarkup($smarty);
    }
}
