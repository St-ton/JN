{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='poll-overview'}
    {block name='poll-overview-heading'}
        {opcMountPoint id='opc_before_heading'}
        <h1>{lang key='umfrage' section='umfrage'}</h1>
    {/block}

    {block name='poll-overview-include-extension'}
        {include file='snippets/extension.tpl'}
    {/block}

    {if $oUmfrage_arr|@count > 0}
        {opcMountPoint id='opc_before_overview'}
        <div id="voting_overview">
            {block name='poll-overview-content'}
                {foreach $oUmfrage_arr as $oUmfrage}
                    {card class="mb-3"}
                        <div class="h3 survey-title{if $oUmfrage@first} nospacing{/if}">
                            {link href="{$ShopURL}/{$oUmfrage->getURL()}"}{$oUmfrage->getName()}{/link}
                        </div>
                        <p>
                            <small>
                                {$oUmfrage->getValidFromFormatted()} | {$oUmfrage->getQuestionCount()}
                                {if $oUmfrage->getQuestionCount() === 1}
                                    {lang key='umfrageQ' section='umfrage'}
                                {else}
                                    {lang key='umfrageQs' section='umfrage'}
                                {/if}
                            </small>
                        </p>
                        <hr>
                        <p>{$oUmfrage->getDescription()}</p>
                    {/card}
                {/foreach}
            {/block}
        </div>
    {/if}
{/block}
