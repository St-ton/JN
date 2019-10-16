{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='snippets-banner'}
    {if isset($oImageMap)}
        {opcMountPoint id='opc_before_banner'}
        <div class="banner mb-5">
            {block name='snippets-banner-map'}
                {image fluid=true lazy=true
                    src=$oImageMap->cBildPfad
                    alt=$oImageMap->cTitel
                }
                {foreach $oImageMap->oArea_arr as $oImageMapArea}
                    {strip}
                        {link href=$oImageMapArea->cUrl class="area {$oImageMapArea->cStyle}" style="left:{math equation="100/bWidth*posX" bWidth=$oImageMap->fWidth posX=$oImageMapArea->oCoords->x}%;top:{math equation="100/bHeight*posY" bHeight=$oImageMap->fHeight posY=$oImageMapArea->oCoords->y}%;width:{math equation="100/bWidth*aWidth" bWidth=$oImageMap->fWidth aWidth=$oImageMapArea->oCoords->w}%;height:{math equation="100/bHeight*aHeight" bHeight=$oImageMap->fHeight aHeight=$oImageMapArea->oCoords->h}%" title="{$oImageMapArea->cTitel|strip_tags|escape:'html'|escape:'quotes'}"}
                            {if $oImageMapArea->oArtikel || $oImageMapArea->cBeschreibung|@strlen > 0}
                                {assign var=oArtikel value=$oImageMapArea->oArtikel}
                                <div class="area-desc">
                                    <div class="text-center mb-3">
                                        {if $oImageMapArea->oArtikel}
                                            {image fluid=true webp=true
                                                src=$oArtikel->Bilder[0]->cURLMini
                                                srcset="{$Artikel->Bilder[0]->cURLMini} {$Einstellungen.bilder.bilder_artikel_mini_breite}w,
                                                    {$Artikel->Bilder[0]->cURLKlein} {$Einstellungen.bilder.bilder_artikel_klein_breite}w,
                                                    {$Artikel->Bilder[0]->cURLNormal} {$Einstellungen.bilder.bilder_artikel_normal_breite}w"
                                                alt=$oArtikel->cName|strip_tags|escape:'quotes'|truncate:60
                                                sizes="auto"
                                                class="mx-auto"
                                            }
                                        {/if}
                                    </div>
                                    {*{if $oImageMapArea->oArtikel}
                                        {include file='productdetails/price.tpl' Artikel=$oArtikel tplscope='box'}
                                    {/if}*}
                                    {if $oImageMapArea->cBeschreibung|@strlen > 0}
                                        <p>
                                            {$oImageMapArea->cBeschreibung}
                                        </p>
                                    {/if}
                                </div>
                            {/if}
                        {/link}
                    {/strip}
                {/foreach}
            {/block}
        </div>
    {/if}
{/block}
