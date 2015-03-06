<?php

/*
* ------------------------------------------------------------------------------
*                               한글 키보드 변환기
* ------------------------------------------------------------------------------
*
* Copyright (c) 2015, Kijin Sung <kijin@kijinsung.com>
*
* All rights reserved.
*
* Permission is hereby granted, free of charge, to any person obtaining a copy
* of this software and associated documentation files (the "Software"), to
* deal in the Software without restriction, including without limitation
* the right to use, copy, modify, merge, publish, distribute, sublicense,
* and/or sell copies of the Software, and to permit persons to whom the
* Software is furnished to do so, subject to the following conditions:
*
* The above copyright notice and this permission notice shall be included
* in all copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
* AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
* LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
* OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
* THE SOFTWARE.
*/

class Hangeul_Keyboard
{
    // 영문 (QWERTY) 키보드에서 입력한 내용을 한글로 변환한다.
    
    public static function convert($str)
    {
        // 빈 문자열은 처리하지 않는다.
        
        if ($str === '') return '';
        
        // 문자열을 한 글자씩 자른다.
        
        $chars = preg_split('//u', $str);
        
        // 변환 과정에 사용할 변수들.
        
        $interim = array();
        $last_char = 0;
        $skip_next = 0;
        
        // 각 문자를 처리한다.
        
        foreach ($chars as $i => $char)
        {
            // 겹자음, 겹모음 때문에 다음 문자를 건너뛰도록 설정한 경우.
            
            if ($skip_next)
            {
                $skip_next = 0;
                continue;
            }
            
            // 2바이트 이상의 문자는 그대로 반환한다.
            
            elseif (strlen($char) > 1)
            {
                $interim[] = $char;
                $last_char = 0;
            }
            
            // 숫자와 특수기호 등은 그대로 반환한다.
            
            elseif (!ctype_alpha($char))
            {
                $interim[] = $char;
                $last_char = 0;
            }
            
            // 그 밖의 문자는 한글로 변환한다.
            
            else
            {
                // 초성인 경우.
                
                if (($last_char == 0 || $last_char == 3) && isset(self::$charmap1[$char]))
                {
                    $interim[] = array(self::$charmap1[$char]);
                    $last_char = 1;
                    continue;
                }
                
                // 중성인 경우.
                
                if ($last_char == 1 && isset(self::$charmap2[$char]))
                {
                    // 겹모음 처리.
                    
                    if (count($chars) > $i + 1)
                    {
                        $next_char = $chars[$i + 1];
                        if (isset(self::$charmap2[$char . $next_char]))
                        {
                            $skip_next = 1;
                            $char = $char . $next_char;
                        }
                    }
                    
                    $interim[count($interim) - 1][] = self::$charmap2[$char];
                    $last_char = 2;
                    continue;
                }
                
                // 종성인 경우.
                
                if ($last_char == 2 && isset(self::$charmap3[$char]))
                {
                    // 겹자음 처리.
                    
                    if (count($chars) > $i + 1)
                    {
                        $next_char = $chars[$i + 1];
                        if (isset(self::$charmap3[$char . $next_char]))
                        {
                            $skip_next = 1;
                            $char = $char . $next_char;
                        }
                    }
                    
                    $interim[count($interim) - 1][] = self::$charmap3[$char];
                    $last_char = 3;
                    continue;
                }
                
                // 종성 후에 중성이 다시 나온 경우 앞의 종성을 초성으로 바꾼다.
                
                if ($last_char == 3 && isset(self::$charmap2[$char]))
                {
                    // 겹모음 처리.
                    
                    if (count($chars) > $i + 1)
                    {
                        $next_char = $chars[$i + 1];
                        if (isset(self::$charmap2[$char . $next_char]))
                        {
                            $skip_next = 1;
                            $char = $char . $next_char;
                        }
                    }
                    
                    if (isset($interim[count($interim) - 1][2]))
                    {
                        $last_batchim = $interim[count($interim) - 1][2];
                        $last_batchim = array_search($last_batchim, self::$charmap3);
                        if (strlen($last_batchim) == 1)
                        {
                            $interim[count($interim) - 1][2] = 0;
                            $interim[] = array(
                                self::$charmap1[$last_batchim],
                                self::$charmap2[$char],
                            );
                        }
                        elseif (strlen($last_batchim) == 2)
                        {
                            $interim[count($interim) - 1][2] = self::$charmap3[substr($last_batchim, 0, 1)];
                            $interim[] = array(
                                self::$charmap1[substr($last_batchim, 1, 1)],
                                self::$charmap2[$char],
                            );
                        }
                        $last_char = 2;
                    }
                    continue;
                }
            }
        }
        
        // 반환할 문자열을 조합한다.
        
        $output = '';
        foreach ($interim as $char)
        {
            // 한글인 경우 유니코드 코드 포인트 번호를 이용한다.
            
            if (is_array($char))
            {
                if (count($char) < 3)
                {
                    $char[] = 0;
                    $char[] = 0;
                }
                
                $char_index = ($char[0] * 21 * 28) + ($char[1] * 28) + $char[2] + 44032;
                $output .= html_entity_decode('&#' . $char_index . ';');
            }
            
            // 그 밖의 문자는 그대로 반환한다.
            
            else
            {
                $output .= $char;
            }
        }
        
        // 결과를 반환한다.
        
        return $output;
    }
    
