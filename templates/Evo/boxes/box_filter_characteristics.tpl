{if $BoxenEinstellungen.navigationsfilter.merkmalfilter_verwenden === 'box' && isset($Suchergebnisse->MerkmalFilter) && $Suchergebnisse->MerkmalFilter|@count > 0}
    {*{foreach $Suchergebnisse->MerkmalFilter as $Merkmal}*}
        {*<section class="panel panel-default box box-filter-characteristics">*}
            {*{if ($Merkmal->cTyp === 'SELECTBOX') && $Merkmal->oMerkmalWerte_arr|@count > 1}*}
                {*<div class="panel-heading dropdown">*}
                    {*<h5 class="panel-title">*}
                        {*{if $Einstellungen.navigationsfilter.merkmal_anzeigen_als !== 'T' && !empty($Merkmal->cBildpfadKlein) && $Merkmal->cBildpfadKlein !== $BILD_KEIN_MERKMALBILD_VORHANDEN}*}
                            {*<img src="{$Merkmal->cBildpfadKlein}" alt="" class="vmiddle" />*}
                        {*{/if}*}
                        {*{if $Einstellungen.navigationsfilter.merkmal_anzeigen_als !== 'B'}*}
                            {*&nbsp;{$Merkmal->cName}*}
                        {*{/if}*}
                    {*</h5>*}
                {*</div>*}
                {*<div class="panel-body dropdown">*}
                    {*<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">*}
                        {*{$Merkmal->cName} &nbsp; <span class="fa fa-caret-down"></span>*}
                    {*</a>*}
                    {*{include file='snippets/filter/characteristic.tpl' Merkmal=$Merkmal class="dropdown-menu"}*}
                {*</div>*}
            {*{else}*}
                {*<div class="panel-heading">*}
                    {*<h5 class="panel-title">*}
                    {*{if $Einstellungen.navigationsfilter.merkmal_anzeigen_als !== 'T' && !empty($Merkmal->cBildpfadKlein) && $Merkmal->cBildpfadKlein !== $BILD_KEIN_MERKMALBILD_VORHANDEN}*}
                        {*<img src="{$Merkmal->cBildpfadKlein}" alt="" class="vmiddle" />*}
                    {*{/if}*}
                    {*{if $Einstellungen.navigationsfilter.merkmal_anzeigen_als !== 'B'}*}
                        {*&nbsp;{$Merkmal->cName}*}
                    {*{/if}*}
                    {*</h5>*}
                {*</div>*}
                {*<div class="box-body">*}
                    {*2 - characteristic.tpl<br>*}
                    {*{include file='snippets/filter/characteristic.tpl' Merkmal=$Merkmal}*}
                {*</div>*}
            {*{/if}*}
        {*</section>*}
    {*{/foreach}*}
    {foreach $NaviFilter->attributeFilterCompat->getFilterCollection() as $filter}
        <section class="panel panel-default box box-filter-characteristics">
            <div class="panel-heading">
                <h5 class="panel-title">
                    {if $Einstellungen.navigationsfilter.merkmal_anzeigen_als !== 'T' && !empty($filter->cBildpfadKlein) && $filter->cBildpfadKlein !== $BILD_KEIN_MERKMALBILD_VORHANDEN}
                        <img src="{$filter->cBildpfadKlein}" alt="" class="vmiddle" />
                    {/if}
                    {if $Einstellungen.navigationsfilter.merkmal_anzeigen_als !== 'B'}
                        &nbsp;{$filter->getName()}
                    {/if}
                </h5>
            </div>
            <div class="box-body">
                {include file='snippets/filter/genericFilterItem.tpl' filter=$filter}
            </div>
        </section>
    {/foreach}
{/if}