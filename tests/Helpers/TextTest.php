<?php declare(strict_types=1);

namespace Tests\Helpers;

use JTL\Helpers\Text;
use PHPUnit\Framework\TestCase;

/**
 * Class TextTest
 * @package Tests\Helpers
 */
final class TextTest extends TestCase
{
    /**
     * @covers \JTL\Helpers\Text::startsWith
     */
    public function testStartsWith(): void
    {
        $this->assertSame(true, Text::startsWith('123abc', '123'));
        $this->assertSame(true, Text::startsWith('abc', ''));
        $this->assertSame(false, Text::startsWith('123abc', 'q123'));
    }

    /**
     * @covers \JTL\Helpers\Text::endsWith
     */
    public function testEndsWith(): void
    {
        $this->assertSame(true, Text::endsWith('123abc', 'abc'));
        $this->assertSame(true, Text::endsWith('123abc', 'c'));
        $this->assertSame(true, Text::endsWith('abc', ''));
        $this->assertSame(false, Text::endsWith('123abc', 'q123'));
        $this->assertSame(false, Text::endsWith('', '1'));
    }

    /**
     * @covers \JTL\Helpers\Text::htmlentities
     */
    public function testHtmlEntities(): void
    {
        $this->assertSame('&euro;', Text::htmlentities('€'));
        $this->assertSame('&lt;div&gt;&lt;p&gt;s&lt;/p&gt;', Text::htmlentities('<div><p>s</p>'));
    }

    /**
     * @covers \JTL\Helpers\Text::htmlentitiesOnce
     */
    public function testHtmlEntitiesOnce(): void
    {
        $this->assertSame('&euro;', Text::htmlentitiesOnce('€'));
        $this->assertSame('&lt;div&gt;&lt;p&gt;s&lt;/p&gt;', Text::htmlentitiesOnce('<div><p>s</p>'));
    }

    /**
     * @covers \JTL\Helpers\Text::htmlentitiesSubstr
     */
    public function testHtmlEntitiesSubstring(): void
    {
        $this->assertSame('&lt;p', Text::htmlentitiesSubstr('&lt;p&gt;test&lt;/p&gt;', 2));
    }

    /**
     * @covers \JTL\Helpers\Text::unhtmlentities
     */
    public function testUnhtmlentities(): void
    {
        $this->assertSame(null, Text::unhtmlentities(null));
        $this->assertSame(42, Text::unhtmlentities(42));
        $this->assertSame('<p>test</p>', Text::unhtmlentities('&lt;p&gt;test&lt;/p&gt;'));
        $input = '<p>test\'s "are" good</p>';
        $this->assertSame($input, Text::unhtmlentities(Text::htmlentitydecode($input)));
    }

    /**
     * @covers \JTL\Helpers\Text::htmlentitydecode
     */
    public function testHtmlentitydecode(): void
    {
        $this->assertSame('&', Text::htmlentitydecode('&amp;'));
        $this->assertSame('&', Text::htmlentitydecode('&'));
    }

    /**
     * @covers \JTL\Helpers\Text::htmlspecialchars
     */
    public function testHtmlspecialchars(): void
    {
        $this->assertSame('&amp;', Text::htmlspecialchars('&'));
        $this->assertSame('&quot;', Text::htmlspecialchars('"'));
        $this->assertSame('"', Text::htmlspecialchars('"', \ENT_NOQUOTES));
    }

    /**
     * @covers \JTL\Helpers\Text::gethtmltranslationtable
     */
    public function testGethtmltranslationtable(): void
    {
        $this->assertIsArray(Text::gethtmltranslationtable());
    }

    /**
     * @covers \JTL\Helpers\Text::filterXSS
     */
    public function testFilterXSS(): void
    {
        $this->assertSame('test', Text::filterXSS('test'));
        $this->assertSame(['test', 'test'], Text::filterXSS(['test', 'test']));
        $this->assertSame('123', Text::filterXSS(123));
        $this->assertSame('alert(1)', Text::filterXSS('<script>alert(1)</script>'));
        $this->assertSame(['alert(1)', 'alert(2)'], Text::filterXSS(['<script>alert(1)</script>', '<script>alert(2)</script>']));
        $this->assertSame('alert(1)', Text::filterXSS('<script type="text/javascript">alert(1)</script>'));
        $this->assertSame('alert(1)', Text::filterXSS('<script type="javascript">alert(1)</script>'));
    }

