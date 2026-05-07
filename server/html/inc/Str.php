<?php

class Str implements ArrayAccess, Iterator, Countable {
	protected $str = '';

	protected $pos = 0;

	public function __construct($str = '') {
		$this->str = (string)$str;
	}

	public function str() {
		return $this->str;
	}
	public function __toString() {
		return $this->str;
	}

	public function offsetSet($offset, $value) {
		if (is_null($offset)) {
			$this->str .= $value;
		} else {
			$this->str = self::_substr($this->str, 0, $offset) . $value . self::_substr($this->str, $offset + 1);
		}
	}
	public function offsetExists($offset) {
		$value = self::_substr($this->str, $offset, 1);
		return $value !== '';
	}
	public function offsetUnset($offset) {
		$this->str = self::_substr($this->str, 0, $offset) . self::_substr($this->str, $offset + 1);
	}
	public function offsetGet($offset) {
		$value = self::_substr($this->str, $offset, 1);
		return $value !== '' ? $value : null;
	}
	public function count() {
		return self::_strlen($this->str);
	}

	public function rewind() {
		$this->pos = 0;
	}
	public function current() {
		return $this[$this->pos];
	}
	public function key() {
		return $this->pos;
	}
	public function next() {
		++$this->pos;
	}
	public function valid() {
		return isset($this[$this->pos]);
	}

	static $methods = array(
		'htmlsec', 'unhtmlsec', 'convert', 'base62',
		'len', 'strlen', 'length', 'tolower', 'toupper', 'totitle', 'ucfirst',
		'substr', 'replace', 'remove', 'cut', 'prepend', 'append',
		'hash', 'md5', 'sha1', 'sha256', 'sha512', 'crc32', 'whirlpool', 'fhash',
		'filesize', 'plus', 'numbers', 'letnum',
		'translit', 'ipurl', 'sqltime', 'texter',
		'encrypt', 'decrypt',
	);

	public function __call($func, $args) {
		$func = strtolower($func);
		if (!in_array($func, self::$methods)) throw new Exception('Method ' . $func . ' does not exist');
		array_unshift($args, $this->str);
		return new self(call_user_func_array('self::_' . $func, $args));
	}
	public static function __callStatic($func, $args) {
		$func = strtolower($func);
		if (!in_array($func, self::$methods)) throw new Exception('Method ' . $func . ' does not exist');
		return call_user_func_array('self::_' . $func, $args);
	}

	protected static function _htmlsec($str) {
		return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
	}
	protected static function _unhtmlsec($str) {
		$str = (string)$str;
		$str = str_replace('&lt;',   '<', $str);
		$str = str_replace('&gt;',   '>', $str);
		$str = str_replace('&#039;', "'", $str);
		$str = str_replace('&quot;', '"', $str);
		$str = str_replace('&amp;',  '&', $str);
		return $str;
	}

	protected static function _convert($str, $from, $to = 'UTF-8') {
		$from = strtoupper($form);
		$to = strtoupper($to);
		if ($from == $to) return $str;
		return mb_convert_encoding((string)$str, $to, $from);
	}

	protected static function _base62($str) {
		static $q = array(
			'0','1','2','3','4','5','6','7','8','9',
			'a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z',
			'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z',
		);
		$s = (string)$str;
		$x = array();
		$b = 16;
		$p = 62;
		$l = strlen($s);
		if ($l % $b) $s = str_repeat("0", $b - ($l % $b)) . $s;
		$m = ceil($l / $b);
		for ($i = 0; $i < $m; $i++) {
			$a = substr($s, $i * $b, $b);
			$a = base_convert($a, 16, 10);
			$r = '';
			while ($a) {
				$c = $a % $p;
				$r = $q[$c] . $r;
				$a = floor($a / $p);
			}
			$x[] = $r;
		}
		return join('', $x);
	}

	protected static function _len($str) {
		return mb_strlen((string)$str);
	}
	protected static function _strlen($str) {
		return mb_strlen((string)$str);
	}
	protected static function _length($str) {
		return mb_strlen((string)$str);
	}

	protected static function _toupper($str) {
		return mb_strtoupper((string)$str);
	}

	protected static function _tolower($str) {
		return mb_strtolower((string)$str);
	}

	protected static function _totitle($str) {
		return mb_convert_case((string)$str, MB_CASE_TITLE);
	}

	protected static function _ucfirst($str) {
		$str = (string)$str;
		$a = self::_substr($str, 0, 1);
		$b = self::_substr($str, 1, self::_len($str) - 1);
		return self::_toUpper($a) . $b;
	}

	protected static function _substr($str, $offset, $length = null) {
		if ($length === null) return mb_substr((string)$str, $offset);
		return mb_substr((string)$str, $offset, $length);
	}

