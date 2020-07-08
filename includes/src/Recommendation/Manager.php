<?php

namespace JTL\Recommendation;

use \Illuminate\Support\Collection;

/**
 * Class Manager
 * @package JTL\Recommendation
 */
class Manager
{

    /**
     * @var Collection
     */
    private $recommendations;

    /**
     * Manager constructor.
     */
    public function __construct()
    {
        $this->setRecommendations();
    }

    /**
     *
     */
    public function setRecommendations(): void
    {
        $this->recommendations = new Collection();

        $testRec1          = (object)[
            'id' => 1,
            'product_description_a' => 'Kurzbeschreibung Testplugin 1',
            'offer_title' => 'Testplugin',
            'preview_image' => 'https://bilder.jtl-software.de/splogos/kServicepartner_519.png',
            'benefits' => ['toll', 'mega', 'muss man haben'],
            'installation_setup_description' => 'Installieren und so',
            'images' => [
                'https://bilder.jtl-software.de/splogos/kServicepartner_519.png',
                'https://bilder.jtl-software.de/splogos/kServicepartner_519.png',
                'https://bilder.jtl-software.de/splogos/kServicepartner_519.png',
                'https://bilder.jtl-software.de/splogos/kServicepartner_519.png'
            ]
        ];
        $testRec2          = (object)[
            'id' => 2,
            'product_description_a' => 'Kurzbeschreibung Testplugin 2',
            'offer_title' => 'Testplugin',
            'preview_image' => 'https://bilder.jtl-software.de/splogos/kServicepartner_519.png',
            'benefits' => ['toll', 'mega', 'muss man haben'],
            'installation_setup_description' => 'Installieren und so',
            'images' => [
                'https://bilder.jtl-software.de/splogos/kServicepartner_519.png',
                'https://bilder.jtl-software.de/splogos/kServicepartner_519.png',
                'https://bilder.jtl-software.de/splogos/kServicepartner_519.png',
                'https://bilder.jtl-software.de/splogos/kServicepartner_519.png'
            ]
        ];
        $testRec3          = (object)[
            'id' => 3,
            'product_description_a' => 'Kurzbeschreibung Testplugin 3',
            'offer_title' => 'Testplugin',
            'preview_image' => 'https://bilder.jtl-software.de/splogos/kServicepartner_519.png',
            'benefits' => ['toll', 'mega', 'muss man haben'],
            'installation_setup_description' => 'Installieren und so',
            'images' => [
                'https://bilder.jtl-software.de/splogos/kServicepartner_519.png',
                'https://bilder.jtl-software.de/splogos/kServicepartner_519.png',
                'https://bilder.jtl-software.de/splogos/kServicepartner_519.png',
                'https://bilder.jtl-software.de/splogos/kServicepartner_519.png'
            ]
        ];
        $recommendations   = [];
        $recommendations[] = $testRec1;
        $recommendations[] = $testRec2;
        $recommendations[] = $testRec3;
        //TODO: Cache
        foreach ($recommendations as $recommendation) {
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
     * @param int $id
     * @return Recommendation
     */
    public function getRecommendationById(int $id): Recommendation
    {
        return $this->recommendations->filter(static function (Recommendation $e) use ($id) {
            return $e->getId() === $id;
        })->first();
    }
}
