{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{card class="box box-poll mb-7" id="sidebox{$oBox->getID()}" title="{lang key='BoxPoll'}"}
    <hr class="mt-0 mb-4">
    {nav class="tree" vertical=true}
        {foreach $oBox->getItems() as $oUmfrageItem}
            {navitem}
                {link href="{$oUmfrageItem->cURLFull}"}
                    {$oUmfrageItem->cName}
                {/link}
            {/navitem}
        {/foreach}
    {/nav}
{/card}
