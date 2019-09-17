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
            class="text-decoration-none font-weight-bold mb-2 d-block d-md-none dropdown-toggle"}
            {$oBox->getTitle()}
        {/link}
        <div class="h4 font-weight-bold mb-3 d-none d-md-flex text-uppercase">{$oBox->getTitle()}</div>
        {block name='boxes-box-linkgroups-content'}
            {collapse
                class="d-md-flex"
                visible=false
                id="crd-cllps-{$oBox->getID()}"
                aria=["labelledby"=>"crd-hdr-{$oBox->getID()}"]}
                    {nav vertical=true}
                    {block name='boxes-box-linkgroups-include-linkgroups-recursive'}
                        {include file='snippets/linkgroup_recursive.tpl' linkgroupIdentifier=$oBox->getLinkGroupTemplate() dropdownSupport=true  tplscope='box'}
                    {/block}
                    {/nav}
            {/collapse}
        {/block}
    {/card}
    <hr class="my-3 d-flex d-md-none">
{/block}
