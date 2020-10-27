{* nur anzeigen, wenn >1 Warenlager aktiv und Artikel ist auf Lager/im Zulauf/Ueberverkaeufe erlaubt/beachtet kein Lager *}
{block name='productdetails-warehouse'}
    {assign var=anzeige value=$Einstellungen.artikeldetails.artikel_lagerbestandsanzeige}
    {if $anzeige !== 'nichts'
        && isset($Artikel->oWarenlager_arr)
        && $Artikel->oWarenlager_arr|@count > 1
        && ($Artikel->cLagerBeachten !== 'Y'
            || $Artikel->cLagerKleinerNull === 'Y'
            || $Artikel->fLagerbestand > 0
            || $Artikel->fZulauf > 0)}
        {block name='productdetails-warehouse-detail-link'}
            {row class="product-stock-info row no-gutters py-3 px-lg-3 border-bottom align-items-end"}
                {col}
                    {button variant="link" class="p-0 text-decoration-underline" data=["toggle"=>"modal", "target"=>"#warehouseAvailability"]}
                        <span class="fas fa-map-marker-alt mr-2"></span>{lang key='warehouseAvailability'}
                    {/button}
                {/col}
            {/row}
        {/block}
        {block name='productdetails-warehouse-modal'}
            {modal id="warehouseAvailability"
                title="{lang key='warehouseAvailability'}"
                centered=true
                size="lg"
                class="fade"}
                {block name='productdetails-warehouse-modal-content'}
                    {block name='productdetails-warehouse-modal-content-header'}
                        {row}
                            {col}
                                <strong>{lang key='warehouse'}</strong>
                            {/col}
                            {col class="ml-auto text-right"}
                                <strong class="ml-auto">{lang key='status'}</strong>
                            {/col}
                        {/row}
                        <hr>
                    {/block}
                    {block name='productdetails-warehouse-modal-content-items'}
                        {foreach $Artikel->oWarenlager_arr as $oWarenlager}
                            {row}
                                {col}
                                    <strong>{$oWarenlager->getName()}</strong>
                                {/col}
                                {col class="ml-auto text-right"}
                                    <span class="ml-auto">
                                         {if $anzeige !== 'nichts'
                                         && ($Artikel->cLagerKleinerNull === 'N'
                                         && $oWarenlager->getBackorderString($Artikel) !== ''
                                         || $Einstellungen.artikeldetails.artikeldetails_lieferantenbestand_anzeigen === 'U')}
                                             <span class="signal_image status-1"><span>{$oWarenlager->getBackorderString($Artikel)}</span></span>
                                        {elseif $anzeige === 'verfuegbarkeit' || $anzeige === 'genau'}
                                            <span class="signal_image status-{$oWarenlager->oLageranzeige->nStatus}">{$oWarenlager->oLageranzeige->cLagerhinweis[$anzeige]}</span>
                                        {elseif $anzeige === 'ampel'}
                                            <span><span class="signal_image status-{$oWarenlager->oLageranzeige->nStatus}">{$oWarenlager->oLageranzeige->AmpelText}</span></span>
                                         {/if}
                                    </span>
                                {/col}
                            {/row}
                            <hr>
                        {/foreach}
                    {/block}
                {/block}
            {/modal}
        {/block}
    {/if}
{/block}
