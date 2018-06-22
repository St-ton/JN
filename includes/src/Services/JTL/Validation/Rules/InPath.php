<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services\JTL\Validation\Rules;


use Eloquent\Pathogen\Exception\InvalidPathStateException;
use Eloquent\Pathogen\Path;
use Eloquent\Pathogen\RelativePath;
use Services\JTL\Validation\RuleInterface;
use Services\JTL\Validation\RuleResult;

/**
 * Class NoPathTraversal
 * @package Services\JTL\Validation\Rules
 *
 * Validates that there is no path traversal in the specified path
 *
 * No transform
 */
class InPath implements RuleInterface
{
    protected $parentPath;

    /**
     * InPath constructor.
     * @param string|\Path $path
     * @throws InvalidPathStateException
     */
    public function __construct($path)
    {
        $this->parentPath = $path instanceof Path ? $path : Path::fromString($path);
        $this->parentPath = $this->parentPath->normalize();
        $this->parentPath = $this->parentPath->toAbsolute();
    }

    /**
     * @param mixed $value
     * @return RuleResult
     */
    public function validate($value)
    {
        // prepare path
        $path = $value instanceof Path ? $value : Path::fromString($value);
        $path = $path->normalize();
        if ($path instanceof RelativePath) {
            $path = $this->parentPath->join($path);
        }
        try {
            $path = $path->toAbsolute();
        } catch (InvalidPathStateException $ex) {
            return new RuleResult(false, 'invalid path state', $value);
        }

        // compare
        return $this->parentPath->isAncestorOf($path)
            ? new RuleResult(true, '', $value)
            : new RuleResult(false, 'path traversal detected', $value);
    }
}
