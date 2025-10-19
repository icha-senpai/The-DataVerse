<?php
// FROM HASH: 85b2647f3230d25efcdc3ae15e3a7699
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '// STICKY NAV SETUP

';
	if ($__templater->func('property', array('publicNavSticky', ), false)) {
		$__finalCompiled .= '

.p-pageWrapper .p-navSticky
{
	z-index: @zIndex-1;

	&.is-sticky
	{
		z-index: @zIndex-4;
		// .m-dropShadow(0, 2px, 5px, 0px, .26);
		';
		if (!$__templater->func('property', array('uix_removeHeaderWrapper', ), false)) {
			$__finalCompiled .= '
		position: fixed !important;
		top: 0;
		bottom: auto !important;
		left: 0;
		right: 0;
		margin-left: auto;
		margin-right: auto;
		';
		}
		$__finalCompiled .= '

		';
		if (($__templater->func('property', array('uix_pageStyle', ), false) == 'wrapped') AND (!$__templater->func('property', array('uix_removeHeaderWrapper', ), false))) {
			$__finalCompiled .= '
		@uix_pageSpacerTotal: (@xf-pageEdgeSpacer * 2);
		@uix_adjustedWrappedPageWidth: @xf-pageWidthMax - @uix_pageSpacerTotal;

		width: 100vw !important;
		@media (min-width: @xf-responsiveEdgeSpacerRemoval) {
			width: ~\'calc(100vw - @{uix_pageSpacerTotal}) !important\';
		}

		@media (min-width: @uix_adjustedWrappedPageWidth) {
			width: @uix_adjustedWrappedPageWidth !important;	
		}
		';
		}
		$__finalCompiled .= '

		> * {box-shadow: @xf-uix_elevation1; border-radius: 0 !important;}

		.p-nav {.xf-uix_stickyNavigationStyle();}

		.p-sectionLinks {.xf-uix_sectionLinksStickyStyle();}

		';
		if ($__templater->func('property', array('uix_pageStyle', ), false) != 'covered') {
			$__finalCompiled .= '
		// .m-pageWidth();
		padding: 0 !important;

		';
		}
		$__finalCompiled .= '

		';
		if ($__templater->func('property', array('uix_pageStyle', ), false) == 'fluid') {
			$__finalCompiled .= '
		> * {
			max-width: 100%;
		}
		';
		}
		$__finalCompiled .= '

		.p-nav-inner {
			height: @xf-uix_stickyNavHeight;

			.p-navEl,
			.p-navEl-link {max-height: @xf-uix_stickyNavHeight;}
		}

		';
		if ($__templater->func('property', array('publicNavSticky', ), false)) {
			$__finalCompiled .= '
		.p-sectionLinks {
			border-bottom: 0;

			.pageContent {
				height: @xf-uix_stickySectionLinkHeight;
			}
		}
		';
		}
		$__finalCompiled .= '
	}

	';
		if ($__templater->func('property', array('uix_removeHeaderWrapper', ), false)) {
			$__finalCompiled .= '
	@supports (position: sticky) or (position: -webkit-sticky)
	{
		&
		{
			position: -webkit-sticky;
			position: sticky;
			top: 0;

			&.is-sticky-broken,
			&.is-sticky-disabled
			{
				position: static;
				top: auto;
			}
		}
	}
	';
		}
		$__finalCompiled .= '
}

';
	}
	$__finalCompiled .= '

';
	if ($__templater->func('property', array('uix_mainTabsSticky', ), false)) {
		$__finalCompiled .= '
.uix_headerContainer .uix_mainTabBar.is-sticky {
	position: fixed !important;
	bottom: auto !important;
	left: 0;
	right: 0;
	@media (max-width: @xf-uix_search_maxResponsiveWidth ) {z-index: 500;}
	margin: 0 auto;
	border-radius: 0 !important;

	.tabs-tab {height: @xf-uix_mainTabsHeight;}

	box-shadow: @xf-uix_elevation1;

	';
		if (($__templater->func('property', array('uix_pageStyle', ), false) == 'wrapped') AND (!$__templater->func('property', array('uix_removeHeaderWrapper', ), false))) {
			$__finalCompiled .= '
	width: @xf-pageWidthMax - (@xf-pageEdgeSpacer * 2) !important;
	';
		}
		$__finalCompiled .= '
}

.uix_mainTabBar.is-sticky {
	box-shadow: @xf-uix_elevation1;
	z-index: 300;
}

';
	}
	$__finalCompiled .= '

';
	if ($__templater->func('property', array('uix_stickyStaffBar', ), false)) {
		$__finalCompiled .= '
.p-staffBar.is-sticky {
	';
		if (!$__templater->func('property', array('uix_removeHeaderWrapper', ), false)) {
			$__finalCompiled .= '
	position: fixed !important;
	bottom: auto !important;
	top: 0 !important;
	left: 0;
	right: 0;
	margin: 0 auto;
	';
		}
		$__finalCompiled .= '
	z-index: 500;
	border-radius: 0 !important;
	.xf-uix_stickyStaffbarStyle();
	height: @xf-uix_stickyStaffBarHeight;

	';
		if ($__templater->func('property', array('publicNavSticky', ), false) == 'none') {
			$__finalCompiled .= '
	box-shadow: @xf-uix_elevation1;
	';
		}
		$__finalCompiled .= '

	';
		if (($__templater->func('property', array('uix_pageStyle', ), false) == 'wrapped') AND (!$__templater->func('property', array('uix_removeHeaderWrapper', ), false))) {
			$__finalCompiled .= '
	width: @xf-pageWidthMax - (@xf-pageEdgeSpacer * 2) !important;
	';
		}
		$__finalCompiled .= '
}

';
		if ($__templater->func('property', array('uix_removeHeaderWrapper', ), false)) {
			$__finalCompiled .= '
.p-staffBar {
	@supports (position: sticky) or (position: -webkit-sticky)
	{
		&
		{
			position: -webkit-sticky;
			position: sticky;
			top: 0;

			&.is-sticky-broken,
			&.is-sticky-disabled
			{
				position: static;
				top: auto;
			}
		}
	}
}
';
		}
		$__finalCompiled .= '

';
	}
	return $__finalCompiled;
}
);