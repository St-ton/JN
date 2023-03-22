<?php

namespace JTL\TestEnvironment;

use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Shop;
use JTL\TestEnvironment\ExpectedResults\CategoryResponse;

class TestsCategorie extends ProvideTestData
{
    //provide valid initial data for API Tests

    protected array $foundCategories = [];
    protected string $expectedGetValue;

    public function __construct()
    {
        parent::__construct();

        $this->expectedGetValue = (new CategoryResponse())->getExpectedResult();
    }

    private function prepareTestEnvironment(): array
    {
        return (new TestDBInstaller(post: [
            'db'    => [
                'host' => \DB_HOST,
                'user' => \DB_USER,
                'pass' => \DB_PASS,
                'name' => \DB_NAME,
            ],
            'admin' => [
                'name'   => 'TestName',
                'pass'   => 'pass',
                'locale' => 'de'
            ],
            'wawi'  => [
                'name' => 'WaWi',
                'pass' => 'pass'
            ]]))->run();
    }

    protected function setData($log): array
    {
        $tkategorie = [
            'kKategorie',
            'cSeo',
            'cName',
            'cBeschreibung',
            'kOberKategorie',
            'nSort',
            'dLetzteAktualisierung',
            'lft',
            'rght',
            'nLevel',
        ];
        $data       = [
            'tkategorie'             => "INSERT INTO `tkategorie` (`kKategorie`, `cSeo`, `cName`, `cBeschreibung`, `kOberKategorie`, `nSort`, `dLetzteAktualisierung`, `lft`, `rght`, `nLevel`) VALUES
    (1, 'Fitness_2', 'Fitness', '', 0, 1, '2023-02-02', 76, 87, 1),
(2, 'Geraete', 'Geräte', 'Die richtige Unterstützung für den Muskelaufbau und das Ausdauertraining.', 1, 2, '2023-02-02', 81, 82, 2)",
            'tkategorieartikel'      => 'INSERT INTO `tkategorieartikel` (`kKategorieArtikel`, `kArtikel`, `kKategorie`) VALUES
(7, 44, 2)',
            'tkategorieattribut'     => "INSERT INTO `tkategorieattribut` (`kKategorieAttribut`, `kKategorie`, `cName`, `cWert`, `nSort`, `bIstFunktionsAttribut`) VALUES
(1, 2, 'kategoriebox', '1', 0, 1),
(600000002, 2, 'meta_description', 'geräte halt', 0, 1);",
            'tkategoriekundengruppe' => 'INSERT INTO `tkategoriekundengruppe` (`kKundengruppe`, `kKategorie`, `fRabatt`) VALUES
(1, 2, 20);',
            'tkategoriesprache'      => "INSERT INTO `tkategoriesprache` (`kKategorie`, `kSprache`, `cSeo`, `cName`, `cBeschreibung`, `cMetaDescription`, `cMetaKeywords`, `cTitleTag`) VALUES
(1, 2, 'Fitness_3', 'Fitness', '', '', '', ''),
(2, 2, 'Equipment', 'Equipment', '', '', '', '');",
            'api_keys'               => "INSERT INTO `api_keys` (`id`, `key`, `created`) VALUES ('1', 'test', '2023-03-06 13:40:30');",
        ];
        $log[]      = 'Preparing categoryData';
        foreach ($data as $table => $stmt) {
            $log[] = 'Executing: ' . $stmt;
            $id    = $this->db->query($stmt, ReturnType::LAST_INSERTED_ID);
            $log[] = 'Inserted with ID: ' . $id;
            if ($id === 0) {
                $log[] = 'ERROR: Could not insert ' . $stmt;
            }
        }

        return $log;
    }

    protected function getList($log): array
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL            => \URL_SHOP . '/api/v1/category',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'GET',
            CURLOPT_HTTPHEADER     => array(
                'testDB: true',
                'Cookie: XDEBUG_SESSION=PHPSTORM',
                'x-api-key: test'
            ),
        ));

        $response = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($httpcode !== 200) {
            $log[] = 'ERROR: Unexpected returncode: ' . $httpcode;
        }
        curl_close($curl);
        $this->foundCategories = json_decode($response, true);
        foreach ($this->foundCategories['data'] as $category) {
            $log[] = 'Category found: ' . serialize($category);
        }
        if ($this->stripResults($response) === $this->expectedGetValue) {
            $log[] = "Result matches Expectation";
        } else {
            $log[] = 'ERROR: Result not matching Expectation';
        }

        return $log;
    }
}
