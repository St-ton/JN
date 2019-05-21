{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='boxes-box-filter-characteristics'}
    {if $nSeitenTyp === $smarty.const.PAGE_ARTIKELLISTE}
        {foreach $oBox->getItems() as $Merkmal}
            <div class="h4">
                {button
                variant="link"
                class="text-decoration-none"
                role="button"
                data=["toggle"=> "collapse", "target"=>"#sidebox{$oBox->getID()}-{$Merkmal->kMerkmal}"]
                }
                {if $Einstellungen.navigationsfilter.merkmal_anzeigen_als !== 'T' && $Merkmal->getData('cBildpfadKlein') !== $smarty.const.BILD_KEIN_MERKMALBILD_VORHANDEN}
                    <img src='{$Merkmal->getData('cBildURLKlein')}' alt='' class='vmiddle' />
                {/if}
                {if $Einstellungen.navigationsfilter.merkmal_anzeigen_als !== 'B'}
                    {$Merkmal->cName}
                {/if}
                +{/button}
            </div>
            {collapse
                class="box box-filter-characteristics overflow-auto"
                style="max-height: 200px"
                id="sidebox{$oBox->getID()}-{$Merkmal->kMerkmal}"
                visible=$Merkmal->isActive()
            }
            {block name='boxes-box-filter-characteristics-characteristics'}
                {if ($Merkmal->getData('cTyp') === 'SELECTBOX') && $Merkmal->oMerkmalWerte_arr|@count > 0}
                    {block name='boxes-box-filter-characteristics-select'}
                        {dropdown variant="light" text="{lang key='selectFilter' section='global'} " toggle-class="btn-block text-left"}
                        {block name='boxes-box-filter-characteristics-include-characteristics-dropdown'}
                            {include file='snippets/filter/characteristic.tpl' Merkmal=$Merkmal class="dropdown-menu"}
                        {/block}
                        {/dropdown}
                    {/block}
                {else}
                    {block name='boxes-box-filter-characteristics-link'}
                        {nav vertical=$Merkmal->getData('cTyp') !== 'BILD'}
                        {block name='boxes-box-filter-characteristics-include-characteristics-link'}
                            {include file='snippets/filter/characteristic.tpl' Merkmal=$Merkmal}
                        {/block}
                        {/nav}
                    {/block}
                {/if}
            {/block}
            {/collapse}
            <hr class="mt-0 mb-4">
        {/foreach}
    {/if}
{/block}
