{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
<p>{lang key='freeGiftFromOrderValue'}</p>
{if !empty($oArtikelGeschenk_arr)}
    {include file='snippets/opc_mount_point.tpl' id='opc_free_gift_prepend'}
    {row id="freegift"}
        {foreach $oArtikelGeschenk_arr as $oArtikelGeschenk}
            {col sm=6 md=4 class="text-center"}
                <label for="gift{$oArtikelGeschenk->kArtikel}">
                    {link href=$oArtikelGeschenk->cURLFull}{image src=$oArtikelGeschenk->Bilder[0]->cURLKlein alt=$oArtikelGeschenk->cName class="image"}{/link}
                    <p class="small text-muted">{lang key='freeGiftFrom1'} {$oArtikelGeschenk->cBestellwert} {lang key='freeGiftFrom2'}</p>
                    <p>{link href=$oArtikelGeschenk->cURLFull}{$oArtikelGeschenk->cName}{/link}</p>
                </label>
            {/col}
        {/foreach}
    {/row}
    {include file='snippets/opc_mount_point.tpl' id='opc_free_gift_append'}
{/if}
