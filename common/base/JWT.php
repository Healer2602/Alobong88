<?php

namespace common\base;

use ArrayAccess;
use DomainException;
use Exception;
use Firebase\JWT\JWT as BaseJWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use UnexpectedValueException;
use Yii;
use yii\helpers\Json;
use yii\helpers\StringHelper;

/**
 * JWT Helper
 */
class JWT extends BaseJWT{

	public static $leeway = 5 * 60;

	/**
	 * @param array $payload
	 * @param $key
	 * @param string $alg
	 * @param null $key_id
	 * @param null $head
	 *
	 * @return array|string
	 */
	public static function encode(
		$payload,
		$key,
		$alg = 'HS256',
		$key_id = NULL,
		$head = NULL){
		try{
			if (empty($payload['iat'])){
				$payload['iat'] = time();
			}

			return parent::encode($payload, $key);
		}catch (Exception $exception){
			return [];
		}
	}

	/**
	 * @param string $signature
	 * @param $key
	 * @param array $allowed_algs deprecated
	 *
	 * @return object
	 */
	public static function decode($signature, $key, array $allowed_algs = []){
		$alg = self::findAlgorithm($signature);
		if (empty($key)){
			return self::decodeWithoutKey($signature, $alg);
		}

		return parent::decode($signature, new Key($key, $alg));
	}

	/**
	 * @return mixed|string
	 */
	private static function algorithm(){
		return Yii::$app->params['jwt']['alg'] ?? 'HS256';
	}

	/**
	 * @param $token
	 *
	 * @return mixed|string
	 */
	private static function findAlgorithm($token){
		$alg = self::algorithm();
		try{
			[$header, ,] = StringHelper::explode($token, '.');
			if (!empty($header)){
				$header = Json::decode(base64_decode($header), FALSE);

				return $header->alg ?? $alg;
			}
		}catch (Exception $exception){
			return $alg;
		}

		return $alg;
	}

	/**
	 * @param $jwt
	 * @param $algorithm
	 *
	 * @return object
	 */
	private static function decodeWithoutKey($jwt, $algorithm){
		$keyMaterial = '';
		$timestamp   = empty(static::$timestamp) ? time() : static::$timestamp;
		$tks         = explode('.', $jwt);

		if (count($tks) != 3){
			throw new UnexpectedValueException('Wrong number of segments');
		}
		[$headb64, $bodyb64, $cryptob64] = $tks;
		if (NULL === ($header = static::jsonDecode(static::urlsafeB64Decode($headb64)))){
			throw new UnexpectedValueException('Invalid header encoding');
		}
		if (NULL === $payload = static::jsonDecode(static::urlsafeB64Decode($bodyb64))){
			throw new UnexpectedValueException('Invalid claims encoding');
		}
		if (FALSE === ($sig = static::urlsafeB64Decode($cryptob64))){
			throw new UnexpectedValueException('Invalid signature encoding');
		}
		if (empty($header->alg)){
			throw new UnexpectedValueException('Empty algorithm');
		}
		if (empty(static::$supported_algs[$header->alg])){
			throw new UnexpectedValueException('Algorithm not supported');
		}

		if (!self::constantTimeEquals($algorithm, $header->alg)){
			throw new UnexpectedValueException('Incorrect key for this algorithm');
		}

		if ($header->alg === 'ES256' || $header->alg === 'ES384'){
			$sig = self::signatureToDER($sig);
		}

		if (!static::verifyWithoutKey("$headb64.$bodyb64", $sig, $keyMaterial, $header)){
			throw new SignatureInvalidException('Signature verification failed');
		}

		return $payload;
	}

