<script type="text/javascript">
    function confirmDelete() {ldelim}
        return confirm('{__('sureDeleteLink')|replace:"\n":''}');
    {rdelim}
</script>

{include file='tpl_inc/seite_header.tpl' cTitel=__('links') cBeschreibung=__('linksDesc') cDokuURL=__('linksUrl')}
<div id="content" class="container-fluid">
    <div class="block container2">
        <form action="links.php" method="post">
            {$jtl_token}
            <button class="btn btn-primary add" name="action" value="create-linkgroup"><i class="fa fa-share"></i> {__('newLinkGroup')}</button>
        </form>
    </div>
    <div class="panel-group accordion" id="accordion2" role="tablist" aria-multiselectable="true">
        {foreach $linkgruppen as $linkgruppe}
            {if $linkgruppe->getID() < 0 && $linkgruppe->getLinks()->count() === 0}
                {continue}
            {/if}
            {assign var=lgName value='linkgroup-'|cat:$linkgruppe->getID()}
            {assign var=missingTranslations value=$linkAdmin->getMissingLinkGroupTranslations($linkgruppe->getID())}
            <div class="panel panel-{if $linkgruppe->getID() > 0}default{else}danger{/if}">
                <div class="panel-heading accordion-heading">
                    <h3 class="panel-title" id="heading-{$lgName}">
                        <span class="pull-left">
                            <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapse{$lgName}"{if $missingTranslations|count > 0} title="{__('missingTranslations')}: {$missingTranslations|count}"{/if}>
                                <span class="accordion-toggle-icon"><i class="fa fa-plus"></i></span>
                                {if $linkgruppe->getID() > 0}
                                    {$linkgruppe->getName()} ({__('linkGroupTemplatename')}: {$linkgruppe->getTemplate()})
                                {else}
                                    {__('linksWithoutLinkGroup')}
                                {/if}
                                {if $missingTranslations|count > 0}<i class="fa fa-warning"></i>{/if}
                            </a>
                        </span>
                    </h3>
                    <form method="post" action="links.php">
                        {$jtl_token}
                        <span class="btn-group pull-right">
                            {if $linkgruppe->getID() > 0}
                                <input type="hidden" name="kLinkgruppe" value="{$linkgruppe->getID()}">
                                <button name="action" value="edit-linkgroup" class="btn btn-primary" title="{__('modify')}">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button name="action" value="add-link-to-linkgroup" class="btn btn-default add" title="{__('addLink')}">
                                    {__('addLink')}
                                </button>
                                <button name="action" value="delete-linkgroup" class="btn btn-danger" title="{__('linkGroup')} {__('delete')}">
                                    <i class="fa fa-trash"></i>
                                </button>
                            {/if}
                        </span>
                    </form>
                </div>
                <div id="collapse{$lgName}" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading-{$lgName}">
                    {*{if $missingTranslations|count > 0}*}
                        {*<div class="help-block container">*}
                            {*<p>Achtung: Übersetzungen fehlen!</p>*}
                            {*<ul class="default">*}
                                {*{foreach $missingTranslations as $translation}*}
                                    {*<li>{$translation->cNameDeutsch}</li>*}
                                {*{/foreach}*}
                            {*</ul>*}
                        {*</div>*}
                    {*{/if}*}
                    {if $linkgruppe->getLinks()->count() > 0}
                        <table class="table">
                            {include file='tpl_inc/links_uebersicht_item.tpl' list=$linkgruppe->getLinks() id=$linkgruppe->getID()}
                        </table>
                    {else}
                        <p class="alert alert-info" style="margin:10px;"><i class="fa fa-info-circle"></i> {__('noData')}</p>
                    {/if}
                </div>
            </div>
        {/foreach}
    </div>{* /accordion *}
    <div class="block container2">
        <form action="links.php" method="post">
            {$jtl_token}
            <button class="btn btn-primary add" name="action" value="create-linkgroup"><i class="fa fa-share"></i> {__('newLinkGroup')}</button>
        </form>
    </div>
</div>
