<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Boxes\Admin;


use Boxes\BoxType;
use DB\DbInterface;
use DB\ReturnType;
use Services\JTL\BoxService;
use Services\JTL\BoxServiceInterface;

/**
 * Class BoxAdmin
 * @package Boxes\Admin
 */
class BoxAdmin
{
    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var BoxServiceInterface
     */
    private $service;

    /**
     * @var array
     */
    private $visibility;

    /**
     * @var array
     */
    private static $validPageTypes = [
        PAGE_UNBEKANNT,
        PAGE_ARTIKEL,
        PAGE_ARTIKELLISTE,
        PAGE_WARENKORB,
        PAGE_MEINKONTO,
        PAGE_KONTAKT,
        PAGE_UMFRAGE,
        PAGE_NEWS,
        PAGE_NEWSLETTER,
        PAGE_LOGIN,
        PAGE_REGISTRIERUNG,
        PAGE_BESTELLVORGANG,
        PAGE_BEWERTUNG,
        PAGE_DRUCKANSICHT,
        PAGE_PASSWORTVERGESSEN,
        PAGE_WARTUNG,
        PAGE_WUNSCHLISTE,
        PAGE_VERGLEICHSLISTE,
        PAGE_STARTSEITE,
        PAGE_VERSAND,
        PAGE_AGB,
        PAGE_DATENSCHUTZ,
        PAGE_TAGGING,
        PAGE_LIVESUCHE,
        PAGE_HERSTELLER,
        PAGE_SITEMAP,
        PAGE_GRATISGESCHENK,
        PAGE_WRB,
        PAGE_PLUGIN,
        PAGE_NEWSLETTERARCHIV,
        PAGE_NEWSARCHIV,
        PAGE_EIGENE,
        PAGE_AUSWAHLASSISTENT,
        PAGE_BESTELLABSCHLUSS,
        PAGE_RMA
    ];

    /**
     * BoxAdmin constructor.
     * @param DbInterface         $db
     * @param BoxServiceInterface $service
     */
    public function __construct(DbInterface $db, BoxServiceInterface $service)
    {
        $this->db      = $db;
        $this->service = $service;
    }

    /**
     * @return array
     */
    public function getValidPageTypes(): array
    {
        return self::$validPageTypes;
    }

    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $bOk = $this->db->delete('tboxen', 'kBox', $id) > 0;

