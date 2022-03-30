<?php
declare(strict_types=1);

namespace RH\RhRecaptcha\Domain\Validator;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use In2code\Powermail\Domain\Model\Answer;
use In2code\Powermail\Domain\Model\Mail;
use In2code\Powermail\Domain\Validator\CustomValidator;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Extbase\Validation\Exception\InvalidValidationOptionsException;

/**
 * ReCaptchaValidator
 */
class ReCaptchaValidator
{
    /**
     * @param Mail            $mail
     * @param CustomValidator $object
     */
    public function isValid(Mail $mail, CustomValidator $object)
    {
		if (!$this->hasRecaptcha($mail)) {
			return;
		}

		$answers = $mail->getAnswers();
		$captchaFoundInAnswer = false;
		$field = null;

		/** @var Answer $answer */
		foreach ($answers as $answer) {
			$field = $answer->getField();

			if ($field->getType() !== 'recaptcha') {
				continue;
			}

			$response = GeneralUtility::_GP('g-recaptcha-response');

			if ($response !== null) {
				$captchaFoundInAnswer = true;

				/** @var ConfigurationManager $configurationManager */
				$configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);
				$fullTs = $configurationManager->getConfiguration(
					ConfigurationManager::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
				);

				$reCaptchaSettings = $fullTs['plugin.']['tx_powermail.']['settings.']['setup.']['reCAPTCHA.'];

				if (isset($reCaptchaSettings) &&
					is_array($reCaptchaSettings) &&
					isset($reCaptchaSettings['secretKey']) &&
					$reCaptchaSettings['secretKey']
				) {
					$ch = curl_init();

					$fields = [
						'secret' => $reCaptchaSettings['secretKey'],
						'response' => $response,
					];

					$fieldsString = '';

					foreach ($fields as $key => $value) {
						$fieldsString .= $key . '=' . $value . '&';
					}

					rtrim($fieldsString, '&');

					//set the url, number of POST vars, POST data
					curl_setopt($ch, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify');
					curl_setopt($ch, CURLOPT_POST, count($fields));
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $fieldsString);

					//execute post
					$result = json_decode(curl_exec($ch));

					if (!(bool) $result->success) {
						$object->setErrorAndMessage(
							$field,
							LocalizationUtility::translate('validation.possible_robot', 'rhRecaptcha')
						);
					}
				} else {
					throw new InvalidValidationOptionsException(
						LocalizationUtility::translate('error.no_secretKey', 'rhRecaptcha'),
						1358349150
					);
				}
			}
		}

		// if no captcha arguments given (maybe deleted from DOM)
		if (!$captchaFoundInAnswer) {
			$object->setErrorAndMessage(
				$field,
				LocalizationUtility::translate('validation.possible_robot', 'rhRecaptcha')
			);
		}
    }

	/**
	 * @param Mail $mail
	 *
	 * @return bool
	 */
    protected function hasRecaptcha(Mail $mail): bool
	{
		$form = $mail->getForm();
		$fields = $form->getFields('recaptcha');

		return !empty($fields);
	}
}
