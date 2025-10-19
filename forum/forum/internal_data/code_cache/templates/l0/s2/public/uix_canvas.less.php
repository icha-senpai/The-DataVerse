<?php
// FROM HASH: 2d07c013788ac2f5678fa7fafab7d959
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '.uix_canvas__tabs {
	display: flex;
	justify-content: space-around;
	align-items: center;
}

.uix_canvasTab {
	display: inline-block;
}

.js-visitorTabPanel {
	.menu-row--highlighted, .menu-separator--hard {display: none;}
}

.sidePanel__tabPanels {
	position: relative;
	
	.menu-row, .menu-footer, .menu-linkRow {
		padding-left: @xf-paddingLarge;
		padding-right: @xf-paddingLarge;
	}
}

.sidePanel__tabPanel {
	height: 0;
	overflow-x: hidden;
	touch-action: pan-y;

	-moz-transition: -moz-transform 0.3s cubic-bezier(0.22, 0.61, 0.36, 1);
	-o-transition: transform 0.3s cubic-bezier(0.22, 0.61, 0.36, 1);
	-o-transition: -o-transform 0.3s cubic-bezier(0.22, 0.61, 0.36, 1);
	-webkit-transition: -webkit-transform 0.3s cubic-bezier(0.22, 0.61, 0.36, 1);
	transition: -webkit-transform 0.3s cubic-bezier(0.22, 0.61, 0.36, 1);
	transition: transform 0.3s cubic-bezier(0.22, 0.61, 0.36, 1);

	// float: left;
	width: 100%;
	// position: absolute;
	top: 0;
}

.sidePanel__tabPanel.is-left {
	transform: translate3d(-300px, 0, 0);
	overflow-y: hidden;
}

.sidePanel__tabPanel.is-right {
	transform: translate3d(300px, 0, 0);
}

.sidePanel__tabPanel.is-active {
	transform: translate3d(0, 0, 0);
	display: block;
	height: auto;
	overflow-y: auto;
}

.sidePanel--visitor .sidePanel__tabPanel.is-active {
	margin-bottom: 50px;
}

.sidePanel__tabPanel.is-hidden {
	visibility: hidden;
}

.sidePanel__tabs {
	display: -ms-flexbox;
	display: -webkit-flex;
	display: flex;
	flex: 0 0 auto;
	padding: 0;
	margin: 0;

	-webkit-justify-content: space-between;
	-ms-flex-pack: justify;
	justify-content: space-between;
}

.sidePanel__tabs li {
	max-width: 50%;
	-webkit-flex: 1 1 auto;
	-ms-flex: 1 1 auto;
	flex: 1 1 auto;
	color: rgba(255,255,255,.7);
	list-style: none;
}

a.sidePanel__tab {
	font-size: 18px;
	color: rgba(255, 255, 255, 0.75);
	background-color: @xf-uix_primaryColor;
	padding-right: 10px;
	padding-left: 10px;
	border-width: 0;
	border-right-style: solid;
	border-right-color: rgba(255,255,255,.1);
	display: block;
	cursor: pointer;
	text-align: center;
	line-height:50px;
	white-space: nowrap;
	text-overflow: ellipsis;
	overflow: hidden;
	height: 50px;
}

a.sidePanel__tab:hover {color: #fff; text-decoration: none;}

a.sidePanel__tab.sidePanel__tab--active {
	color: #fff;
	box-shadow: inset 0 -3px rgba(0,0,0,.2);
}';
	return $__finalCompiled;
}
);