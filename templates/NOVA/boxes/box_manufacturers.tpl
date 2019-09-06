{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='boxes-box-manufacturers'}
    {card class="box box-manufacturers mb-7" id="sidebox{$oBox->getID()}" title="{lang key='manufacturers'}"}
        {block name='boxes-box-manufacturers-content'}
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
        {/block}
    {/card}
{/block}
