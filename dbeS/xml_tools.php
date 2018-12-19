<?php
###################################################################################
#
# XML Library, by Keith Devens, version 1.2b
# http://keithdevens.com/software/phpxml
#
# This code is Open Source, released under terms similar to the Artistic License.
# Read the license at http://keithdevens.com/software/license
#
###################################################################################

/**
 * takes raw XML as a parameter (a string)
 * and returns an equivalent PHP data structure
 *
 * @param string $xml
 * @param string $cEncoding
 * @return array|null
 */
function XML_unserialize(&$xml, $cEncoding = 'UTF-8')
{
    $xml_parser = new \JTL\XML($cEncoding);
    $data       = $xml_parser->parse($xml);
    $xml_parser->destruct();

    return $data;
}

/**
 * serializes any PHP data structure into XML
 * Takes one parameter: the data to serialize. Must be an array.
 *
 * @param mixed $data
 * @param int   $level
 * @param null  $prior_key
 * @return string
 */
function XML_serialize($data, $level = 0, $prior_key = null)
{
    $parser = new \JTL\XMLParser();
    return $parser->serializeXML($data, $level, $prior_key);
}
