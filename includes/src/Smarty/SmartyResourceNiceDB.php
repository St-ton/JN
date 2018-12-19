<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Smarty;

use DB\DbInterface;
use DB\ReturnType;
use Smarty_Resource_Custom;

/**
 * Class SmartyResourceNiceDB
 * @package Smarty
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
                $cTableSprache = 'temailvorlagesprache';
                if (isset($pcs[3]) && (int)$pcs[3] > 0) {
                    $cTableSprache = 'tpluginemailvorlagesprache';
                }
                $vl = $this->db->select(
                    $cTableSprache,
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
                    \Shop::Container()->getLogService()->notice('Ungueltiger Emailvorlagen-Typ: ' . $pcs[0]);
                }
            } else {
                $source = '';
                \Shop::Container()->getLogService()->notice(
                    'Emailvorlage mit der ID ' . (int)$pcs[1] .
                    ' in der Sprache ' . (int)$pcs[2] . ' wurde nicht gefunden'
                );
            }
        } elseif ($this->type === 'newsletter') {
            $cTeile_arr = \explode('_', $name);
            $cTabelle   = 'tnewslettervorlage';
            $cFeld      = 'kNewsletterVorlage';
            if ($cTeile_arr[0] === 'NL') {
                $cTabelle = 'tnewsletter';
                $cFeld    = 'kNewsletter';
            }
            $oNewsletter = $this->db->select($cTabelle, $cFeld, $cTeile_arr[1]);

            if ($cTeile_arr[2] === 'html') {
                $source = $oNewsletter->cInhaltHTML;
            } elseif ($cTeile_arr[2] === 'text') {
                $source = $oNewsletter->cInhaltText;
            }
        } else {
            $source = '';
            \Shop::Container()->getLogService()->notice('Template-Typ ' . $this->type . ' wurde nicht gefunden');
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
