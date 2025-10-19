<?php
// FROM HASH: bbc134a8f83c623f0e7159720c36fec1
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->pageParams['pageTitle'] = $__templater->preEscaped('Continue with ' . $__templater->escape($__vars['provider']['title']) . '');
	$__finalCompiled .= '

' . $__templater->form('
	<div class="block-container">
		<div class="block-body">
			' . $__templater->formInfoRow('
				' . 'Are you sure you want to continue connecting this account?' . '
				<strong>' . $__templater->escape($__vars['provider']['title']) . '</strong>
			', array(
		'rowtype' => 'confirm',
	)) . '

			' . $__templater->func('redirect_input', array($__vars['redirect'], null, true)) . '
		</div>

		' . $__templater->formSubmitRow(array(
		'icon' => 'confirm',
	), array(
		'rowtype' => 'simple',
	)) . '
	</div>
', array(
		'action' => $__templater->func('link', array('register/connected-accounts', $__vars['provider'], array('setup' => true, ), ), false),
		'class' => 'block',
	));
	return $__finalCompiled;
}
);