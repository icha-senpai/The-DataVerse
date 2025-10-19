<?php
// FROM HASH: f47dc18859f0c68fd1df504868c6f9fb
return array(
'macros' => array('uix_iconBlurb' => array(
'arguments' => function($__templater, array $__vars) { return array(
		'title' => '',
		'text' => '',
		'buttonText' => '',
		'icon' => '',
		'image' => '',
		'iconDark' => '',
		'buttonUrl' => '',
	); },
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '
	<div class="uix_blurb">
		';
	if ($__vars['icon']) {
		$__finalCompiled .= '
			' . $__templater->fontAwesome($__templater->escape($__vars['icon']), array(
			'class' => 'uix_blurbIcon',
		)) . '
		';
	}
	$__finalCompiled .= '
		';
	if ($__vars['image']) {
		$__finalCompiled .= '
			<img alt="" class="uix_blurbIcon" src="' . $__templater->escape($__vars['icon']) . '" />
		';
	}
	$__finalCompiled .= '
		<div class="uix_blurbBody">
			<div class="uix_heading uix_heading--h3">' . $__templater->escape($__vars['title']) . '</div>
			<div class="uix_paragraph">' . $__templater->escape($__vars['text']) . '</div>
			<a href="' . $__templater->escape($__vars['buttonUrl']) . '" class="button">' . $__templater->escape($__vars['buttonText']) . '</a>
		</div>
	</div>
';
	return $__finalCompiled;
}
),
'uix_testimonial' => array(
'arguments' => function($__templater, array $__vars) { return array(
		'user' => '',
		'text' => '',
	); },
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '
	<div class="uix_testimonial block">
		<div class="block-container">
			<div class="block-body block-row">
				<div class="uix_paragraph">' . $__templater->escape($__vars['text']) . '</div>
				<div class="uix_text uix_text--small uix_text--secondary uix_text--centered">' . $__templater->escape($__vars['user']) . '</div>
			</div>
		</div>
	</div>
';
	return $__finalCompiled;
}
)),
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->setPageParam('removePageWrapper', '1');
	$__finalCompiled .= '
';
	$__templater->setPageParam('uix_hidePageTitle', true);
	$__finalCompiled .= '
';
	$__templater->setPageParam('pageAction', false);
	$__finalCompiled .= '
';
	$__templater->setPageParam('uix_hideNotices', true);
	$__finalCompiled .= '
';
	$__templater->setPageParam('homePage', true);
	$__finalCompiled .= '
';
	$__templater->includeCss('uix_home.less');
	$__finalCompiled .= '

' . '

<div class="uix_section uix_section--hero uix_section--emphasis">
	<div class="pageContent">
		<div class="uix_hero">
			<div class="uix_hero__content">
				<div class="uix_heading uix_heading--h1">This is a hero title</div>
				<div class="uix_paragraph">This is hero text</div>
				<a href="#" class="button button--cta">Hero button</a>
			</div>
			<div class="uix_hero__media">
			</div>
		</div>
	</div>
</div>

' . '

<div class="uix_section uix_section--primary">
	<div class="pageContent">
		<div class="uix_titlebar">
			<h3 class="uix_heading uix_heading--h2">Heading 2</h3>
			<div class="uix_titlebar--opposite">
				<a class="button" href="#">Titlebar button</a>
			</div>
		</div>
		' . $__templater->renderWidget('forum_overview_new_posts', array(), array()) . '
	</div>
</div>

' . '

<div class="uix_section uix_section--small uix_section--cta uix_section--emphasis">
	<div class="pageContent">
		<div class="uix_heading uix_heading--h2">CTA Title</div>
		<div class="uix_paragraph">This is some sample CTA text. This is some sample CTA text.</div>
		<a href="#" class="button button--cta"><span class="button-text">CTA Button</span></a>
	</div>
</div>

' . '

' . '

<div class="uix_section uix_section--small uix_section--noPadding uix_blurbSection">
	<div class="pageContent">
		<div class="uix_blurbRow">
			' . $__templater->callMacro(null, 'uix_iconBlurb', array(
		'buttonText' => 'View forum',
		'icon' => 'fa-arrow-up',
		'iconDark' => $__templater->func('base_url', array(), false) . $__templater->func('property', array('uix_imagePath', ), false) . '/syngates/icons/community-icon-dark.png',
		'buttonUrl' => $__templater->func('link', array('forums', ), false),
		'title' => 'Join our community',
		'text' => 'Join a growing list of people who love to talk about playing guitar.',
	), $__vars) . '
			' . $__templater->callMacro(null, 'uix_iconBlurb', array(
		'title' => 'Show your new skills',
		'icon' => 'fa-arrow-down',
		'iconDark' => $__templater->func('base_url', array(), false) . $__templater->func('property', array('uix_imagePath', ), false) . '/syngates/icons/riff-skills-icon-dark.png',
		'buttonUrl' => $__templater->func('link', array('riffs', ), false),
		'buttonText' => 'see community riffs',
		'text' => 'Share with the community song covers, new skills learned, or just any progress with our Community Riffs.',
	), $__vars) . '
		</div>
	</div>
</div>

' . '

' . '

<div class="uix_section uix_section--secondary">
	<div class="pageContent">
		<div class="uix_heading uix_heading--h2 uix_heading--centered">Testimonial Title</div>

		<div class="uix_testimonialList">
			' . $__templater->callMacro(null, 'uix_testimonial', array(
		'user' => 'Testimonial User',
		'text' => 'This is a chunk of testimonial text. This is a chunk of testimonial text. This is a chunk of testimonial text. This is a chunk of testimonial text.',
	), $__vars) . '
			' . $__templater->callMacro(null, 'uix_testimonial', array(
		'user' => 'Testimonial User',
		'text' => 'This is a chunk of testimonial text. This is a chunk of testimonial text. This is a chunk of testimonial text. This is a chunk of testimonial text.',
	), $__vars) . '
			' . $__templater->callMacro(null, 'uix_testimonial', array(
		'user' => 'Testimonial User',
		'text' => 'This is a chunk of testimonial text. This is a chunk of testimonial text. This is a chunk of testimonial text. This is a chunk of testimonial text.',
	), $__vars) . '
		</div>
	</div>
</div>';
	return $__finalCompiled;
}
);