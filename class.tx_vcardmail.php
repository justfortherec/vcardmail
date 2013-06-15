<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012-2013 Franz Geiger <mail_at_fx-g.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * A postprocessor extending the mail postprocessor by attaching entered
 * contact details as a vCard.
 *
 * @author Franz Geiger <mail_at_fx-g.de>
 */
class tx_vcardmail extends \TYPO3\CMS\Form\PostProcess\MailPostProcessor implements \TYPO3\CMS\Form\PostProcess\PostProcessorInterface {

	/** 
	 * Version of the vCard. Must be either 2.1 or 3.0. Defaults to 2.1
	 *
	 * @var string
	 */
	protected $version = tx_vcardmail_vcard::VERSION21;
	/**
	 * Filename of the vCard file. Defaults to vcard.vcf
	 *
	 * @var string
	 */
	protected $filename = 'vcard.vcf';
	/**
	 * Mimetype of the vCard file. Defaults to text/x-vcard
	 *
	 * @var string
	 */
	protected $mimetype = 'text/x-vcard';
	/**
	 * Representation of the vCard object.
	 *
	 * @var tx_vcardmail_vcard
	 */
	protected $vcard;

	/**
	 * Output format of birthday date.
	 *
	 * @var string
	 */
	const BDAY_FORMAT = 'Y-m-d';
	/**
	 * Output format of revision date. ISO8601.
	 *
	 * @var string
	 */
	const REV_FORMAT = DateTime::ISO8601;
	/**
	 * Default input format of birthday date.
	 *
	 * @var string
	 */
	const DEFAULT_BDAY_INPUT_FORMAT = 'd-m-Y';
	/**
	 * Default input format of revision date.
	 *
	 * @var string
	 */
	const DEFAULT_REV_INPUT_FORMAT = 'd-m-Y H:i:s';


	/**
	 * Add attachments when uploaded. Overrides the inherited function in
	 * tx_form_System_Postprocessor_Mail by adding a call to
	 * $this->addVcardAttachment().
	 *
	 * @override
	 * @return void
	 */
	protected function addAttachmentsFromForm() {
		parent::addAttachmentsFromForm();

		$values = $this->requestHandler->getByMethod();
		$this->addVcardAttachment($values);
	}

	/**
	 * Attach vcard based on typoscript fields in
	 * $typoScript['postprocessor'].x.properties.
	 *
	 * @param array $submittedValues
	 * @return void
	 */
	protected function addVcardAttachment($submittedValues) {
		$this->vcard = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_vcardmail_vcard');

		// version can be eiter 2.1 (default) or 3.0
		if ($this->typoScript['version'] == tx_vcardmail_vcard::VERSION30) {
			$this->version = tx_vcardmail_vcard::VERSION30;
		}

		if ($this->typoscript['fileName']) {
			$this->filename = $this->typoScript['fileName'];
		}

		if ($this->typoscript['mimeType']) {
			$this->mimetype = $this->typoScript['mimeType'];
		}

		$this->generateVcardFromTyposcript(
			$this->typoScript['properties.'],
			$submittedValues
		);
		$vcardString = tx_vcardmail_vcard_generator::generate($this->vcard, $this->version);

		$this->mailMessage->attach(
			Swift_Attachment::newInstance(
				$vcardString,
				$this->filename,
				$this->mimetype
			)
		);
	}

	/**
	 * Create vcard based on typoscript fields in
	 * $typoScript['postprocessors'].x.properties.
	 *
	 * @param array $typoscript	Array of properties defined in typoScript
	 * @param array $submittedValues
	 */
	protected function generateVcardFromTyposcript($typoscript, $submittedValues) {

		$keys =  \TYPO3\CMS\Core\TypoScript\TemplateService::sortedKeyList($typoscript);

		foreach ($keys as $propertyKey) {
			if (!intval($propertyKey) || strpos($propertyKey, '.') !== FALSE) {
				continue;
			}

			// Properties are defined by typoscript
			// (e.g. postProcessor.1.properties.1 = EMAIL)
			$property = ucfirst(strtolower($typoscript[$propertyKey]));

			if ($property == 'Address' ||
				$property == 'Phonenumber' ||
				$property == 'Email' ||
				$property == 'Url' ||
				$property == 'Instantmessenger'
			) {
				$this->addMultiProperty($property, $typoscript[$propertyKey . '.'], $submittedValues);

			} elseif ($property == 'Birthday' || $property == 'Revision') {
				$this->setDateProperty($property, $typoscript[$propertyKey . '.'], $submittedValues);

			} else {
				// if it's not one of the properties above: just try calling this:
				$this->setSingleProperty($property, $typoscript[$propertyKey . '.'], $submittedValues);
			}
		}

		// If REVISION is not set yet: set to current time
		if ($this->vcard->__get('revision') === NULL) {

			$revisionDate = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('DateTime');
			$this->vcard->setRevision($revisionDate->format(self::REV_FORMAT));
		}
	}

