{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='boxes-box-custom-empty'}
    {card class="box box-custom mb-7" id="sidebox{$oBox->getID()}"}
        {block name='boxes-box-custom-empty-content'}
            {eval var=$oBox->getContent()}
        {/block}
    {/card}
{/block}
