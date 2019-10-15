{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='comparelist-index'}
    {block name='comparelist-index-include-header'}
        {include file='layout/header.tpl'}
    {/block}

    {assign var='descriptionLength' value=200}

    {block name='comparelist-index-content'}
        {block name='comparelist-index-heading'}
            {opcMountPoint id='opc_before_heading'}
            {container}
                <h1 class="h2">{lang key='compare' section='global'}</h1>
            {/container}
        {/block}
        {block name='comparelist-index-include-extension'}
            {include file='snippets/extension.tpl'}
        {/block}

        {if $oVergleichsliste->oArtikel_arr|@count > 0}
            {block name='comparelist-index-filter'}
                {opcMountPoint id='opc_before_filter'}
                {container}
                    <hr class="mt-0 mb-3">
                    <div id="filter-checkboxes" class="mb-4">
                        {row}
                            {col}
                                {buttongroup}
                                    {button
                                        variant="outline-secondary"
                                        role="button"
                                        data=["toggle"=> "collapse", "target"=>"#collapse-checkboxes"]
                                    }
                                        {lang key='filter'}
                                    {/button}
                                    {button variant="outline-secondary" id="check-all"}
                                        {lang key='showAll'}
                                    {/button}
                                    {button variant="outline-secondary" id="check-none"}
                                        {lang key='showNone'}
                                    {/button}
                                {/buttongroup}
                            {/col}
                        {/row}
                        {collapse id="collapse-checkboxes" visible=false class="pt-3"}
                            {row}
                                {foreach $prioRows as $row}
                                    {if $row['key'] !== 'Merkmale' && $row['key'] !== 'Variationen'}
                                        {col cols=6 md=4 lg=3 xl=2 class="my-2"}
                                            {checkbox checked=true data=['id' => $row['key']] class='comparelist-checkbox'}
                                                {$row['name']}
                                            {/checkbox}
                                        {/col}
                                    {/if}
                                    {if $row['key'] === 'Merkmale'}
                                        {foreach $oMerkmale_arr as $oMerkmale}
                                            {col cols=6 md=4 lg=3 xl=2 class="my-2"}
                                                {checkbox checked=true data=['id' => "attr-{$oMerkmale->cName}"] class='comparelist-checkbox'}
                                                    {$oMerkmale->cName}
                                                {/checkbox}
                                            {/col}
                                        {/foreach}
                                    {/if}
                                    {if $row['key'] === 'Variationen'}
                                        {foreach $oVariationen_arr as $oVariationen}
                                            {col cols=6 md=4 lg=3 xl=2 class="my-2"}
                                                {checkbox checked=true data=['id' => "vari-{$oVariationen->cName}"] class='comparelist-checkbox'}
                                                    {$oVariationen->cName}
                                                {/checkbox}
                                            {/col}
                                        {/foreach}
                                    {/if}
                                {/foreach}
                            {/row}
                        {/collapse}
                    </div>
                {/container}
            {/block}
            {block name='comparelist-index-products'}
                {container}
                    <div class="comparelist table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                            <tr>
                                <th class="sticky-top">&nbsp;</th>
                                {foreach $oVergleichsliste->oArtikel_arr as $oArtikel}
                                    <th class="text-center sticky-top equal-height min-w">
                                        <div class="stretched">
                                            <div>
                                                <div class="text-right">
                                                    {link href=$oArtikel->cURLDEL
                                                        class="text-decoration-none"
                                                        title="{lang key='removeFromCompareList' section='comparelist'}"
                                                        aria=["label"=>"{lang key='removeFromCompareList' section='comparelist'}"]
                                                        data=["toggle"=>"tooltip"]}
                                                        <i class="fas fa-times"></i>
                                                    {/link}
                                                </div>
                                                {link href=$oArtikel->cURLFull}
                                                    {image src=$oArtikel->cVorschaubild alt=$oArtikel->cName class="image"}
                                                {/link}
                                            </div>
                                            <span>
                                                {link href=$oArtikel->cURLFull}{$oArtikel->cName}{/link}
                                            </span>
                                            {block name='comparelist-index-include-rating'}
                                                {include file='productdetails/rating.tpl' stars=$oArtikel->fDurchschnittsBewertung}
                                            {/block}
                                            {if $oArtikel->getOption('nShowOnlyOnSEORequest', 0) === 1}
                                                <p>{lang key='productOutOfStock' section='productDetails'}</p>
                                            {elseif $oArtikel->Preise->fVKNetto == 0 && $Einstellungen.global.global_preis0 === 'N'}
                                                <p>{lang key='priceOnApplication' section='global'}</p>
                                            {else}
                                                {block name='comparelist-index-include-price'}
                                                    {include file='productdetails/price.tpl' Artikel=$oArtikel tplscope='detail'}
                                                {/block}
                                            {/if}
                                        </div>
                                    </th>
                                {/foreach}
                            </tr>
                            </thead>
                            {foreach $prioRows as $row}
                                {if $row['key'] !== 'Merkmale' && $row['key'] !== 'Variationen'}
                                    <tr class="comparelist-row" data-id="row-{$row['key']}">
                                    <td>
                                        <b>{$row['name']}</b>
                                    </td>
                                    {block name='comparelist-index-products'}
                                        {foreach $oVergleichsliste->oArtikel_arr as $oArtikel}
                                            {if $row['key'] === 'verfuegbarkeit'}
                                                <td>
                                                    {block name='comparelist-index-products-includes-stock-availability'}
                                                        {include file='productdetails/stock.tpl' Artikel=$oArtikel availability=true}
                                                    {/block}
                                                    {if $oArtikel->nErscheinendesProdukt}
                                                        <div>
                                                            {lang key='productAvailableFrom' section='global'}: <strong>{$oArtikel->Erscheinungsdatum_de}</strong>
                                                            {if $Einstellungen.global.global_erscheinende_kaeuflich === 'Y' && $oArtikel->inWarenkorbLegbar == 1}
                                                                ({lang key='preorderPossible' section='global'})
                                                            {/if}
                                                        </div>
                                                    {/if}
                                                </td>
                                            {elseif $row['key'] === 'lieferzeit'}
                                                <td>
                                                    {block name='comparelist-index-products-includes-stock-shipping-time'}
                                                        {include file='productdetails/stock.tpl' Artikel=$oArtikel shippingTime=true}
                                                    {/block}
                                                </td>
                                            {elseif $oArtikel->$row['key'] !== ''}
                                                <td style="min-width: {$Einstellungen_Vergleichsliste.vergleichsliste.vergleichsliste_spaltengroesse}px">
                                                    {if $row['key'] === 'fArtikelgewicht' || $row['key'] === 'fGewicht'}
                                                        {$oArtikel->$row['key']} {lang key='weightUnit' section='comparelist'}
                                                    {elseif $row['key'] === 'cBeschreibung'}
                                                        {if $oArtikel->$row['key']|strlen < $descriptionLength}
                                                            {$oArtikel->$row['key']}
                                                        {else}
                                                            <div>
                                                                <span>
                                                                    {$oArtikel->$row['key']|substr:0:$descriptionLength}
                                                                </span>
                                                                {collapse tag='span' id="read-more-{$oArtikel->kArtikel}"}
                                                                    {$oArtikel->$row['key']|substr:$descriptionLength}
                                                                {/collapse}
                                                            </div>
                                                            {button variant='link' data=['toggle' => 'collapse', 'target' => "#read-more-{$oArtikel->kArtikel}"]}
                                                                {lang key='more'}
                                                            {/button}
                                                        {/if}
                                                    {/if}
                                                </td>
                                            {else}
                                                <td>--</td>
                                            {/if}
                                        {/foreach}
                                    {/block}
                                    </tr>
                                {/if}
                                {if $row['key'] === 'Merkmale'}
                                    {block name='comparelist-index-characteristics'}
                                        {foreach $oMerkmale_arr as $oMerkmale}
                                            <tr class="comparelist-row" data-id="row-attr-{$oMerkmale->cName}">
                                                <td>
                                                    <b>{$oMerkmale->cName}</b>
                                                </td>
                                                {foreach $oVergleichsliste->oArtikel_arr as $oArtikel}
                                                    <td style="min-width: {$Einstellungen_Vergleichsliste.vergleichsliste.vergleichsliste_spaltengroesse}px">
                                                        {if count($oArtikel->oMerkmale_arr) > 0}
                                                            {foreach $oArtikel->oMerkmale_arr as $oMerkmaleArtikel}
                                                                {if $oMerkmale->cName == $oMerkmaleArtikel->cName}
                                                                    {foreach $oMerkmaleArtikel->oMerkmalWert_arr as $oMerkmalWert}
                                                                        {$oMerkmalWert->cWert}{if !$oMerkmalWert@last}, {/if}
                                                                    {/foreach}
                                                                {/if}
                                                            {/foreach}
                                                        {else}
                                                            --
                                                        {/if}
                                                    </td>
                                                {/foreach}
                                            </tr>
                                        {/foreach}
                                    {/block}
                                {/if}
                                {if $row['key'] === 'Variationen'}
                                    {block name='comparelist-index-variations'}
                                        {foreach $oVariationen_arr as $oVariationen}
                                            <tr class="comparelist-row" data-id="row-vari-{$oVariationen->cName}">
                                                <td>
                                                    <b>{$oVariationen->cName}</b>
                                                </td>
                                                {foreach $oVergleichsliste->oArtikel_arr as $oArtikel}
                                                    <td>
                                                        {if isset($oArtikel->oVariationenNurKind_arr) && $oArtikel->oVariationenNurKind_arr|@count > 0}
                                                            {foreach $oArtikel->oVariationenNurKind_arr as $oVariationenArtikel}
                                                                {if $oVariationen->cName == $oVariationenArtikel->cName}
                                                                    {foreach $oVariationenArtikel->Werte as $oVariationsWerte}
                                                                        {$oVariationsWerte->cName}
                                                                        {if $oArtikel->nVariationOhneFreifeldAnzahl == 1 && ($oArtikel->kVaterArtikel > 0 || $oArtikel->nIstVater == 1)}
                                                                            {assign var=kEigenschaftWert value=$oVariationsWerte->kEigenschaftWert}
                                                                            ({$oArtikel->oVariationDetailPreisKind_arr[$kEigenschaftWert]->Preise->cVKLocalized[$NettoPreise]}{if !empty($oArtikel->oVariationDetailPreisKind_arr[$kEigenschaftWert]->Preise->PreisecPreisVPEWertInklAufpreis[$NettoPreise])}, {$oArtikel->oVariationDetailPreisKind_arr[$kEigenschaftWert]->Preise->PreisecPreisVPEWertInklAufpreis[$NettoPreise]}{/if})
                                                                        {/if}
                                                                    {/foreach}
                                                                {/if}
                                                            {/foreach}
                                                        {elseif $oArtikel->Variationen|@count > 0}
                                                            {foreach $oArtikel->Variationen as $oVariationenArtikel}
                                                                {if $oVariationen->cName == $oVariationenArtikel->cName}
                                                                    {foreach $oVariationenArtikel->Werte as $oVariationsWerte}
                                                                        {$oVariationsWerte->cName}
                                                                        {if $Einstellungen_Vergleichsliste.artikeldetails.artikel_variationspreisanzeige == 1 && $oVariationsWerte->fAufpreisNetto != 0}
                                                                            ({$oVariationsWerte->cAufpreisLocalized[$NettoPreise]}{if !empty($oVariationsWerte->cPreisVPEWertAufpreis[$NettoPreise])}, {$oVariationsWerte->cPreisVPEWertAufpreis[$NettoPreise]}{/if})
                                                                        {elseif $Einstellungen_Vergleichsliste.artikeldetails.artikel_variationspreisanzeige == 2 && $oVariationsWerte->fAufpreisNetto != 0}
                                                                            ({$oVariationsWerte->cPreisInklAufpreis[$NettoPreise]}{if !empty($oVariationsWerte->cPreisVPEWertInklAufpreis[$NettoPreise])}, {$oVariationsWerte->cPreisVPEWertInklAufpreis[$NettoPreise]}{/if})
                                                                        {/if}
                                                                        {if !$oVariationsWerte@last},{/if}
                                                                    {/foreach}
                                                                {/if}
                                                            {/foreach}
                                                        {else}
                                                            &nbsp;
                                                        {/if}
                                                    </td>
                                                {/foreach}
                                            </tr>
                                        {/foreach}
                                    {/block}
                                {/if}
                            {/foreach}
                        </table>
                    </div>
                {/container}
            {/block}
        {else}
            {block name='comparelist-index-empty'}
                {container}
                    {lang key='compareListNoItems'}
                {/container}
            {/block}
        {/if}

        {if isset($bAjaxRequest) && $bAjaxRequest}
            {block name='comparelist-index-script-remove'}
                {inline_script}<script>
                    $('.modal a.remove').click(function(e) {
                        var kArtikel = $(e.currentTarget).data('id');
                        $('section.box-compare li[data-id="' + kArtikel + '"]').remove();
                        eModal.ajax({
                            size: 'lg',
                            url: e.currentTarget.href,
                            title: '{lang key='compare' section='global'}',
                            keyboard: true,
                            tabindex: -1
                        });

                        return false;
                    });
                    new function(){
                        var clCount = {if isset($oVergleichsliste->oArtikel_arr)}{$oVergleichsliste->oArtikel_arr|count}{else}0{/if};
                        $('.navbar-nav .compare-list-menu .badge em').html(clCount);
                        if (clCount > 1) {
                            $('section.box-compare .panel-body').removeClass('hidden');
                        } else {
                            $('.navbar-nav .compare-list-menu .link_to_comparelist').removeAttr('href').removeClass('popup');
                            eModal.close();
                        }
                    }();
                </script>{/inline_script}
            {/block}
        {/if}
        {block name='comparelist-index-script-check'}
            {inline_script}<script>
                $(document).ready(function () {
                    $('.comparelist-checkbox').change(function () {
                        $('[data-id="row-' + $(this).data('id') + '"]').toggleClass('d-none');
                    });
                    $('#check-all').click(function () {
                        $('.comparelist-checkbox').prop('checked', true);
                        $('.comparelist-row').removeClass('d-none');
                    });
                    $('#check-none').click(function () {
                        $('.comparelist-checkbox').prop('checked', false);
                        $('.comparelist-row').addClass('d-none');
                    });
                });
            </script>{/inline_script}
        {/block}
    {/block}

    {block name='comparelist-index-include-footer'}
        {include file='layout/footer.tpl'}
    {/block}
{/block}
