{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
<section class="panel panel-default box box-filter-category" id="sidebox{$oBox->getID()}">
    <div class="panel-heading">
        <div class="panel-title">{$oBox->getTitle()}</div>
    </div>
    <div class="box-body">
        {include file='snippets/filter/genericFilterItem.tpl' filter=$oBox->getItems()}
    </div>
</section>
