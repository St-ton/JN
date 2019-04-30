<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Newsletter;

use DateTime;
use JTL\Alert\Alert;
use JTL\Backend\Revision;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Exceptions\EmptyResultSetException;
use JTL\Exceptions\InvalidInputException;
use JTL\Optin\Optin;
use JTL\Optin\OptinNewsletter;
use JTL\Shop;
use stdClass;

/**
 * Class Admin
 * @package JTL\Newsletter
 */
final class Admin
{
    /**
     * @var DbInterface
     */
    private $db;

    /**
     * Admin constructor.
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @param string $input
     * @return stdClass
     */
    public function getDateData($input): stdClass
    {
        $res = new stdClass();

        if (mb_strlen($input) > 0) {
            [$date, $time]        = \explode(' ', $input);
            [$year, $month, $day] = \explode('-', $date);
            [$hour, $minute]      = \explode(':', $time);

            $res->dZeit     = $day . '.' . $month . '.' . $year . ' ' . $hour . ':' . $minute;
            $res->cZeit_arr = [$day, $month, $year, $hour, $minute];
        }

        return $res;
    }

    /**
     * @param string $name
     * @param array  $customerGroups
     * @param string $subject
     * @param string $type
     * @return array
     */
    public function checkDefaultTemplate($name, $customerGroups, $subject, $type): array
    {
        $checks = [];
        if (empty($name)) {
            $checks['cName'] = 1;
        }
        if (!\is_array($customerGroups) || count($customerGroups) === 0) {
            $checks['kKundengruppe_arr'] = 1;
        }
        if (empty($subject)) {
            $checks['cBetreff'] = 1;
        }
        if (empty($type)) {
            $checks['cArt'] = 1;
        }

        return $checks;
    }

    /**
     * @param string $type
     * @return string
     */
    public function mapFileType(string $type): string
    {
        switch ($type) {
            case 'image/gif':
                return '.gif';
            case 'image/png':
                return '.png';
            case 'image/bmp':
                return '.bmp';
            default:
                return '.jpg';
        }
    }

    /**
     * @param string $text
     * @param array  $stdVars
     * @param bool   $noHTML
     * @return mixed|string
     */
    public function mapDefaultTemplate($text, $stdVars, $noHTML = false)
    {
        if (!\is_array($stdVars) || count($stdVars) === 0) {
            return $text;
        }
        foreach ($stdVars as $stdVar) {
            if ($stdVar->cTyp === 'TEXT') {
                if ($noHTML) {
                    $text = \strip_tags($this->br2nl(\str_replace(
                        '$#' . $stdVar->cName . '#$',
                        $stdVar->cInhalt,
                        $text
                    )));
                } else {
                    $text = \str_replace('$#' . $stdVar->cName . '#$', $stdVar->cInhalt, $text);
                }
            } elseif ($stdVar->cTyp === 'BILD') {
                // Bildervorlagen auf die URL SHOP umbiegen
                $stdVar->cInhalt = \str_replace(
                    \NEWSLETTER_STD_VORLAGE_URLSHOP,
                    Shop::getURL() . '/',
                    $stdVar->cInhalt
                );
                if ($noHTML) {
                    $text = \strip_tags($this->br2nl(
                        \str_replace(
                            '$#' . $stdVar->cName . '#$',
                            $stdVar->cInhalt,
                            $text
                        )
                    ));
                } else {
                    $cAltTag = '';
                    if (isset($stdVar->cAltTag) && mb_strlen($stdVar->cAltTag) > 0) {
                        $cAltTag = $stdVar->cAltTag;
                    }

                    if (isset($stdVar->cLinkURL) && mb_strlen($stdVar->cLinkURL) > 0) {
                        $text = \str_replace(
                            '$#' . $stdVar->cName . '#$',
                            '<a href="' .
                            $stdVar->cLinkURL .
                            '"><img src="' .
                            $stdVar->cInhalt . '" alt="' . $cAltTag . '" title="' .
                            $cAltTag .
                            '" /></a>',
                            $text
                        );
                    } else {
                        $text = \str_replace(
                            '$#' . $stdVar->cName . '#$',
                            '<img src="' .
                            $stdVar->cInhalt .
                            '" alt="' .
                            $cAltTag . '" title="' . $cAltTag . '" />',
                            $text
                        );
                    }
                }
            }
        }

        return $text;
    }

