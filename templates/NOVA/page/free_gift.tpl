{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='page-freegift'}
    {container}
        {opcMountPoint id='opc_before_free_gift'}
        <p>{lang key='freeGiftFromOrderValue'}</p>
        {if !empty($oArtikelGeschenk_arr)}
            {opcMountPoint id='opc_before_free_gift_list'}
            {row id="freegift"}
                {block name='page-freegift-freegifts'}
                    {foreach $oArtikelGeschenk_arr as $oArtikelGeschenk}
                        {col sm=6 md=4 class="text-center"}
                            <label for="gift{$oArtikelGeschenk->kArtikel}">
                                {block name='page-freegift-freegift-image'}
                                    {link href=$oArtikelGeschenk->cURLFull}
                                        {image fluid=true webp=true lazy=true
                                            alt=$oArtikelGeschenk->cName
                                            src=$oArtikelGeschenk->Bilder[0]->cURLMini
                                            srcset="{$oArtikelGeschenk->Bilder[0]->cURLMini} {$Einstellungen.bilder.bilder_artikel_mini_breite}w,
                                                {$oArtikelGeschenk->Bilder[0]->cURLKlein} {$Einstellungen.bilder.bilder_artikel_klein_breite}w,
                                                {$oArtikelGeschenk->Bilder[0]->cURLNormal} {$Einstellungen.bilder.bilder_artikel_normal_breite}w,
                                                {$oArtikelGeschenk->Bilder[0]->cURLGross} {$Einstellungen.bilder.bilder_artikel_gross_breite}w"
                                            sizes="200px"
                                        }
                                    {/link}
                                {/block}
                                {block name='page-freegift-freegift-info'}
                                    <p class="small text-muted">{lang key='freeGiftFrom1'} {$oArtikelGeschenk->cBestellwert} {lang key='freeGiftFrom2'}</p>
                                {/block}
                                {block name='page-freegift-freegift-link'}
                                    <p>{link href=$oArtikelGeschenk->cURLFull}{$oArtikelGeschenk->cName}{/link}</p>
                                {/block}
                            </label>
                        {/col}
                    {/foreach}
                {/block}
            {/row}
        {/if}
    {/container}
{/block}
