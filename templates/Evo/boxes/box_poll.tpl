{if $oBox->show()}
    <section class="panel panel-default box box-poll" id="sidebox{$oBox->getID()}">
        <div class="panel-heading">
            <div class="panel-title">{lang key='BoxPoll'}</div>
        </div>
        <div class="box-body">
            <ul class="nav nav-list tree">
                {foreach $oBox->getItems() as $oUmfrageItem}
                    <li><a href="{$oUmfrageItem->cURLFull}">{$oUmfrageItem->cName}</a></li>
                {/foreach}
            </ul>
        </div>
    </section>
{/if}
