<?php
// FROM HASH: de8f7cdd35915c6211fbc04a850e7670
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '// Added to fix the dark pixel bug https://github.com/Audentio/xf2theme-issues/issues/1055

.device--isAndroid .p-staffBar .hScroller-scroll {
	overflow-x: auto;
}

// Add persistent scrollbar on windows

.offCanvasMenu-content {
	overflow-y: scroll;
}

// remove bottom tabbar on mobile when keyboard is present

@media (max-height: 400px) {
	.uix_tabBar {display: none !important;}
}

// Double block-outer-opposite filter fix
.block-outer-opposite--postSortFilter {
	margin-top: @xf-paddingMedium;
	
	@media (min-width: @xf-responsiveNarrow) {
		clear:both;
	}
}

//Sidenav width hotfix
.p-body-sideNavInner.is-active .uix_sidebar--scroller {
    width: auto !important;
}

/* Fix for What\'s New icon */
.p-navgroup-link.p-navgroup-link--whatsnew i:after {
	width: 1em;
}';
	return $__finalCompiled;
}
);