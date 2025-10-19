<?php
// FROM HASH: 5ac776bc9eb23afac954dc07f0284d53
return array(
'macros' => array('forum' => array(
'arguments' => function($__templater, array $__vars) { return array(
		'activeTab' => '',
	); },
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '
	<!-- first tab --> 
	<!-- new tab --> 
	';
	if ($__vars['xf']['options']['forumsDefaultPage'] == 'new_posts') {
		$__finalCompiled .= '
		<a href="' . $__templater->func('link', array('forums', ), true) . '" class="tabs-tab ' . (($__vars['activeTab'] == 'new_posts') ? 'is-active' : '') . '">' . 'New posts' . '</a>
	';
	}
	$__finalCompiled .= '
	<a href="' . $__templater->func('link', array('forums/-/list', ), true) . '" class="tabs-tab ' . (($__vars['activeTab'] == 'forum_list') ? 'is-active' : '') . '">' . 'Forum list' . '</a>
';
	return $__finalCompiled;
}
)),
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';

	return $__finalCompiled;
}
);