	/**
	 * Extracts types from typoscript.
	 *
	 * @param array $types numbered array of types
	 */
	static protected function getPropertyTypes($typeArray) {

		$types = array();
		// Types are mapped to this entry by typoscript
		// (e.g. postProcessor.1.properties.1.type.1 = WORK)
		$typeKeys = \TYPO3\CMS\Core\TypoScript\TemplateService::sortedKeyList($typeArray);

		foreach ($typeKeys as $typeKey) {
			if (!intval($typeKey) || strpos($typeKey, '.') !== FALSE) {
				continue;
			}

			$types[] = strtoupper($typeArray[$typeKey]);
		}
		return $types;
	}

	/**
	 * Adds another instance of $property to the vcard.
	 *
	 * @param string $property
	 * @param array $typoscript TS-configuration of this property
	 * @param array $submittedValues
	 */
	protected function addMultiProperty($property, $typoscript, $submittedValues) {

		$entryArray[0] = array();

		// (e.g. postProcessor.1.properties.1.zip = zip-fieldname
		//  or   postProcessor.1.properties.2.field = phone-fieldname)

		foreach ($typoscript as $key => $field) {

			if ($key == 'type.') {
				$entryArray[0]['type'] = self::getPropertyTypes($typoscript[$key]);

			} elseif ($key == 'messenger') {
				// addInstantmessenger() takes the type of messenger as an additional parameter
				// (e.g. postProcessor.1.properties.1.messenger = X-JABBER)
				$entryArray[0][$key] = $typoscript[$key];

			} elseif (isset($submittedValues[$field])) {
				if ($key == 'field') {
					$entryArray[0]['value'] = $submittedValues[$field];

				} else {
					$entryArray[0][$key] = $submittedValues[$field];
				}

			} elseif ($key == 'field' && $property != 'Address') {
				// all this properties except address require 'value'
				return;
			}
		}
		$this->vcard->{'add' . $property}($entryArray);
	}

	/**
	 * Sets $property in vcard as a date.
	 *
	 * @param string $property
	 * @param array $typoscript TS-configuration of this property
	 * @param array $submittedValues
	 */
	protected function setDateProperty($property, $typoscript, $submittedValues) {

		if (!isset($submittedValues[$typoscript['field']])) {
			return;
		}

		$value = $submittedValues[$typoscript['field']];
		$outputFormat;

		if ($property == 'Birthday') {
			$outputFormat = self::BDAY_FORMAT;
		} else {
			$outputFormat = self::REV_FORMAT;
		}

		$format = $typoscript['format'];
		if ($format !== NULL) {
			if ($property == 'Birthday') {
				$format = self::DEFAULT_BDAY_INPUT_FORMAT;
			} else {
				$format = self::DEFAULT_REV_INPUT_FORMAT;
			}
		}

		$date = DateTime::createFromFormat($format, $value);

		$this->vcard->{'set' . $property}($date->format($outputFormat));
	}

	/**
	 * Sets $property in the vcard.
	 *
	 * @param string $property
	 * @param array $typoscript TS-configuration of this property
	 * @param array $submittedValues
	 */
	protected function setSingleProperty($property, $typoscript, $submittedValues) {

		if (!isset($submittedValues[$typoscript['field']])) {
			return;
		}

		try {
			$this->vcard->{'set' . $property}($submittedValues[$typoscript['field']]);
		} catch (Exception $e) {}
	}
}
?>
