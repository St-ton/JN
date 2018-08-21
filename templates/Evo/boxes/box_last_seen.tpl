{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if $oBox->show()}
    {lang key='lastViewed' assign='slidertitle'}
    {include file='snippets/product_slider.tpl'
        id='boxslider-recently-viewed'
        productlist=$oBox->getProducts()
        title=$slidertitle
        tplscope='box'
        hideOverlays=true}
{/if}
