#!/usr/bin/env php
<?php declare(strict_types=1);

require_once __DIR__ . '/../../includes/vendor/autoload.php';

use OpenApi\Generator;
use Symfony\Component\Yaml\Yaml;

$src         = Generator::scan([__DIR__ . '/../../includes/src/REST']);
$yaml        = $src->toYaml(
    Yaml::DUMP_OBJECT_AS_MAP
    ^ Yaml::DUMP_EMPTY_ARRAY_AS_SEQUENCE
    ^ Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK
);
$openApiFile = __DIR__ . '/../../openapi.yaml';
file_put_contents($openApiFile, $yaml);
echo sprintf("Wrote OpenAPI specification file to %s\n", realpath($openApiFile));
