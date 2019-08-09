<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Survey;

use DateTime;
use Illuminate\Support\Collection;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Helpers\Text;
use JTL\MagicCompatibilityTrait;
use JTL\Nice;
use stdClass;
use function Functional\group;

/**
 * Class Survey
 * @package JTL\Survey
 */
class Survey
{
    use MagicCompatibilityTrait;

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
     * @var DateTime
     */
    private $validFrom;

    /**
     * @var DateTime
     */
    private $validUntil;

    /**
     * @var DateTime
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
     * @var Nice
     */
    private $nice;

    /**
     * @var SurveyQuestionFactory
     */
    private $factory;

    /**
     * @var array
     */
    protected static $mapping = [
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
     * @param Nice                  $nice
     * @param SurveyQuestionFactory $factory
     */
    public function __construct(DbInterface $db, Nice $nice, SurveyQuestionFactory $factory)
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
            "SELECT tumfrage.kUmfrage, tumfrage.kSprache, tumfrage.kKupon, tumfrage.cKundengruppe,
                tumfrage.cName, tumfrage.cBeschreibung, tumfrage.fGuthaben, tumfrage.nBonuspunkte, 
                tumfrage.nAktiv, tumfrage.dGueltigVon, tumfrage.dGueltigBis, tumfrage.dErstellt, 
                tseo.cSeo, COUNT(tumfragefrage.kUmfrageFrage) AS nAnzahlFragen
                FROM tumfrage
                JOIN tumfragefrage 
                    ON tumfragefrage.kUmfrage = tumfrage.kUmfrage
                LEFT JOIN tseo 
                    ON tseo.cKey = 'kUmfrage'
                    AND tseo.kKey = tumfrage.kUmfrage
                WHERE tumfrage.kUmfrage = :sid
                    AND ((dGueltigVon <= NOW() AND dGueltigBis >= NOW()) 
                        || (dGueltigVon <= NOW() AND dGueltigBis IS NULL))
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
            $questions = group($questions, function (stdClass $e) {
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
    public function setID($id): void
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
    public function setLanguageID($languageID): void
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
    public function setCouponID($couponID): void
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
    public function setCustomerGroups($customerGroups): void
    {
        if (!\is_array($customerGroups)) {
            $customerGroups = Text::parseSSK($customerGroups);
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
    public function setName(string $name): void
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
    public function setDescription(string $description): void
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
    public function setCredits($credits): void
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
    public function setBonusCredits($bonusCredits): void
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
    public function setIsActive($isActive): void
    {
        $this->isActive = (bool)$isActive;
    }

    /**
     * @return DateTime
     */
    public function getValidFrom(): DateTime
    {
        return $this->validFrom;
    }

    /**
     * @param DateTime|string $validFrom
     */
    public function setValidFrom($validFrom): void
    {
        if (\is_string($validFrom)) {
            $validFrom = new DateTime($validFrom);
        }
        $this->validFrom = $validFrom;
    }

    /**
     * @return DateTime
     */
    public function getValidUntil(): DateTime
    {
        return $this->validUntil;
    }

    /**
     * @param DateTime|string $validUntil
     */
    public function setValidUntil($validUntil): void
    {
        if (\is_string($validUntil)) {
            $validUntil = new DateTime($validUntil);
        }
        $this->validUntil = $validUntil;
    }

    /**
     * @return DateTime
     */
    public function getCreated(): DateTime
    {
        return $this->created;
    }

    /**
     * @param DateTime|string $created
     */
    public function setCreated($created): void
    {
        if (\is_string($created)) {
            $created = new DateTime($created);
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
    public function setURL(string $url): void
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
    public function setQuestionCount($count): void
    {
        $this->questionCount = (int)$count;
    }

    /**
     * @param int $id
     * @return SurveyQuestion|null
     */
    public function getQuestionByID(int $id): ?SurveyQuestion
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
    public function setQuestions(Collection $questions): void
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
    public function setDb(DbInterface $db): void
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
        $res['Nice']    = '*truncated*';
        $res['factory'] = '*truncated*';

        return $res;
    }
}
