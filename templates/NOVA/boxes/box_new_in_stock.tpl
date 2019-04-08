{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='boxes-box-new-in-stock'}
    {lang key='newProducts' assign='slidertitle'}
    {assign var=moreLink value=$oBox->getURL()}
    {lang key='showAllNewProducts' assign='moreTitle'}
    {block name='boxes-box-new-in-stock-include-product-slider'}
        {include file='snippets/product_slider.tpl'
            id='boxslider-newproducts'
            productlist=$oBox->getProducts()->elemente
            title=$slidertitle
            tplscope='box'
            moreLink=$moreLink
            moreTitle=$moreTitle
        }
    {/block}
{/block}