    /**
     * @param string $text
     * @return string
     */
    public function br2nl(string $text): string
    {
        return \str_replace(['<br>', '<br />', '<br/>'], "\n", $text);
    }

    /**
     * Baut eine Vorlage zusammen
     * Falls kNewsletterVorlage angegeben wurde und kNewsletterVorlageStd = 0 ist
     * wurde eine Vorlage editiert, die von einer Std Vorlage stammt.
     *
     * @param int $defaultTemplateID
     * @param int $templateID
     * @return stdClass|null
     */
    public function getDefaultTemplate(int $defaultTemplateID, int $templateID = 0): ?stdClass
    {
        if ($defaultTemplateID === 0 && $templateID === 0) {
            return null;
        }
        $tpl = new stdClass();
        if ($templateID > 0) {
            $tpl = $this->db->select(
                'tnewslettervorlage',
                'kNewsletterVorlage',
                $templateID
            );
            if (isset($tpl->kNewslettervorlageStd) && $tpl->kNewslettervorlageStd > 0) {
                $defaultTemplateID = $tpl->kNewslettervorlageStd;
            }
        }

        $defaultTpl = $this->db->select(
            'tnewslettervorlagestd',
            'kNewslettervorlageStd',
            $defaultTemplateID
        );
        if ($defaultTpl !== null && $defaultTpl->kNewslettervorlageStd > 0) {
            if (isset($tpl->kNewslettervorlageStd) && $tpl->kNewslettervorlageStd > 0) {
                $defaultTpl->kNewsletterVorlage = $tpl->kNewsletterVorlage;
                $defaultTpl->kKampagne          = $tpl->kKampagne;
                $defaultTpl->cName              = $tpl->cName;
                $defaultTpl->cBetreff           = $tpl->cBetreff;
                $defaultTpl->cArt               = $tpl->cArt;
                $defaultTpl->cArtikel           = mb_substr(mb_substr($tpl->cArtikel, 1), 0, -1);
                $defaultTpl->cHersteller        = mb_substr(mb_substr($tpl->cHersteller, 1), 0, -1);
                $defaultTpl->cKategorie         = mb_substr(mb_substr($tpl->cKategorie, 1), 0, -1);
                $defaultTpl->cKundengruppe      = $tpl->cKundengruppe;
                $defaultTpl->dStartZeit         = $tpl->dStartZeit;
            }

            $defaultTpl->oNewslettervorlageStdVar_arr = $this->db->selectAll(
                'tnewslettervorlagestdvar',
                'kNewslettervorlageStd',
                $defaultTemplateID
            );

            foreach ($defaultTpl->oNewslettervorlageStdVar_arr as $j => $nlTplStdVar) {
                $nlTplContent = new stdClass();
                if (isset($nlTplStdVar->kNewslettervorlageStdVar) && $nlTplStdVar->kNewslettervorlageStdVar > 0) {
                    $cSQL = ' AND kNewslettervorlage IS NULL';
                    if ($templateID > 0) {
                        $cSQL = ' AND kNewslettervorlage = ' . $templateID;
                    }

                    $nlTplContent = $this->db->query(
                        'SELECT *
                        FROM tnewslettervorlagestdvarinhalt
                        WHERE kNewslettervorlageStdVar = ' . (int)$nlTplStdVar->kNewslettervorlageStdVar .
                        $cSQL,
                        ReturnType::SINGLE_OBJECT
                    );
                }

                if (isset($nlTplContent->cInhalt) && mb_strlen($nlTplContent->cInhalt) > 0) {
                    $defaultTpl->oNewslettervorlageStdVar_arr[$j]->cInhalt = \str_replace(
                        \NEWSLETTER_STD_VORLAGE_URLSHOP,
                        Shop::getURL() . '/',
                        $nlTplContent->cInhalt
                    );
                    if (isset($nlTplContent->cLinkURL) && mb_strlen($nlTplContent->cLinkURL) > 0) {
                        $defaultTpl->oNewslettervorlageStdVar_arr[$j]->cLinkURL = $nlTplContent->cLinkURL;
                    }
                    if (isset($nlTplContent->cAltTag) && mb_strlen($nlTplContent->cAltTag) > 0) {
                        $defaultTpl->oNewslettervorlageStdVar_arr[$j]->cAltTag = $nlTplContent->cAltTag;
                    }
                } else {
                    $defaultTpl->oNewslettervorlageStdVar_arr[$j]->cInhalt = '';
                }
            }
        }

        return $defaultTpl;
    }

