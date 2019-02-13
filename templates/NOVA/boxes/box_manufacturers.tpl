{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{card class="box box-manufacturers mb-7" id="sidebox{$oBox->getID()}" title="{lang key='manufacturers'}"}
    <hr class="mt-0 mb-4">
    {if $oBox->getManufacturers()|@count > 8}
        {dropdown class="w-100" variant="secondary btn-block" text="{lang key='selectManufacturer'}<span class='caret'></span>"}
            {foreach $oBox->getManufacturers() as $hst}
               {dropdownitem href="{$hst->cSeo}"}
                    {$hst->cName|escape:'html'}
                {/dropdownitem}
            {/foreach}
        {/dropdown}
    {else}
        {nav vertical=true}
            {foreach $oBox->getManufacturers() as $hst}
                {navitem}
                    {link href="{$hst->cSeo}" title="{$hst->cName|escape:'html'}"}
                        {$hst->cName|escape:'html'}
                    {/link}
                {/navitem}
            {/foreach}
        {/nav}
    {/if}
{/card}
