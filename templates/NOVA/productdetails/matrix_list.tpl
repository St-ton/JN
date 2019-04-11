{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='productdetails-matrix-list'}
    {if $Artikel->nIstVater == 1 && $Artikel->oVariationKombiKinderAssoc_arr|count > 0}
        {block name='productdetails-index-childs'}
            {foreach $Artikel->oVariationKombiKinderAssoc_arr as $child}
                {row class="mb-3 pt-2 pb-2 {cycle values="bg-light,"}"}
                    {if $Einstellungen.artikeldetails.artikeldetails_warenkorbmatrix_lagerbeachten !== 'Y' ||
                    ($Einstellungen.artikeldetails.artikeldetails_warenkorbmatrix_lagerbeachten === 'Y' && $child->inWarenkorbLegbar == 1)}
                        {block name='productdetails-matrix-list-image'}
                            {col cols=6 md=1}
                                {image fluid=true lazy=true src=$child->Bilder[0]->cURLMini alt=$child->Bilder[0]->cAltAttribut}
                            {/col}
                        {/block}
                        {block name='productdetails-matrix-list-coming-soon'}
                            {col cols=6 md=5}
                                <div >
                                    {link href=$child->cSeo}<span itemprop="name">{$child->cName}</span>{/link}
                                </div>
                                <div class="small">
                                    {if $child->nErscheinendesProdukt}
                                        {lang key='productAvailableFrom'}: <strong>{$child->Erscheinungsdatum_de}</strong>
                                        {if $Einstellungen.global.global_erscheinende_kaeuflich === 'Y' && $child->inWarenkorbLegbar == 1}
                                            ({lang key='preorderPossible'})
                                        {/if}
                                    {/if}
                                    {block name='productdetails-matrix-list-include-stock'}
                                        {include file='productdetails/stock.tpl' Artikel=$child tplscope='matrix'}
                                    {/block}
                                </div>
                            {/col}
                        {/block}
                        {block name='productdetails-matrix-list-var-box-count'}
                            {col cols=6 md=2}
                                {if $child->inWarenkorbLegbar == 1 && !$child->bHasKonfig && ($child->nVariationAnzahl == $child->nVariationOhneFreifeldAnzahl)}
                                    {inputgroup size="sm" class="float-right {if isset($smarty.session.variBoxAnzahl_arr[$child->kArtikel]->bError) && $smarty.session.variBoxAnzahl_arr[$child->kArtikel]->bError} has-error{/if}"}
                                        {if $child->cEinheit}
                                            {inputgroupaddon prepend=true is-text=true class="unit form-control"}
                                                {$child->cEinheit}:
                                            {/inputgroupaddon}
                                        {/if}
                                    {input placeholder="0"
                                        name="variBoxAnzahl[{$child->kArtikel}]"
                                        type="text"
                                        value="{if isset($smarty.session.variBoxAnzahl_arr[$child->kArtikel]->fAnzahl)}{$smarty.session.variBoxAnzahl_arr[$child->kArtikel]->fAnzahl|replace_delim}{/if}"
                                        class="text-right"
                                        aria=["label"=>"{lang key='quantity'} {$child->cName}"]}
                                    {/inputgroup}
                                {/if}
                            {/col}
                        {/block}
                        {block name='productdetails-matrix-list-muted-x'}
                            {col cols=1}
                                <span class="text-muted">&times;</span>
                            {/col}
                        {/block}
                        {block name='productdetails-matrix-list-include-price'}
                            {col cols=5 md=3 class="text-right"}
                                {include file='productdetails/price.tpl' Artikel=$child tplscope='matrix'}
                            {/col}
                        {/block}
                    {/if}
                {/row}
            {/foreach}
        {/block}
        {block name='productdetails-matrix-list-submit'}
            {input type="hidden" name="variBox" value="1"}
            {input type="hidden" name="varimatrix" value="1"}
            {button name="inWarenkorb" type="submit" value="1" variant="primary" class="pull-right"}{lang key='addToCart'}{/button}
        {/block}
    {/if}
{/block}
