{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='boxes-box-custom'}
    {card class="box box-custom mb-7" id="sidebox{$oBox->getID()}" title=$oBox->getTitle()}
        {eval var=$oBox->getContent()}
    {/card}
{/block}
