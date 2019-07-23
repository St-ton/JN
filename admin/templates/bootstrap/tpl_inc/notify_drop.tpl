{if $notifications->count() > 0}
    {$notifyTypes = [0 => 'info', 1 => 'warning', 2 => 'danger']}
    <a href="#" class="dropdown-toggle parent" data-toggle="dropdown">
        <span class="fa-layers fa-fw">
            <span class="fas fa-bell"></span>
            {if $notifications->count() > 0}<span class="fas fa-circle text-{$notifyTypes[$notifications->getHighestType()]}" data-fa-transform="shrink-8 right-9 up-6"></span>{/if}
        </span>
    </a>
    <div class="dropdown-menu dropdown-menu-right dropdown-menu-lg" role="main">
        <span class="dropdown-header">{__('notificationsHeader')}</span>
        <div class="dropdown-divider"></div>
        {foreach $notifications as $notify}
            <div class="dropdown-item-text">
                <span class="icon-text-indent">
                    <div><i class="fa fa-circle text-{$notifyTypes[$notify->getType()]}" aria-hidden="true"></i></div>
                    <a href="{$notify->getUrl()}">
                        <div class="font-weight-bold">{$notify->getTitle()}: </div>
                        {$notify->getDescription()}
                    </a>
                </span>
            </div>
        {/foreach}
    </div>
{/if}