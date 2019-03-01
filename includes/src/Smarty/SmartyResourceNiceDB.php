<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Smarty;

use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Shop;
use Smarty_Resource_Custom;

/**
 * Class SmartyResourceNiceDB
 * @package JTL\Smarty
 */
class SmartyResourceNiceDB extends Smarty_Resource_Custom
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * SmartyResourceNiceDB constructor.
     * @param DbInterface $db
     * @param string      $type
     */
    public function __construct(DbInterface $db, string $type = 'export')
    {
        $this->db   = $db;
        $this->type = $type;
    }

    /**
     * @param string $name
     * @param string $source
     * @param int    $mtime
     * @return bool|void
     */
    public function fetch($name, &$source, &$mtime)
    {
        if ($this->type === 'export') {
            $exportformat = $this->db->select('texportformat', 'kExportformat', (int)$name);
            if (empty($exportformat->kExportformat) || $exportformat->kExportformat <= 0) {
                return false;
            }
            $source = $exportformat->cContent;
        } elseif ($this->type === 'mail') {
            $pcs = \explode('_', $name);
            if (isset($pcs[0], $pcs[1], $pcs[2], $pcs[3]) && $pcs[3] === 'anbieterkennzeichnung') {
                // Anbieterkennzeichnungsvorlage holen
                $vl = $this->db->queryPrepared(
                    "SELECT tevs.cContentHtml, tevs.cContentText
                        FROM temailvorlageoriginal tevo
                        JOIN temailvorlagesprache tevs
                            ON tevs.kEmailVorlage = tevo.kEmailvorlage
                            AND tevs.kSprache = :langID
                        WHERE tevo.cModulId = 'core_jtl_anbieterkennzeichnung'",
                    ['langID' => (int)$pcs[4]],
                    ReturnType::SINGLE_OBJECT
                );
            } else {
                // Plugin Emailvorlage?
                $table = 'temailvorlagesprache';
                if (isset($pcs[3]) && (int)$pcs[3] > 0) {
                    $table = 'tpluginemailvorlagesprache';
                }
                $vl = $this->db->select(
                    $table,
                    ['kEmailvorlage', 'kSprache'],
                    [(int)$pcs[1], (int)$pcs[2]]
                );
            }
            if ($vl !== null && $vl !== false) {
                if ($pcs[0] === 'html') {
                    $source = $vl->cContentHtml;
                } elseif ($pcs[0] === 'text') {
                    $source = $vl->cContentText;
                } else {
                    $source = '';
                    Shop::Container()->getLogService()->notice('Ungueltiger Emailvorlagen-Typ: ' . $pcs[0]);
                }
            } else {
                $source = '';
                Shop::Container()->getLogService()->notice(
                    'Emailvorlage mit der ID ' . (int)$pcs[1] .
                    ' in der Sprache ' . (int)$pcs[2] . ' wurde nicht gefunden'
                );
            }
        } elseif ($this->type === 'newsletter') {
            $parts = \explode('_', $name);
            $table = 'tnewslettervorlage';
            $row   = 'kNewsletterVorlage';
            if ($parts[0] === 'NL') {
                $table = 'tnewsletter';
                $row   = 'kNewsletter';
            }
            $newsletter = $this->db->select($table, $row, $parts[1]);
            if ($parts[2] === 'html') {
                $source = $newsletter->cInhaltHTML;
            } elseif ($parts[2] === 'text') {
                $source = $newsletter->cInhaltText;
            }
        } else {
            $source = '';
            Shop::Container()->getLogService()->notice('Template-Typ ' . $this->type . ' wurde nicht gefunden');
        }
    }

    /**
     * @param string $name
     * @return int
     */
    protected function fetchTimestamp($name): int
    {
        return \time();
    }
}
