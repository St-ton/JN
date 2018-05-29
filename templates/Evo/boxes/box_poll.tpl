{if isset($oBox->oUmfrage_arr) && $oBox->oUmfrage_arr|@count > 0}
    <section class="panel panel-default box box-poll" id="sidebox{$oBox->kBox}">
        <div class="panel-heading">
            <div class="panel-title">{lang key='BoxPoll'}</div>
        </div>
        <div class="box-body">
            <ul class="nav nav-list tree">
                {foreach name=umfragen from=$oBox->oUmfrage_arr item=oUmfrageItem}
                    <li><a href="{$oUmfrageItem->cURLFull}">{$oUmfrageItem->cName}</a></li>
                {/foreach}
            </ul>
        </div>
    </section>
{elseif isset($Boxen.Umfrage->oUmfrage_arr) && $Boxen.Umfrage->oUmfrage_arr|@count > 0}
    <section class="panel panel-default box box-poll" id="sidebox{$oBox->kBox}">
        <div class="panel-heading">
            <div class="panel-title">{lang key='BoxPol'}</div>
        </div>
        <div class="box-body">
            <ul class="nav nav-list tree">
                {foreach name=umfragen from=$Boxen.Umfrage->oUmfrage_arr item=oUmfrageItem}
                    <li>
                        <a href="{$oUmfrageItem->cURLFull}">{$oUmfrageItem->cName}</a>
                    </li>
                {/foreach}
            </ul>
        </div>
    </section>
{/if}