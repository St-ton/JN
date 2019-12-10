<div class="{if $question->isFullWidth()}col-12{else}col-xl-6{/if}">
    {if $question->getType() === JTL\Backend\Wizard\QuestionType::TEXT || $question->getType() === JTL\Backend\Wizard\QuestionType::EMAIL}
        <div class="form-group-lg mb-4">
            <span class="form-title">
                {$question->getText()}:
                {if $question->getDescription() !== null}
                    <span class="fal fa-info-circle text-muted ml-4" data-toggle="tooltip" title="{$question->getDescription()}"></span>
                {/if}
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
        {if $question->getText() !== null}
            <span class="form-title">
                {$question->getText()}:
                {if $question->getDescription() !== null}
                    <span class="fal fa-info-circle text-muted ml-4" data-toggle="tooltip" title="{$question->getDescription()}"></span>
                {/if}
            </span>
        {/if}
        <div class="custom-control custom-checkbox">
            <input type="checkbox"
                   class="custom-control-input"
                   id="question-{$question->getID()}"
                   data-setup-summary-id="question-{$question->getID()}"
                   data-setup-summary-text="{$question->getText()}"
                    {if $question->getValue() === true} checked{/if}
            >
            <label class="custom-control-label" for="question-{$question->getID()}">
                {$question->getLabel()}
                {if $question->getText() === null && $question->getDescription() !== null}
                    <span class="fal fa-info-circle text-muted ml-4" data-toggle="tooltip" title="{$question->getDescription()}"></span>
                {/if}
            </label>
        </div>
    {elseif $question->getType() === JTL\Backend\Wizard\QuestionType::MULTI_BOOL}
        <div class="form-group-lg mb-4">
            <span class="form-title">
                {$question->getText()}:
                {if $question->getDescription() !== null}
                    <span class="fal fa-info-circle text-muted ml-4" data-toggle="tooltip" title="{$question->getDescription()}"></span>
                {/if}
            </span>
            {foreach $question->getOptions() as $option}
                <div class="custom-control custom-checkbox">
                    <input type="checkbox"
                           class="custom-control-input"
                           id="question-{$question->getID()}-{$option@index}"
                           name="question-{$question->getID()}"
                           data-setup-summary-id="question-{$question->getID()}-{$option@index}"
                           data-setup-summary-text="{$option->getName()}"
                            {if $option->getValue() === true} checked{/if}
                    >
                    <label class="custom-control-label" for="question-{$question->getID()}-{$option@index}">
                        {$option->getName()}
                    </label>
                </div>
            {/foreach}
        </div>
    {elseif $question->getType() === JTL\Backend\Wizard\QuestionType::PLUGIN}
        <div class="form-group-list">
            {foreach $question->getOptions() as $option}
                <div class="form-group-list-item">
                    <div class="form-row">
                        <div class="col-xl-3">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox"
                                       class="custom-control-input"
                                       id="question-{$question->getID()}"
                                       data-setup-summary-id="question-{$question->getID()}"
                                       data-setup-summary-text="{$question->getText()}"
                                        {if $question->getValue() === true} checked{/if}
                                >
                                <label class="custom-control-label" for="rechtstext-paypal-1-1">
                                    <img src="{$option->getLogoPath()}" width="108" height="42" alt="{$option->getName()}">
                                </label>
                            </div>
                        </div>
                        <div class="col-xl">
                            <p class="text-muted">{$option->getDescription()}</p>
                            <a href="{$option->getLink()}">{__('getToKnowMore')}</a>
                        </div>
                    </div>
                </div>
            {/foreach}
        </div>
    {/if}
</div>
