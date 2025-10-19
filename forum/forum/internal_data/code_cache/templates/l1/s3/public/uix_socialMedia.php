<?php
// FROM HASH: 203d8d665a0f917adf31f092a2e3aa9c
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->includeCss('uix_socialMedia.less');
	$__finalCompiled .= '
';
	$__compilerTemp1 = '';
	$__compilerTemp1 .= '
		';
	if ($__vars['xf']['options']['th_facebookUrl_uix']) {
		$__compilerTemp1 .= '
			<li><a aria-label="Facebook" data-xf-init="tooltip" title="' . 'Facebook' . '" target="_blank" href="' . $__templater->escape($__vars['xf']['options']['th_facebookUrl_uix']) . '">
					' . $__templater->fontAwesome('fab fa-facebook', array(
		)) . '
				</a></li>
		';
	}
	$__compilerTemp1 .= '
		';
	if ($__vars['xf']['options']['th_deviantArtUrl_uix']) {
		$__compilerTemp1 .= '
			<li><a aria-label="Deviant Art" data-xf-init="tooltip" title="' . 'Deviant Art' . '" target="_blank" href="' . $__templater->escape($__vars['xf']['options']['th_deviantArtUrl_uix']) . '">
					' . $__templater->fontAwesome('fab fa-deviantart', array(
		)) . '
				</a></li>
		';
	}
	$__compilerTemp1 .= '
		';
	if ($__vars['xf']['options']['th_discordUrl_uix']) {
		$__compilerTemp1 .= '
			<li><a aria-label="Discord" data-xf-init="tooltip" title="' . 'Discord URL' . '" target="_blank" href="' . $__templater->escape($__vars['xf']['options']['th_discordUrl_uix']) . '">
					' . $__templater->fontAwesome('fab fa-discord', array(
		)) . '
				</a></li>
		';
	}
	$__compilerTemp1 .= '
		';
	if ($__vars['xf']['options']['th_flickrUrl_uix']) {
		$__compilerTemp1 .= '
			<li><a aria-label="Flickr" data-xf-init="tooltip" title="' . 'option.uix_flickr' . '" target="_blank" href="' . $__templater->escape($__vars['xf']['options']['th_flickrUrl_uix']) . '">
					' . $__templater->fontAwesome('fab fa-flickr', array(
		)) . '
				</a></li>
		';
	}
	$__compilerTemp1 .= '
		';
	if ($__vars['xf']['options']['th_gitHubUrl_uix']) {
		$__compilerTemp1 .= '
			<li><a aria-label="GitHub" data-xf-init="tooltip" title="' . 'GitHub' . '" target="_blank" href="' . $__templater->escape($__vars['xf']['options']['th_gitHubUrl_uix']) . '">
					' . $__templater->fontAwesome('fab fa-github-alt', array(
		)) . '
				</a></li>
		';
	}
	$__compilerTemp1 .= '
		';
	if (($__vars['xf']['versionId'] >= 2010010) AND $__vars['xf']['options']['th_googlePlus_uix']) {
		$__compilerTemp1 .= '
			<li><a aria-label="Google Plus" data-xf-init="tooltip" title="' . 'Google+' . '" target="_blank" href="' . $__templater->escape($__vars['xf']['options']['th_googlePlus_uix']) . '">
				' . $__templater->fontAwesome('fab fa-google-plus-g', array(
		)) . '
			</a></li>
		';
	}
	$__compilerTemp1 .= '
		';
	if ($__vars['xf']['options']['th_instagramUrl_uix']) {
		$__compilerTemp1 .= '
			<li><a aria-label="Instagram" data-xf-init="tooltip" title="' . 'Instagram' . '" target="_blank" href="' . $__templater->escape($__vars['xf']['options']['th_instagramUrl_uix']) . '">
					' . $__templater->fontAwesome('fab fa-instagram', array(
		)) . '
				</a></li>
		';
	}
	$__compilerTemp1 .= '
		';
	if ($__vars['xf']['options']['th_linkedInUrl_uix']) {
		$__compilerTemp1 .= '
			<li><a aria-label="LinkedIn" data-xf-init="tooltip" title="' . 'LinkedIn' . '" target="_blank" href="' . $__templater->escape($__vars['xf']['options']['th_linkedInUrl_uix']) . '">
					' . $__templater->fontAwesome('fab fa-linkedin', array(
		)) . '
				</a></li>
		';
	}
	$__compilerTemp1 .= '
		';
	if ($__vars['xf']['options']['th_pinterestUrl_uix']) {
		$__compilerTemp1 .= '
			<li><a aria-label="Pinterest" data-xf-init="tooltip" title="' . 'Pinterest' . '" target="_blank" href="' . $__templater->escape($__vars['xf']['options']['th_pinterestUrl_uix']) . '">
					' . $__templater->fontAwesome('fab fa-pinterest', array(
		)) . '
				</a></li>
		';
	}
	$__compilerTemp1 .= '
		';
	if ($__vars['xf']['options']['th_redditUrl_uix']) {
		$__compilerTemp1 .= '
			<li><a aria-label="Reddit" data-xf-init="tooltip" title="' . 'Reddit' . '" target="_blank" href="' . $__templater->escape($__vars['xf']['options']['th_redditUrl_uix']) . '">
					' . $__templater->fontAwesome('fab fa-reddit', array(
		)) . '
				</a></li>
		';
	}
	$__compilerTemp1 .= '
		';
	if ($__vars['xf']['options']['th_steamUrl_uix']) {
		$__compilerTemp1 .= '
			<li><a aria-label="Steam" data-xf-init="tooltip" title="' . 'Steam' . '" target="_blank" href="' . $__templater->escape($__vars['xf']['options']['th_steamUrl_uix']) . '">
					' . $__templater->fontAwesome('fab fa-steam', array(
		)) . '
				</a></li>
		';
	}
	$__compilerTemp1 .= '
		' . '
		';
	if ($__vars['xf']['options']['th_twitchUrl_uix']) {
		$__compilerTemp1 .= '
			<li><a aria-label="Twitch" data-xf-init="tooltip" title="' . 'Twitch' . '" target="_blank" href="' . $__templater->escape($__vars['xf']['options']['th_twitchUrl_uix']) . '">
					' . $__templater->fontAwesome('fab fa-twitch', array(
		)) . '
				</a></li>
		';
	}
	$__compilerTemp1 .= '
		';
	if ($__vars['xf']['options']['th_twitterUrl_uix']) {
		$__compilerTemp1 .= '
			<li><a aria-label="Twitter" data-xf-init="tooltip" title="' . 'Twitter' . '" target="_blank" href="' . $__templater->escape($__vars['xf']['options']['th_twitterUrl_uix']) . '">
					' . $__templater->fontAwesome('fab fa-twitter', array(
		)) . '
				</a></li>
		';
	}
	$__compilerTemp1 .= '
		';
	if ($__vars['xf']['options']['th_youtubeUrl_uix']) {
		$__compilerTemp1 .= '
			<li><a aria-label="YouTube" data-xf-init="tooltip" title="' . 'YouTube' . '" target="_blank" href="' . $__templater->escape($__vars['xf']['options']['th_youtubeUrl_uix']) . '">
					' . $__templater->fontAwesome('fab fa-youtube', array(
		)) . '
				</a></li>
		';
	}
	$__compilerTemp1 .= '
	';
	if (strlen(trim($__compilerTemp1)) > 0) {
		$__finalCompiled .= '
<ul class="uix_socialMedia">
	' . $__compilerTemp1 . '
</ul>
';
	}
	return $__finalCompiled;
}
);