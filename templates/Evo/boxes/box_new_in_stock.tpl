{lang key='newProducts' assign='slidertitle'}
{assign var='moreLink' value=$oBox->getURL()}
{lang key='showAllNewProducts' assign='moreTitle'}
{include file='snippets/product_slider.tpl'
    id='boxslider-newproducts'
    productlist=$oBox->getProducts()->elemente
    title=$slidertitle
    tplscope='box'
    moreLink=$moreLink
    moreTitle=$moreTitle}
