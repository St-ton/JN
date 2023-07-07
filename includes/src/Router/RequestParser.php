<?php declare(strict_types=1);

namespace JTL\Router;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Class RequestParser
 * @package JTL\Router
 * @since 5.3.0
 */
class RequestParser
{
    private array|null|object $body;

    private array $queryParams;

    public function __construct(private readonly ServerRequestInterface $request)
    {
        $this->body        = $this->request->getParsedBody() ?? [];
        $this->queryParams = $this->request->getQueryParams();
    }

    public function post(string $var, mixed $default = null): mixed
    {
        return \is_array($this->body)
            ? $this->body[$var] ?? $default
            : $this->body?->$var ?? $default;
    }

    public function postInt(string $var, int $default = 0): int
    {
        return (int)$this->post($var, $default);
    }

    public function get(string $var, mixed $default = null): mixed
    {
        return $this->queryParams[$var] ?? $default;
    }

    public function getInt(string $var, int $default = 0): int
    {
        return (int)$this->get($var, $default);
    }

    public function request(string $var, mixed $default = ''): mixed
    {
        return $this->post($var) ?? $this->get($var) ?? $default;
    }

    public function requestInt(string $var, int $default = 0): int
    {
        return (int)($this->post($var) ?? $this->get($var) ?? $default);
    }

    /**
     * @param string $var
     * @return array|int[]
     */
    public function requestIntArray(string $var): array
    {
        $val = $this->request($var, null);
        if ($val === null) {
            return [];
        }

        return \is_numeric($val)
            ? [(int)$val]
            : \array_map(static function ($e): int {
                return (int)$e;
            }, (array)$val);
    }

    public function getBody(): array
    {
        return $this->body;
    }

    public function updateBody(string $var, mixed $value): void
    {
        $this->body[$var] = $var;
    }
}
