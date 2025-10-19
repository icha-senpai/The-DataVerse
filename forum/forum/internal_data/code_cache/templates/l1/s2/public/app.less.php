<?php
// FROM HASH: 0e6a5c65b8118e00368616237f616bec
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '@_nav-elTransitionSpeed: @xf-animationSpeed;
@_navAccount-hPadding: @xf-paddingLarge;
@uix_sidebarNavBreakpoint: ' . (($__templater->func('uix_intval', array($__templater->func('property', array('pageWidthMax', ), false), ), false) + 1) + ((2 * (($__templater->func('uix_intval', array($__templater->func('property', array('uix_sidebarNavWidth', ), false), ), false) + $__templater->func('uix_intval', array($__templater->func('property', array('elementSpacer', ), false), ), false)))))) . 'px;

.u-anchorTarget
{
	.m-stickyHeaderConfig(@xf-publicNavSticky);
	height: (@_stickyHeader-height + @_stickyHeader-offset);
	margin-top: -(@_stickyHeader-height + @_stickyHeader-offset);
}

.u-pageCentered
{
	.m-pageWidth();
	.m-pageInset();
}

@supports (scroll-padding-top: 10px)
{
	html:not(.has-browser-safari)
	{
		.m-stickyHeaderConfig(@xf-publicNavSticky);
		scroll-padding-top: (@_stickyHeader-height + @_stickyHeader-offset);
	}

	html:not(.has-browser-safari) .u-anchorTarget
	{
		height: 0;
		margin-top: 0;
	}
}

.uix_pageWidth--wrapped {
	width: 100%;
	';
	if ($__templater->func('property', array('uix_pageStyle', ), false) == 'wrapped') {
		$__finalCompiled .= '
		.m-pageWidth();
	';
	}
	$__finalCompiled .= '
}

.p-pageWrapper
{
	position: relative;
	display: flex;
	flex-direction: column;
	min-height: 100vh;
	// .xf-pageBackground();

	.is-modalOverlayOpen &
	{
		& when (unit(xf-default(@xf-overlayMaskBlur, 0)) > 0)
		{
			filter: blur(@xf-overlayMaskBlur);
		}
	}
	
	flex-grow: 1;
	';
	if ($__templater->func('property', array('uix_pageStyle', ), false) == 'wrapped') {
		$__finalCompiled .= '
		.xf-uix_pageWrapper();
		@media (min-width: (@xf-responsiveEdgeSpacerRemoval + 1px)) {
			margin-top: @xf-pageEdgeSpacer;
			margin-bottom: @xf-pageEdgeSpacer;
			padding: @xf-pageEdgeSpacer;
		}
	
		@media (max-width: @xf-responsiveEdgeSpacerRemoval) {
			border: none;
			box-shadow: none;
		}
	';
	}
	$__finalCompiled .= '
}

// RESPONSIVE HEADER

.p-offCanvasAccountLink
{
	display: none;
	
	.avatar {margin-right: 24px;}
}

@media (max-width: @xf-responsiveNarrow)
{
	.p-offCanvasAccountLink, .p-offCanvasRegisterLink
	{
		display: block;
	}
}

@media (max-width: @xf-pageWidthMax) {
	#uix_widthToggle--trigger {display: none;}
}

#uix_widthToggle--trigger:hover {cursor: pointer;}
';
	if ($__templater->func('property', array('uix_iconFontFamily', ), false) != 'fontawesome') {
		$__finalCompiled .= '
.uix_page--fixed #uix_widthToggle--trigger .fa--xf:before {
	.m-faBase();
	.m-faContent(@fa-var-expand-alt);
}
';
	}
	$__finalCompiled .= '

' . $__templater->includeTemplate('app_staffbar.less', $__vars) . '
';
	if ($__templater->func('property', array('uix_viewportShowLogoBlock', ), false) != '100%') {
		$__finalCompiled .= '
	' . $__templater->includeTemplate('app_header.less', $__vars) . '
';
	}
	$__finalCompiled .= '
' . $__templater->includeTemplate('app_stickynav.less', $__vars) . '
' . $__templater->includeTemplate('app_nav.less', $__vars) . '
' . $__templater->includeTemplate('app_sectionlinks.less', $__vars) . '
' . $__templater->includeTemplate('app_body.less', $__vars) . '
' . $__templater->includeTemplate('app_breadcrumbs.less', $__vars) . '
' . $__templater->includeTemplate('app_title.less', $__vars) . '
' . $__templater->includeTemplate('app_footer.less', $__vars) . '
' . $__templater->includeTemplate('app_inlinemod.less', $__vars) . '
' . $__templater->includeTemplate('app_ignored.less', $__vars) . '
' . $__templater->includeTemplate('app_username_styles.less', $__vars) . '
' . $__templater->includeTemplate('app_user_banners.less', $__vars) . '
' . $__templater->includeTemplate('app_alerts.less', $__vars) . '
' . $__templater->includeTemplate('app_content_vote.less', $__vars);
	return $__finalCompiled;
}
);