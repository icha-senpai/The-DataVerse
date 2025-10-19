<?php
// FROM HASH: 4601de396534cca6a35eb6d4ce6ab0fe
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '.media
{
	position: relative;
	margin-bottom: 10px;
}


@_buttonWidth: 30px;
@_buttonHeight: 50px;

.media-button
{
	position: absolute;
	top: calc(~\'50% - \'(@_buttonHeight / 2 + 2));
	z-index: @zIndex-1;

	width: @_buttonWidth;
	height: @_buttonHeight;

	background: fade(mix(@xf-paletteNeutral2, @xf-paletteNeutral3), 70%);
	border-radius: @xf-borderRadiusMedium;

	opacity: 0.2;
	.m-transition(opacity);

	cursor: pointer;

	.has-touchevents &,
	.media:hover &
	{
		opacity: 0.6;
	}

	&&:hover
	{
		text-decoration: none;
		opacity: 1;
	}

	.media-button-icon
	{
		position: absolute;
		top: 50%;
		left: 50%;
		transform: translate(-50%, -50%);

		color: #FFF;
		.m-textOutline(white, black);

		.m-faBase();
		font-size: 1.75em;
	}

	&.media-button--next
	{
		right: 5px;

		.media-button-icon
		{
			&:before
			{
				.m-faContent(@fa-var-chevron-right, 1.1em, ltr); // making this wider to make the button more square
				.m-faContent(@fa-var-chevron-left, 1.1em, rtl);
			}
		}
	}

	&.media-button--prev
	{
		left: 5px;

		.media-button-icon
		{
			&:before
			{
				.m-faContent(@fa-var-chevron-left, 1.1em, ltr);
				.m-faContent(@fa-var-chevron-right, 1.1em, rtl);
			}
		}
	}
}

.media-container
{
	display: flex;
	justify-content: center;
	align-items: center;

	border: 1px solid transparent;

	min-height: @_buttonHeight;

	img
	{
		//max-width: 100%;
		max-height: 80vh;
		//width: auto;
		//height: auto;
		object-fit: contain;
	}

	video
	{
		width: 100%;

		&[data-video-type="audio"]
		{
			&[poster=""]
			{
				// slightly arbitrary but reduces wasted space and ensures nav icons not covered
				max-height: 180px;
				display: block;
				padding: @xf-paddingMedium;
			}
		}
	}

	.bbWrapper
	{
		width: 100%;
		text-align: center;

		>:first-child
		{
			margin-left: auto;
			margin-right: auto;
		}
	}

	.bbMediaWrapper
	{
		width: 100%;
		text-align: center; // helps center some embeds

		.bbMediaWrapper-inner
		{
			&.bbMediaWrapper-inner--thumbnail
			{
				padding-bottom: xf-option(\'xfmgThumbnailDimensions.width\', px);
			}
		}
	}

	.fb-video
	{
		iframe
		{
			background-color: @xf-contentBg;
		}
	}

	.fb_iframe_widget
	{
		margin-left: auto;
		margin-right: auto;
	}
}

@media (max-width: @xf-responsiveEdgeSpacerRemoval)
{
	.media-container
	{
		margin-left: (@xf-pageEdgeSpacer * -.5);
		margin-right: (@xf-pageEdgeSpacer * -.5);
	}
}

' . $__templater->includeTemplate('xfmg_media_note.less', $__vars) . '
' . $__templater->includeTemplate('xfmg_cropper.less', $__vars) . '
' . $__templater->includeTemplate('xfmg_item_view.less', $__vars);
	return $__finalCompiled;
}
);