    /**
     * @param object $defaultTpl
     * @param int    $kNewslettervorlageStd
     * @param array  $post
     * @param int    $templateID
     * @return array
     * @throws \Exception
     */
    public function saveDefaultTemplate($defaultTpl, int $kNewslettervorlageStd, $post, int $templateID): array
    {
        $checks = [];
        if ($kNewslettervorlageStd <= 0) {
            return $checks;
        }
        if (!isset($post['kKundengruppe'])) {
            $post['kKundengruppe'] = null;
        }
        $checks = $this->checkDefaultTemplate(
            $post['cName'],
            $post['kKundengruppe'],
            $post['cBetreff'],
            $post['cArt']
        );

        if (!\is_array($checks) || count($checks) !== 0) {
            return $checks;
        }
        $dTag    = $post['dTag'];
        $dMonat  = $post['dMonat'];
        $dJahr   = $post['dJahr'];
        $dStunde = $post['dStunde'];
        $dMinute = $post['dMinute'];
        $dZeitDB = $dJahr . '-' . $dMonat . '-' . $dTag . ' ' . $dStunde . ':' . $dMinute . ':00';
        $oZeit   = $this->getDateData($dZeitDB);

        $cArtikel      = ';' . $post['cArtikel'] . ';';
        $cHersteller   = ';' . $post['cHersteller'] . ';';
        $cKategorie    = ';' . $post['cKategorie'] . ';';
        $cKundengruppe = ';' . \implode(';', $post['kKundengruppe']) . ';';
        if (isset($defaultTpl->oNewslettervorlageStdVar_arr)
            && \is_array($defaultTpl->oNewslettervorlageStdVar_arr)
            && count($defaultTpl->oNewslettervorlageStdVar_arr) > 0
        ) {
            foreach ($defaultTpl->oNewslettervorlageStdVar_arr as $i => $nlTplStdVar) {
                if ($nlTplStdVar->cTyp === 'TEXT') {
                    $defaultTpl->oNewslettervorlageStdVar_arr[$i]->cInhalt =
                        $post['kNewslettervorlageStdVar_' . $nlTplStdVar->kNewslettervorlageStdVar];
                }
                if ($nlTplStdVar->cTyp === 'BILD') {
                    $defaultTpl->oNewslettervorlageStdVar_arr[$i]->cLinkURL = $post['cLinkURL'];
                    $defaultTpl->oNewslettervorlageStdVar_arr[$i]->cAltTag  = $post['cAltTag'];
                }
            }
        }

        $tpl                        = new stdClass();
        $tpl->kNewslettervorlageStd = $kNewslettervorlageStd;
        $tpl->kKampagne             = (int)$post['kKampagne'];
        $tpl->kSprache              = $_SESSION['kSprache'];
        $tpl->cName                 = $post['cName'];
        $tpl->cBetreff              = $post['cBetreff'];
        $tpl->cArt                  = $post['cArt'];
        $tpl->cArtikel              = $cArtikel;
        $tpl->cHersteller           = $cHersteller;
        $tpl->cKategorie            = $cKategorie;
        $tpl->cKundengruppe         = $cKundengruppe;
        $tpl->cInhaltHTML           = $this->mapDefaultTemplate(
            $defaultTpl->cInhaltHTML,
            $defaultTpl->oNewslettervorlageStdVar_arr
        );
        $tpl->cInhaltText           = $this->mapDefaultTemplate(
            $defaultTpl->cInhaltText,
            $defaultTpl->oNewslettervorlageStdVar_arr,
            true
        );

        $dt  = new DateTime($oZeit->dZeit);
        $now = new DateTime();

        $tpl->dStartZeit = ($dt > $now)
            ? $dt->format('Y-m-d H:i:s')
            : $now->format('Y-m-d H:i:s');

        if ($templateID > 0) {
            $revision = new Revision($this->db);
            $revision->addRevision('newsletterstd', $templateID, true);

            $upd                = new stdClass();
            $upd->cName         = $tpl->cName;
            $upd->cBetreff      = $tpl->cBetreff;
            $upd->kKampagne     = (int)$tpl->kKampagne;
            $upd->cArt          = $tpl->cArt;
            $upd->cArtikel      = $tpl->cArtikel;
            $upd->cHersteller   = $tpl->cHersteller;
            $upd->cKategorie    = $tpl->cKategorie;
            $upd->cKundengruppe = $tpl->cKundengruppe;
            $upd->cInhaltHTML   = $tpl->cInhaltHTML;
            $upd->cInhaltText   = $tpl->cInhaltText;
            $upd->dStartZeit    = $tpl->dStartZeit;
            $this->db->update(
                'tnewslettervorlage',
                'kNewsletterVorlage',
                $templateID,
                $upd
            );
        } else {
            $templateID = $this->db->insert('tnewslettervorlage', $tpl);
        }
        if ($templateID > 0
            && isset($defaultTpl->oNewslettervorlageStdVar_arr)
            && \is_array($defaultTpl->oNewslettervorlageStdVar_arr)
            && count($defaultTpl->oNewslettervorlageStdVar_arr) > 0
        ) {
            $this->db->delete(
                'tnewslettervorlagestdvarinhalt',
                'kNewslettervorlage',
                $templateID
            );
            $uploadDir = PFAD_ROOT . \PFAD_BILDER . \PFAD_NEWSLETTERBILDER;
            foreach ($defaultTpl->oNewslettervorlageStdVar_arr as $i => $nlTplStdVar) {
                $imageExists = false;
                if ($nlTplStdVar->cTyp === 'BILD') {
                    if (!\is_dir($uploadDir . $templateID)) {
                        \mkdir($uploadDir . $templateID);
                    }
                    $idx = 'kNewslettervorlageStdVar_' . $nlTplStdVar->kNewslettervorlageStdVar;
                    if (isset($_FILES[$idx]['name']) && mb_strlen($_FILES[$idx]['name']) > 0) {
                        $file = $uploadDir . $templateID .
                            '/kNewslettervorlageStdVar_' . $nlTplStdVar->kNewslettervorlageStdVar .
                            $this->mapFileType($_FILES['kNewslettervorlageStdVar_' .
                            $nlTplStdVar->kNewslettervorlageStdVar]['type']);
                        if (\file_exists($file)) {
                            \unlink($file);
                        }
                        \move_uploaded_file(
                            $_FILES['kNewslettervorlageStdVar_' . $nlTplStdVar->kNewslettervorlageStdVar]['tmp_name'],
                            $file
                        );
                        if (isset($post['cLinkURL']) && mb_strlen($post['cLinkURL']) > 0) {
                            $defaultTpl->oNewslettervorlageStdVar_arr[$i]->cLinkURL = $post['cLinkURL'];
                        }
                        if (isset($post['cAltTag']) && mb_strlen($post['cAltTag']) > 0) {
                            $defaultTpl->oNewslettervorlageStdVar_arr[$i]->cAltTag = $post['cAltTag'];
                        }
                        $defaultTpl->oNewslettervorlageStdVar_arr[$i]->cInhalt =
                            Shop::getURL() . '/' . \PFAD_BILDER . \PFAD_NEWSLETTERBILDER . $templateID .
                            '/kNewslettervorlageStdVar_' . $nlTplStdVar->kNewslettervorlageStdVar .
                            $this->mapFileType(
                                $_FILES['kNewslettervorlageStdVar_' .
                                $nlTplStdVar->kNewslettervorlageStdVar]['type']
                            );

                        $imageExists = true;
                    }
                }

                $nlTplContent                           = new stdClass();
                $nlTplContent->kNewslettervorlageStdVar = $nlTplStdVar->kNewslettervorlageStdVar;
                $nlTplContent->kNewslettervorlage       = $templateID;
                if ($nlTplStdVar->cTyp === 'TEXT') {
                    $nlTplContent->cInhalt = $nlTplStdVar->cInhalt;
                } elseif ($nlTplStdVar->cTyp === 'BILD') {
                    if ($imageExists) {
                        $nlTplContent->cInhalt = $defaultTpl->oNewslettervorlageStdVar_arr[$i]->cInhalt;
                        if (isset($post['cLinkURL']) && mb_strlen($post['cLinkURL']) > 0) {
                            $nlTplContent->cLinkURL = $post['cLinkURL'];
                        }
                        if (isset($post['cAltTag']) && mb_strlen($post['cAltTag']) > 0) {
                            $nlTplContent->cAltTag = $post['cAltTag'];
                        }
                        $upd              = new stdClass();
                        $upd->cInhaltHTML = $this->mapDefaultTemplate(
                            $defaultTpl->cInhaltHTML,
                            $defaultTpl->oNewslettervorlageStdVar_arr
                        );
                        $upd->cInhaltText = $this->mapDefaultTemplate(
                            $defaultTpl->cInhaltText,
                            $defaultTpl->oNewslettervorlageStdVar_arr,
                            true
                        );
                        $this->db->update(
                            'tnewslettervorlage',
                            'kNewsletterVorlage',
                            $templateID,
                            $upd
                        );
                    } else {
                        $nlTplContent->cInhalt = $nlTplStdVar->cInhalt;
                        if (isset($post['cLinkURL']) && mb_strlen($post['cLinkURL']) > 0) {
                            $nlTplContent->cLinkURL = $post['cLinkURL'];
                        }
                        if (isset($post['cAltTag']) && mb_strlen($post['cAltTag']) > 0) {
                            $nlTplContent->cAltTag = $post['cAltTag'];
                        }
                    }
                }
                $this->db->insert('tnewslettervorlagestdvarinhalt', $nlTplContent);
            }
        }

        return $checks;
    }

