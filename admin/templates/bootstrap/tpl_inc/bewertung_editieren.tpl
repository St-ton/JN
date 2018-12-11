{include file='tpl_inc/seite_header.tpl' cTitel=__("bearbeiteBewertung")}
<div id="content" class="container-fluid">
    <form name="umfrage" method="post" action="bewertung.php">
        {$jtl_token}
        <input type="hidden" name="bewertung_editieren" value="1" />
        <input type="hidden" name="tab" value="{$cTab}" />
        {if isset($nFZ) && $nFZ == 1}<input name="nFZ" type="hidden" value="1">{/if}
        <input type="hidden" name="kBewertung" value="{$oBewertung->kBewertung}" />

        <table class="kundenfeld table" id="formtable">
            <tr>
                <td><label for="cName">{__("customerName")}:</label></td>
                <td><input class="form-control" id="cName" name="cName" type="text" value="{$oBewertung->cName}" /></td>
            </tr>
            <tr>
                <td><label for="cTitel">{__("ratingTitle")}:</label></td>
                <td><input class="form-control" id="cTitel" name="cTitel" type="text" value="{$oBewertung->cTitel}" /></td>
            </tr>
            <tr>
                <td><label for="nSterne">{__("ratingStars")}:</label></td>
                <td>
                    <select id="nSterne" name="nSterne" class="form-controlcombo">
                        <option value="1"{if $oBewertung->nSterne == 1} selected{/if}>1</option>
                        <option value="2"{if $oBewertung->nSterne == 2} selected{/if}>2</option>
                        <option value="3"{if $oBewertung->nSterne == 3} selected{/if}>3</option>
                        <option value="4"{if $oBewertung->nSterne == 4} selected{/if}>4</option>
                        <option value="5"{if $oBewertung->nSterne == 5} selected{/if}>5</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td><label for="cText">{__("ratingText")}:</label></td>
                <td><textarea id="cText" class="ckeditor" name="cText" rows="15" cols="60">{$oBewertung->cText}</textarea></td>
            </tr>
            <tr>
                <td><label for="cAntwort">{__("ratingReply")}:</label></td>
                <td><textarea id="cAntwort" class="ckeditor" name="cAntwort" rows="15" cols="60">{$oBewertung->cAntwort}</textarea></td>
            </tr>
        </table>
        <div class="save_wrapper">
            <button name="bewertungsubmit" type="submit" value="{__("ratingSave")}" class="btn btn-primary"><i class="fa fa-save"></i> {__("ratingSave")}</button>
        </div>
    </form>
</div>