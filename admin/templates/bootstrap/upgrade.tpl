{include file='tpl_inc/header.tpl'}
<h2>{__('System upgrade')}</h2>
{form id="upgrade-form" method="post"}
    <span class="version">{__('Currently installed version:')}</span>
    <span class="badge badge-primary">{$smarty.const.APPLICATION_VERSION}</span>
    {include file="tpl_inc/upgrade_channels.tpl"}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let changelogs = [];
            {foreach $availableVersions as $version}
                {if $version->changelog !== ''}
                    changelogs[{$version->id}] = `{trim($version->changelog)}`;
                {/if}
            {/foreach}
            $('#upgrade-form').on('change', '#channels', function (data) {
                let value = $(data.currentTarget).val();
                ioCall('changeUpgradeChannel', [value], function (response) {
                    $('#wrap-channels').replaceWith(response.channels);
                    $('#wrap-newerversions').replaceWith(response.upgrades);
                    for (let key in response.filtered) {
                        let obj = response.filtered[key];
                        changelogs[obj.id] = obj.changelog;
                    }
                });
            });
            $('#upgrade-form').on('change', '#newerversions', function (data) {
                let value = $(data.currentTarget).val();
                if (typeof changelogs[value] !== 'undefined' && changelogs[value] !== '') {
                    let modal = $('#changelogModal');
                    modal.find('.modal-body').html(changelogs[value]);
                    modal.modal('show');
                }
            });
        });
    </script>
    {include file="tpl_inc/upgrade_upgrades.tpl"}
    <hr>
    {button type='submit' name='upgrade' value='1' block=true variant='primary'}
        {__('Start upgrade')}
    {/button}
{/form}

<hr>
{include file='tpl_inc/upgrade_rollback.tpl'}

<div class="log-list">
    <code>
        {if count($logs) > 0}
            <hr>
            {foreach $logs as $log}
                {$log}
            {/foreach}
            <hr>
        {/if}
        {if count($errors) > 0}
            <hr>
            {foreach $errors as $error}
                {$error}
            {/foreach}
            <hr>
        {/if}
    </code>
</div>

<div id="changelogModal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">{__('Changelog')}</h2>
                <button type="button" class="close" data-dismiss="modal">
                    <i class="fal fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
            </div>
            <div class="modal-footer">
                <div class="row">
                    <div class="col-sm-6 col-xl-auto">
                        <button type="button" class="btn btn-primary" name="ok" data-dismiss="modal">
                            <i class="fal fa-check text-success"></i>&nbsp;{__('ok')}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{include file='tpl_inc/footer.tpl'}
