<section class="panel panel-default box box-categories word-break" id="sidebox_categories{$oBox->getCustomID()}">
    <div class="panel-heading">
        <div class="panel-title">{if !empty($oBox->getTitle())}{$oBox->getTitle()}{else}{lang key='categories'}{/if}</div>
    </div>
    <div class="box-body">
        <nav class="nav-panel">
            <ul class="nav">
                {include file='snippets/categories_recursive.tpl' i=0 categoryId=0 categoryBoxNumber=$oBox->getCustomID() limit=3 categories=$oBox->getItems()}
            </ul>
        </nav>
    </div>
</section>
