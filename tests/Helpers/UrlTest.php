<?php declare(strict_types=1);

namespace Tests\Helpers;

use JTL\Helpers\URL;
use PHPUnit\Framework\TestCase;

/**
 * Class TextTest
 * @package Tests\Helpers
 */
final class UrlTest extends TestCase
{
    /**
     * @var string
     */
    private $url = 'https://foo:bar@example.com:33/baz?q=22&z=aaa#foobar';

    /**
     * @var URL
     */
    private $helper;

    /**
     * UrlTest constructor.
     * @inheritdoc
     */
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->helper = new URL($this->url);
    }

    /**
     * @covers \JTL\Helpers\URL::getUrl
     */
    public function testGetUrl(): void
    {
        $this->assertSame($this->url, $this->helper->getUrl());
    }

    /**
     * @covers \JTL\Helpers\URL::getScheme
     */
    public function testGetScheme(): void
    {
        $this->assertSame('https', $this->helper->getScheme());
    }

    /**
     * @covers \JTL\Helpers\URL::getHost
     */
    public function testGetHost(): void
    {
        $this->assertSame('example.com', $this->helper->getHost());
    }

    /**
     * @covers \JTL\Helpers\URL::getPort
     */
    public function testGetPort(): void
    {
        $this->assertSame('33', $this->helper->getPort());
    }

    /**
     * @covers \JTL\Helpers\URL::getUser
     */
    public function testGetUser(): void
    {
        $this->assertSame('foo', $this->helper->getUser());
    }

    /**
     * @covers \JTL\Helpers\URL::getPass
     */
    public function testGetPass(): void
    {
        $this->assertSame('bar', $this->helper->getPass());
    }

    /**
     * @covers \JTL\Helpers\URL::getPath
     */
    public function testGetPath(): void
    {
        $this->assertSame('/baz', $this->helper->getPath());
    }

    /**
     * @covers \JTL\Helpers\URL::getQuery
     */
    public function testGetQuery(): void
    {
        $this->assertSame('q=22&z=aaa', $this->helper->getQuery());
    }

    /**
     * @covers \JTL\Helpers\URL::getFragment
     */
    public function testGetFragment(): void
    {
        $this->assertSame('foobar', $this->helper->getFragment());
    }

    /**
     * @covers \JTL\Helpers\URL::normalize
     */
    public function testNormalize(): void
    {
        $this->assertSame($this->url, $this->helper->normalize());
    }

    /**
     * @covers \JTL\Helpers\URL::urlDecodeUnreservedChars
     */
    public function testUrlDecodeUnreservedChars(): void
    {
        $url = 'http://example.com/%7E%46%6F%6F/a-%5Fbx%2EABc%30%2D%31';
        $this->assertSame('http://example.com/~Foo/a-_bx.ABc0-1', $this->helper->urlDecodeUnreservedChars($url));
    }

    /**
     * @covers \JTL\Helpers\URL::removeDotSegments
     */
    public function testRemoveDotSegments(): void
    {
        $url = 'http://example.com/a/b/../c/../';
        $this->assertSame('http://example.com/a/', $this->helper->removeDotSegments($url));
    }

    /**
     * @covers \JTL\Helpers\URL::buildURL
     */
    public function testBuildURL(): void
    {
        //@todo
        $this->assertSame(true, true);
    }
}
