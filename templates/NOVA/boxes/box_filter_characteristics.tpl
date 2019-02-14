{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
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
        {if ($Merkmal->getData('cTyp') === 'SELECTBOX') && $Merkmal->oMerkmalWerte_arr|@count > 0}
            {dropdown variant="link" text="{lang key='selectFilter' section='global'} "}
                {include file='snippets/filter/characteristic.tpl' Merkmal=$Merkmal class="dropdown-menu"}
            {/dropdown}
        {else}
            {nav vertical=true}
                {include file='snippets/filter/characteristic.tpl' Merkmal=$Merkmal}
            {/nav}
        {/if}
    {/card}
{/foreach}
