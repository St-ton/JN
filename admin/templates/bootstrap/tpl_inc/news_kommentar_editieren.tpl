{include file='tpl_inc/seite_header.tpl' cTitel=__('newsCommentEdit')}
<div id="content" class="container-fluid2">
    <form name="umfrage" method="post" action="news.php" class="navbar-form">
        {$jtl_token}
        <input type="hidden" name="news" value="1" />
        <input type="hidden" name="nkedit" value="1" />
        {if isset($cTab)}
            <input type="hidden" name="tab" value="{$cTab}" />
        {/if}
        {if isset($nFZ) && $nFZ == 1}
            <input name="nFZ" type="hidden" value="1">
        {/if}
        {if isset($cSeite)}
            <input type="hidden" name="{if isset($cTab) && $cTab === 'aktiv'}s2{else}s1{/if}" value="{$cSeite}" />
        {/if}
        <input type="hidden" name="kNews" value="{$oNewsKommentar->getNewsID()}" />
        <input type="hidden" name="kNewsKommentar" value="{$oNewsKommentar->getID()}" />
        <div class="card">
            <div class="card-header">
                <div class="subheading1">{$oNewsKommentar->getName()} - {__('newsCommentEdit')}</div>
            </div>
            <div class="table-responsive card-body">
                <table class="list table" id="formtable">
                    <tr>
                        <td><label for="cName">{__('visitors')}</label></td>
                        <td>
                            <input id="cName" name="cName" class="form-control" type="text" value="{$oNewsKommentar->getName()}" />
                            {if $oNewsKommentar->getCustomerID() === 0}
                                &nbsp;({$oNewsKommentar->getMail()})
                            {/if}
                        </td>
                    </tr>
                    <tr>
                        <td><label for="cKommentar">{__('text')}</label></td>
                        <td>
                            <textarea id="cKommentar" class="ckeditor form-control" name="cKommentar" rows="15" cols="60">{$oNewsKommentar->getText()}</textarea>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="card-footer">
                <span class="btn-group">
                    <button name="newskommentarsavesubmit" type="submit" value="{__('save')}" class="btn btn-primary"><i class="fa fa-save"></i> {__('save')}</button>
                    <a class="btn btn-danger" href="news.php{if isset($cBackPage)}?{$cBackPage}{elseif isset($cTab)}?tab={$cTab}{/if}"><i class="fa fa-exclamation"></i> {__('Cancel')}</a>
                </span>
            </div>
        </div>
    </form>
</div>
