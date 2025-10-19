<?php
// FROM HASH: d1ce539a435fa86ebe4f99f01b9835d7
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '// ######################################### STAFF BAR #################################

.p-staffBar
{
	z-index: 150;
	.xf-publicStaffBar();
		';
	if ($__templater->func('property', array('uix_pageStyle', ), false) == 'fixed') {
		$__finalCompiled .= '
			.m-pageWidth();
			.m-pageInset();
		';
	}
	$__finalCompiled .= '

	.hScroller-action
	{
		.m-hScrollerActionColorVariation(
			xf-default(@xf-publicStaffBar--background-color, transparent),
			xf-default(@xf-publicStaffBar--color, ~""),
			xf-intensify(@xf-publicStaffBar--color, 10%)
		);
	}
	
	';
	if ($__templater->func('property', array('uix_stickyStaffBar', ), false)) {
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

	.pageContent {
		display: flex;
		align-items: center;
		// justify-content: space-between;
		position: relative;
		height: 100%;
		';
	if ($__templater->func('property', array('uix_pageStyle', ), false) == 'covered') {
		$__finalCompiled .= '
			.m-pageWidth();
			.m-pageInset();
		';
	}
	$__finalCompiled .= '
		
		min-height: @xf-uix_stickyStaffBarHeight;

		.p-nav-opposite a:not(.button)
		{
			color: inherit;

			&:hover {.xf-uix_staffBarTabHover();}
		}
		
		a {
			.xf-uix_staffBarTab();
			
			&:hover {.xf-uix_staffBarTabHover();}
		}

	}


}

.p-staffBar-inner
{
	.m-clearFix();
	// padding-top: @xf-paddingMedium;
	//padding-bottom: @xf-paddingMedium;
}

.p-staffBar-link
{
	display: inline-block;
	vertical-align: top;
	color: inherit;
	padding: 4px @xf-paddingMedium;
	margin-right: .35em;
	.xf-uix_staffBarTab();

	&:last-child
	{
		margin-right: 0;
	}

	&:hover
	{
		text-decoration: none;
		// background: xf-diminish(@xf-publicStaffBar--background-color, 6%);
		border-radius: @xf-borderRadiusSmall;
		.xf-uix_staffBarTabHover();
	}
}';
	return $__finalCompiled;
}
);