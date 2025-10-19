<?php
// FROM HASH: 392f5acf6032731f1cc0dd3d8e59065c
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	if ($__templater->method($__vars['xf']['cookieConsent'], 'getMode', array()) == 'advanced') {
		$__finalCompiled .= '
	<div class="u-pageCentered">
		' . $__templater->callMacro(null, 'misc_cookies::cookie_consent_form', array(), $__vars) . '
	</div>
';
	} else {
		$__finalCompiled .= '
	<div class="u-alignCenter">
		' . 'This site uses cookies to help personalise content, tailor your experience and to keep you logged in if you register.<br />
By continuing to use this site, you are consenting to our use of cookies.' . '
	</div>

	<div class="u-inputSpacer u-alignCenter uix_cookieButtonRow">
		' . $__templater->button('Accept', array(
			'icon' => 'confirm',
			'href' => $__templater->func('link', array('account/dismiss-notice', null, array('notice_id' => $__vars['notice']['notice_id'], ), ), false),
			'class' => 'js-noticeDismiss button--notice',
		), '', array(
		)) . '
		' . $__templater->button('Learn more' . $__vars['xf']['language']['ellipsis'], array(
			'href' => $__templater->func('link', array('help/cookies', ), false),
			'class' => 'button--notice',
		), '', array(
		)) . '
	</div>
';
	}
	return $__finalCompiled;
}
);