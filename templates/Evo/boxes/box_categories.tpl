{if $oBox->show()}
    {assign var='nID' value=$oBox->getCustomID()}
    {get_category_array categoryId=0 categoryBoxNumber=$nID assign='categories'}
    {if !empty($categories)}
        <section class="panel panel-default box box-categories word-break" id="sidebox_categories{$nID}">
            <div class="panel-heading">
                <div class="panel-title">{if !empty($oBox->getTitle())}{$oBox->getTitle()}{else}{lang key='categories'}{/if}</div>
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
{/if}