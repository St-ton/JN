{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if $oBox->show()}
    <section class="panel panel-default box box-filter-manufacturer" id="sidebox{$oBox->getID()}">
        <div class="panel-heading">
            <div class="panel-title">{lang key='manufacturers'}</div>
        </div>
        <div class="box-body">
            {include file='snippets/filter/genericFilterItem.tpl' filter=$oBox->getItems()}
        </div>
    </section>
{/if}
