<?php
// FROM HASH: 7298896b5902d1e849aa244a5d2406a1
return array(
'macros' => array('news_feed_media' => array(
'extends' => 'audfeeds_newsFeedItem_macros::newsFeedItem',
'extensions' => array('attribution_extras' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
		$__finalCompiled .= '
		<script>
			var initFeeds = function() {
			var commentLightboxActions = document.querySelectorAll(\'.actionBar-action--commentLightbox\');
			var lightboxTriggerButtons = document.querySelectorAll(\'.js-audfeeds_lightboxTrigger\');

			var commentLightboxClickHandler = function(e) {
				e.preventDefault();
				th_xfmgCommentClick = true;
				var messageMain = this.closest(\'.message-main\');
				var lbImage = messageMain.querySelector(\'.js-lbImage\');
				if (lbImage) {
					lbImage.click();
				}
			};

			var lightboxTriggerClickHandler = function(e) {
				e.preventDefault();
				var messageMain = this.closest(\'.message-main\');
				var lbImage = messageMain.querySelector(\'.js-lbImage\');
				if (lbImage) {
					lbImage.click();
				}
			};

			commentLightboxActions.forEach(function(action) {
				action.addEventListener(\'click\', commentLightboxClickHandler);
			});

			lightboxTriggerButtons.forEach(function(button) {
				button.addEventListener(\'click\', lightboxTriggerClickHandler);
			});
		};

		if (document.readyState === \'complete\') {
			initFeeds();
		} else {
			document.addEventListener(\'DOMContentLoaded\', initFeeds);
		}
		</script>
		' . '
		';
	$__templater->inlineJs('
			if (!!initFeeds) {
				if (document.readyState === \'complete\') {
					initFeeds();
				}
			}
		', true);
	$__finalCompiled .= '
		';
	$__compilerTemp1 = '';
	$__compilerTemp1 .= '
					';
	if ($__vars['content']['category_id']) {
		$__compilerTemp1 .= '
						<a href="' . $__templater->func('link', array('media/categories', $__vars['content']['Category'], ), true) . '">' . $__templater->escape($__vars['content']['Category']['title']) . '</a>
					';
	}
	$__compilerTemp1 .= '
					<li>' . $__templater->escape($__vars['content']['comment_count']) . ' ' . 'Comments' . '</li>
				';
	if (strlen(trim($__compilerTemp1)) > 0) {
		$__finalCompiled .= '
			<ul class="listInline listInline--bullet">
				' . $__compilerTemp1 . '
			</ul>
		';
	}
	$__finalCompiled .= '
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
		'content' => $__vars['content'],
		'class' => 'bookmarkLink--highlightable',
		'confirmUrl' => $__templater->func('link', array('media/bookmark', $__vars['content'], ), false),
		'showText' => false,
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
		';
	if ($__vars['content']['description']) {
		$__finalCompiled .= '
			' . $__templater->escape($__vars['content']['description']) . '
			<br />
			<br />
		';
	}
	$__finalCompiled .= '

		<a href="' . $__templater->func('link', array('media', $__vars['content'], ), true) . '" class="js-lbImage"
		   data-src="' . $__templater->escape($__vars['content']['lightbox_src']) . '"
		   data-type="' . $__templater->escape($__vars['content']['lightbox_type']) . '"
		   data-lb-type-override="' . ((($__vars['content']['lightbox_type'] == 'ajax')) ? 'video' : '') . '"
		   data-lb-sidebar="1"
		   data-lb-caption-desc="' . $__templater->func('snippet', array($__vars['content']['description'], 100, array('stripBbCode' => true, ), ), true) . '"
		   data-lb-caption-href="' . $__templater->func('link', array('media', $__vars['content'], ), true) . '">
			' . $__templater->callMacro(null, 'xfmg_media_view_macros::media_content', array(
		'mediaItem' => $__vars['content'],
	), $__vars) . '
		</a>
	';
	return $__finalCompiled;
},
'after_content' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
		$__finalCompiled .= '
		<div class="reactionsBar js-reactionsList' . $__templater->escape($__vars['content']['media_id']) . ' ' . ($__vars['content']['reactions'] ? 'is-active' : '') . '">
			' . $__templater->func('reactions', array($__vars['content'], 'media/reactions', array())) . '
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
		'content' => $__vars['content'],
		'link' => 'media/react',
		'list' => '.js-reactionsList' . $__vars['content']['media_id'],
	))) . '
									';
	if ($__templater->method($__vars['content'], 'canAddComment', array())) {
		$__compilerTemp2 .= '
										<a href="' . $__templater->func('link', array('media', $__vars['content'], ), true) . '" class="actionBar-action actionBar-action--comment">
											' . 'Comment' . '
										</a>
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
									<span class="actionBar-action actionBar-action--inlineMod">
										' . $__templater->callMacro('xfmg_media_list_macros', 'media_list_item_inline_mod', array(
		'mediaItem' => $__vars['content'],
		'forceInlineMod' => $__vars['forceInlineMod'],
	), $__vars) . '
									</span>
									
									';
	if ($__templater->method($__vars['content'], 'canReport', array())) {
		$__compilerTemp3 .= '
										<a href="' . $__templater->func('link', array('media/report', $__vars['content'], ), true) . '"
										   class="actionBar-action actionBar-action--report"
										   data-xf-click="overlay">' . 'Report' . '</a>
									';
	}
	$__compilerTemp3 .= '

									';
	$__vars['hasActionBarMenu'] = false;
	$__compilerTemp3 .= '
									';
	if ($__templater->method($__vars['content'], 'canEdit', array())) {
		$__compilerTemp3 .= '
										<a href="' . $__templater->func('link', array('media/edit', $__vars['content'], ), true) . '"
										   class="actionBar-action actionBar-action--edit actionBar-action--menuItem"
										   data-xf-click="overlay">' . 'Edit' . '</a>
										';
		$__vars['hasActionBarMenu'] = true;
		$__compilerTemp3 .= '
									';
	}
	$__compilerTemp3 .= '
									';
	if ($__templater->method($__vars['content'], 'canDelete', array())) {
		$__compilerTemp3 .= '
										<a href="' . $__templater->func('link', array('media/delete', $__vars['content'], ), true) . '"
										   class="actionBar-action actionBar-action--delete actionBar-action--menuItem"
										   data-xf-click="overlay">' . 'Delete' . '</a>
										';
		$__vars['hasActionBarMenu'] = true;
		$__compilerTemp3 .= '
									';
	}
	$__compilerTemp3 .= '
									';
	if ($__templater->method($__vars['content'], 'canCleanSpam', array())) {
		$__compilerTemp3 .= '
										<a href="' . $__templater->func('link', array('spam-cleaner', $__vars['content'], ), true) . '"
										   class="actionBar-action actionBar-action--spam actionBar-action--menuItem"
										   data-xf-click="overlay">' . 'Spam' . '</a>
										';
		$__vars['hasActionBarMenu'] = true;
		$__compilerTemp3 .= '
									';
	}
	$__compilerTemp3 .= '
									';
	if ($__templater->method($__vars['xf']['visitor'], 'canViewIps', array()) AND $__vars['content']['ip_id']) {
		$__compilerTemp3 .= '
										<a href="' . $__templater->func('link', array('media/ip', $__vars['content'], ), true) . '"
										   class="actionBar-action actionBar-action--ip actionBar-action--menuItem"
										   data-xf-click="overlay">' . 'IP' . '</a>
										';
		$__vars['hasActionBarMenu'] = true;
		$__compilerTemp3 .= '
									';
	}
	$__compilerTemp3 .= '
									';
	if ($__templater->method($__vars['content'], 'canWarn', array())) {
		$__compilerTemp3 .= '
										<a href="' . $__templater->func('link', array('media/warn', $__vars['content'], ), true) . '"
										   class="actionBar-action actionBar-action--warn actionBar-action--menuItem">' . 'Warn' . '</a>
										';
		$__vars['hasActionBarMenu'] = true;
		$__compilerTemp3 .= '
										';
	} else if ($__vars['content']['warning_id'] AND $__templater->method($__vars['xf']['visitor'], 'canViewWarnings', array())) {
		$__compilerTemp3 .= '
										<a href="' . $__templater->func('link', array('warnings', array('warning_id' => $__vars['content']['warning_id'], ), ), true) . '"
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
	<a href="' . $__templater->func('link', array('media', $__vars['content'], ), true) . '" class="js-audfeeds_lightboxTrigger">
		' . $__templater->escape($__vars['content']['title']) . '
	</a>
');
	$__finalCompiled .= '

' . $__templater->callMacro(null, 'news_feed_media', array(
		'contentType' => 'media',
		'feedTitle' => $__vars['feedTitle'],
		'content' => $__vars['content'],
		'date' => $__vars['content']['media_date'],
	), $__vars) . '

';
	return $__finalCompiled;
}
);