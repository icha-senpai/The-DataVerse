<?php
// FROM HASH: f48abe4649c1627872e84089a5c27dd7
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '.uix_fabBar {
	';
	if ($__templater->func('property', array('uix_fab', ), false) == 'never') {
		$__finalCompiled .= '
	@media (max-width: @xf-responsiveNarrow) {
		.uix_hasBottomTabs & {margin-bottom: calc(60px - @xf-paddingLarge);}
	}
	';
	}
	$__finalCompiled .= '

	.uix_hasBottomTabs & {
		@media (max-width: @xf-responsiveNarrow) {
			.u-scrollButtons:last-child {
				margin-bottom: (@xf-paddingLarge + 30px);
			}
		}		
	}

	.uix_editor--focused & {
		display: none;
	}

	&--mirror {
		visibility: hidden;
		position: static;
		padding-top: calc(@xf-paddingLarge * 2);
		background-color: @xf-uix_fabBarBackground;
		.p-title-pageAction{ padding-top: 0;}
		';
	if ($__templater->func('property', array('uix_fab', ), false) == 'mobile') {
		$__finalCompiled .= '
		@media (min-width: @xf-uix_fabVw) {
			display: none;
		}
		';
	}
	$__finalCompiled .= '
	}

	';
	if (!$__templater->func('property', array('uix_fabScroll', ), false)) {
		$__finalCompiled .= '
	// background-color: @xf-uix_fabBarBackground;
	// height: 60px;
	padding: @xf-paddingLarge 0;
	';
	}
	$__finalCompiled .= '

	';
	if ($__templater->func('property', array('uix_fab', ), false) == 'mobile') {
		$__finalCompiled .= '
	@media (min-width: @xf-uix_fabVw) {
		.p-title-pageAction{ display: none;}
	}
	';
	}
	$__finalCompiled .= '

	display: flex;
	flex-direction: column;
	align-items: flex-end;
	position: fixed;
	bottom: 0;
	left: 0;
	right: 0;
	padding: @xf-paddingLarge;
	z-index: 150;
	pointer-events: none;

	.u-scrollButtons {position: static;}

	.p-title-pageAction {
		.uix_hasBottomTabs & {margin-bottom: -30px;}
		margin-bottom: calc(-60px - @xf-paddingLarge);
		transition: ease-in .2s margin-bottom;
		z-index: 5;
		padding-top: @xf-paddingLarge;

		.button {
			border-radius: 100%;
			height: 60px;
			width: 60px;
			padding: 0;
			font-size: 0;
			display: none;
			align-items: center;
			justify-content: center;
			box-shadow: @xf-uix_elevation2;
			background: @xf-buttonCta--background-color;
			color: @xf-buttonCta--color;

			&:last-child {display: flex;}

			&:not(.button--icon) {display: none;}
			
			i {
				display: flex;
			}

			svg {
				font-size: @xf-uix_iconSizeLarge; 
				margin: 0;
				width: 1em;
			}
			
			.button-text {
				//display: none;
				
				&:before {
					margin: 0;
				}
			}
		}
	}

	.u-scrollButtons {pointer-events:auto;}

	&.uix_fabBar--active .p-title-pageAction {
		margin-bottom: 0;
		pointer-events: auto;
		.uix_hasBottomTabs & {
			@media (max-width: @xf-responsiveNarrow) {
				margin-bottom: (@xf-paddingLarge + 30px);
			}
		}
	}
}

#uix_jumpToFixed {
	font-size: 24px;
	color: #FFF;
	background-color: @xf-uix_secondaryColor;
	padding: 8px;
	margin: 16px;
	border-radius: 2px;
	position: fixed;
	z-index: 1;
	transition: opacity 0.4s;
	display: block;
	padding: 0;
	bottom: 0;
	right: 0;
	left: auto;
}

#uix_jumpToFixed a:first-child {
	padding-bottom: 4px;
}

#uix_jumpToFixed a {
	color: inherit;
	display: block;
	padding: 8px;
}

#uix_jumpToFixed a:last-child {
	padding-top: 4px;
}';
	return $__finalCompiled;
}
);