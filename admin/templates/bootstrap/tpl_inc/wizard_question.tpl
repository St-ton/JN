
{if $question->getType() === JTL\Backend\Wizard\QuestionType::TEXT || $question->getType() === JTL\Backend\Wizard\QuestionType::EMAIL}
    <div class="form-group-lg mb-4">
        <span class="form-title">
            {$question->getText()}:
            <span class="fal fa-info-circle text-muted ml-4" data-toggle="tooltip" title="{$question->getDescription()}"></span>
        </span>
        <input type="{if $question->getType() === JTL\Backend\Wizard\QuestionType::EMAIL}email{else}text{/if}"
               class="form-control rounded-pill"
               id="question-{$question->getID()}"
               placeholder=""
               data-setup-summary-id="shop-name"
               value="{if $question->getValue() !== null}{$question->getValue()}{/if}"
        >
    </div>
{elseif $question->getType() === JTL\Backend\Wizard\QuestionType::BOOL}
    <div class="custom-control custom-checkbox">
        <input type="checkbox"
               class="custom-control-input"
               id="question-{$question->getID()}"
               data-setup-summary-id="secure-default-settings"
               data-setup-summary-text="{__('secureDefaultSettings')}"
                {if $question->getValue() === true} checked{/if}
        >
        <label class="custom-control-label" for="secure-default-settings">
            {$question->getText()}
            <span class="fal fa-info-circle text-muted ml-4" data-toggle="tooltip" title="{$question->getDescription()}"></span>
        </label>
    </div>
{else}

{/if}