    /**
     * @param array $post
     * @return array|null|stdClass
     * @throws \Exception
     */
    public function saveTemplate($post)
    {
        $alertHelper = Shop::Container()->getAlertService();
        $tpl         = null;
        $checks      = $this->checkTemplate(
            $post['cName'],
            $post['kKundengruppe'],
            $post['cBetreff'],
            $post['cArt'],
            $post['cHtml'],
            $post['cText']
        );

        if (\is_array($checks) && count($checks) === 0) {
            $dTag    = $post['dTag'];
            $dMonat  = $post['dMonat'];
            $dJahr   = $post['dJahr'];
            $dStunde = $post['dStunde'];
            $dMinute = $post['dMinute'];
            $dZeitDB = $dJahr . '-' . $dMonat . '-' . $dTag . ' ' . $dStunde . ':' . $dMinute . ':00';
            $oZeit   = $this->getDateData($dZeitDB);

            $kNewsletterVorlage = isset($post['kNewsletterVorlage'])
                ? (int)$post['kNewsletterVorlage']
                : null;
            $kKampagne          = (int)$post['kKampagne'];
            $cArtikel           = $post['cArtikel'];
            $cHersteller        = $post['cHersteller'];
            $cKategorie         = $post['cKategorie'];
            $cKundengruppe      = ';' . \implode(';', $post['kKundengruppe']) . ';';
            $cArtikel           = ';' . $cArtikel . ';';
            $cHersteller        = ';' . $cHersteller . ';';
            $cKategorie         = ';' . $cKategorie . ';';
            $tpl                = new stdClass();
            if ($kNewsletterVorlage !== null) {
                $tpl->kNewsletterVorlage = $kNewsletterVorlage;
            }
            $tpl->kSprache      = (int)$_SESSION['kSprache'];
            $tpl->kKampagne     = $kKampagne;
            $tpl->cName         = $post['cName'];
            $tpl->cBetreff      = $post['cBetreff'];
            $tpl->cArt          = $post['cArt'];
            $tpl->cArtikel      = $cArtikel;
            $tpl->cHersteller   = $cHersteller;
            $tpl->cKategorie    = $cKategorie;
            $tpl->cKundengruppe = $cKundengruppe;
            $tpl->cInhaltHTML   = $post['cHtml'];
            $tpl->cInhaltText   = $post['cText'];

            $dt              = new DateTime($oZeit->dZeit);
            $now             = new DateTime();
            $tpl->dStartZeit = ($dt > $now)
                ? $dt->format('Y-m-d H:i:s')
                : $now->format('Y-m-d H:i:s');
            if (isset($post['kNewsletterVorlage']) && (int)$post['kNewsletterVorlage'] > 0) {
                $revision = new Revision(Shop::Container()->getDB());
                $revision->addRevision('newsletter', $kNewsletterVorlage, true);
                $upd                = new stdClass();
                $upd->cName         = $tpl->cName;
                $upd->kKampagne     = $tpl->kKampagne;
                $upd->cBetreff      = $tpl->cBetreff;
                $upd->cArt          = $tpl->cArt;
                $upd->cArtikel      = $tpl->cArtikel;
                $upd->cHersteller   = $tpl->cHersteller;
                $upd->cKategorie    = $tpl->cKategorie;
                $upd->cKundengruppe = $tpl->cKundengruppe;
                $upd->cInhaltHTML   = $tpl->cInhaltHTML;
                $upd->cInhaltText   = $tpl->cInhaltText;
                $upd->dStartZeit    = $tpl->dStartZeit;
                $this->db->update('tnewslettervorlage', 'kNewsletterVorlage', $kNewsletterVorlage, $upd);
                $alertHelper->addAlert(
                    Alert::TYPE_SUCCESS,
                    \sprintf(__('successNewsletterTemplateEdit'), $tpl->cName),
                    'successNewsletterTemplateEdit'
                );
            } else {
                $kNewsletterVorlage = $this->db->insert('tnewslettervorlage', $tpl);
                $alertHelper->addAlert(
                    Alert::TYPE_SUCCESS,
                    \sprintf(__('successNewsletterTemplateSave'), $tpl->cName),
                    'successNewsletterTemplateSave'
                );
            }
            $tpl->kNewsletterVorlage = $kNewsletterVorlage;

            return $tpl;
        }

        return $checks;
    }

