{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='header'}
    {include file='layout/header.tpl'}
{/block}

{block name='content'}
    <h1>{lang key='compare' section='global'}</h1>
    <hr class="mt-2 mb-2">
    {include file='snippets/extension.tpl'}

    {if $oVergleichsliste->oArtikel_arr|@count > 0}
        <div id="filter-checkboxes" class="mb-3">
            {row}
                {col}
                    {buttongroup}
                        {button
                        variant="light"
                        role="button"
                        data=["toggle"=> "collapse", "target"=>"#collapse-checkboxes"]
                        }
                            <i class="fa fa-chevron-down"></i>
                            Filter
                        {/button}
                        {button variant="light" id="check-all"}
                            {lang key='showAll'}
                        {/button}
                        {button variant="light" id="check-none"}
                            {lang key='showNone'}
                        {/button}
                    {/buttongroup}
                {/col}
            {/row}
            {collapse id="collapse-checkboxes" visible=false}
                {foreach $prioRows as $row}
                    {if $row['key'] !== 'Merkmale' && $row['key'] !== 'Variationen'}
                        {checkbox checked=true data=['id' => $row['key']] class='comparelist-checkbox'}
                            {$row['name']}
                        {/checkbox}
                    {/if}
                    {if $row['key'] === 'Merkmale'}
                        {foreach $oMerkmale_arr as $oMerkmale}
                            {checkbox checked=true data=['id' => "attr-{$oMerkmale->cName}"] class='comparelist-checkbox'}
                                {$oMerkmale->cName}
                            {/checkbox}
                        {/foreach}
                    {/if}
                    {if $row['key'] === 'Variationen'}
                        {foreach $oVariationen_arr as $oVariationen}
                            {checkbox checked=true data=['id' => "vari-{$oVariationen->cName}"] class='comparelist-checkbox'}
                                {$oVariationen->cName}
                            {/checkbox}
                        {/foreach}
                    {/if}
                {/foreach}
            {/collapse}
        </div>
        <div class="comparelist table-responsive">
            <table class="table table-bordered table-sm">
                <tr>
                    <td>&nbsp;</td>
                    {foreach $oVergleichsliste->oArtikel_arr as $oArtikel}
                        <td style="width:{$Einstellungen_Vergleichsliste.vergleichsliste.vergleichsliste_spaltengroesse}px;" class="text-center">
                            <div class="text-right">
                                {link href=$oArtikel->cURLDEL class="text-decoration-none"}
                                    &times;
                                {/link}
                            </div>
                            <div>
                                {link href=$oArtikel->cURLFull}
                                    {image src=$oArtikel->cVorschaubild alt=$oArtikel->cName class="image"}
                                {/link}
                            </div>
                            <p>
                                {link href=$oArtikel->cURLFull}{$oArtikel->cName}{/link}
                            </p>
                            {include file='productdetails/rating.tpl' stars=$oArtikel->fDurchschnittsBewertung}
                            {if $oArtikel->getOption('nShowOnlyOnSEORequest', 0) === 1}
                                <p>{lang key='productOutOfStock' section='productDetails'}</p>
                            {elseif $oArtikel->Preise->fVKNetto == 0 && $Einstellungen.global.global_preis0 === 'N'}
                                <p>{lang key='priceOnApplication' section='global'}</p>
                            {else}
                                <p>
                                    {include file='productdetails/price.tpl' Artikel=$oArtikel tplscope='detail'}
                                </p>
                            {/if}
                            {if ($oArtikel->inWarenkorbLegbar === 1 || ($oArtikel->nErscheinendesProdukt === 1 && $Einstellungen.global.global_erscheinende_kaeuflich === 'Y'))}
                                {if $oArtikel->bHasKonfig}
                                    {link href=$oArtikel->cURLFull class="btn btn-primary"
                                    title="{lang key='product' section='global'} {lang key='configure' section='global'}"}
                                        <span class="fa fa-cogs"></span>
                                    {/link}
                                {elseif !empty($oArtikel->Variationen)}
                                    {link href=$oArtikel->cURLFull class="btn btn-primary"
                                    title="{lang key='product' section='global'} {lang key='wishlistaddToCart' section='global'}"}
                                        <span class="fas fa-shopping-cart"></span>
                                    {/link}
                                {else}
                                    {form method='post'}
                                        {formgroup class="quantity-wrapper"}
                                            {inputgroup class="quantity-wrapper"}
                                                {input type="{if $oArtikel->cTeilbar === 'Y' && $oArtikel->fAbnahmeintervall == 0}text{else}number{/if}" min="0"
                                                step="{if $oArtikel->fAbnahmeintervall > 0}{$oArtikel->fAbnahmeintervall}{/if}"
                                                id="quantity{$oArtikel->kArtikel}"
                                                class="quantity text-right"
                                                name="anzahl"
                                                autocomplete="off"
                                                value="{if $oArtikel->fAbnahmeintervall > 0}{if $oArtikel->fMindestbestellmenge > $oArtikel->fAbnahmeintervall}{$oArtikel->fMindestbestellmenge}{else}{$oArtikel->fAbnahmeintervall}{/if}{else}1{/if}"}
                                            {/inputgroup}
                                        {/formgroup}
                                        {button
                                        type="submit"
                                        name="addToCart"
                                        value=$oArtikel->kArtikel
                                        variant="primary"
                                        title="{lang key='wishlistaddToCart' section='login'}"
                                        }
                                            <span class="fas fa-shopping-cart"></span>
                                        {/button}
                                    {/form}
                                {/if}
                            {/if}
                        </td>
                    {/foreach}
                </tr>
                {foreach $prioRows as $row}
                    {if $row['key'] !== 'Merkmale' && $row['key'] !== 'Variationen'}
                        <tr class="comparelist-row" data-id="row-{$row['key']}">
                        <td>
                            <b>{$row['name']}</b>
                        </td>
                        {foreach $oVergleichsliste->oArtikel_arr as $oArtikel}
                            {if $row['key'] === 'verfuegbarkeit'}
                                <td>
                                    {include file='productdetails/stock.tpl' Artikel=$oArtikel availability=true}
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
                                <td>{include file='productdetails/stock.tpl' Artikel=$oArtikel shippingTime=true}</td>
                            {elseif $oArtikel->$row['key'] !== ''}
                                <td style="min-width: {$Einstellungen_Vergleichsliste.vergleichsliste.vergleichsliste_spaltengroesse}px">
                                    {if $row['key'] === 'fArtikelgewicht' || $row['key'] === 'fGewicht'}
                                        {$oArtikel->$row['key']} {lang key='weightUnit' section='comparelist'}
                                    {else}
                                        {$oArtikel->$row['key']}
                                    {/if}
                                </td>
                            {else}
                                <td>--</td>
                            {/if}
                        {/foreach}
                        </tr>
                    {/if}
                    {if $row['key'] === 'Merkmale'}
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
                    {/if}
                    {if $row['key'] === 'Variationen'}
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
                    {/if}
                {/foreach}
            </table>
        </div>
    {else}
        {lang key='compareListNoItems'}
    {/if}

    {if isset($bAjaxRequest) && $bAjaxRequest}
        <script type="text/javascript">
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
        </script>
    {/if}
    <script>
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
    </script>
{/block}

{block name='footer'}
    {include file='layout/footer.tpl'}
{/block}
