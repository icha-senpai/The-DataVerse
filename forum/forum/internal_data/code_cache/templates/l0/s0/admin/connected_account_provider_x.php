<?php
// FROM HASH: 6ffa8a9e71f388a0f8b7697127d1db92
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= $__templater->formTextBoxRow(array(
		'name' => 'options[consumer_key]',
		'value' => $__vars['options']['consumer_key'],
	), array(
		'label' => 'Consumer key',
		'hint' => 'Required',
		'explain' => 'To allow users to sign in with their X accounts, you must create an <a href="https://developer.x.com/" target="_blank">X application</a> and enter the consumer key and secret.',
	)) . '

' . $__templater->formTextBoxRow(array(
		'name' => 'options[consumer_secret]',
		'value' => $__vars['options']['consumer_secret'],
	), array(
		'label' => 'Consumer secret',
		'hint' => 'Required',
		'explain' => 'If you have created an X application to allow X sign in, set the application\'s consumer secret here.',
	));
	return $__finalCompiled;
}
);