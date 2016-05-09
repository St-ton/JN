<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

/**
 * Class UnitsOfMeasure
 *
 * @see http://unitsofmeasure.org/ucum.html
 */
class UnitsOfMeasure
{
    /**
     * ucum code to print mapping table
     *
     * @var array
     */
    public static $UCUMcodeToPrint = array(
        'm'      => 'm',
        'mm'     => 'mm',
        'cm'     => 'cm',
        'dm'     => 'dm',
        '[in_i]' => '&Prime;', //inch
        'km'     => 'km',
        'kg'     => 'kg',
        'mg'     => 'mg',
        'g'      => 'g',
        't'      => 't',
        'm2'     => 'm<sup>2</sup>', //square meters
        'mm2'    => 'mm<sup>2</sup>',
        'cm2'    => 'cm<sup>2</sup>',
        'L'      => 'l',
        'mL'     => 'ml',
        'dL'     => 'dl',
        'cL'     => 'cl',
        'm3'     => 'm<sup>3</sup>',
        'cm3'    => 'cm<sup>3</sup>'
    );

    /**
     * @param string $ucumCode
     * @return mixed
     */
    public static function getPrintAbbreviation($ucumCode)
    {
        return ($ucumCode !== null && !empty(self::$UCUMcodeToPrint[$ucumCode])) ? self::$UCUMcodeToPrint[$ucumCode] : '';
    }
}