    /**
     * @covers \JTL\Helpers\Text::is_utf8
     */
    public function testIs_utf8(): void
    {
        $this->assertSame(1, Text::is_utf8('test'));
        $this->assertSame(1, Text::is_utf8(\utf8_encode('test')));
        $this->assertSame(1, Text::is_utf8('täßt'));
        $this->assertSame(0, Text::is_utf8(\utf8_decode('täßt')));
    }

    /**
     * @covers \JTL\Helpers\Text::xssClean
     */
    public function testXssClean(): void
    {
        $this->assertSame('test', Text::xssClean('test'));
        $this->assertSame('täst', Text::xssClean('t&auml;st'));
        $this->assertSame('alert(1)', Text::xssClean('<script>alert(1)</script>'));
    }

    /**
     * @covers \JTL\Helpers\Text::convertUTF8
     */
    public function testConvertUTF8(): void
    {
        $this->assertSame('test', Text::convertUTF8('test'));
        $this->assertSame('täst', Text::convertUTF8(\utf8_decode('täst')));
    }

    /**
     * @covers \JTL\Helpers\Text::convertISO
     */
    public function testConvertISO(): void
    {
        $this->assertSame('test', Text::convertISO('test'));
        $this->assertSame(\utf8_decode('täst'), Text::convertISO('täst'));
    }

    /**
     * @covers \JTL\Helpers\Text::convertISO2ISO639
     */
    public function testConvertISO2ISO639(): void
    {
        $this->assertSame('test', Text::convertISO2ISO639('test'));
        $this->assertSame('ba', Text::convertISO2ISO639('bak'));
        $this->assertSame('de', Text::convertISO2ISO639('ger'));
        $this->assertSame('de', Text::convertISO2ISO639('de'));
    }

    /**
     * @covers \JTL\Helpers\Text::convertISO6392ISO
     */
    public function testConvertISO6392ISO(): void
    {
        $this->assertSame('ger', Text::convertISO6392ISO('de'));
        $this->assertSame('ger', Text::convertISO6392ISO('ger'));
        $this->assertSame('bak', Text::convertISO6392ISO('ba'));
        $this->assertSame('ba', Text::convertISO2ISO639(Text::convertISO6392ISO('ba')));
    }

    /**
     * @covers \JTL\Helpers\Text::getISOMappings
     */
    public function testGetISOMappings(): void
    {
        $this->assertCount(184, Text::getISOMappings());
    }

    /**
     * @covers \JTL\Helpers\Text::removeDoubleSpaces
     */
    public function testRemoveDoubleSpaces(): void
    {
        $this->assertSame('test 123 456', Text::removeDoubleSpaces('test    123  456'));
    }

    /**
     * @covers \JTL\Helpers\Text::removeWhitespace
     */
    public function testRemoveWhitespace(): void
    {
        $this->assertSame('test 123 456', Text::removeWhitespace('test    123  456'));
    }

    /**
     * @covers \JTL\Helpers\Text::createSSK
     */
    public function testCreateSSK(): void
    {
        $this->assertSame('', Text::createSSK('test123456'));
        $this->assertSame('', Text::createSSK(123));
        $this->assertSame('', Text::createSSK([]));
        $this->assertSame(';a;', Text::createSSK(['a']));
        $this->assertSame(';1;2;a;b;', Text::createSSK([1, 2, 'a', 'b']));
    }

    /**
     * @covers \JTL\Helpers\Text::parseSSK
     */
    public function testParseSSK(): void
    {
        $this->assertSame([], Text::parseSSK(123));
        $this->assertSame([], Text::parseSSK(123));
        $this->assertSame([], Text::parseSSK([]));
        $this->assertSame([0 => 'a', 1 => 'b'], Text::parseSSK('a;b'));
        $this->assertSame([1 => '1', 2 => '2', 3 => 'a', 4 => 'b'], Text::parseSSK(';1;2;a;b;'));
        $this->assertSame([1 => 'a'], Text::parseSSK(Text::createSSK(['a'])));
    }

