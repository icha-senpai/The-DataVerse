<?php
// FROM HASH: acdeefd60cee89530e57294f21e8a1a6
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '@_nodeList-statsCellBreakpoint: @xf-uix_viewportCollapseStats;
@_iconWidth: (@xf-uix_nodeIconWidth + @xf-paddingLarge);

.block--category {
	.block-header {
		display: flex;
		align-items: center;
		.xf-uix_categoryStrip();
		';
	if ($__templater->func('property', array('uix_categoryStripOutsideWrapper', ), false)) {
		$__finalCompiled .= '
		.m-uix_removePageSpacer();
		';
	}
	$__finalCompiled .= '

		&.uix_stickyCategoryStrips {
			position: sticky;
			z-index: 200;
		}

		.uix_categoryStrip__icon {
			align-self: flex-start;

			i {
				.xf-uix_categoryIconStyle();
				vertical-align: middle;

				/*
				&:before {
					.m-faBase();
					.m-faContent(@fa-var-folder);
					';
	if ($__templater->func('property', array('uix_glyphForumIcon', ), false)) {
		$__finalCompiled .= '
					content: \'@xf-uix_glyphForumIcon\';				
					';
	}
	$__finalCompiled .= '
				}*/
			}
		}

		.node-description {
			.xf-uix_categoryDescription();
		}

		.categoryCollapse--trigger {
			font-size: @xf-uix_iconSizeLarge;
			// padding-right: @xf-paddingMedium;
			margin-left: auto;
		}
	}
}

.uix_nodeList {
	.block-container {.xf-uix_nodeContainer();}
	.block-body {.xf-uix_nodeBlockBody();}
}

.block-body {
	.node {
		&:first-child .node-body {
			border-top-left-radius: @xf-uix_nodeBlockBody--border-radius;
			border-top-right-radius: @xf-uix_nodeBlockBody--border-radius;
		}

		&:last-child .node-body {
			border-bottom-left-radius: @xf-uix_nodeBlockBody--border-radius;
			border-bottom-right-radius: @xf-uix_nodeBlockBody--border-radius;
		}
	}
}


.node
{
	& + .node
	{
		border-top: @xf-borderSize solid @xf-borderColorFaint;
	}
}


.node-body
{
	display: table;
	table-layout: fixed;
	width: 100%;
	transition: all @uix_move .15s;
	.xf-uix_nodeBody();

	&:hover {
		.xf-uix_nodeBodyHover();
		';
	if ($__templater->func('property', array('uix_nodeClickable', ), false)) {
		$__finalCompiled .= '
		cursor: pointer;
		';
	}
	$__finalCompiled .= '
	}

	';
	if ($__templater->func('property', array('uix_nodeStatsHover', ), false)) {
		$__finalCompiled .= '
	.node-meta,
	.node-stats {
		opacity: 0;
		transition: @uix_moveOut all .15s;
		left: -6px;
		position: relative;
	}

	&:hover {
		.node-meta,
		.node-stats {
			transition: @uix_moveIn all .15s;
			opacity: 1;
			left: 0;
		}
	}
	';
	}
	$__finalCompiled .= '
}

.node--depth2:nth-child(even) .node-body{
	background-color: @xf-uix_nodeBodyEven;

	&:hover {
		.xf-uix_nodeBodyHover();
	}
}

.node-body {
	display: flex;

	.node-icon,
	.node-main {display: inline-block;}

	.node-main {
		flex-grow: 1;
		width: calc(~"100% - ' . ($__templater->func('uix_intval', array($__templater->func('property', array('uix_nodeIconWidth', ), false), ), false) + $__templater->func('uix_intval', array($__templater->func('property', array('uix_nodePadding', ), false), ), false)) . 'px");
	}

	@media (max-width: @xf-responsiveMedium) {
		flex-wrap: wrap;
		.node-extra {
			width: 100%;
			padding-left: (@xf-uix_nodeIconWidth + @xf-uix_nodePadding);
		}
	}
}

