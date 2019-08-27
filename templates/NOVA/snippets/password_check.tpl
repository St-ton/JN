<script>
    var deferredTasks = window.deferredTasks || [];
    deferredTasks.push(["ready",function () {
        $(window).on('load', function () {
            $('{$id}').password({
                shortPass:         '{lang key='passwordTooShort' section='login' printf=$Einstellungen.kunden.kundenregistrierung_passwortlaenge}',
                badPass:           '{lang key='passwordIsWeak' section='login'}',
                goodPass:          '{lang key='passwordIsMedium' section='login'}',
                strongPass:        '{lang key='passwordIsStrong' section='login'}',
                containsField:     '{lang key='passwordhasUsername' section='login'}',
                enterPass:         '{lang key='typeYourPassword' section='login'}',
                showPercent:       false,
                showText:          true,
                animate:           true,
                animateSpeed:      'fast',
                field:             false,
                fieldPartialMatch: true,
                minimumLength: {$Einstellungen.kunden.kundenregistrierung_passwortlaenge}
            });
        });
    }]);
</script>
