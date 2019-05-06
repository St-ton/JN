<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL;

use DateTime;
use InvalidArgumentException;
use JTL\Cron\LegacyCron;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Helpers\Text;
use JTL\Mail\Mail\Attachment;
use JTL\Mail\Mail\Mail;
use JTL\Mail\Mailer;
use SmartyException;
use stdClass;
use function Functional\first;
use function Functional\map;

/**
 * Class Statusmail
 * @package JTL
 */
class Statusmail
{
    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var string
     */
    private $dateStart;

    /**
     * @var string
     */
    private $dateEnd;

    /**
     * Statusmail constructor.
     *
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @return bool
     */
    public function updateConfig(): bool
    {
        if ((int)$_POST['nAktiv'] === 0
            || (Text::filterEmailAddress($_POST['cEmail']) !== false
                && \is_array($_POST['cIntervall_arr'])
                && \count($_POST['cIntervall_arr']) > 0
                && \is_array($_POST['cInhalt_arr'])
                && \count($_POST['cInhalt_arr']) > 0)
        ) {
            $this->db->query('TRUNCATE TABLE tstatusemail', ReturnType::DEFAULT);
            $this->db->query(
                "DELETE tcron, tjobqueue
                    FROM tcron
                    LEFT JOIN tjobqueue 
                        ON tjobqueue.cronID = tcron.cronID
                    WHERE tcron.jobType = 'statusemail'",
                ReturnType::DEFAULT
            );
            foreach ($_POST['cIntervall_arr'] as $interval) {
                $interval              = (int)$interval;
                $statusMail            = new stdClass();
                $statusMail->cEmail    = $_POST['cEmail'];
                $statusMail->nInterval = $interval;
                $statusMail->cInhalt   = Text::createSSK($_POST['cInhalt_arr']);
                $statusMail->nAktiv    = (int)$_POST['nAktiv'];
                $statusMail->dLastSent = 'NOW()';

                $id = $this->db->insert('tstatusemail', $statusMail);
                $this->createCronJob($id, $interval * 24);
            }

            return true;
        }

        return false;
    }

    /**
     * @param int $id
     * @param int $nAlleXStunden
     * @return bool
     */
    private function createCronJob(int $id, int $nAlleXStunden): bool
    {
        $d = new DateTime();
        $d->modify('+1 days');
        $d->setTime(0, 0);
        $oCron = new LegacyCron(
            0,
            $id,
            $nAlleXStunden,
            'statusemail',
            'statusemail',
            'tstatusemail',
            'id',
            $d->format('Y-m-d H:i:s'),
            $d->format('H:i:s')
        );

        return $oCron->speicherInDB() !== false;
    }

    /**
     * @return stdClass
     */
    public function loadConfig(): stdClass
    {
        $data = $this->db->query(
            'SELECT * FROM tstatusemail',
            ReturnType::ARRAY_OF_OBJECTS
        );

        $first                        = first($data);
        $conf                         = new stdClass();
        $conf->cIntervallMoeglich_arr = $this->getPossibleIntervals();
        $conf->cInhaltMoeglich_arr    = $this->getPossibleContentTypes();
        $conf->nIntervall_arr         = map($data, function ($e) {
            return (int)$e->nInterval;
        });
        $conf->nInhalt_arr            = Text::parseSSK($first->cInhalt ?? '');
        $conf->cEmail                 = $first->cEmail ?? '';
        $conf->nAktiv                 = (int)($first->nAktiv ?? 0);

        return $conf;
    }

    /**
     * @return array
     */
    private function getPossibleIntervals(): array
    {
        return [
            'Tagesbericht'  => 1,
            'Wochenbericht' => 7,
            'Monatsbericht' => 30
        ];
    }

