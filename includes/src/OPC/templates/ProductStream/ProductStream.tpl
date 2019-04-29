{$style = $instance->getProperty('listStyle')}

{if $isPreview}
    <div {$instance->getAttributeString()} {$instance->getDataAttributeString()}>
        {image alt='' src=$portlet->getTemplateUrl()|cat:'preview.'|cat:$style|cat:'.png'
            style='width: 98%; filter: grayscale(50%) opacity(60%)'}
        <div style="color:#5cbcf6;font-size:40px;font-weight:bold;margin-top:-1em;line-height:1em;text-align:center;">
            Produktliste
        </div>
    </div>
{else}
    {$productlist = $portlet->getFilteredProducts($instance)}

    {if $style === 'slider'}
        {include file="./final.slider.tpl"}
    {elseif $style === 'vertSlider'}
        {$title = $instance->getProperty('sliderTitle')}

        {card no-body=true bg-variant='default' class='panel-slider box box-slider' id=$id|default:null
                style=$instance->getStyleString()}
            {if !empty($title)}
                {cardheader}
                    <h5 class="panel-title card-title">{$title}</h5>
                {/cardheader}
            {/if}
            {cardbody}
                <div class="evo-box-vertical" style="text-align:center">
                    {foreach $productlist as $product}
                        <div class="product-wrapper {if isset($style)}{$style}{/if}"
                             {if isset($Link) && $Link->getLinkType() == $smarty.const.LINKTYP_STARTSEITE}
                                 itemprop="about"
                             {else}
                                 itemprop="isRelatedTo"
                             {/if}
                             itemscope itemtype="http://schema.org/Product">
                            {include file='productlist/item_slider.tpl' Artikel=$product tplscope='box' class=''}
                        </div>
                    {/foreach}
                </div>
            {/cardbody}
        {/card}
        <script>
            function startSlider() {
                console.log('load.opc');
                $('.evo-box-vertical:not(.slick-initialized)').slick({
                    //dots: true,
                    arrows: true,
                    lazyLoad: 'ondemand',
                    slidesToShow: {$instance->getProperty('productCount')},
                });
            }
            $(window).on('load', startSlider);
            $(startSlider);
        </script>
    {else}
        {if $style === 'gallery'}
            {$gridLG = 4}
            {$gridXS = 6}
        {else}
            {$gridXS = 12}
        {/if}

        {if $style !== 'list'}
            {$class = 'row-eq-height row-eq-img-height'}
        {/if}

        {$class = $class|default:''|cat:' '|cat:$style}

        <div id="result-wrapper" style="{$instance->getStyleString()}">
            <div {$instance->getAttributeString()}>
                {row class=$class|default:null id='product-list'}
                    {foreach $portlet->getFilteredProducts($instance) as $Artikel}
                        {col cols=$gridXS lg=$gridLG|default:false class='product-wrapper'}
                            {if $style === 'list'}
                                {include file='productlist/item_list.tpl' tplscope=$style}
                            {else}
                                {include file='productlist/item_box.tpl' tplscope=$style class='thumbnail'}
                            {/if}
                        {/col}
                    {/foreach}
                {/row}
            </div>
        </div>
    {/if}
{/if}