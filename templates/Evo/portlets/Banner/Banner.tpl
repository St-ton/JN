{assign var=imgAttribs
        value=$instance->getImageAttributes(null, null, null, 1, $portlet->getPlaceholderImgUrl())
}

{if $isPreview}
    <div style="text-align: center;" {$instance->getAttributeString()} {$instance->getDataAttributeString()} >
        {image
            src=$imgAttribs.src
            srcset=$imgAttribs.srcset
            sizes=$imgAttribs.srcsizes
            alt=$imgAttribs.alt
            style='width: 98%; filter: grayscale(50%) opacity(60%)'
            title=$imgAttribs.title
            fluid=true
        }
        <p style="color: #5cbcf6; font-size: 40px; font-weight: bold; margin-top: -56px;">Banner</p>
    </div>
{else}
    <div {$instance->getAttributeString()}>
        {image
            src=$imgAttribs.src
            srcset=$imgAttribs.srcset
            sizes=$imgAttribs.srcsizes
            alt=$imgAttribs.alt
            style='width: 100%'
            title=$imgAttribs.title
            fluid=true
        }
        {assign var=oBanner value=$portlet->getImageMap($instance)}
        {foreach $oBanner->oArea_arr as $oImageMapArea}
            {strip}
                <a href="{$oImageMapArea->cUrl}" class="area {$oImageMapArea->cStyle}"
                   style="left:{math equation="100/bWidth*posX"
                            bWidth=$oBanner->fWidth
                            posX=$oImageMapArea->oCoords->x}%;
                       top:{math equation="100/bHeight*posY"
                            bHeight=$oBanner->fHeight
                            posY=$oImageMapArea->oCoords->y}%;
                       width:{math equation="100/bWidth*aWidth"
                            bWidth=$oBanner->fWidth
                            aWidth=$oImageMapArea->oCoords->w}%;
                       height:{math equation="100/bHeight*aHeight"
                            bHeight=$oBanner->fHeight
                            aHeight=$oImageMapArea->oCoords->h}%"
                   title="{$oImageMapArea->cTitel|strip_tags|escape:'html'|escape:'quotes'}">
                    {if $oImageMapArea->oArtikel || $oImageMapArea->cBeschreibung|@strlen > 0}
                        {assign var="oArtikel" value=$oImageMapArea->oArtikel}
                        <div class="area-desc">
                            {if $oImageMapArea->oArtikel}
                                {image
                                    src=$oArtikel->cVorschaubild
                                    alt=$oArtikel->cName|strip_tags|escape:'quotes'|truncate:60
                                    style='display: block; margin-left: auto; margin-right: auto'
                                    fluid=true}
                            {/if}
                            {if $oImageMapArea->oArtikel}
                                {include file='productdetails/price.tpl' Artikel=$oArtikel tplscope="box"}
                            {/if}
                            {if $oImageMapArea->cBeschreibung|@strlen > 0}
                                <p>
                                    {$oImageMapArea->cBeschreibung}
                                </p>
                            {/if}
                        </div>
                    {/if}
                </a>
            {/strip}
        {/foreach}
        <hr>
    </div>
{/if}