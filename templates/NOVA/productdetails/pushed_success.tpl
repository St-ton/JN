{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{alert id="pushed-success" variant="light" dismissible=true}
    {if isset($zuletztInWarenkorbGelegterArtikel)}
        {assign var=pushedArtikel value=$zuletztInWarenkorbGelegterArtikel}
    {else}
        {assign var=pushedArtikel value=$Artikel}
    {/if}
    {assign var=showXSellingCart value=isset($Xselling->Kauf) && count($Xselling->Kauf->Artikel) > 0}
    {if isset($cartNote)}
        <div class="h4 alert-heading bg-success text-success mb-3">{$cartNote}</div>
    {/if}
    {row}
        {col cols=12 md="{if $showXSellingCart}6{else}12{/if}" class="text-center mb-3"}
            {block name='pushed-success-product-cell'}
                <div class="h5 text-center">{$pushedArtikel->cName}</div>
                <div class="product-cell text-center{if isset($class)} {$class}{/if}">
                    {row}
                        {col cols=4 offset=1}
                            {counter assign=imgcounter print=0}
                            {image src=$pushedArtikel->Bilder[0]->cURLNormal
                                 alt="{if isset($pushedArtikel->Bilder[0]->cAltAttribut)}{$pushedArtikel->Bilder[0]->cAltAttribut|strip_tags|truncate:60|escape:'html'}{else}{$pushedArtikel->cName}{/if}"
                                 id="image{$pushedArtikel->kArtikel}_{$imgcounter}"
                                 class="image mb-3" fluid=true}
                        {/col}
                        {col cols=7 class="text-left"}
                            <dl>
                                {foreach $pushedArtikel->oMerkmale_arr as $oMerkmal}
                                    <dt>{$oMerkmal->cName}:</dt>
                                    <dd class="attr-characteristic">
                                        {strip}
                                            {foreach $oMerkmal->oMerkmalWert_arr as $oMerkmalWert}
                                                <span class="value ml-2">
                                                    {if $oMerkmal->cTyp === 'TEXT' || $oMerkmal->cTyp === 'SELECTBOX' || $oMerkmal->cTyp === ''}
                                                        {$oMerkmalWert->cWert|escape:'html'}
                                                    {else}
                                                        {if $oMerkmalWert->cBildpfadKlein !== 'gfx/keinBild_kl.gif'}
                                                            <span data-toggle="tooltip" data-placement="top" title="{$oMerkmalWert->cWert|escape:'html'}">
                                                                {image src=$oMerkmalWert->cBildURLKlein title=$oMerkmalWert->cWert|escape:'html' alt=$oMerkmalWert->cWert|escape:'html'}
                                                            </span>
                                                        {else}
                                                            {$oMerkmalWert->cWert|escape:'html'}
                                                        {/if}
                                                    {/if}
                                                </span>
                                            {/foreach}
                                        {/strip}
                                    </dd>

                                {/foreach}
                            </dl>
                        {/col}
                    {/row}
                </div>
            {/block}

            {link href="{get_static_route id='warenkorb.php'}" class="btn btn-secondary btn-basket"}<i class="fas fa-shopping-cart"></i> {lang key='gotoBasket'}{/link}
            {link href=$pushedArtikel->cURLFull class="btn btn-primary" data=["dismiss"=>"alert"] aria=["label"=>"Close"]}<i class="fa fa-arrow-circle-right"></i> {lang key='continueShopping' section='checkout'}{/link}

        {/col}
        {block name='pushed-success-x-sell'}
            {if $showXSellingCart}
                {col cols=6 class="d-none d-md-block border-left"}
                    <div class="h5 text-center">{lang key='customerWhoBoughtXBoughtAlsoY' section='productDetails'}</div>
                    {include file='snippets/product_slider.tpl' id='' productlist=$Xselling->Kauf->Artikel title=''}
                {/col}
            {/if}
        {/block}
    {/row}
{/alert}
