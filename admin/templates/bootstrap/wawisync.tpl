{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section='wawisync'}
{include file='tpl_inc/seite_header.tpl' cTitel=__('wawisync') cBeschreibung=__('wawisyncDesc') cDokuURL=__('wawisyncURL')}
<div id="content" class="container-fluid">
    <form action="wawisync.php" method="post">
        {$jtl_token}
        <div class="card">
            <div class="card-header">
                <div class="card-title">{__('username')}/{__('password')} {__('change')}</div>
            </div>
            <div class="card-body">
                <div class="input-group">
                    <span class="input-group-addon">
                        <label for="wawi-user">{__('user')}</label>
                    </span>
                    <input id="wawi-user" name="wawi-user" class="form-control" type="text" value="{$wawiuser}" />
                </div>
                <div class="input-group">
                    <span class="input-group-addon">
                        <label for="wawi-pass">{__('password')}</label>
                    </span>
                    <input id="wawi-pass" name="wawi-pass" class="form-control" type="password" value="{$wawipass}" />
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {__('save')}</button>
            </div>
        </div>
    </form>
</div>
{include file='tpl_inc/footer.tpl'}
