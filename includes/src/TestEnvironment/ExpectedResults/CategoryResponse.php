<?php

namespace JTL\TestEnvironment\ExpectedResults;

/**
 * Response expected by \JTL\TestEnvironment\ProvideTestData::getCategoryList
 */
class CategoryResponse implements TestCurlResponsesInterface
{
    protected string $version = '5.2.3-alpha';

    protected string $categoriesReply = '{
    "data": [
        {
            "id": 1,
            "slug": "Fitness_2",
            "name": "Fitness",
            "description": "",
            "parentID": 0,
            "sort": 1,
            "lastModified": {
                "date": "2023-02-02 00:00:00.000000",
                "timezone_type": 3,
                "timezone": "Europe/Berlin"
            },
            "lft": 76,
            "rght": 87,
            "level": 1,
            "localization": [
                {
                    "categoryID": 1,
                    "languageID": 2,
                    "slug": "Fitness_3",
                    "name": "Fitness",
                    "description": "",
                    "metaDescription": "",
                    "metaKeywords": "",
                    "metaTitle": ""
                }
            ],
            "images": [],
            "attributes": [],
            "visibility": [],
            "discount": [],
            "categories": []
        },
        {
            "id": 2,
            "slug": "Geraete",
            "name": "Ger\u00e4te",
            "description": "Die richtige Unterst\u00fctzung f\u00fcr den Muskelaufbau und das Ausdauertraining.",
            "parentID": 1,
            "sort": 2,
            "lastModified": {
                "date": "2023-02-02 00:00:00.000000",
                "timezone_type": 3,
                "timezone": "Europe/Berlin"
            },
            "lft": 81,
            "rght": 82,
            "level": 2,
            "localization": [
                {
                    "categoryID": 2,
                    "languageID": 2,
                    "slug": "Equipment",
                    "name": "Equipment",
                    "description": "",
                    "metaDescription": "",
                    "metaKeywords": "",
                    "metaTitle": ""
                }
            ],
            "images": [],
            "attributes": [
                {
                    "id": 1,
                    "categoryID": 2,
                    "name": "kategoriebox",
                    "value": "1",
                    "sort": 0,
                    "function": 1,
                    "localization": []
                },
                {
                    "id": 600000002,
                    "categoryID": 2,
                    "name": "meta_description",
                    "value": "ger\u00e4te halt",
                    "sort": 0,
                    "function": 1,
                    "localization": []
                }
            ],
            "visibility": [],
            "discount": [],
            "categories": [
                {
                    "id": 7,
                    "productID": 44,
                    "categoryID": 2
                }
            ]
        }
    ],
    "meta": {
        "cursor": {
            "current": 0,
            "prev": 0,
            "next": "2",
            "count": 2
            }
        }
    }';

    public function getExpectedResult() : string
    {
        return str_replace(array("\r", "\n", ' '), '', json_decode(json_encode($this->categoriesReply)));
    }

    public function getVersion(): string
    {
        return $this->version;
    }
}