    /**
     * @param string $name
     * @param array  $customerGroups
     * @param string $subject
     * @param string $type
     * @param string $html
     * @param string $text
     * @return array
     */
    public function checkTemplate($name, $customerGroups, $subject, $type, $html, $text): array
    {
        $checks = [];
        if (empty($name)) {
            $checks['cName'] = 1;
        }
        if (!\is_array($customerGroups) || count($customerGroups) === 0) {
            $checks['kKundengruppe_arr'] = 1;
        }
        if (empty($subject)) {
            $checks['cBetreff'] = 1;
        }
        if (empty($type)) {
            $checks['cArt'] = 1;
        }
        if (empty($html)) {
            $checks['cHtml'] = 1;
        }
        if (empty($text)) {
            $checks['cText'] = 1;
        }

        return $checks;
    }

    /**
     * @param string $productString
     * @return stdClass
     */
    public function getProductData($productString): stdClass
    {
        $productIDs                = \explode(';', $productString);
        $productData               = new stdClass();
        $productData->kArtikel_arr = [];
        $productData->cArtNr_arr   = [];
        if (\is_array($productIDs) && count($productIDs) > 0) {
            foreach ($productIDs as $item) {
                if ($item) {
                    $productData->kArtikel_arr[] = $item;
                }
            }
            // hole zu den kArtikeln die passende cArtNr
            foreach ($productData->kArtikel_arr as $kArtikel) {
                $cArtNr = $this->getProductNo($kArtikel);
                if (mb_strlen($cArtNr) > 0) {
                    $productData->cArtNr_arr[] = $cArtNr;
                }
            }
        }

        return $productData;
    }

