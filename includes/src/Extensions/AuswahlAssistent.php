<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Extensions;

/**
 * Class AuswahlAssistent
 *
 * @package Extensions
 */
class AuswahlAssistent
{
    /**
     * @var int
     */
    private $kAuswahlAssistentOrt = 0;

    /**
     * @var int
     */
    private $kAuswahlAssistentGruppe = 0;

    /**
     * @var string
     */
    private $cKey = '';

    /**
     * @var int
     */
    private $kKey = 0;

    /**
     * @var int
     */
    private $kSprache = 0;

    /**
     * @var string
     */
    private $cName = '';

    /**
     * @var string
     */
    private $cBeschreibung = '';

    /**
     * @var int
     */
    private $nAktiv = 0;

    /**
     * @var AuswahlAssistentFrage[]
     */
    private $questions = [];

    /**
     * @var AuswahlAssistentFrage[] - keys are kMerkmal
     */
    private $questionsAssoc = [];

    /**
     * @var int
     */
    private $nCurQuestion = 0;

    /**
     * @var array
     */
    private $selections = [];

    /**
     * @var \Filter\ProductFilter
     */
    private $productFilter;

    /**
     * @var array
     */
    private $config;

    /**
     * AuswahlAssistent constructor.
     *
     * @param string $cKey
     * @param int    $kKey
     * @param int    $kSprache
     * @param bool   $bOnlyActive
     */
    public function __construct($cKey, int $kKey, int $kSprache = 0, bool $bOnlyActive = true)
    {
        $this->config = \Shop::getSettings(\CONF_AUSWAHLASSISTENT)['auswahlassistent'];

        if ($kSprache === 0) {
            $kSprache = \Shop::getLanguageID();
        }

        if ($kKey > 0
            && $kSprache > 0
            && !empty($cKey)
            && \Nice::getInstance()->checkErweiterung(\SHOP_ERWEITERUNG_AUSWAHLASSISTENT)
        ) {
            $this->loadFromDB($cKey, $kKey, $kSprache, $bOnlyActive);
        }
    }

    /**
     * @param string $cKey
     * @param int    $kKey
     * @param int    $kSprache
     * @param bool   $bOnlyActive
     */
    private function loadFromDB($cKey, int $kKey, int $kSprache, bool $bOnlyActive = true): void
    {
        $item = \Shop::Container()->getDB()->queryPrepared(
            'SELECT *
                FROM tauswahlassistentort AS ao
                    JOIN tauswahlassistentgruppe AS ag
                        ON ao.kAuswahlAssistentGruppe = ag.kAuswahlAssistentGruppe
                            AND ao.cKey = :ckey
                            AND ao.kKey = :kkey
                            AND ag.kSprache = :ksprache' .
            ($bOnlyActive ? ' AND ag.nAktiv = 1' : ''),
            [
                'ckey'     => $cKey,
                'kkey'     => $kKey,
                'ksprache' => $kSprache
            ],
            \DB\ReturnType::SINGLE_OBJECT
        );

        if ($item !== null && $item !== false) {
            foreach (\get_object_vars($item) as $name => $value) {
                $this->$name = $value;
            }

            $this->kAuswahlAssistentOrt    = (int)$this->kAuswahlAssistentOrt;
            $this->kAuswahlAssistentGruppe = (int)$this->kAuswahlAssistentGruppe;
            $this->kKey                    = (int)$this->kKey;
            $this->kSprache                = (int)$this->kSprache;
            $this->nAktiv                  = (int)$this->nAktiv;

            $questionIDs = \Shop::Container()->getDB()->queryPrepared(
                'SELECT kAuswahlAssistentFrage AS id
                    FROM tauswahlassistentfrage
                    WHERE kAuswahlAssistentGruppe = :groupID' .
                ($bOnlyActive ? ' AND nAktiv = 1 ' : ' ') .
                'ORDER BY nSort',
                ['groupID' => $this->kAuswahlAssistentGruppe],
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );

            $this->questions = [];

            foreach ($questionIDs as $questionID) {
                $question                                  = new AuswahlAssistentFrage((int)$questionID->id);
                $this->questions[]                         = $question;
                $this->questionsAssoc[$question->kMerkmal] = $question;
            }
        }
    }

