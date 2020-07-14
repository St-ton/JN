<?php

namespace JTL\Recommendation;

use \Illuminate\Support\Collection;
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
     * Manager constructor.
     * @param string $scope
     */
    public function __construct(string $scope)
    {
        $this->scope = $scope;
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
     * @return Recommendation
     */
    public function getRecommendationById(string $id): Recommendation
    {
        return $this->recommendations->filter(static function (Recommendation $e) use ($id) {
            return $e->getId() === $id;
        })->first();
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
            \file_get_contents(\PFAD_ROOT .'includes/src/Recommendation/getRecommendation.json'),
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
