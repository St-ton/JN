{*
    Parameters:
        modalID - unique id for the modal (e.g. 'reset-payment')
        modalTitle - the modal dialogs title
        modalBody (optional) - body text of the modal with more information
*}
<div id="{$modalID}-modal" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{$modalTitle}</h4>
            </div>
            <div class="modal-body">{if isset($modalBody)}{$modalBody}{/if}</div>
            <div class="modal-footer">
                <p>{#wantToConfirm#}</p>
                <div class="btn-group">
                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times"></i> {#cancel#}</button>
                    <button id="{$modalID}-confirm" type="button" class="btn btn-primary"><i class="fa fa-check"></i> {#confirm#}</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function(){
        $('#{$modalID}-confirm').click(function(){
            var $modalButton = $('button[data-target="#{$modalID}-modal"');

            if($modalButton.data('href')) {
                window.location.href = $modalButton.data('href');
            } else if ($modalButton.data('form')) {
                $($modalButton.data('form')).submit();
            }
        });
    });
</script>
