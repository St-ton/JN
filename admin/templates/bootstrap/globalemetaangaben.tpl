{include file='tpl_inc/header.tpl'}
{include file='tpl_inc/seite_header.tpl' cTitel=__('globalemetaangaben') cBeschreibung=__('globalemetaangabenDesc') cDokuURL=__('globalemetaangabenUrl')}
{assign var=currentLanguage value=''}
<div id="content">
    <div class="card">
        <div class="card-body">
            {include file='tpl_inc/language_switcher.tpl' action=$adminURL|cat:$route}
        </div>
    </div>
    <form method="post" action="{$adminURL}{$route}">
        {$jtl_token}
        <input type="hidden" name="einstellungen" value="1" />
        <div class="settings">
            <div class="card">
                <div class="card-header">
                    <div class="subheading1">{$currentLanguage}</div>
                </div>
                <div class="card-body">
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 text-sm-right" for="Title">{__('title')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <input type="text" class="form-control" id="Title" name="Title" value="{if isset($oMetaangaben_arr.Title)}{$oMetaangaben_arr.Title}{/if}" tabindex="1" />
                        </div>
                    </div>
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 text-sm-right" for="Meta_Description">{__('globalemetaangabenMetaDesc')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <input type="text" class="form-control" id="Meta_Description" name="Meta_Description" value="{if isset($oMetaangaben_arr.Meta_Description)}{$oMetaangaben_arr.Meta_Description}{/if}" tabindex="1" />
                        </div>
                    </div>
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 text-sm-right" for="Meta_Description_Praefix">{__('globalemetaangabenMetaDescPraefix')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <input type="text" class="form-control" id="Meta_Description_Praefix" name="Meta_Description_Praefix" value="{if isset($oMetaangaben_arr.Meta_Description_Praefix)}{$oMetaangaben_arr.Meta_Description_Praefix}{/if}" tabindex="1" />
                        </div>
                    </div>
                </div>
            </div>

            {include file='tpl_inc/config_sections.tpl'}
        </div>

        <div class="card-footer save-wrapper submit">
            <div class="row">
                <div class="ml-auto col-sm-6 col-xl-auto">
                    <button name="speichern" type="submit" value="{__('save')}" class="btn btn-primary btn-block">
                        {__('saveWithIcon')}
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
{include file='tpl_inc/footer.tpl'}