    /**
     * @param string $cKundengruppe
     * @return array
     */
    public function getCustomerGroupData($cKundengruppe): array
    {
        $groupIDs = [];
        foreach (\explode(';', $cKundengruppe) as $item) {
            if (mb_strlen($item) > 0) {
                $groupIDs[] = $item;
            }
        }

        return $groupIDs;
    }

    /**
     * @param int $kArtikel
     * @return string
     */
    private function getProductNo(int $kArtikel): string
    {
        $cArtNr   = '';
        $oArtikel = null;

        if ($kArtikel > 0) {
            $oArtikel = $this->db->select('tartikel', 'kArtikel', $kArtikel);
        }

        return $oArtikel->cArtNr ?? $cArtNr;
    }

    /**
     * @param array $recipientIDs
     * @return bool
     */
    public function activateSubscribers($recipientIDs): bool
    {
        if (!\is_array($recipientIDs) || count($recipientIDs) === 0) {
            return false;
        }
        $where      = ' IN (' . \implode(',', \array_map('\intval', $recipientIDs)) . ')';
        $recipients = $this->db->query(
            'SELECT *
            FROM tnewsletterempfaenger
            WHERE kNewsletterEmpfaenger' .
            $where,
            ReturnType::ARRAY_OF_OBJECTS
        );

        if (count($recipients) === 0) {
            return false;
        }
        $this->db->query(
            'UPDATE tnewsletterempfaenger
            SET nAktiv = 1
            WHERE kNewsletterEmpfaenger' . $where,
            ReturnType::AFFECTED_ROWS
        );
        foreach ($recipients as $recipient) {
            $hist               = new stdClass();
            $hist->kSprache     = $recipient->kSprache;
            $hist->kKunde       = $recipient->kKunde;
            $hist->cAnrede      = $recipient->cAnrede;
            $hist->cVorname     = $recipient->cVorname;
            $hist->cNachname    = $recipient->cNachname;
            $hist->cEmail       = $recipient->cEmail;
            $hist->cOptCode     = $recipient->cOptCode;
            $hist->cLoeschCode  = $recipient->cLoeschCode;
            $hist->cAktion      = 'Aktiviert';
            $hist->dEingetragen = $recipient->dEingetragen;
            $hist->dAusgetragen = 'NOW()';
            $hist->dOptCode     = '_DBNULL_';

            $this->db->insert('tnewsletterempfaengerhistory', $hist);
        }
        (new Optin(OptinNewsletter::class))
            ->getOptinInstance()
            ->bulkActivateOptins($recipients);

        return true;
    }

