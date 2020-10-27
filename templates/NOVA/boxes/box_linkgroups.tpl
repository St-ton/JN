{block name='boxes-box-linkgroups'}
    {card class="box box-linkgroup mb-md-7 text-left" id="box{$oBox->getID()}" no-body=true}
        {block name='boxes-box-linkgroups-toggle-title'}
            {link
                id="crd-hdr-{$oBox->getID()}"
                href="#crd-cllps-{$oBox->getID()}"
                data=["toggle"=>"collapse"]
                role="button"
                aria=["expanded"=>"false","controls"=>"crd-cllps-{$oBox->getID()}"]
                class="text-decoration-none font-weight-bold-util d-md-none dropdown-toggle"}
                <span class="text-truncate">
                    {$oBox->getTitle()}
                </span>
            {/link}
        {/block}
        {block name='boxes-box-linkgroups-title'}
            <div class="productlist-filter-headline d-none d-md-flex">
                {$oBox->getTitle()}
            </div>
        {/block}
        {block name='boxes-box-linkgroups-content'}
            {collapse
                class="d-md-block"
                visible=false
                id="crd-cllps-{$oBox->getID()}"
                aria=["labelledby"=>"crd-hdr-{$oBox->getID()}"]}
                    <div class="nav-panel mt-2 mt-md-0">
                        {nav vertical=true}
                            {block name='boxes-box-linkgroups-include-linkgroups-recursive'}
                                {include file='snippets/linkgroup_recursive.tpl' linkgroupIdentifier=$oBox->getLinkGroupTemplate() dropdownSupport=true  tplscope='box'}
                            {/block}
                        {/nav}
                    </div>
            {/collapse}
        {/block}
    {/card}
    {block name='boxes-box-linkgroups-hr-end'}
        <hr class="my-3 d-flex d-md-none">
    {/block}
{/block}