    /**
     * @covers \JTL\Helpers\Text::parseSSKint
     */
    public function testParseSSKint(): void
    {
        $this->assertSame([0 => 1, 1 => 2], Text::parseSSKint('1;2;'));
        $this->assertSame([1 => 1, 2 => 2], Text::parseSSKint(';1;2;'));
        $this->assertSame([], Text::parseSSKint([]));
        $this->assertSame([1 => '1', 2 => '2', 3 => 'a', 4 => 'b'], Text::parseSSK(';1;2;a;b;'));
        $this->assertSame([0 => 0, 1 => 0, 2 => 1], Text::parseSSKint('a;b;1;'));
        $this->assertSame([], Text::parseSSKint([]));
        $this->assertSame([], Text::parseSSKint(123));
    }

    /**
     * @covers \JTL\Helpers\Text::raiseException
     */
//    public function testRaiseException(): void
//    {
//        $this->expectException(InvalidArgumentException::class);
//        Text::raiseException();
//    }

    /**
     * @param mixed $in Input
     * @param mixed $expect Expected output
     * @param bool  $validate
     * @param bool  $addSchema
     * @covers       \JTL\Helpers\Text::filterURL
     * @dataProvider providesFilterURL
     */
    public function testFilterURL($in, $expect, bool $validate = true, bool $addSchema = false): void
    {
        $this->assertSame($expect, Text::filterURL($in, $validate, $addSchema));
    }

    /**
     * @return string[][]
     */
    public function providesFilterURL(): array
    {
        return [
            'array'                 => [[], false],
            'array v'               => [[], false, false],
            'int'                   => [11, false],
            'int v'                 => [11, false, false],
            'obj'                   => [(object)[], false],
            'obj v'                 => [(object)[], false, false],
            'no tld'                => ['example', false],
            'no tld v'              => ['example', 'example', false],
            'no proto v'            => ['example.com', false],
            'no proto'              => ['example.com', 'example.com', false],
            'no proto add v'        => ['example.com', 'http://example.com', true, true],
            'no proto add'          => ['example.com', 'http://example.com', false, true],
            'normal v'              => ['http://example.com', 'http://example.com'],
            'normal'                => ['http://example.com', 'http://example.com', false],
            'normal brackets v'     => ['(http://example.com)', false],
            'normal brackets'       => ['(http://example.com)', '(http://example.com)', false],
            'https v'               => ['https://example.com', 'https://example.com'],
            'https'                 => ['https://example.com', 'https://example.com', false],
            'umlaut v'              => ['https://ümläüt.com', 'https://xn--mlt-rla1jd.com'],
            'umlaut'                => ['https://ümläüt.com', 'https://xn--mlt-rla1jd.com', false],
            'umlaut no proto v'     => ['ümläüt.com', false, true],
            'umlaut no proto'       => ['ümläüt.com', 'xn--mlt-rla1jd.com', false],
            'umlaut no proto add v' => ['ümläüt.com', 'http://xn--mlt-rla1jd.com', true, true],
            'umlaut no proto add'   => ['ümläüt.com', 'http://xn--mlt-rla1jd.com', false, true],
        ];
    }

    /**
     * @param mixed $in Input
     * @param mixed $expect Expected output
     * @param bool  $validate
     * @covers       \JTL\Helpers\Text::filterEmailAddress
     * @dataProvider providesFilterEmailAddress
     */
    public function testFilterEmailAddress($in, $expect, bool $validate = true): void
    {
        $this->assertSame($expect, Text::filterEmailAddress($in, $validate));
    }

