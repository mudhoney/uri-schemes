<?php

/**
 * League.Uri (http://uri.thephpleague.com).
 *
 * @package    League\Uri
 * @subpackage League\Uri\Schemes
 * @author     Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @license    https://github.com/thephpleague/uri-components/blob/master/LICENSE (MIT License)
 * @version    2.0.0
 * @link       https://github.com/thephpleague/uri-schemes
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LeagueTest\Uri;

use League\Uri\Exception\InvalidUri;
use League\Uri\Exception\MalformedUri;
use League\Uri\Factory;
use League\Uri\File;
use League\Uri\Ftp;
use League\Uri\Http;
use League\Uri\Uri;
use PHPUnit\Framework\TestCase;
use TypeError;

/**
 * @coversDefaultClass \League\Uri\Factory
 * @group factory
 */
class FactoryTest extends TestCase
{
    public function testCreateThrowExceptionWithBaseUriNotAbsolute(): void
    {
        self::expectException(InvalidUri::class);
        Factory::create('/path/to/you', Http::createFromString('//example.com'));
    }

    public function testCreateThrowExceptionWithUriNotAbsolute(): void
    {
        self::expectException(InvalidUri::class);
        Factory::create('/path/to/you');
    }

    public function testCreateThrowExceptionWithUnsupportedType(): void
    {
        self::expectException(TypeError::class);
        Factory::create(null);
    }

    /**
     * @dataProvider uriProvider
     */
    public function testCreate(string $expected, string $uri): void
    {
        self::assertInstanceOf($expected, Factory::create($uri));
    }

    public function uriProvider(): array
    {
        return [
            'http' => [
                'expected' => Http::class,
                'uri' => 'http://www.example.com',
            ],
            'https' => [
                'expected' => Http::class,
                'uri' => 'https://www.example.com',
            ],
            'ftp' => [
                'expected' => Ftp::class,
                'uri' => 'ftp://www.example.com',
            ],
            'generic' => [
                'expected' => Uri::class,
                'uri' => 'mailto:info@thephpleague.com',
            ],
            'multiline URI' => [
                'expected' => Http::class,
                'uri' => 'http://www'.PHP_EOL.'example.com ',
            ],
        ];
    }

    /**
     * @dataProvider uriBaseUriProvider
     *
     * @covers \League\Uri\Resolver
     * @covers ::create
     * @covers ::sanitizeComponents
     * @covers ::formatUri
     *
     * @param string|mixed $base_uri
     */
    public function testCreateWithBaseUri(string $expected_class, string $expected_uri, string $uri, $base_uri): void
    {
        $obj = Factory::create($uri, $base_uri);
        self::assertInstanceOf($expected_class, $obj);
        self::assertSame($expected_uri, (string) $obj);
    }

