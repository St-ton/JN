{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='boxes-box-linkgroups'}
    {card class="box box-linkgroup mb-7" id="box{$oBox->getID()}" title=$oBox->getTitle()}
        <hr class="mt-0 mb-4">
        {block name='boxes-box-linkgroups-content'}
            {nav vertical=true}
                {block name='boxes-box-linkgroups-include-linkgroups-recursive'}
                    {include file='snippets/linkgroup_recursive.tpl' linkgroupIdentifier=$oBox->getLinkGroupTemplate() dropdownSupport=true  tplscope='box'}
                {/block}
            {/nav}
        {/block}
    {/card}
{/block}
