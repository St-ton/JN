<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Boxes\Admin;


use Boxes\BoxType;
use DB\DbInterface;
use DB\ReturnType;
use function Functional\group;
use function Functional\map;
use Services\JTL\BoxServiceInterface;

/**
 * Class BoxAdmin
 * @package Boxes\Admin
 */
final class BoxAdmin
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
        if ($id < 1) {
            return false;
        }
        $affectedBoxes = map($this->db->queryPrepared(
            'SELECT kBox 
                FROM tboxen 
                WHERE kBox = :bid OR kContainer = :bid',
            ['bid' => $id],
            ReturnType::ARRAY_OF_OBJECTS
        ), function ($e) {
            return (int)$e->kBox;
        });

        return count($affectedBoxes) > 0
            && $this->db->query(
                'DELETE 
                    FROM tboxen
                    WHERE kBox IN (' . implode(',', $affectedBoxes) . ')',
                ReturnType::AFFECTED_ROWS
            ) > 0
            && $this->db->query(
                'DELETE 
                    FROM tboxensichtbar
                    WHERE kBox IN (' . implode(',', $affectedBoxes) . ')',
                ReturnType::AFFECTED_ROWS
            ) > 0;
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
     * @param int    $pageID
     * @param string $position
     * @param int    $containerID
     * @return int
     * @former letzteSortierID()
     */
    private function getLastSortID(int $pageID, string $position = 'left', int $containerID = 0): int
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
                'pageid'      => $pageID,
                'position'    => $position,
                'containerid' => $containerID
            ],
            ReturnType::SINGLE_OBJECT
        );

        return $oBox ? ++$oBox->nSort : 0;
    }

    /**
     * @param int    $boxID
     * @param string $isoCode
     * @return mixed
     */
    public function getContent(int $boxID, string $isoCode = '')
    {

        return strlen($isoCode) > 0
            ? $this->db->select('tboxsprache', 'kBox', $boxID, 'cISO', $isoCode)
            : $this->db->selectAll('tboxsprache', 'kBox', $boxID);
    }

    /**
     * @param int $boxID
     * @return \stdClass
     */
    public function getByID(int $boxID): \stdClass
    {
        $oBox = $this->db->queryPrepared(
            'SELECT tboxen.kBox, tboxen.kBoxvorlage, tboxen.kCustomID, tboxen.cTitel, tboxen.ePosition,
                tboxvorlage.eTyp, tboxvorlage.cName, tboxvorlage.cVerfuegbar, tboxvorlage.cTemplate
                FROM tboxen
                LEFT JOIN tboxvorlage 
                    ON tboxen.kBoxvorlage = tboxvorlage.kBoxvorlage
                WHERE kBox = :bxid',
            ['bxid' => $boxID],
            ReturnType::SINGLE_OBJECT
        );

        $oBox->oSprache_arr      = ($oBox && ($oBox->eTyp === BoxType::TEXT || $oBox->eTyp === BoxType::CATBOX))
            ? $this->getContent($boxID)
            : [];
        $oBox->kBox              = (int)$oBox->kBox;
        $oBox->kBoxvorlage       = (int)$oBox->kBoxvorlage;
        $oBox->supportsRevisions = $oBox->kBoxvorlage === BOX_EIGENE_BOX_OHNE_RAHMEN || $oBox->kBoxvorlage === BOX_EIGENE_BOX_MIT_RAHMEN;

        return $oBox;
    }

    /**
     * @param int    $baseID
     * @param int    $pageID
     * @param string $position
     * @param int    $containerID
     * @return bool
     */
    public function create(int $baseID, int $pageID, string $position = 'left', int $containerID = 0): bool
    {
        $validPageTypes    = $this->getValidPageTypes();
        $oBox              = new \stdClass();
        $template          = $this->getTemplate($baseID);
        $oBox->cTitel      = $template === null
            ? ''
            : $template->cName;
        $oBox->kBoxvorlage = $baseID;
        $oBox->ePosition   = $position;
        $oBox->kContainer  = $containerID;
        $oBox->kCustomID   = (isset($template->kCustomID) && is_numeric($template->kCustomID))
            ? (int)$template->kCustomID
            : 0;

        $boxID = $this->db->insert('tboxen', $oBox);
        if ($boxID) {
            $cnt                = count($validPageTypes);
            $oBoxSichtbar       = new \stdClass();
            $oBoxSichtbar->kBox = $boxID;
            for ($i = 0; $i < $cnt; ++$i) {
                $oBoxSichtbar->nSort  = $this->getLastSortID($pageID, $position, $containerID);
                $oBoxSichtbar->kSeite = $i;
                $oBoxSichtbar->bAktiv = ($pageID === $i || $pageID === 0) ? 1 : 0;
                $this->db->insert('tboxensichtbar', $oBoxSichtbar);
            }

            return true;
        }

        return false;
    }

    /**
     * @param int    $boxID
     * @param string $title
     * @param int    $customID
     * @return bool
     * @former bearbeiteBox()
     */
    public function update(int $boxID, $title, int $customID = 0): bool
    {
        $oBox            = new \stdClass();
        $oBox->cTitel    = $title;
        $oBox->kCustomID = $customID;

        return $this->db->update('tboxen', 'kBox', $boxID, $oBox) >= 0;
    }

    /**
     * @param int    $boxID
     * @param string $isoCode
     * @param string $title
     * @param string $content
     * @return bool
     * @former bearbeiteBoxSprache()
     */
    public function updateLanguage(int $boxID, string $isoCode, string $title, string $content): bool
    {
        $oBox = $this->db->select('tboxsprache', 'kBox', $boxID, 'cISO', $isoCode);
        if (isset($oBox->kBox)) {
            $upd          = new \stdClass();
            $upd->cTitel  = $title;
            $upd->cInhalt = $content;

            return $this->db->update('tboxsprache', ['kBox', 'cISO'], [$boxID, $isoCode], $upd) >= 0;
        }
        $_ins          = new \stdClass();
        $_ins->kBox    = $boxID;
        $_ins->cISO    = $isoCode;
        $_ins->cTitel  = $title;
        $_ins->cInhalt = $content;

        return $this->db->insert('tboxsprache', $_ins) > 0;
    }

    /**
     * @param int      $pageID
     * @param string   $position
     * @param bool|int $show
     * @return bool
     * @former setzeBoxAnzeige()
     */
    public function setVisibility(int $pageID, string $position, $show): bool
    {
        $show           = (int)$show;
        $validPageTypes = $this->getValidPageTypes();
        if ($pageID === 0) {
            $ok = true;
            for ($i = 0; $i < count($validPageTypes) && $ok; $i++) {
                $ok = $this->db->executeQueryPrepared(
                        "REPLACE INTO tboxenanzeige 
                            SET bAnzeigen = :show,
                                nSeite = :page, 
                                ePosition = :position",
                        [
                            'show'     => $show,
                            'page'     => $i,
                            'position' => $position
                        ],
                        ReturnType::DEFAULT
                    ) && $ok;
            }

            return $ok;
        }

        return $this->db->executeQueryPrepared(
            "REPLACE INTO tboxenanzeige 
                SET bAnzeigen = :show, 
                    nSeite = :page, 
                    ePosition = :position",
            ['show' => $show, 'page' => $pageID, 'position' => $position],
            ReturnType::DEFAULT
        );
    }

    /**
     * @param int      $boxID
     * @param int      $pageID
     * @param int      $nSort
     * @param bool|int $active
     * @return bool
     * @former sortBox()
     */
    public function sort(int $boxID, int $pageID, int $nSort, $active = true): bool
    {
        $active         = (int)$active;
        $validPageTypes = $this->getValidPageTypes();
        if ($pageID === 0) {
            $ok = true;
            for ($i = 0; $i < count($validPageTypes) && $ok; $i++) {
                $oBox = $this->db->select('tboxensichtbar', 'kBox', $boxID);
                $ok   = !empty($oBox)
                    ? ($this->db->query(
                            "UPDATE tboxensichtbar 
                                SET nSort = " . $nSort . ",
                                    bAktiv = " . $active . " 
                                WHERE kBox = " . $boxID . " 
                                    AND kSeite = " . $i,
                            ReturnType::DEFAULT
                        ) !== false)
                    : ($this->db->query(
                            "INSERT INTO tboxensichtbar 
                                SET kBox = " . $boxID . ",
                                    kSeite = " . $i . ", 
                                    nSort = " . $nSort . ", 
                                    bAktiv = " . $active,
                            ReturnType::DEFAULT
                        ) === true);
            }

            return $ok;
        }

        return $this->db->query(
                "REPLACE INTO tboxensichtbar 
                  SET kBox = " . $boxID . ", 
                      kSeite = " . $pageID . ", 
                      nSort = " . $nSort . ", 
                      bAktiv = " . $active,
                ReturnType::AFFECTED_ROWS
            ) !== false;
    }

    /**
     * @param int          $boxID
     * @param int          $kSeite
     * @param string|array $cFilter
     * @return int
     */
    public function filterBoxVisibility(int $boxID, int $kSeite, $cFilter = ''): int
    {
        if (is_array($cFilter)) {
            $cFilter = array_unique($cFilter);
            $cFilter = implode(',', $cFilter);
        }
        $upd          = new \stdClass();
        $upd->cFilter = $cFilter;

        return $this->db->update('tboxensichtbar', ['kBox', 'kSeite'], [$boxID, $kSeite], $upd);
    }

    /**
     * @param int      $boxID
     * @param int      $pageID
     * @param bool|int $active
     * @return bool
     * @former aktiviereBox()
     */
    public function activate(int $boxID, int $pageID, $active = true): bool
    {
        $active         = (int)$active;
        $validPageTypes = $this->getValidPageTypes();
        if ($pageID === 0) {
            $ok  = true;
            $upd = new \stdClass();
            for ($i = 0; $i < count($validPageTypes) && $ok; ++$i) {
                $upd->bAktiv = $active;
                $ok          = $this->db->update(
                        'tboxensichtbar',
                        ['kBox', 'kSeite'],
                        [$boxID, $i],
                        $upd
                    ) >= 0;
            }

            return $ok;
        }
        $upd         = new \stdClass();
        $upd->bAktiv = $active;

        return $this->db->update('tboxensichtbar', ['kBox', 'kSeite'], [$boxID, 0], $upd) >= 0;
    }

    /**
     * @param int $pageID
     * @return array
     * @former holeVorlagen()
     */
    public function getTemplates(int $pageID = -1): array
    {
        $templates    = [];
        $cSQL         = $pageID >= 0
            ? 'WHERE (cVerfuegbar = "' . $pageID . '" OR cVerfuegbar = "0")'
            : '';
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

            if (!isset($templates[$nID])) {
                $templates[$nID]               = new \stdClass();
                $templates[$nID]->oVorlage_arr = [];
            }

            $templates[$nID]->cName          = $cName;
            $templates[$nID]->oVorlage_arr[] = $oVorlage;
        }

        return $templates;
    }

    /**
     * @param int  $pageID
     * @param bool $global
     * @return array|bool
     * @former holeBoxAnzeige()
     */
    public function getVisibility(int $pageID, bool $global = true)
    {
        if ($this->visibility !== null) {
            return $this->visibility;
        }
        $oBoxAnzeige = [];
        $oBox_arr    = $this->db->selectAll('tboxenanzeige', 'nSeite', $pageID);
        if (count($oBox_arr) > 0) {
            foreach ($oBox_arr as $oBox) {
                $oBoxAnzeige[$oBox->ePosition] = (bool)$oBox->bAnzeigen;
            }
            $this->visibility = $oBoxAnzeige;

            return $oBoxAnzeige;
        }

        return $pageID !== 0 && $global
            ? $this->getVisibility(0)
            : false;
    }

    /**
     * @param string $position
     * @return array
     * @former holeContainer()
     */
    public function getContainer(string $position): array
    {
        return $this->db->selectAll(
            'tboxen',
            ['kBoxvorlage', 'ePosition'],
            [BOX_CONTAINER, $position],
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
                WHERE ePosition IN (' . implode(',', $mapped) . ') 
                    OR (kContainer > 0  AND kContainer NOT IN (SELECT kBox FROM tboxen))',
            ReturnType::ARRAY_OF_OBJECTS
        );
    }
}
