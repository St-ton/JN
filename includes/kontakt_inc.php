<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @return array
 * @deprecated since 5.0.0
 */
function gibFehlendeEingabenKontaktformular()
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return FormHelper::getMissingContactFormData();
}

/**
 * @return stdClass
 * @deprecated since 5.0.0
 */
function baueKontaktFormularVorgaben()
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return FormHelper::baueKontaktFormularVorgaben();
}

/**
 * @return bool
 * @deprecated since 5.0.0
 */
function pruefeBetreffVorhanden()
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return FormHelper::checkSubject();
}

/**
 * @return int|bool
 * @deprecated since 5.0.0
 */
function bearbeiteNachricht()
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return FormHelper::editMessage();
}

/**
 * @param int $min
 * @return bool
 * @deprecated since 5.0.0
 */
function floodSchutz($min)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return FormHelper::checkFloodProtection($min);
}

if (!function_exists('baueFormularVorgaben')) {
    /**
     * @return stdClass
     * @deprecated since 5.0.0
     */
    function baueFormularVorgaben()
    {
        trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
        return FormHelper::baueKontaktFormularVorgaben();
    }
}
