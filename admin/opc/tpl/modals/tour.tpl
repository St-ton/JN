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