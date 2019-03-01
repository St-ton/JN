{include file='tpl_inc/seite_header.tpl' cTitel=__('bearbeiteBewertung')}
<div id="content" class="container-fluid">
    <form name="umfrage" method="post" action="bewertung.php">
        {$jtl_token}
        <input type="hidden" name="bewertung_editieren" value="1" />
        <input type="hidden" name="tab" value="{$cTab}" />
        {if isset($nFZ) && $nFZ == 1}<input name="nFZ" type="hidden" value="1">{/if}
        <input type="hidden" name="kBewertung" value="{$oBewertung->kBewertung}" />

        <table class="kundenfeld table" id="formtable">
            <tr>
                <td><label for="cName">{__('customerName')}:</label></td>
                <td><input class="form-control" id="cName" name="cName" type="text" value="{$oBewertung->cName}" /></td>
            </tr>
            <tr>
                <td><label for="cTitel">{__('name')}:</label></td>
                <td><input class="form-control" id="cTitel" name="cTitel" type="text" value="{$oBewertung->cTitel}" /></td>
            </tr>
            <tr>
                <td><label for="nSterne">{__('ratingStars')}:</label></td>
                <td>
                    <select id="nSterne" name="nSterne" class="form-controlcombo">
                        {for $i=1 to 5}
                            <option value="{$i}"{if (int)$oBewertung->nSterne === $i} selected{/if}>{$i}</option>
                        {/for}
                    </select>
                </td>
            </tr>
            <tr>
                <td><label for="cText">{__('ratingText')}:</label></td>
                <td><textarea id="cText" class="ckeditor" name="cText" rows="15" cols="60">{$oBewertung->cText}</textarea></td>
            </tr>
            <tr>
                <td><label for="cAntwort">{__('ratingReply')}:</label></td>
                <td><textarea id="cAntwort" class="ckeditor" name="cAntwort" rows="15" cols="60">{$oBewertung->cAntwort}</textarea></td>
            </tr>
        </table>
        <div class="save_wrapper">
            <button name="bewertungsubmit" type="submit" value="{__('save')}" class="btn btn-primary"><i class="fa fa-save"></i> {__('save')}</button>
        </div>
    </form>
</div>