
<style>
    /* CONSIDER: styles ar mandatory for the QR-code! */

    /* a small space arround the whole code (not mandatory) */
    div.qrcode{
        /* margin: 0 5px; */
        margin: 5px
    }

    /* row element */
    div.qrcode > p {
        margin: 0;
        padding: 0;
        height: 5px;
    }

    /* column element(s) */
    div.qrcode > p > b,
    div.qrcode > p > i {
        display: inline-block;
        width: 5px;
        height: 5px;
    }

    /* color of 'on-elements' - "the color of the QR" */
    div.qrcode > p > b {
        background-color: #000;
    }

    /* color of 'off-elements' - "the color of the background" */
    div.qrcode > p > i {
        background-color: #fff;
    }
</style>

{block name='account-change-password'}
    {block name='account-change-password-heading'}
        <h1>{lang key='manageTwoFA' section='account data'}</h1>
    {/block}
    {block name='account-manage-two-fa-manage-two-fa-form'}
        {block name='account--manage-two-fa-alert'}
            {alert variant="info"}{lang key='manageTwoFADesc' section='login'}{/alert}
        {/block}
        {row}
            {col md=7 lg=6}
                {block name='account-change-password-form-password'}
                    {form id="manage-two-fa" action="{get_static_route id='jtl.php'}" method="post" class="jtl-validate" slide=true}
                        <input type="hidden" name="twoFACustomerID" id="twoFACustomerID" value="{$Kunde->getID()}">
                        {block name='account-manage-two-fa-form-content'}
                            {lang key='enableTwoFA' section='account data' assign=lbl}
                            {formgroup label-for='b2FAauth' label=$lbl}
                                {select id='b2FAauth' name='b2FAauth'}
                                    <option value="0"{if $Kunde->getB2FAauth() === 0} selected="selected"{/if}>{lang key='no'}</option>
                                    <option value="1"{if $Kunde->getB2FAauth() === 1} selected="selected"{/if}>{lang key='yes'}</option>
                                {/select}
                            {/formgroup}
                            <div id="TwoFAwrapper" class="form-group{if isset($cError_arr.c2FAsecret)} error{/if}" style="border:1px solid {if isset($cError_arr.c2FAsecret)}red{else}lightgrey{/if};padding:10px;">
                                <div id="QRcodeCanvas" style="display:{if '' !== $QRcodeString }block{else}none{/if}">
                                    <div class="alert alert-danger" role="alert">
                                        {lang key='enableTwoFAwarning' section='account data'}
                                    </div>
                                    {lang key='infoScanQR' section='account data'}
                                    <div id="QRcode" class="qrcode">{$QRcodeString}</div><br>
                                    <input type="hidden" id="c2FAsecret" name="c2FAsecret" value="{$cKnownSecret}">
                                    <br>
                                </div>
                                <div class="modal fade" id="EmergencyCodeModal">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h2 class="modal-title">{lang key='emergencyCode' section='account data'}</h2>
                                                <button type="button" class="close" data-dismiss="modal">
                                                    <i class="fal fa-times"></i>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <div id="EmergencyCodes">
                                                    <div class="iframewrapper">
                                                        <iframe src="" id="printframe" name="printframe" frameborder="0" width="100%" height="300" align="middle"></iframe>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <div class="row">
                                                    <div class="ml-auto col-sm-6 col-xl-auto mb-2">
                                                        <button class="btn btn-outline-primary btn-block" type="button" data-dismiss="modal">{lang key='close' section='account data'}</button>
                                                    </div>
                                                    <div class="col-sm-6 col-xl-auto mb-2">
                                                        <button class="btn btn-outline-primary btn-block" type="button" onclick="printframe.print();">{lang key='print' section='account data'}</button>
                                                    </div>
                                                    <div class="col-sm-6 col-xl-auto">
                                                        <button class="btn btn-danger btn-block" type="button" onclick="showEmergencyCodes('forceReload');">
                                                            {lang key='codeCreateAgain' section='account data'}
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                {lang key='clickHereToCreateQR' section='account data'}
                                <br>
                                <div class="row">
                                    <div class="col-sm-auto mb-3">
                                        <button class="btn btn-warning btn-block" type="button" onclick="showEmergencyCodes();">
                                            {lang key='emergencyCodeCreate' section='account data'}
                                        </button>
                                    </div>
                                    <div class="col-sm-auto">
                                        <button class="btn btn-primary btn-block" type="button" onclick="createNewSecret();">
                                            {lang key='codeCreate' section='account data'}
                                        </button>
                                    </div>
                                </div>
                            </div>
                            {block name='account-manage-two-fa-form-submit'}
                                {row}
                                    {col cols=12 class='col-md'}
                                        {link class='btn btn-outline-primary btn-back' href="{get_static_route id='jtl.php'}"}
                                            {lang key='back'}
                                        {/link}
                                    {/col}
                                    {col class='ml-auto-util col-md-auto'}
                                        {input type='hidden' name='manage_two_fa' value='1'}
                                        {button type='submit' value='1' block=true variant='primary'}
                                            {lang key='save' section='account data'}
                                        {/button}
                                    {/col}
                                {/row}
                            {/block}
                        {/block}
                    {/form}
                {/block}
            {/col}
        {/row}
    {/block}
{/block}

{literal}
<script>
    $(document).ready(function() {
        if ($('#b2FAauth option:selected').val() == 0) {
            $('#TwoFAwrapper').hide();
        } else {
            $('#TwoFAwrapper').show();
        }

        $('#b2FAauth').on('change', function(e) {
            e.stopImmediatePropagation(); // stop this event during page-load
            if('none' === $('#TwoFAwrapper').css('display')) {
                $('#TwoFAwrapper').slideDown();
            } else {
                $('#TwoFAwrapper').slideUp();
            }
        });
    });

    function createNewSecret() {
        if (confirm('{/literal}{lang key='warningAuthSecretOverwrite' section='account data'}{literal}')) {
            var userID = parseInt($('#twoFACustomerID').val());
            var that = this;
            $.evo.io().call('getNewTwoFA', [userID], that, function (error, data) {
                // display the new RQ-code
                $('#QRcode').html(data.response.szQRcode);
                $('#c2FAsecret').val(data.response.szSecret);
                // toggle code-canvas
                if('none' === $('#QRcodeCanvas').css('display')) {
                    $('#QRcodeCanvas').css('display', 'block');
                }
            });
        }
    }

    function showEmergencyCodes(action) {
        var userID = parseInt($('#twoFACustomerID').val());
        var that = this;
        $.evo.io().call('genTwoFAEmergencyCodes', [userID], that, function (error, data) {
            var iframeHtml = '';

            iframeHtml += '<h4>{/literal}{lang key='shopEmergencyCodes' section='account data'}{literal}</h4>';
            iframeHtml += '{/literal}{lang key='account' section='account data'}{literal}: <b>' + data.response.loginName + '</b><br>';
            iframeHtml += '{/literal}{lang key='shop' section='account data'}{literal}: <b>' + data.response.shopName + '</b><br><br>';
            iframeHtml += '<pre>';

            data.response.vCodes.forEach(function (code, i) {
                iframeHtml += code + ' ';
                if (i%2 === 1) {
                    iframeHtml += '\n';
                }
            });

            iframeHtml += '</pre>';
            $('#printframe').contents().find('body')[0].innerHTML = iframeHtml;
            $('#EmergencyCodeModal').modal('show');
        });
    }
</script>
{/literal}
