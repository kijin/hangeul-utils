PHP 한글 유틸
=============

한글 처리와 관련된 클래스 몇 개를 오픈소스(MIT 라이선스)로 배포합니다.
PHP 5.2 이상, `mbstring` 모듈, UTF-8 환경에서 사용할 수 있습니다.

Hangeul_Keyboard
---------------

영문 (QWERTY) 키보드에서 입력한 내용을 한글로 변환하는 클래스입니다.
검색어 등을 입력할 때 실수로 한/영 키를 누르지 않더라도 검색이 되도록 하는 데 사용합니다.
영문 알파벳이 아닌 문자는 그대로 반환합니다.

    $str = 'ekfkawnl gjs cptqkznldp xkrhvk';
    $str = Hangeul_Keyboard::convert($str);
    echo $str;  // 다람쥐 헌 쳇바퀴에 타고파

Hangeul_Romaja
--------------

주어진 한글 문자열을 국어 로마자 표기법에 맞게 변환하는 클래스입니다.
일반적인 자음동화도 자동으로 처리 가능합니다.
한글이 아닌 문자는 그대로 반환합니다.

    $str = '다람쥐 헌 쳇바퀴에 타고파';
    $str = Hangeul_Romaja::convert($str);
    echo $str;  // daramjwi heon chepbakwie tagopa

사람 이름인 경우 `TYPE_NAME` 설정을 사용하면 됩니다.

    $str = Hangeul_Romaja::convert('홍길동', Hangeul_Romaja::TYPE_NAME);
    echo $str;  // Hong Gildong

주소인 경우 `TYPE_ADDRESS` 설정을 사용하면 됩니다.

    $str = Hangeul_Romaja::convert('서울 종로5가 123-4', Hangeul_Romaja::TYPE_ADDRESS);
    echo $str;  // 123-4, Jongno 5-ga, Seoul

그 밖의 옵션들은 세 번째 인자에 넣으면 됩니다.

  - `Hangeul_Romaja::CAPITALIZE_FIRST` : 첫 글자를 대문자로 바꿉니다.
  - `Hangeul_Romaja::CAPITALIZE_WORDS` : 각 단어의 첫 글자를 대문자로 바꿉니다.
  - `Hangeul_Romaja::PRONOUNCE_NUMBERS` : 숫자에 해당하는 발음을 로마자로 표시하여 괄호 안에 병기합니다.

두 가지 이상의 옵션을 사용하려면 bitwise OR 연산자로 조합하면 됩니다.

    Hangeul_Romaja::CAPITALIZE_WORDS | Hangeul_Romaja::PRONOUNCE_NUMBERS

흔하지 않은 경우의 자음동화는 적용되지 않을 수 있으며,
이런 사례를 신고해 주시면 고쳐 드리겠습니다.

단, "원리"의 "ㄴㄹ"(→"ll")과 "남대문로"의 "ㄴㄹ"(→"nn")처럼
동일한 문자 조합이 경우에 따라 다르게 발음되는 경우에는
둘 중 한 가지만 제대로 적용될 수도 있으니 유의하시기 바랍니다.
