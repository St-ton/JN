<div id="{$}" class="modal">
    <div class="modal-dialog">
        <div class="modal-header">
            <div class="modal-title">{__('draftPublic')}</div>
            <button data-close><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label for="draftName">{__('draftName')}</label>
                <input class="control" id="draftName" name="draftName" value="">
            </div>
            <div class="form-group">
                <input type="radio" id="checkPublishNot" name="scheduleStrategy">
                <label for="checkPublishNot">{__('publishNot')}</label>
            </div>
            <div class="form-group">
                <input type="radio" id="checkPublishNow" name="scheduleStrategy">
                <label for="checkPublishNow">{__('publishImmediately')}</label>
            </div>
            <div class="row">
                <div class="form-group">
                    <input type="radio" id="checkPublishSchedule" name="scheduleStrategy">
                    <label for="checkPublishSchedule">{__('selectDate')}</label>
                </div>
                <div class="form-group">
                    <input type="checkbox" id="checkPublishInfinite">
                    <label for="checkPublishInfinite">{__('indefinitePeriodOfTime')}</label>
                </div>
            </div>
            <div class="row">
                <div class="form-group">
                    <label for="publishFrom">{__('publicFrom')}</label>
                    <input type="datetime-local" class="control" id="publishFrom" name="publishFrom" value="">
                </div>
                <div class="form-group">
                    <label for="publishTo">{__('publicTill')}</label>
                    <input class="control" id="publishTo" name="publishTo" value="">
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <div class="btn-group">
                <button data-close class="btn">
                    {__('cancel')}
                </button>
                <button class="btn btn-primary">
                    {__('apply')}
                </button>
            </div>
        </div>
    </div>
</div>