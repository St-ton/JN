{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section='wawisync'}
{include file='tpl_inc/seite_header.tpl' cTitel=__('wawisync') cBeschreibung=__('wawisyncDesc') cDokuURL=__('wawisyncURL')}
<div id="content" class="container-fluid">
    <form action="wawisync.php" method="post">
        {$jtl_token}
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Benutzername/Passwort ändern</h3>
            </div>
            <div class="panel-body">
                <div class="input-group">
                    <span class="input-group-addon">
                        <label for="wawi-user">Benutzer</label>
                    </span>
                    <input id="wawi-user" name="wawi-user" class="form-control" type="text" value="{$wawiuser}" />
                </div>
                <div class="input-group">
                    <span class="input-group-addon">
                        <label for="wawi-pass">Passwort</label>
                    </span>
                    <input id="wawi-pass" name="wawi-pass" class="form-control" type="password" value="{$wawipass}" />
                </div>
            </div>
            <div class="panel-footer">
                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {__('save')}</button>
            </div>
        </div>
    </form>
</div>
{include file='tpl_inc/footer.tpl'}
