{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}

<p class="box_info">{lang key="freeGiftFromOrderValue" section="global"}</p>
{if !empty($oArtikelGeschenk_arr)}
    <div id="freegift" class="row row-eq-height">
        {foreach name=gratisgeschenke from=$oArtikelGeschenk_arr item=oArtikelGeschenk}
            <div class="col-sm-6 col-md-4 text-center">
                <label class="thumbnail" for="gift{$oArtikelGeschenk->kArtikel}">
                    <a href="{$oArtikelGeschenk->cURLFull}"><img src="{$oArtikelGeschenk->Bilder[0]->cURLKlein}" class="image" /></a>
                    <p class="small text-muted">{lang key='freeGiftFrom1'} {$oArtikelGeschenk->cBestellwert} {lang key='freeGiftFrom2'}</p>
                    <p><a href="{$oArtikelGeschenk->cURLFull}">{$oArtikelGeschenk->cName}</a></p>
                </label>
            </div>
        {/foreach}
    </div>
{/if}