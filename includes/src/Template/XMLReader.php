<?php declare(strict_types=1);

namespace JTL\Template;

use JTL\Shop;
use SimpleXMLElement;
use stdClass;

/**
 * Class XMLReader
 * @package JTL\Template
 */
class XMLReader
{
    /**
     * @param string $dirName
     * @param bool   $isAdmin
     * @return SimpleXMLElement|null
     */
    public function getXML(string $dirName, bool $isAdmin = false): ?SimpleXMLElement
    {
        $dirName = \basename($dirName);
        $xmlFile = $isAdmin === false
            ? \PFAD_ROOT . \PFAD_TEMPLATES . $dirName . \DIRECTORY_SEPARATOR . \TEMPLATE_XML
            : \PFAD_ROOT . \PFAD_ADMIN . \PFAD_TEMPLATES . $dirName . \DIRECTORY_SEPARATOR . \TEMPLATE_XML;
        if (!\file_exists($xmlFile)) {
            return null;
        }
        if (\defined('LIBXML_NOWARNING')) {
            //try to suppress warning if opening fails
            $xml = \simplexml_load_file($xmlFile, 'SimpleXMLElement', \LIBXML_NOWARNING);
        } else {
            $xml = \simplexml_load_file($xmlFile);
        }
        if ($xml === false) {
            $xml = \simplexml_load_string(\file_get_contents($xmlFile));
        }

        if (\is_a($xml, SimpleXMLElement::class)) {
            $xml->Ordner = $dirName;
        } else {
            $xml = null;
        }

        return $xml;
    }

