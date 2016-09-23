{**
 * @copyright (c) 2006-2015 JTL-Software-GmbH, all rights reserved
 * @author JTL-Software-GmbH (www.jtl-software.de)
 *
 * use is subject to license terms
 * http://jtl-software.de/jtlshop3license.html
 *}

{if $Einstellungen.kunden.kundenregistrierung_vcardupload === 'Y'}
<div class="dropdown nav-toggle">
    <a href="#" class="dropdown-toggle btn btn-default{if isset($panel_heading)} heading{/if}" title="{lang key="uploadVCard" section="account data"}" data-toggle="dropdown"><i class="fa fa-file-text-o"></i><span class="hidden-xs">&nbsp;{lang key="uploadVCard" section="account data"}&nbsp;</span><span class="caret"></span></a>
    <ul class="dropdown-menu dropdown-menu-right">
        <li>
            <div class="panel">
                <form class="form panel-body" enctype="multipart/form-data" method="post" action="{get_static_route id=$id}{if isset($checkout)}?checkout={$checkout}{/if}">
                    {$jtl_token}
                    <fieldset>
                        <legend>{lang key="uploadVCard" section="account data"}</legend>
                        <div class="form-group"><input class="form-inline file-upload file-loading" required="required" type="file" accept="text/vcard" name="vcard"></div>
                        <div class="form-group"><button class="btn btn-primary btn-block" type="submit"><i class="fa fa-file-text-o"></i>&nbsp;{lang key="uploadVCard" section="account data"}</button></div>
                    </fieldset>
                </form>
            </div>
        </li>
    </ul>
</div>
{/if}