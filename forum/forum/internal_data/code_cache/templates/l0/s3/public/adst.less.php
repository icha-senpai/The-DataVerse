<?php
// FROM HASH: 41d8a724b206d8e371917500ef52a61b
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '.tourStep--disabledTarget {
	pointer-events: none !important;

	* {
		pointer-events: none !important;
	}
}

.stepType(@text, @background, @featureColor, @featureBackground) {
	&.shepherd-has-title {
		.shepherd-content {
			.shepherd-header {
				background: @featureBackground;

				.shepherd-title {
					color: @featureColor;
				}

				.shepherd-cancel-icon {
					color: @featureColor;
				}
			}
			.shepherd-text {
				padding-top: 10px;
			}
		}
	}

	&.tourStep--noTitle {
		.shepherd-content {
			.shepherd-header {
				.shepherd-cancel-icon {
					color: @text;
				}
			}
			
			.shepherd-text {
				padding-right: 40px;
			}
		}
	}

	.shepherd-arrow:before {
		background: @background;
	}

	background: @background;
	color: @text;

	.shepherd-footer {
		.shepherd-button {
			background: @featureBackground;
			.m-buttonBorderColorVariation(@featureBackground);
			color: @featureColor;

			&:hover, &:active {
				background: xf-intensify(@featureBackground, 5%);
			}
		}
	}
}

.shepherd-element.tourStep {
	&--noTitle {
		.shepherd-header {
			margin-bottom: -30px;
		}
	}

	&--noButtons {
		.shepherd-footer {
			display: none;
		}

		.shepherd-text {
			margin-bottom: 5px;
		}
	}

	&--default {
		.stepType(@xf-textColor, @xf-contentBg, @xf-chromeTextColor, @xf-uix_primaryColor);
	}

	&--highlight {
		.stepType(#fff, @xf-uix_primaryColor, #fff, @xf-uix_primaryColor);
	}

	&--success {
		.stepType(@xf-successColor , @xf-successBg, @xf-contentBg, @xf-successFeatureColor);
	}

	&--warning {
		.stepType(@xf-warningColor , @xf-warningBg, @xf-warningColor, @xf-warningFeatureColor);
	}

	&--error {
		.stepType(@xf-errorColor , @xf-errorBg, @xf-contentBg, @xf-errorFeatureColor);
	}
}

.shepherd-element {
	visibility: hidden;
	opacity: 0;
	transition: opacity .3s, visibility .3s;
	.xf-adst_shepherdElement();

	&.shepherd-enabled {
		opacity: 1;
		visibility: visible;
	}

	&[data-popper-reference-hidden]:not(.shepherd-centered) {
		opacity: 0;
		pointer-events: none;
		visibility: hidden;
	}
}

.shepherd-element,
.shepherd-element *,
.shepherd-element :after,
.shepherd-element :before {
	box-sizing: border-box
}

.shepherd-arrow,
.shepherd-arrow:before {
	position: absolute;
	width: 16px;
	height: 16px;
	z-index: -1
}

.shepherd-arrow:before {
	content: "";
	transform: rotate(45deg);
	background: @xf-contentBg;
}

.shepherd-element {
	&[data-popper-placement^=top] > .shepherd-arrow {
		bottom: -8px
	}
	&[data-popper-placement^=bottom] > .shepherd-arrow {
		top: -8px
	}
	&[data-popper-placement^=left] > .shepherd-arrow {
		right: -8px
	}
	&[data-popper-placement^=right] > .shepherd-arrow {
		left: -8px
	}

	.shepherd-centered > .shepherd-arrow {
		opacity: 0
	}

	.shepherd-has-title[data-popper-placement^=bottom] > .shepherd-arrow:before {
		background-color: @xf-blockTabHeaderTextColor;
	}
}

.shepherd-target-click-disabled.shepherd-enabled.shepherd-target,
.shepherd-target-click-disabled.shepherd-enabled.shepherd-target * {
	pointer-events: none
}

.shepherd-modal-overlay-container {
	.xf-adst_shepherdOverlayContainer();

	&.shepherd-modal-is-visible {
		.xf-adst_shepherdOverlayContainerVisible();
	}

	&.shepherd-modal-is-visible path {
		pointer-events: all;
	}
}

.shepherd-content {
	.xf-adst_shepherdContent();
}

.shepherd-footer {
	.xf-adst_shepherdFooter();

	&.shepherd-button:last-child {
		margin-right: 0;
	}
}

.shepherd-header {
	.xf-adst_shepherdHeader();
}

.shepherd-has-title .shepherd-content .shepherd-header {
	.xf-adst_shepherdHeaderActive();
}

.shepherd-text {
	.xf-adst_shepherdText();

	p {
		margin-top: 0;

		&:last-child {
			margin-bottom: 0;
		}
	}
}

.shepherd-button {
	.m-buttonBase();
	.xf-buttonDefault();
	.m-buttonBlockColorVariationSimple(xf-default(@xf-buttonDefault--background-color, transparent));

	&:not(.shepherd-button-secondary) {
		.xf-buttonPrimary();
		.m-buttonBlockColorVariationSimple(xf-default(@xf-buttonPrimary--background-color, transparent));
	}

	&:disabled {
		cursor: not-allowed;
		.xf-buttonDisabled();
		.m-buttonBorderColorVariation(xf-default(@xf-buttonDisabled--background-color, transparent));

		&:hover,
		&:active,
		&:focus
		{
			background: xf-default(@xf-buttonDisabled--background-color, transparent) !important;
		}
	}
}

.shepherd-cancel-icon {
	.xf-adst_shepherdCancel();

	&:hover {
		.xf-adst_shepherdCancelHover();
	}
}

.shepherd-has-title .shepherd-content .shepherd-cancel-icon {
	.xf-adst_shepherdCancel();

	&:hover {
		.xf-adst_shepherdCancelHover();
	}
}

.shepherd-title {
	.xf-adst_shepherdTitle();
}

.shepherd-modal-overlay-container {fill: #000;}';
	return $__finalCompiled;
}
);