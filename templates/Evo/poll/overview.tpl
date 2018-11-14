{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}

<h1>{lang key='umfrage' section='umfrage'}</h1>

{if !empty($hinweis)}
    <div class="alert aler-info">{$hinweis}</div>
{/if}
{if !empty($fehler)}
    <div class="alert alert-danger">{$fehler}</div>
{/if}

{include file='snippets/extension.tpl'}

{if $oUmfrage_arr|@count > 0}
    <div id="voting_overview">
        {block name='poll-overview'}
            {foreach $oUmfrage_arr as $oUmfrage}
                <h3 class="survey-title{if $oUmfrage@first} nospacing{/if}">
                    <a href="{$ShopURL}/{$oUmfrage->getURL()}">{$oUmfrage->getName()}</a>
                </h3>
                <p><small>{$oUmfrage->getValidFromFormatted()} | {$oUmfrage->getQuestionCount()} {if $oUmfrage->getQuestionCount() === 1}{lang key='umfrageQ' section='umfrage'}{else}{lang key='umfrageQs' section='umfrage'}{/if}</small></p>
                <p>{$oUmfrage->getDescription()}</p>
            {/foreach}
        {/block}
    </div>
{/if}
