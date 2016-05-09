{include file='tpl_inc/seite_header.tpl' cTitel=#newsCommentEdit#}
<div id="content" class="container-fluid2">
    <form name="umfrage" method="post" action="news.php" class="navbar-form">
        {$jtl_token}
        <input type="hidden" name="news" value="1" />
        <input type="hidden" name="nkedit" value="1" />
        <input type="hidden" name="tab" value="inaktiv" />
        {if isset($nFZ) && $nFZ == 1}
            <input name="nFZ" type="hidden" value="1">
        {/if}
        <input type="hidden" name="kNews" value="{$oNewsKommentar->kNews}" />
        <input type="hidden" name="kNewsKommentar" value="{$oNewsKommentar->kNewsKommentar}" />
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">{$oNewsKommentar->cName} - {#newsCommentEdit#}</h3>
            </div>
            <table class="list table" id="formtable">
                <tr>
                    <td><label for="cName">{#newsHeadline#}</label></td>
                    <td><input id="cName" name="cName" class="form-control" type="text" value="{$oNewsKommentar->cName}" /></td>
                </tr>
                <tr>
                    <td><label for="cKommentar">{#newsText#}</label></td>
                    <td>
                        <textarea id="cKommentar" class="ckeditor form-control" name="cKommentar" rows="15" cols="60">{$oNewsKommentar->cKommentar}</textarea>
                    </td>
                </tr>
            </table>
            <div class="panel-footer">
                <button name="newskommentarsavesubmit" type="submit" value="{#newsSave#}" class="btn btn-primary"><i class="fa fa-save"></i> {#newsSave#}</button>
            </div>
        </div>
    </form>
</div>