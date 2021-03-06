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

use Opis\Uri\{Punycode, PunycodeException};
use PHPUnit\Framework\TestCase;

class PunycodeTest extends TestCase
{
    /**
     * @dataProvider getValidTestData
     * @param string $decoded
     * @param string $encoded
     */
    public function testValidData(string $decoded, string $encoded)
    {
        $p_encoded = Punycode::encode($decoded);
        $p_decoded = Punycode::decode($encoded);

        $this->assertSame($decoded, $p_decoded, 'Decoding ' . $encoded);
        $this->assertSame($encoded, $p_encoded, 'Encoding ' . $decoded);
    }

    public function getValidTestData(): array
    {
        return [
            [
                'opis.io',
                'opis.io',
            ],
            [
                'șăîț.example.com',
                'xn--fda5bx8cka.example.com',
            ],
            // https://en.wikipedia.org/wiki/IDN_Test_TLDs
            [
                'إختبار',
                'xn--kgbechtv',
            ],
            [
                'آزمایشی',
                'xn--hgbk6aj7f53bba',
            ],
            [
                '测试',
                'xn--0zwm56d',
            ],
            [
                '測試',
                'xn--g6w251d',
            ],
            [
                'испытание',
                'xn--80akhbyknj4f',
            ],
            [
                'परीक्षा',
                'xn--11b5bs3a9aj6g',
            ],
            [
                'δοκιμή',
                'xn--jxalpdlp',
            ],
            [
                '테스트',
                'xn--9t4b11yi5a',
            ],
            [
                'טעסט',
                'xn--deba0ad',
            ],
            [
                'テスト',
                'xn--zckzah',
            ],
            [
                'பரிட்சை',
                'xn--hlcj6aya9esc7a',
            ],
        ];
    }

    /**
     * @dataProvider getInvalidTestData
     * @param string $data
     * @param bool $encode
     */
    public function testInvalidData(string $data, bool $encode = false)
    {
        $this->expectException(PunycodeException::class);

        if ($encode) {
            Punycode::encode($data);
        } else {
            Punycode::decode($data);
        }
    }

    public function getInvalidTestData(): array
    {
        return [
            ['xn--xn', false],
            ['xn--șțăî', false],
        ];
    }
}