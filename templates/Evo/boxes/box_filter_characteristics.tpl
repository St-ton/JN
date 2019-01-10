{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{foreach $oBox->getItems() as $Merkmal}
    {assign var=kMerkmal value=$Merkmal->kMerkmal}
    <section class="panel panel-default box box-filter-characteristics" id="sidebox{$oBox->getID()}-{$Merkmal->kMerkmal}">
        {if ($Merkmal->getData('cTyp') === 'SELECTBOX') && $Merkmal->oMerkmalWerte_arr|@count > 1}
            <div class="panel-heading dropdown">
                <div class="panel-title">
                    {if $Einstellungen.navigationsfilter.merkmal_anzeigen_als !== 'T' && $Merkmal->getData('cBildpfadKlein') !== $smarty.const.BILD_KEIN_MERKMALBILD_VORHANDEN}
                        <img src="{$Merkmal->getData('cBildURLKlein')}" alt="" class="vmiddle" />
                    {/if}
                    {if $Einstellungen.navigationsfilter.merkmal_anzeigen_als !== 'B'}
                        &nbsp;{$Merkmal->cName}
                    {/if}
                </div>
            </div>
            <div class="box-body panel-body dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                     {lang key='selectFilter' section='global'}&nbsp; <span class="fa fa-caret-down"></span>
                </a>
                {include file='snippets/filter/characteristic.tpl' Merkmal=$Merkmal class="dropdown-menu"}
            </div>
        {else}
            <div class="panel-heading">
                <div class="panel-title">
                {if $Einstellungen.navigationsfilter.merkmal_anzeigen_als !== 'T' && $Merkmal->getData('cBildpfadKlein') !== $smarty.const.BILD_KEIN_MERKMALBILD_VORHANDEN}
                    <img src="{$Merkmal->getData('cBildURLKlein')}" alt="" class="vmiddle" />
                {/if}
                {if $Einstellungen.navigationsfilter.merkmal_anzeigen_als !== 'B'}
                    &nbsp;{$Merkmal->cName}
                {/if}
                </div>
            </div>
            <div class="box-body">
                {include file='snippets/filter/characteristic.tpl' Merkmal=$Merkmal}
            </div>
        {/if}
    </section>
{/foreach}
