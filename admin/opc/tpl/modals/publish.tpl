<div class="modal fade" tabindex="-1" id="publishModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{__('draftPublic')}</h5>
                <button type="button" class="opc-header-btn" data-toggle="tooltip" data-dismiss="modal"
                        data-placement="bottom">
                    <i class="fa fas fa-times"></i>
                </button>
            </div>
            <form id="publishForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="draftName">{__('draftName')}</label>
                        <input type="text" class="form-control opc-control" id="draftName" name="draftName"
                               value="">
                    </div>
                    <div class="form-group">
                        <input type="radio" id="checkPublishNot" name="scheduleStrategy"
                               onchange="opc.gui.onChangePublishStrategy()">
                        <label for="checkPublishNot">
                            Nicht veröffentlichen
                        </label>
                    </div>
                    <div class="form-group">
                        <input type="radio" id="checkPublishNow" name="scheduleStrategy"
                               onchange="opc.gui.onChangePublishStrategy()">
                        <label for="checkPublishNow">
                            Sofort veröffentlichen
                        </label>
                    </div>
                    <div class="form-group" style="float:left; width:50%">
                        <input type="radio" id="checkPublishSchedule" name="scheduleStrategy"
                               onchange="opc.gui.onChangePublishStrategy()">
                        <label for="checkPublishSchedule">
                            Planen
                        </label>
                    </div>
                    <div class="form-group">
                        <input type="checkbox" id="checkPublishInfinite"
                               onchange="opc.gui.onChangePublishInfinite()">
                        <label for="checkPublishInfinite">
                            Auf unbestimmte Zeit
                        </label>
                    </div>
                    <div class="form-group" style="float:left; width:50%; padding-right:16px">
                        <label for="publishFrom">{__('publicFrom')}</label>
                        <input type="text" class="form-control opc-control datetimepicker-input" id="publishFrom"
                               name="publishFrom" data-toggle="datetimepicker" data-target="#publishFrom">
                    </div>
                    <div class="form-group" style="float:left; width:50%">
                        <label for="publishTo">{__('publicTill')}</label>
                        <input type="text" class="form-control opc-control datetimepicker-input" id="publishTo"
                               name="publishTo" data-toggle="datetimepicker" data-target="#publishTo">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="opc-btn-secondary opc-small-btn" data-dismiss="modal">
                        {__('cancel')}
                    </button>
                    <button type="submit" class="opc-btn-primary opc-small-btn">
                        {__('apply')}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>