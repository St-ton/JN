<div id="tourModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{__('help')}</h5>
                <button type="button" class="opc-header-btn" data-toggle="tooltip" data-dismiss="modal"
                        data-placement="bottom">
                    <i class="fa fas fa-times"></i>
                </button>
            </div>
            <form id="tourForm" onsubmit="opc.tutorial.modalStartTour()">
                <div class="modal-body">
                    <p>{__('noteInfoInGuide')}</p>
                    <div class="radio">
                        <label class="tour-label">
                            <input type="radio" name="help-tour" id="helpTour0" value="0" checked
                                   class="hidden">
                            <span class="card">
                                <span class="card-header">{__('generalIntroduction')}</span>
                                <span class="card-body">
                                    {__('getToKnowComposer')}
                                </span>
                            </span>
                        </label>
                    </div>
                    <div class="radio">
                        <label class="tour-label">
                            <input type="radio" name="help-tour" id="helpTour1" value="1"
                                   class="hidden">
                            <div class="card">
                                <div class="card-header">{__('animation')}</div>
                                <div class="card-body">
                                    {__('noteMovementOnPage')}
                                </div>
                            </div>
                        </label>
                    </div>
                    <div class="radio">
                        <label class="tour-label">
                            <input type="radio" name="help-tour" id="helpTour2" value="2"
                                   class="hidden">
                            <div class="card">
                                <div class="card-header">{__('templates')}</div>
                                <div class="card-body">
                                    {__('noteSaveAsTemplate')}
                                </div>
                            </div>
                        </label>
                    </div>
                    <div class="radio">
                        <label class="tour-label">
                            <input type="radio" name="help-tour" id="helpTour3" value="3"
                                   class="hidden">
                            <div class="card">
                                <div class="card-header">{__('settingsMore')}</div>
                                <div class="card-body">{__('noteTricks')}</div>
                            </div>
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="opc-btn-secondary opc-small-btn" data-dismiss="modal">
                        {__('cancel')}
                    </button>
                    <button type="submit" class="opc-btn-primary opc-small-btn">
                        {__('startTour')}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>