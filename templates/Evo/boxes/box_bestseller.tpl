{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if $oBox->show()}
    {lang key='showAllBestsellers' section='global' assign='moreTitle'}
    {lang key='bestsellers' section='global' assign='slidertitle'}
    {include file='snippets/product_slider.tpl'
        id='boxslider-bestsellers'
        productlist=$oBox->getProducts()->elemente
        title=$slidertitle
        tplscope='box'
        moreLink=$oBox->getURL()
        moreTitle=$moreTitle}
{/if}
