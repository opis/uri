<?php
/* ============================================================================
 * Copyright 2021 Zindex Software
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ============================================================================ */

namespace Opis\Uri\Test;

use Opis\Uri\UriTemplate;
use PHPUnit\Framework\TestCase;

class UriTemplateTest extends TestCase
{
    /**
     * @dataProvider rfc6570DataProvider
     * @dataProvider resolveDataProvider
     */
    public function testResolve(string $template, string $result, array $vars)
    {
        $this->assertEquals($result, (new UriTemplate($template))->resolve($vars));
    }

    public function rfc6570DataProvider(): array
    {
        // https://tools.ietf.org/html/rfc6570#section-1.2

        $vars_1 = ['var' => 'value', 'hello' => 'Hello World!'];
        $vars_2 = $vars_1 + ['path' => '/foo/bar'];
        $vars_3 = $vars_2 + ['empty' => '', 'x' => 1024, 'y' => 768];
        $vars_4 = $vars_2 + [
            'list' => ['red', 'green', 'blue'],
            'keys' => [
                'semi' => ';',
                'dot' => '.',
                'comma' => ',',
            ],
        ];

        return [
            // Level 1

            // Simple string expansion
            ['{var}', 'value', $vars_1],
            ['{hello}', 'Hello%20World%21', $vars_1],

            // Level 2

            // + Reserved string expansion
            ['{+var}', 'value', $vars_2],
            ['{+hello}', 'Hello%20World!', $vars_2],
            ['{+path}/here', '/foo/bar/here', $vars_2],
            ['here?ref={+path}', 'here?ref=/foo/bar', $vars_2],

            // # Fragment expansion, crosshatch-prefixed
            ['X{#var}', 'X#value', $vars_2],
            ['X{#hello}', 'X#Hello%20World!', $vars_2],

            // Level 3

            // + Reserved expansion with multiple variables
            ['{+x,hello,y}', '1024,Hello%20World!,768', $vars_3],
            ['{+path,x}/here', '/foo/bar,1024/here', $vars_3],

            // # Fragment expansion with multiple variables
            ['{#x,hello,y}', '#1024,Hello%20World!,768', $vars_3],
            ['{#path,x}/here', '#/foo/bar,1024/here', $vars_3],

            // . Label expansion, dot-prefixed
            ['X{.var}', 'X.value', $vars_3],
            ['X{.x,y}', 'X.1024.768', $vars_3],

            // / Path segments, slash-prefixed
            ['{/var}', '/value', $vars_3],
            ['{/var,x}/here', '/value/1024/here', $vars_3],

            // ; Path-style parameters, semicolon-prefixed
            ['{;x,y}', ';x=1024;y=768', $vars_3],
            ['{;x,y,empty}', ';x=1024;y=768;empty', $vars_3],

            // ? Form-style query, ampersand-separated
            ['{?x,y}', '?x=1024&y=768', $vars_3],
            ['{?x,y,empty}', '?x=1024&y=768&empty=', $vars_3],

            // & Form-style query continuation
            ['?fixed=yes{&x}', '?fixed=yes&x=1024', $vars_3],
            ['{&x,y,empty}', '&x=1024&y=768&empty=', $vars_3],

            // Level 4

            // String expansion with value modifiers
            ['{var:3}', 'val', $vars_4],
            ['{var:30}', 'value', $vars_4],
            ['{list}', 'red,green,blue', $vars_4],
            ['{list*}', 'red,green,blue', $vars_4],
            ['{keys}', 'semi,%3B,dot,.,comma,%2C', $vars_4],
            ['{keys*}', 'semi=%3B,dot=.,comma=%2C', $vars_4],

            // + Reserved expansion with value modifiers
            ['{+path:6}/here', '/foo/b/here', $vars_4],
            ['{+list}', 'red,green,blue', $vars_4],
            ['{+list*}', 'red,green,blue', $vars_4],
            ['{+keys}', 'semi,;,dot,.,comma,,', $vars_4],
            ['{+keys*}', 'semi=;,dot=.,comma=,', $vars_4],

            // # Fragment expansion with value modifiers
            ['{#path:6}/here', '#/foo/b/here', $vars_4],
            ['{#list}', '#red,green,blue', $vars_4],
            ['{#list*}', '#red,green,blue', $vars_4],
            ['{#keys}', '#semi,;,dot,.,comma,,', $vars_4],
            ['{#keys*}', '#semi=;,dot=.,comma=,', $vars_4],


            // . Label expansion, dot-prefixed
            ['X{.var:3}', 'X.val', $vars_4],
            ['X{.list}', 'X.red,green,blue', $vars_4],
            ['X{.list*}', 'X.red.green.blue', $vars_4],
            ['X{.keys}', 'X.semi,%3B,dot,.,comma,%2C', $vars_4],
            ['X{.keys*}', 'X.semi=%3B.dot=..comma=%2C', $vars_4],

            // / Path segments, slash-prefixed
            ['{/var:1,var}', '/v/value', $vars_4],
            ['{/list}', '/red,green,blue', $vars_4],
            ['{/list*}', '/red/green/blue', $vars_4],
            ['{/list*,path:4}', '/red/green/blue/%2Ffoo', $vars_4],
            ['{/keys}', '/semi,%3B,dot,.,comma,%2C', $vars_4],
            ['{/keys*}', '/semi=%3B/dot=./comma=%2C', $vars_4],

            // ; Path-style parameters, semicolon-prefixed
            ['{;hello:5}', ';hello=Hello', $vars_4],
            ['{;list}', ';list=red,green,blue', $vars_4],
            ['{;list*}', ';list=red;list=green;list=blue', $vars_4],
            ['{;keys}', ';keys=semi,%3B,dot,.,comma,%2C', $vars_4],
            ['{;keys*}', ';semi=%3B;dot=.;comma=%2C', $vars_4],

            // ? Form-style query, ampersand-separated
            ['{?var:3}', '?var=val', $vars_4],
            ['{?list}', '?list=red,green,blue', $vars_4],
            ['{?list*}', '?list=red&list=green&list=blue', $vars_4],
            ['{?keys}', '?keys=semi,%3B,dot,.,comma,%2C', $vars_4],
            ['{?keys*}', '?semi=%3B&dot=.&comma=%2C', $vars_4],

            // & Form-style query continuation
            ['{&var:3}', '&var=val', $vars_4],
            ['{&list}', '&list=red,green,blue', $vars_4],
            ['{&list*}', '&list=red&list=green&list=blue', $vars_4],
            ['{&keys}', '&keys=semi,%3B,dot,.,comma,%2C', $vars_4],
            ['{&keys*}', '&semi=%3B&dot=.&comma=%2C', $vars_4],
        ];
    }

    public function resolveDataProvider(): array
    {
        return [
            [
                'http://www.example.com/foo{?query,number}',
                'http://www.example.com/foo?query=mycelium&number=100',
                ['query' => 'mycelium', 'number' => 100],
            ],
            [
                'http://www.example.com/foo{?query,number}',
                'http://www.example.com/foo?number=100',
                ['number' => 100],
            ],
            [
                '{/a,b*}{?a:1,b*}',
                '/ABC/1/2?a=A&b=1&b=2',
                ['a' => 'ABC', 'b' => [1, 2]]
            ],
        ];
    }
}