<?php

/***************************************************************
 * Copyright 2010 Thomas Schaaf <Thomaschaaf@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * Changes by: Franz Geiger <mail_at_fx-g.de>
 * Original code is available at:
 * http://code.google.com/p/zendvcard/
 ***************************************************************/
/**
 * A represantation of a Vcard.Version
 *
 * @author Thomas Schaaf <Thomaschaaf@gmail.com>
 */
class tx_vcardmail_vcard
{
	const VERSION21 = '2.1';
	const VERSION30 = '3.0';
	const LINEBREAK = "\r\n";

	protected $_uid = null;
	protected $_title = null;
	protected $_fullname = null;
	protected $_lastname = null;
	protected $_firstname = null;
	protected $_nickname = null;
	protected $_additionalnames = null;
	protected $_nameprefix = null;
	protected $_namesuffix = null;
	protected $_birthday = null;
	protected $_organization = null;
	protected $_department = null;
	protected $_subdepartment = null;
	protected $_role = null;
	protected $_revision = null;
	protected $_geolocation = null;
	protected $_mailer = null;
	protected $_timezone = null;
	/**
	 * @var array array(postofficeaddress, extendedaddress, street, city, state, zip, country, type=array())
	 */
	protected $_address = array();
	const ADDRESSDOMESTIC = 'DOM';
	const ADDRESSINTERNATIONAL = 'INTL';
	const ADDRESSSPOSTAL = 'POSTAL';
	const ADDRESSPARCEL = 'PARCEL';
	const ADDRESSHOME = 'HOME';
	const ADDRESSWORK = 'WORK';
	protected $addressConstantArray = array(self::ADDRESSDOMESTIC,
			self::ADDRESSHOME,
			self::ADDRESSINTERNATIONAL,
			self::ADDRESSPARCEL,
			self::ADDRESSSPOSTAL,
			self::ADDRESSWORK);
	/**
	 * @var array array(value, type=array())
	 */
	protected $_telephone = array();
	const TELEPHONEPREF = 'PREF';
	const TELEPHONEWORK = 'WORK';
	const TELEPHONEHOME = 'HOME';
	const TELEPHONEOTHER = 'OTHER';
	const TELEPHONEVOICE = 'VOICE';
	const TELEPHONEFAX = 'FAX';
	const TELEPHONEMSG = 'MSG';
	const TELEPHONECELL = 'CELL';
	const TELEPHONECAR = 'CAR';
	protected $telephoneConstantArray = array(self::TELEPHONECAR,
			self::TELEPHONECELL,
			self::TELEPHONEFAX,
			self::TELEPHONEHOME,
			self::TELEPHONEMSG,
			self::TELEPHONEOTHER,
			self::TELEPHONEPREF,
			self::TELEPHONEVOICE,
			self::TELEPHONEWORK);
	/**
	 * @var array array(value, type=array())
	 */
	protected $_email = array();
	const EMAILINTERNET = 'INTERNET';
	const EMAILWORK= 'WORK';
	const EMAILHOME = 'HOME';
	const EMAILOTHER = 'OTHER';
	const EMAILPREF = 'PREF';
	protected $emailConstantArray = array(self::EMAILHOME,
			self::EMAILINTERNET,
			self::EMAILOTHER,
			self::EMAILPREF,
			self::EMAILWORK);
	/**
	 * @var array array(value, type(array())
	 */
	protected $_url = array();
	const URLWORK= 'WORK';
	const URLHOME = 'HOME';
	const URLOTHER = 'OTHER';
	const URLPREF = 'PREF';
	protected $urlConstantArray = array(self::URLHOME,
			self::URLOTHER,
			self::URLPREF,
			self::URLWORK);
	/**
	 * @var array array(value, type=array(), $messenger=null)
	 */
	protected $_im = array();
	const IMHOME = 'HOME';
	const IMWORK = 'WORK';
	const IMPREF = 'PREF';
	const IMOTHER = 'OTHER';
	const IMAIM = 'X-AIM';
	const IMICQ = 'X-ICQ';
	const IMJABBER = 'X-JABBER';
	const IMMSN = 'X-MSN';
	const IMYAHOO = 'X-YAHOO';
	const IMSKYPE = 'X-SKYPE';
	const IMSKYPEALTERNATIVE = 'X-SKYPE-USERNAME';
	const IMGADUGADU = 'X-GADUGADU';
	protected $imMessengerConstantArray = array(
			self::IMAIM,
			self::IMICQ,
			self::IMJABBER,
			self::IMMSN,
			self::IMYAHOO,
			self::IMSKYPE,
			self::IMSKYPEALTERNATIVE,
			self::IMGADUGADU
	);
	protected $imConstantArray = array(self::IMHOME,
			self::IMOTHER,
			self::IMPREF,
			self::IMWORK);
	protected $_gender = null;
	const GENDERMALE = 'Male';
	const GENDERFEMALE = 'Female';

	public function __call($function, $args)
	{
		$function = strtolower($function);
		$property = substr($function, 3);

		if (substr($function, 0, 3) == 'set' &&
						property_exists($this, "_" . $property) &&
						!is_array($this->{"_" . $property}))
		{
			if (is_string($args[0]) || is_int($args[0]))
			{
				$this->$property = $args[0];

				return $this;
			}
		}
		else
		{
			throw new Exception("Unknown property $property");
		}
	}

