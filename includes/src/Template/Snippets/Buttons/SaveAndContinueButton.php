<?php declare(strict_types=1);

namespace JTL\Template\Snippets\Buttons;

class SaveAndContinueButton extends Button
{
    protected string $value   = 'save-config-continue';
    protected string $class   = 'btn btn-outline-primary btn-block';
    protected string $id      = 'save-and-continue';
    protected string $content = '<i class="fal fa-save"></i> {__(\'saveAndContinue\')}';
}
