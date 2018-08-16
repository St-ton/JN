{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if $oBox->show()}
    <section class="panel panel-default box box-linkgroup" id="box{$oBox->getID()}">
        <div class="panel-heading">
            <div class="panel-title">{$oBox->getLinkGroupTemplate()}</div>
        </div>
        <div class="box-body nav-panel">
            <ul class="nav nav-list">
                {include file='snippets/linkgroup_recursive.tpl' linkgroupIdentifier=$oBox->getLinkGroupTemplate() dropdownSupport=true  tplscope='box'}
            </ul>
        </div>
    </section>
{/if}
