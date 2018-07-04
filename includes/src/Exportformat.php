<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Exportformat
 */
class Exportformat
{
    /**
     * @var int
     */
    protected $kExportformat;

    /**
     * @var int
     */
    protected $kKundengruppe;

    /**
     * @var int
     */
    protected $kSprache;

    /**
     * @var int
     */
    protected $kWaehrung;

    /**
     * @var int
     */
    protected $kKampagne;

    /**
     * @var int
     */
    protected $kPlugin;

    /**
     * @var string
     */
    protected $cName;

    /**
     * @var string
     */
    protected $cDateiname;

    /**
     * @var string
     */
    protected $cKopfzeile;

    /**
     * @var string
     */
    protected $cContent;

    /**
     * @var string
     */
    protected $cFusszeile;

    /**
     * @var string
     */
    protected $cKodierung;

    /**
     * @var int
     */
    protected $nSpecial;

    /**
     * @var int
     */
    protected $nVarKombiOption;

    /**
     * @var int
     */
    protected $nSplitgroesse;

    /**
     * @var string
     */
    protected $dZuletztErstellt;

    /**
     * @var int
     */
    protected $nUseCache = 1;

    /**
     * @var JTLSmarty
     */
    protected $smarty;

    /**
     * @var object|null
     */
    private $oldSession;

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var object
     */
    protected $queue;

    /**
     * @var object
     */
    protected $currency;

    /**
     * @var string|null
     */
    private $campaignParameter;

    /**
     * @var string|null
     */
    private $campaignValue;

    /**
     * @var bool
     */
    private $isOk = false;

    /**
     * @var string
     */
    private $tempFileName;

    /**
     * Exportformat constructor.
     *
     * @param int $kExportformat
     */
    public function __construct(int $kExportformat = 0)
    {
        if ($kExportformat > 0) {
            $this->loadFromDB($kExportformat);
        }
    }

