{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='boxes-box-filter-characteristics'}
    {if $nSeitenTyp === $smarty.const.PAGE_ARTIKELLISTE && (!$device->isMobile() || $device->isTablet())}
        {foreach $oBox->getItems() as $Merkmal}
            <div id="sidebox{$oBox->getID()}-{$Merkmal->kMerkmal}" class="box box-filter-characteristics{if $Merkmal@last} mb-7{/if}">
                {button
                    variant="link"
                    class="text-decoration-none px-0 text-left dropdown-toggle"
                    role="button"
                    block=true
                    data=["toggle"=> "collapse", "target"=>"#cllps-box{$oBox->getID()}-{$Merkmal->kMerkmal}"]
                }
                    {if $Einstellungen.navigationsfilter.merkmal_anzeigen_als !== 'T' && $Merkmal->getData('cBildpfadKlein')|strpos:$smarty.const.BILD_KEIN_MERKMALBILD_VORHANDEN === false}
                        <img src='{$Merkmal->getData('cBildURLKlein')}' alt='' class='vmiddle' />
                    {/if}
                    {if $Einstellungen.navigationsfilter.merkmal_anzeigen_als !== 'B'}
                        {$Merkmal->cName}
                    {/if}
                {/button}
                {collapse
                    class="{if $Merkmal->getData('cTyp') !== 'SELECTBOX'}overflow-auto{/if}"
                    id="cllps-box{$oBox->getID()}-{$Merkmal->kMerkmal}"
                    visible=$Merkmal->isActive()
                }
                {block name='boxes-box-filter-characteristics-characteristics'}
                    {if ($Merkmal->getData('cTyp') === 'SELECTBOX') && $Merkmal->oMerkmalWerte_arr|@count > 0}
                        {block name='boxes-box-filter-characteristics-select'}
                            {dropdown variant="light" text="{lang key='selectFilter' section='global'} " toggle-class="btn-block text-left"}
                            {block name='boxes-box-filter-characteristics-include-characteristics-dropdown'}
                                {include file='snippets/filter/characteristic.tpl' Merkmal=$Merkmal}
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
                {if !$Merkmal@last}<hr class="my-2">{/if}
            </div>
        {/foreach}
    {/if}
{/block}