    /**
     * @param string      $targetDir - the current template's dir name
     * @param string|null $parent
     * @return array
     */
    public function getConfigXML($targetDir, $parent = null): array
    {
        $dirs = [$targetDir];
        if ($parent !== null) {
            $dirs[] = $parent;
        }
        $sections        = [];
        $ignoredSettings = []; //list of settings that are overridden by child
        foreach ($dirs as $dir) {
            $xml = $this->getXML($dir);
            if (!$xml || !isset($xml->Settings, $xml->Settings->Section)) {
                continue;
            }
            /** @var SimpleXMLElement $xmlSection */
            foreach ($xml->Settings->Section as $xmlSection) {
                $section   = null;
                $sectionID = (string)$xmlSection->attributes()->Key;
                $exists    = false;
                foreach ($sections as &$_section) {
                    if ($_section->cKey === $sectionID) {
                        $exists  = true;
                        $section = $_section;
                        break;
                    }
                }
                if (!$exists) {
                    $section                = new stdClass();
                    $section->cName         = (string)$xmlSection->attributes()->Name;
                    $section->cKey          = $sectionID;
                    $section->oSettings_arr = [];
                }
                /** @var SimpleXMLElement $XMLSetting */
                foreach ($xmlSection->Setting as $XMLSetting) {
                    $key                    = (string)$XMLSetting->attributes()->Key;
                    $setting                = new stdClass();
                    $setting->rawAttributes = [];
                    $settingExists          = false;
                    $atts                   = $XMLSetting->attributes();
                    if (\in_array($key, $ignoredSettings, true)) {
                        continue;
                    }
                    foreach ($atts as $_k => $_attr) {
                        $setting->rawAttributes[$_k] = (string)$_attr;
                    }
                    if ((string)$XMLSetting->attributes()->override === 'true') {
                        $ignoredSettings[] = $key;
                    }
                    $setting->cName        = (string)$XMLSetting->attributes()->Description;
                    $setting->cKey         = $key;
                    $setting->cType        = (string)$XMLSetting->attributes()->Type;
                    $setting->cValue       = (string)$XMLSetting->attributes()->Value;
                    $setting->bEditable    = (string)$XMLSetting->attributes()->Editable;
                    $setting->cPlaceholder = (string)$XMLSetting->attributes()->Placeholder;
                    // negative values for the 'toggle'-attributes of textarea(resizable), check-boxes and radio-buttons
                    $vToggleValues = ['0', 'no', 'none', 'off', 'false'];
                    // special handling for textarea-type settings
                    if ($setting->cType === 'textarea') {
                        // inject the tag-attributes of the TextAreaValue in our oSetting
                        $setting->vTextAreaAttr_arr = [];
                        // get the SimpleXMLElement-array
                        $attr = $XMLSetting->TextAreaValue->attributes();
                        // we insert our default "no resizable"
                        $setting->vTextAreaAttr_arr['Resizable'] = 'none';
                        foreach ($attr as $_key => $_val) {
                            $_val                              = (string)$_val; // cast the value(!)
                            $setting->vTextAreaAttr_arr[$_key] = $_val;
                            // multiple values of 'disable resizing' are allowed,
                            // but only vertical is ok, if 'resizable' is required
                            if ((string)$_key === 'Resizable') {
                                \in_array($_val, $vToggleValues, true)
                                    ? $setting->vTextAreaAttr_arr[$_key] = 'none'
                                    : $setting->vTextAreaAttr_arr[$_key] = 'vertical';
                                // only vertical, because horizontal breaks the layout
                            } else {
                                $setting->vTextAreaAttr_arr[$_key] = $_val;
                            }
                        }
                        // get the tag-content of "TextAreaValue"; trim leading and trailing spaces
                        $textLines = \mb_split("\n", (string)$XMLSetting->TextAreaValue);
                        \array_walk($textLines, '\trim');
                        $setting->cTextAreaValue = \implode("\n", $textLines);
                    }
                    foreach ($section->oSettings_arr as $_setting) {
                        if ($_setting->cKey === $setting->cKey) {
                            $settingExists = true;
                            $setting       = $_setting;
                            break;
                        }
                    }
                    if (\is_string($setting->bEditable)) {
                        $setting->bEditable = \mb_strlen($setting->bEditable) === 0
                            ? true
                            : (bool)(int)$setting->bEditable;
                    }
                    if (isset($XMLSetting->Option)) {
                        if (!isset($setting->oOptions_arr)) {
                            $setting->oOptions_arr = [];
                        }
                        /** @var SimpleXMLElement $XMLOption */
                        foreach ($XMLSetting->Option as $XMLOption) {
                            $oOption          = new stdClass();
                            $oOption->cName   = (string)$XMLOption;
                            $oOption->cValue  = (string)$XMLOption->attributes()->Value;
                            $oOption->cOrdner = $dir; //add current folder to option - useful for theme previews
                            if ((string)$XMLOption === '' && (string)$XMLOption->attributes()->Name !== '') {
                                // overwrite the cName (which defaults to the tag-content),
                                // if it's empty, with the Option-attribute "Name", if we got that
                                $oOption->cName = (string)$XMLOption->attributes()->Name;
                            }
                            $setting->oOptions_arr[] = $oOption;
                        }
                    }
                    if (isset($XMLSetting->Optgroup)) {
                        if (!isset($setting->oOptgroup_arr)) {
                            $setting->oOptgroup_arr = [];
                        }
                        /** @var SimpleXMLElement $XMLOptgroup */
                        foreach ($XMLSetting->Optgroup as $XMLOptgroup) {
                            $optgroup              = new stdClass();
                            $optgroup->cName       = (string)$XMLOptgroup->attributes()->label;
                            $optgroup->oValues_arr = [];
                            /** @var SimpleXMLElement $XMLOptgroupOption */
                            foreach ($XMLOptgroup->Option as $XMLOptgroupOption) {
                                $oOptgroupValues         = new stdClass();
                                $oOptgroupValues->cName  = (string)$XMLOptgroupOption;
                                $oOptgroupValues->cValue = (string)$XMLOptgroupOption->attributes()->Value;
                                $optgroup->oValues_arr[] = $oOptgroupValues;
                            }
                            $setting->oOptgroup_arr[] = $optgroup;
                        }
                    }
                    if (!$settingExists) {
                        $section->oSettings_arr[] = $setting;
                    }
                }
                if (!$exists) {
                    $sections[] = $section;
                }
            }
        }

        return $sections;
    }
}
