{if $oBox->show()}
    {lang key='upcomingProducts' assign='slidertitle'}
    {assign var='moreLink' value=$oBox->getURL()}
    {lang key='showAllUpcomingProducts' assign='moreTitle'}
    {include file='snippets/product_slider.tpl'
        id='boxslider-comingsoon'
        productlist=$oBox->getProducts()->elemente
        title=$slidertitle
        tplscope='box'
        moreLink=$moreLink
        moreTitle=$moreTitle}
{/if}