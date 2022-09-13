{block name='productdetails-config-container'}
    {if $Einstellungen.template.productdetails.config_position === 'popup'}
    {block name='productdetails-config-container-main'}
        {modal id="cfg-container" size="xl" title="{lang key="configure"}"}
            <div class="tab-content" id="cfg-container-tab-panes">
                <div class="tab-pane fade show active" id="cfg-tab-pane-options" role="tabpanel" aria-labelledby="cfg-tab-options">
                    {block name='productdetails-config-container-options'}
                        {if $Einstellungen.template.productdetails.config_layout === 'list'}
                            {include file='productdetails/config_options_list.tpl'}
                        {else}
                            {include file='productdetails/config_options_gallery.tpl'}
                        {/if}
                    {/block}
                </div>
                <div class="tab-pane fade" id="cfg-tab-pane-summary" role="tabpanel" aria-labelledby="cfg-tab-summary">
                    {block name='productdetails-config-container-include-config-sidebar'}
                        {include file='productdetails/config_sidebar.tpl'}
                    {/block}
                </div>
            </div>


            {nav id="cfg-modal-tabs" pills=true fill=true role="tablist"}
                {navitem id="cfg-tab-options" active=true
                    href="#cfg-tab-pane-options" role="tab" router-data=["toggle"=>"pill"]
                    router-aria=["controls"=>"cfg-tab-pane-options", "selected"=>"true"]
                }
                    <i class="fas fa-cogs"></i> <span class="nav-link-text">{lang key='configComponents' section='productDetails'}</span>
                {/navitem}
                {navitem id="cfg-tab-summary"
                    href="#cfg-tab-pane-summary" role="tab" router-data=["toggle"=>"pill"]
                    router-aria=["controls"=>"cfg-tab-pane-summary", "selected"=>"false"]
                }
                    <i class="fas fa-cart-plus"></i> <span class="nav-link-text">{lang key='yourConfiguration'}</span>
                {/navitem}
                {navitem href="#" disabled=true class="cfg-tab-total"}
                    <strong id="cfg-price" class="price"></strong>&nbsp;<span class="footnote-reference">*</span>
                {/navitem}
            {/nav}
            <div class="cfg-footnote small">
                <span class="footnote-reference">*</span>{include file='snippets/shipping_tax_info.tpl' taxdata=$Artikel->taxData}
            </div>
        {/modal}
    {/block}
    {block name='productdetails-config-container-script'}
        {if isset($kEditKonfig) && !isset($bWarenkorbHinzugefuegt)}
            {inline_script}<script>
                $('#cfg-container').modal('show');
            </script>{/inline_script}
        {/if}
    {/block}
    {else}
        {if $Einstellungen.template.productdetails.config_layout === 'list'}
            {row id="cfg-container"}
                {col cols=8}
                    {include file='productdetails/config_options_list.tpl'}
                {/col}
                {col cols=4}
                    <div id="product-configuration-sidebar" class="sticky-top">
                        <div class="panel panel-primary no-margin">
                            <div class="panel-heading">
                                <h5 class="panel-title">{lang key='yourConfiguration'}</h5>
                            </div>
                            <table class="table table-sm config-table">
                                <tbody class="summary"></tbody>
                                <tfoot>
                                <tr>
                                    <td colspan="3" class="text-right word-break">
                                        <strong class="price"></strong>
                                        <p class="vat_info text-muted">
                                            <small>{include file='snippets/shipping_tax_info.tpl' taxdata=$Artikel->taxData}</small>
                                        </p>
                                    </td>
                                </tr>
                                </tfoot>
                            </table>
                            <div class="panel-footer">
                                {if $Artikel->inWarenkorbLegbar == 1}
                                    <div id="quantity-grp" class="choose_quantity input-group">
                                        <input type="number"{if $Artikel->fAbnahmeintervall > 0} required step="{$Artikel->fAbnahmeintervall}"{/if} id="quantity"
                                               class="quantity form-control text-right" name="anzahl"
                                               value="{if $Artikel->fAbnahmeintervall > 0}{if $Artikel->fMindestbestellmenge > $Artikel->fAbnahmeintervall}{$Artikel->fMindestbestellmenge}{else}{$Artikel->fAbnahmeintervall}{/if}{elseif isset($fAnzahl)}{$fAnzahl}{else}1{/if}" />
                                        <span class="input-group-btn">
                                    <button name="inWarenkorb" type="submit" value="{lang key='addToCart'}"
                                            class="submit btn btn-primary">
                                        {if isset($kEditKonfig)}
                                            {lang key='applyChanges'}
                                        {else}
                                            {lang key='addToCart'}
                                        {/if}
                                    </button>
                                </span>
                                    </div>
                                    {if $Artikel->kVariKindArtikel > 0}
                                        <input type="hidden" name="a2" value="{$Artikel->kVariKindArtikel}"/>
                                    {/if}
                                    {if isset($kEditKonfig)}
                                        <input type="hidden" name="kEditKonfig" value="{$kEditKonfig}"/>
                                    {/if}
                                {/if}
                            </div>
                        </div>
                    </div>
                {/col}
            {/row}
        {else}
        {col id="cfg-container"}
            <div class="tab-content{if $Einstellungen.template.productdetails.config_layout === 'list'} cfg-list{else} cfg-gallery{/if}" id="cfg-container-tab-panes">
                <div class="tab-pane fade show active" id="cfg-tab-pane-options" role="tabpanel" aria-labelledby="cfg-tab-options">
                    {block name='productdetails-config-container-options'}
                        {if $Einstellungen.template.productdetails.config_layout === 'list'}
                            {include file='productdetails/config_options_list.tpl'}
                        {else}
                            {include file='productdetails/config_options_gallery.tpl'}
                        {/if}
                    {/block}
                </div>
                <div class="tab-pane fade" id="cfg-tab-pane-summary" role="tabpanel" aria-labelledby="cfg-tab-summary">
                    {block name='productdetails-config-container-include-config-sidebar'}
                        {include file='productdetails/config_sidebar.tpl'}
                    {/block}
                </div>
            </div>


            {nav id="cfg-modal-tabs" pills=true fill=true role="tablist"}
                {navitem id="cfg-tab-options" active=true
                    href="#cfg-tab-pane-options" role="tab" router-data=["toggle"=>"pill"]
                    router-aria=["controls"=>"cfg-tab-pane-options", "selected"=>"true"]
                }
                    <i class="fas fa-cogs"></i> <span class="nav-link-text">{lang key='configComponents' section='productDetails'}</span>
                {/navitem}
                {navitem id="cfg-tab-summary"
                    href="#cfg-tab-pane-summary" role="tab" router-data=["toggle"=>"pill"]
                    router-aria=["controls"=>"cfg-tab-pane-summary", "selected"=>"false"]
                }
                    <i class="fas fa-cart-plus"></i> <span class="nav-link-text">{lang key='yourConfiguration'}</span>
                {/navitem}
                {navitem href="#" disabled=true class="cfg-tab-total"}
                    <strong id="cfg-price" class="price"></strong>&nbsp;<span class="footnote-reference">*</span>
                {/navitem}
            {/nav}
            <div class="cfg-footnote small">
                <span class="footnote-reference">*</span>{include file='snippets/shipping_tax_info.tpl' taxdata=$Artikel->taxData}
            </div>
        {/col}
        {/if}
    {/if}
{/block}
