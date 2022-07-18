<?php declare(strict_types=1);

namespace JTL\Template\Snippets\Buttons;

use JTL\Template\Snippets\Snippet;

abstract class Button extends Snippet
{
    protected string $type    = 'submit';
    protected string $name    = 'action';
    protected string $value   = '';
    protected string $class   = 'btn';
    protected string $id      = '';
    protected string $content = '';

    protected function setHtml(): void
    {
        $this->html = '
                <button 
                    type="' . $this->type . '"
                    name="' . $this->name . '"
                    value="' . $this->value . '"
                    class="' . $this->class . '"
                    id="' . $this->id . '"
                >
                    ' . $this->content . '
                </button>
        ';
    }
}
