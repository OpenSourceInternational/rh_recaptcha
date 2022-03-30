<?php
defined('TYPO3_MODE') or die;

$GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['rh'][] = 'RH\\RhRecaptcha\\ViewHelpers';

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('
tx_powermail.flexForm.type.addFieldOptions.recaptcha = reCAPTCHA
', 43);

$signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class);
$signalSlotDispatcher->connect(
	\In2code\Powermail\Domain\Validator\CustomValidator::class,
    'isValid',
	\RH\RhRecaptcha\Domain\Validator\ReCaptchaValidator::class,
    'isValid',
    false
);
