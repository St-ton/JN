{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='boxes-box-manufacturers'}
    {card class="box box-manufacturers mb-7" id="sidebox{$oBox->getID()}" title="{lang key='manufacturers'}"}
        <hr class="mt-0 mb-4">
        {block name='boxes-box-manufacturers-content'}
            {if $oBox->getManufacturers()|@count > 8}
                {block name='boxes-box-manufacturers-dropdown'}
                    {dropdown class="w-100" variant="secondary btn-block" text="{lang key='selectManufacturer'}<span class='caret'></span>"}
                        {foreach $oBox->getManufacturers() as $hst}
                           {dropdownitem href=$hst->cSeo}
                                {$hst->cName|escape:'html'}
                            {/dropdownitem}
                        {/foreach}
                    {/dropdown}
                {/block}
            {else}
                {block name='boxes-box-manufacturers-link'}
                    {nav vertical=true}
                        {foreach $oBox->getManufacturers() as $hst}
                            {navitem}
                                {link href=$hst->cSeo title=$hst->cName|escape:'html'}
                                    {$hst->cName|escape:'html'}
                                {/link}
                            {/navitem}
                        {/foreach}
                    {/nav}
                {/block}
            {/if}
        {/block}
    {/card}
{/block}
