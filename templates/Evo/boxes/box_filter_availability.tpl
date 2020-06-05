<section class="panel panel-default box box-filter-availability" id="sidebox{$oBox->getID()}">
    <div class="panel-heading">
        <div class="panel-title">{lang key='filterAvailability'}</div>
    </div>
    <div class="box-body">
        {include file='snippets/filter/genericFilterItem.tpl' filter=$oBox->getItems()}
    </div>
</section>
