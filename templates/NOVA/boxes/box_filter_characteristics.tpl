{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='boxes-box-filter-characteristics'}
    {foreach $oBox->getItems() as $Merkmal}
        {assign var=kMerkmal value=$Merkmal->kMerkmal}
        {card
            class="box box-filter-characteristics mb-7"
            id="sidebox{$oBox->getID()}-{$Merkmal->kMerkmal}"
            title="{if $Einstellungen.navigationsfilter.merkmal_anzeigen_als !== 'T' && $Merkmal->getData('cBildpfadKlein') !== $smarty.const.BILD_KEIN_MERKMALBILD_VORHANDEN}
                        <img src='{$Merkmal->getData('cBildURLKlein')}' alt='' class='vmiddle' />
                    {/if}
                    {if $Einstellungen.navigationsfilter.merkmal_anzeigen_als !== 'B'}
                        {$Merkmal->cName}
                    {/if}"
        }
            <hr class="mt-0 mb-4">
            {block name='boxes-box-filter-characteristics-characteristics'}
                {if ($Merkmal->getData('cTyp') === 'SELECTBOX') && $Merkmal->oMerkmalWerte_arr|@count > 0}
                    {block name='boxes-box-filter-characteristics-select'}
                        {dropdown variant="link" text="{lang key='selectFilter' section='global'} "}
                            {block name='boxes-box-filter-characteristics-include-characteristics-dropdown'}
                                {include file='snippets/filter/characteristic.tpl' Merkmal=$Merkmal class="dropdown-menu"}
                            {/block}
                        {/dropdown}
                    {/block}
                {else}
                    {block name='boxes-box-filter-characteristics-link'}
                        {nav vertical=true}
                            {block name='boxes-box-filter-characteristics-include-characteristics-link'}
                                {include file='snippets/filter/characteristic.tpl' Merkmal=$Merkmal}
                            {/block}
                        {/nav}
                    {/block}
                {/if}
            {/block}
        {/card}
    {/foreach}
{/block}
