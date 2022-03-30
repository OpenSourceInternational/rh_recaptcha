<?php
namespace RH\RhRecaptcha\ViewHelpers;

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

use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * Class ReCaptchaViewHelper
 *
 * @author Vladimir Cherednichenko <vovacherednichenko@o-s-i.org>
 */
class ReCaptchaViewHelper extends AbstractViewHelper
{
	use CompileWithRenderStatic;

	/**
	 * @inheritDoc
	 */
	public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
	{
		$configurationManager = GeneralUtility::makeInstance(ConfigurationManagerInterface::class);

		$fullTs = $configurationManager->getConfiguration(
			ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
		);

		$reCaptchaSettings = $fullTs['plugin.']['tx_powermail.']['settings.']['setup.']['reCAPTCHA.'];

		if (isset($reCaptchaSettings) &&
			is_array($reCaptchaSettings) &&
			isset($reCaptchaSettings['siteKey']) &&
			$reCaptchaSettings['siteKey']
		) {
			$renderingContext->getVariableProvider()->add('siteKey', $reCaptchaSettings['siteKey']);
			$content = $renderChildrenClosure();
		} else {
			throw new \InvalidArgumentException('No siteKey provided in TypoScript constants', 1358349150);
		}

		$key = $reCaptchaSettings['siteKey'];
		$pageRenderer = static::getPageRenderer();
		$pageRenderer->addJsFooterInlineCode(
			'recaptcha',
			'
					var recaptchaCallback = function() {
						for (var i = 1; i <= 1000; ++i) {
							if (document.getElementById(\'g-recaptcha-\' + i)) {
								grecaptcha.render(\'g-recaptcha-\' + i, {\'sitekey\' : \'' . $key . '\'});
							}
						}
					};
					/*]]>*/					
					</script>
					<script src="https://www.google.com/recaptcha/api.js?hl=' . $reCaptchaSettings['lang'] . '&onload=recaptchaCallback&render=explicit"
						async defer data-ignore="1">/*<![CDATA[*/
				',
			false,
			true
		);

		return $content;
	}

	/**
	 * @return PageRenderer
	 */
	protected static function getPageRenderer(): PageRenderer
	{
		return GeneralUtility::makeInstance(PageRenderer::class);
	}
}
