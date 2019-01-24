<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * takes raw XML as a parameter (a string)
 * and returns an equivalent PHP data structure
 *
 * @param string $xml
 * @param string $encoding
 * @return array|null
 */
function XML_unserialize(&$xml, $encoding = 'UTF-8')
{
    $parser = new \JTL\XML($encoding);
    $data   = $parser->parse($xml);
    $parser->destruct();

    return $data;
}

/**
 * serializes any PHP data structure into XML
 * Takes one parameter: the data to serialize. Must be an array.
 *
 * @param mixed $data
 * @param int   $level
 * @param null  $prevKey
 * @return string
 */
function XML_serialize($data, $level = 0, $prevKey = null)
{
    $parser = new \JTL\XMLParser();

    return $parser->serializeXML($data, $level, $prevKey);
}
