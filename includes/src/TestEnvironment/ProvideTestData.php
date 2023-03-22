<?php

namespace JTL\TestEnvironment;

use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Shop;
use JTL\TestEnvironment\ExpectedResults\CategoryResponse;

abstract class ProvideTestData
{
    //provide valid initial data for API Tests

    protected array $foundCategories = [];

    protected DbInterface $db;

    public function __construct()
    {
        $this->db               = Shop::Container()->getDB();
        $this->expectedGetValue = (new CategoryResponse())->getExpectedResult();
    }

    public function runCategoryTests()
    {
        $log = $this->prepareTestEnvironment();

        $log = $this->setData($log);

        $log = $this->getList($log);

        return $log;
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

    abstract protected function setData($log): array;

    abstract protected function getList($log): array;

    protected function stripResults(string $value): string
    {
        return str_replace(["\r", "\n", ' '], '', json_decode(json_encode($value)));
    }
}
