{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}

<p class="box_info">{lang key='freeGiftFromOrderValue'}</p>
{if !empty($oArtikelGeschenk_arr)}
    {include file='snippets/opc_mount_point.tpl' id='opc_free_gift_prepend'}
    <div id="freegift" class="row row-eq-height">
        {foreach $oArtikelGeschenk_arr as $oArtikelGeschenk}
            <div class="col-sm-6 col-md-4 text-center">
                <label class="thumbnail" for="gift{$oArtikelGeschenk->kArtikel}">
                    <a href="{$oArtikelGeschenk->cURLFull}"><img src="{$oArtikelGeschenk->Bilder[0]->cURLKlein}" class="image" /></a>
                    <p class="small text-muted">{lang key='freeGiftFrom1'} {$oArtikelGeschenk->cBestellwert} {lang key='freeGiftFrom2'}</p>
                    <p><a href="{$oArtikelGeschenk->cURLFull}">{$oArtikelGeschenk->cName}</a></p>
                </label>
            </div>
        {/foreach}
    </div>
    {include file='snippets/opc_mount_point.tpl' id='opc_free_gift_append'}
{/if}