    /**
     * @return string[][]
     */
    public function providesFilterEmailAddress(): array
    {
        return [
            'array'                             => [[], false],
            'array v'                           => [[], '', false],
            'int'                               => [11, false],
            'int v'                             => [11, '', false],
            'obj'                               => [(object)[], false],
            'obj v'                             => [(object)[], '', false],
            'no tld'                            => ['user@example', false],
            'no tld v'                          => ['user@example', 'user@example', false],
            'no tld brackets'                   => ['(user@example)', false],
            'no tld brackets v'                 => ['(user@example)', 'user@example', false],
            'no domain'                         => ['user', false],
            'no domain v'                       => ['user', 'user', false],
            'no domain no at'                   => ['user@', false],
            'no domain no at v'                 => ['user@', 'user@', false],
            'no domain umlaut'                  => ['(info@ümlüt)', false],
            'no domain umlaut v'                => ['(info@ümlüt)', 'xn--info@mlt-febc', false],
            'no domain no at umlaut'            => ['infoümlüt', false],
            'no domain no at umlaut v'          => ['infoümlüt', 'xn--infomlt-q2ac', false],
            'no domain no at umlaut brackets'   => ['(infoümlüt)', false],
            'no domain no at umlaut brackets v' => ['(infoümlüt)', 'xn--infomlt-u9ac', false],
            'multi at'                          => ['user@example@com', false],
            'multi at v'                        => ['user@example@com', 'user@example@com', false],
            'normal'                            => ['user@example.com', 'user@example.com'],
            'normal v'                          => ['user@example.com', 'user@example.com', false],
            'brackets'                          => ['(user@example.com)', 'user@example.com'],
            'brackets2'                         => ['(user@example.com)', 'user@example.com', false],
            'umlauts'                           => ['info@ümlüt.de', 'xn--info@mlt-b6ac.de'],
            'umlauts non-utf8'                  => [\utf8_decode('info@ümlüt.de'), 'xn--info@mlt-b6ac.de'],
            'umlauts v'                         => ['info@ümlüt.de', 'xn--info@mlt-b6ac.de', false],
            'umlauts non-utf8 v'                => [\utf8_decode('info@ümlüt.de'), 'xn--info@mlt-b6ac.de', false],
            'umlauts brackets'                  => ['(info@ümlüt.de)', 'xn--info@mlt-v9ac.de'],
            'umlauts brackets v'                => ['(info@ümlüt.de)', 'xn--info@mlt-v9ac.de', false],
        ];
    }

    /**
     * @covers \JTL\Helpers\Text::buildUrl
     */
    public function testBuildUrl(): void
    {
        $arr = [];
        $this->assertSame('', Text::buildUrl($arr));
        $arr['scheme'] = 'https';
        $this->assertSame('https://', Text::buildUrl($arr));
        $arr['user'] = 'somebody';
        $arr['pass'] = 'secret';
        $this->assertSame('https://somebody:secret@', Text::buildUrl($arr));
        $arr['host'] = 'example.com';
        $this->assertSame('https://somebody:secret@example.com', Text::buildUrl($arr));
        $arr['port'] = 12345;
        $this->assertSame('https://somebody:secret@example.com:12345', Text::buildUrl($arr));
        $arr['path'] = '/foo';
        $this->assertSame('https://somebody:secret@example.com:12345/foo', Text::buildUrl($arr));
        $arr['query'] = 'a=1';
        $this->assertSame('https://somebody:secret@example.com:12345/foo?a=1', Text::buildUrl($arr));
        $arr['fragment'] = 'bar';
        $this->assertSame('https://somebody:secret@example.com:12345/foo?a=1#bar', Text::buildUrl($arr));
        $arr['foobar'] = 'baz';
        $this->assertSame('https://somebody:secret@example.com:12345/foo?a=1#bar', Text::buildUrl($arr));
    }

    /**
     * @covers \JTL\Helpers\Text::checkPhoneNumber
     */
    public function testCheckPhoneNumber(): void
    {
        $this->assertSame(1, Text::checkPhoneNumber(''));
        $this->assertSame(0, Text::checkPhoneNumber('', false));
        $this->assertSame(0, Text::checkPhoneNumber('123456'));
        $this->assertSame(0, Text::checkPhoneNumber('030-123456'));
        $this->assertSame(0, Text::checkPhoneNumber('030/123456'));
        $this->assertSame(0, Text::checkPhoneNumber('+49 030/123456'));
        $this->assertSame(0, Text::checkPhoneNumber('+49 030 123456'));
        $this->assertSame(0, Text::checkPhoneNumber('+49 030 - 123456'));
        $this->assertSame(2, Text::checkPhoneNumber('030a123456'));
        $this->assertSame(2, Text::checkPhoneNumber('a123456'));
        $this->assertSame(2, Text::checkPhoneNumber('a123456'));
    }

