<?php declare(strict_types=1);

namespace Tests\Helpers;

use JTL\Helpers\Request;
use PHPUnit\Framework\TestCase;

/**
 * Class RequestTest
 * @package Tests\Helpers
 */
final class RequestTest extends TestCase
{
    /**
     * @covers \JTL\Helpers\Request::getVar
     */
    public function testGetVar(): void
    {
        $_GET['foo'] = 1;
        $this->assertSame(1, Request::getVar('foo'));
        $_GET['foo'] = 'bar';
        $this->assertSame('bar', Request::getVar('foo'));
        unset($_GET['foo']);
        $this->assertSame(42, Request::getVar('foo', 42));
        $this->assertSame(null, Request::getVar('foo'));
    }

    /**
     * @covers \JTL\Helpers\Request::postVar
     */
    public function testPostVar(): void
    {
        $_POST['foo'] = 1;
        $this->assertSame(1, Request::postVar('foo'));
        $_POST['foo'] = 'bar';
        $this->assertSame('bar', Request::postVar('foo'));
        unset($_POST['foo']);
        $this->assertSame(42, Request::postVar('foo', 42));
        $this->assertSame(null, Request::postVar('foo'));
    }

    /**
     * @covers \JTL\Helpers\Request::getInt
     */
    public function testGetInt(): void
    {
        $_GET['foo'] = 1;
        $this->assertSame(1, Request::getInt('foo'));
        $_GET['foo'] = '42';
        $this->assertSame(42, Request::getInt('foo'));
        unset($_GET['foo']);
        $this->assertSame(42, Request::getInt('foo', 42));
        $this->assertSame(0, Request::getInt('foo'));
    }

    /**
     * @covers \JTL\Helpers\Request::postInt
     */
    public function testPostInt(): void
    {
        $_POST['foo'] = 1;
        $this->assertSame(1, Request::postInt('foo'));
        $_POST['foo'] = '42';
        $this->assertSame(42, Request::postInt('foo'));
        unset($_POST['foo']);
        $this->assertSame(42, Request::postInt('foo', 42));
        $this->assertSame(0, Request::postInt('foo'));
    }

    /**
     * @covers \JTL\Helpers\Request::hasGPCData
     */
    public function testHasGPCData(): void
    {
        $_POST['foo'] = 1;
        $this->assertSame(true, Request::hasGPCData('foo'));
        unset($_POST['foo'], $_GET['foo']);
        $this->assertSame(false, Request::hasGPCData('foo'));
        $_GET['foo'] = 2;
        $this->assertSame(true, Request::hasGPCData('foo'));
        $_GET['foo'] = null;
        $this->assertSame(false, Request::hasGPCData('foo'));
        unset($_GET['foo']);
    }

    /**
     * @covers \JTL\Helpers\Request::verifyGPDataIntegerArray
     */
    public function testVerifyGPDataIntegerArray(): void
    {
        $_REQUEST['foo'] = ['foo' => 1, 'bar' => '42'];
        $this->assertSame(['foo' => 1, 'bar' => 42], Request::verifyGPDataIntegerArray('foo'));
        $_REQUEST['foo'] = '33';
        $this->assertSame([33], Request::verifyGPDataIntegerArray('foo'));
        unset($_REQUEST['foo']);
        $this->assertSame([], Request::verifyGPDataIntegerArray('foo'));
    }

    /**
     * @covers \JTL\Helpers\Request::verifyGPCDataInt
     */
    public function testVerifyGPCDataInt(): void
    {
        $_POST['foo'] = 1;
        $this->assertSame(1, Request::verifyGPCDataInt('foo'));
        unset($_POST['foo'], $_GET['foo']);
        $this->assertSame(0, Request::verifyGPCDataInt('foo'));
        $_GET['foo'] = 2;
        $this->assertSame(2, Request::verifyGPCDataInt('foo'));
        $_GET['foo'] = null;
        $this->assertSame(0, Request::verifyGPCDataInt('foo'));
        unset($_GET['foo'], $_POST['foo']);
    }

    /**
     * @covers \JTL\Helpers\Request::verifyGPDataString
     */
    public function testVerifyGPDataString(): void
    {
        $_POST['foo'] = 'bar';
        $this->assertSame('bar', Request::verifyGPDataString('foo'));
        unset($_POST['foo'], $_GET['foo']);
        $this->assertSame('', Request::verifyGPDataString('foo'));
        $_GET['foo'] = 2;
        $this->assertSame(2, Request::verifyGPDataString('foo'));
        $_GET['foo'] = null;
        $this->assertSame('', Request::verifyGPDataString('foo'));
        unset($_GET['foo'], $_POST['foo']);
    }

    /**
     * @covers \JTL\Helpers\Request::getRealIP
     */
    public function testGetRealIP(): void
    {
        // @todo
        $this->assertSame(true, true);
    }

    /**
     * @covers \JTL\Helpers\Request::makeHTTPHeader
     */
    public function testMakeHTTPHeader(): void
    {
        // @todo
        $this->assertSame(true, true);
    }

    /**
     * @covers \JTL\Helpers\Request::checkSSL
     */
    public function testCheckSSL(): void
    {
        // @todo
        $this->assertSame(true, true);
    }

    /**
     * @covers \JTL\Helpers\Request::isAjaxRequest
     */
    public function testIsAjaxRequest(): void
    {
        $this->assertSame(false, Request::isAjaxRequest());
        $_REQUEST['isAjax'] = true;
        $this->assertSame(true, Request::isAjaxRequest());
        unset($_REQUEST['isAjax']);
    }

    /**
     * @covers \JTL\Helpers\Request::extractExternalParams
     */
    public function testExtractExternalParams(): void
    {
        // @todo
        $this->assertSame(true, true);
    }

    /**
     * @covers \JTL\Helpers\Request::urlHasEqualRequestParameter
     */
    public function testUrlHasEqualRequestParameter(): void
    {
        $_GET['foo'] = 'bar';
        $this->assertSame(true, Request::urlHasEqualRequestParameter('http://example.com/?foo=bar', 'foo'));
        $this->assertSame(true, Request::urlHasEqualRequestParameter('http://example.com/?foo=bar', 'baz'));
        $this->assertSame(false, Request::urlHasEqualRequestParameter('http://example.com/?foo=baz', 'foo'));
        unset($_GET['foo']);
    }
}
