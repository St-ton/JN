{include file="./modals/publish.tpl"}

<div id="loaderModal" class="modal fade" tabindex="-1" style="padding-top:25%">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{__('Please wait...')}</h4>
            </div>
            <div class="modal-body">
                <div class="progress">
                    <div class="progress-bar progress-bar-striped progress-bar-info active" style="width:100%">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="errorModal" class="modal fade" tabindex="-1" style="padding-top:25%">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{__('error')}</h4>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger" id="errorAlert">
                    {__('somethingHappend')}
                </div>
            </div>
        </div>
    </div>
</div>

<div id="configModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <i class="fa fa-lg fa-times"></i>
                </button>
                <h4 class="modal-title" id="configModalTitle">{__('Edit Portlet')}</h4>
            </div>
            <form id="configForm">
                <div class="modal-body" id="configModalBody"></div>
                <div class="modal-footer">
                    <div class="btn-group" id="stdConfigButtons">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">{__('Cancel')}</button>
                        <button class="btn btn-primary">{__('Save')}</button>
                    </div>
                    <button type="button" class="btn btn-primary" data-dismiss="modal" id="missingConfigButtons">
                        {__('OK')}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="blueprintModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{__('Save this Portlet as a blueprint')}</h4>
            </div>
            <form id="blueprintForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="blueprintName">{__('Blueprint name')}</label>
                        <input type="text" class="form-control" id="blueprintName" name="blueprintName"
                               value="Neue Vorlage">
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">{__('Cancel')}</button>
                        <button class="btn btn-primary">{__('Save')}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="blueprintDeleteModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{__('Delete Blueprint?')}</h4>
            </div>
            <form id="blueprintDeleteForm">
                <div class="modal-footer">
                    <div class="btn-group">
                        <input type="hidden" id="blueprintDeleteId" name="id" value="">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">{__('cancel')}</button>
                        <button class="btn btn-primary">{__('delete')}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="tourModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{__('help')}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-xs-12">
                        {__('noteInfoInGuide')}
                        <form id="tourForm">
                            <div class="radio">
                                <label class="tour-label">
                                    <input type="radio" name="help-tour" id="helpTour1" value="ht1" checked
                                           class="hidden">
                                    <div class="panel panel-default">
                                        <div class="panel-heading">{__('generalIntroduction')}</div>
                                        <div class="panel-body">
                                            {__('getToKnowComposer')}
                                        </div>
                                    </div>
                                </label>
                            </div>
                            <div class="radio">
                                <label class="tour-label">
                                    <input type="radio" name="help-tour" id="helpTour2" value="ht2"
                                           class="hidden">
                                    <div class="panel panel-default">
                                        <div class="panel-heading">{__('animation')}</div>
                                        <div class="panel-body">
                                            {__('noteMovementOnPage')}
                                        </div>
                                    </div>
                                </label>
                            </div>
                            <div class="radio">
                                <label class="tour-label">
                                    <input type="radio" name="help-tour" id="helpTour3" value="ht3"
                                           class="hidden">
                                    <div class="panel panel-default">
                                        <div class="panel-heading">{__('templates')}</div>
                                        <div class="panel-body">
                                            {__('noteSaveAsTemplate')}
                                        </div>
                                    </div>
                                </label>
                            </div>
                            <div class="radio">
                                <label class="tour-label">
                                    <input type="radio" name="help-tour" id="helpTour4" value="ht4"
                                           class="hidden">
                                    <div class="panel panel-default">
                                        <div class="panel-heading">{__('settingsMore')}</div>
                                        <div class="panel-body">{__('noteTricks')}</div>
                                    </div>
                                </label>
                            </div>
                            <div class="btn-group pull-right">
                                <button type="button" class="btn btn-danger" data-dismiss="modal">{__('Cancel')}</button>
                                <button class="btn btn-primary">{__('startTour')}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="restoreUnsavedModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{__('restoreChanges')}</h4>
            </div>
            <div class="modal-body">
                <div class="alert alert-info" id="errorAlert">
                    {__('restoreUnsaved')}
                </div>
            </div>
            <form id="restoreUnsavedForm">
                <div class="modal-footer">
                    <div class="btn-group">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">
                            {__('noCurrent')}
                        </button>
                        <button class="btn btn-primary">{__('yesRestore')}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
