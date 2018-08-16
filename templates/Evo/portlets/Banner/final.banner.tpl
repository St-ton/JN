{if isset($oBanner)}
    <div class="banner">
        {block name='banner-map'}
            <img {$attribString} src="{$srcString}"/>
            {foreach from=$oBanner->oArea_arr item=oImageMapArea}
                {strip}
                    <a href="{$oImageMapArea->cUrl}" class="area {$oImageMapArea->cStyle}" style="left:{math equation="100/bWidth*posX" bWidth=$oBanner->fWidth posX=$oImageMapArea->oCoords->x}%;top:{math equation="100/bHeight*posY" bHeight=$oBanner->fHeight posY=$oImageMapArea->oCoords->y}%;width:{math equation="100/bWidth*aWidth" bWidth=$oBanner->fWidth aWidth=$oImageMapArea->oCoords->w}%;height:{math equation="100/bHeight*aHeight" bHeight=$oBanner->fHeight aHeight=$oImageMapArea->oCoords->h}%"
                       title="{$oImageMapArea->cTitel|strip_tags|escape:'html'|escape:'quotes'}">
                        {if $oImageMapArea->oArtikel || $oImageMapArea->cBeschreibung|@strlen > 0}
                            {assign var="oArtikel" value=$oImageMapArea->oArtikel}
                            <div class="area-desc">
                                {if $oImageMapArea->oArtikel}
                                    <img src="{$oArtikel->cVorschaubild}" alt="{$oArtikel->cName|strip_tags|escape:'quotes'|truncate:60}" class="img-responsive center-block" />
                                {/if}
                                {if $oImageMapArea->oArtikel}
                                    {include file='productdetails/price.tpl' Artikel=$oArtikel tplscope='box'}
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
        {/block}
        {if isset($isFluid) && $isFluid == false}
            <hr>
        {/if}
    </div>
{/if}
