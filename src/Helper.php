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

namespace Opis\Uri;

use RuntimeException;

/**
 * @internal
 */
class Helper
{
    public static function getStrBytes(string $str): iterable
    {
        // Inspired by opis/string

        $i = 0;
        $length = strlen($str);

        while ($i < $length) {
            $index = $i;

            $ord0 = ord($str[$i++]);

            if ($ord0 < 0x80) {
                yield $index => [$ord0];
                continue;
            }

            if ($i === $length || $ord0 < 0xC2 || $ord0 > 0xF4) {
                throw self::createInvalidOffsetException($str, $i - 1);
            }

            $ord1 = ord($str[$i++]);

            if ($ord0 < 0xE0) {
                if ($ord1 < 0x80 || $ord1 >= 0xC0) {
                    throw self::createInvalidOffsetException($str, $i - 1);
                }

                yield $index => [$ord0, $ord1];

                continue;
            }

            if ($i === $length) {
                throw self::createInvalidOffsetException($str, $i - 1);
            }

            $ord2 = ord($str[$i++]);

            if ($ord0 < 0xF0) {
                if ($ord0 === 0xE0) {
                    if ($ord1 < 0xA0 || $ord1 >= 0xC0) {
                        throw self::createInvalidOffsetException($str, $i - 2);
                    }
                } elseif ($ord0 === 0xED) {
                    if ($ord1 < 0x80 || $ord1 >= 0xA0) {
                        throw self::createInvalidOffsetException($str, $i - 2);
                    }
                } elseif ($ord1 < 0x80 || $ord1 >= 0xC0) {
                    throw self::createInvalidOffsetException($str, $i - 2);
                }

                if ($ord2 < 0x80 || $ord2 >= 0xC0) {
                    throw self::createInvalidOffsetException($str, $i - 1);
                }

                yield $index => [$ord0, $ord1, $ord2];

                continue;
            }

            if ($i === $length) {
                throw self::createInvalidOffsetException($str, $i - 1);
            }

            $ord3 = ord($str[$i++]);

            if ($ord0 < 0xF5) {
                if ($ord0 === 0xF0) {
                    if ($ord1 < 0x90 || $ord1 >= 0xC0) {
                        throw self::createInvalidOffsetException($str, $i - 3);
                    }
                } elseif ($ord0 === 0xF4) {
                    if ($ord1 < 0x80 || $ord1 >= 0x90) {
                        throw self::createInvalidOffsetException($str, $i - 3);
                    }
                } elseif ($ord1 < 0x80 || $ord1 >= 0xC0) {
                    throw self::createInvalidOffsetException($str, $i - 3);
                }

                if ($ord2 < 0x80 || $ord2 >= 0xC0) {
                    throw self::createInvalidOffsetException($str, $i - 2);
                }

                if ($ord3 < 0x80 || $ord3 >= 0xC0) {
                    throw self::createInvalidOffsetException($str, $i - 1);
                }

                yield $index => [$ord0, $ord1, $ord2, $ord3];

                continue;
            }

            throw self::createInvalidOffsetException($str, $i - 1);
        }
    }

    private static function createInvalidOffsetException(string $str, int $offset): RuntimeException
    {
        return new RuntimeException("Invalid byte at offset {$offset}: {$str}");
    }
}