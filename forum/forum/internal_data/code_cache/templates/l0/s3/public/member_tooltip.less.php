<?php
// FROM HASH: 971fba1df9d10267105fc357d0d70426
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '@_memberTooltip-padding: @xf-paddingMedium;
@_memberTooltip-avatarSize: @avatar-m;

.memberTooltip-header
{
	display: flex;
	padding: @_memberTooltip-padding;
	.xf-memberTooltipHeader();
}

.memberTooltip-avatar
{
	//width: (@_memberTooltip-padding * 2 + @_memberTooltip-avatarSize);
	padding-right: @_memberTooltip-padding;
}

.memberTooltip-headerInfo
{
	flex: 1;
}

.memberTooltip-name
{
	margin: 0;
	margin-top: -.15em;
	padding: 0;
	font-weight: @xf-fontWeightNormal;
	line-height: (@xf-lineHeightDefault * 0.8);
	.xf-memberTooltipName();

	.m-hiddenLinks();

	.memberTooltip-nameChangeIndicator
	{
		color: @xf-textColorMuted;
		font-size: 75%;

		&:hover
		{
			color: @xf-textColorMuted;
		}
	}

	.memberTooltip--withBanner &
	{
		.xf-memberTooltipNameBanner();

		.memberTooltip-nameChangeIndicator
		{
			color: darken(xf-default(@xf-memberTooltipNameBanner--color, white), 20%);

			&:hover
			{
				color: darken(xf-default(@xf-memberTooltipNameBanner--color, white), 20%);
			}
		}
	}
}

// See XF-197998
@_memberTooltip-textStroke: 0 #000;

.memberTooltip-textStroke()
{
	text-shadow:
		-1px -1px @_memberTooltip-textStroke,
		1px -1px @_memberTooltip-textStroke,
		-1px 1px @_memberTooltip-textStroke,
		1px 1px @_memberTooltip-textStroke;
}

.memberTooltip--withBanner
{
	.memberTooltip-nameWrapper
	{
		.username:hover
		{
			text-decoration: none;
		}
	}

	.username
	{
		.memberTooltip-textStroke();
	}

	.memberTooltip-nameChangeIndicator .fa-history
	{
		.memberTooltip-textStroke();
	}
}

.memberTooltip-headerAction
{
	float: right;
}

.memberTooltip-blurbContainer
{
	.memberTooltip--withBanner &
	{
		.xf-memberTooltipBlurbContainerBanner();

		.memberTooltip-blurb
		{
			&:first-child
			{
				margin-top: 0;
			}

			.pairs dt, a
			{
				color: darken(xf-default(@xf-memberTooltipBlurbContainerBanner--color, white), 20%);
			}
		}
	}
}

.memberTooltip-banners {
	margin: -2px;
	
	.userBanner {margin: 2px;}
}

.memberTooltip-banners,
.memberTooltip-blurb
{
	margin-top: .25em;
}

.memberTooltip-blurb
{
	font-size: @xf-fontSizeSmall;
}

.memberTooltip-stats
{
	font-size: @xf-fontSizeSmall;

	dl.pairs.pairs--rows > dt
	{
		font-size: @xf-fontSizeSmaller;
	}
	
	.pairJustifier {
		grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
		display: grid;
	}
}

.memberTooltip-info,
.memberTooltip-actions
{
	padding: @_memberTooltip-padding;
	
	.button {
		.xf-uix_buttonSmall;
	}
}

.memberTooltip-separator
{
	margin: (@xf-borderSize * -1) @_memberTooltip-padding 0;
	border: none;
	border-top: @xf-borderSize solid @xf-borderColorLight;
}

@media (max-width: @xf-responsiveNarrow)
{
	.memberTooltip-avatar
	{
		width: (@_memberTooltip-padding * 2 + @_memberTooltip-avatarSize * 2 / 3);

		.avatar
		{
			.m-avatarSize((@_memberTooltip-avatarSize * 2 / 3));
		}
	}
}';
	return $__finalCompiled;
}
);