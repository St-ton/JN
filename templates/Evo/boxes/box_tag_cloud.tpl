{if (isset($Boxen.Tagwolke->Tagbegriffe) && $Boxen.Tagwolke->Tagbegriffe|@count > 0) || (isset($oBox->Tagbegriffe) && $oBox->Tagbegriffe|@count > 0)}
    <section class="panel panel-default box box-tagcloud" id="sidebox{$oBox->kBox}">
        <div class="panel-heading">
            <div class="panel-title">{lang key='tagcloud'}</div>
        </div>
        <div class="box-body panel-body">
            <div class="tagbox">
                {if isset($oBox->Tagbegriffe)}
                    {assign var=from value=$oBox->Tagbegriffe}
                {else}
                    {assign var=from value=$Boxen.Tagwolke->Tagbegriffe}
                {/if}
                {foreach name=suchwolken from=$from item=Wolke}
                    <a href="{$Wolke->cURLFull}" class="tag{$Wolke->Klasse}">{$Wolke->cName}</a>
                {/foreach}
            </div>
        </div>
    </section>
{/if}