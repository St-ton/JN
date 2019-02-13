{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}

<h1>{lang key='umfrage' section='umfrage'}</h1>

{include file='snippets/extension.tpl'}

{if $oUmfrage_arr|@count > 0}
    <div id="voting_overview">
        {block name='poll-overview'}
            {foreach $oUmfrage_arr as $oUmfrage}
                {card}
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