    /**
     * Loads database member into class member
     *
     * @param int $kExportformat
     * @return $this
     */
    private function loadFromDB(int $kExportformat = 0): self
    {
        $oObj = Shop::Container()->getDB()->query(
            "SELECT texportformat.*, tkampagne.cParameter AS campaignParameter, tkampagne.cWert AS campaignValue
               FROM texportformat
               LEFT JOIN tkampagne 
                  ON tkampagne.kKampagne = texportformat.kKampagne
                  AND tkampagne.nAktiv = 1
               WHERE texportformat.kExportformat = " . $kExportformat,
            \DB\ReturnType::SINGLE_OBJECT
        );
        if (isset($oObj->kExportformat) && $oObj->kExportformat > 0) {
            foreach (get_object_vars($oObj) as $k => $v) {
                $this->$k = $v;
            }
            $confObj = Shop::Container()->getDB()->selectAll('texportformateinstellungen', 'kExportformat', $kExportformat);
            foreach ($confObj as $conf) {
                $this->config[$conf->cName] = $conf->cWert;
            }
            if (!isset($this->config['exportformate_lager_ueber_null'])) {
                $this->config['exportformate_lager_ueber_null'] = 'N';
            }
            if (!isset($this->config['exportformate_preis_ueber_null'])) {
                $this->config['exportformate_preis_ueber_null'] = 'N';
            }
            if (!isset($this->config['exportformate_beschreibung'])) {
                $this->config['exportformate_beschreibung'] = 'N';
            }
            if (!isset($this->config['exportformate_quot'])) {
                $this->config['exportformate_quot'] = 'N';
            }
            if (!isset($this->config['exportformate_equot'])) {
                $this->config['exportformate_equot'] = 'N';
            }
            if (!isset($this->config['exportformate_semikolon'])) {
                $this->config['exportformate_semikolon'] = 'N';
            }
            if (!$this->getKundengruppe()) {
                $this->setKundengruppe(Kundengruppe::getDefaultGroupID());
            }
            $this->isOk         = true;
            $this->tempFileName = 'tmp_' . $this->cDateiname;
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isOK(): bool
    {
        return $this->isOk;
    }

    /**
     * @param bool $bPrim
     * @return bool|int
     */
    public function save(bool $bPrim = true)
    {
        $ins                   = new stdClass();
        $ins->kKundengruppe    = (int)$this->kKundengruppe;
        $ins->kSprache         = (int)$this->kSprache;
        $ins->kWaehrung        = (int)$this->kWaehrung;
        $ins->kKampagne        = (int)$this->kKampagne;
        $ins->kPlugin          = (int)$this->kPlugin;
        $ins->cName            = $this->cName;
        $ins->cDateiname       = $this->cDateiname;
        $ins->cKopfzeile       = $this->cKopfzeile;
        $ins->cContent         = $this->cContent;
        $ins->cFusszeile       = $this->cFusszeile;
        $ins->cKodierung       = $this->cKodierung;
        $ins->nSpecial         = (int)$this->nSpecial;
        $ins->nVarKombiOption  = (int)$this->nVarKombiOption;
        $ins->nSplitgroesse    = (int)$this->nSplitgroesse;
        $ins->dZuletztErstellt = $this->dZuletztErstellt;
        $ins->nUseCache        = $this->nUseCache;

        $this->kExportformat = Shop::Container()->getDB()->insert('texportformat', $ins);
        if ($this->kExportformat > 0) {
            return $bPrim ? $this->kExportformat : true;
        }

        return false;
    }

    /**
     * @return int
     */
    public function update(): int
    {
        $upd                   = new stdClass();
        $upd->kKundengruppe    = (int)$this->kKundengruppe;
        $upd->kSprache         = (int)$this->kSprache;
        $upd->kWaehrung        = (int)$this->kWaehrung;
        $upd->kKampagne        = (int)$this->kKampagne;
        $upd->kPlugin          = (int)$this->kPlugin;
        $upd->cName            = $this->cName;
        $upd->cDateiname       = $this->cDateiname;
        $upd->cKopfzeile       = $this->cKopfzeile;
        $upd->cContent         = $this->cContent;
        $upd->cFusszeile       = $this->cFusszeile;
        $upd->cKodierung       = $this->cKodierung;
        $upd->nSpecial         = (int)$this->nSpecial;
        $upd->nVarKombiOption  = (int)$this->nVarKombiOption;
        $upd->nSplitgroesse    = (int)$this->nSplitgroesse;
        $upd->dZuletztErstellt = $this->dZuletztErstellt;
        $upd->nUseCache        = $this->nUseCache;

        return Shop::Container()->getDB()->update('texportformat', 'kExportformat', $this->getExportformat(), $upd);
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setTempFileName(string $name): self
    {
        $this->tempFileName = $name;

        return $this;
    }

    /**
     * @return int
     */
    public function delete(): int
    {
        return Shop::Container()->getDB()->delete('texportformat', 'kExportformat', $this->getExportformat());
    }

    /**
     * @param int $kExportformat
     * @return $this
     */
    public function setExportformat(int $kExportformat): self
    {
        $this->kExportformat = $kExportformat;

        return $this;
    }

    /**
     * @param int $kKundengruppe
     * @return $this
     */
    public function setKundengruppe(int $kKundengruppe): self
    {
        $this->kKundengruppe = $kKundengruppe;

        return $this;
    }

    /**
     * @param int $kSprache
     * @return $this
     */
    public function setSprache(int $kSprache): self
    {
        $this->kSprache = $kSprache;

        return $this;
    }

    /**
     * @param int $kWaehrung
     * @return $this
     */
    public function setWaehrung(int $kWaehrung): self
    {
        $this->kWaehrung = $kWaehrung;

        return $this;
    }

    /**
     * @param int $kKampagne
     * @return $this
     */
    public function setKampagne(int $kKampagne): self
    {
        $this->kKampagne = $kKampagne;

        return $this;
    }

    /**
     * @param int $kPlugin
     * @return $this
     */
    public function setPlugin(int $kPlugin): self
    {
        $this->kPlugin = $kPlugin;

        return $this;
    }

    /**
     * @param string $cName
     * @return $this
     */
    public function setName(string $cName): self
    {
        $this->cName = $cName;

        return $this;
    }

    /**
     * @param string $cDateiname
     * @return $this
     */
    public function setDateiname(string $cDateiname): self
    {
        $this->cDateiname = $cDateiname;

        return $this;
    }

    /**
     * @param string $cKopfzeile
     * @return $this
     */
    public function setKopfzeile($cKopfzeile)
    {
        $this->cKopfzeile = $cKopfzeile;

        return $this;
    }

    /**
     * @param string $cContent
     * @return $this
     */
    public function setContent($cContent)
    {
        $this->cContent = $cContent;

        return $this;
    }

    /**
     * @param string $cFusszeile
     * @return $this
     */
    public function setFusszeile($cFusszeile)
    {
        $this->cFusszeile = $cFusszeile;

        return $this;
    }

    /**
     * @param string $cKodierung
     * @return $this
     */
    public function setKodierung($cKodierung)
    {
        $this->cKodierung = $cKodierung;

        return $this;
    }

    /**
     * @param int $nSpecial
     * @return $this
     */
    public function setSpecial($nSpecial)
    {
        $this->nSpecial = (int)$nSpecial;

        return $this;
    }

    /**
     * @param int $nVarKombiOption
     * @return $this
     */
    public function setVarKombiOption($nVarKombiOption)
    {
        $this->nVarKombiOption = (int)$nVarKombiOption;

        return $this;
    }

    /**
     * @param int $nSplitgroesse
     * @return $this
     */
    public function setSplitgroesse($nSplitgroesse)
    {
        $this->nSplitgroesse = (int)$nSplitgroesse;

        return $this;
    }

    /**
     * @param string $dZuletztErstellt
     * @return $this
     */
    public function setZuletztErstellt($dZuletztErstellt)
    {
        $this->dZuletztErstellt = $dZuletztErstellt;

        return $this;
    }

    /**
     * @return int
     */
    public function getExportformat(): int
    {
        return (int)$this->kExportformat;
    }

    /**
     * @return int
     */
    public function getKundengruppe(): int
    {
        return (int)$this->kKundengruppe;
    }

    /**
     * @return int
     */
    public function getSprache(): int
    {
        return (int)$this->kSprache;
    }

    /**
     * @return int
     */
    public function getWaehrung(): int
    {
        return (int)$this->kWaehrung;
    }

    /**
     * @return int
     */
    public function getKampagne(): int
    {
        return (int)$this->kKampagne;
    }

    /**
     * @return int
     */
    public function getPlugin(): int
    {
        return (int)$this->kPlugin;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->cName;
    }

    /**
     * @return string
     */
    public function getDateiname()
    {
        return $this->cDateiname;
    }

    /**
     * @return string
     */
    public function getKopfzeile()
    {
        return $this->cKopfzeile;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->cContent;
    }

    /**
     * @return string
     */
    public function getFusszeile()
    {
        return $this->cFusszeile;
    }

    /**
     * @return string
     */
    public function getKodierung()
    {
        return $this->cKodierung;
    }

    /**
     * @return int
     */
    public function getSpecial()
    {
        return $this->nSpecial;
    }

    /**
     * @return int
     */
    public function getVarKombiOption()
    {
        return $this->nVarKombiOption;
    }

    /**
     * @return int
     */
    public function getSplitgroesse()
    {
        return $this->nSplitgroesse;
    }

    /**
     * @return string
     */
    public function getZuletztErstellt()
    {
        return $this->dZuletztErstellt;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param array $einstellungenAssoc_arr
     * @return bool
     * @deprecated since 5.0.0
     */
    public function insertEinstellungen($einstellungenAssoc_arr)
    {
        $ok = false;
        if (is_array($einstellungenAssoc_arr)) {
            $ok = true;
            foreach ($einstellungenAssoc_arr as $einstellungAssoc_arr) {
                $oObj = new stdClass();
                if (is_array($einstellungAssoc_arr) && count($einstellungAssoc_arr) > 0) {
                    foreach (array_keys($einstellungAssoc_arr) as $cMember) {
                        $oObj->$cMember = $einstellungAssoc_arr[$cMember];
                    }
                    $oObj->kExportformat = $this->getExportformat();
                }
                $ok = $ok && (Shop::Container()->getDB()->insert('texportformateinstellungen', $oObj) > 0);
            }
        }

        return $ok;
    }

    /**
     * @param array $einstellungenAssoc_arr
     * @return bool
     * @deprecated since 5.0.0
     */
    public function updateEinstellungen($einstellungenAssoc_arr)
    {
        $ok = false;
        if (is_array($einstellungenAssoc_arr)) {
            $ok = true;
            foreach ($einstellungenAssoc_arr as $einstellungAssoc_arr) {
                $cExportEinstellungenToImport_arr = [
                    'exportformate_semikolon',
                    'exportformate_equot',
                    'exportformate_quot'
                ];
                if (in_array($einstellungAssoc_arr['cName'], $cExportEinstellungenToImport_arr, true)) {
                    $_upd        = new stdClass();
                    $_upd->cWert = $einstellungAssoc_arr['cWert'];
                    $ok          = $ok && (Shop::Container()->getDB()->update(
                                'tboxensichtbar',
                                ['kExportformat', 'cName'],
                                [$this->getExportformat(), $einstellungAssoc_arr['cName']],
                                $_upd
                            ) >= 0);
                }
            }
        }

        return $ok;
    }

    /**
     * @return $this
     */
    private function initSmarty(): self
    {
        $this->smarty = (new JTLSmarty(true, false, false, 'export'))
            ->setCaching(0)
            ->setTemplateDir(PFAD_TEMPLATES)
            ->setCompileDir(PFAD_ROOT . PFAD_ADMIN . PFAD_COMPILEDIR)
            ->registerResource('db', new SmartyResourceNiceDB('export'))
            ->assign('URL_SHOP', Shop::getURL())
            ->assign('Waehrung', Session::Currency())
            ->assign('Einstellungen', $this->getConfig());

        // disable php execution in export format templates for security
        if (EXPORTFORMAT_USE_SECURITY) {
            $this->smarty->activateBackendSecurityMode();
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function initSession(): self
    {
        if (isset($_SESSION['Kundengruppe'])) {
            $this->oldSession               = new stdClass();
            $this->oldSession->Kundengruppe = $_SESSION['Kundengruppe'];
            $this->oldSession->kSprache     = $_SESSION['kSprache'];
            $this->oldSession->cISO         = $_SESSION['cISOSprache'];
            $this->oldSession->Waehrung     = Session::Currency();
        }
        $this->currency = $this->kWaehrung > 0
            ? new Currency($this->kWaehrung)
            : (new Currency())->getDefault();
        TaxHelper::setTaxRates();
        $net       = Shop::Container()->getDB()->select('tkundengruppe', 'kKundengruppe', $this->getKundengruppe());
        $languages = \Functional\map(Shop::Container()->getDB()->query(
            "SELECT * 
                FROM tsprache",
            \DB\ReturnType::ARRAY_OF_OBJECTS
        ), function ($lang) {
            $lang->kSprache = (int)$lang->kSprache;

            return $lang;
        });
        $langISO   = \Functional\first($languages, function ($l) {
            return $l->kSprache === $this->getSprache();
        });

        $_SESSION['Kundengruppe']  = (new Kundengruppe($this->getKundengruppe()))
            ->setMayViewPrices(1)
            ->setMayViewCategories(1)
            ->setIsMerchant($net !== null ? $net->nNettoPreise : 0);
        $_SESSION['kKundengruppe'] = $this->getKundengruppe();
        $_SESSION['kSprache']      = $this->getSprache();
        $_SESSION['Sprachen']      = $languages;
        $_SESSION['Waehrung']      = $this->currency;
        Shop::setLanguage($this->getSprache(), $langISO->cISO ?? null);

        return $this;
    }

    /**
     * @return $this
     */
    private function restoreSession(): self
    {
        if ($this->oldSession !== null) {
            $_SESSION['Kundengruppe'] = $this->oldSession->Kundengruppe;
            $_SESSION['Waehrung']     = $this->oldSession->Waehrung;
            $_SESSION['kSprache']     = $this->oldSession->kSprache;
            $_SESSION['cISOSprache']  = $this->oldSession->cISO;
            Shop::setLanguage($this->oldSession->kSprache, $this->oldSession->cISO);
        }

        return $this;
    }

    /**
     * @param bool $countOnly
     * @return string
     */
    private function getExportSQL(bool $countOnly = false): string
    {
        $where = '';
        $join  = '';
        $limit = '';

        switch ($this->getVarKombiOption()) {
            case 2:
                $where = " AND kVaterArtikel = 0";
                break;
            case 3:
                $where = " AND (tartikel.nIstVater != 1 OR tartikel.kEigenschaftKombi > 0)";
                break;
            default:
                break;
        }
        if ($this->config['exportformate_lager_ueber_null'] === 'Y') {
            $where .= " AND (NOT (tartikel.fLagerbestand <= 0 AND tartikel.cLagerBeachten = 'Y'))";
        } elseif ($this->config['exportformate_lager_ueber_null'] === 'O') {
            $where .= " AND (NOT (tartikel.fLagerbestand <= 0 AND tartikel.cLagerBeachten = 'Y') 
                            OR tartikel.cLagerKleinerNull = 'Y')";
        }

        if ($this->config['exportformate_preis_ueber_null'] === 'Y') {
            $join .= " JOIN tpreise ON tpreise.kArtikel = tartikel.kArtikel
                            AND tpreise.kKundengruppe = " . $this->getKundengruppe() . "
                            AND tpreise.fVKNetto > 0";
        }

        if ($this->config['exportformate_beschreibung'] === 'Y') {
            $where .= " AND tartikel.cBeschreibung != ''";
        }

        $condition = 'AND NOT (DATE(tartikel.dErscheinungsdatum) > DATE(NOW()))';
        $conf      = Shop::getSettings([CONF_GLOBAL]);
        if (isset($conf['global']['global_erscheinende_kaeuflich'])
            && $conf['global']['global_erscheinende_kaeuflich'] === 'Y'
        ) {
            $condition = 'AND (
                NOT (DATE(tartikel.dErscheinungsdatum) > DATE(NOW()))
                OR  (
                        DATE(tartikel.dErscheinungsdatum) > DATE(NOW())
                        AND (tartikel.cLagerBeachten = "N" 
                            OR tartikel.fLagerbestand > 0 OR tartikel.cLagerKleinerNull = "Y")
                    )
            )';
        }

        if ($countOnly === true) {
            $select = 'count(*) AS nAnzahl';
        } else {
            $select     = 'tartikel.kArtikel';
            $limit      = ' ORDER BY tartikel.kArtikel LIMIT ' . $this->getQueue()->nLimitM;
            $condition .= ' AND tartikel.kArtikel > ' . $this->getQueue()->nLastArticleID;
        }

        return "SELECT " . $select . "
            FROM tartikel
            LEFT JOIN tartikelattribut ON tartikelattribut.kArtikel = tartikel.kArtikel
                AND tartikelattribut.cName = '" . FKT_ATTRIBUT_KEINE_PREISSUCHMASCHINEN . "'
            " . $join . "
            LEFT JOIN tartikelsichtbarkeit ON tartikelsichtbarkeit.kArtikel = tartikel.kArtikel
                AND tartikelsichtbarkeit.kKundengruppe = " . $this->getKundengruppe() . "
            WHERE tartikelattribut.kArtikelAttribut IS NULL" . $where . "
                AND tartikelsichtbarkeit.kArtikel IS NULL " . $condition . $limit;
    }

    /**
     * @param object $queue
     * @return $this
     */
    private function setQueue($queue): self
    {
        if (isset($queue->nLimit_m)) {
            $queue->nLimitM = $queue->nLimit_m;
            $queue->nLimitN = $queue->nLimit_n;
        }
        $this->queue = $queue;

        return $this;
    }

    /**
     * @return object
     */
    public function getQueue()
    {
        return $this->queue;
    }

    /**
     * @return bool
     */
    public function useCache(): bool
    {
        return (int)$this->nUseCache === 1;
    }

    /**
     * @param int $caching
     * @return $this
     */
    public function setCaching(int $caching): self
    {
        $this->nUseCache = $caching;

        return $this;
    }

    /**
     * @return int
     */
    public function getCaching(): int
    {
        return (int)$this->nUseCache;
    }

    /**
     * @param resource $handle
     * @return int
     */
    private function writeHeader($handle): int
    {
        $header = $this->getKopfzeile();
        if (strlen($header) > 0) {
            $encoding = $this->getKodierung();
            if ($encoding === 'UTF-8') {
                fwrite($handle, "\xEF\xBB\xBF");
            }
            if ($encoding === 'UTF-8' || $encoding === 'UTF-8noBOM') {
                $header = StringHandler::convertUTF8($header);
            }

            return fwrite($handle, $header . "\n");
        }

        return 0;
    }

    /**
     * @param resource $handle
     * @return int
     */
    private function writeFooter($handle): int
    {
        $footer = $this->getFusszeile();
        if (strlen($footer) > 0) {
            $encoding = $this->getKodierung();
            if ($encoding === 'UTF-8' || $encoding === 'UTF-8noBOM') {
                $footer = StringHandler::convertUTF8($footer);
            }

            return fwrite($handle, $footer);
        }

        return 0;
    }

    /**
     * @return $this
     */
    private function splitFile(): self
    {
        if ((int)$this->nSplitgroesse <= 0 || !file_exists(PFAD_ROOT . PFAD_EXPORT . $this->cDateiname)) {
            return $this;
        }
        $fileCounter       = 1;
        $fileNameSplit_arr = [];
        $nFileTypePos      = strrpos($this->cDateiname, '.');
        // Dateiname splitten nach Name + Typ
        if ($nFileTypePos === false) {
            $fileNameSplit_arr[0] = $this->cDateiname;
        } else {
            $fileNameSplit_arr[0] = substr($this->cDateiname, 0, $nFileTypePos);
            $fileNameSplit_arr[1] = substr($this->cDateiname, $nFileTypePos);
        }
        // Ist die angelegte Datei größer als die Einstellung im Exportformat?
        clearstatcache();
        if (filesize(PFAD_ROOT . PFAD_EXPORT . $this->cDateiname) >= ($this->nSplitgroesse * 1024 * 1024 - 102400)) {
            sleep(2);
            $this->cleanupFiles($this->cDateiname, $fileNameSplit_arr[0]);
            $handle     = fopen(PFAD_ROOT . PFAD_EXPORT . $this->cDateiname, 'r');
            $nZeile     = 1;
            $new_handle = fopen($this->getFileName($fileNameSplit_arr, $fileCounter), 'w');
            $nSizeDatei = 0;
            while (($cContent = fgets($handle)) !== false) {
                if ($nZeile > 1) {
                    $nSizeZeile = strlen($cContent) + 2;
                    // Schwelle erreicht?
                    if ($nSizeDatei <= ($this->nSplitgroesse * 1024 * 1024 - 102400)) {
                        // Schreibe Content
                        fwrite($new_handle, $cContent);
                        $nSizeDatei += $nSizeZeile;
                    } else {
                        // neue Datei
                        $this->writeFooter($new_handle);
                        fclose($new_handle);
                        ++$fileCounter;
                        $new_handle = fopen($this->getFileName($fileNameSplit_arr, $fileCounter), 'w');
                        $this->writeHeader($new_handle);
                        // Schreibe Content
                        fwrite($new_handle, $cContent);
                        $nSizeDatei = $nSizeZeile;
                    }
                } elseif ($nZeile === 1) {
                    $this->writeHeader($new_handle);
                }
                ++$nZeile;
            }
            fclose($new_handle);
            fclose($handle);
            unlink(PFAD_ROOT . PFAD_EXPORT . $this->cDateiname);
        }

        return $this;
    }

    /**
     * @param array $fileNameSplit_arr
     * @param int   $fileCounter
     * @return string
     */
    private function getFileName($fileNameSplit_arr, $fileCounter): string
    {
        $fn = (is_array($fileNameSplit_arr) && count($fileNameSplit_arr) > 1)
            ? $fileNameSplit_arr[0] . $fileCounter . $fileNameSplit_arr[1]
            : $fileNameSplit_arr[0] . $fileCounter;

        return PFAD_ROOT . PFAD_EXPORT . $fn;
    }

    /**
     * @param string $fileName
     * @param string $fileNameSplit
     * @return $this
     */
    private function cleanupFiles(string $fileName, string $fileNameSplit): self
    {
        if (is_dir(PFAD_ROOT . PFAD_EXPORT) && ($dir = opendir(PFAD_ROOT . PFAD_EXPORT)) !== false) {
            while (($cDatei = readdir($dir)) !== false) {
                if ($cDatei !== $fileName && strpos($cDatei, $fileNameSplit) !== false) {
                    unlink(PFAD_ROOT . PFAD_EXPORT . $cDatei);
                }
            }
            closedir($dir);
        }

        return $this;
    }

    /**
     * @param JobQueue|object $queueObject
     * @param bool            $isAsync
     * @param bool            $back
     * @param bool            $isCron
     * @param int|null        $max
     * @return bool
     */
    public function startExport($queueObject, bool $isAsync = false, bool $back = false, bool $isCron = false, int $max = null): bool
    {
        if (!$this->isOK()) {
            Jtllog::cronLog('Export is not ok.');
            return false;
        }
        $started = false;
        $this->setQueue($queueObject)->initSession()->initSmarty();
        if ($this->getPlugin() > 0 && strpos($this->getContent(), PLUGIN_EXPORTFORMAT_CONTENTFILE) !== false) {
            Jtllog::cronLog('Starting plugin exportformat "' . $this->getName() .
                '" for language ' . $this->getSprache() . ' and customer group ' . $this->getKundengruppe() .
                ' with caching ' . ((Shop::Cache()->isActive() && $this->useCache()) ? 'enabled' : 'disabled'));
            $oPlugin = new Plugin($this->getPlugin());
            if ($isCron === true) {
                global $oJobQueue;
                $oJobQueue = $queueObject;
            } else {
                global $queue;
                $queue = $queueObject;
            }
            global $exportformat, $ExportEinstellungen;
            $exportformat                   = new stdClass();
            $exportformat->kKundengruppe    = $this->getKundengruppe();
            $exportformat->kExportformat    = $this->getExportformat();
            $exportformat->kSprache         = $this->getSprache();
            $exportformat->kWaehrung        = $this->getWaehrung();
            $exportformat->kKampagne        = $this->getKampagne();
            $exportformat->kPlugin          = $this->getPlugin();
            $exportformat->cName            = $this->getName();
            $exportformat->cDateiname       = $this->getDateiname();
            $exportformat->cKopfzeile       = $this->getKopfzeile();
            $exportformat->cContent         = $this->getContent();
            $exportformat->cFusszeile       = $this->getFusszeile();
            $exportformat->cKodierung       = $this->getKodierung();
            $exportformat->nSpecial         = $this->getSpecial();
            $exportformat->nVarKombiOption  = $this->getVarKombiOption();
            $exportformat->nSplitgroesse    = $this->getSplitgroesse();
            $exportformat->dZuletztErstellt = $this->getZuletztErstellt();
            $exportformat->nUseCache        = $this->getCaching();
            // needed by Google Shopping export format plugin
            $exportformat->tkampagne_cParameter = $this->campaignParameter;
            $exportformat->tkampagne_cWert      = $this->campaignValue;
            // needed for plugin exports
            $ExportEinstellungen = $this->getConfig();
            include $oPlugin->cAdminmenuPfad . PFAD_PLUGIN_EXPORTFORMAT .
                str_replace(PLUGIN_EXPORTFORMAT_CONTENTFILE, '', $this->getContent());

            if (isset($queueObject->kExportqueue)) {
                Shop::Container()->getDB()->delete('texportqueue', 'kExportqueue', (int)$queueObject->kExportqueue);
            }
            if (isset($_GET['back']) && $_GET['back'] === 'admin') {
                header('Location: exportformate.php?action=exported&token=' .
                    $_SESSION['jtl_token'] . '&kExportformat=' . (int)$this->queue->kExportformat);
                exit;
            }
            Jtllog::cronLog('Finished export');

            return true;
        }
        $start        = microtime(true);
        $cacheHits    = 0;
        $cacheMisses  = 0;
        $cOutput      = '';
        $errorMessage = '';
        Shop::dbg($this->queue->nLimitN, false, '$this->queue->nLimitN:');
        Shop::dbg($this->queue->nLimitM, false, '$this->queue->nLimitM:');
        if ((int)$this->queue->nLimitN === 0 && file_exists(PFAD_ROOT . PFAD_EXPORT . $this->tempFileName)) {
            unlink(PFAD_ROOT . PFAD_EXPORT . $this->tempFileName);
        }
        $datei = fopen(PFAD_ROOT . PFAD_EXPORT . $this->tempFileName, 'a');
        if ($max === null) {
            $maxObj = Shop::Container()->getDB()->executeQuery($this->getExportSQL(true), \DB\ReturnType::SINGLE_OBJECT);
            $max    = (int)$maxObj->nAnzahl;
        }

        Jtllog::cronLog('Starting exportformat "' . StringHandler::convertUTF8($this->getName()) .
            '" for language ' . $this->getSprache() . ' and customer group ' . $this->getKundengruppe() .
            ' with caching ' . ((Shop::Cache()->isActive() && $this->useCache()) ? 'enabled' : 'disabled') .
            ' - ' . $queueObject->nLimitN . '/' . $max . ' products exported');
        // Kopfzeile schreiben
        if ((int)$this->queue->nLimitN === 0) {
            $this->writeHeader($datei);
        }
        $content                                     = $this->getContent();
        $categoryFallback                            = (strpos($content, '->oKategorie_arr') !== false);
        $oArtikelOptionen                            = new stdClass();
        $oArtikelOptionen->nMerkmale                 = 1;
        $oArtikelOptionen->nAttribute                = 1;
        $oArtikelOptionen->nArtikelAttribute         = 1;
        $oArtikelOptionen->nKategorie                = 1;
        $oArtikelOptionen->nKeinLagerbestandBeachten = 1;
        $oArtikelOptionen->nMedienDatei              = 1;

        $helper       = KategorieHelper::getInstance($this->getSprache(), $this->getKundengruppe());
        $shopURL      = Shop::getURL();
        $imageBaseURL = Shop::getImageBaseURL();
        $find         = ['<br />', '<br>', '</'];
        $replace      = [' ', ' ', ' </'];
        $findTwo      = ["\r\n", "\r", "\n", "\x0B", "\x0"];
        $replaceTwo   = [' ', ' ', ' ', ' ', ''];

        if (isset($this->config['exportformate_quot']) && $this->config['exportformate_quot'] !== 'N') {
            $findTwo[] = '"';
            if ($this->config['exportformate_quot'] === 'q' || $this->config['exportformate_quot'] === 'bq') {
                $replaceTwo[] = '\"';
            } elseif ($this->config['exportformate_quot'] === 'qq') {
                $replaceTwo[] = '""';
            } else {
                $replaceTwo[] = $this->config['exportformate_quot'];
            }
        }
        if (isset($this->config['exportformate_quot']) && $this->config['exportformate_equot'] !== 'N') {
            $findTwo[] = "'";
            if ($this->config['exportformate_equot'] === 'q' || $this->config['exportformate_equot'] === 'bq') {
                $replaceTwo[] = '"';
            } else {
                $replaceTwo[] = $this->config['exportformate_equot'];
            }
        }
        if (isset($this->config['exportformate_semikolon']) && $this->config['exportformate_semikolon'] !== 'N') {
            $findTwo[]    = ';';
            $replaceTwo[] = $this->config['exportformate_semikolon'];
        }
        foreach (Shop::Container()->getDB()->query($this->getExportSQL(), \DB\ReturnType::QUERYSINGLE) as $iterArticle) {
            $Artikel = new Artikel();
            $Artikel->fuelleArtikel(
                $iterArticle['kArtikel'],
                $oArtikelOptionen,
                $this->kKundengruppe,
                $this->kSprache,
                !$this->useCache()
            );
            $articleCategoryID = $Artikel->gibKategorie();
            if ($categoryFallback === true) {
                // since 4.05 the article class only stores category IDs in Artikel::oKategorie_arr
                // but old google base exports rely on category attributes that wouldn't be available anymore
                // so in that case we replace oKategorie_arr with an array of real Kategorie objects
                $categories = [];
                foreach ($Artikel->oKategorie_arr as $categoryID) {
                    $categories[] = new Kategorie(
                        (int)$categoryID,
                        $this->kSprache,
                        $this->kKundengruppe,
                        !$this->useCache()
                    );
                }
                $Artikel->oKategorie_arr = $categories;
            }

            if ($Artikel->kArtikel > 0) {
                $started = true;
                ++$this->queue->nLimitN;
                $this->queue->nLastArticleID = $Artikel->kArtikel;

                if ($Artikel->cacheHit === true) {
                    ++$cacheHits;
                } else {
                    ++$cacheMisses;
                }
                $Artikel->cBeschreibungHTML     = StringHandler::removeWhitespace(
                    str_replace(
                        $findTwo,
                        $replaceTwo,
                        str_replace('"', '&quot;', $Artikel->cBeschreibung)
                    )
                );
                $Artikel->cKurzBeschreibungHTML = StringHandler::removeWhitespace(
                    str_replace(
                        $findTwo,
                        $replaceTwo,
                        str_replace('"', '&quot;', $Artikel->cKurzBeschreibung)
                    )
                );
                $Artikel->cName                 = StringHandler::removeWhitespace(
                    str_replace(
                        $findTwo,
                        $replaceTwo,
                        StringHandler::unhtmlentities(strip_tags(str_replace($find, $replace, $Artikel->cName)))
                    )
                );
                $Artikel->cBeschreibung         = StringHandler::removeWhitespace(
                    str_replace(
                        $findTwo,
                        $replaceTwo,
                        StringHandler::unhtmlentities(strip_tags(str_replace($find, $replace, $Artikel->cBeschreibung)))
                    )
                );
                $Artikel->cKurzBeschreibung     = StringHandler::removeWhitespace(
                    str_replace(
                        $findTwo,
                        $replaceTwo,
                        StringHandler::unhtmlentities(strip_tags(str_replace($find, $replace,
                            $Artikel->cKurzBeschreibung)))
                    )
                );
                $Artikel->fUst                  = TaxHelper::getSalesTax($Artikel->kSteuerklasse);
                $Artikel->Preise->fVKBrutto     = TaxHelper::getGross(
                    $Artikel->Preise->fVKNetto * $this->currency->getConversionFactor(),
                    $Artikel->fUst
                );
                $Artikel->Preise->fVKNetto      = round($Artikel->Preise->fVKNetto, 2);
                $Artikel->Kategorie             = new Kategorie(
                    $articleCategoryID,
                    $this->kSprache,
                    $this->kKundengruppe,
                    !$this->useCache()
                );
                // calling gibKategoriepfad() should not be necessary since it has already been called in Kategorie::loadFromDB()
                $Artikel->Kategoriepfad = $Artikel->Kategorie->cKategoriePfad ?? $helper->getPath($Artikel->Kategorie);
                $Artikel->Versandkosten = VersandartHelper::getLowestShippingFees(
                    $this->config['exportformate_lieferland'] ?? '',
                    $Artikel,
                    0,
                    $this->kKundengruppe
                );
                if ($Artikel->Versandkosten !== -1) {
                    $price = Currency::convertCurrency($Artikel->Versandkosten, null, $this->kWaehrung);
                    if ($price !== false) {
                        $Artikel->Versandkosten = $price;
                    }
                }
                // Kampagne URL
                if (!empty($this->campaignParameter)) {
                    $cSep          = (strpos($Artikel->cURL, '.php') !== false) ? '&' : '?';
                    $Artikel->cURL .= $cSep . $this->campaignParameter . '=' . $this->campaignValue;
                }

                $Artikel->cDeeplink             = $shopURL . '/' . $Artikel->cURL;
                $Artikel->Artikelbild           = $Artikel->Bilder[0]->cPfadGross
                    ? $imageBaseURL . $Artikel->Bilder[0]->cPfadGross
                    : '';
                $Artikel->Lieferbar             = $Artikel->fLagerbestand <= 0 ? 'N' : 'Y';
                $Artikel->Lieferbar_01          = $Artikel->fLagerbestand <= 0 ? 0 : 1;
                $Artikel->Verfuegbarkeit_kelkoo = $Artikel->fLagerbestand > 0 ? '001' : '003';

                $_out = $this->smarty->assign('Artikel', $Artikel)->fetch('db:' . $this->getExportformat());
                if (!empty($_out)) {
                    $cOutput .= $_out . "\n";
                }

                executeHook(HOOK_DO_EXPORT_OUTPUT_FETCHED);
                if (!$isAsync && ($queueObject->nLimitN % max(round($queueObject->nLimitM / 10), 10)) === 0) {
                    //max. 10 status updates per run
                    Jtllog::cronLog($queueObject->nLimitN . '/' . $max . ' products exported', 2);
                }
            }
        }
        if (strlen($cOutput) > 0) {
            fwrite($datei, (($this->cKodierung === 'UTF-8' || $this->cKodierung === 'UTF-8noBOM')
                ? StringHandler::convertUTF8($cOutput)
                : $cOutput));
        }

        if ($isCron === false) {
            if ($started === true) {
                // One or more articles have been exported
                fclose($datei);
                Shop::Container()->getDB()->queryPrepared(
                    "UPDATE texportqueue SET
                        nLimit_n       = nLimit_n + :nLimitM,
                        nLastArticleID = :nLastArticleID
                      WHERE kExportqueue = :kExportqueue",
                    [
                        'nLimitM'        => $this->queue->nLimitM,
                        'nLastArticleID' => $this->queue->nLastArticleID,
                        'kExportqueue'   => (int)$this->queue->kExportqueue,
                    ],
                    \DB\ReturnType::DEFAULT
                );
                $protocol = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
                    || RequestHelper::checkSSL() === 2)
                    ? 'https://'
                    : 'http://';
                if ($isAsync) {
                    $oCallback                 = new stdClass();
                    $oCallback->kExportformat  = $this->getExportformat();
                    $oCallback->kExportqueue   = $this->queue->kExportqueue;
                    $oCallback->nMax           = $max;
                    $oCallback->nCurrent       = $this->queue->nLimitN;
                    $oCallback->nLastArticleID = $this->queue->nLastArticleID;
                    $oCallback->bFinished      = false;
                    $oCallback->bFirst         = ((int)$this->queue->nLimitN === 0);
                    $oCallback->cURL           = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
                    $oCallback->cacheMisses    = $cacheMisses;
                    $oCallback->cacheHits      = $cacheHits;
                    echo json_encode($oCallback);
                } else {
                    $cURL = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] .
                        '?e=' . (int)$this->queue->kExportqueue .
                        '&back=admin&token=' . $_SESSION['jtl_token'] . '&max=' . $max;
                    header('Location: ' . $cURL);
                }
            } else {
                // There are no more articles to export
                Shop::Container()->getDB()->query(
                    "UPDATE texportformat 
                        SET dZuletztErstellt = now() 
                        WHERE kExportformat = " . $this->getExportformat(),
                    \DB\ReturnType::DEFAULT
                );
                Shop::dbg($this->queue, false, '$this->queue:');
                Shop::Container()->getDB()->delete('texportqueue', 'kExportqueue', (int)$this->queue->kExportqueue);

                $this->writeFooter($datei);
                fclose($datei);
                if (copy(PFAD_ROOT . PFAD_EXPORT . $this->tempFileName, PFAD_ROOT . PFAD_EXPORT . $this->cDateiname)) {
                    unlink(PFAD_ROOT . PFAD_EXPORT . $this->tempFileName);
                } else {
                    $errorMessage = 'Konnte Export-Datei ' .
                        PFAD_ROOT . PFAD_EXPORT . $this->cDateiname .
                        ' nicht erstellen. Fehlende Schreibrechte?';
                }
                // Versucht (falls so eingestellt) die erstellte Exportdatei in mehrere Dateien zu splitten
                $this->splitFile();
                if ($back === true) {
                    if ($isAsync) {
                        $oCallback                 = new stdClass();
                        $oCallback->kExportformat  = $this->getExportformat();
                        $oCallback->nMax           = $max;
                        $oCallback->nCurrent       = $this->queue->nLimitN;
                        $oCallback->nLastArticleID = $this->queue->nLastArticleID;
                        $oCallback->bFinished      = true;
                        $oCallback->cacheMisses    = $cacheMisses;
                        $oCallback->cacheHits      = $cacheHits;
                        $oCallback->errorMessage   = $errorMessage;

                        echo json_encode($oCallback);
                    } else {
                        header('Location: exportformate.php?action=exported&token=' .
                            $_SESSION['jtl_token'] .
                            '&kExportformat=' . $this->getExportformat() .
                            '&max=' . $max .
                            '&hasError=' . (int)($errorMessage !== '')
                        );
                    }
                }
            }
        } else {
            //finalize job when there are no more articles to export
            if ($started === false) {
                Jtllog::cronLog('Finalizing job.', 2);
                Shop::Container()->getDB()->update(
                    'texportformat',
                    'kExportformat',
                    (int)$queueObject->kKey,
                    (object)['dZuletztErstellt' => 'now()']
                );
                if (file_exists(PFAD_ROOT . PFAD_EXPORT . $this->cDateiname)) {
                    Jtllog::cronLog('Deleting final file ' . PFAD_ROOT . PFAD_EXPORT . $this->cDateiname);
                    unlink(PFAD_ROOT . PFAD_EXPORT . $this->cDateiname);
                }
                // Schreibe Fusszeile
                $this->writeFooter($datei);
                fclose($datei);
                if (copy(PFAD_ROOT . PFAD_EXPORT . $this->tempFileName, PFAD_ROOT . PFAD_EXPORT . $this->cDateiname)) {
                    unlink(PFAD_ROOT . PFAD_EXPORT . $this->tempFileName);
                }
                // Versucht (falls so eingestellt) die erstellte Exportdatei in mehrere Dateien zu splitten
                $this->splitFile();
            }
            Jtllog::cronLog('Finished after ' . round(microtime(true) - $start, 4) .
                's. Article cache hits: ' . $cacheHits . ', misses: ' . $cacheMisses);
        }
        $this->restoreSession();

        if ($isAsync) {
            exit();
        }

        return $started;
    }

    /**
     * @param array $post
     * @return array|bool
     */
    public function check(array $post)
    {
        $cPlausiValue_arr = [];
        if (empty($post['cName'])) {
            $cPlausiValue_arr['cName'] = 1;
        } else {
            $this->setName($post['cName']);
        }
        $pathinfo           = pathinfo(PFAD_ROOT . PFAD_EXPORT . $post['cDateiname']);
        $extensionWhitelist = array_map('strtolower', explode(',', EXPORTFORMAT_ALLOWED_FORMATS));
        if (empty($post['cDateiname'])) {
            $cPlausiValue_arr['cDateiname'] = 1;
        } elseif (strpos($post['cDateiname'], '.') === false) { // Dateiendung fehlt
            $cPlausiValue_arr['cDateiname'] = 2;
        } elseif (strpos(realpath($pathinfo['dirname']), realpath(PFAD_ROOT)) === false) {
            $cPlausiValue_arr['cDateiname'] = 3;
        } elseif (!in_array(strtolower($pathinfo['extension']), $extensionWhitelist, true)) {
            $cPlausiValue_arr['cDateiname'] = 4;
        } else {
            $this->setDateiname($post['cDateiname']);
        }
        if (empty($post['cContent'])) {
            $cPlausiValue_arr['cContent'] = 1;
        } elseif ((
                strpos($post['cContent'], '{php}') !== false
                || strpos($post['cContent'], '<?php') !== false
                || strpos($post['cContent'], '<%') !== false
                || strpos($post['cContent'], '<%=') !== false
                || strpos($post['cContent'], '<script language="php">') !== false
            ) && !EXPORTFORMAT_ALLOW_PHP
        ) {
            $cPlausiValue_arr['cContent'] = 2;
        } else {
            $this->setContent(str_replace('<tab>', "\t", $post['cContent']));
        }
        if (!isset($post['kSprache']) || (int)$post['kSprache'] === 0) {
            $cPlausiValue_arr['kSprache'] = 1;
        } else {
            $this->setSprache($post['kSprache']);
        }
        if (!isset($post['kWaehrung']) || (int)$post['kWaehrung'] === 0) {
            $cPlausiValue_arr['kWaehrung'] = 1;
        } else {
            $this->setWaehrung($post['kWaehrung']);
        }
        if (!isset($post['kKundengruppe']) || (int)$post['kKundengruppe'] === 0) {
            $cPlausiValue_arr['kKundengruppe'] = 1;
        } else {
            $this->setKundengruppe($post['kKundengruppe']);
        }
        if (count($cPlausiValue_arr) === 0) {
            $this->setCaching($post['nUseCache'])
                 ->setVarKombiOption($post['nVarKombiOption'])
                 ->setSplitgroesse($post['nSplitgroesse'])
                 ->setSpecial(0)
                 ->setKodierung($post['cKodierung'])
                 ->setPlugin($post['kPlugin'] ?? 0)
                 ->setExportformat(!empty($post['kExportformat']) ? $post['kExportformat'] : 0)
                 ->setKampagne($post['kKampagne'] ?? 0);
            if (isset($post['cFusszeile'])) {
                $this->setFusszeile(str_replace('<tab>', "\t", $post['cFusszeile']));
            }
            if (isset($post['cKopfzeile'])) {
                $this->setKopfzeile(str_replace('<tab>', "\t", $post['cKopfzeile']));
            }

            return true;
        }

        return $cPlausiValue_arr;
    }

    /**
     * @return bool|string
     */
    public function checkSyntax()
    {
        $this->initSession()->initSmarty();
        $error = false;
        try {
            $article       = null;
            $articleObject = Shop::Container()->getDB()->query(
                "SELECT * 
                    FROM tartikel 
                    WHERE kVaterArtikel = 0 
                    AND (cLagerBeachten = 'N' OR fLagerbestand > 0) LIMIT 1",
                \DB\ReturnType::SINGLE_OBJECT
            );
            if (!empty($articleObject->kArtikel)) {
                $oArtikelOptionen                            = new stdClass();
                $oArtikelOptionen->nMerkmale                 = 1;
                $oArtikelOptionen->nAttribute                = 1;
                $oArtikelOptionen->nArtikelAttribute         = 1;
                $oArtikelOptionen->nKategorie                = 1;
                $oArtikelOptionen->nKeinLagerbestandBeachten = 1;
                $oArtikelOptionen->nMedienDatei              = 1;

                $article = new Artikel();
                $article->fuelleArtikel($articleObject->kArtikel, $oArtikelOptionen);
                $article->cDeeplink             = '';
                $article->Artikelbild           = '';
                $article->Lieferbar             = '';
                $article->Lieferbar_01          = '';
                $article->Verfuegbarkeit_kelkoo = '';
                $article->cBeschreibungHTML     = '';
                $article->cKurzBeschreibungHTML = '';
                $article->fUst                  = 0;
                $article->Kategorie             = new Kategorie();
                $article->Kategoriepfad         = '';
                $article->Versandkosten         = -1;

            }
            $this->smarty->assign('Artikel', $article)
                         ->fetch('db:' . $this->kExportformat);
        } catch (Exception $e) {
            $error = '<strong>Smarty-Syntaxfehler:</strong><br />';
            $error .= '<pre>' . $e->getMessage() . '</pre>';
        }

        return $error;
    }
}
