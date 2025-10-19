<?php
// FROM HASH: cf9a29eddb81ea2c4957051f9ff4617c
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '// default

// remove share component
.p-body-pageContent > .blockMessage:last-child {display: none;}

body {
	font-size: 1.6rem;
}

// ----- typography ----- //

.uix_heading {
	font-weight: @xf-fontWeightHeavy;
	font-family: @xf-uix_headingFont;
	margin-bottom: .3em;

	&.uix_heading--h1 {
		font-size: 2.8rem;
	}
	&.uix_heading--h2 {
		font-size: 2.4rem;
	}
	&.uix_heading--h3 {
		font-size: 1.8rem;
	}
	&.uix_heading--h4 {
		font-size: 1.6rem;
	}

	&.uix_heading--centered {
		text-align: center;
	}
}

.uix_paragraph {
	font-size: 1.6rem;
	margin-bottom: .7em;

	&.uix_paragraph--noMargin {
		margin: 0;
	}
}

.uix_text {
	&.uix_text--small {
		font-size: 1.4rem;
	}
	&.uix_text--secondary {
		color: @xf-textColorDimmed;
	}
	&.uix_text--centered {
		text-align: center;
	}
}

// titlebar

.uix_titlebar {
	border-bottom: 2px solid @xf-borderColor;
	display: flex;
	justify-content: space-between;
	align-items: flex-end;
	padding-bottom: 10px;
	margin-bottom: 20px;

	.uix_heading {
		margin: 0;
	}
}

// -------- section component --------- //

.uix_section {
	padding: 30px 0;
	background: @xf-contentBg;

	@media (min-width: @xf-responsiveWide) {
		padding: 80px 0;	

		&.uix_section--small {
			padding: 45px 0;
		}
	}

	&.uix_section--noPadding {
		padding: 0;
	}

	&.uix_section--narrow .pageContent {
		max-width: 950px;
	}

	&.uix_section--secondary {
		background: xf-intensify(@xf-contentHighlightBg, 3%);
	}

	&.uix_section--emphasis {
		background-color: @xf-uix_sectionBg;
		background-size: cover;
		color: #fff;
		text-shadow: 0 0 1px #000;
	}

	.pageContent {
		.m-pageWidth();
	}

	&.uix_section--cta {

		.uix_heading {
			margin-bottom: 5px;
		}

		.button {
			margin-top: 15px;
		}
	}
}

// ------- layouts -------- //

.uix_layout {
	display: grid;
	grid-template-columns: 1fr;

	&.uix_layout--withSidebar {
		grid-gap: @xf-elementSpacer;

		@media (min-width: @xf-responsiveWide) {
			grid-template-columns: 1fr 250px;
		}
	}
}

.uix_layoutCol {
	display: grid;
	grid-gap: @xf-elementSpacer;

	&.uix_layoutCol--small {
		grid-template-columns: repeat(auto-fit, minmax(175px, 1fr));
	}

	&.uix_layoutCol--medium {
		grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
	}

	&.uix_layoutCol--large {
		grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
	}

	&.uix_layoutCol--mobileScroll {
		@media (max-width: @xf-responsiveMedium) {
			overflow-x: scroll;	
			grid-template-columns: auto;

			&.uix_layoutCol--medium > * {width: 250px;}
			&.uix_layoutCol--small > * {width: 175px;}

			> * {
				grid-row: 1;
			}
		}
	}
}

// ------- section titlebar

// hero

.uix_hero {
	display: flex;
	// flex-wrap: wrap;
	align-items: center;

	@media (max-width: @xf-responsiveWide) {
		flex-direction: column;
	}

	.uix_hero__content {
		margin-bottom: 20px;

		@media (min-width: @xf-responsiveWide) {
			flex-basis: 350px;	
			flex-grow: 1;
			margin-right: 40px;
			flex-shrink: 0;
			margin-bottom: 0;
		}
	}

	.button {
		margin-top: @xf-paddingLarge;
	}
}

// Blurb section

.uix_blurbRow {
	display: flex;
	@media (max-width: @xf-responsiveWide) {
		flex-direction: column;
	}
}

.uix_blurb {
	flex-grow: 1;
	padding: 20px @xf-paddingMedium;
	display: flex;
	align-items: center;
	flex-basis: 50%;
	@media (min-width: @xf-responsiveWide) {
		padding: 45px;
	}

	.uix_blurbIcon {
		width: 60px;
		margin-right: 15px;
		font-size: 60px;
	}
}

// Testimonials

.uix_testimonialList {
	display: flex;
	flex-wrap: wrap;
	justify-content: center;
}

.uix_testimonial {
	position: relative;
	flex-basis: 250px;
	margin: 30px 15px;

	.block-container {margin: 0;}

	.block-container {
		z-index: 1;
		position: relative;
	}

	.block-row {
		padding: 20px;
	}
}';
	return $__finalCompiled;
}
);