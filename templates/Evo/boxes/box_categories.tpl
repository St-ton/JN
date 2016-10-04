{assign var="nID" value=$oBox->kCustomID}
{get_category_array categoryId=0 categoryBoxNumber=$nID assign='categories'}
{if isset($categories) && $categories|count > 0}
    <section class="panel panel-default box box-categories word-break" id="sidebox_categories{$nID}">
        <div class="panel-heading">
            <h5 class="panel-title">{if !empty($oBox->cTitel)}{$oBox->cTitel}{else}{lang key="categories" section="global"}{/if}</h5>
        </div>
        <div class="box-body">
            <nav class="nav-panel">
                <ul class="nav">
                    {include file='snippets/categories_recursive.tpl' i=0 categoryId=0 categoryBoxNumber=$nID limit=3}
                </ul>
            </nav>
        </div>
    </section>
{/if}