{if $notifications->count() > 0}
    {$notifyTypes = [0 => 'info', 1 => 'warning', 2 => 'danger']}
    <a href="#" class="dropdown-toggle parent" data-toggle="dropdown">
        <span class="badge-notify btn-{$notifyTypes[$notifications->getHighestType()]}">{$notifications->count()}</span>
        <i class="fa fa-bell"></i>
    </a>
    <div class="dropdown-menu dropdown-menu-right dropdown-menu-lg" role="main">
        <span class="dropdown-header">Mitteilungen</span>
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