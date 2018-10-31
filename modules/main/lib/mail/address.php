<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2018 Bitrix
 */
namespace Bitrix\Main\Mail;

/**
 * Class Address
 * @package Bitrix\Main\Mail
 */
class Address
{
	/** @var string|null $name Name. */
	protected $name = null;

	/** @var string|null $email Email. */
	protected $email = null;
	private $checkingPunycode;

	/**
	 * Return true if is valid.
	 *
	 * @param string $address Address.
	 * @return bool
	 */
	public static function isValid($address)
	{
		return (new static($address))->validate();
	}

	/**
	 * Address constructor.
	 *
	 * @param string|null $address Address.
	 */
	public function __construct($address = null, $checkingPunycode = false)
	{
		$this->setCheckingPunycode($checkingPunycode);
		if ($address)
		{
			$this->set($address);
		}
	}

	public function setCheckingPunycode($checkingPunycode = true)
	{
		$this->checkingPunycode = $checkingPunycode;
	}

	/**
	 * Get encoded address.
	 *
	 * @return null|string
	 */
	public function getEncoded()
	{
		if (!$this->email)
		{
			return null;
		}

		if ($this->name)
		{
			$address = sprintf(
				'%s <%s>',
				sprintf('=?%s?B?%s?=', SITE_CHARSET, base64_encode($this->name)),
				$this->email
			);
		}
		else
		{
			$address = "<{$this->email}>";
		}

		return $address;
	}

	/**
	 * Get address.
	 *
	 * @return null|string
	 */
	public function get()
	{
		if (!$this->email)
		{
			return null;
		}

		$address = '';
		if ($this->name)
		{
			$address = $this->name . ' ';
		}

		$address .= "<{$this->email}>";

		return $address;
	}

	/**
	 * Set address.
	 *
	 * @param null|string $address
	 * @return $this
	 */
	public function set($address)
	{
		$this->parse($address);
		return $this;
	}

	/**
	 * Get name.
	 *
	 * @return null|string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Set name.
	 *
	 * @param null|string $name
	 * @return $this
	 */
	public function setName($name)
	{
		$name = trim($name, "\"\x20\t\n\r\0\x0b");
		if ($name != '')
		{
			$name = str_replace(
				array('\\', '"', '<', '>'),
				array('/', '\'', '(', ')'),
				$name
			);
		}

		$this->name = $name;
		return $this;
	}

	/**
	 * Get email.
	 *
	 * @return null|string
	 */
	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * Set email.
	 *
	 * @param null|string $email
	 * @return $this
	 */
	public function setEmail($email)
	{
		$email = strtolower(trim($email));
		if (!$this->checkMail($email))
		{
			$email = null;
		}

		$this->email = $email;
		return $this;
	}



	/**
	 * Validate address.
	 *
	 * @return bool.
	 */
	public function validate()
	{
		return !empty($this->email);
	}

	/**
	 * Parse address.
	 *
	 * @param string $address Address.
	 * @return void
	 */
	protected function parse($address)
	{
		$this->setName('');
		$this->setEmail('');

		if (!$address)
		{
			return;
		}

		if (preg_match('/(.*)<(.+?)>\s*$/is', $address, $matches))
		{
			$this->setName($matches[1]);
			$this->setEmail($matches[2]);
		}
		else
		{
			$this->setEmail($address);
		}
	}

	private function checkMail($email)
	{
		if (!$this->checkingPunycode)
		{
			return check_email($email, true);
		}
		if (count(explode("@", $email)) === 2)
		{
			$domainPart = array_pop(explode("@", $email));
			if ($domainPart)
			{
				$emailAddressName = array_shift(explode("@", $email));
				$encoder = new \CBXPunycode();
				if ($encodedDomain = $encoder->encode($domainPart))
				{
					return check_email($emailAddressName . '@' . $encodedDomain);
				}
			}
		}
		return false;
	}
}
