{include file='tpl_inc/header.tpl'}
<h1>Upgrade!</h1>
{form id="upgrade-form" method="post"}
    <span class="version">{__('Current version:')}</span> <span class="badge badge-primary">{$smarty.const.APPLICATION_VERSION}</span>
    {include file="tpl_inc/upgrade_channels.tpl"}

    <script>
        document.addEventListener( 'DOMContentLoaded', function() {
            $('#upgrade-form').on('change', '#channels', function (data) {
                let value = $(data.currentTarget).val();
                ioCall('changeUpgradeChannel', [value], function (response) {
                    $('#wrap-channels').replaceWith(response.channels);
                    $('#wrap-newerversions').replaceWith(response.upgrades);
                });
            })
        });
    </script>

    {include file="tpl_inc/upgrade_upgrades.tpl"}
    <hr>
    {button type='submit' name='upgrade' value='1' block=true variant='primary'}
        {__('update')}
    {/button}
{/form}
{include file='tpl_inc/footer.tpl'}
