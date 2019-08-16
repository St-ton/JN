{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='boxes-box-news-categories'}
    {card class="box box-newscategories mb-7" id="sidebox{$oBox->getID()}" title="{lang key='newsBoxCatOverview'}"}
        {block name='boxes-box-news-categories-content'}
            {nav vertical=true}
                {foreach $oBox->getItems() as $newsCategory}
                    {if $newsCategory@index === 10}{break}{/if}
                    {navitem href=$newsCategory->cURLFull title=$newsCategory->cName}
                        {$newsCategory->cName} <span class="badge badge-light float-right">{$newsCategory->nAnzahlNews}</span>
                    {/navitem}
                {/foreach}
            {/nav}
        {/block}
    {/card}
{/block}
