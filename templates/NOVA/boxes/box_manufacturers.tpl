{block name='boxes-box-manufacturers'}
    {card class="box box-manufacturers mb-md-4 dropdown-full-width" id="sidebox{$oBox->getID()}"}
        {block name='boxes-box-manufacturers-content'}
            {block name='boxes-box-manufacturers-toggle-title'}
                {link id="crd-hdr-{$oBox->getID()}"
                    href="#crd-cllps-{$oBox->getID()}"
                    data=["toggle"=>"collapse"]
                    role="button"
                    aria=["expanded"=>"false","controls"=>"crd-cllps-{$oBox->getID()}"]
                    class="text-decoration-none font-bold mb-2 d-md-none dropdown-toggle"}
                    {lang key='manufacturers'}
                {/link}
            {/block}
            {block name='boxes-box-manufacturers-title'}
                <div class="productlist-filter-headline d-none d-md-flex">
                    {lang key='manufacturers'}
                </div>
            {/block}
            {block name='boxes-box-manufacturers-collapse'}
                {collapse
                class="d-md-block"
                visible=false
                id="crd-cllps-{$oBox->getID()}"
                aria=["labelledby"=>"crd-hdr-{$oBox->getID()}"]}
                    {if $oBox->getManufacturers()|@count > 8}
                        {block name='boxes-box-manufacturers-dropdown'}
                            {dropdown class="w-100" variant="secondary btn-block" text="{lang key='selectManufacturer'}<span class='caret'></span>"}
                                {foreach $oBox->getManufacturers() as $manufacturer}
                                    {if $manufacturer@index === 10}{break}{/if}
                                    {dropdownitem href=$manufacturer->cSeo}
                                        {$manufacturer->cName|escape:'html'}
                                    {/dropdownitem}
                                {/foreach}
                            {/dropdown}
                        {/block}
                    {else}
                        {block name='boxes-box-manufacturers-link'}
                            {nav vertical=true}
                                {foreach $oBox->getManufacturers() as $manufacturer}
                                    {if $manufacturer@index === 10}{break}{/if}
                                    {navitem}
                                        {link href=$manufacturer->cSeo title=$manufacturer->cName|escape:'html'}
                                            {$manufacturer->cName|escape:'html'}
                                        {/link}
                                    {/navitem}
                                {/foreach}
                            {/nav}
                        {/block}
                    {/if}
                {/collapse}
            {/block}
            {block name='boxes-box-manufacturers-hr-end'}
                <hr class="my-3 d-flex d-md-none">
            {/block}
        {/block}
    {/card}
{/block}
