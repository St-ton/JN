{if isset($oBox->oLinkGruppe)}
    <section class="panel panel-default box box-linkgroup" id="box{$oBox->kBox}">
        <div class="panel-heading">
            <div class="panel-title">{$oBox->oLinkGruppe->getName()}</div>
        </div>
        <div class="box-body nav-panel">
            <ul class="nav nav-list">
                {include file='snippets/linkgroup_recursive.tpl' linkgroupIdentifier=$oBox->oLinkGruppeTemplate dropdownSupport=true  tplscope='box'}
            </ul>
        </div>
    </section>
{/if}