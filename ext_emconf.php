<?php

########################################################################
# Extension Manager/Repository config file for ext "vcardmail".
#
# Auto generated 06-03-2012 01:34
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'VCard Mailform Postprocessor',
	'description' => 'Extends core form\'s mail postprocessor by attaching a vCard featuring the submitted contact details to mails sent by the mailform.',
	'category' => 'fe',
	'author' => 'Franz Geiger',
	'author_email' => 'mail@fx-g.de',
	'shy' => '',
	'dependencies' => 'form',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'beta',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author_company' => '',
	'version' => '0.1.1',
	'docPath' => 'doc',
	'constraints' => array(
		'depends' => array(
			'form' => '6.0.0-0.0.0',
			'typo3' => '6.0.0-6.0.99'
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:7:{s:9:"ChangeLog";s:4:"b42e";s:6:"README";s:4:"ab58";s:22:"class.tx_vcardmail.php";s:4:"47cc";s:16:"ext_autoload.php";s:4:"90fd";s:12:"ext_icon.gif";s:4:"1bdc";s:48:"libraries/zendvcard/class.tx_vcardmail_vcard.php";s:4:"750c";s:64:"libraries/zendvcard/Vcard/class.tx_vcardmail_vcard_generator.php";s:4:"e767";}',
	'suggests' => array(
	),
);
?>
