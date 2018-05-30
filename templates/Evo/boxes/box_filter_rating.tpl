{if $oBox->show()}
    <section class="panel panel-default box box-filter-reviews" id="sidebox{$oBox->getID()}">
        <div class="panel-heading">
            <div class="panel-title">{lang key='Votes'}</div>
        </div>
        <div class="box-body">
            {include file='snippets/filter/genericFilterItem.tpl' filter=$oBox->getItems()}
        </div>
    </section>
{/if}