        return $bOk
            ? ($this->db->delete('tboxensichtbar', 'kBox', $id) > 0)
            : false;
    }

    /**
     * @param int $baseType
     * @return \stdClass|null
     * @former holeVorlage()
     */
    private function getTemplate(int $baseType)
    {
        return $this->db->select('tboxvorlage', 'kBoxvorlage', $baseType);
    }

    /**
     * @param int    $nSeite
     * @param string $ePosition
     * @param int    $containerID
     * @return int
     * @former letzteSortierID()
     */
    private function getLastSortID(int $nSeite, string $ePosition = 'left', int $containerID = 0): int
    {
        $oBox = $this->db->queryPrepared(
            'SELECT tboxensichtbar.nSort, tboxen.ePosition
                FROM tboxensichtbar
                LEFT JOIN tboxen
                    ON tboxensichtbar.kBox = tboxen.kBox
                    WHERE tboxensichtbar.kSeite = :pageid
                        AND tboxen.ePosition = :position
                        AND tboxen.kContainer = :containerid
                ORDER BY tboxensichtbar.nSort DESC LIMIT 1',
            [
                'pageid'      => $nSeite,
                'position'    => $ePosition,
                'containerid' => $containerID
            ],
            ReturnType::SINGLE_OBJECT
        );

        return $oBox ? ++$oBox->nSort : 0;
    }

    /**
     * @param int    $kBox
     * @param string $cISO
     * @return mixed
     */
    public function getContent(int $kBox, string $cISO = '')
    {

        return strlen($cISO) > 0
            ? $this->db->select('tboxsprache', 'kBox', $kBox, 'cISO', $cISO)
            : $this->db->selectAll('tboxsprache', 'kBox', $kBox);
    }

    /**
     * @param int $kBox
     * @return \stdClass
     */
    public function getByID(int $kBox): \stdClass
    {
        $oBox = $this->db->queryPrepared(
            'SELECT tboxen.kBox, tboxen.kBoxvorlage, tboxen.kCustomID, tboxen.cTitel, tboxen.ePosition,
                tboxvorlage.eTyp, tboxvorlage.cName, tboxvorlage.cVerfuegbar, tboxvorlage.cTemplate
                FROM tboxen
                LEFT JOIN tboxvorlage 
                    ON tboxen.kBoxvorlage = tboxvorlage.kBoxvorlage
                WHERE kBox = :bxid',
            ['bxid' => $kBox],
            ReturnType::SINGLE_OBJECT
        );

        $oBox->oSprache_arr      = ($oBox && ($oBox->eTyp === BoxType::TEXT || $oBox->eTyp === BoxType::CATBOX))
            ? $this->getContent($kBox)
            : [];
        $oBox->kBox              = (int)$oBox->kBox;
        $oBox->kBoxvorlage       = (int)$oBox->kBoxvorlage;
        $oBox->supportsRevisions = $oBox->kBoxvorlage === BOX_EIGENE_BOX_OHNE_RAHMEN || $oBox->kBoxvorlage === BOX_EIGENE_BOX_MIT_RAHMEN;

        return $oBox;
    }

    /**
     * @param int    $baseID
     * @param int    $nSeite
     * @param string $ePosition
     * @param int    $containerID
     * @return bool
     */
    public function create(int $baseID, int $nSeite, string $ePosition = 'left', int $containerID = 0): bool
    {
        $validPageTypes = $this->getValidPageTypes();
        $oBox           = new \stdClass();
        $oBoxVorlage    = $this->getTemplate($baseID);
        $oBox->cTitel   = '';
        if ($oBoxVorlage) {
            $oBox->cTitel = $oBoxVorlage->cName;
        }

        $oBox->kBoxvorlage = $baseID;
        $oBox->ePosition   = $ePosition;
        $oBox->kContainer  = $containerID;
        $oBox->kCustomID   = (isset($oBoxVorlage->kCustomID) && is_numeric($oBoxVorlage->kCustomID))
            ? (int)$oBoxVorlage->kCustomID
            : 0;

        $kBox = $this->db->insert('tboxen', $oBox);
        if ($kBox) {
            $cnt                = count($validPageTypes);
            $oBoxSichtbar       = new \stdClass();
            $oBoxSichtbar->kBox = $kBox;
            for ($i = 0; $i < $cnt; $i++) {
                $oBoxSichtbar->nSort  = $this->getLastSortID($nSeite, $ePosition, $containerID);
                $oBoxSichtbar->kSeite = $i;
                $oBoxSichtbar->bAktiv = ($nSeite === $i || $nSeite === 0) ? 1 : 0;
                $this->db->insert('tboxensichtbar', $oBoxSichtbar);
            }

            return true;
        }

        return false;
    }

    /**
     * @param int    $kBox
     * @param string $cTitel
     * @param int    $kCustomID
     * @return bool
     * @former bearbeiteBox()
     */
    public function update(int $kBox, $cTitel, int $kCustomID = 0): bool
    {
        $oBox            = new \stdClass();
        $oBox->cTitel    = $cTitel;
        $oBox->kCustomID = $kCustomID;

        return $this->db->update('tboxen', 'kBox', $kBox, $oBox) >= 0;
    }

    /**
     * @param int    $kBox
     * @param string $cISO
     * @param string $cTitel
     * @param string $cInhalt
     * @return bool
     * @former bearbeiteBoxSprache()
     */
    public function updateLanguage(int $kBox, string $cISO, string $cTitel, string $cInhalt): bool
    {
        $oBox = $this->db->select('tboxsprache', 'kBox', $kBox, 'cISO', $cISO);
        if (isset($oBox->kBox)) {
            $_upd          = new \stdClass();
            $_upd->cTitel  = $cTitel;
            $_upd->cInhalt = $cInhalt;

            return $this->db->update('tboxsprache', ['kBox', 'cISO'], [$kBox, $cISO], $_upd) >= 0;
        }
        $_ins          = new \stdClass();
        $_ins->kBox    = $kBox;
        $_ins->cISO    = $cISO;
        $_ins->cTitel  = $cTitel;
        $_ins->cInhalt = $cInhalt;

        return $this->db->insert('tboxsprache', $_ins) > 0;
    }

    /**
     * @param int      $nSeite
     * @param string   $ePosition
     * @param bool|int $bAnzeigen
     * @return bool
     * @former setzeBoxAnzeige()
     */
    public function setVisibility(int $nSeite, string $ePosition, $bAnzeigen): bool
    {
        $bAnzeigen      = (int)$bAnzeigen;
        $validPageTypes = $this->getValidPageTypes();
        if ($nSeite === 0) {
            $bOk = true;
            for ($i = 0; $i < count($validPageTypes) && $bOk; $i++) {
                $bOk = $this->db->executeQueryPrepared(
                        "REPLACE INTO tboxenanzeige 
                            SET bAnzeigen = :show,
                                nSeite = :page, 
                                ePosition = :position",
                        ['show' => $bAnzeigen, 'page' => $i, 'position' => $ePosition],
                        ReturnType::DEFAULT
                    ) && $bOk;
            }

            return $bOk;
        }

        return $this->db->executeQueryPrepared(
            "REPLACE INTO tboxenanzeige 
                SET bAnzeigen = :show, 
                    nSeite = :page, 
                    ePosition = :position",
            ['show' => $bAnzeigen, 'page' => $nSeite, 'position' => $ePosition],
            ReturnType::DEFAULT
        );
    }

    /**
     * @param int      $kBox
     * @param int      $nSeite
     * @param int      $nSort
     * @param bool|int $bAktiv
     * @return bool
     * @former sortBox()
     */
    public function sort(int $kBox, int $nSeite, int $nSort, $bAktiv = true): bool
    {
        $bAktiv         = (int)$bAktiv;
        $validPageTypes = $this->getValidPageTypes();
        if ($nSeite === 0) {
            $bOk = true;
            for ($i = 0; $i < count($validPageTypes) && $bOk; $i++) {
                $oBox = $this->db->select('tboxensichtbar', 'kBox', $kBox);
                $bOk  = !empty($oBox)
                    ? ($this->db->query(
                            "UPDATE tboxensichtbar 
                                SET nSort = " . $nSort . ",
                                    bAktiv = " . $bAktiv . " 
                                WHERE kBox = " . $kBox . " 
                                    AND kSeite = " . $i,
                            ReturnType::DEFAULT
                        ) !== false)
                    : ($this->db->query(
                            "INSERT INTO tboxensichtbar 
                                SET kBox = " . $kBox . ",
                                    kSeite = " . $i . ", 
                                    nSort = " . $nSort . ", 
                                    bAktiv = " . $bAktiv,
                            ReturnType::DEFAULT
                        ) === true);
            }

            return $bOk;
        }

        return $this->db->query(
                "REPLACE INTO tboxensichtbar 
                  SET kBox = " . $kBox . ", 
                      kSeite = " . $nSeite . ", 
                      nSort = " . $nSort . ", 
                      bAktiv = " . $bAktiv,
                ReturnType::AFFECTED_ROWS
            ) !== false;
    }

    /**
     * @param int          $kBox
     * @param int          $kSeite
     * @param string|array $cFilter
     * @return int
     */
    public function filterBoxVisibility(int $kBox, int $kSeite, $cFilter = ''): int
    {
        if (is_array($cFilter)) {
            $cFilter = array_unique($cFilter);
            $cFilter = implode(',', $cFilter);
        }
        $_upd          = new \stdClass();
        $_upd->cFilter = $cFilter;

        return $this->db->update('tboxensichtbar', ['kBox', 'kSeite'], [$kBox, $kSeite], $_upd);
    }

    /**
     * @param int      $kBox
     * @param int      $nSeite
     * @param bool|int $bAktiv
     * @return bool
     * @former aktiviereBox()
     */
    public function activate(int $kBox, int $nSeite, $bAktiv = true): bool
    {
        $bAktiv         = (int)$bAktiv;
        $validPageTypes = $this->getValidPageTypes();
        if ($nSeite === 0) {
            $bOk = true;
            for ($i = 0; $i < count($validPageTypes) && $bOk; $i++) {
                $_upd         = new \stdClass();
                $_upd->bAktiv = $bAktiv;
                $bOk          = $this->db->update(
                        'tboxensichtbar',
                        ['kBox', 'kSeite'],
                        [$kBox, $i],
                        $_upd
                    ) >= 0;
            }

            return $bOk;
        }
        $_upd         = new \stdClass();
        $_upd->bAktiv = $bAktiv;

        return $this->db->update('tboxensichtbar', ['kBox', 'kSeite'], [$kBox, 0], $_upd) >= 0;
    }

    /**
     * @param int $nSeite
     * @return array
     * @former holeVorlagen()
     */
    public function getTemplates(int $nSeite = -1): array
    {
        $cSQL          = '';
        $oVorlagen_arr = [];

        if ($nSeite >= 0) {
            $cSQL = 'WHERE (cVerfuegbar = "' . $nSeite . '" OR cVerfuegbar = "0")';
        }
        $oVorlage_arr = $this->db->query(
            "SELECT * 
                FROM tboxvorlage " . $cSQL . " 
                ORDER BY cVerfuegbar ASC",
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($oVorlage_arr as $oVorlage) {
            $nID   = 0;
            $cName = 'Vorlage';
            if ($oVorlage->eTyp === BoxType::TEXT) {
                $nID   = 1;
                $cName = 'Inhalt';
            } elseif ($oVorlage->eTyp === BoxType::LINK) {
                $nID   = 2;
                $cName = 'Linkliste';
            } elseif ($oVorlage->eTyp === BoxType::PLUGIN) {
                $nID   = 3;
                $cName = 'Plugin';
            } elseif ($oVorlage->eTyp === BoxType::CATBOX) {
                $nID   = 4;
                $cName = 'Kategorie';
            }

            if (!isset($oVorlagen_arr[$nID])) {
                $oVorlagen_arr[$nID]               = new \stdClass();
                $oVorlagen_arr[$nID]->oVorlage_arr = [];
            }

            $oVorlagen_arr[$nID]->cName          = $cName;
            $oVorlagen_arr[$nID]->oVorlage_arr[] = $oVorlage;
        }

        return $oVorlagen_arr;
    }

    /**
     * @param int  $nSeite
     * @param bool $bGlobal
     * @return array|bool
     * @former holeBoxAnzeige()
     */
    public function getVisibility(int $nSeite, bool $bGlobal = true)
    {
        if ($this->visibility !== null) {
            return $this->visibility;
        }
        $oBoxAnzeige = [];
        $oBox_arr    = $this->db->selectAll('tboxenanzeige', 'nSeite', $nSeite);
        if (is_array($oBox_arr) && count($oBox_arr)) {
            foreach ($oBox_arr as $oBox) {
                $oBoxAnzeige[$oBox->ePosition] = (boolean)$oBox->bAnzeigen;
            }
            $this->visibility = $oBoxAnzeige;

            return $oBoxAnzeige;
        }

        return $nSeite !== 0 && $bGlobal
            ? $this->getVisibility(0)
            : false;
    }

    /**
     * @param string $ePosition
     * @return array
     * @former holeContainer()
     */
    public function getContainer(string $ePosition): array
    {
        return $this->db->selectAll(
            'tboxen',
            ['kBoxvorlage', 'ePosition'],
            [0, $ePosition],
            'kBox',
            'kBox ASC'
        );
    }

    /**
     * @return array
     */
    public function getInvisibleBoxes(): array
    {
        $unavailabe = \Functional\filter(\Template::getInstance()->getBoxLayoutXML(), function ($e) {
            return $e === false;
        });
        $mapped     = \Functional\map($unavailabe, function ($e, $key) {
            return "'" . $key . "'";
        });

        return $this->db->query(
            'SELECT tboxen.*, tboxvorlage.eTyp, tboxvorlage.cName, tboxvorlage.cTemplate 
                FROM tboxen 
                    LEFT JOIN tboxvorlage
                    ON tboxen.kBoxvorlage = tboxvorlage.kBoxvorlage
                WHERE ePosition IN (' . implode(',', $mapped) . ')',
            ReturnType::ARRAY_OF_OBJECTS
        );
    }
}
