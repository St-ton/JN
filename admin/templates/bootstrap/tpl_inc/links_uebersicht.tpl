<script type="text/javascript">
    function confirmDelete() {ldelim}
        return confirm('Möchten Sie den Link wirklich löschen?\nBitte beachten Sie, dass auch untergeordnete Links gelöscht werden!');
    {rdelim}
</script>

{include file='tpl_inc/seite_header.tpl' cTitel=#links# cBeschreibung=#linksDesc# cDokuURL=#linksUrl#}
<div id="content" class="container-fluid">
    <div class="block container2">
        <form action="links.php" method="post">
            {$jtl_token}
            <button class="btn btn-primary add" name="neuelinkgruppe" value="1"><i class="fa fa-share"></i> {#newLinkGroup#}</button>
        </form>
    </div>
    <div class="panel-group accordion" id="accordion2" role="tablist" aria-multiselectable="true">
        {foreach $linkgruppen as $linkgruppe}
            {assign var=lgName value='linkgroup-'|cat:$linkgruppe->getID()}
            <div class="panel panel-default">
                <div class="panel-heading accordion-heading">
                    <h3 class="panel-title" id="heading-{$lgName}">
                        <span class="pull-left">
                            <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapse{$lgName}">
                                <span class="accordion-toggle-icon"><i class="fa fa-plus"></i></span> {$linkgruppe->getName()} ({#linkGroupTemplatename#}: {$linkgruppe->getTemplate()})
                            </a>
                        </span>
                    </h3>
                    <form method="post" action="links.php">
                        {$jtl_token}
                        <span class="btn-group pull-right">
                            <button name="kLinkgruppe" value="{$linkgruppe->getID()}" class="btn btn-primary" title="{#modify#}"><i class="fa fa-edit"></i></button>
                            <button name="addlink" value="{$linkgruppe->getID()}" class="btn btn-default add" title="{#addLink#}">{#addLink#}</button>
                            <button name="delconfirmlinkgruppe" value="{$linkgruppe->getID()}" class="btn btn-danger" title="{#linkGroup#} {#delete#}"><i class="fa fa-trash"></i></button>
                        </span>
                    </form>
                </div>
                <div id="collapse{$lgName}" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading-{$lgName}">
                    {if $linkgruppe->getLinks()->count() > 0}
                        <table class="table">
                            {include file="tpl_inc/links_uebersicht_item.tpl" list=$linkgruppe->getLinks() id=$linkgruppe->getID()}
                        </table>
                    {else}
                        <p class="alert alert-info" style="margin:10px;"><i class="fa fa-info-circle"></i> {#noData#}</p>
                    {/if}
                </div>
            </div>
        {/foreach}
    </div>{* /accordion *}
    <div class="block container2">
        <form action="links.php" method="post">
            {$jtl_token}
            <button class="btn btn-primary add" name="neuelinkgruppe" value="1"><i class="fa fa-share"></i> {#newLinkGroup#}</button>
        </form>
    </div>
</div>