	public function __get($name)
	{
		if (property_exists($this, "_" . $name))
		{
			return $this->{"_" . $name};
		}
		else
		{
			throw new Exception("Unknown property $name");
		}
	}

	public function __set($name, $value)
	{
		if (property_exists($this, "_" . $name) && !is_array($this->{"_" . $name}))
		{
			$this->{"_" . $name} = $value;
		}
		else
		{
			throw new Exception("Unknown property $name");
		}
	}

	public function addPhonenumber($phonenumberArray, $type = null)
	{
		if (!is_array($phonenumberArray))
		{
			$phonenumberCacheArray = array(array("value" => $phonenumberArray));

			if (is_array($type))
			{
				$phonenumberCacheArray[0]['type'] = $type;
			}
			else
			{
				$phonenumberCacheArray[0]['type'][] = $type;
			}

			$phonenumberArray = $phonenumberCacheArray;
		}

		foreach ($phonenumberArray as $value)
		{
			$typeArray = array();
			foreach ($value['type'] as $telephoneType)
			{
				if (in_array($telephoneType, $this->telephoneConstantArray))
				{
					$typeArray[] = $telephoneType;
				}
			}
			$this->_telephone[] = array('value' => $value['value'], 'type' => $typeArray);
		}
		return $this;
	}

	public function addEmail($emailArray, $type = null)
	{
		if (!is_array($emailArray) && is_string($emailArray))
		{
			$emailCacheArray = array();
			$emailCacheArray[0]['value'] = $emailArray;
			if (is_array($type))
			{
				$emailCacheArray[0]['type'] = $type;
			}
			else
			{
				$emailCacheArray[0]['type'][] = $type;
			}
			$emailArray = $emailCacheArray;
		}
		foreach ($emailArray as $value)
		{
			$typeArray = array();
			foreach ($value['type'] as $emailType)
			{
				if (in_array($emailType, $this->emailConstantArray))
				{
					$typeArray[] = $emailType;
				}
			}
			$this->_email[] = array('value' => $value['value'], 'type' => $typeArray);
		}
		return $this;
	}

	public function addUrl($urlArray, $type = null)
	{
		if (!is_array($urlArray) && is_string($urlArray))
		{
			$urlCacheArray = array();
			$urlCacheArray[0]['value'] = $urlArray;
			if (is_array($type))
			{
				$urlCacheArray[0]['type'] = $type;
			}
			else
			{
				$urlCacheArray[0]['type'][] = $type;
			}
			$urlArray = $urlCacheArray;
		}
		foreach ($urlArray as $value)
		{
			$typeArray = array();
			foreach ($value['type'] as $urlType)
			{
				if (in_array($urlType, $this->urlConstantArray))
				{
					$typeArray[] = $urlType;
				}
			}
			$this->_url[] = array('value' => $value['value'], 'type' => $typeArray);
		}
		return $this;
	}

	public function addAddress($addressArray)
	{
		if (isset($addressArray['type']) || !isset($addressArray[0]))
		{
			$addressCacheArray = array();
			$addressCacheArray[] = $addressArray;
			$addressArray = $addressCacheArray;
		}
		foreach ($addressArray as $value)
		{
			$typeCacheArray = array();
			if (!isset($value['type']))
			{
				$value['type'][] = null;
			}
			elseif (!is_array($value['type']))
			{
				$typeCacheArray[] = $value['type'];

				$value['type'] = $typeCacheArray;
			}

			$typeArray = array();
			foreach ($value['type'] as $addressType)
			{
				if (in_array($addressType, $this->addressConstantArray))
				{
					$typeArray[] = $addressType;
				}
			}


			$this->_address[] = array(
					'postofficeaddress' => (isset($value['postofficeaddress']) ? $value['postofficeaddress'] : ''),
					'extendedaddress' => (isset($value['extendedaddress']) ? $value['extendedaddress'] : ''),
					'street' => (isset($value['street']) ? $value['street'] : ''),
					'city' => (isset($value['city']) ? $value['city'] : ''),
					'state' => (isset($value['state']) ? $value['state'] : ''),
					'zip' => (isset($value['zip']) ? $value['zip'] : ''),
					'country' => (isset($value['country']) ? $value['country'] : ''),
					'type' => $typeArray);
		}
		return $this;
	}

	public function addInstantmessenger($imArray, $messenger = null, $type = null)
	{
		if (!is_array($imArray) && is_string($imArray) && isset($messenger))
		{
			$imCacheArray = array();
			$imCacheArray[0]['value'] = $imArray;
			$imCacheArray[0]['messenger'] = $messenger;
			if (is_array($type))
			{
				$imCacheArray[0]['type'] = $type;
			}
			else
			{
				$imCacheArray[0]['type'][] = $type;
			}
			$imArray = $imCacheArray;
		}

		foreach ($imArray as $value)
		{
			$typeArray = array();
			foreach ($value['type'] as $imArray)
			{
				if (in_array($imArray, $this->imConstantArray))
				{
					$typeArray[] = $imArray;
				}
			}
			if (in_array($value['messenger'], $this->imMessengerConstantArray))
			{
				$this->_im[] = array('value' => $value['value'], 'type' => $typeArray, 'messenger' => $value['messenger']);
			}
		}
		return $this;
	}
}
