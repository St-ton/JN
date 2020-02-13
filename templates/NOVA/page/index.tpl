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
                        {container fluid=true}
                            {include file='snippets/product_slider.tpl' productlist=$Box->Artikel->elemente title=$title hideOverlays=true moreLink=$moreLink moreTitle=$moreTitle}
                        {/container}
                    {/block}
                {/if}
            {/foreach}
        {/block}
    {/if}

    {block name='page-index-additional-content'}
        {if isset($oNews_arr) && $oNews_arr|@count > 0}

            {opcMountPoint id='opc_before_news'}

            <section>
                {container}
                    {block name='page-index-subheading-news'}
                        <div class="hr-sect h2 mb-5">
                            {link href="{get_static_route id='news.php'}"}{lang key='news' section='news'}{/link}
                        </div>
                    {/block}
                    {block name='page-index-news'}
                        <div itemprop="about"
                             itemscope=true
                             itemtype="http://schema.org/Blog"
                             class="slick-smooth-loading carousel carousel-arrows-inside slick-lazy slick-type-news"
                             data-slick-type="news-slider">
                            {include file='snippets/slider_items.tpl' items=$oNews_arr type='news'}
                        </div>
                    {/block}
                {/container}
            </section>
        {/if}
    {/block}
{/block}
