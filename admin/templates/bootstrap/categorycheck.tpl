{config_load file="$lang.conf" section='categorycheck'}
{include file='tpl_inc/header.tpl'}
{include file='tpl_inc/seite_header.tpl' cTitel=__('categorycheck') cBeschreibung=__('categorycheckDesc') cDokuURL=__('categorycheckURL')}
<div id="content">
    <div class="systemcheck">
        {if !$passed}
            <div class="alert alert-warning">
                {__('errorCatsWithoutParents')}
            </div>
            <table class="table table-striped table-hover">
                <thead>
                <tr>
                    <th class="col-xs-3 text-center">ID</th>
                    <th class="col-xs-9 text-center">Name</th>
                </tr>
                </thead>
                <tbody>
                {foreach $cateogries as $category}
                    <tr>
                        <td class="text-center">{$category->kKategorie}</td>
                        <td class="text-center">{$category->cName}</td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        {else}
            <div class="alert alert-info">{__('infoNoOrphanedCats')}</div>
        {/if}
    </div>
</div>
{include file='tpl_inc/footer.tpl'}
