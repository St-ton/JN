{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{card class="box box-linkgroup mb-7" id="box{$oBox->getID()}" title="{$oBox->getTitle()}"}
    <hr class="mt-0 mb-4">
    {nav vertical=true}
        {include file='snippets/linkgroup_recursive.tpl' linkgroupIdentifier=$oBox->getLinkGroupTemplate() dropdownSupport=true  tplscope='box'}
    {/nav}
{/card}
