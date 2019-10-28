<div class="modal fade" tabindex="-1" id="restoreUnsavedModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{__('restoreChanges')}</h5>
            </div>
            <div class="modal-body">
                <div class="alert alert-info" id="errorAlert">
                    {__('restoreUnsaved')}
                </div>
            </div>
            <form id="restoreUnsavedForm">
                <div class="modal-footer">
                    <button type="button" class="opc-btn-secondary opc-small-btn" data-dismiss="modal"
                            id="btnNoRestoreUnsaved">
                        {__('noCurrent')}
                    </button>
                    <button type="submit" class="opc-btn-primary opc-small-btn">
                        {__('yesRestore')}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