    public function uriBaseUriProvider(): array
    {
        $base_uri = 'https://example.com/index.php';

        return [
            'empty URI' => [
                'expected_class' => Http::class,
                'expected_uri' => 'https://example.com/index.php',
                'uri' => '',
                'base_uri' => $base_uri,
            ],
            'uri with absolute path' => [
                'expected_class' => Http::class,
                'expected_uri' => 'https://example.com/path/to/file',
                'uri' => '/path/to/file',
                'base_uri' => $base_uri,
            ],
            'uri with authority' => [
                'expected_class' => Http::class,
                'expected_uri' => 'https://toto.com/path/to/file',
                'uri' => '//toto.com/path/to/file',
                'base_uri' => $base_uri,
            ],
            'uri with relative path' => [
                'expected_class' => Http::class,
                'expected_uri' => 'https://example.com/path/here.png',
                'uri' => 'path/here.png',
                'base_uri' => $base_uri,
            ],
            'uri with query' => [
                'expected_class' => Http::class,
                'expected_uri' => 'https://example.com/index.php?foo=bar',
                'uri' => '?foo=bar',
                'base_uri' => $base_uri,
            ],
            'uri with another scheme' => [
                'expected_class' => Ftp::class,
                'expected_uri' => 'ftp://example.com/to/file.csv',
                'uri' => 'ftp://example.com/to/file.csv',
                'base_uri' => $base_uri,
            ],
            'uri with dot segments (1)' => [
                'expected_class' => Http::class,
                'expected_uri' => 'https://example.com/to/the/sky.php',
                'uri' => '/path/../to/the/./sky.php',
                'base_uri' => $base_uri,
            ],
            'uri with dot segments (2)' => [
                'expected_class' => Http::class,
                'expected_uri' => 'https://example.com/to/the/sky/',
                'uri' => '/path/../to/the/./sky/.',
                'base_uri' => $base_uri,
            ],
            'uri with dot segments (3)' => [
                'expected_class' => Http::class,
                'expected_uri' => 'http://h:b@a/y',
                'uri' => 'b/../y',
                'base_uri' => Http::createFromString('http://h:b@a'),
            ],
            'uri with dot segments (4)' => [
                'expected_class' => Http::class,
                'expected_uri' => 'http://a/b/c/g',
                'uri' => './g',
                'base_uri' => Http::createFromString('http://a/b/c/d;p?q'),
            ],
            'uri with a base URI as string' => [
                'expected_class' => Http::class,
                'expected_uri' => 'https://example.com/path/to/file',
                'uri' => 'https://example.com/path/to/file',
                'base_uri' => 'ftp://example.com/index.php',
            ],
            'uri with a base URI as league URI' => [
                'expected_class' => Http::class,
                'expected_uri' => 'https://example.com/path/to/file',
                'uri' => 'https://example.com/path/to/file',
                'base_uri' => Ftp::createFromString('ftp://example.com/index.php'),
            ],
            'living standard #1' => [
                'expected_class' => Http::class,
                'expected_uri' => 'https://example.org',
                'uri' => 'https:example.org ',
                'base_uri' => null,
            ],
            'living standard #2' => [
                'expected_class' => Http::class,
                'expected_uri' => 'https://example.com///',
                'uri' => 'https://////example.com///',
                'base_uri' => null,
            ],
            'living standard #3' => [
                'expected_class' => Http::class,
                'expected_uri' => 'https://example.com/foo',
                'uri' => 'https://example.com/././foo ',
                'base_uri' => null,
            ],
            'living standard #4' => [
                'expected_class' => Uri::class,
                'expected_uri' => 'hello:world',
                'uri' => 'hello:world',
                'base_uri' => 'https://example.com/',
            ],
            'living standard #5' => [
                'expected_class' => Http::class,
                'expected_uri' => 'https://example.com/example.org',
                'uri' => 'https:example.org',
                'base_uri' => 'https://example.com/',
            ],
            'living standard #6' => [
                'expected_class' => Http::class,
                'expected_uri' => 'https://example.com/demo/',
                'uri' => '\example\..\demo/.\\',
                'base_uri' => 'https://example.com/',
            ],
            'living standard #7' => [
                'expected_class' => Http::class,
                'expected_uri' => 'https://example.com/example',
                'uri' => 'example',
                'base_uri' => 'https://example.com/demo',
            ],
            'living standard #8' => [
                'expected_class' => File::class,
                'expected_uri' => 'file://localhost/C:/demo',
                'uri' => 'file:///C|/demo',
                'base_uri' => null,
            ],
            'living standard #9' => [
                'expected_class' => File::class,
                'expected_uri' => 'file://localhost/C:/',
                'uri' => '..',
                'base_uri' => 'file:///C:/demo',
            ],
            'living standard #10' => [
                'expected_class' => File::class,
                'expected_uri' => 'file://localhost/',
                'uri' => 'file://loc%61lhost/',
                'base_uri' => null,
            ],
            'remove Gopher Port' => [
                'expected_class' => Uri::class,
                'expected_uri' => 'gopher://example.com/example',
                'uri' => 'example',
                'base_uri' => 'gopher://example.com:70/demo',
            ],
            'non special URI' => [
                'expected_class' => Uri::class,
                'expected_uri' => 'non-special://test:@test/x',
                'uri' => 'non-special://test:@test/x',
                'base_uri' => 'about:blank',
            ],
            'non valid path #1' => [
                'expected_class' => Http::class,
                'expected_uri' => 'http://example.org/foo/:23',
                'uri' => ':23',
                'base_uri' => 'http://example.org/foo/bar',
            ],
            'non valid path #2' => [
                'expected_class' => Http::class,
                'expected_uri' => 'http://example.org/foo/:@c:29',
                'uri' => 'http::@c:29',
                'base_uri' => 'http://example.org/foo/bar',
            ],
        ];
    }

