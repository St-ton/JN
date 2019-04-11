<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Mail\Admin;

use JTL\Customer\Kundengruppe;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Helpers\Text;
use JTL\Mail\Template\Model;
use JTL\Mail\Template\TemplateFactory;
use JTL\Mail\Template\TemplateInterface;
use JTL\Shop;
use JTL\Sprache;
use stdClass;

/**
 * Class Controller
 * @package JTL\Mail\Admin
 */
final class Controller
{
    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var array
     */
    private $config;

    /**
     * @var TemplateFactory
     */
    private $factory;

    /**
     * Controller constructor.
     * @param DbInterface     $db
     * @param TemplateFactory $factory
     * @param array           $config
     */
    public function __construct(DbInterface $db, TemplateFactory $factory, array $config)
    {
        $this->db      = $db;
        $this->factory = $factory;
        $this->config  = $config;
    }

    /**
     * @param int $templateID
     * @param int $pluginID
     * @return bool
     */
    public function resetTemplate(int $templateID, int $pluginID): bool
    {
        if ($pluginID > 0) {
            $this->db->delete(
                'tpluginemailvorlagesprache',
                'kEmailvorlage',
                $templateID
            );
            $this->db->query(
                'INSERT INTO tpluginemailvorlagesprache
                    SELECT *
                    FROM tpluginemailvorlagespracheoriginal
                    WHERE tpluginemailvorlagespracheoriginal.kEmailvorlage = ' . $templateID,
                ReturnType::DEFAULT
            );

            return true;
        }

        $this->db->queryPrepared(
            'DELETE temailvorlage, temailvorlagesprache
                FROM temailvorlage
                LEFT JOIN temailvorlagesprache
                    ON temailvorlagesprache.kEmailvorlage = temailvorlage.kEmailvorlage
                WHERE temailvorlage.kEmailvorlage = :tid',
            ['tid' => $templateID],
            ReturnType::DEFAULT
        );
        $this->db->queryPrepared(
            'INSERT INTO temailvorlage
                SELECT *
                FROM temailvorlageoriginal
                WHERE temailvorlageoriginal.kEmailvorlage = :tid',
            ['tid' => $templateID],
            ReturnType::DEFAULT
        );
        $this->db->queryPrepared(
            'INSERT INTO temailvorlagesprache
                SELECT *
                FROM temailvorlagespracheoriginal
                WHERE temailvorlagespracheoriginal.kEmailvorlage = :tid',
            ['tid' => $templateID],
            ReturnType::DEFAULT
        );
        $data = $this->db->select(
            'temailvorlageoriginal',
            'kEmailvorlage',
            $templateID
        );
        if (isset($data->cDateiname) && mb_strlen($data->cDateiname) > 0) {
            $this->resetFromFile($templateID, $data);
        }

        return true;
    }

    /**
     * @param int      $templateID
     * @param stdClass $data
     * @return int
     */
    private function resetFromFile(int $templateID, stdClass $data): int
    {
        $affected = 0;
        foreach (Sprache::getAllLanguages() as $lang) {
            $path      = \PFAD_ROOT . \PFAD_EMAILVORLAGEN . $lang->cISO;
            $fileHtml  = $path . '/' . $data->cDateiname . '_html.tpl';
            $filePlain = $path . '/' . $data->cDateiname . '_plain.tpl';
            if (!isset($lang->cISO)
                || !\file_exists(\PFAD_ROOT . \PFAD_EMAILVORLAGEN . $lang->cISO)
                || !\file_exists($fileHtml)
                || !\file_exists($filePlain)
            ) {
                continue;
            }
            $upd               = new stdClass();
            $html              = \file_get_contents($fileHtml);
            $text              = \file_get_contents($filePlain);
            $doDecodeHtml      = \function_exists('mb_detect_encoding')
                ? (\mb_detect_encoding($html, ['UTF-8', 'ISO-8859-1', 'ISO-8859-15'], true) !== 'UTF-8')
                : (Text::is_utf8($html) === 1);
            $doDecodeText      = \function_exists('mb_detect_encoding')
                ? (\mb_detect_encoding($text, ['UTF-8', 'ISO-8859-1', 'ISO-8859-15'], true) !== 'UTF-8')
                : (Text::is_utf8($text) === 1);
            $upd->cContentHtml = $doDecodeHtml === true ? Text::convertUTF8($html) : $html;
            $upd->cContentText = $doDecodeText === true ? Text::convertUTF8($text) : $text;
            $affected         += $this->db->update(
                'temailvorlagesprache',
                ['kEmailVorlage', 'kSprache'],
                [$templateID, (int)$lang->kSprache],
                $upd
            );
        }

        return $affected;
    }

    /**
     * @param int $templateID
     * @return Model|null
     */
    public function getTemplateByID(int $templateID): ?Model
    {
        $mailTpl = $this->factory->getTemplateByID($templateID);
        if ($mailTpl !== null) {
            $mailTpl->load(1, 1); // @todo
        }

        return $mailTpl->getModel();
    }

    /**
     * @return TemplateInterface[]
     */
    public function getAllTemplates(): array
    {
        $templates   = [];
        $templateIDs = \array_merge(
            $this->db->selectAll('temailvorlage', [], [], 'cModulId'),
            $this->db->selectAll('tpluginemailvorlage', [], [], 'cModulId, kPlugin')
        );
        $langID      = Sprache::getDefaultLanguage()->kSprache;
        $cgroupID    = Kundengruppe::getDefaultGroupID();
        foreach ($templateIDs as $templateID) {
            $module = $templateID->cModulId;
            if (isset($templateID->kPlugin) && $templateID->kPlugin > 0) {
                $module = 'kPlugin_' . $templateID->kPlugin . '_' . $templateID->cModulId;
            }
            if (($template = $this->factory->getTemplate($module)) !== null) {
                $template->load($langID, $cgroupID);
                $templates[] = $template->getModel();
            }
        }

        return $templates;
    }
}