.node-icon
{
	display: table-cell;
	vertical-align: top;
	text-align: center;
	width: 46px;
	width: @xf-uix_nodeIconWidth;
	padding: @xf-uix_nodePadding 0 @xf-uix_nodePadding @xf-uix_nodePadding;
	flex-shrink: 0;

	i
	{
		display: block;
		line-height: 1;
		font-size: 32px;
		.xf-uix_nodeIconStyle();

		color: @xf-nodeIconReadColor;

		.node--unread &
		{
			opacity: 1;
			color: @xf-nodeIconUnreadColor;
		}

		/*
		&:before
		{
			.m-faBase();
			font-size: inherit;

			color: @xf-nodeIconReadColor;

			.node--unread &
			{
				opacity: 1;
				color: @xf-nodeIconUnreadColor;
			}
		}
		
		.node--category &:before {
			';
	if ($__templater->func('property', array('uix_glyphForumIcon', ), false)) {
		$__finalCompiled .= '
			.m-faContent(@fa-var-folder);
			content: \'@xf-uix_glyphForumIcon\';			
			';
	}
	$__finalCompiled .= '
		}
		.node--forum &:before {
			';
	if ($__templater->func('property', array('uix_glyphForumIcon', ), false)) {
		$__finalCompiled .= '
			.m-faContent(@fa-var-comments);
			content: \'@xf-uix_glyphForumIcon\';			
			';
	}
	$__finalCompiled .= '
		}
		
		.node--search &::before
		{
			.m-faContent(@fa-var-search);
		}
		
		.node--link &:before
		{
			';
	if ($__templater->func('property', array('uix_glyphLinkIcon', ), false)) {
		$__finalCompiled .= '
			.m-faContent(@fa-var-link);
			content: \'@xf-uix_glyphLinkIcon\';			
			';
	}
	$__finalCompiled .= '
		}*/

		';
	if ($__templater->func('property', array('uix_nodeIconImages', ), false)) {
		$__finalCompiled .= '
		.xf-uix_imageIcon();

		&:before {display: none !important;}
		';
	}
	$__finalCompiled .= '
	}
}

';
	if ($__templater->func('property', array('uix_glyphCategoryIcon', ), false)) {
		$__finalCompiled .= '
.node--category .node-icon i, .uix_categoryStrip__icon i {
	svg {
		display: none;
	}

	&:before {
		.m-faContent(@xf-uix_glyphCategoryIcon);
	}
}
';
	}
	$__finalCompiled .= '

';
	if ($__templater->func('property', array('uix_glyphForumIcon', ), false)) {
		$__finalCompiled .= '
.node--forum .node-icon i {
	svg {
		display: none;
	}

	&:before {
		.m-faContent(@xf-uix_glyphForumIcon);
	}
}
';
	}
	$__finalCompiled .= '

';
	if ($__templater->func('property', array('uix_glyphPageIcon', ), false)) {
		$__finalCompiled .= '
.node--page .node-icon i {
	svg {
		display: none;
	}

	&:before {
		.m-faContent(@xf-uix_glyphPageIcon);
	}
}
';
	}
	$__finalCompiled .= '

';
	if ($__templater->func('property', array('uix_glyphLinkIcon', ), false)) {
		$__finalCompiled .= '
.node--link .node-icon i {
	svg {
		display: none;
	}

	&:before {
		.m-faContent(@xf-uix_glyphLinkIcon);
	}
}
';
	}
	$__finalCompiled .= '

';
	if ($__templater->func('property', array('uix_nodeIconImages', ), false)) {
		$__finalCompiled .= '
.node .node-icon i svg { display: none; }

.node--category .node-icon i,
.node--forum .node-icon i {.xf-uix_imageForumIcon();}

.node--forum.node--unread .node-icon i {.xf-uix_imageForumUnreadIcon();}

.node--link .node-icon i {.xf-uix_imageLinkIcon();}

.node--page .node-icon i {.xf-uix_imagePageIcon();}
';
	}
	$__finalCompiled .= '

.node-main
{
	display: table-cell;
	vertical-align: middle;
	padding: @xf-uix_nodePadding;
}

