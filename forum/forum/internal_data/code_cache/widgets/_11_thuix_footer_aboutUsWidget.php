<?php

return function($__templater, array $__vars, array $__options = [])
{
	$__widget = \XF::app()->widget()->widget('thuix_footer_aboutUsWidget', $__options)->render();

	return $__widget;
};