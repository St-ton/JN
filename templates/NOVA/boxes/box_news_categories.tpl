{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='boxes-box-news-categories'}
    {card class="box box-newscategories mb-4" id="sidebox{$oBox->getID()}"}
        {block name='boxes-box-news-categories-content'}
            {block name='boxes-box-news-categories-title'}
                <div class="productlist-filter-headline">
                    <span>{lang key='newsBoxCatOverview'}</span>
                </div>
            {/block}
            {nav vertical=true}
                {foreach $oBox->getItems() as $newsCategory}
                    {if $newsCategory@index === 10}{break}{/if}
                    {navitem href=$newsCategory->cURLFull title=$newsCategory->cName}
                        <span class="align-items-center d-flex">
                            {$newsCategory->cName}
                            <span class="badge badge-outline-secondary ml-auto">{$newsCategory->nAnzahlNews}</span>
                        </span>
                    {/navitem}
                {/foreach}
            {/nav}
        {/block}
    {/card}
{/block}