    /**
     * @param array $recipientIDs
     * @return bool
     */
    public function deleteSubscribers($recipientIDs): bool
    {
        if (!\is_array($recipientIDs) || count($recipientIDs) === 0) {
            return false;
        }
        $where      = ' IN (' . \implode(',', \array_map('\intval', $recipientIDs)) . ')';
        $recipients = $this->db->query(
            'SELECT *
            FROM tnewsletterempfaenger
            WHERE kNewsletterEmpfaenger' .
            $where,
            ReturnType::ARRAY_OF_OBJECTS
        );

        if (count($recipients) === 0) {
            return false;
        }
        $this->db->query(
            'DELETE FROM tnewsletterempfaenger
            WHERE kNewsletterEmpfaenger' . $where,
            ReturnType::AFFECTED_ROWS
        );
        foreach ($recipients as $recipient) {
            $hist               = new stdClass();
            $hist->kSprache     = $recipient->kSprache;
            $hist->kKunde       = $recipient->kKunde;
            $hist->cAnrede      = $recipient->cAnrede;
            $hist->cVorname     = $recipient->cVorname;
            $hist->cNachname    = $recipient->cNachname;
            $hist->cEmail       = $recipient->cEmail;
            $hist->cOptCode     = $recipient->cOptCode;
            $hist->cLoeschCode  = $recipient->cLoeschCode;
            $hist->cAktion      = 'Geloescht';
            $hist->dEingetragen = $recipient->dEingetragen;
            $hist->dAusgetragen = 'NOW()';
            $hist->dOptCode     = '_DBNULL_';

            $this->db->insert('tnewsletterempfaengerhistory', $hist);
        }
        try {
            (new Optin())
                ->bulkDeleteOptins($recipients, 'cOptCode');
        } catch (EmptyResultSetException $e) {
            // suppress exception, because a optin implementation class is not needed here
        }

        return true;
    }

