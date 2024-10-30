<?php
include_once WP_CS_TESTIMONIALS_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'libraries' .DIRECTORY_SEPARATOR . '3rd-part' . DIRECTORY_SEPARATOR . 'json.php';
class CreativeSignalTestimonialUtils
{
	/**
	 *
	 * Encode a array to JSON format
	 * @param string $data The array will be encoded
	 *
	 * @return string
	 */
	function encodeJSON($data)
	{
		if (!function_exists('json_encode'))
		{
		    $json = new Services_JSON;
		    return $json->encode($data);
		}
		else
		{
			return json_encode($data);
		}
	}

	/**
	 *
	 * Decode a JSON string to a ArrayObject
	 * @param string $data The string will be decoded
	 *
	 * @return ArrayObject
	 */

	function decodeJSON($data)
	{
		$data = str_replace('\"', '"', $data);

		if (!function_exists('json_decode'))
		{
			$json = new Services_JSON;
			return $json->decode($data);
		}
		else
		{
			return json_decode($data);
		}
	}

	/**
	 *
	 * Limit the output to specified numbers
	 * @param string $str	Given string
	 * @param int $limit	Specified number to limit allowed number of words in the string
	 * @param string $end_char
	 */
	function wordLimiter($str, $limit = 100, $end_char = '&hellip;')
	{
		$append = '';
		$words = explode(" ", $str);
		if (count($words) > $limit)
		{
			$append = $end_char;
		}
		return implode(" ",array_splice($words, 0, $limit)) . $append;
	}

	/**
	 *
	 * Create a random string. Copied from CodeIgniter
	 * @param string $type	The type of creating random string
	 * @param int $len 		The length of string you want to
	 */
	function randomString($type = 'alpha', $len = 8)
	{
		switch($type) {
			case 'basic'	:
				return mt_rand();
				break;
			case 'alnum'	:
			case 'numeric'	:
			case 'nozero'	:
			case 'alpha'	:
				switch ($type) {
					case 'alpha' :
						$pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
						break;
					case 'alnum' :
						$pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
						break;
					case 'numeric' :
						$pool = '0123456789';
						break;
					case 'nozero' :
						$pool = '123456789';
						break;
				}

				$str = '';
				for ($i=0; $i < $len; $i++) {
					$str .= substr($pool, mt_rand(0, strlen($pool) -1), 1);
				}
				return $str;
				break;
			case 'unique'	:
			case 'md5'	:
				return md5(uniqid(mt_rand()));
				break;
				break;
		}
	}

	/**
	 * This method implements unicode slugs instead of transliteration.
	 *
	 * @param   string  $string  String to process
	 *
	 * @return  string  Processed string
	 *
	 */
	function stringURLSafe($string)
	{
		$str = str_replace('-', ' ', $string);
		$str = trim(strtolower($str));
		$str = preg_replace('/(\s|[^A-Za-z0-9\-])+/', '-', $str);
		$str = trim($str, '-');

		return $str;
	}
}