    // 초성 목록.
    
    protected static $charmap1 = array(
        'r'  =>  0,  // ㄱ
        'R'  =>  1,  // ㄲ
        's'  =>  2,  // ㄴ
        'e'  =>  3,  // ㄷ
        'E'  =>  4,  // ㄸ
        'f'  =>  5,  // ㄹ
        'a'  =>  6,  // ㅁ
        'q'  =>  7,  // ㅂ
        'Q'  =>  8,  // ㅃ
        't'  =>  9,  // ㅅ
        'T'  => 10,  // ㅆ
        'd'  => 11,  // ㅇ
        'w'  => 12,  // ㅈ
        'W'  => 13,  // ㅉ
        'c'  => 14,  // ㅊ
        'z'  => 15,  // ㅋ
        'x'  => 16,  // ㅌ
        'v'  => 17,  // ㅍ
        'g'  => 18,  // ㅎ
    );
    
    // 중성 목록.
    
    protected static $charmap2 = array(
        'k'  =>  0,  // ㅏ
        'o'  =>  1,  // ㅐ
        'i'  =>  2,  // ㅑ
        'O'  =>  3,  // ㅒ
        'j'  =>  4,  // ㅓ
        'p'  =>  5,  // ㅔ
        'u'  =>  6,  // ㅕ
        'P'  =>  7,  // ㅖ
        'h'  =>  8,  // ㅗ
        'hk' =>  9,  // ㅘ
        'ho' => 10,  // ㅙ
        'hl' => 11,  // ㅚ
        'y'  => 12,  // ㅛ
        'n'  => 13,  // ㅜ
        'nj' => 14,  // ㅝ
        'np' => 15,  // ㅞ
        'nl' => 16,  // ㅟ
        'b'  => 17,  // ㅠ
        'm'  => 18,  // ㅡ
        'ml' => 19,  // ㅢ
        'l'  => 20,  // ㅣ
    );
    
    // 종성 목록.
    
    protected static $charmap3 = array(
        '0'  =>  0,  // 받침이 없는 경우
        'r'  =>  1,  // ㄱ
        'R'  =>  2,  // ㄲ
        'rt' =>  3,  // ㄳ
        's'  =>  4,  // ㄴ
        'sw' =>  5,  // ㄵ
        'sg' =>  6,  // ㄶ
        'e'  =>  7,  // ㄷ
        'f'  =>  8,  // ㄹ
        'fr' =>  9,  // ㄺ
        'fa' => 10,  // ㄻ
        'fq' => 11,  // ㄼ
        'ft' => 12,  // ㄽ
        'fx' => 13,  // ㄾ
        'fv' => 14,  // ㄿ
        'fg' => 15,  // ㅀ
        'a'  => 16,  // ㅁ
        'q'  => 17,  // ㅂ
        'qt' => 18,  // ㅄ
        't'  => 19,  // ㅅ
        'T'  => 20,  // ㅆ
        'd'  => 21,  // ㅇ
        'w'  => 22,  // ㅈ
        'c'  => 23,  // ㅊ
        'z'  => 24,  // ㅋ
        'x'  => 25,  // ㅌ
        'v'  => 26,  // ㅍ
        'g'  => 27,  // ㅎ
    );
}
