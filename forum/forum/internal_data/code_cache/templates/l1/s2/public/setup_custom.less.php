<?php
// FROM HASH: e8a3143642f1aeec2a7e2969541a3965
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '// This should be used for additional LESS setup code (that does not output anything).
// setup.less customizations should be avoided when possible.

// ######################## UI.X Variables ######################

// ANIMATIONS

// use for incoming elements, or growing elements
@uix_moveIn: cubic-bezier(0.0, 0.0, 0.2, 1);
// use for exiting elements, or shrinking elements
@uix_moveOut: cubic-bezier(0.4, 0.0, 1, 1);
// use for growing or moving elements that dont exit/enter the page
@uix_move: cubic-bezier(0.4, 0.0, 0.2, 1);

// UI.X browser query variables

@isIe: ~"(-ms-high-contrast: none), (-ms-high-contrast: active)";

// @uix_sidebarNavBreakpoint: ( @xf-pageWidthMax  + ( 2 * (@xf-uix_sidebarNavWidth + @xf-elementSpacer) ) );
@uix_sidebarNavBreakpoint: ' . ($__templater->func('uix_intval', array($__templater->func('property', array('pageWidthMax', ), false), ), false) + ((2 * (($__templater->func('uix_intval', array($__templater->func('property', array('uix_sidebarNavWidth', ), false), ), false) + $__templater->func('uix_intval', array($__templater->func('property', array('elementSpacer', ), false), ), false)))))) . 'px;

';
	$__vars['uix_pageEdgeSpacer'] = $__templater->preEscaped(($__templater->func('uix_intval', array($__templater->func('property', array('pageEdgeSpacer', ), false), ), false) * 2) . 'px');
	$__finalCompiled .= '

@uix_navigationPaddingV: 8px;

// UI.X MIXINS

.m-uix_whiteText (@background-color, @color: #fff) when (luma(@background-color) <= 43%) {
	color: @color;
}

.m-uix_collapseOverflow() {
	clip-path: inset(-2px -2px -2px -2px);
	
	@media @isIe {
		overflow: hidden;
	}
}

.m-uix_removePageSpacer() {
	@media (max-width: @xf-responsiveEdgeSpacerRemoval) {
		margin-left: (-@xf-pageEdgeSpacer * .5);
		margin-right: (-@xf-pageEdgeSpacer * .5);
		border-radius: 0;
		border-left: none;
		border-right: none;
	}
}

.m-pageSpacerPadding(@defaultPadding: @xf-pageEdgeSpacer) {
	
	padding-left: @defaultPadding;
	padding-right: @defaultPadding;

	// iPhone X/Xr/Xs support
	/*
	@supports(padding: max(0px))
	{
		&
		{
			padding-left: ~"max(@{defaultPadding}, env(safe-area-inset-left))";
			padding-right: ~"max(@{defaultPadding}, env(safe-area-inset-right))";
		}
	}
	*/
	
	@media (max-width: @xf-responsiveEdgeSpacerRemoval) {
		@defaultPadding: (@xf-pageEdgeSpacer / 2);
			
		padding-left: @defaultPadding;
		padding-right: @defaultPadding;

		// iPhone X/Xr/Xs support
		@supports(padding: max(0px))
		{
			&
			{
				padding-left: ~"max(@{defaultPadding}, env(safe-area-inset-left))";
				padding-right: ~"max(@{defaultPadding}, env(safe-area-inset-right))";
			}
		}		
	}
}

.m-pageSpacer() {
	';
	if ($__templater->func('property', array('uix_pageStyle', ), false) != 'wrapped') {
		$__finalCompiled .= '
		width: calc(~"100% - ' . $__templater->escape($__vars['uix_pageEdgeSpacer']) . '");
	';
	}
	$__finalCompiled .= '

	@media (max-width: @xf-responsiveEdgeSpacerRemoval) {
		';
	if ($__templater->func('property', array('uix_pageStyle', ), false) == 'covered') {
		$__finalCompiled .= '
			width: calc(~"100% - @xf-pageEdgeSpacer");
		';
	} else {
		$__finalCompiled .= '
			width: 100%;
		';
	}
	$__finalCompiled .= '
	}
}

.m-pageWidth()
{
	max-width: @xf-pageWidthMax;
	margin-left: auto;
	margin-right: auto;
	width: 100%;
	.m-pageSpacer();
	';
	if ($__templater->func('property', array('uix_pageStyle', ), false) != 'covered') {
		$__finalCompiled .= '
		.m-pageSpacerPadding();
	';
	}
	$__finalCompiled .= '
	transition: max-width 0.2s;
	
	@media (max-width: @xf-responsiveWide) {
		';
	if ($__templater->func('property', array('uix_pageStyle', ), false) == 'covered') {
		$__finalCompiled .= '
			padding-left: env(safe-area-inset-left) !important;
			padding-right: env(safe-area-inset-right) !important;
		';
	}
	$__finalCompiled .= '
	}

	.uix_page--fluid & {
		';
	if ($__templater->func('property', array('uix_pageStyle', ), false) != 'wrapped') {
		$__finalCompiled .= '
		@media (min-width: @xf-pageWidthMax) {
			max-width: 100%;
		}
		';
	} else {
		$__finalCompiled .= '
			max-width: 100%;
		';
	}
	$__finalCompiled .= '
	}

	';
	if ((($__templater->func('property', array('uix_navigationType', ), false) == 'sidebarNav')) AND (($__templater->func('property', array('uix_pageStyle', ), false) == 'covered'))) {
		$__finalCompiled .= '
	@media (max-width: @uix_sidebarNavBreakpoint)  {
		.uix_page--fixed & {max-width: 100%;}
		#uix_widthToggle--trigger {display: none;}
	}
	';
	}
	$__finalCompiled .= '
}

.m-pageInset(@defaultPadding: @xf-pageEdgeSpacer)
{
	// here to satisfy global scope only
}

';
	return $__finalCompiled;
}
);