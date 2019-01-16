{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section='kundenimport'}
{include file='tpl_inc/seite_header.tpl' cTitel=__('newsletterMail') cBeschreibung=__('newsletterMailDesc') cDokuURL=__('newsletterURL')}
<div id="content" class="container-fluid">
    <form name="kundenimporter" method="post" action="newsletterimport.php" enctype="multipart/form-data">
        {$jtl_token}
        <input type="hidden" name="newsletterimport" value="1" />
        <div class="settings">
            <div class="input-group">
                <span class="input-group-addon">
                    <label for="kSprache">{__('language')}</label>
                </span>
                <span class="input-group-wrap">
                    <select name="kSprache" id="kSprache" class="form-control combo">
                        {foreach $sprachen as $sprache}
                            <option value="{$sprache->kSprache}">{$sprache->cNameDeutsch}</option>
                        {/foreach}
                    </select>
                </span>
            </div>
            <div class="input-group">
                <span class="input-group-addon">
                    <label for="csv">{__('csvFile')}</label>
                </span>
                <span class="input-group-wrap">
                    <input class="form-control" type="file" name="csv" id="csv"  tabindex="1" />
                </span>
            </div>
            <p class="submit">
                <button type="submit" value="{__('import')}" class="btn btn-primary">{__('import')}</button>
            </p>
        </div>
    </form>
</div>
{include file='tpl_inc/footer.tpl'}