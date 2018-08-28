<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Survey;


use DB\DbInterface;
use DB\ReturnType;
use function Functional\group;
use Tightenco\Collect\Support\Collection;

/**
 * Class Survey
 * @package JTL
 */
class Survey
{
    use \MagicCompatibilityTrait;

    /**
     * @var int
     */
    private $id = 0;

    /**
     * @var int
     */
    private $languageID = 0;

    /**
     * @var int
     */
    private $couponID = 0;

    /**
     * @var array
     */
    private $customerGroups = [];

    /**
     * @var string
     */
    private $name = '';

    /**
     * @var string
     */
    private $description = '';

    /**
     * @var float
     */
    private $credits = 0.0;

    /**
     * @var int
     */
    private $bonusCredits = 0;

    /**
     * @var bool
     */
    private $isActive = false;

    /**
     * @var \DateTime
     */
    private $validFrom;

    /**
     * @var \DateTime
     */
    private $validUntil;

    /**
     * @var \DateTime
     */
    private $created;

    /**
     * @var string
     */
    private $url = '';

    /**
     * @var Collection
     */
    private $questions;

    /**
     * @var int
     */
    private $questionCount = 0;

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var \Nice
     */
    private $nice;

    /**
     * @var SurveyQuestionFactory
     */
    private $factory;

    /**
     * @var array
     */
    private static $mapping = [
        'kUmfrage'          => 'ID',
        'kSprache'          => 'LanguageID',
        'kKupon'            => 'CouponID',
        'cKundengruppe'     => 'CustomerGroups',
        'cName'             => 'Name',
        'cSeo'              => 'URL',
        'cBeschreibung'     => 'Description',
        'fGuthaben'         => 'Credits',
        'nBonuspunkte'      => 'BonusCredits',
        'nAktiv'            => 'IsActive',
        'dGueltigVon'       => 'ValidFrom',
        'dGueltigBis'       => 'ValidUntil',
        'dGueltigVon_de'    => 'ValidFromFormatted',
        'dErstellt'         => 'Created',
        'nAnzahlFragen'     => 'QuestionCount',
        'oUmfrageFrage_arr' => 'Questions',
    ];

    /**
     * Survey constructor.
     * @param DbInterface           $db
     * @param \Nice                 $nice
     * @param SurveyQuestionFactory $factory
     */
    public function __construct(DbInterface $db, \Nice $nice, SurveyQuestionFactory $factory)
    {
        $this->db        = $db;
        $this->nice      = $nice;
        $this->factory   = $factory;
        $this->questions = new Collection();
    }

