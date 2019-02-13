{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{card class="box box-custom mb-7" id="sidebox{$oBox->getID()}" title="{$oBox->getTitle()}"}
    <hr class="mt-0 mb-4">
    {eval var=$oBox->getContent()}
{/card}
