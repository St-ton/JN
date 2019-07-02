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
