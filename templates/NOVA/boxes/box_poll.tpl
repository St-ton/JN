{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='boxes-box-poll'}
    {card class="box box-poll mb-4" id="sidebox{$oBox->getID()}"}
        {block name='boxes-box-poll-content'}
            {block name='boxes-box-poll-title'}
                <div class="productlist-filter-headline">
                    <span>{lang key='BoxPoll'}</span>
                </div>
            {/block}
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