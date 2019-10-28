<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Mapper;

use JTL\Plugin\InstallCode;

/**
 * Class PluginValidation
 * @package JTL\Mapper
 */
class PluginValidation
{
    /**
     * @param int     $code
     * @param string|null $pluginID
     * @return string
     */
    public function map(int $code, string $pluginID = null): string
    {
        if ($code === 0) {
            return '';
        }
        $return = 'Fehler: ';
        switch ($code) {
            case InstallCode::WRONG_PARAM:
                $return .= 'Die Plausibilität ist aufgrund fehlender Parameter abgebrochen.';
                break;
            case InstallCode::DIR_DOES_NOT_EXIST:
                $return .= 'Das Pluginverzeichnis existiert nicht.';
                break;
            case InstallCode::INFO_XML_MISSING:
                $return .= 'Die Informations XML Datei existiert nicht.';
                break;
            case InstallCode::NO_PLUGIN_FOUND:
                $return .= 'Das ausgewählte Plugin wurde nicht in der Datenbank gefunden.';
                break;
            case InstallCode::INVALID_NAME:
                $return .= 'Der Pluginname entspricht nicht der Konvention.';
                break;
            case InstallCode::INVALID_PLUGIN_ID:
                $return .= 'Die PluginID entspricht nicht der Konvention.';
                break;
            case InstallCode::INSTALL_NODE_MISSING:
                $return .= 'Der Installationsknoten ist nicht vorhanden.';
                break;
            case InstallCode::INVALID_XML_VERSION_NUMBER:
                $return .= 'Erste Versionsnummer entspricht nicht der Konvention.';
                break;
            case InstallCode::INVALID_VERSION_NUMBER:
                $return .= 'Die Versionsnummer entspricht nicht der Konvention.';
                break;
            case InstallCode::INVALID_DATE:
                $return .= 'Das Versionsdatum entspricht nicht der Konvention.';
                break;
            case InstallCode::MISSING_SQL_FILE:
                $return .= 'SQL-Datei für die aktuelle Version existiert nicht.';
                break;
            case InstallCode::MISSING_HOOKS:
                $return .= 'Keine Hooks vorhanden.';
                break;
            case InstallCode::INVALID_HOOK:
                $return .= 'Die Hook-Werte entsprechen nicht den Konventionen.';
                break;
            case InstallCode::INVALID_CUSTOM_LINK_NAME:
                $return .= 'CustomLink Name entspricht nicht der Konvention.';
                break;
            case InstallCode::INVALID_CUSTOM_LINK_FILE_NAME:
                $return .= 'Dateiname entspricht nicht der Konvention.';
                break;
            case InstallCode::MISSING_CUSTOM_LINK_FILE:
                $return .= 'CustomLink-Datei existiert nicht.';
                break;
            case InstallCode::INVALID_CONFIG_LINK_NAME:
                $return .= 'EinstellungsLink Name entspricht nicht der Konvention.';
                break;
            case InstallCode::MISSING_CONFIG:
                $return .= 'Einstellungen fehlen.';
                break;
            case InstallCode::INVALID_CONFIG_TYPE:
                $return .= 'Einstellungen type entspricht nicht der Konvention.';
                break;
            case InstallCode::INVALID_CONFIG_INITIAL_VALUE:
                $return .= 'Einstellungen initialValue entspricht nicht der Konvention.';
                break;
            case InstallCode::INVALID_CONFIG_SORT_VALUE:
                $return .= 'Einstellungen sort entspricht nicht der Konvention.';
                break;
            case InstallCode::INVALID_CONFIG_NAME:
                $return .= 'Einstellungen Name entspricht nicht der Konvention.';
                break;
            case InstallCode::MISSING_CONFIG_SELECTBOX_OPTIONS:
            case InstallCode::MISSING_PAYMENT_METHOD_SELECTBOX_OPTIONS:
                $return .= 'Keine SelectboxOptionen vorhanden.';
                break;
            case InstallCode::INVALID_CONFIG_OPTION:
            case InstallCode::INVALID_PAYMENT_METHOD_OPTION:
                $return .= 'Die Option entspricht nicht der Konvention.';
                break;
            case InstallCode::MISSING_LANG_VARS:
                $return .= 'Keine Sprachvariablen vorhanden.';
                break;
            case InstallCode::INVALID_LANG_VAR_NAME:
                $return .= 'Variable Name entspricht nicht der Konvention.';
                break;
            case InstallCode::MISSING_LOCALIZED_LANG_VAR:
                $return .= 'Keine lokalisierte Sprachvariable vorhanden.';
                break;
            case InstallCode::INVALID_LANG_VAR_ISO:
                $return .= 'Die ISO der lokalisierten Sprachvariable entspricht nicht der Konvention.';
                break;
            case InstallCode::INVALID_LOCALIZED_LANG_VAR_NAME:
                $return .= 'Der Name der lokalisierten Sprachvariable entspricht nicht der Konvention.';
                break;
            case InstallCode::MISSING_HOOK_FILE:
                $return .= 'Die Hook-Datei ist nicht vorhanden.';
                break;
            case InstallCode::MISSING_VERSION_DIR:
                $return .= 'Version existiert nicht im Versionsordner.';
                break;
            case InstallCode::INVALID_CONF:
                $return .= 'Einstellungen conf entspricht nicht der Konvention.';
                break;
            case InstallCode::INVALID_CONF_VALUE_NAME:
                $return .= 'Einstellungen ValueName entspricht nicht der Konvention.';
                break;
            case InstallCode::INVALID_XML_VERSION:
                $return .= 'XML-Version entspricht nicht der Konvention.';
                break;
            case InstallCode::INVALID_SHOP_VERSION:
                $return .= 'Shopversion entspricht nicht der Konvention.';
                break;
            case InstallCode::SHOP_VERSION_COMPATIBILITY:
                $return .= 'Shopversion ist zu niedrig.';
                break;
            case InstallCode::MISSING_FRONTEND_LINKS:
                $return .= 'Keine Frontendlinks vorhanden, obwohl der Node angelegt wurde.';
                break;
            case InstallCode::INVALID_FRONTEND_LINK_FILENAME:
                $return .= 'Link Filename entspricht nicht der Konvention.';
                break;
            case InstallCode::INVALID_FRONTEND_LINK_NAME:
                $return .= 'LinkName entspricht nicht der Konvention.';
                break;
            case InstallCode::INVALID_FRONEND_LINK_VISIBILITY:
                $return .= 'Angabe ob erst Sichtbar nach Login entspricht nicht der Konvention.';
                break;
            case InstallCode::INVALID_FRONEND_LINK_PRINT:
                $return .= 'Abgabe ob eine Druckbutton gezeigt werden soll entspricht nicht der Konvention.';
                break;
            case InstallCode::INVALID_FRONEND_LINK_ISO:
                $return .= 'Die ISO der Linksprache entspricht nicht der Konvention.';
                break;
            case InstallCode::INVALID_FRONEND_LINK_SEO:
                $return .= 'Der Seo Name entspricht nicht der Konvention.';
                break;
            case InstallCode::INVALID_FRONEND_LINK_NAME:
                $return .= 'Der Name entspricht nicht der Konvention.';
                break;
            case InstallCode::INVALID_FRONEND_LINK_TITLE:
                $return .= 'Der Title entspricht nicht der Konvention.';
                break;
            case InstallCode::INVALID_FRONEND_LINK_META_TITLE:
                $return .= 'Der MetaTitle entspricht nicht der Konvention.';
                break;
            case InstallCode::INVALID_FRONEND_LINK_META_KEYWORDS:
                $return .= 'Die MetaKeywords entsprechen nicht der Konvention.';
                break;
            case InstallCode::INVALID_FRONEND_LINK_META_DESCRIPTION:
                $return .= 'Die MetaDescription entspricht nicht der Konvention.';
                break;
            case InstallCode::INVALID_PAYMENT_METHOD_NAME:
                $return .= 'Der Name in den Zahlungsmethoden entspricht nicht der Konvention.';
                break;
            case InstallCode::INVALID_PAYMENT_METHOD_MAIL:
                $return .= 'Sende Mail in den Zahlungsmethoden entspricht nicht der Konvention.';
                break;
            case InstallCode::INVALID_PAYMENT_METHOD_TSCODE:
                $return .= 'TSCode in den Zahlungsmethoden entspricht nicht der Konvention.';
                break;
            case InstallCode::INVALID_PAYMENT_METHOD_PRE_ORDER:
                $return .= 'PreOrder in den Zahlungsmethoden entspricht nicht der Konvention.';
                break;
            case InstallCode::INVALID_PAYMENT_METHOD_CLASS_FILE:
                $return .= 'ClassFile in den Zahlungsmethoden entspricht nicht der Konvention.';
                break;
            case InstallCode::MISSING_PAYMENT_METHOD_FILE:
                $return .= 'Die Datei für die Klasse der Zahlungsmethode existiert nicht.';
                break;
            case InstallCode::INVALID_PAYMENT_METHOD_TEMPLATE:
                $return .= 'TemplateFile in den Zahlungsmethoden entspricht nicht der Konvention.';
                break;
            case InstallCode::MISSING_PAYMENT_METHOD_TEMPLATE:
                $return .= 'Die Datei für das Template der Zahlungsmethode existiert nicht.';
                break;
            case InstallCode::MISSING_PAYMENT_METHOD_LANGUAGES:
                $return .= 'Keine Sprachen in den Zahlungsmethoden hinterlegt.';
                break;
            case InstallCode::INVALID_PAYMENT_METHOD_LANGUAGE_ISO:
                $return .= 'Die ISO der Sprache in der Zahlungsmethode entspricht nicht der Konvention.';
                break;
            case InstallCode::INVALID_PAYMENT_METHOD_NAME_LOCALIZED:
                $return .= 'Der Name in den Zahlungsmethoden Sprache entspricht nicht der Konvention.';
                break;
            case InstallCode::INVALID_PAYMENT_METHOD_CHARGE_NAME:
                $return .= 'Der ChargeName in den Zahlungsmethoden Sprache entspricht nicht der Konvention.';
                break;
            case InstallCode::INVALID_PAYMENT_METHOD_INFO_TEXT:
                $return .= 'Der InfoText in den Zahlungsmethoden Sprache entspricht nicht der Konvention.';
                break;
            case InstallCode::INVALID_PAYMENT_METHOD_CONFIG_TYPE:
                $return .= 'Zahlungsmethode Einstellungen type entspricht nicht der Konvention.';
                break;
            case InstallCode::INVALID_PAYMENT_METHOD_CONFIG_INITITAL_VALUE:
                $return .= 'Zahlungsmethode Einstellungen initialValue entspricht nicht der Konvention.';
                break;
            case InstallCode::INVALID_PAYMENT_METHOD_CONFIG_SORT:
                $return .= 'Zahlungsmethode Einstellungen sort entspricht nicht der Konvention.';
                break;
            case InstallCode::INVALID_PAYMENT_METHOD_CONFIG_CONF:
                $return .= 'Zahlungsmethode Einstellungen conf entspricht nicht der Konvention.';
                break;
            case InstallCode::INVALID_PAYMENT_METHOD_CONFIG_NAME:
                $return .= 'Zahlungsmethode Einstellungen Name entspricht nicht der Konvention.';
                break;
            case InstallCode::INVALID_PAYMENT_METHOD_VALUE_NAME:
                $return .= 'Zahlungsmethode Einstellungen ValueName entspricht nicht der Konvention.';
                break;
            case InstallCode::INVALID_PAYMENT_METHOD_SORT:
                $return .= 'Die Sortierung in den Zahlungsmethoden entspricht nicht der Konvention.';
                break;
            case InstallCode::INVALID_PAYMENT_METHOD_SOAP:
                $return .= 'Soap in den Zahlungsmethoden entspricht nicht der Konvention.';
                break;
            case InstallCode::INVALID_PAYMENT_METHOD_CURL:
                $return .= 'Curl in den Zahlungsmethoden entspricht nicht der Konvention.';
                break;
            case InstallCode::INVALID_PAYMENT_METHOD_SOCKETS:
                $return .= 'Sockets in den Zahlungsmethoden entspricht nicht der Konvention.';
                break;
            case InstallCode::INVALID_PAYMENT_METHOD_CLASS_NAME:
                $return .= 'ClassName in den Zahlungsmethoden entspricht nicht der Konvention.';
                break;
            case InstallCode::INVALID_FULLSCREEN_TEMPLATE:
                $return .= 'Der Fullscreen-Templatename entspricht nicht der Konvention.';
                break;
            case InstallCode::MISSING_FRONTEND_LINK_TEMPLATE:
                $return .= 'Die Templatedatei für den Frontend Link existiert nicht.';
                break;
            case InstallCode::TOO_MANY_FULLSCREEN_TEMPLATE_NAMES:
                $return .= 'Es darf nur ein Templatename oder ein Fullscreen Templatename existieren.';
                break;
            case InstallCode::INVALID_FULLSCREEN_TEMPLATE_NAME:
                $return .= 'Der Fullscreen Templatename entspricht nicht der Konvention.';
                break;
            case InstallCode::MISSING_FULLSCREEN_TEMPLATE_FILE:
                $return .= 'Die Fullscreen Templatedatei für den Frontend Link existiert nicht.';
                break;
            case InstallCode::INVALID_FRONTEND_LINK_TEMPLATE_FULLSCREEN_TEMPLATE:
                $return .= 'Für einen Frontend Link muss ein Templatename ' .
                    'oder Fullscreen Templatename angegeben werden.';
                break;
            case InstallCode::MISSING_BOX:
                $return .= 'Keine Box vorhanden.';
                break;
            case InstallCode::INVALID_BOX_NAME:
                $return .= 'Box Name entspricht nicht der Konvention.';
                break;
            case InstallCode::INVALID_BOX_TEMPLATE:
                $return .= 'Box Templatedatei entspricht nicht der Konvention.';
                break;
            case InstallCode::MISSING_BOX_TEMPLATE_FILE:
                $return .= 'Box Templatedatei existiert nicht.';
                break;
            case InstallCode::MISSING_LICENCE_FILE:
                $return .= 'Lizenzklasse existiert nicht.';
                break;
            case InstallCode::INVALID_LICENCE_FILE_NAME:
                $return .= 'Name der Lizenzklasse entspricht nicht der konvention.';
                break;
            case InstallCode::MISSING_LICENCE:
                $return .= 'Lizenklasse ist nicht definiert.';
                break;
            case InstallCode::MISSING_LICENCE_CHECKLICENCE_METHOD:
                $return .= 'Methode checkLicence in der Lizenzklasse ist nicht definiert.';
                break;
            case InstallCode::DUPLICATE_PLUGIN_ID:
                $return .= 'PluginID bereits in der Datenbank vorhanden.';
                break;
            case InstallCode::MISSING_EMAIL_TEMPLATES:
                $return .= 'Keine Emailtemplates vorhanden, obwohl der Node angelegt wurde.';
                break;
            case InstallCode::INVALID_TEMPLATE_NAME:
                $return .= 'Template Name entspricht nicht der Konvention.';
                break;
            case InstallCode::INVALID_TEMPLATE_TYPE:
                $return .= 'Template Type entspricht nicht der Konvention.';
                break;
            case InstallCode::INVALID_TEMPLATE_MODULE_ID:
                $return .= 'Template ModulId entspricht nicht der Konvention.';
                break;
            case InstallCode::INVALID_TEMPLATE_ACTIVE:
                $return .= 'Template Active entspricht nicht der Konvention.';
                break;
            case InstallCode::INVALID_TEMPLATE_AKZ:
                $return .= 'Template AKZ entspricht nicht der Konvention.';
                break;
            case InstallCode::INVALID_TEMPLATE_AGB:
                $return .= 'Template AGB entspricht nicht der Konvention.';
                break;
            case InstallCode::INVALID_TEMPLATE_WRB:
                $return .= 'Template WRB entspricht nicht der Konvention.';
                break;
            case InstallCode::INVALID_EMAIL_TEMPLATE_ISO:
                $return .= 'Die ISO der Emailtemplate Sprache entspricht nicht der Konvention.';
                break;
            case InstallCode::INVALID_EMAIL_TEMPLATE_SUBJECT:
                $return .= 'Der Subject Name entspricht nicht der Konvention.';
                break;
            case InstallCode::MISSING_EMAIL_TEMPLATE_LANGUAGE:
                $return .= 'Keine Templatesprachen vorhanden.';
                break;
            case InstallCode::INVALID_CHECKBOX_FUNCTION_NAME:
                $return .= 'CheckBoxFunction Name entspricht nicht der Konvention.';
                break;
            case InstallCode::INVALID_CHECKBOX_FUNCTION_ID:
                $return .= 'CheckBoxFunction ID entspricht nicht der Konvention.';
                break;
            case InstallCode::INVALID_FRONTEND_LINK_NO_FOLLOW:
                $return .= 'Frontend Link Attribut NoFollow entspricht nicht der Konvention.';
                break;
            case InstallCode::MISSING_WIDGETS:
                $return .= 'Keine Widgets vorhanden.';
                break;
            case InstallCode::INVALID_WIDGET_TITLE:
                $return .= 'Widget Title entspricht nicht der Konvention.';
                break;
            case InstallCode::INVALID_WIDGET_CLASS:
                $return .= 'Widget Class entspricht nicht der Konvention.';
                break;
            case InstallCode::MISSING_WIDGET_CLASS_FILE:
                $return .= 'Die Datei für die Klasse des AdminWidgets existiert nicht.';
                break;
            case InstallCode::INVALID_WIDGET_CONTAINER:
                $return .= 'Container im Widget entspricht nicht der Konvention.';
                break;
            case InstallCode::INVALID_WIDGET_POS:
                $return .= 'Pos im Widget entspricht nicht der Konvention.';
                break;
            case InstallCode::INVALID_WIDGET_EXPANDED:
                $return .= 'Expanded im Widget entspricht nicht der Konvention.';
                break;
            case InstallCode::INVALID_WIDGET_ACTIVE:
                $return .= 'Active im Widget entspricht nicht der Konvention.';
                break;
            case InstallCode::INVALID_PAYMENT_METHOD_ADDITIONAL_STEP_TEMPLATE_FILE:
                $return .= 'AdditionalTemplateFile in den Zahlungsmethoden entspricht nicht der Konvention';
                break;
            case InstallCode::MISSING_PAYMENT_METHOD_ADDITIONAL_STEP_FILE:
                $return .= 'Die Datei für das Zusatzschritt-Template der Zahlungsmethode existiert nicht';
                break;
            case InstallCode::MISSING_FORMATS:
                $return .= 'Keine Formate vorhanden';
                break;
            case InstallCode::INVALID_FORMAT_NAME:
                $return .= 'Format Name entspricht nicht der Konvention';
                break;
            case InstallCode::INVALID_FORMAT_FILE_NAME:
                $return .= 'Format Filename entspricht nicht der Konvention';
                break;
            case InstallCode::MISSING_FORMAT_CONTENT:
                $return .= 'Format enthält weder Content, noch eine Contentdatei';
                break;
            case InstallCode::INVALID_FORMAT_ENCODING:
                $return .= 'Format Encoding entspricht nicht der Konvention';
                break;
            case InstallCode::INVALID_FORMAT_SHIPPING_COSTS_DELIVERY_COUNTRY:
                $return .= 'Format ShippingCostsDeliveryCountry entspricht nicht der Konvention';
                break;
            case InstallCode::INVALID_FORMAT_CONTENT_FILE:
                $return .= 'Format ContenFile entspricht nicht der Konvention';
                break;
            case InstallCode::MISSING_EXTENDED_TEMPLATE:
                $return .= 'Kein Template vorhanden';
                break;
            case InstallCode::INVALID_EXTENDED_TEMPLATE_FILE_NAME:
                $return .= 'Templatedatei entspricht nicht der Konvention';
                break;
            case InstallCode::MISSING_EXTENDED_TEMPLATE_FILE:
                $return .= 'Templatedatei existiert nicht';
                break;
            case InstallCode::MISSING_UNINSTALL_FILE:
                $return .= 'Uninstall File existiert nicht';
                break;
            case InstallCode::IONCUBE_REQUIRED:
                $return .= 'Das Plugin benötigt ionCube';
                break;
            case InstallCode::INVALID_OPTIONS_SOURE_FILE:
                $return .= 'OptionsSource-Datei wurde nicht angegeben';
                break;
            case InstallCode::MISSING_OPTIONS_SOURE_FILE:
                $return .= 'OptionsSource-Datei existiert nicht';
                break;
            case InstallCode::MISSING_BOOTSTRAP_CLASS:
                $return .= 'Bootstrap-Klasse "%cPluginID%\\Bootstrap" existiert nicht';
                break;
            case InstallCode::INVALID_BOOTSTRAP_IMPLEMENTATION:
                $return .= 'Bootstrap-Klasse "%cPluginID%\\Bootstrap" muss das PlugInterface implementieren';
                break;
            case InstallCode::INVALID_AUTHOR:
                $return .= 'Autor entspricht nicht der Konvention.';
                break;
            case InstallCode::MISSING_PORTLETS:
                $return = 'Fehler: Keine Portlets vorhanden';
                break;
            case InstallCode::INVALID_PORTLET_TITLE:
                $return = 'Fehler: Portlet Title entspricht nicht der Konvention';
                break;
            case InstallCode::INVALID_PORTLET_CLASS:
                $return = 'Fehler: Portlet Class entspricht nicht der Konvention';
                break;
            case InstallCode::INVALID_PORTLET_CLASS_FILE:
                $return = 'Fehler: Die Datei für die Klasse des Portlet existiert nicht';
                break;
            case InstallCode::INVALID_PORTLET_GROUP:
                $return = 'Fehler: Group im Portlet entspricht nicht der Konvention';
                break;
            case InstallCode::INVALID_PORTLET_ACTIVE:
                $return = 'Fehler: Active im Portlet entspricht nicht der Konvention';
                break;
            case InstallCode::MISSING_BLUEPRINTS:
                $return = 'Fehler: Keine Blueprints vorhanden';
                break;
            case InstallCode::INVALID_BLUEPRINT_NAME:
                $return = 'Fehler: Blueprint Name entspricht nicht der Konvention';
                break;
            case InstallCode::INVALID_BLUEPRINT_FILE:
                $return = 'Fehler: Die Datei für das Blueprint existiert nicht';
                break;
            case InstallCode::EXT_MUST_NOT_HAVE_UNINSTALLER:
                $return = 'Fehler: Extension darf keinen Uninstaller definieren';
                break;
            case InstallCode::WRONG_EXT_DIR:
                $return = 'Fehler: Extension in falschem Ordner installiert';
                break;
            default:
                $return = 'Unbekannter Fehler.';
                break;
        }

        return \str_replace('%cPluginID%', $pluginID ?? '', $return);
    }
}