    /**
     * @param stdClass $cAktiveSucheSQL
     * @return int
     */
    public function getSubscriberCount($cAktiveSucheSQL): int
    {
        return (int)Shop::Container()->getDB()->query(
            'SELECT COUNT(*) AS nAnzahl
                FROM tnewsletterempfaenger
                WHERE kSprache = ' . (int)$_SESSION['kSprache'] . $cAktiveSucheSQL->cWHERE,
            ReturnType::SINGLE_OBJECT
        )->nAnzahl;
    }

    /**
     * @param string   $cSQL
     * @param stdClass $cAktiveSucheSQL
     * @return array
     */
    public function getSubscribers($cSQL, $cAktiveSucheSQL): array
    {
        return $this->db->query(
            "SELECT tnewsletterempfaenger.*,
                DATE_FORMAT(tnewsletterempfaenger.dEingetragen, '%d.%m.%Y %H:%i') AS dEingetragen_de,
                DATE_FORMAT(tnewsletterempfaenger.dLetzterNewsletter, '%d.%m.%Y %H:%i') AS dLetzterNewsletter_de,
                tkunde.kKundengruppe, tkundengruppe.cName, tnewsletterempfaengerhistory.cOptIp,
                DATE_FORMAT(tnewsletterempfaengerhistory.dOptCode, '%d.%m.%Y %H:%i') AS optInDate
                FROM tnewsletterempfaenger
                LEFT JOIN tkunde
                    ON tkunde.kKunde = tnewsletterempfaenger.kKunde
                LEFT JOIN tkundengruppe
                    ON tkundengruppe.kKundengruppe = tkunde.kKundengruppe
                LEFT JOIN tnewsletterempfaengerhistory
                    ON tnewsletterempfaengerhistory.cEmail = tnewsletterempfaenger.cEmail
                      AND tnewsletterempfaengerhistory.cAktion = 'Eingetragen'
                WHERE tnewsletterempfaenger.kSprache = " . (int)$_SESSION['kSprache'] .
            $cAktiveSucheSQL->cWHERE . '
                ORDER BY tnewsletterempfaenger.dEingetragen DESC' . $cSQL,
            ReturnType::ARRAY_OF_OBJECTS
        );
    }
}
