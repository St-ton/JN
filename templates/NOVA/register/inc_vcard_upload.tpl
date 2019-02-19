{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if $Einstellungen.kunden.kundenregistrierung_vcardupload === 'Y'}
    <div id="vcard-upload" class="clearfix">
        {dropdown
            class="float-right"
            variant="secondary btn-sm d-flex align-items-center"
            title="{lang key='uploadVCard' section='account data'}"
            text="<i class='fas fa-file-alt'></i>&nbsp;{lang key='uploadVCard' section='account data'}"
            right=true
        }
            {form enctype="multipart/form-data" method="POST" action="{get_static_route id=$id}{if isset($checkout)}?checkout={$checkout}{/if}" class="dropdown-item-text"}
                <fieldset>
                    {*{inputfile class="file-loading mb-2 custom-file-input" required=true accept="text/vcard" name="vcard"}*}
                    <div class="custom-file mb-3">
                        <input type="file" class="custom-file-input" name="vcard" required>
                        <label class="custom-file-label" for="customFile">Choose file</label>
                    </div>
                    {button class="btn-block" type="submit"}
                        <i class="fas fa-file-alt"></i>&nbsp;{lang key='uploadVCard' section='account data'}
                    {/button}
                </fieldset>
            {/form}
        {/dropdown}
    </div>
{/if}
