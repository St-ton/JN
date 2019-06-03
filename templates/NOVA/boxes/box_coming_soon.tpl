{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='boxes-box-coming-soon'}
    {lang key='upcomingProducts' assign='slidertitle'}
    {assign var=moreLink value=$oBox->getURL()}
    {lang key='showAllUpcomingProducts' assign='moreTitle'}
    {block name='boxes-box-coming-soon-include-product-slider'}
        {include file='snippets/product_slider.tpl'
            id='boxslider-comingsoon'
            productlist=$oBox->getProducts()->elemente
            title=$slidertitle
            tplscope='box'
            moreLink=$moreLink
            moreTitle=$moreTitle
        }
    {/block}
{/block}