    /**
     * @covers \JTL\Helpers\Text::checkDate
     */
    public function testCheckDate(): void
    {
        $this->assertSame(2, Text::checkDate('1234'));
        $this->assertSame(2, Text::checkDate('1234', false));
        $this->assertSame(1, Text::checkDate(''));
        $this->assertSame(0, Text::checkDate('', false));
        $this->assertSame(2, Text::checkDate('2021-09-01'));
        $this->assertSame(2, Text::checkDate('2021-09-01', false));
        $this->assertSame(0, Text::checkDate('01.09.2021'));
        $this->assertSame(0, Text::checkDate('01.09.2021', false));
        $this->assertSame(3, Text::checkDate('13.14.2021'));
        $this->assertSame(3, Text::checkDate('13.14.2021', false));
        $this->assertSame(0, Text::checkDate('01.01.0001'));
        $this->assertSame(0, Text::checkDate('01.01.0001', false));
        $this->assertSame(0, Text::checkDate('1.9.1984'));
        $this->assertSame(0, Text::checkDate('1.9.1984', false));
        $this->assertSame(2, Text::checkDate('1.9.84'));
        $this->assertSame(2, Text::checkDate('1.9.84', false));
        $this->assertSame(2, Text::checkDate('May 01 1970'));
        $this->assertSame(2, Text::checkDate('May 01 1970', false));
    }

    /**
     * @covers \JTL\Helpers\Text::formatSize
     */
    public function testFormatSize(): void
    {
        $size = 1024;
        $this->assertSame('1024.00 b', Text::formatSize($size));
        $this->assertSame('1024 b', Text::formatSize($size, '%.0f'));
        $this->assertSame('1024.0000 b', Text::formatSize($size, '%.4f'));
        $size *= 1024;
        $this->assertSame('1024.00 Kb', Text::formatSize($size));
        $size *= 1024;
        $this->assertSame('1024.00 Mb', Text::formatSize($size));
        $size *= 1024;
        $this->assertSame('1024.00 Gb', Text::formatSize($size));
        $size *= 1024;
        $this->assertSame('1024.00 Tb', Text::formatSize($size));
        $size *= 1024;
        $this->assertSame('1024.00 Pb', Text::formatSize($size));
        $size *= 1024;
        $this->assertSame('1024.00 Eb', Text::formatSize($size));
        $size *= 1024;
        $this->assertSame('1024.00 Zb', Text::formatSize($size));
        $size *= 1024;
        $this->assertSame('1024.00 Yb', Text::formatSize($size));
        $size *= 1024;
        $this->assertSame('1048576.00 Yb', Text::formatSize($size));
    }

    /**
     * @covers \JTL\Helpers\Text::utf8_convert_recursive
     */
    public function testUtf8_convert_recursive(): void
    {
        $arr = ['täst', 'foo' => ['foobar', 44], 1];
        $this->assertSame($arr, Text::utf8_convert_recursive($arr));
        $utf8arr = [\utf8_decode('täst'), 'foo' => ['foobar', 44], 1];
        $this->assertSame($arr, Text::utf8_convert_recursive($utf8arr));
    }

    /**
     * @covers \JTL\Helpers\Text::json_safe_encode
     */
    public function testJson_safe_encode(): void
    {
        $json = '{"0":"t\u00e4st","foo":["foobar",44],"1":1}';
        $arr  = ['täst', 'foo' => ['foobar', 44], 1];
        $this->assertSame($json, Text::json_safe_encode($arr));
        $utf8arr = [\utf8_decode('täst'), 'foo' => ['foobar', 44], 1];
        $this->assertSame($json, Text::json_safe_encode($utf8arr));
    }

    /**
     * @covers \JTL\Helpers\Text::removeNumerousWhitespaces
     */
    public function testRemoveNumerousWhitespaces(): void
    {
        $expected = ' 123 456 ';
        $this->assertSame($expected, Text::removeNumerousWhitespaces($expected . '      '));
        $this->assertSame($expected, Text::removeNumerousWhitespaces('     ' . $expected . '      '));
    }

    /**
     * @covers \JTL\Helpers\Text::replaceUmlauts
     */
    public function testReplaceUmlauts(): void
    {
        $this->assertSame('test', Text::replaceUmlauts('test'));
        $this->assertSame('taest', Text::replaceUmlauts('täst'));
        $this->assertSame('tAest', Text::replaceUmlauts('tÄst'));
        $this->assertSame('oelig', Text::replaceUmlauts('ölig'));
        $this->assertSame('Oel', Text::replaceUmlauts('Öl'));
        $this->assertSame('suess', Text::replaceUmlauts('süß'));
        $this->assertSame('Uebel', Text::replaceUmlauts('Übel'));
        $this->assertSame('taest', Text::replaceUmlauts('tæst'));
    }
}
