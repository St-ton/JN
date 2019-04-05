{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='boxes-box-bestseller'}
    {lang key='showAllBestsellers' assign='moreTitle'}
    {lang key='bestsellers' assign='slidertitle'}
    {block name='boxes-box-bestseller-include-product-slider'}
        {include file='snippets/product_slider.tpl'
            id='boxslider-bestsellers'
            productlist=$oBox->getProducts()->elemente
            title=$slidertitle
            tplscope='box'
            moreLink=$oBox->getURL()
            moreTitle=$moreTitle
        }
    {/block}
{/block}