	protected static function _replace($str, $from, $to) {
		return str_replace($from, $to, (string)$str);
	}

	protected static function _remove($str, $what) {
		return str_replace($what, '', (string)$str);
	}

	protected static function _cut($str, $n) {
		if (($n = (int)$n) <= 0) return '';
		return preg_replace('/^(.{' . $n . '}).*$/uSs', '$1', $str);
	}

	protected static function _prepend($str, $new) {
		return (string)$new . (string)$str;
	}

	protected static function _append($str, $new) {
		return (string)$str . (string)$new;
	}

	protected static function _hash($str, $key = FG_KEY_CRYPT) {
		$str = (string)$str;
		$l = strlen($str);
		$a = $l % 7;
		$b = $l % 11;
		$c = $l % 13;
		$d = FG_KEY_HASH;
		$e = self::_sha512($key);
		$str = str_repeat("\x13", $l % 17 + 7) . "\0" . $a . "\0" . $d . "\0" . $b . "\0" . $str . "\0" . $c . "\0" . $e;
		$str = self::_whirlpool($str);
		$str = self::_base62($str);
		return $str;
	}
	protected static function _fhash($str, $key = FG_KEY_CRYPT) {
		$str = (string)$str;
		$len6 = strlen($str) % 6;
		$key = md5($key);
		$str = md5($key . $str . $key . $len6);
		$str = self::_toUpper(base_convert($str, 16, 36));
		return $str;
	}
	protected static function _md5($str) {
		return hash('md5', (string)$str);
	}
	protected static function _sha1($str) {
		return hash('sha1', (string)$str);
	}
	protected static function _sha256($str) {
		return hash('sha256', (string)$str);
	}
	protected static function _sha512($str) {
		return hash('sha512', (string)$str);
	}
	protected static function _crc32($str) {
		return hash('crc32', (string)$str);
	}
	protected static function _whirlpool($str) {
		return hash('whirlpool', (string)$str);
	}

	protected static function _filesize($size) {
		if ($size < 1024) return $size . ' B';
		if ($size < 1048576) return round($size / 1024, 2) . ' KiB';
		if ($size < 1073741824) return round($size / 1048576, 2) . ' MiB';
		return round($size / 1073741824, 2) . ' GiB';
	}

	protected static function _plus($n) {
		return $n > 0 ? '+' . $n : $n;
	}

	protected static function _numbers($str) {
		return preg_replace('#[^0-9]+#uis', '', (string)$str);
	}

	protected static function _letnum($str) {
		return preg_replace('#[^A-Za-zА-Яа-я0-9]+#uis', '', (string)$str);
	}

	protected static function _translit($str, $flag = false) {
		$str = (string)$str;
		$str = preg_replace("/[^A-Za-zА-ЯЁа-яё0-9_.\\- ]+/uis", "", $str);
		$repl = array(
			'а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'yo','ж'=>'zh','з'=>'z','и'=>'i','й'=>'y',
			'к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f',
			'х'=>'h','ц'=>'ts','ч'=>'ch','ш'=>'sh','щ'=>'shch','ъ'=>'','ы'=>'i','ь'=>'','э'=>'e','ю'=>'yu','я'=>'ya',
			'А'=>'A','Б'=>'B','В'=>'V','Г'=>'G','Д'=>'D','Е'=>'E','Ё'=>'Yo','Ж'=>'Zh','З'=>'Z','И'=>'I','Й'=>'Y',
			'К'=>'K','Л'=>'L','М'=>'M','Н'=>'N','О'=>'O','П'=>'P','Р'=>'R','С'=>'S','Т'=>'T','У'=>'U','Ф'=>'F',
			'Х'=>'H','Ц'=>'Ts','Ч'=>'Ch','Ш'=>'Sh','Щ'=>'Shch','Ъ'=>'','Ы'=>'I','Ь'=>'','Э'=>'E','Ю'=>'Yu','Я'=>'Ya',
		);
		foreach ($repl as $rus => $eng) {
			$str = str_replace($rus, $eng, $str);
		}
		if (!$flag) {
			$str = str_replace(' ', '_', $str);
		}
		return $str;
	}

	protected static function _ipurl($str, $url = false) {
		$str = (string)$str;
		if (!$url) $url = 'https://www.nic.ru/whois/?query=';
		$url = str_replace('$', '', $url);
		return preg_replace(
			"/([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})/uis",
			"<a href=\"" . $url . "$1\" target=\"_blank\">$1</a>",
			$str
		);
	}

	protected static function _sqltime($str) {
		$str = (string)$str;
		$dt = explode(" ", $str);
		$d = explode("-", @$dt[0]);
		$t = explode(":", @$dt[1]);
		if (count($t) != 3) $t = array(12, 0, 0);
		if (count($d) != 3) return 0;
		if ($d[0] < 1900) return 0;
		return (int)mktime($t[0], $t[1], $t[2], $d[1], $d[2], $d[0]);
	}

