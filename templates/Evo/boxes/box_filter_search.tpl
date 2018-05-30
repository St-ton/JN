{if $oBox->show()}
    <section class="panel panel-default box box-filter-search" id="sidebox{$oBox->getID()}">
    <div class="panel-heading">
            <div class="panel-title">{lang key='searchFilter'}</div>
        </div>
        <div class="box-body">
            {include file='snippets/filter/search.tpl'}
        </div>
    </section>
{/if}