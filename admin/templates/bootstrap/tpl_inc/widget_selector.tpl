{foreach $oAvailableWidget_arr as $oAvailableWidget}
    <a href="#" class="dropdown-item" data-widget-add="1" onclick="addWidget({$oAvailableWidget->kWidget})">
        <span class="icon-text-indent">
            <span class="icon-hover text-primary">
                <span href="#" class="fal fa-plus"></span>
            </span>
            <span class="font-weight-bold">{$oAvailableWidget->cTitle}</span><br />
            {$oAvailableWidget->cDescription}
        </span>
    </a>
    <div class="dropdown-divider"></div>
{/foreach}
{if $oAvailableWidget_arr|@count == 0}
    <span class="ml-3 font-weight-bold">{__('noMoreWidgets')}</span>
{/if}