	protected static function _texter($n, $f = false) {
		$a = array();
		$a[0] = array('', 'один', 'два', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять', 'десять', 'одиннадцать', 'двенадцать', 'тринадцать', 'четырнадцать', 'пятнадцать', 'шестнадцать', 'семьнадцать', 'восемьнадцать', 'девятнадцать');
		$a[1] = array('', 'десять', 'двадцать', 'тридцать', 'сорок', 'пятьдесят', 'шестьдесят', 'семьдесят', 'восемьдесят', 'девяносто');
		$a[2] = array('', 'сто', 'двести', 'триста', 'четыреста', 'пятьсот', 'шестьсот', 'семьсот', 'восемьсот', 'девятьсот');
		$a[3] = array('', 'одна тысяча', 'две тысячи', 'три тысячи', 'четыре тысячи', 'пять тысяч', 'шесть тысяч', 'семь тысяч', 'восемь тысяч', 'девять тысяч', 'десять тысяч', 'одиннадцать тысяч', 'двенадцать тысяч', 'тринадцать тысяч', 'четырнадцать тысяч', 'пятнадцать тысяч', 'шестнадцать тысяч', 'семьнадцать тысяч', 'восемьнадцать тысяч', 'девятнадцать тысяч');
		$a[4] = array('', 'одна', 'две', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять', 'десять', 'одиннадцать', 'двенадцать', 'тринадцать', 'четырнадцать', 'пятнадцать', 'шестнадцать', 'семьнадцать', 'восемьнадцать', 'девятнадцать');

		if ($n < 0) return 'меньше нуля';
		if ($n == 0) return 'ноль';
		if ($n >= 10000000) return 'очень много';
		$s = '';
		$flag1 = false;
		if ($n >= 1000000 && $n <= 9999999) {
			$m = floor($n / 1000000);
			$n = $n % 1000000;
			$s .= ' ' . $a[0][$m] . ' ' . self::_plural($m, 'миллион', 'миллиона', 'миллионов');
		}
		if ($n >= 100000 && $n <= 999999) {
			$m = floor($n / 100000);
			$n = $n % 100000;
			$s .= ' ' . $a[2][$m];
			$flag1 = true;
		}
		if ($n >= 20000 && $n <= 99999) {
			$m = floor($n / 1000);
			$n = $n % 1000;
			$p1 = floor($m / 10);
			$p2 = $m % 10;
			$s .= ' ' . $a[1][$p1] . ' ' . $a[4][$p2] . ' ' . self::_plural($m, 'тысяча', 'тысячи', 'тысяч');
		} elseif ($n >= 1000 && $n <= 19999) {
			$m = floor($n / 1000);
			$n = $n % 1000;
			$s .= ' ' . $a[3][$m];
		} elseif ($flag1) {
			$s .= ' тысяч';
		}
		if ($n >= 100 && $n <= 999) {
			$m = floor($n / 100);
			$n = $n % 100;
			$s .= ' ' . $a[2][$m];
		}
		if ($n >= 20 && $n <= 99) {
			$m = floor($n / 10);
			$n = $n % 10;
			$s .= ' ' . $a[1][$m] . ' ' . $a[0][$n];
		} elseif ($n >= 1 && $n <= 19) {
			$s .= ' ' . $a[0][$n];
		}
		$s = trim(preg_replace('# +#uis', ' ', $s));
		switch ($f) {
			case 'up':
				$s = self::_toUpper($s);
			break;
			case 'uc':
				$s = self::_ucFirst($s);
			break;
		}
		return $s;
	}

	public static function random($l, $t = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ') {
		$str = '';
		$m = strlen($t) - 1;
		for ($i = 0; $i < $l; $i++) {
			$n = mt_rand(0, $m);
			$str .= $t{$n};
		}
		if (isset($this) && get_class($this) == __CLASS__) {
			return new self($str);
		}
		return $str;
	}

	public static function str2arr($s) {
		$a = array();
		$l = self::_len($s);
		$i = 0;
		while ($i < $l) {
			$a[] = self::_substr($s, $i, 1);
			$i++;
		}
		return $a;
	}

	public static function plural($n, $v1, $v2, $v3) {
		return $n % 100 < 10 || $n % 100 > 20 ? ($n % 10 == 1 ? $v1 : ($n % 10 >= 2 && $n % 10 <= 4 ? $v2 : $v3)) : $v3;
	}

	public static function isnum($str) {
		return (string)(int)$str === (string)$str;
	}

}

function htmlsec() {return call_user_func_array(array('Str', 'htmlsec'), func_get_args());}
