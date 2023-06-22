<div id="areYouSureModal" class="modal fade" role="dialog" data-form-id="">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">{__('wantToConfirm')}</h2>
                <button type="button" class="close" data-dismiss="modal">
                    <i class="fal fa-times"></i>
                </button>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <div class="row">
                    <div class="ml-auto col-sm-6 col-xl-auto">
                        <button type="button" class="btn btn-primary yes" data-dismiss="modal">
                            {__('yes')}
                        </button>
                    </div>
                    <div class="col-sm-6 col-xl-auto">
                        <button type="button" class="btn btn-danger" name="cancel" data-dismiss="modal">
                            {__('no')}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $("#areYouSureModal button.yes").on('click', function () {
        let formID = $("#areYouSureModal").attr("data-form-id");
        $("form[name='" + formID + "']").submit();
    });
</script>