    /**
     * @param int $kWert
     * @return $this
     */
    public function setNextSelection(int $kWert): self
    {
        if ($this->nCurQuestion < \count($this->questions)) {
            $this->selections[] = $kWert;
            ++$this->nCurQuestion;
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function filter(): self
    {
        $params = [];
        if ($this->cKey === AUSWAHLASSISTENT_ORT_KATEGORIE) {
            $params['kKategorie'] = $this->kKey;

            if (\count($this->selections) > 0) {
                $params['MerkmalFilter_arr'] = $this->selections;
            }
        } elseif (\count($this->selections) > 0) {
            $params['kMerkmalWert'] = $this->selections[0];
            if (\count($this->selections) > 1) {
                $params['MerkmalFilter_arr'] = \array_slice($this->selections, 1);
            }
        }
        $productFilter     = \Shop::buildProductFilter($params);
        $AktuelleKategorie = isset($params['kKategorie'])
            ? new \Kategorie($params['kKategorie'])
            : null;
        $attributeFilters  = (new \Filter\SearchResults())->setFilterOptions(
            $productFilter,
            $AktuelleKategorie,
            true
        )->getAttributeFilterOptions();

        foreach ($attributeFilters as $attributeFilter) {
            /** @var \Filter\Items\Attribute $attributeFilter */
            if (\array_key_exists($attributeFilter->getValue(), $this->questionsAssoc)) {
                $oFrage                    = $this->questionsAssoc[$attributeFilter->getValue()];
                $oFrage->oWert_arr         = $attributeFilter->getOptions();
                $oFrage->nTotalResultCount = 0;
                foreach ($attributeFilter->getOptions() as $oWert) {
                    $oFrage->nTotalResultCount                           += $oWert->getCount();
                    $oFrage->oWert_assoc[$oWert->getData('kMerkmalWert')] = $oWert;
                }
            }
        }
        $this->productFilter = $productFilter;

        return $this;
    }

    /**
     * Return the HTML for this selection wizard in its current state
     *
     * @param \Smarty\JTLSmarty $smarty
     * @return string
     */
    public function fetchForm($smarty): string
    {
        return $smarty->assign('AWA', $this)->fetch('selectionwizard/form.tpl');
    }

    /**
     * @return int
     */
    public function getLocationId(): int
    {
        return $this->kAuswahlAssistentOrt;
    }

    /**
     * @return int
     */
    public function getGroupId(): int
    {
        return $this->kAuswahlAssistentGruppe;
    }

    /**
     * @return string
     */
    public function getLocationKeyName(): string
    {
        return $this->cKey;
    }

    /**
     * @return int
     */
    public function getLocationKeyId(): int
    {
        return $this->kKey;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->cName;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return \preg_replace('/\s+/', ' ', \trim($this->cBeschreibung));
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->nAktiv === 1;
    }

    /**
     * @param int $nFrage
     * @return AuswahlAssistentFrage|null
     */
    public function getQuestion(int $nFrage)
    {
        return $this->questions[$nFrage] ?? null;
    }

    /**
     * @return array
     */
    public function getQuestions(): array
    {
        return $this->questions;
    }

    /**
     * @return int
     */
    public function getQuestionCount(): int
    {
        return \count($this->questions);
    }

    /**
     * @return int
     */
    public function getCurQuestion(): int
    {
        return $this->nCurQuestion;
    }

    /**
     * @return array
     */
    public function getSelections(): array
    {
        return $this->selections;
    }

    /**
     * @param int $nFrage
     * @return array|null
     */
    public function getSelectedValue(int $nFrage)
    {
        $oFrage         = $this->questions[$nFrage];
        $kSelectedValue = $this->selections[$nFrage];

        return $oFrage->oWert_assoc[$kSelectedValue];
    }

    /**
     * @return \Filter\ProductFilter
     */
    public function getNaviFilter(): \Filter\ProductFilter
    {
        return $this->productFilter;
    }

    /**
     * @return \stdClass|null
     */
    public function getLastSelectedValue()
    {
        $oFrage         = \end($this->questions);
        $kSelectedValue = \end($this->selections);

        return $oFrage->oWert_assoc[$kSelectedValue] ?? null;
    }

    /**
     * @param string $cName
     * @return mixed
     */
    public function getConf($cName)
    {
        return $this->config[$cName];
    }

    /**
     * Tells whether the product wizard is enabled in the shop settings
     *
     * @return bool
     */
    public static function isRequired(): bool
    {
        return \Shop::getSettings([\CONF_AUSWAHLASSISTENT])['auswahlassistent']['auswahlassistent_nutzen'] === 'Y';
    }

    /**
     * @param string                     $cKey
     * @param int                        $kKey
     * @param int                        $kSprache
     * @param \Smarty\JTLSmarty           $smarty
     * @param array                      $selected
     * @param \Filter\ProductFilter|null $pf
     * @return self|null
     */
    public static function startIfRequired(
        $cKey,
        int $kKey,
        int $kSprache = 0,
        $smarty = null,
        $selected = [],
        $pf = null
    ): ?self {
        // only start if enabled in the backend settings
        if (!self::isRequired()) {
            return null;
        }
        $filterCount = $pf !== null ? $pf->getFilterCount() : 0;
        // only start if no filters are already set
        if ($filterCount === 0) {
            $AWA = new self($cKey, $kKey, $kSprache, true);
            // only start if the respective selection wizard group is enabled (active)
            if ($AWA->isActive()) {
                foreach ($selected as $kMerkmalWert) {
                    $AWA->setNextSelection($kMerkmalWert);
                }

                $AWA->filter();

                if ($smarty !== null) {
                    $smarty->assign('AWA', $AWA);
                }

                return $AWA;
            }
        }

        return null;
    }

    /**
     * @return array
     */
    public static function getLinks(): array
    {
        return \Shop::Container()->getDB()->selectAll('tlink', 'nLinkart', \LINKTYP_AUSWAHLASSISTENT);
    }
}