.node-stats
{
	// display: table-cell;
	display: inline-flex;
	align-items: center;
	width: 140px;
	min-width: 140px;
	vertical-align: middle;
	text-align: center;
	padding: @xf-uix_nodePadding 0;

	.pairs {line-height: 1.5;}

	> dl.pairs.pairs--rows
	{
		width: 50%;
		float: left;
		margin: 0;
		padding: 0 (@xf-paddingMedium/2);
		border-right: 1px solid @xf-borderColor;

		&:first-child
		{
			padding-left: 0;
		}

		&:last-child
		{
			padding-right: 0;
			border-right: 0;
		}
	}

	&.node-stats--single
	{
		width: 100px;
		> dl.pairs.pairs--rows
		{
			width: 100%;
			float: none;
		}
	}
	&.node-stats--triple
	{
		width: 240px;
		> dl.pairs.pairs--rows
		{
			width: 33.333%;
		}
	}

	';
	if ($__templater->func('property', array('uix_viewportCollapseStats', ), false) == '100%') {
		$__finalCompiled .= '
	display: none;
	';
	}
	$__finalCompiled .= '

	@media (max-width: @_nodeList-statsCellBreakpoint)
	{
		display: none;
	}
}

@_nodeExtra-avatarSize: 36px;

.node-extra
{
	// display: table-cell;
	display: flex;
	vertical-align: middle;
	width: 280px;
	min-width: 230px;
	padding: @xf-uix_nodePadding;
	display: inline-flex;
	//flex-direction: column;
	//justify-content: center;
	align-items: center;
	font-size: @xf-fontSizeSmall;
	';
	if ($__templater->func('property', array('uix_hideNodeLastPost', ), false)) {
		$__finalCompiled .= '
	display: none;
	';
	}
	$__finalCompiled .= '

	a:not(:hover) {color: inherit;}

	.node-extra-title:not(:hover) {color: @xf-textColor;}

	.uix_nodeExtra__rows {
		display: flex;
		flex-wrap: wrap;
		min-width: 0;
		max-width: 100%;
		flex-direction: column;
		width: 100%;
	}
}

.node-extra-row
{
	.m-overflowEllipsis();
	color: @xf-textColorMuted;
	max-width: 100%;

	.listInline {.m-overflowEllipsis();}
}

.node-extra-icon
{
	padding-right: @xf-uix_nodePadding;
	float: left;

	.avatar
	{
		.m-avatarSize(@_nodeExtra-avatarSize);
	}
}

.node-extra-title {
	padding-right: .5em; 
	font-weight: @xf-fontWeightHeavy;
}

.node-title
{
	/* -- Changed to Style Property -- Ian --
	margin: 0;
	padding: 0;
	font-size: @xf-fontSizeLarge;
	font-weight: @xf-fontWeightNormal;
	*/
	.xf-uix_nodeTitle();

	a {color: inherit;}

	.node--unread &
	{
		font-weight: @xf-fontWeightHeavy;
		.xf-uix_nodeTitle__unread();
	}
}

.node-description
{
	/* UI.X Style property -- Ian
	font-size: @xf-fontSizeSmall;
	color: @xf-textColorDimmed;
	*/
	.xf-uix_nodeDescription();

	&.node-description--tooltip
	{
		.has-js:not(.has-touchevents) &
		{
			display: none;
		}
	}
}

.node-meta
{
	display: inline;
}

.node-statsMeta
{
	display: none;

	.pairs {padding-right: .4em;}

	dt,
	dt:after {display: none;}

	';
	if ($__templater->func('property', array('uix_viewportCollapseStats', ), false) == '100%') {
		$__finalCompiled .= '
	display: inline;
	';
	}
	$__finalCompiled .= '

	@media (max-width: @_nodeList-statsCellBreakpoint)
	{
		display: inline;
	}
}

.node-bonus
{
	font-size: @xf-fontSizeSmall;
	color: @xf-textColorMuted;
	text-align: right;
}

.node-subNodesFlat
{
	font-size: @xf-fontSizeSmall;
	margin-top: .3em;

	.node-subNodesLabel
	{
		display: none;
	}
}

.node-subNodeMenu
{
	display: inline;

	i {display: none;}

	';
	if ($__templater->func('property', array('uix_viewportCollapseStats', ), false) == '100%') {
		$__finalCompiled .= '
	i {display: inline-block;}
	span {display: none;}
	';
	}
	$__finalCompiled .= '

	@media (max-width: @xf-uix_viewportCollapseStats) {
		';
	if ($__templater->func('property', array('uix_nodeStatsIcons', ), false)) {
		$__finalCompiled .= '
		i {display: inline-block;}
		span {display: none;}
		';
	}
	$__finalCompiled .= '
	}

	.menuTrigger
	{
		color: @xf-textColorMuted;
	}
}

