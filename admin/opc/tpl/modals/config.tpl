<div id="configModal" class="modal">
    <div class="modal-dialog">
        <div class="modal-header">
            <div class="modal-title">
                {__('editPortletPrefix')}
                <span id="configPortletName"></span>
                {__('editPortletPostfix')}
            </div>
            <button data-close><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body" id="configModalBody">
        </div>
        <div class="modal-footer">
            <div class="btn-group" id="stdConfigButtons">
                <button data-close class="btn">
                    {__('cancel')}
                </button>
                <button class="btn btn-primary" id="configSave">
                    {__('Save')}
                </button>
            </div>
            <div class="btn-group" id="missingConfigButtons">
                <button data-close class="btn">
                    {__('OK')}
                </button>
            </div>
        </div>
    </div>
</div>