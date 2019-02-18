{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='header'}
    {include file='layout/header.tpl'}
{/block}

{block name='content'}
    <h1>{lang key='compare' section='global'}</h1>

    {include file='snippets/extension.tpl'}
    
    {if $oVergleichsliste->oArtikel_arr|@count > 1}
        <div class="comparelist table-responsive">
            <table class="table table-striped table-bordered table-sm">
                <tr>
                    <td>&nbsp;</td>
                    {foreach $oVergleichsliste->oArtikel_arr as $oArtikel}
                        <td style="width:{$Einstellungen_Vergleichsliste.vergleichsliste.vergleichsliste_spaltengroesse}px;" class="text-center">
                            <div>
                                {link href=$oArtikel->cURLFull}
                                    {image src=$oArtikel->cVorschaubild alt=$oArtikel->cName class="image"}
                                {/link}
                            </div>
                            <p>
                                {link href=$oArtikel->cURLFull}{$oArtikel->cName}{/link}
                            </p>

                            {if $oArtikel->getOption('nShowOnlyOnSEORequest', 0) === 1}
                                <p>{lang key='productOutOfStock' section='productDetails'}</p>
                            {elseif $oArtikel->Preise->fVKNetto == 0 && $Einstellungen.global.global_preis0 === 'N'}
                                <p>{lang key='priceOnApplication' section='global'}</p>
                            {else}
                                <p>
                                    {include file='productdetails/price.tpl' Artikel=$oArtikel tplscope='detail'}
                                </p>
                            {/if}
                        </td>
                    {/foreach}
                </tr>
                {foreach $cPrioSpalten_arr as $cPrioSpalten}
                    <tr>
    
                    {if $cPrioSpalten === 'cArtNr' && $Einstellungen_Vergleichsliste.vergleichsliste.vergleichsliste_artikelnummer != 0}
                        <!-- Artikelnummer-->
                        <td>
                            <b>{lang key='productNumber' section='comparelist'}</b>
                        </td>
                    {/if}
                    {if $cPrioSpalten === 'cHersteller' && $Einstellungen_Vergleichsliste.vergleichsliste.vergleichsliste_hersteller != 0}
                        <td>
                            <b>{lang key='manufacturer' section='comparelist'}</b>
                        </td>
                    {/if}
                    {if $cPrioSpalten === 'cBeschreibung' && $Einstellungen_Vergleichsliste.vergleichsliste.vergleichsliste_beschreibung != 0}
                        <td>
                            <div class="custom_content">
                                <b>{lang key='description' section='comparelist'}</b>
                            </div>
                        </td>
                    {/if}
                    {if $cPrioSpalten === 'cKurzBeschreibung' && $Einstellungen_Vergleichsliste.vergleichsliste.vergleichsliste_kurzbeschreibung != 0}
                        <td>
                            <b>{lang key='shortDescription' section='comparelist'}</b>
                        </td>
                    {/if}
                    {if $cPrioSpalten === 'fArtikelgewicht' && $Einstellungen_Vergleichsliste.vergleichsliste.vergleichsliste_artikelgewicht != 0}
                        <td>
                            <b>{lang key='productWeight' section='comparelist'}</b>
                        </td>
                    {/if}
                    {if $cPrioSpalten === 'fGewicht' && $Einstellungen_Vergleichsliste.vergleichsliste.vergleichsliste_versandgewicht != 0}
                        <td>
                            <b>{lang key='shippingWeight' section='comparelist'}</b>
                        </td>
                    {/if}
                    {if $cPrioSpalten !== 'Merkmale' && $cPrioSpalten !== 'Variationen'}
                        {foreach $oVergleichsliste->oArtikel_arr as $oArtikel}
                            {if $oArtikel->$cPrioSpalten !== ''}
                                <td style="min-width: {$Einstellungen_Vergleichsliste.vergleichsliste.vergleichsliste_spaltengroesse}px">
                                    {if $cPrioSpalten === 'fArtikelgewicht' || $cPrioSpalten === 'fGewicht'}
                                        {$oArtikel->$cPrioSpalten} {lang key='weightUnit' section='comparelist'}
                                    {else}
                                        {$oArtikel->$cPrioSpalten}
                                    {/if}
                                </td>
                            {else}
                                <td>--</td>
                            {/if}
                        {/foreach}
                        </tr>
                    {/if}
    
                    {if $cPrioSpalten === 'Merkmale' && $Einstellungen_Vergleichsliste.vergleichsliste.vergleichsliste_merkmale != 0}
                        {foreach $oMerkmale_arr as $oMerkmale}
                            <tr>
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
    
                    {if $cPrioSpalten === 'Variationen' && $Einstellungen_Vergleichsliste.vergleichsliste.vergleichsliste_variationen != 0}
                        {foreach $oVariationen_arr as $oVariationen}
                            <tr>
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

                <tr>
                    <td>
                        &nbsp;
                    </td>
                    {foreach $oVergleichsliste->oArtikel_arr as $oArtikel}
                        <td class="text-center" style="min-width: {$Einstellungen_Vergleichsliste.vergleichsliste.vergleichsliste_spaltengroesseattribut}px">
                            {link href=$oArtikel->cURLDEL class="btn btn-secondary"}
                                <span class="fa fa-trash"></span>
                            {/link}
                        </td>
                    {/foreach}
                </tr>

                {if !empty($bWarenkorb)}
                    <tr>
                        <td style="min-width: {$Einstellungen_Vergleichsliste.vergleichsliste.vergleichsliste_spaltengroesseattribut}px">
                            &nbsp;
                        </td>
                        {foreach $oVergleichsliste->oArtikel_arr as $oArtikel}
                            <td class="text-center" style="min-width: {$Einstellungen_Vergleichsliste.vergleichsliste.vergleichsliste_spaltengroesse}px">
                                {link class="btn btn-primary" href=$oArtikel->cURL}
                                    {lang key='details' section='global'}
                                {/link}
                            </td>
                        {/foreach}
                    </tr>
                {/if}
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
{/block}

{block name='footer'}
    {include file='layout/footer.tpl'}
{/block}
