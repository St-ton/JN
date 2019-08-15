{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='page-freegift'}
    {opcMountPoint id='opc_before_free_gift'}
    <p>{lang key='freeGiftFromOrderValue'}</p>
    {if !empty($oArtikelGeschenk_arr)}
        {opcMountPoint id='opc_before_free_gift_list'}
        {row id="freegift"}
            {block name='page-freegift-freegifts'}
                {foreach $oArtikelGeschenk_arr as $oArtikelGeschenk}
                    {col sm=6 md=4 class="text-center"}
                        <label for="gift{$oArtikelGeschenk->kArtikel}">
                            {link href=$oArtikelGeschenk->cURLFull}{image src=$oArtikelGeschenk->Bilder[0]->cURLKlein alt=$oArtikelGeschenk->cName class="image"}{/link}
                            <p class="small text-muted">{lang key='freeGiftFrom1'} {$oArtikelGeschenk->cBestellwert} {lang key='freeGiftFrom2'}</p>
                            <p>{link href=$oArtikelGeschenk->cURLFull}{$oArtikelGeschenk->cName}{/link}</p>
                        </label>
                    {/col}
                {/foreach}
            {/block}
        {/row}
    {/if}
{/block}
