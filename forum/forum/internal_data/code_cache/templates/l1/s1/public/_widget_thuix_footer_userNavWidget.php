<?php
// FROM HASH: dd462f0492ea71eb00dc7db06e198c62
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '<div class="block" data-widget-definition="th_userNavigation">
    <div class="block-container block-container--noStripRadius">
        <h3 class="block-minorHeader">User Menu</h3>
        <div class="block-body">
            ';
	if ($__vars['xf']['visitor']['user_id']) {
		$__finalCompiled .= '
                <a class="blockLink rippleButton" href="' . $__templater->func('link', array('members', $__vars['xf']['visitor'], ), true) . '">' . 'Profile' . '</a>
                <a class="blockLink rippleButton" href="' . $__templater->func('link', array('account/account-details', ), true) . '">' . 'Account details' . '</a>
                <a class="blockLink rippleButton" href="' . $__templater->func('link', array('whats-new/news-feed', ), true) . '">' . 'News feed' . '</a>
                <a class="blockLink rippleButton" href="' . $__templater->func('link', array('logout', null, array('t' => $__templater->func('csrf_token', array(), false), ), ), true) . '">' . 'Log out' . '</a>
            ';
	} else {
		$__finalCompiled .= '
                <a class="blockLink rippleButton" href="' . $__templater->func('link', array('login', ), true) . '">' . 'Login' . '</a>
            ';
	}
	$__finalCompiled .= '
        </div>
    </div>
</div>';
	return $__finalCompiled;
}
);