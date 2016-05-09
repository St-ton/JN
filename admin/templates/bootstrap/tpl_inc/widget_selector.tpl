<div class="widget_settings list-group">
    {foreach from=$oAvailableWidget_arr item=oAvailableWidget}
        <div class="widget_item list-group-item">
            <p class="title">{$oAvailableWidget->cTitle}</p>
            <p class="desc">{$oAvailableWidget->cDescription}</p>
            <a href="#" class="add" ref="{$oAvailableWidget->kWidget}"><i class="fa fa-plus-square fa-2x"></i></a>
        </div>
    {/foreach}
    {if $oAvailableWidget_arr|@count == 0}
        <div class="widget_item">
            <p class="title">Keine weiteren Widgets vorhanden.</p>
        </div>
    {/if}
</div>
