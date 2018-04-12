{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section='opc'}
{include file='tpl_inc/seite_header.tpl' cTitel=#opc# cBeschreibung=#opcDesc# cDokuURL=#opcUrl#}

<ul class="nav nav-tabs">
    <li class="active">
        <a data-toggle="tab" href="#portlets">{#opcPortlets#}</a>
    </li>
</ul>

<div class="tab-content">
    <div class="tab-pane fade active in" id="portlets">
        <div class="panel panel-default">
            {foreach $portlets as $portlet}
                <div>
                    {$portlet->getModel()->getTitle()}
                </div>
            {/foreach}
        </div>
    </div>
</div>

{*
{if $links|@count > 0 && $links}
    {include file='tpl_inc/pagination.tpl' oPagination=$oPagiCmsLinks cAnchor='cmsLinks'}
    <div class="panel panel-default">
        {$jtl_token}
        <input type="hidden" name="cmsLinks" value="1" />
        <div class="table-responsive">
            <table class="list table table-hover">
                <thead>
                <tr>
                    <th class="tleft">interne ID</th>
                    <th class="tleft">URL</th>
                    <th class="tleft">last modified</th>
                    <th class="tleft">actions</th>
                </tr>
                </thead>
                <tbody>
                {foreach name=cmsLinks from=$links item=oLink}
                    <tr>
                        <td>{$oLink->kPage}</td>
                        <td>{$oLink->cPageURL}</td>
                        <td>{$oLink->dLastModified}</td>
                        <td>
                            <a href="{$oLink->cPageURL}" target="_blank" class="btn btn-default"
                               title="öffnet die ausgewählte Seite in einem neuen Fenster">
                                <i class="fa fa-external-link"></i> Shopseite öffnen
                            </a>

                            <button class="btn btn-default preview-btn" data-src="{$URL_SHOP}{$oLink->cPageURL}" data-toggle="modal"
                                    data-target="#previewModal">Vorschau
                            </button>
                        </td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        </div>
    </div>

    <div id="previewModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">×</button>
                    <h3>Preview</h3>
                </div>
                <div class="modal-body">
                    <iframe id="previewFrame" src="" style="zoom:0.60" width="99.6%" height="850" frameborder="0"></iframe>
                </div>
                <div class="modal-footer">
                    <button class="btn" data-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>
{else}
    <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
{/if}

<script>
    {literal}
        $('#previewModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var frameSrc = button.data('src');

            var modal = $(this);
            modal.find('#previewFrame').attr('src', frameSrc);
        });
    {/literal}
</script>
*}

{include file='tpl_inc/footer.tpl'}