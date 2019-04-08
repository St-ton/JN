{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='boxes-box-last-seen'}
    {lang key='lastViewed' assign='slidertitle'}
    {block name='boxes-box-last-seen-include-product-slider'}
        {include file='snippets/product_slider.tpl'
            id='boxslider-recently-viewed'
            productlist=$oBox->getProducts()
            title=$slidertitle
            tplscope='box'
            hideOverlays=true
        }
    {/block}
{/block}
