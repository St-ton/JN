<div id="blueprintDeleteModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{__('Delete Blueprint?')}</h5>
            </div>
            <div class="modal-body">
                {__('templateDeleteSure')}
            </div>
            <form id="blueprintDeleteForm">
                <div class="modal-footer">
                    <input type="hidden" id="blueprintDeleteId" name="id" value="">
                    <button type="button" class="opc-btn-secondary opc-small-btn" data-dismiss="modal">
                        {__('cancel')}
                    </button>
                    <button type="submit" class="opc-btn-primary opc-small-btn">
                        {__('delete')}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
