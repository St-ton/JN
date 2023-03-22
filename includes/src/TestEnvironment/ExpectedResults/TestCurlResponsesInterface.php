<?php

namespace JTL\TestEnvironment\ExpectedResults;

interface TestCurlResponsesInterface
{
    public function getExpectedResult(): string;

    public function getVersion(): string;
}