<?php
// FROM HASH: 5af3ce903ae04be49a82113381253347
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '.p-body-header {
	';
	if ($__templater->func('property', array('uix_pageStyle', ), false) == 'fixed') {
		$__finalCompiled .= '
	.m-pageWidth();
	';
	}
	$__finalCompiled .= '

	.pageContent {
		';
	if ($__templater->func('property', array('uix_pageStyle', ), false) != 'fixed') {
		$__finalCompiled .= '
		.m-pageWidth();
		';
	}
	$__finalCompiled .= '
	}
	
	.p-body-inner & {
		max-width: 100%;
		width: 100%;
		padding-left: 0;
		padding-right: 0;

		.pageContent {
			max-width: 100%;
			width: 100%;		
			padding-left: 0;
			padding-right: 0;
		}
	}
}

.p-pageWrapper > .p-body-header .pageContent {
	min-height: 36px;
}

.p-pageWrapper .p-body-header {
	.xf-uix_titlebar();
	
	.pageContent {
		display: flex;
		align-items: center;
		
		.p-title-pageAction {
			margin-top: 0;
			display: inline-flex;
			grid-gap: 10px;
			@media(max-width: @xf-responsiveMedium) {
				flex-wrap: wrap;
			}
		}
		
		@media(max-width: @xf-responsiveMedium) {
			flex-wrap: wrap;
		}
	}
	
	.contentRow {flex-grow: 1;}
	.uix_headerInner {
		margin: 5px;
		margin-left: 0;
		flex-grow: 1;
		min-width: 0;
	}
	
	.uix_headerInner--opposite {
		display: inline-flex;
		align-items: center;
		flex-wrap: wrap;
		justify-content: center;
		margin-left: auto;
		margin-right: 0;
	}
	
	.uix_headerInner + .uix_headerInner--opposite {
		margin: 5px auto;
	}
	
	.p-title
	{
		display: flex;
		flex-wrap: wrap;
		align-items: center;
		max-width: 100%;

		&.p-title--noH1
		{
			flex-direction: row-reverse;
		}

		.p-title-pageAction {
			margin: 5px 0;
		}
	}

	.p-title-value
	{
		padding: 0;
		margin: 0 0 3px 0;
		font-size: @xf-fontSizeLargest;
		font-weight: @xf-fontWeightNormal;
		margin-right: auto;
		min-width: 0;
		.xf-uix_pageTitle();

		.has-no-flexbox &
		{
			float: left;
		}
	}

	.p-title-pageAction
	{
		margin-left: @xf-paddingLarge;
		
		@media(max-width: @xf-responsiveNarrow) {
			margin-left: 0;
			margin-top: @xf-paddingLarge;
		}
	}

	.p-description
	{
		margin: 0;
		padding: 0;
		font-size: inherit;
		color: inherit;
	}
}

@media (max-width: @xf-responsiveNarrow)
{
	.p-title-value
	{
		font-size: @xf-fontSizeLarger;
	}
}';
	return $__finalCompiled;
}
);