	/**
	 * Verify a signature with the message, key and method. Not all methods
	 * are symmetric, so we must have a separate verify and sign method.
	 *
	 * @param string $msg The original message (header and body)
	 * @param string $signature The original signature
	 * @param string|resource $key For HS*, a string key works. for RS*, must be a resource of an
	 *     openssl public key
	 * @param array $header The header of signature
	 *
	 * @return bool
	 *
	 * @throws DomainException Invalid Algorithm, bad key, or OpenSSL failure
	 */
	private static function verifyWithoutKey($msg, $signature, $key, $header){
		$alg = $header->alg ?? NULL;
		if (empty($key)){
			$key = '';
		}

		if (empty(static::$supported_algs[$alg])){
			throw new DomainException('Algorithm not supported');
		}

		[$function, $algorithm] = static::$supported_algs[$alg];

		switch ($function){
			case 'openssl':
				$success = openssl_verify($msg, $signature, $key, $algorithm);
				if ($success === 1){
					return TRUE;
				}elseif ($success === 0){
					return FALSE;
				}
				// returns 1 on success, 0 on failure, -1 on error.
				throw new DomainException(
					'OpenSSL error: ' . openssl_error_string()
				);
			case 'sodium_crypto':
				if (!function_exists('sodium_crypto_sign_verify_detached')){
					throw new DomainException('libsodium is not available');
				}
				try{
					// The last non-empty line is used as the key.
					$lines = array_filter(explode("\n", $key));
					$key   = base64_decode(end($lines));

					return sodium_crypto_sign_verify_detached($signature, $msg, $key);
				}catch (Exception $e){
					throw new DomainException($e->getMessage(), 0, $e);
				}
			case 'hash_hmac':
			default:
				$hash = hash_hmac($algorithm, $msg, $key, TRUE);

				return self::constantTimeEquals($signature, $hash);
		}
	}

	/**
	 * Determine if an algorithm has been provided for each Key
	 *
	 * @param Key|array<Key>|mixed $keyOrKeyArray
	 * @param string|null $kid
	 *
	 * @return array containing the keyMaterial and algorithm
	 * @throws UnexpectedValueException
	 *
	 */
	private static function getKeyMaterialAndAlgorithm($keyOrKeyArray, $kid = NULL){
		if (
			is_string($keyOrKeyArray)
			|| is_resource($keyOrKeyArray)
			|| $keyOrKeyArray instanceof OpenSSLAsymmetricKey
		){
			return [$keyOrKeyArray, NULL];
		}

		if ($keyOrKeyArray instanceof Key){
			return [$keyOrKeyArray->getKeyMaterial(), $keyOrKeyArray->getAlgorithm()];
		}

		if (is_array($keyOrKeyArray) || $keyOrKeyArray instanceof ArrayAccess){
			if (!isset($kid)){
				throw new UnexpectedValueException('"kid" empty, unable to lookup correct key');
			}
			if (!isset($keyOrKeyArray[$kid])){
				throw new UnexpectedValueException('"kid" invalid, unable to lookup correct key');
			}

			$key = $keyOrKeyArray[$kid];

			if ($key instanceof Key){
				return [$key->getKeyMaterial(), $key->getAlgorithm()];
			}

			return [$key, NULL];
		}

		throw new UnexpectedValueException(
			'$keyOrKeyArray must be a string|resource key, an array of string|resource keys, '
			. 'an instance of Firebase\JWT\Key key or an array of Firebase\JWT\Key keys'
		);
	}

	/**
	 * Convert an ECDSA signature to an ASN.1 DER sequence
	 *
	 * @param string $sig The ECDSA signature to convert
	 *
	 * @return  string The encoded DER object
	 */
	private static function signatureToDER($sig){
		// Separate the signature into r-value and s-value
		[$r, $s] = str_split($sig, (int) (strlen($sig) / 2));

		// Trim leading zeros
		$r = ltrim($r, "\x00");
		$s = ltrim($s, "\x00");

		// Convert r-value and s-value from unsigned big-endian integers to
		// signed two's complement
		if (ord($r[0]) > 0x7f){
			$r = "\x00" . $r;
		}
		if (ord($s[0]) > 0x7f){
			$s = "\x00" . $s;
		}

		return self::encodeDER(
			self::ASN1_SEQUENCE,
			self::encodeDER(self::ASN1_INTEGER, $r) .
			self::encodeDER(self::ASN1_INTEGER, $s)
		);
	}

	/**
	 * Encodes a value into a DER object.
	 *
	 * @param int $type DER tag
	 * @param string $value the value to encode
	 *
	 * @return  string  the encoded object
	 */
	private static function encodeDER($type, $value){
		$tag_header = 0;
		if ($type === self::ASN1_SEQUENCE){
			$tag_header |= 0x20;
		}

		// Type
		$der = chr($tag_header | $type);

		// Length
		$der .= chr(strlen($value));

		return $der . $value;
	}
}