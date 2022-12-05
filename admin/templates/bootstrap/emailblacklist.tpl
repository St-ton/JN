{include file='tpl_inc/header.tpl'}
{include file='tpl_inc/seite_header.tpl' cTitel=__('emailblacklist') cBeschreibung=__('emailblacklistDesc') cDokuURL=__('emailblacklistURL')}
<div id="content">
    <form method="post" action="{$adminURL}{$route}">
        {$jtl_token}
        <input type="hidden" name="einstellungen" value="1" />
        <input type="hidden" name="emailblacklist" value="1" />
        <div id="settings">
            {include file='tpl_inc/config_sections.tpl'}
        </div>

        <div class="card">
            <div class="card-header">
                <div class="subheading1">{__('emailblacklistEmail')} {__('emailblacklistSeperator')}</div>
                <hr class="mb-n3">
            </div>
            <div class="card-body">
                <textarea class="form-control" name="cEmail" cols="50" rows="10" placeholder="{__('emailblacklistPlaceholder')}">{foreach $blacklist as $item}{$item->cEmail}{if !$item@last};{/if}{/foreach}</textarea>
            </div>
        </div>
        <div class="save-wrapper">
            <div class="row">
                <div class="ml-auto col-sm-6 col-xl-auto">
                    <button name="speichern" type="submit" value="{__('save')}" class="btn btn-primary btn-block">
                        {__('saveWithIcon')}
                    </button>
                </div>
            </div>
        </div>
    </form>
    {if count($blocked) > 0}
        <div class="card">
            <div class="card-header">
                <div class="subheading1">{__('emailblacklistBlockedEmails')}</div>
                <hr class="mb-n3">
            </div>
            <div class="card-body">
                {foreach $blocked as $item}
                    {$item->cEmail} ({$item->dLetzterBlock})<br />
                {/foreach}
            </div>
        </div>
    {/if}
</div>
{include file='tpl_inc/footer.tpl'}
