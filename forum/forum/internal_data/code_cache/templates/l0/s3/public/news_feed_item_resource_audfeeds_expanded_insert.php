<?php
// FROM HASH: 3d054d6f794e6ef48282da48dd39d612
return array(
'macros' => array('news_feed_resource' => array(
'extends' => 'audfeeds_newsFeedItem_macros::newsFeedItem',
'arguments' => function($__templater, array $__vars) { return array(
		'description' => '',
	); },
'extensions' => array('attribution_extras' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
		$__finalCompiled .= '
		<ul class="listInline listInline--bullet">
			<li><a href="' . $__templater->func('link', array('resources/categories', $__vars['content']['Category'], ), true) . '">' . $__templater->escape($__vars['content']['Category']['title']) . '</a></li>
			<li>' . $__templater->escape($__vars['content']['download_count']) . ' ' . 'Downloads' . '</li>
			<li>' . $__templater->escape($__vars['content']['rating_count']) . ' ' . 'ratings' . '</li>
		</ul>
	';
	return $__finalCompiled;
},
'attribution_opposite' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
		$__finalCompiled .= '
		';
	$__compilerTemp1 = '';
	$__compilerTemp1 .= '
					' . $__templater->callMacro(null, 'bookmark_macros::link', array(
		'class' => 'bookmarkLink--highlightable',
		'showText' => false,
		'content' => $__vars['content'],
		'confirmUrl' => $__templater->func('link', array('resources/bookmark', $__vars['content'], ), false),
	), $__vars) . '

				';
	if (strlen(trim($__compilerTemp1)) > 0) {
		$__finalCompiled .= '
			<li>
				' . $__compilerTemp1 . '
			</li>
		';
	}
	$__finalCompiled .= '
	';
	return $__finalCompiled;
},
'content' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
		$__finalCompiled .= '
		' . $__templater->func('bb_code_type', array('audfeedsHtml', $__vars['description']['message'], 'resource_update', $__vars['description'], ), true) . '
	';
	return $__finalCompiled;
},
'after_content' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
		$__finalCompiled .= '
		<div class="reactionsBar js-reactionsList ' . ($__vars['description']['reactions'] ? 'is-active' : '') . '">
			' . $__templater->func('reactions', array($__vars['description'], 'resources/update/reactions', array())) . '
		</div>
	';
	return $__finalCompiled;
},
'footer' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
		$__finalCompiled .= '
		';
	$__compilerTemp1 = '';
	$__compilerTemp1 .= '
						';
	$__compilerTemp2 = '';
	$__compilerTemp2 .= '
									' . $__templater->func('react', array(array(
		'content' => $__vars['description'],
		'link' => 'resources/update/react',
		'list' => '< .js-resourceBody | .js-reactionsList',
	))) . '
									';
	if ($__templater->method($__vars['content'], 'canRate', array(false, )) OR $__templater->method($__vars['content'], 'canRatePreReg', array())) {
		$__compilerTemp2 .= '
										<a href="' . $__templater->func('link', array('resources/rate', $__vars['content'], ), true) . '"
										   class="actionBar-action actionBar-action--comment" data-xf-click="overlay">' . 'Leave a rating' . '</a>
									';
	}
	$__compilerTemp2 .= '
								';
	if (strlen(trim($__compilerTemp2)) > 0) {
		$__compilerTemp1 .= '
							<div class="actionBar-set actionBar-set--external">
								' . $__compilerTemp2 . '
							</div>
						';
	}
	$__compilerTemp1 .= '

						';
	$__compilerTemp3 = '';
	$__compilerTemp3 .= '
									';
	if ($__templater->method($__vars['content'], 'canUseInlineModeration', array())) {
		$__compilerTemp3 .= '
										<span class="actionBar-action actionBar-action--inlineMod">
											' . $__templater->formCheckBox(array(
			'standalone' => 'true',
		), array(array(
			'value' => $__vars['resource']['resource_id'],
			'class' => 'js-inlineModToggle',
			'data-xf-init' => 'tooltip',
			'title' => 'Select for moderation',
			'_type' => 'option',
		))) . '
										</span>
									';
	}
	$__compilerTemp3 .= '
									';
	if ($__templater->method($__vars['description'], 'canReport', array())) {
		$__compilerTemp3 .= '
										<a href="' . $__templater->func('link', array('resources/update/report', $__vars['description'], ), true) . '"
										   class="actionBar-action actionBar-action--report" data-xf-click="overlay">' . 'Report' . '</a>
									';
	}
	$__compilerTemp3 .= '

									';
	$__vars['hasActionBarMenu'] = false;
	$__compilerTemp3 .= '
									';
	if ($__templater->method($__vars['content'], 'canEdit', array())) {
		$__compilerTemp3 .= '
										<a href="' . $__templater->func('link', array('resources/edit', $__vars['content'], ), true) . '"
										   class="actionBar-action actionBar-action--edit actionBar-action--menuItem">' . 'Edit' . '</a>
										';
		$__vars['hasActionBarMenu'] = true;
		$__compilerTemp3 .= '
									';
	}
	$__compilerTemp3 .= '
									';
	if ($__vars['description']['edit_count'] AND $__templater->method($__vars['description'], 'canViewHistory', array())) {
		$__compilerTemp3 .= '
										<a href="' . $__templater->func('link', array('resources/update/history', $__vars['description'], ), true) . '"
										   class="actionBar-action actionBar-action--history actionBar-action--menuItem"
										   data-xf-click="toggle"
										   data-target="< .js-resourceBody | .js-historyTarget"
										   data-menu-closer="true">' . 'History' . '</a>

										';
		$__vars['hasActionBarMenu'] = true;
		$__compilerTemp3 .= '
									';
	}
	$__compilerTemp3 .= '
									';
	if ($__templater->method($__vars['description'], 'canDelete', array('soft', ))) {
		$__compilerTemp3 .= '
										<a href="' . $__templater->func('link', array('resources/delete', $__vars['description'], ), true) . '"
										   class="actionBar-action actionBar-action--delete actionBar-action--menuItem"
										   data-xf-click="overlay">' . 'Delete' . '</a>
										';
		$__vars['hasActionBarMenu'] = true;
		$__compilerTemp3 .= '
									';
	}
	$__compilerTemp3 .= '
									';
	if ($__templater->method($__vars['xf']['visitor'], 'canViewIps', array()) AND $__vars['description']['ip_id']) {
		$__compilerTemp3 .= '
										<a href="' . $__templater->func('link', array('resources/update/ip', $__vars['description'], ), true) . '"
										   class="actionBar-action actionBar-action--ip actionBar-action--menuItem"
										   data-xf-click="overlay">' . 'IP' . '</a>
										';
		$__vars['hasActionBarMenu'] = true;
		$__compilerTemp3 .= '
									';
	}
	$__compilerTemp3 .= '
									';
	if ($__templater->method($__vars['description'], 'canWarn', array())) {
		$__compilerTemp3 .= '
										<a href="' . $__templater->func('link', array('resources/update/warn', $__vars['description'], ), true) . '"
										   class="actionBar-action actionBar-action--warn actionBar-action--menuItem">' . 'Warn' . '</a>
										';
		$__vars['hasActionBarMenu'] = true;
		$__compilerTemp3 .= '
										';
	} else if ($__vars['description']['warning_id'] AND $__templater->method($__vars['xf']['visitor'], 'canViewWarnings', array())) {
		$__compilerTemp3 .= '
										<a href="' . $__templater->func('link', array('warnings', array('warning_id' => $__vars['description']['warning_id'], ), ), true) . '"
										   class="actionBar-action actionBar-action--warn actionBar-action--menuItem"
										   data-xf-click="overlay">' . 'View warning' . '</a>
										';
		$__vars['hasActionBarMenu'] = true;
		$__compilerTemp3 .= '
									';
	}
	$__compilerTemp3 .= '

									';
	if ($__vars['hasActionBarMenu']) {
		$__compilerTemp3 .= '
										<a class="actionBar-action actionBar-action--menuTrigger"
										   data-xf-click="menu"
										   title="' . $__templater->filter('More options', array(array('for_attr', array()),), true) . '"
										   role="button"
										   tabindex="0"
										   aria-expanded="false"
										   aria-haspopup="true">&#8226;&#8226;&#8226;</a>

										<div class="menu" data-menu="menu" aria-hidden="true" data-menu-builder="actionBar">
											<div class="menu-content">
												<h4 class="menu-header">' . 'More options' . '</h4>
												<div class="js-menuBuilderTarget"></div>
											</div>
										</div>
									';
	}
	$__compilerTemp3 .= '
								';
	if (strlen(trim($__compilerTemp3)) > 0) {
		$__compilerTemp1 .= '
							<div class="actionBar-set actionBar-set--internal">
								' . $__compilerTemp3 . '
							</div>
						';
	}
	$__compilerTemp1 .= '
					';
	if (strlen(trim($__compilerTemp1)) > 0) {
		$__finalCompiled .= '
			<div class="message-footer">
				<div class="message-actionBar actionBar">
					' . $__compilerTemp1 . '
				</div>
				' . '
			</div>
		';
	}
	$__finalCompiled .= '
	';
	return $__finalCompiled;
}),
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '

	' . $__templater->renderExtension('attribution_extras', $__vars, $__extensions) . '

	' . $__templater->renderExtension('attribution_opposite', $__vars, $__extensions) . '

	' . $__templater->renderExtension('content', $__vars, $__extensions) . '

	' . $__templater->renderExtension('after_content', $__vars, $__extensions) . '

	' . $__templater->renderExtension('footer', $__vars, $__extensions) . '
';
	return $__finalCompiled;
}
)),
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__vars['feedTitle'] = $__templater->preEscaped('
	' . '
	' . $__templater->func('prefix', array('resource', $__vars['content'], ), true) . '<a href="' . $__templater->func('link', array('resources', $__vars['content'], ), true) . '">' . $__templater->escape($__vars['content']['title']) . '</a>
');
	$__finalCompiled .= '

' . $__templater->callMacro(null, 'news_feed_resource', array(
		'contentType' => 'resource',
		'description' => $__vars['content']['Description'],
		'feedTitle' => $__vars['feedTitle'],
		'content' => $__vars['content'],
		'date' => $__vars['content']['resource_date'],
	), $__vars) . '

' . '

';
	return $__finalCompiled;
}
);