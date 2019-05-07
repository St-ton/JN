{include file='tpl_inc/seite_header.tpl' cTitel=__('deleteLinkGroup')}
<div id="content">
    <form method="post" action="links.php">
        {$jtl_token}
        <input type="hidden" name="action" value="confirm-delete" />
        <input type="hidden" name="kLinkgruppe" value="{$linkGroup->getID()}" />

        <div class="alert alert-danger">
            <p><strong>{__('danger')}</strong>: {__('dangerDeleteAllLinksInLinkGroup')}</p>
            {if $affectedLinkNames|count > 0}
                <p>{__('dangerDeleteLinksAlso')}:</p>
                <ul class="list">
                    {foreach $affectedLinkNames as $link}
                        <li>{$link}</li>
                    {/foreach}
                </ul>
            {/if}
            <p>{{__('sureDeleteLinkGroup')}|sprintf:{$linkGroup->getName()}}</p>
        </div>
        <div class="btn-group">
            <button type="submit" name="confirmation" value="1" class="btn btn-danger">{__('yes')}</button>
            <button type="submit" name="confirmation" value="0" class="btn btn-default">{__('no')}</button>
        </div>
    </form>
</div>