    /**
     * @return array
     */
    private function getPossibleContentTypes(): array
    {
        return [
            'Anzahl Produkte pro Kundengruppe'            => 1,
            'Anzahl Neukunden'                            => 2,
            'Anzahl Neukunden, die gekauft haben'         => 3,
            'Anzahl Bestellungen'                         => 4,
            'Anzahl Bestellungen von Neukunden'           => 5,
            'Anzahl Zahlungseingänge zu Bestellungen'     => 23,
            'Anzahl versendeter Bestellungen'             => 24,
            'Anzahl Besucher'                             => 6,
            'Anzahl Besucher von Suchmaschinen'           => 7,
            'Anzahl Bewertungen'                          => 8,
            'Anzahl Bewertungen nicht freigeschaltet'     => 9,
            'Anzahl Bewertungsguthaben gezahlt'           => 10,
            'Anzahl geworbener Kunden'                    => 13,
            'Anzahl geworbener Kunden, die gekauft haben' => 14,
            'Anzahl versendeter Wunschlisten'             => 15,
            'Anzahl durchgeführter Umfragen'              => 16,
            'Anzahl neuer Newskommentare'                 => 17,
            'Anzahl Newskommentare nicht freigeschaltet'  => 18,
            'Anzahl neuer Produktanfragen'                => 19,
            'Anzahl neuer Verfügbarkeitsanfragen'         => 20,
            'Anzahl Produktvergleiche'                    => 21,
            'Anzahl genutzter Kupons'                     => 22,
            'Letzte Fehlermeldungen im Systemlog'         => 25,
            'Letzte Hinweise im Systemlog'                => 26,
            'Letzte Debugeinträge im Systemlog'           => 27
        ];
    }

