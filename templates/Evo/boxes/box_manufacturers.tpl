{if $oBox->show()}
    <section class="panel panel-default box box-manufacturers" id="sidebox{$oBox->getID()}">
        <div class="panel-heading">
            <div class="panel-title">{lang key='manufacturers'}</div>
        </div>
        {if $oBox->getManufacturers()|@count > 8}
            <div class="box-body">
                <div class="dropdown">
                    <button class="btn btn-default btn-block dropdown-toggle" type="button" id="dropdown-manufacturer" data-toggle="dropdown" aria-expanded="true">
                        {lang key='selectManufacturer'}
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu" role="menu" aria-labelledby="dropdown-manufacturer">
                        {foreach $oBox->getManufacturers() as $hst}
                            <li role="presentation">
                                <a role="menuitem" tabindex="-1" href="{$hst->cSeo}">{$hst->cName|escape:'html'}</a></li>
                        {/foreach}
                    </ul>
                </div>
            </div>
        {else}
            <div class="box-body">
                <ul class="nav nav-list">
                    {foreach $oBox->getManufacturers() as $hst}
                        <li><a href="{$hst->cSeo}" title="{$hst->cName|escape:'html'}">{$hst->cName|escape:'html'}</a></li>
                    {/foreach}
                </ul>
            </div>
        {/if}
    </section>
{/if}
