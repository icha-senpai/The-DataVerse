<?php
// FROM HASH: 87b5ac214d04359629f054a31d2b0810
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '// ################################ CATEGORY LIST #######################

@_categoryListTogglerWidth: 1em;
@_categoryListTogglerPaddingH: (@xf-blockPaddingH / 2);

.categoryList
{
	display: none;
	.m-listPlain();

	&.is-active
	{
		display: block;
	}
}

.categoryList-item
{
	padding: 0;
	text-decoration: none;
	font-size: @xf-fontSizeNormal;

	&.categoryList-item--small
	{
		font-size: @xf-fontSizeSmall;
	}

	.categoryList
	{
		padding-left: @xf-paddingLarge;
	}
}

.categoryList-itemDesc
{
	display: block;
	font-size: @xf-fontSizeSmaller;
	font-weight: @xf-fontWeightNormal;
	color: @xf-textColorMuted;
	margin-top: -@xf-blockPaddingV;

	.m-overflowEllipsis();
}

.categoryList-header
{
	padding: @xf-blockPaddingV 0;
	margin: 0;
	color: @xf-textColorFeature;
	text-decoration: none;
	font-weight: @xf-fontWeightHeavy;

	&.categoryList-header--muted
	{
		color: @xf-textColorMuted;
	}

	.m-clearFix();
	.m-hiddenLinks();
}

.categoryList-itemRow
{
	display: flex;
	min-width: 0;
	align-items: center;
}

.categoryList-link
{
	display: block;
	flex-grow: 1;
	padding: (@xf-blockPaddingV / 2) @xf-blockPaddingH;
	text-decoration: none;

	.m-overflowEllipsis();

	&:hover
	{
		text-decoration: none;
	}

	&.is-selected
	{
		font-weight: @xf-fontWeightHeavy;
	}

	.categoryList-toggler + &,
	.categoryList-togglerSpacer + &
	{
		padding-left: 0;
	}
}

.categoryList-label
{
	margin-left: auto;
	align-self: center;
	padding-right: @_categoryListTogglerPaddingH;
}

.categoryList-toggler
{
	display: inline-block;
	padding: (@xf-blockPaddingV / 2) @_categoryListTogglerPaddingH;
	text-decoration: none;
	flex-grow: 0;
	line-height: 1;

	&:hover
	{
		text-decoration: none;
	}

	&:after
	{
		.m-faBase();
		// font-size: 80%;
		.m-faContent(@fa-var-chevron-down, @_categoryListTogglerWidth);
	}

	&.is-active:after
	{
		.m-faContent(@fa-var-chevron-up, @_categoryListTogglerWidth);
	}
}

.categoryList-togglerSpacer
{
	display: inline-block;
	visibility: hidden;
	padding: (@xf-blockPaddingV / 2) (@xf-blockPaddingH / 2);

	&:after
	{
		.m-faBase();
		// font-size: 80%;
		.m-faContent(@fa-var-chevron-down, @_categoryListTogglerWidth);
	}
}';
	return $__finalCompiled;
}
);