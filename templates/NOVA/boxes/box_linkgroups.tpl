{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='boxes-box-linkgroups'}
    {card class="box box-linkgroup mb-md-7 text-left" id="box{$oBox->getID()}" no-body=true}
        {link
            id="crd-hdr-{$oBox->getID()}"
            href="#crd-cllps-{$oBox->getID()}"
            data=["toggle"=>"collapse"]
            role="button"
            aria=["expanded"=>"false","controls"=>"crd-cllps-{$oBox->getID()}"]
            class="text-decoration-none font-weight-bold mb-2 d-flex d-md-none"}
            {$oBox->getTitle()}<span class="ml-3 float-right"><i class="fas fa-chevron-down"></i></span>
        {/link}
        <div class="h4 font-weight-bold mb-2 d-none d-md-flex">{$oBox->getTitle()}</div>
        <hr class="mt-0 mb-4 d-none d-md-flex">
        {block name='boxes-box-linkgroups-content'}
            {collapse
                class="d-md-flex"
                visible=false
                id="crd-cllps-{$oBox->getID()}"
                aria=["labelledby"=>"#crd-hdr-{$oBox->getID()}"]}
                    {nav vertical=true class="ml-2"}
                    {block name='boxes-box-linkgroups-include-linkgroups-recursive'}
                        {include file='snippets/linkgroup_recursive.tpl' linkgroupIdentifier=$oBox->getLinkGroupTemplate() dropdownSupport=true  tplscope='box'}
                    {/block}
                    {/nav}
            {/collapse}
        {/block}
    {/card}
    <hr class="my-3 d-flex d-md-none">
{/block}
