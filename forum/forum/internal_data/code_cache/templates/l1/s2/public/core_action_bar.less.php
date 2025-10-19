<?php
// FROM HASH: e24dfdf1b64b5a348dc0ab9debe50589
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '.actionBar
{
	.m-clearFix();
}

.actionBar-set
{
	&.actionBar-set--internal
	{
		float: left;
		// margin-left: -3px;

		> .actionBar-action:first-child
		{
			margin-left: 0;
		}
	}

	&.actionBar-set--external
	{
		float: right;
		// margin-right: -3px;

		> .actionBar-action:last-child
		{
			margin-right: 0;
		}
	}
}

.actionBar .actionBar-action
{
	display: inline-block;
	padding: 3px;
	border: 1px solid transparent;
	border-radius: @xf-borderRadiusMedium;
	margin-left: 5px;
	.xf-uix_messageControl();

	&:hover {
		.xf-uix_messageControlHover();
	}

	&:before
	{
		.m-faBase();
		font-size: 1.1em;
		padding-right: 2px;
	}

	&.actionBar-action--menuTrigger
	{
		display: none;

		&.is-displayed
		{
			display: inline;
		}

		&:after
		{
			.m-menuGadget();
		}
	}

	&.actionBar-action--inlineMod
	{
		label
		{
			color: @xf-linkColor;
			font-size: 120%;
			// vertical-align: -2px;
		}

		input
		{
			.m-checkboxAligner();
		}
	}

	&.actionBar-action--mq
	{
		&:before { .m-faContent("@{fa-var-plus}\\20");} // plus

		&.is-selected
		{
			background-color: @xf-contentHighlightBg;
			border-color: @xf-borderColorHighlight;

			&:before { .m-faContent("@{fa-var-minus}\\20");} // minus
		}
	}

	&.actionBar-action--postLink
	{
		text-decoration: inherit !important;
		color: inherit !important;
	}

	&.actionBar-action--reply:before { .m-faContent("@{fa-var-reply}");}
	&.actionBar-action--like:before { .m-faContent("@{fa-var-thumbs-up}");}

	&.actionBar-action--reaction:not(.has-reaction) .reaction-text
	{
		color: inherit;
	}

	&.actionBar-action--view
	{
		background: @xf-contentBg;
		color: @xf-linkColor;
		.m-buttonBorderColorVariation(@xf-borderColor);
		padding-left: @xf-paddingLarge;
		padding-right: @xf-paddingLarge;

		&:hover,
		&:active,
		&:focus
		{
			text-decoration: none;
			background: @xf-contentHighlightBg;
		}
	}
	&.actionBar-action--reply:before { .m-faContent("@{fa-var-reply}\\20");} // reply
	&.actionBar-action--comment:before { .m-faContent("@{fa-var-comment}\\20");} // reply
	&.actionBar-action--like.unlike:before { .m-faContent("@{fa-var-thumbs-down}\\20");}// thumbs up
	&.actionBar-action--reaction.reaction--imageHidden i:before { .m-faContent("@{fa-var-thumbs-up}\\20");}
	&.actionBar-action--report:before { .m-faContent("@{fa-var-exclamation-circle}\\20");}
	&.actionBar-action--delete:before { .m-faContent("@{fa-var-trash}\\20");}
	&.actionBar-action--edit:before { .m-faContent("@{fa-var-edit}\\20");}
	&.actionBar-action--ip:before { .m-faContent("@{fa-var-globe}\\20");}
	&.actionBar-action--history:before { .m-faContent("@{fa-var-history}\\20");}
	&.actionBar-action--warn:before { .m-faContent("@{fa-var-exclamation-triangle}\\20");}
	&.actionBar-action--spam:before { .m-faContent("@{fa-var-exclamation-triangle}\\20");}
}

@media (max-width: @xf-responsiveMedium)
{
	.actionBar .actionBar-action
	{
		&.actionBar-action--menuItem
		{
			display: none !important;
		}

		&.actionBar-action--menuTrigger
		{
			display: inline;
		}
	}
}';
	return $__finalCompiled;
}
);