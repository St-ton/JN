{if $oBox->show()}
    {lang key='topOffer' assign='slidertitle'}
    {assign var='moreLink' value=$oBox->getURL()}
    {lang key='showAllTopOffers' assign='moreTitle'}
    {include file='snippets/product_slider.tpl'
        id='boxslider-topoffer'
        productlist=$oBox->getProducts()->elemente
        title=$slidertitle
        tplscope='box'
        moreLink=$moreLink
        moreTitle=$moreTitle}
{/if}