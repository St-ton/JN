{if $notifications->count() > 0}
    {$notifyTypes = [0 => 'info', 1 => 'warning', 2 => 'danger']}
    <a href="#" class="nav-link text-primary px-2" data-toggle="dropdown">
        <span class="fa-layers fa-fw has-notify-icon">
            <span class="fas fa-bell"></span>
            <span class="fa-stack">
                <span class="fas fa-circle fa-stack-2x text-{$notifyTypes[$notifications->getHighestType()]}"></span>
                <strong class="fa-stack-1x">{$notifications->count()}</strong>
            </span>
        </span>
    </a>
    <div class="dropdown-menu dropdown-menu-right dropdown-menu-lg" role="main">
        <span class="dropdown-header">{__('notificationsHeader')}</span>
        <div class="dropdown-divider"></div>
        {foreach $notifications as $notify}
            <div class="dropdown-item-text">
                <span class="icon-text-indent">
                    <div><i class="fa fa-circle text-{$notifyTypes[$notify->getType()]}" aria-hidden="true"></i></div>
                    {if $notify->getUrl() !== null}<a href="{$notify->getUrl()}">{/if}
                        <div class="font-weight-bold">{$notify->getTitle()}: </div>
                        {$notify->getDescription()}
                    {if $notify->getUrl() !== null}</a>{/if}
                </span>
            </div>
        {/foreach}
    </div>
{/if}
