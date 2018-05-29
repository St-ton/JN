assign var=hf value=$NaviFilter->getManufacturerFilter()}
{if $bBoxenFilterNach
    && !$hf->getVisibility()->equals(\Filter\Visibility::SHOW_NEVER())
    && !$hf->getVisibility()->equals(\Filter\Visibility::SHOW_CONTENT())
    && (!empty($Suchergebnisse->getManufacturerFilterOptions()) || $hf->isInitialized())}
    <section class="panel panel-default box box-filter-manufacturer" id="sidebox{$oBox->kBox}">
        <div class="panel-heading">
            <div class="panel-title">{lang key='manufacturers' section='global'}</div>
        </div>
        <div class="box-body">
            {include file='snippets/filter/genericFilterItem.tpl' filter=$hf}
        </div>
    </section>
{/if}
