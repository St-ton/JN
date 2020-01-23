{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='productdetails-config-container'}
    {block name='productdetails-config-container-main'}
        {modal id="cfg-container" class="mb-5" size="xl" title="{lang key="yourConfiguration"}"}
            <div class="tab-content" id="cfg-container-tab-panes">
                <div class="tab-pane fade show active" id="cfg-tab-pane-options" role="tabpanel" aria-labelledby="cfg-tab-options">
                    {block name='productdetails-config-container-options'}
                        {include file='productdetails/config_options.tpl'}
                    {/block}
                </div>
                <div class="tab-pane fade" id="cfg-tab-pane-summary" role="tabpanel" aria-labelledby="cfg-tab-summary">
                    {block name='productdetails-config-container-include-config-sidebar'}
                        {include file='productdetails/config_sidebar.tpl'}
                    {/block}
                </div>
               {* <div class="tab-pane fade" id="cfg-tab-pane-save" role="tabpanel" aria-labelledby="cfg-tab-save">
                    save, QR-code, short URL
                </div>*}
            </div>


            {nav pills=true fill=true class="mt-auto" role="tablist"}
                {navitem id="cfg-tab-options" active=true
                    href="#cfg-tab-pane-options" role="tab" router-data=["toggle"=>"pill"]
                    router-aria=["controls"=>"cfg-tab-pane-options", "selected"=>"true"]
                }
                    <i class="fas fa-cogs"></i> <span class="d-none d-sm-inline-flex ml-2">Options</span>
                {/navitem}
                {navitem id="cfg-tab-summary"
                    href="#cfg-tab-pane-summary" role="tab" router-data=["toggle"=>"pill"]
                    router-aria=["controls"=>"cfg-tab-pane-summary", "selected"=>"false"]
                }
                    <i class="fas fa-cart-plus"></i> <span class="d-none d-sm-inline-flex ml-2">Details</span>
                {/navitem}
                {*{navitem id="cfg-tab-save"
                    href="#cfg-tab-pane-save" role="tab" router-data=["toggle"=>"pill"]
                    router-aria=["controls"=>"cfg-tab-pane-save", "selected"=>"false"]
                }
                    <i class="fas fa-save"></i> <span class="d-none d-sm-inline-flex ml-2">save</span>
                {/navitem}*}
                {navitem href="#" disabled=true}
                    <span id="cfg-price" class="price">465,18 €</span>
                {/navitem}
            {/nav}
        {/modal}
    {/block}

    {block name='productdetails-config-container-sticky-sidebar'}
        {col cols=12 class="mb-6"}
            {*<div id="cfg-sticky-sidebar" class="mb-4">
                {if $Artikel->bHasKonfig}
                    {block name='productdetails-config-container-include-config-sidebar'}
                        {include file='productdetails/config_sidebar.tpl'}
                    {/block}
                {/if}
            </div>
            {row}
                {col cols=12 md=6 offset-md=6}
                    {block name='productdetails-config-container-include-basket'}
                        <div class="mt-3">
                            {include file='productdetails/basket.tpl'}
                        </div>
                    {/block}
                {/col}
            {/row}*}
        {/col}
    {/block}
{/block}