    /**
     * @return array
     */
    private function getProductCountPerCustomerGroup(): array
    {
        $products = [];
        // Hole alle Kundengruppen im Shop
        $customerGroups = $this->db->query(
            'SELECT kKundengruppe, cName FROM tkundengruppe',
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($customerGroups as $oKundengruppe) {
            $productData            = $this->db->queryPrepared(
                'SELECT COUNT(*) AS cnt
                    FROM tartikel
                    LEFT JOIN tartikelsichtbarkeit 
                        ON tartikelsichtbarkeit.kArtikel = tartikel.kArtikel
                        AND tartikelsichtbarkeit.kKundengruppe = :cgid
                    WHERE tartikelsichtbarkeit.kArtikel IS NULL',
                ['cgid' => (int)$oKundengruppe->kKundengruppe],
                ReturnType::SINGLE_OBJECT
            );
            $product                = new stdClass();
            $product->nAnzahl       = (int)$productData->cnt;
            $product->kKundengruppe = (int)$oKundengruppe->kKundengruppe;
            $product->cName         = $oKundengruppe->cName;

            $products[] = $product;
        }

        return $products;
    }

    /**
     * @return int
     */
    private function getNewCustomersCount(): int
    {
        return (int)$this->db->queryPrepared(
            'SELECT COUNT(*) AS cnt
                FROM tkunde
                WHERE dErstellt >= :from
                    AND dErstellt < :to
                    AND nRegistriert = 1',
            [
                'from' => $this->dateStart,
                'to'   => $this->dateEnd
            ],
            ReturnType::SINGLE_OBJECT
        )->cnt;
    }

    /**
     * @return int
     */
    private function getNewCustomerSalesCount(): int
    {
        return (int)$this->db->queryPrepared(
            'SELECT COUNT(DISTINCT(tkunde.kKunde)) AS cnt
                FROM tkunde
                JOIN tbestellung 
                    ON tbestellung.kKunde = tkunde.kKunde
                WHERE tbestellung.dErstellt >= :from
                    AND tbestellung.dErstellt < :to
                    AND tkunde.dErstellt >= :from
                    AND tkunde.dErstellt < :to
                    AND tkunde.nRegistriert = 1',
            [
                'from' => $this->dateStart,
                'to'   => $this->dateEnd
            ],
            ReturnType::SINGLE_OBJECT
        )->cnt;
    }

    /**
     * Holt die Anzahl an Bestellungen für einen bestimmten Zeitraum
     *
     * @return int
     */
    private function getOrderCount(): int
    {
        return (int)$this->db->queryPrepared(
            'SELECT COUNT(*) AS cnt
                FROM tbestellung
                WHERE dErstellt >= :from
                    AND dErstellt < :to',
            [
                'from' => $this->dateStart,
                'to'   => $this->dateEnd
            ],
            ReturnType::SINGLE_OBJECT
        )->cnt;
    }

    /**
     * Holt die Anzahl an Bestellungen für einen bestimmten Zeitraum von Neukunden
     *
     * @return int
     */
    private function getOrderCountForNewCustomers(): int
    {
        return (int)$this->db->queryPrepared(
            'SELECT COUNT(*) AS cnt
                FROM tbestellung
                JOIN tkunde 
                    ON tkunde.kKunde = tbestellung.kKunde
                WHERE tbestellung.dErstellt >= :from
                    AND tbestellung.dErstellt < :to
                    AND tkunde.nRegistriert = 1',
            [
                'from' => $this->dateStart,
                'to'   => $this->dateEnd
            ],
            ReturnType::SINGLE_OBJECT
        )->cnt;
    }

    /**
     * Anzahl Zahlungseingänge zu Bestellungen
     *
     * @return int
     */
    private function getIncomingPaymentsCount(): int
    {
        return (int)$this->db->queryPrepared(
            'SELECT COUNT(*) AS cnt
                FROM tbestellung
                WHERE tbestellung.dErstellt >= :from
                    AND tbestellung.dErstellt < :to
                    AND tbestellung.dBezahltDatum IS NOT NULL',
            [
                'from' => $this->dateStart,
                'to'   => $this->dateEnd
            ],
            ReturnType::SINGLE_OBJECT
        )->cnt;
    }

    /**
     * Anzahl versendeter Bestellungen
     *
     * @return int
     */
    private function getShippedOrdersCount(): int
    {
        return (int)$this->db->queryPrepared(
            'SELECT COUNT(*) AS cnt
                FROM tbestellung
                WHERE tbestellung.dErstellt >= :from
                    AND tbestellung.dErstellt < :to
                    AND tbestellung.dVersandDatum IS NOT NULL',
            [
                'from' => $this->dateStart,
                'to'   => $this->dateEnd
            ],
            ReturnType::SINGLE_OBJECT
        )->cnt;
    }

    /**
     * Holt die Anzahl von Besucher für einen bestimmten Zeitraum
     *
     * @return int
     */
    private function getVisitorCount(): int
    {
        return (int)$this->db->queryPrepared(
            'SELECT COUNT(*) AS cnt
                FROM tbesucherarchiv
                WHERE dZeit >= :from
                    AND dZeit < :to 
                    AND kBesucherBot = 0',
            [
                'from' => $this->dateStart,
                'to'   => $this->dateEnd
            ],
            ReturnType::SINGLE_OBJECT
        )->cnt;
    }

    /**
     * Holt die Anzahl von Besucher für einen bestimmten Zeitraum die von Suchmaschinen kamen
     *
     * @return int
     */
    private function getBotVisitCount(): int
    {
        return (int)$this->db->queryPrepared(
            "SELECT COUNT(*) AS cnt
                FROM tbesucherarchiv
                WHERE dZeit >= :from
                    AND dZeit < :to
                    AND cReferer != ''",
            [
                'from' => $this->dateStart,
                'to'   => $this->dateEnd
            ],
            ReturnType::SINGLE_OBJECT
        )->cnt;
    }

    /**
     * Holt die Anzahl von Bewertungen für einen bestimmten Zeitraum
     *
     * @return int
     */
    private function getRatingsCount(): int
    {
        return (int)$this->db->queryPrepared(
            'SELECT COUNT(*) AS cnt
                FROM tbewertung
                WHERE dDatum >= :from
                    AND dDatum < :to
                    AND nAktiv = 1',
            [
                'from' => $this->dateStart,
                'to'   => $this->dateEnd
            ],
            ReturnType::SINGLE_OBJECT
        )->cnt;
    }

    /**
     * Holt die Anzahl von Bewertungen für einen bestimmten Zeitraum die nicht freigeschaltet wurden
     *
     * @return int
     */
    private function getNonApprovedRatingsCount(): int
    {
        return (int)$this->db->queryPrepared(
            'SELECT COUNT(*) AS cnt
                FROM tbewertung
                WHERE dDatum >= :from
                    AND dDatum < :to
                    AND nAktiv = 0',
            [
                'from' => $this->dateStart,
                'to'   => $this->dateEnd
            ],
            ReturnType::SINGLE_OBJECT
        )->cnt;
    }

    /**
     * Holt die Anzahl von gezahlten Guthaben für einen bestimmten Zeitraum
     *
     * @return stdClass
     */
    private function getRatingCreditsCount(): stdClass
    {
        $rating = $this->db->queryPrepared(
            'SELECT COUNT(*) AS cnt, SUM(fGuthabenBonus) AS fSummeGuthaben
                FROM tbewertungguthabenbonus
                WHERE dDatum >= :from
                    AND dDatum < :to',
            [
                'from' => $this->dateStart,
                'to'   => $this->dateEnd
            ],
            ReturnType::SINGLE_OBJECT
        );

        $res                 = new stdClass();
        $res->nAnzahl        = (int)$rating->cnt;
        $res->fSummeGuthaben = $rating->fSummeGuthaben;

        return $res;
    }

    /**
     * Holt die Anzahl Kunden die geworben wurden für einen bestimmten Zeitraum
     *
     * @return int
     */
    private function getNewCustomerPromotionsCount(): int
    {
        return (int)$this->db->queryPrepared(
            'SELECT COUNT(*) AS cnt
                FROM tkundenwerbenkunden
                WHERE dErstellt >= :from
                    AND dErstellt < :to',
            [
                'from' => $this->dateStart,
                'to'   => $this->dateEnd
            ],
            ReturnType::SINGLE_OBJECT
        )->cnt;
    }

    /**
     * Holt die Anzahl Kunden die erfolgreich geworben wurden für einen bestimmten Zeitraum
     *
     * @return int
     */
    private function getSuccessfulNewCustomerPromotionsCount(): int
    {
        return (int)$this->db->queryPrepared(
            'SELECT COUNT(*) AS cnt
                FROM tkundenwerbenkunden
                WHERE dErstellt >= :from
                    AND dErstellt < :to
                    AND nRegistriert = 1
                    AND nGuthabenVergeben = 1',
            [
                'from' => $this->dateStart,
                'to'   => $this->dateEnd
            ],
            ReturnType::SINGLE_OBJECT
        )->cnt;
    }

    /**
     * Holt die Anzahl von versendeten Wunschlisten für einen bestimmten Zeitraum
     *
     * @return int
     */
    private function getSentWishlistCount(): int
    {
        return (int)$this->db->queryPrepared(
            'SELECT COUNT(*) AS cnt
                    FROM twunschlisteversand
                    WHERE dZeit >= :from
                        AND dZeit < :to',
            [
                'from' => $this->dateStart,
                'to'   => $this->dateEnd
            ],
            ReturnType::SINGLE_OBJECT
        )->cnt;
    }

    /**
     * Holt die Anzahl durchgeführter Umfragen für einen bestimmten Zeitraum
     *
     * @return int
     */
    private function getSurveyParticipationsCount(): int
    {
        return (int)$this->db->queryPrepared(
            'SELECT COUNT(*) AS cnt
                FROM tumfragedurchfuehrung
                WHERE dDurchgefuehrt >= :from
                    AND dDurchgefuehrt < :to',
            [
                'from' => $this->dateStart,
                'to'   => $this->dateEnd
            ],
            ReturnType::SINGLE_OBJECT
        )->cnt;
    }

    /**
     * Holt die Anzahl an Newskommentare für einen bestimmten Zeitraum
     *
     * @return int
     */
    private function getNewsCommentsCount(): int
    {
        return (int)$this->db->queryPrepared(
            'SELECT COUNT(*) AS cnt
                FROM tnewskommentar
                WHERE dErstellt >= :from
                    AND dErstellt < :to
                    AND nAktiv = 1',
            [
                'from' => $this->dateStart,
                'to'   => $this->dateEnd
            ],
            ReturnType::SINGLE_OBJECT
        )->cnt;
    }

    /**
     * Holt die Anzahl an Newskommentare nicht freigeschaltet für einen bestimmten Zeitraum
     *
     * @return int
     */
    private function getNonApprovedCommentsCount(): int
    {
        return (int)$this->db->queryPrepared(
            'SELECT COUNT(*) AS cnt
                FROM tnewskommentar
                WHERE dErstellt >= :from
                    AND dErstellt < :to
                    AND nAktiv = 0',
            [
                'from' => $this->dateStart,
                'to'   => $this->dateEnd
            ],
            ReturnType::SINGLE_OBJECT
        )->cnt;
    }

    /**
     * Holt die Anzahl an Produktanfragen zur Verfügbarkeit für einen bestimmten Zeitraum
     *
     * @return int
     */
    private function getAvailabilityNotificationsCount(): int
    {
        return (int)$this->db->queryPrepared(
            'SELECT COUNT(*) AS cnt
                FROM tverfuegbarkeitsbenachrichtigung
                WHERE dErstellt >= :from
                    AND dErstellt < :to',
            [
                'from' => $this->dateStart,
                'to'   => $this->dateEnd
            ],
            ReturnType::SINGLE_OBJECT
        )->cnt;
    }

    /**
     * Holt die Anzahl an Produktanfragen zum Artikel für einen bestimmten Zeitraum
     *
     * @return int
     */
    private function getProductInquriesCount(): int
    {
        return (int)$this->db->queryPrepared(
            'SELECT COUNT(*) AS cnt
                FROM tproduktanfragehistory
                WHERE dErstellt >= :from
                    AND dErstellt < :to',
            [
                'from' => $this->dateStart,
                'to'   => $this->dateEnd
            ],
            ReturnType::SINGLE_OBJECT
        )->cnt;
    }

    /**
     * Holt die Anzahl von Vergleichen für einen bestimmten Zeitraum
     *
     * @return int
     */
    private function getComparisonsCount(): int
    {
        return (int)$this->db->queryPrepared(
            'SELECT COUNT(*) AS cnt
                FROM tvergleichsliste
                WHERE dDate >= :from
                    AND dDate < :to',
            [
                'from' => $this->dateStart,
                'to'   => $this->dateEnd
            ],
            ReturnType::SINGLE_OBJECT
        )->cnt;
    }

    /**
     * Holt die Anzahl von genutzten Kupons für einen bestimmten Zeitraum
     *
     * @return int
     */
    private function getCouponUsageCount(): int
    {
        return (int)$this->db->queryPrepared(
            'SELECT COUNT(*) AS cnt
                FROM tkuponkunde
                WHERE dErstellt >= :from
                    AND dErstellt < :to',
            [
                'from' => $this->dateStart,
                'to'   => $this->dateEnd
            ],
            ReturnType::SINGLE_OBJECT
        )->cnt;
    }

    /**
     * @param array $logLevels
     * @return array|int|object
     */
    public function getLogEntries(array $logLevels): array
    {
        return map(
            $this->db->queryPrepared(
                'SELECT *
                    FROM tjtllog
                    WHERE dErstellt >= :from
                        AND dErstellt < :to
                        AND nLevel IN (' . \implode(',', \array_map('\intval', $logLevels)) . ')
                    ORDER BY dErstellt DESC',
                [
                    'from' => $this->dateStart,
                    'to'   => $this->dateEnd
                ],
                ReturnType::ARRAY_OF_OBJECTS
            ),
            function ($e) {
                $e->kLog   = (int)$e->kLog;
                $e->nLevel = (int)$e->nLevel;
                $e->kKey   = (int)$e->kKey;

                return $e;
            }
        );
    }

    /**
     * @param object $statusMail
     * @param string $dateStart
     * @param string $dateEnd
     * @return object|bool
     * @throws SmartyException
     */
    public function generate($statusMail, $dateStart, $dateEnd)
    {
        $this->dateStart = $dateStart;
        $this->dateEnd   = $dateEnd;
        if (!\is_array($statusMail->nInhalt_arr)
            || empty($dateStart)
            || empty($dateEnd)
            || \count($statusMail->nInhalt_arr) === 0
        ) {
            return false;
        }

        $mailType = $this->db->select(
            'temailvorlage',
            'cModulId',
            \MAILTEMPLATE_STATUSEMAIL,
            null,
            null,
            null,
            null,
            false,
            'cMailTyp'
        )->cMailTyp;

        $mail                                           = new stdClass();
        $mail->mail                                     = new stdClass();
        $mail->oAnzahlArtikelProKundengruppe            = -1;
        $mail->nAnzahlNeukunden                         = -1;
        $mail->nAnzahlNeukundenGekauft                  = -1;
        $mail->nAnzahlBestellungen                      = -1;
        $mail->nAnzahlBestellungenNeukunden             = -1;
        $mail->nAnzahlBesucher                          = -1;
        $mail->nAnzahlBesucherSuchmaschine              = -1;
        $mail->nAnzahlBewertungen                       = -1;
        $mail->nAnzahlBewertungenNichtFreigeschaltet    = -1;
        $mail->oAnzahlGezahltesGuthaben                 = -1;
        $mail->nAnzahlTags                              = -1;
        $mail->nAnzahlTagsNichtFreigeschaltet           = -1;
        $mail->nAnzahlGeworbenerKunden                  = -1;
        $mail->nAnzahlErfolgreichGeworbenerKunden       = -1;
        $mail->nAnzahlVersendeterWunschlisten           = -1;
        $mail->nAnzahlDurchgefuehrteUmfragen            = -1;
        $mail->nAnzahlNewskommentare                    = -1;
        $mail->nAnzahlNewskommentareNichtFreigeschaltet = -1;
        $mail->nAnzahlProduktanfrageArtikel             = -1;
        $mail->nAnzahlProduktanfrageVerfuegbarkeit      = -1;
        $mail->nAnzahlVergleiche                        = -1;
        $mail->nAnzahlGenutzteKupons                    = -1;
        $mail->nAnzahlZahlungseingaengeVonBestellungen  = -1;
        $mail->nAnzahlVersendeterBestellungen           = -1;
        $mail->dVon                                     = $dateStart;
        $mail->dBis                                     = $dateEnd;
        $mail->oLogEntry_arr                            = [];
        $logLevels                                      = [];

        foreach ($statusMail->nInhalt_arr as $nInhalt) {
            switch ($nInhalt) {
                case 1:
                    $mail->oAnzahlArtikelProKundengruppe = $this->getProductCountPerCustomerGroup();
                    break;
                case 2:
                    $mail->nAnzahlNeukunden = $this->getNewCustomersCount();
                    break;
                case 3:
                    $mail->nAnzahlNeukundenGekauft = $this->getNewCustomerSalesCount();
                    break;
                case 4:
                    $mail->nAnzahlBestellungen = $this->getOrderCount();
                    break;
                case 5:
                    $mail->nAnzahlBestellungenNeukunden = $this->getOrderCountForNewCustomers();
                    break;
                case 6:
                    $mail->nAnzahlBesucher = $this->getVisitorCount();
                    break;
                case 7:
                    $mail->nAnzahlBesucherSuchmaschine = $this->getBotVisitCount();
                    break;
                case 8:
                    $mail->nAnzahlBewertungen = $this->getRatingsCount();
                    break;
                case 9:
                    $mail->nAnzahlBewertungenNichtFreigeschaltet = $this->getNonApprovedRatingsCount();
                    break;
                case 10:
                    $mail->oAnzahlGezahltesGuthaben = $this->getRatingCreditsCount();
                    break;
                case 13:
                    $mail->nAnzahlGeworbenerKunden = $this->getNewCustomerPromotionsCount();
                    break;
                case 14:
                    $mail->nAnzahlErfolgreichGeworbenerKunden = $this->getSuccessfulNewCustomerPromotionsCount();
                    break;
                case 15:
                    $mail->nAnzahlVersendeterWunschlisten = $this->getSentWishlistCount();
                    break;
                case 16:
                    $mail->nAnzahlDurchgefuehrteUmfragen = $this->getSurveyParticipationsCount();
                    break;
                case 17:
                    $mail->nAnzahlNewskommentare = $this->getNewsCommentsCount();
                    break;
                case 18:
                    $mail->nAnzahlNewskommentareNichtFreigeschaltet = $this->getNonApprovedCommentsCount();
                    break;
                case 19:
                    $mail->nAnzahlProduktanfrageArtikel = $this->getProductInquriesCount();
                    break;
                case 20:
                    $mail->nAnzahlProduktanfrageVerfuegbarkeit = $this->getAvailabilityNotificationsCount();
                    break;
                case 21:
                    $mail->nAnzahlVergleiche = $this->getComparisonsCount();
                    break;
                case 22:
                    $mail->nAnzahlGenutzteKupons = $this->getCouponUsageCount();
                    break;
                case 23:
                    $mail->nAnzahlZahlungseingaengeVonBestellungen = $this->getIncomingPaymentsCount();
                    break;
                case 24:
                    $mail->nAnzahlVersendeterBestellungen = $this->getShippedOrdersCount();
                    break;
                case 25:
                    $logLevels[] = \JTLLOG_LEVEL_ERROR;
                    $logLevels[] = \JTLLOG_LEVEL_CRITICAL;
                    $logLevels[] = \JTLLOG_LEVEL_ALERT;
                    $logLevels[] = \JTLLOG_LEVEL_EMERGENCY;
                    break;
                case 26:
                    $logLevels[] = \JTLLOG_LEVEL_NOTICE;
                    break;
                case 27:
                    $logLevels[] = \JTLLOG_LEVEL_DEBUG;
                    break;
            }
        }

        if (\count($logLevels) > 0) {
            $mail->oLogEntry_arr = $this->getLogEntries($logLevels);
            $logfile             = \tempnam(\sys_get_temp_dir(), 'jtl');
            $info                = \pathinfo($logfile);
            $fileStream          = \fopen($logfile, 'wb');
            $attachment          = new Attachment();
            $attachment->setFileName($info['filename']);
            $attachment->setDir($info['dirname'] . '/');
            $smarty = Shop::Smarty()->assign('oMailObjekt', $mail);
            if ($mailType === 'text') {
                \fwrite(
                    $fileStream,
                    $smarty->fetch(\PFAD_ROOT . \PFAD_EMAILVORLAGEN . 'ger/email_bericht_plain_log.tpl')
                );
                $attachment->setName('jtl-log-digest.txt');
            } else {
                \fwrite(
                    $fileStream,
                    $smarty->fetch(\PFAD_ROOT . \PFAD_EMAILVORLAGEN . 'ger/email_bericht_html_log.tpl')
                );
                $attachment->setName('jtl-log-digest.html');
            }

            \fclose($fileStream);
            $mail->mail->attachment = $attachment;
        }

        $mail->mail->toEmail = $statusMail->cEmail;

        return $mail;
    }

    /**
     * @return bool
     * @throws SmartyException
     */
    public function sendAllActiveStatusMails(): bool
    {
        $ok          = true;
        $statusMails = $this->db->selectAll('tstatusemail', 'nAktiv', 1);
        foreach ($statusMails as $statusMail) {
            $ok = $ok && $this->send($statusMail);
        }

        return $ok;
    }

    /**
     * @param stdClass|null $statusMail
     * @return bool
     * @throws SmartyException
     */
    public function send($statusMail = null): bool
    {
        $sent = false;
        if ($statusMail === null) {
            $statusMail = $this->db->select('tstatusemail', 'nAktiv', 1);
        }
        $statusMail->nInhalt_arr = Text::parseSSK($statusMail->cInhalt);
        $nIntervall              = (int)$statusMail->nInterval;
        switch ($nIntervall) {
            case 1:
                $interval    = 'day';
                $intervalLoc = 'Tägliche';
                break;
            case 7:
                $interval    = 'week';
                $intervalLoc = 'Wöchentliche';
                break;
            case 30:
                $interval    = 'month';
                $intervalLoc = 'Monatliche';
                break;
            default:
                throw new InvalidArgumentException('Invalid interval type: ' . $nIntervall);
                break;
        }

        $data = $this->generate(
            $statusMail,
            \date_create()->modify('-1 ' . $interval)->format('Y-m-d H:i:s'),
            \date_create()->format('Y-m-d H:i:s')
        );
        if ($data) {
            $data->cIntervall = $intervalLoc . ' Status-Email';

            $mailer = Shop::Container()->get(Mailer::class);
            $mail   = new Mail();
            $mail   = $mail->createFromTemplateID(\MAILTEMPLATE_STATUSEMAIL, $data);
            $mail->setToMail($statusMail->cEmail);
            if (!empty($data->mail->attachment)) {
                $mail->setAttachments([$data->mail->attachment]);
            }
            $sent = $mailer->send($mail);

            foreach ($mail->getAttachments() as $attachment) {
                \unlink($attachment->getFullPath());
            }
        }

        return $sent;
    }
}
