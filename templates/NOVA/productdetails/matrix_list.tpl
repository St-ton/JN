{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if $Artikel->nIstVater == 1 && $Artikel->oVariationKombiKinderAssoc_arr|count > 0}

    {foreach $Artikel->oVariationKombiKinderAssoc_arr as $child}
        {row class="mb-3 pt-2 pb-2 {cycle values="bg-light,"}"}
            {if $Einstellungen.artikeldetails.artikeldetails_warenkorbmatrix_lagerbeachten !== 'Y' ||
            ($Einstellungen.artikeldetails.artikeldetails_warenkorbmatrix_lagerbeachten === 'Y' && $child->inWarenkorbLegbar == 1)}
                {assign var=cVariBox value=''}
                {foreach $child->oVariationKombi_arr as $variation}
                    {if $cVariBox|strlen > 0}
                        {assign var=cVariBox value=$cVariBox|cat:'_'}
                    {/if}
                    {assign var=cVariBox value=$cVariBox|cat:$variation->kEigenschaft|cat:':'|cat:$variation->kEigenschaftWert}
                {/foreach}
                {col cols=6 md=1}
                    {image fluid=true lazy=true src=$child->Bilder[0]->cURLMini alt=$child->Bilder[0]->cAltAttribut}
                {/col}
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
                        {include file='productdetails/stock.tpl' Artikel=$child tplscope='matrix'}
                    </div>
                {/col}
                {col cols=6 md=2}
                    {if $child->inWarenkorbLegbar == 1 && !$child->bHasKonfig && ($child->nVariationAnzahl == $child->nVariationOhneFreifeldAnzahl)}
                        {inputgroup size="sm" class="float-right {if isset($smarty.session.variBoxAnzahl_arr[$cVariBox]->bError) && $smarty.session.variBoxAnzahl_arr[$cVariBox]->bError} has-error{/if}"}
                            {if $child->cEinheit}
                                {inputgroupaddon prepend=true is-text=true class="unit"}
                                    {$child->cEinheit}:
                                {/inputgroupaddon}
                            {/if}
                        {input placeholder="0"
                            name="variBoxAnzahl[{$cVariBox}]"
                            type="text"
                            value="{if isset($smarty.session.variBoxAnzahl_arr[$cVariBox]->fAnzahl)}{$smarty.session.variBoxAnzahl_arr[$cVariBox]->fAnzahl|replace_delim}{/if}"
                            class="text-right"
                            aria=["label"=>"{lang key='quantity'} {$child->cName}"]}
                        {/inputgroup}
                    {/if}
                {/col}
                {col cols=1}
                    <span class="text-muted">&times;</span>
                {/col}
                {col cols=5 md=3 class="text-right"}
                    {include file='productdetails/price.tpl' Artikel=$child tplscope='matrix'}
                {/col}
            {/if}
        {/row}
    {/foreach}
    {input type="hidden" name="variBox" value="1"}
    {input type="hidden" name="varimatrix" value="1"}
    {button name="inWarenkorb" type="submit" value="1" variant="primary" class="pull-right"}{lang key='addToCart'}{/button}
{/if}
