{include file='tpl_inc/header.tpl'}
<h1>Upgrade!</h1>
{form id="upgrade-form" method="post"}
    {include file="tpl_inc/upgrade_channels.tpl"}

    <script>
        document.addEventListener( 'DOMContentLoaded', function() {
            $('#upgrade-form').on('change', '#channels', function (data) {
                let value = $(data.currentTarget).val();
                console.log('Changed!', value);
                ioCall('changeUpgradeChannel', [value], function (response) {
                    console.log('got data; ', response);
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
