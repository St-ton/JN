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

    /**
     * @param string $type
     *
     * @return Button
     */
    public function setType(string $type): Button
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @param string $name
     *
     * @return Button
     */
    public function setName(string $name): Button
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param string $value
     *
     * @return Button
     */
    public function setValue(string $value): Button
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @param string $class
     *
     * @return Button
     */
    public function setClass(string $class): Button
    {
        $this->class = $class;

        return $this;
    }

    /**
     * @param string $id
     *
     * @return Button
     */
    public function setId(string $id): Button
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @param string $content
     *
     * @return Button
     */
    public function setContent(string $content): Button
    {
        $this->content = $content;

        return $this;
    }
}
