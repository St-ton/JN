<?php declare(strict_types=1);

namespace JTL\Recommendation;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use JTL\Exceptions\CircularReferenceException;
use JTL\Exceptions\ServiceNotFoundException;
use JTL\Services\JTL\AlertServiceInterface;
use JTL\Shop;

/**
 * Class Manager
 * @package JTL\Recommendation
 */
class Manager
{
    public const SCOPE_WIZARD_PAYMENT_PROVIDER = 'wizard.payment-provider';

    public const SCOPE_WIZARD_LEGAL_TEXTS = 'wizard.legal-texts';

    public const SCOPE_BACKEND_PAYMENT_PROVIDER = 'backend.payment-provider';

    public const SCOPE_BACKEND_LEGAL_TEXTS = 'backend.legal-texts';

    private const API_DEV_URL = 'https://checkout-stage.jtl-software.com/v1/recommendations';

    private const API_LIVE_URL = 'https://checkout.jtl-software.com/v1/recommendations';

    /**
     * @var Client
     */
    private Client $client;

    /**
     * @var Collection
     */
    private Collection $recommendations;

    /**
     * Manager constructor.
     * @param AlertServiceInterface $alertService
     * @param string                $scope
     */
    public function __construct(private AlertServiceInterface $alertService, private string $scope)
    {
        $this->client          = new Client();
        $this->recommendations = new Collection();
        $this->setRecommendations();
    }

    /**
     *
     */
    public function setRecommendations(): void
    {
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
     * @param bool   $showAlert
     * @return Recommendation|null
     */
    public function getRecommendationById(string $id, bool $showAlert = true): ?Recommendation
    {
        $recommendation = $this->recommendations->first(static function (Recommendation $e) use ($id): bool {
            return $e->getId() === $id;
        });

        if ($recommendation === null && $showAlert) {
            $this->alertService->addWarning(\__('noRecommendationFound'), 'noRecommendationFound');
        }

        return $recommendation;
    }

    /**
     * @param string $scope
     * @return array
     * @throws GuzzleException
     * @throws CircularReferenceException
     * @throws ServiceNotFoundException
     */
    private function getJSONFromAPI(string $scope): array
    {
        $url = (\EXS_LIVE === true ? self::API_LIVE_URL : self::API_DEV_URL) . '?scope=' . $scope;
        try {
            $res = $this->client->request(
                'GET',
                $url,
                [
                    'headers' => [
                        'Accept'       => 'application/json',
                        'Content-Type' => 'application/json',
                    ],
                    'verify'  => true
                ]
            );
        } catch (Exception $e) {
            Shop::Container()->getLogService()->error($e->getMessage());
        }

        return empty($res) ? [] : \json_decode((string)$res->getBody(), false, 512, \JSON_THROW_ON_ERROR)->extensions;
    }

    /**
     * @return string
     */
    public function getScope(): string
    {
        return $this->scope;
    }
}
