{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if $oBox->show()}
    {lang key='topReviews' assign='slidertitle'}
    {assign var='moreLink' value=$oBox->getURL()}
    {lang key='topReviews' assign='moreTitle'}
    {include file='snippets/product_slider.tpl'
        id='boxslider-toprated'
        productlist=$oBox->getProducts()
        title=$slidertitle
        tplscope='box'
        moreLink=$moreLink
        moreTitle=$moreTitle}
{/if}