    /**
     * @param int $id
     * @return $this
     */
    public function load(int $id): self
    {
        if (!$id || !$this->nice->checkErweiterung(\SHOP_ERWEITERUNG_UMFRAGE)) {
            return $this;
        }
        $survey = $this->db->queryPrepared(
            "SELECT tumfrage.kUmfrage, tumfrage.kSprache, tumfrage.kKupon, tumfrage.cKundengruppe, tumfrage.cName, 
                tumfrage.cBeschreibung, tumfrage.fGuthaben, tumfrage.nBonuspunkte, tumfrage.nAktiv, tumfrage.dGueltigVon, 
                tumfrage.dGueltigBis, tumfrage.dErstellt, tseo.cSeo, COUNT(tumfragefrage.kUmfrageFrage) AS nAnzahlFragen
                FROM tumfrage
                JOIN tumfragefrage 
                    ON tumfragefrage.kUmfrage = tumfrage.kUmfrage
                LEFT JOIN tseo 
                    ON tseo.cKey = 'kUmfrage'
                    AND tseo.kKey = tumfrage.kUmfrage
                WHERE tumfrage.kUmfrage = :sid
                    AND ((dGueltigVon <= now() AND dGueltigBis >= now()) 
                        || (dGueltigVon <= now() AND (dGueltigBis IS NULL OR dGueltigBis = '0000-00-00 00:00:00')))
                GROUP BY tumfrage.kUmfrage
                ORDER BY tumfrage.dGueltigVon DESC",
            ['sid' => $id],
            ReturnType::SINGLE_OBJECT
        );
        if ($survey !== false) {
            foreach (\get_object_vars($survey) as $var => $value) {
                if (($mapping = self::getMapping($var)) !== null) {
                    $method = 'set' . $mapping;
                    $this->$method($value);
                }
            }
            $questions = $this->db->queryPrepared(
                'SELECT tumfragefrage.*, 
                    tumfragefrageantwort.kUmfrageFrageAntwort AS answerID, 
                    tumfragefrageantwort.cName AS answerName, 
                    tumfragefrageantwort.nSort AS answerSort,
                    tumfragematrixoption.kUmfrageMatrixOption AS matrixID, 
                    tumfragematrixoption.cName AS matrixName, 
                    tumfragematrixoption.nSort AS matrixSort
                    FROM tumfragefrage
                    LEFT JOIN tumfragefrageantwort
                        ON tumfragefrage.kUmfrageFrage = tumfragefrageantwort.kUmfrageFrage
                    LEFT JOIN tumfragematrixoption
                        ON tumfragefrage.kUmfrageFrage = tumfragematrixoption.kUmfrageFrage
                    WHERE tumfragefrage.kUmfrage = :sid
                    ORDER BY tumfragefrage.nSort',
                ['sid' => $this->getID()],
                ReturnType::ARRAY_OF_OBJECTS
            );
            $questions = group($questions, function (\stdClass $e) {
                return $e->kUmfrageFrage;
            });
            foreach ($questions as $questionID => $questionData) {
                $question = $this->factory->create();
                $this->questions->push($question->mapGroup($questionData));
            }
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getID(): int
    {
        return $this->id;
    }

    /**
     * @param int|string $id
     */
    public function setID($id)
    {
        $this->id = (int)$id;
    }

    /**
     * @return int
     */
    public function getLanguageID(): int
    {
        return $this->languageID;
    }

    /**
     * @param int|string $languageID
     */
    public function setLanguageID($languageID)
    {
        $this->languageID = (int)$languageID;
    }

    /**
     * @return int
     */
    public function getCouponID(): int
    {
        return $this->couponID;
    }

    /**
     * @param int|string $couponID
     */
    public function setCouponID($couponID)
    {
        $this->couponID = (int)$couponID;
    }

    /**
     * @return array
     */
    public function getCustomerGroups(): array
    {
        return $this->customerGroups;
    }

    /**
     * @param array|string $customerGroups
     */
    public function setCustomerGroups($customerGroups)
    {
        if (!\is_array($customerGroups)) {
            $customerGroups = \StringHandler::parseSSK($customerGroups);
        }
        $this->customerGroups = $customerGroups;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description)
    {
        $this->description = $description;
    }

    /**
     * @return float
     */
    public function getCredits(): float
    {
        return $this->credits;
    }

    /**
     * @param float|string $credits
     */
    public function setCredits($credits)
    {
        $this->credits = (float)$credits;
    }

    /**
     * @return int
     */
    public function getBonusCredits(): int
    {
        return $this->bonusCredits;
    }

    /**
     * @param int|string $bonusCredits
     */
    public function setBonusCredits($bonusCredits)
    {
        $this->bonusCredits = (int)$bonusCredits;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @param bool|string $isActive
     */
    public function setIsActive($isActive)
    {
        $this->isActive = (bool)$isActive;
    }

    /**
     * @return \DateTime
     */
    public function getValidFrom(): \DateTime
    {
        return $this->validFrom;
    }

    /**
     * @param \DateTime|string $validFrom
     */
    public function setValidFrom($validFrom)
    {
        if (\is_string($validFrom)) {
            $validFrom = new \DateTime($validFrom);
        }
        $this->validFrom = $validFrom;
    }

    /**
     * @return \DateTime
     */
    public function getValidUntil(): \DateTime
    {
        return $this->validUntil;
    }

    /**
     * @param \DateTime|string $validUntil
     */
    public function setValidUntil($validUntil)
    {
        if (\is_string($validUntil)) {
            $validUntil = new \DateTime($validUntil);
        }
        $this->validUntil = $validUntil;
    }

    /**
     * @return \DateTime
     */
    public function getCreated(): \DateTime
    {
        return $this->created;
    }

    /**
     * @param \DateTime|string $created
     */
    public function setCreated($created)
    {
        if (\is_string($created)) {
            $created = new \DateTime($created);
        }
        $this->created = $created;
    }

    /**
     * @return string
     */
    public function getURL(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setURL(string $url)
    {
        $this->url = $url;
    }

    /**
     * @return int
     */
    public function getQuestionCount(): int
    {
        return $this->questionCount;
    }

    /**
     * @param int|string $count
     */
    public function setQuestionCount($count)
    {
        $this->questionCount = (int)$count;
    }

    /**
     * @param int $id
     * @return SurveyQuestion|null
     */
    public function getQuestionByID(int $id)
    {
        return $this->questions->first(function (SurveyQuestion $q) use ($id) {
            return $q->getID() === $id;
        });
    }

    /**
     * @return Collection
     */
    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    /**
     * @param Collection $questions
     */
    public function setQuestions(Collection $questions)
    {
        $this->questions     = $questions;
        $this->questionCount = $questions->count();
    }

    /**
     * @return string
     */
    public function getValidFromFormatted(): string
    {
        return $this->validFrom !== null
            ? $this->validFrom->format('d.m.Y')
            : '';
    }

    /**
     * @return DbInterface
     */
    public function getDb(): DbInterface
    {
        return $this->db;
    }

    /**
     * @param DbInterface $db
     */
    public function setDb(DbInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @return array
     */
    public function __debugInfo(): array
    {
        $res            = \get_object_vars($this);
        $res['db']      = '*truncated*';
        $res['nice']    = '*truncated*';
        $res['factory'] = '*truncated*';

        return $res;
    }
}
