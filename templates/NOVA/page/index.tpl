{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='page-index'}
    {block name='page-index-include-selection-wizard'}
        {include file='selectionwizard/index.tpl'}
    {/block}

    {if isset($StartseiteBoxen) && $StartseiteBoxen|@count > 0}
        {assign var=moreLink value=null}
        {assign var=moreTitle value=null}

        {opcMountPoint id='opc_before_boxes'}

        {block name='page-index-boxes'}
            {foreach $StartseiteBoxen as $Box}
                {if isset($Box->Artikel->elemente) && count($Box->Artikel->elemente) > 0 && isset($Box->cURL)}
                    {if $Box->name === 'TopAngebot'}
                        {lang key='topOffer' assign='title'}
                        {lang key='showAllTopOffers' assign='moreTitle'}
                    {elseif $Box->name === 'Sonderangebote'}
                        {lang key='specialOffer' assign='title'}
                        {lang key='showAllSpecialOffers' assign='moreTitle'}
                    {elseif $Box->name === 'NeuImSortiment'}
                        {lang key='newProducts' assign='title'}
                        {lang key='showAllNewProducts'  assign='moreTitle'}
                    {elseif $Box->name === 'Bestseller'}
                        {lang key='bestsellers' assign='title'}
                        {lang key='showAllBestsellers' assign='moreTitle'}
                    {/if}
                    {assign var=moreLink value=$Box->cURL}
                    {block name='page-index-include-product-slider'}
                        {include file='snippets/product_slider.tpl' productlist=$Box->Artikel->elemente title=$title hideOverlays=true moreLink=$moreLink moreTitle=$moreTitle}
                    {/block}
                {/if}
            {/foreach}
        {/block}
    {/if}

    {block name='page-index-additional-content'}
        {if isset($oNews_arr) && $oNews_arr|@count > 0}
            <hr>

            {opcMountPoint id='opc_before_news'}

            {container}
                {block name='page-index-subheading-news'}
                    <div class="h2">{lang key='news' section='news'}</div>
                {/block}
                {block name='page-index-news'}
                    {row itemprop="about" itemscope=true itemtype="http://schema.org/Blog" class="news-slider mx-0"}
                        {foreach $oNews_arr as $newsItem}
                            {col}
                                {block name='page-index-include-preview'}
                                    {include file='blog/preview.tpl'}
                                {/block}
                            {/col}
                        {/foreach}
                    {/row}
                {/block}
            {/container}
        {/if}
    {/block}
{/block}
