<?php
// FROM HASH: 378487bec1932d0914d973c06b780a22
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	if ($__templater->func('count', array($__vars['xf']['visitor']['ADAVUserData']['tracked_achievement_ids'], ), false)) {
		$__finalCompiled .= '
	<a href="' . $__templater->func('link', array('account/aud-achievements', ), true) . '"
	   class="tabs-tab" role="tab" tabindex="0"
	   aria-controls="' . $__templater->func('unique_id', array('accountAdavTrackedAchievements', ), true) . '">
		' . $__templater->fontAwesome('fa-trophy', array(
		)) . '
	</a>
';
	}
	return $__finalCompiled;
}
);