<?php

namespace modules\gmail\src;

use Yii;
use yii\mail\BaseMessage;

/**
 * Message for Gmail API
 */
class Message extends BaseMessage{

	private $_charset;
	private $_from_name;
	private $_from;
	private $_to_name;
	private $_to;
	private $_reply_to;
	private $_cc;
	private $_bcc;
	private $_subject;
	private $_body_html;
	private $_body_text;
	private $_files = [];

	/**
	 * @return string
	 */
	public function getCharset(){
		return $this->_charset;
	}

	/**
	 * @param $charset
	 *
	 * @return $this|\modules\gmail\src\Message
	 */
	public function setCharset($charset){
		$this->_charset = $charset;

		return $this;
	}

	/**
	 * @return array|string
	 */
	public function getFrom(){
		return $this->_from;
	}

	/**
	 * @param $from
	 *
	 * @return $this|\modules\gmail\src\Message
	 */
	public function setFrom($from){
		$this->_from = $from;
		if (is_array($from)){
			$this->_from      = key($from);
			$this->_from_name = $from[$this->_from];
		}

		return $this;
	}

	/**
	 * @return array|string
	 */
	public function getTo(){
		return $this->_to;
	}

	/**
	 * @param $to
	 *
	 * @return $this|\modules\gmail\src\Message
	 */
	public function setTo($to){
		$this->_to = $to;
		if (is_array($to)){
			$this->_to      = key($to);
			$this->_to_name = $to[$this->_to];
		}

		return $this;
	}

	/**
	 * @return array|string
	 */
	public function getReplyTo(){
		return $this->_reply_to;
	}

	/**
	 * @param $replyTo
	 *
	 * @return $this|\modules\gmail\src\Message
	 */
	public function setReplyTo($replyTo){
		$this->_reply_to = $replyTo;

		return $this;
	}

	/**
	 * @return array|string
	 */
	public function getCc(){
		return $this->_cc;
	}

	/**
	 * @param $cc
	 *
	 * @return $this|\modules\gmail\src\Message
	 */
	public function setCc($cc){
		$this->_cc = $cc;

		return $this;
	}

	/**
	 * @return array|string
	 */
	public function getBcc(){
		return $this->_bcc;
	}

	/**
	 * @param $bcc
	 *
	 * @return $this|\modules\gmail\src\Message
	 */
	public function setBcc($bcc){
		$this->_bcc = $bcc;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getSubject(){
		return $this->_subject;
	}

	/**
	 * @param $subject
	 *
	 * @return $this|\modules\gmail\src\Message
	 */
	public function setSubject($subject){
		$this->_subject = $subject;

		return $this;
	}

	/**
	 * @param $text
	 *
	 * @return $this|\modules\gmail\src\Message
	 */
	public function setTextBody($text){
		$this->_body_text = $text;

		return $this;
	}

	/**
	 * @param $html
	 *
	 * @return $this|\modules\gmail\src\Message
	 */
	public function setHtmlBody($html){
		$this->_body_html = $html;

		return $this;
	}

	/**
	 * @param $fileName
	 * @param array $options
	 *
	 * @return $this|\modules\gmail\src\Message
	 */
	public function attach($fileName, array $options = []){
		return $this;
	}

	/**
	 * @param $content
	 * @param array $options
	 *
	 * @return $this|\modules\gmail\src\Message
	 */
	public function attachContent($content, array $options = []){
		return $this;
	}

	/**
	 * @param $fileName
	 * @param array $options
	 *
	 * @return $this|string
	 */
	public function embed($fileName, array $options = []){
		$this->_files[] = $fileName;

		return $this;
	}

	/**
	 * @param $content
	 * @param array $options
	 *
	 * @return $this|string
	 */
	public function embedContent($content, array $options = []){
		return $this;
	}

	/**
	 * @return string
	 */
	public function toString(){
		$from_name = Yii::$app->name;
		if (!empty($this->_from_name)){
			$from_name = $this->_from_name;
		}

		$to_name = !empty($this->_to_name) ? $this->_to_name : '';

		$message = "From: {$from_name} <{$this->_from}>\r\n";
		$message .= "To: {$to_name} <{$this->_to}>\r\n";
		if (!empty($this->_cc)){
			$cc = $this->_cc;
			if (is_array($this->_cc)){
				$cc = implode(", ", $this->_cc);
			}

			$message .= "Cc: {$cc}\r\n";
		}

		if (!empty($this->_bcc)){
			$bcc = $this->_bcc;
			if (is_array($this->_bcc)){
				$bcc = implode(", ", $this->_bcc);
			}

			$message .= "Bcc: {$bcc}\r\n";
		}

		$message .= 'Subject: =?utf-8?B?' . base64_encode($this->_subject) . "?=\r\n";
		$message .= "MIME-Version: 1.0\r\n";

		if (!empty($this->_files)){
			foreach ($this->_files as $file){
				$boundary  = uniqid(rand(), TRUE);
				$finfo     = finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
				$mimeType  = finfo_file($finfo, $file);
				$file_name = basename($file);
				$file_data = base64_encode(file_get_contents($file));

				$message .= 'Content-type: Multipart/Mixed; boundary="' . $boundary . '"' . "\r\n";
				$message .= "\r\n--{$boundary}\r\n";
				$message .= 'Content-Type: ' . $mimeType . '; name="' . $file_name . '";' . "\r\n";
				$message .= 'Content-ID: <' . $this->_from . '>' . "\r\n";
				$message .= 'Content-Description: ' . $file_name . ';' . "\r\n";
				$message .= 'Content-Disposition: attachment; filename="' . $file_name . '"; size=' . filesize($file) . ';' . "\r\n";
				$message .= 'Content-Transfer-Encoding: base64' . "\r\n\r\n";
				$message .= chunk_split($file_data, 76, "\n") . "\r\n";
				$message .= "--{$boundary}\r\n";
			}
		}

		$message .= "Content-Type: text/html; charset=utf-8\r\n";
		$message .= 'Content-Transfer-Encoding: base64' . "\r\n\r\n";

		if (!empty($this->_body_html)){
			$message .= "{$this->_body_html}\r\n";
		}elseif (!empty($this->_body_text)){
			$message .= "{$this->_body_text}\r\n";
		}

		return strtr(base64_encode($message), ['+' => '-', '/' => '_']);
	}
}