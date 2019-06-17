{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='boxes-box-poll'}
    {card class="box box-poll mb-7" id="sidebox{$oBox->getID()}" title="{lang key='BoxPoll'}"}
        {block name='boxes-box-poll-content'}
            {nav class="tree" vertical=true}
                {foreach $oBox->getItems() as $oUmfrageItem}
                    {block name='boxes-box-poll-item'}
                        {navitem href=$oUmfrageItem->cURLFull}
                            {$oUmfrageItem->cName}
                        {/navitem}
                    {/block}
                {/foreach}
            {/nav}
        {/block}
    {/card}
{/block}