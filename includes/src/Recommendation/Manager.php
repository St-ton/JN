<?php

namespace JTL\Recommendation;

use \Illuminate\Support\Collection;
use JTL\Alert\Alert;
use JTL\Services\JTL\AlertServiceInterface;
use JTL\Shop;

/**
 * Class Manager
 * @package JTL\Recommendation
 */
class Manager
{
    public const SCOPE_WIZARD_PAYMENT_PROVIDER  = 'wizard.payment-provider';
    public const SCOPE_WIZARD_LEGAL_TEXTS       = 'wizard.legal-texts';
    public const SCOPE_BACKEND_PAYMENT_PROVIDER = 'backend.payment-provider';
    public const SCOPE_BACKEND_LEGAL_TEXTS      = 'backend.legal-texts';

    /**
     * @var Collection
     */
    private $recommendations;

    /**
     * @var string
     */
    private $scope;

    /**
     * @var AlertServiceInterface
     */
    private $alertService;

    /**
     * Manager constructor.
     * @param AlertServiceInterface $alertService
     * @param string $scope
     */
    public function __construct(AlertServiceInterface $alertService, string $scope)
    {
        $this->alertService = $alertService;
        $this->scope        = $scope;
        $this->setRecommendations();
    }

    /**
     *
     */
    public function setRecommendations(): void
    {
        $this->recommendations = new Collection();

//        //TODO: Cache?
        foreach ($this->getJSONFromAPI($this->getScope()) as $recommendation) {
            $this->recommendations->push(new Recommendation($recommendation));
        }
    }

    /**
     * @return Collection
     */
    public function getRecommendations(): Collection
    {
        return $this->recommendations;
    }

    /**
     * @param string $id
     * @param bool $showAlert
     * @return Recommendation|null
     */
    public function getRecommendationById(string $id, bool $showAlert = true): ?Recommendation
    {
        $recommendation = $this->recommendations->filter(static function (Recommendation $e) use ($id) {
            return $e->getId() === $id;
        })->first();

        if ($recommendation === null && $showAlert) {
            $this->alertService->addAlert(Alert::TYPE_WARNING, __('noRecommendationFound'), 'noRecommendationFound');
        }

        return $recommendation;
    }

    /**
     * @param string $scope
     * @return mixed
     */
    private function getJSONFromAPI(string $scope)
    {
        return $this->getTestJSON();
//        return json_decode(file_get_contents(\JTLURL_RECOMMENDATIONS_SCOPE . $scope));
    }

    /**
     * @return mixed
     */
    private function getTestJSON()
    {
        return \json_decode(
            \file_get_contents(\PFAD_ROOT .'getRecommendation.json'),
            false
        )->extensions;
    }

    /**
     * @return string
     */
    public function getScope(): string
    {
        return $this->scope;
    }
}
