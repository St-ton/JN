{if $notifications->totalCount() > 0}
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
        <div class="dropdown-header subheading1">
            <a href="#"><i data-toggle="tooltip" title="{__('Refresh all notifications')}" class="fa fa-refresh pull-right refresh-notify" aria-hidden="true"></i></a>
            {__('notificationsHeader')}
        </div>
        <div class="dropdown-divider"></div>
        {foreach $notifications as $notify}
            {if !$notify->isIgnored()}
                <div{if $notify->getHash() !== ''} id="{$notify->getHash()}"{/if}>
                    <div class="dropdown-header">
                        {if $notify->getHash() !== null}
                            <button type="button" class="close pull-right close-notify" aria-label="Close" data-hash="{$notify->getHash()}">
                                <span aria-hidden="true"  data-toggle="tooltip" title="{__('Mark notification as read')}">&times;</span>
                            </button>
                        {/if}
                        <i class="fa fa-circle text-{$notifyTypes[$notify->getType()]}" aria-hidden="true"></i>
                        {$notify->getTitle()}
                    </div>
                    <div class="dropdown-item-text">
                        {if $notify->getUrl() !== null}<a href="{$notify->getUrl()}">{/if}
                            {$notify->getDescription()}
                        {if $notify->getUrl() !== null}</a>{/if}
                    </div>
                    {if !$notify@last}
                    <div class="dropdown-divider"></div>
                    {/if}
                </div>
            {/if}
        {/foreach}
        {if $notifications->count() != $notifications->totalCount()}
            {if $notifications->count() > 0}
                <div class="dropdown-divider"></div>
            {/if}
            <div class="dropdown-item-text">
                <a href="#" class="showall-notify" data-toggle="tooltip" title="{__('Mark all notifications as unread')}">{__('showAll')}</a>
            </div>
        {/if}
    </div>
{/if}
<script>
    {literal}
    var notificationActionTimer = null;
    $('#notify-drop')
        .on('click', '.close-notify', function (e) {
            e.stopPropagation();
            ioCall('notificationAction', ['dismiss', $(this).data('hash')], function () {
                if (notificationActionTimer !== null) {
                    window.clearTimeout(notificationActionTimer);
                }
                notificationActionTimer = window.setTimeout(function () {
                    notificationActionTimer = null;
                    ioCall('notificationAction', ['update'], undefined, undefined, undefined, true);
                }, 1500);
            }, undefined, undefined, true);
        })
        .on('click', '.refresh-notify', function () {
            ioCall('notificationAction', ['refresh'], undefined, undefined, undefined, true);
        })
        .on('click', '.showall-notify', function () {
            ioCall('notificationAction', ['reset'], undefined, undefined, undefined, true);
        });
    {/literal}
</script>
