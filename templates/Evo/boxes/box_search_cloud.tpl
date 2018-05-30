{if $oBox->show()}
    <section class="panel panel-default box box-searchcloud" id="sidebox{$oBox->getID()}">
        <div class="panel-heading">
            <div class="panel-title">{lang key='searchcloud'}</div>
        </div>
        <div class="box-body panel-body">
            <div class="tagbox">
                {foreach $oBox->getItems() as $Suchwolken}
                    <a href="{$Suchwolken->cURLFull}" class="tag{$Suchwolken->Klasse}">{$Suchwolken->cSuche}</a>
                {/foreach}
            </div>
        </div>
    </section>
{/if}