<?php
// FROM HASH: ca5cd4a796fb3e8b003dc433e84bc392
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '// media

.itemList-itemOverlayTop.iconic--noLabel {
	width: 23px; height: 23px;
	top: @xf-paddingMedium;
	left: @xf-paddingMedium;
}

// feeds

.audfeeds_newsFeedItem .audfeeds_attachLink {
	margin-top: 0;
	margin-bottom: @xf-messagePadding;
}

.uix_fabBar .button--cta.audfeeds_createButton--dropdown {display: none;}

.uix_fabBar .p-title-pageAction {
	.button.button--icon--add[data-target] {display: inline-flex;}
	.js-unlimitedScrollStopTrigger {display: none !important;}
}

// Featured Threads

.message.thfeature {
	.message-body {padding: 0;}	
	.message-attribution {margin-bottom: 0;}	

	.message-footer {padding-top: @xf-messagePadding;}
}

// Nodes

';
	if ($__templater->func('property', array('th_enableGrid_nodes', ), false)) {
		$__finalCompiled .= '

.node + .node {border: none;}

.thNodes__nodeList.block .block-container .th_nodes--below-lg .node-extra {padding-top: 0;}

.thNodes__nodeList.block .block-container .node-body {
	border: none;
	box-shadow: @xf-uix_elevation1;

	.th_node--hasBackground& {
		&:hover {
			.xf-uix_nodeBodyHover();
		}		
	}
}

';
	}
	$__finalCompiled .= '

// XenPorta

.porta-article-item .block-body.message-inner {display: flex;}

.porta-articles-above-full {margin-bottom: @xf-elementSpacer;}

// resource manager

.resourceBody .actionBar {
	padding: 0;
	margin: 0;
}

.resourceBody-main .bbWrapper {
	margin-bottom: @xf-messagePadding;
}

// XFMG

.xfmgInfoBlock .actionBar .actionBar-set {
	margin-top: 0;
}

// post comments

.block--messages .message .thpostcomments_commentsContainer .message {

	.message-actionBar {
		padding-top: 0;
		border-top: 0;
	}

	.message-attribution {
		padding-top: 0;
		padding-bottom: @xf-paddingSmall;
	}
}

// achivements

.memberHeader-actionTop .profile-achievement-showcase {
	@media (max-width: @xf-responsiveMedium) {
		margin: 0;
	}
}';
	return $__finalCompiled;
}
);