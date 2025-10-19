<?php
// FROM HASH: fead06340a7b51153ba96e3add151b52
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '.alert
{
	&.is-unread
	{
		.xf-contentHighlightBase();
		
		.contentRow-main {
			font-weight: @xf-fontWeightHeavy;
			
			.contentRow-minor {font-weight: @xf-fontWeightNormal;}
		}
	}
}

.alertToggler
{
	text-decoration: none !important;
	padding: @xf-paddingMedium;
	margin-right: -@xf-paddingMedium;

	.alert &
	{
		opacity: 0;
	}

	.alert:hover &,
	.has-touchevents &
	{
		opacity: 1;
	}
}

.alertToggler-icon
{
	& when (@xf-fontAwesomeWeight >= @faWeight-solid)
	{
		.m-faIcon(@fa-var-regular-circle, .75em);
	}
	& when (@xf-fontAwesomeWeight < @faWeight-solid)
	{
		.m-faIcon(@fa-var-circle, .75em);
	}

	.is-unread &
	{
		.m-faIcon(@fa-var-solid-circle, .75em);
	}
}';
	return $__finalCompiled;
}
);