@media (max-width: @xf-responsiveMedium)
{
	.node-main
	{
		display: block;
		width: auto;

		.node--link &,
		.node--page &
		{
			// #168882: we only display the title for these types
			// so keep these as table-cells for vertical alignment
			display: table-cell;
		}
	}

	.node-extra
	{
		';
	if ((!$__templater->func('property', array('uix_hideNodeLastPost', ), false))) {
		$__finalCompiled .= '
		display: flex;
		';
	}
	$__finalCompiled .= '
		// align-items: center;
		width: auto;
		// this gives an equivalent of medium padding between main and extra, with main still having large
		margin-top: (@xf-paddingMedium - @xf-paddingLarge);
		padding-top: 0;
	}

	.node-extra-row
	{
		display: inline-block;
		vertical-align: top;
		max-width: 100%;
	}

	.node-extra-icon
	{
		display: none;
	}

	.node-description,
	.node-subNodesFlat
	{
		display: none;
	}
}

@media (max-width: @xf-responsiveNarrow)
{
	.node-subNodeMenu
	{
		// display: none;
	}
}

.subNodeLink
{
	&:before,
	.subNodeLink-icon
	{
		display: inline-block;
		width: 1.28571429em;
		margin-right: .3em;
		text-decoration: none;
		text-align: center;

		color: @xf-textColorMuted;
	}

	&:before
	{
		.m-faBase();
	}

	&:hover:before
	{
		&:before,
		.subNodeLink-icon
		{
			text-decoration: none;
		}
	}

	&.subNodeLink--unread
	{
		font-weight: @xf-fontWeightHeavy;

		&:before,
		.subNodeLink-icon
		{
			color: @xf-nodeIconUnreadColor;
		}
	}

	/*
	&.subNodeLink--forum .subNodeLink-icon:before {
		.m-faContent(@fa-var-comments);
		';
	if ($__templater->func('property', array('uix_glyphForumIcon', ), false)) {
		$__finalCompiled .= '
			content: \'@xf-uix_glyphForumIcon\';		
		';
	}
	$__finalCompiled .= '
	}

	&.subNodeLink--category:before
	{
		.m-faContent(@fa-var-folder);
		';
	if ($__templater->func('property', array('uix_glyphCategoryIcon', ), false)) {
		$__finalCompiled .= '
			content: \'@xf-uix_glyphCategoryIcon\';		
		';
	}
	$__finalCompiled .= '
	}

	&.subNodeLink--page:before
	{
		.m-faContent(@fa-var-file-alt);
		';
	if ($__templater->func('property', array('uix_glyphPageIcon', ), false)) {
		$__finalCompiled .= '
			content: \'@xf-uix_glyphPageIcon\';		
		';
	}
	$__finalCompiled .= '
	}

	&.subNodeLink--link:before
	{
		.m-faContent(@fa-var-link);
		';
	if ($__templater->func('property', array('uix_glyphLinkIcon', ), false)) {
		$__finalCompiled .= '
			content: \'@xf-uix_glyphLinkIcon\';		
		';
	}
	$__finalCompiled .= '
	}

	&.subNodeLink--search::before
	{
		.m-faContent(@fa-var-search);
	}*/
}

.node-subNodeFlatList
{
	.m-listPlain();
	.m-clearFix();

	> li
	{
		display: inline-block;
		margin-right: 1em;


		&:last-child
		{
			margin-right: 0;
		}

		a {
			.xf-uix_subForumTitle();
		}
	}

	ol,
	ul,
	.node-subNodes
	{
		display: none;
	}
}

.subNodeMenu
{
	.m-listPlain();

	ol,
	ul
	{
		.m-listPlain();
	}

	.subNodeLink
	{
		display: block;
		padding: (@xf-blockPaddingV / 2) @xf-blockPaddingH;
		text-decoration: none;
		cursor: pointer;

		&:hover
		{
			text-decoration: none;
			background: @xf-contentHighlightBg;
		}
	}

	li li .subNodeLink { padding-left: 1.5em; }
	li li li .subNodeLink { padding-left: 3em; }
	li li li li .subNodeLink { padding-left: 4.5em; }
	li li li li li .subNodeLink { padding-left: 6em; }
	li li li li li li .subNodeLink { padding-left: 7.5em; }
}';
	return $__finalCompiled;
}
);