    /**
     * @dataProvider resolveProvider
     */
    public function testCreateResolve(string $base_uri, string $uri, string $expected): void
    {
        self::assertSame($expected, (string) Factory::create($uri, $base_uri));
    }

    public function resolveProvider(): array
    {
        $base_uri = 'http://a/b/c/d;p?q';

        return [
            'base uri'                => [$base_uri, '',              $base_uri],
            'scheme'                  => [$base_uri, 'http://d/e/f',  'http://d/e/f'],
            'path 1'                  => [$base_uri, 'g',             'http://a/b/c/g'],
            'path 2'                  => [$base_uri, './g',           'http://a/b/c/g'],
            'path 3'                  => [$base_uri, 'g/',            'http://a/b/c/g/'],
            'path 4'                  => [$base_uri, '/g',            'http://a/g'],
            'authority'               => [$base_uri, '//g',           'http://g'],
            'query'                   => [$base_uri, '?y',            'http://a/b/c/d;p?y'],
            'path + query'            => [$base_uri, 'g?y',           'http://a/b/c/g?y'],
            'fragment'                => [$base_uri, '#s',            'http://a/b/c/d;p?q#s'],
            'path + fragment'         => [$base_uri, 'g#s',           'http://a/b/c/g#s'],
            'path + query + fragment' => [$base_uri, 'g?y#s',         'http://a/b/c/g?y#s'],
            'single dot 1'            => [$base_uri, '.',             'http://a/b/c/'],
            'single dot 2'            => [$base_uri, './',            'http://a/b/c/'],
            'single dot 3'            => [$base_uri, './g/.',         'http://a/b/c/g/'],
            'single dot 4'            => [$base_uri, 'g/./h',         'http://a/b/c/g/h'],
            'double dot 1'            => [$base_uri, '..',            'http://a/b/'],
            'double dot 2'            => [$base_uri, '../',           'http://a/b/'],
            'double dot 3'            => [$base_uri, '../g',          'http://a/b/g'],
            'double dot 4'            => [$base_uri, '../..',         'http://a/'],
            'double dot 5'            => [$base_uri, '../../',        'http://a/'],
            'double dot 6'            => [$base_uri, '../../g',       'http://a/g'],
            'double dot 7'            => [$base_uri, '../../../g',    'http://a/g'],
            'double dot 8'            => [$base_uri, '../../../../g', 'http://a/g'],
            'double dot 9'            => [$base_uri, 'g/../h' ,       'http://a/b/c/h'],
            'mulitple slashes'        => [$base_uri, 'foo////g',      'http://a/b/c/foo////g'],
            'complex path 1'          => [$base_uri, ';x',            'http://a/b/c/;x'],
            'complex path 2'          => [$base_uri, 'g;x',           'http://a/b/c/g;x'],
            'complex path 3'          => [$base_uri, 'g;x?y#s',       'http://a/b/c/g;x?y#s'],
            'complex path 4'          => [$base_uri, 'g;x=1/./y',     'http://a/b/c/g;x=1/y'],
            'complex path 5'          => [$base_uri, 'g;x=1/../y',    'http://a/b/c/y'],
            'dot segments presence 1' => [$base_uri, '/./g',          'http://a/g'],
            'dot segments presence 2' => [$base_uri, '/../g',         'http://a/g'],
            'dot segments presence 3' => [$base_uri, 'g.',            'http://a/b/c/g.'],
            'dot segments presence 4' => [$base_uri, '.g',            'http://a/b/c/.g'],
            'dot segments presence 5' => [$base_uri, 'g..',           'http://a/b/c/g..'],
            'dot segments presence 6' => [$base_uri, '..g',           'http://a/b/c/..g'],
            'origin uri without path' => ['http://h:b@a', 'b/../y',   'http://h:b@a/y'],
        ];
    }

    public function testCreateAlwaysResolveUri(): void
    {
        self::assertSame(
            (string) Factory::create('../cats', 'http://www.example.com/dogs'),
            (string) Factory::create('http://www.example.com/dogs/../cats')
        );
    }

    public function testCreateThrowsExceptionIftheBaseURLIsNotValid(): void
    {
        self::expectException(MalformedUri::class);
        Factory::create('../cats', 'data:,');
    }
}
