<?php
// FROM HASH: 27439376a64f6cb7f2bc4526b221867c
return array(
'macros' => array('comment_list' => array(
'arguments' => function($__templater, array $__vars) { return array(
		'comments' => '!',
		'content' => '!',
		'linkPrefix' => '!',
		'link' => '!',
		'linkParams' => array(),
		'page' => '!',
		'perPage' => '!',
		'totalItems' => '!',
		'pageParam' => 'page',
		'canInlineMod' => '!',
	); },
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '
	';
	if ($__vars['canInlineMod']) {
		$__finalCompiled .= '
		';
		$__templater->includeJs(array(
			'src' => 'xf/inline_mod.js',
			'min' => '1',
		));
		$__finalCompiled .= '
	';
	}
	$__finalCompiled .= '

	<div class="block block--messages"
		data-xf-init="' . ($__vars['canInlineMod'] ? 'inline-mod' : '') . '"
		data-type="xfmg_comment"
		data-href="' . $__templater->func('link', array('inline-mod', ), true) . '">

		<div class="block-outer">';
	$__compilerTemp1 = '';
	$__compilerTemp2 = '';
	$__compilerTemp2 .= '
							';
	if ($__vars['canInlineMod']) {
		$__compilerTemp2 .= '
								' . $__templater->callMacro(null, 'inline_mod_macros::button', array(), $__vars) . '
							';
	}
	$__compilerTemp2 .= '
						';
	if (strlen(trim($__compilerTemp2)) > 0) {
		$__compilerTemp1 .= '
				<div class="block-outer-opposite">
					<div class="buttonGroup">
						' . $__compilerTemp2 . '
					</div>
				</div>
			';
	}
	$__finalCompiled .= $__templater->func('trim', array('

			' . $__templater->func('page_nav', array(array(
		'page' => $__vars['page'],
		'total' => $__vars['totalItems'],
		'link' => $__vars['link'],
		'data' => $__vars['content'],
		'params' => $__vars['linkParams'],
		'wrapperclass' => 'block-outer-main',
		'perPage' => $__vars['perPage'],
		'pageParam' => $__vars['pageParam'],
	))) . '

			' . $__compilerTemp1 . '

		'), false) . '</div>
		<div class="block-container"
			data-xf-init="' . ($__vars['xf']['options']['selectQuotable'] ? 'select-to-quote' : '') . '"
			data-message-selector=".js-comment">

			<div class="block-body js-replyNewCommentContainer">
				';
	if (!$__templater->test($__vars['comments'], 'empty', array())) {
		$__finalCompiled .= '
					<span class="u-anchorTarget" id="comments"></span>
					';
		if ($__templater->isTraversable($__vars['comments'])) {
			foreach ($__vars['comments'] AS $__vars['comment']) {
				$__finalCompiled .= '
						';
				if ($__vars['comment']['comment_state'] == 'deleted') {
					$__finalCompiled .= '
							' . $__templater->callMacro(null, 'comment_deleted', array(
						'comment' => $__vars['comment'],
						'content' => $__vars['content'],
						'linkPrefix' => $__vars['linkPrefix'],
					), $__vars) . '
						';
				} else {
					$__finalCompiled .= '
							' . $__templater->callMacro(null, 'comment', array(
						'comment' => $__vars['comment'],
						'content' => $__vars['content'],
						'linkPrefix' => $__vars['linkPrefix'],
					), $__vars) . '
						';
				}
				$__finalCompiled .= '
					';
			}
		}
		$__finalCompiled .= '
				';
	} else {
		$__finalCompiled .= '
					<div class="blockMessage js-replyNoMessages">' . 'xfmg_there_no_comments_to_display' . '</div>
				';
	}
	$__finalCompiled .= '
			</div>
		</div>

		<div class="block-outer block-outer--after">
			' . $__templater->func('page_nav', array(array(
		'page' => $__vars['page'],
		'total' => $__vars['totalItems'],
		'link' => $__vars['link'],
		'data' => $__vars['content'],
		'params' => $__vars['linkParams'],
		'wrapperclass' => 'block-outer-main',
		'perPage' => $__vars['perPage'],
		'pageParam' => $__vars['pageParam'],
	))) . '
			' . $__templater->func('show_ignored', array(array(
		'wrapperclass' => 'block-outer-opposite',
	))) . '
		</div>
	</div>

	' . $__templater->callMacro(null, 'comment_add', array(
		'comments' => $__vars['comments'],
		'content' => $__vars['content'],
		'linkPrefix' => $__vars['linkPrefix'],
	), $__vars) . '
';
	return $__finalCompiled;
}
),
'comment' => array(
'arguments' => function($__templater, array $__vars) { return array(
		'comment' => '!',
		'content' => '!',
		'linkPrefix' => '!',
	); },
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '
	';
	$__templater->includeCss('xfmg_comment.less');
	$__finalCompiled .= '

	<article class="message message--simple message--comment' . ($__templater->method($__vars['comment'], 'isIgnored', array()) ? ' is-ignored' : '') . ' js-comment js-inlineModContainer"
		data-author="' . ($__templater->escape($__vars['comment']['User']['username']) ?: $__templater->escape($__vars['comment']['username'])) . '"
		data-content="xfmg-comment-' . $__templater->escape($__vars['comment']['comment_id']) . '">

		<span class="u-anchorTarget" id="xfmg-comment-' . $__templater->escape($__vars['comment']['comment_id']) . '"></span>

		<div class="message-inner">
			<div class="message-cell message-cell--user">
				' . $__templater->callMacro(null, 'message_macros::user_info_simple', array(
		'user' => $__vars['comment']['User'],
		'fallbackName' => $__vars['comment']['username'],
	), $__vars) . '
			</div>
			<div class="message-cell message-cell--main">
				<div class="js-quickEditTarget">
					<div class="message-content js-messageContent">
						<header class="message-attribution message-attribution--plain">
							<ul class="listInline listInline--bullet">
								<li class="message-attribution-user">
									' . $__templater->func('avatar', array($__vars['comment']['User'], 'xxs', false, array(
	))) . '
									<h4 class="attribution">' . $__templater->func('username_link', array($__vars['comment']['User'], true, array(
		'defaultname' => $__vars['comment']['username'],
	))) . '</h4>
								</li>
								<li>
									<a href="' . $__templater->func('link', array('media/comments', $__vars['comment'], ), true) . '" class="u-concealed">' . $__templater->func('date_dynamic', array($__vars['comment']['comment_date'], array(
	))) . '</a>
								</li>
								';
	if ($__vars['comment']['Rating']) {
		$__finalCompiled .= '
									<li>
										' . $__templater->callMacro(null, 'rating_macros::stars', array(
			'rating' => $__vars['comment']['Rating']['rating'],
			'class' => 'ratingStars--smaller',
		), $__vars) . '
									</li>
								';
	}
	$__finalCompiled .= '
							</ul>
						</header>

						';
	if ($__vars['comment']['comment_state'] == 'deleted') {
		$__finalCompiled .= '
							<div class="messageNotice messageNotice--deleted">
								' . $__templater->callMacro(null, 'deletion_macros::notice', array(
			'log' => $__vars['comment']['DeletionLog'],
		), $__vars) . '
							</div>
						';
	} else if ($__vars['comment']['comment_state'] == 'moderated') {
		$__finalCompiled .= '
							<div class="messageNotice messageNotice--moderated">
								' . 'This message is awaiting moderator approval, and is invisible to normal visitors.' . '
							</div>
						';
	}
	$__finalCompiled .= '
						';
	if ($__vars['comment']['warning_message']) {
		$__finalCompiled .= '
							<div class="messageNotice messageNotice--warning">
								' . $__templater->escape($__vars['comment']['warning_message']) . '
							</div>
						';
	}
	$__finalCompiled .= '
						';
	if ($__templater->method($__vars['comment'], 'isIgnored', array())) {
		$__finalCompiled .= '
							<div class="messageNotice messageNotice--ignored">
								' . 'You are ignoring content by this member.' . '
							</div>
						';
	}
	$__finalCompiled .= '

						<div class="message-userContent">

							<article class="message-body js-selectToQuote">
								' . $__templater->func('bb_code', array($__vars['comment']['message'], 'xfmg_comment', $__vars['comment'], ), true) . '
								<div class="js-selectToQuoteEnd">&nbsp;</div>
							</article>

						</div>

						';
	if ($__vars['comment']['last_edit_date']) {
		$__finalCompiled .= '
							<div class="message-lastEdit">
								';
		if ($__vars['comment']['user_id'] == $__vars['comment']['last_edit_user_id']) {
			$__finalCompiled .= '
									' . 'Last edited' . $__vars['xf']['language']['label_separator'] . ' ' . $__templater->func('date_dynamic', array($__vars['comment']['last_edit_date'], array(
			))) . '
								';
		} else {
			$__finalCompiled .= '
									' . 'Last edited by a moderator' . $__vars['xf']['language']['label_separator'] . ' ' . $__templater->func('date_dynamic', array($__vars['comment']['last_edit_date'], array(
			))) . '
								';
		}
		$__finalCompiled .= '
							</div>
						';
	}
	$__finalCompiled .= '

						' . $__templater->callMacro(null, 'message_macros::signature', array(
		'user' => $__vars['comment']['User'],
	), $__vars) . '
					</div>
					
					<div class="reactionsBar js-reactionsList ' . ($__vars['comment']['reactions'] ? 'is-active' : '') . '">
						' . $__templater->func('reactions', array($__vars['comment'], 'media/comments/reactions', array())) . '
					</div>

					<footer class="message-footer">
						';
	$__compilerTemp1 = '';
	$__compilerTemp1 .= '
									';
	$__compilerTemp2 = '';
	$__compilerTemp2 .= '
												' . $__templater->func('react', array(array(
		'content' => $__vars['comment'],
		'link' => 'media/comments/react',
		'list' => '< .js-comment | .js-reactionsList',
	))) . '
												';
	if ($__templater->method($__vars['content'], 'canReplyToComment', array())) {
		$__compilerTemp2 .= '
													';
		$__vars['quoteLink'] = $__templater->preEscaped($__templater->func('link', array($__vars['linkPrefix'] . '/comment', $__vars['content'], array('quote' => $__vars['comment']['comment_id'], ), ), true));
		$__compilerTemp2 .= '
													';
		if ($__vars['xf']['options']['multiQuote']) {
			$__compilerTemp2 .= '
														<a href="' . $__templater->escape($__vars['quoteLink']) . '"
															class="actionBar-action actionBar-action--mq u-jsOnly js-multiQuote"
															title="' . $__templater->filter('Toggle multi-quote', array(array('for_attr', array()),), true) . '"
															data-message-id="' . $__templater->escape($__vars['comment']['comment_id']) . '"
															data-mq-action="add">
															' . 'Quote' . '
														</a>
													';
		}
		$__compilerTemp2 .= '
													<a href="' . $__templater->escape($__vars['quoteLink']) . '"
														class="actionBar-action actionBar-action--reply"
														title="' . $__templater->filter('Reply, quoting this message', array(array('for_attr', array()),), true) . '"
														data-xf-click="quote"
														data-quote-href="' . $__templater->func('link', array('media/comments/quote', $__vars['comment'], ), true) . '">' . 'Reply' . '</a>
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
	if ($__templater->method($__vars['comment'], 'canUseInlineModeration', array())) {
		$__compilerTemp3 .= '
													<span class="actionBar-action actionBar-action--inlineMod">
														' . $__templater->formCheckBox(array(
			'standalone' => 'true',
		), array(array(
			'value' => $__vars['comment']['comment_id'],
			'class' => 'js-inlineModToggle',
			'data-xf-init' => 'tooltip',
			'title' => 'Select for moderation',
			'label' => 'Select for moderation',
			'hiddenlabel' => 'true',
			'_type' => 'option',
		))) . '
													</span>
												';
	}
	$__compilerTemp3 .= '
												';
	if ($__templater->method($__vars['comment'], 'canReport', array())) {
		$__compilerTemp3 .= '
													<a href="' . $__templater->func('link', array('media/comments/report', $__vars['comment'], ), true) . '" class="actionBar-action actionBar-action--report" data-xf-click="overlay" data-cache="false">' . 'Report' . '</a>
												';
	}
	$__compilerTemp3 .= '
												';
	if ($__templater->method($__vars['comment'], 'canEdit', array())) {
		$__compilerTemp3 .= '
													';
		$__templater->includeJs(array(
			'src' => 'xf/action.js, xf/message.js',
			'min' => '1',
		));
		$__compilerTemp3 .= '
													<a href="' . $__templater->func('link', array('media/comments/edit', $__vars['comment'], ), true) . '"
														class="actionBar-action actionBar-action--edit"
														data-xf-click="quick-edit"
														data-editor-target="< .js-quickEditTarget">' . 'Edit' . '</a>
												';
	}
	$__compilerTemp3 .= '
												';
	if ($__vars['comment']['edit_count'] AND $__templater->method($__vars['comment'], 'canViewHistory', array())) {
		$__compilerTemp3 .= '
													<a href="' . $__templater->func('link', array('media/comments/history', $__vars['comment'], ), true) . '" class="actionBar-action actionBar-action--history" data-xf-click="toggle" data-target="< .js-comment | .js-historyTarget">' . 'History' . '</a>
												';
	}
	$__compilerTemp3 .= '
												';
	if ($__templater->method($__vars['comment'], 'canDelete', array())) {
		$__compilerTemp3 .= '
													<a href="' . $__templater->func('link', array('media/comments/delete', $__vars['comment'], ), true) . '" class="actionBar-action actionBar-action--delete" data-xf-click="overlay">' . 'Delete' . '</a>
												';
	}
	$__compilerTemp3 .= '
												';
	if ($__templater->method($__vars['comment'], 'canCleanSpam', array())) {
		$__compilerTemp3 .= '
													<a href="' . $__templater->func('link', array('spam-cleaner', $__vars['comment'], ), true) . '" class="actionBar-action actionBar-action--spam" data-xf-click="overlay">' . 'Spam' . '</a>
												';
	}
	$__compilerTemp3 .= '
												';
	if ($__templater->method($__vars['xf']['visitor'], 'canViewIps', array()) AND $__vars['comment']['ip_id']) {
		$__compilerTemp3 .= '
													<a href="' . $__templater->func('link', array('media/comments/ip', $__vars['comment'], ), true) . '" class="actionBar-action actionBar-action--ip" data-xf-click="overlay">' . 'IP' . '</a>
												';
	}
	$__compilerTemp3 .= '
												';
	if ($__templater->method($__vars['comment'], 'canWarn', array())) {
		$__compilerTemp3 .= '
													<a href="' . $__templater->func('link', array('media/comments/warn', $__vars['comment'], ), true) . '" class="actionBar-action actionBar-action--warn">' . 'Warn' . '</a>
												';
	} else if ($__vars['comment']['warning_id'] AND $__templater->method($__vars['xf']['visitor'], 'canViewWarnings', array())) {
		$__compilerTemp3 .= '
													<a href="' . $__templater->func('link', array('warnings', array('warning_id' => $__vars['comment']['warning_id'], ), ), true) . '" class="actionBar-action actionBar-action--warn" data-xf-click="overlay">' . 'View warning' . '</a>
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
							<div class="message-actionBar actionBar">
								' . $__compilerTemp1 . '
							</div>
						';
	}
	$__finalCompiled .= '
					</footer>

					<div class="js-historyTarget toggleTarget" data-href="trigger-href"></div>
				</div>
			</div>
		</div>
	</article>
';
	return $__finalCompiled;
}
),
'comment_simple' => array(
'arguments' => function($__templater, array $__vars) { return array(
		'comment' => '!',
		'content' => '!',
	); },
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '

	<div class="contentRow">
		<div class="contentRow-figure">
			' . $__templater->func('avatar', array($__vars['comment']['User'], 'xxs', false, array(
		'defaultname' => $__vars['comment']['username'],
	))) . '
		</div>
		<div class="contentRow-main contentRow-main--close">
			<a href="' . $__templater->func('link', array('media/comments', $__vars['comment'], ), true) . '">' . $__templater->escape($__vars['content']['title']) . '</a>

			<div class="contentRow-faderContainer">
				<div class="contentRow-faderContent">
					' . $__templater->func('bb_code_snippet', array($__vars['comment']['message'], 'xfmg_comment', $__vars['comment'], 300, array('simpleUnfurl' => true, ), ), true) . '
				</div>
				<div class="contentRow-fader"></div>
			</div>

			<div class="contentRow-minor contentRow-minor--hideLinks">
				<ul class="listInline listInline--bullet">
					<li>' . $__templater->func('date_dynamic', array($__vars['comment']['comment_date'], array(
	))) . '</li>
				</ul>
			</div>
		</div>
	</div>
';
	return $__finalCompiled;
}
),
'comment_lightbox' => array(
'arguments' => function($__templater, array $__vars) { return array(
		'comment' => '!',
		'content' => '!',
		'linkPrefix' => '!',
	); },
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '
	<div class="message-responseRow">
		<div class="comment js-comment ' . ($__templater->method($__vars['comment'], 'isIgnored', array()) ? 'is-ignored' : '') . '"
			data-author="' . ($__templater->escape($__vars['comment']['User']['username']) ?: $__templater->escape($__vars['comment']['username'])) . '"
			data-content="xfmg-comment-' . $__templater->escape($__vars['comment']['comment_id']) . '">
			<div class="comment-inner">
				<span class="comment-avatar">
					' . $__templater->func('avatar', array($__vars['comment']['User'], 'xxs', false, array(
		'defaultname' => $__vars['comment']['username'],
	))) . '
				</span>
				<div class="comment-main">
					<span class="u-anchorTarget" id="xfmg-comment-' . $__templater->escape($__vars['comment']['comment_id']) . '"></span>

					<div class="js-quickEditTarget">
						<div class="comment-content">
							<div class="comment-contentWrapper">
								<ul class="listInline listInline--bullet">
									<li>' . $__templater->func('username_link', array($__vars['comment']['User'], true, array(
		'defaultname' => $__vars['comment']['username'],
		'class' => 'comment-user',
	))) . '</li>
									';
	if ($__vars['comment']['Rating']) {
		$__finalCompiled .= '
										<li>
											' . $__templater->callMacro(null, 'rating_macros::stars', array(
			'rating' => $__vars['comment']['Rating']['rating'],
			'class' => 'ratingStars--smaller',
		), $__vars) . '
										</li>
									';
	}
	$__finalCompiled .= '
								</ul>

								<article class="comment-body">' . $__templater->func('bb_code', array($__vars['comment']['message'], 'xfmg_comment', $__vars['comment'], ), true) . '</article>
							</div>
						</div>

						<footer class="comment-footer">
							<div class="comment-actionBar actionBar">
								';
	$__compilerTemp1 = '';
	$__compilerTemp1 .= '
											' . $__templater->func('react', array(array(
		'content' => $__vars['comment'],
		'link' => 'media/comments/react',
		'list' => '< .js-comment | .js-commentReactionsList',
	))) . '
										';
	if (strlen(trim($__compilerTemp1)) > 0) {
		$__finalCompiled .= '
									<div class="actionBar-set actionBar-set--external">
										' . $__compilerTemp1 . '
									</div>
								';
	}
	$__finalCompiled .= '
								<div class="actionBar-set actionBar-set--internal">
									<span class="actionBar-action">' . $__templater->func('date_dynamic', array($__vars['comment']['comment_date'], array(
	))) . '</span>
									';
	if ($__templater->method($__vars['comment'], 'canReport', array())) {
		$__finalCompiled .= '
										<a href="' . $__templater->func('link', array('media/comments/report', $__vars['comment'], ), true) . '" class="actionBar-action actionBar-action--report" data-xf-click="overlay" data-cache="false">' . 'Report' . '</a>
									';
	}
	$__finalCompiled .= '

									';
	$__compilerTemp2 = '';
	$__compilerTemp2 .= '
													';
	if ($__templater->method($__vars['comment'], 'canEdit', array())) {
		$__compilerTemp2 .= '
														';
		$__templater->includeJs(array(
			'src' => 'xf/action.js, xf/message.js',
			'min' => '1',
		));
		$__compilerTemp2 .= '
														<a href="' . $__templater->func('link', array('media/comments/edit', $__vars['comment'], array('lightbox' => 1, ), ), true) . '"
															class="menu-linkRow"
															data-menu-closer="true"
															data-xf-click="quick-edit"
															data-editor-target="[data-content=xfmg-comment-' . $__templater->escape($__vars['comment']['comment_id']) . '] .js-quickEditTarget">' . 'Edit' . '</a>
													';
	}
	$__compilerTemp2 .= '

													';
	if ($__vars['comment']['edit_count'] AND $__templater->method($__vars['comment'], 'canViewHistory', array())) {
		$__compilerTemp2 .= '
														<a href="' . $__templater->func('link', array('media/comments/history', $__vars['comment'], ), true) . '" class="menu-linkRow" data-xf-click="toggle" data-menu-closer="true" data-target="[data-content=xfmg-comment-' . $__templater->escape($__vars['comment']['comment_id']) . '] .js-historyTarget">' . 'History' . '</a>
													';
	}
	$__compilerTemp2 .= '

													';
	if ($__templater->method($__vars['comment'], 'canDelete', array())) {
		$__compilerTemp2 .= '
														<a href="' . $__templater->func('link', array('media/comments/delete', $__vars['comment'], ), true) . '" class="menu-linkRow" data-xf-click="overlay">' . 'Delete' . '</a>
													';
	}
	$__compilerTemp2 .= '

													';
	if ($__templater->method($__vars['comment'], 'canCleanSpam', array())) {
		$__compilerTemp2 .= '
														<a href="' . $__templater->func('link', array('spam-cleaner', $__vars['comment'], ), true) . '" class="menu-linkRow" data-xf-click="overlay">' . 'Spam' . '</a>
													';
	}
	$__compilerTemp2 .= '
													';
	if ($__templater->method($__vars['xf']['visitor'], 'canViewIps', array()) AND $__vars['comment']['ip_id']) {
		$__compilerTemp2 .= '
														<a href="' . $__templater->func('link', array('media/comments/ip', $__vars['comment'], ), true) . '" class="menu-linkRow" data-xf-click="overlay">' . 'IP' . '</a>
													';
	}
	$__compilerTemp2 .= '
													';
	if ($__templater->method($__vars['comment'], 'canWarn', array())) {
		$__compilerTemp2 .= '
														<a href="' . $__templater->func('link', array('media/comments/warn', $__vars['comment'], ), true) . '" class="menu-linkRow">' . 'Warn' . '</a>
													';
	} else if ($__vars['comment']['warning_id'] AND $__templater->method($__vars['xf']['visitor'], 'canViewWarnings', array())) {
		$__compilerTemp2 .= '
														<a href="' . $__templater->func('link', array('warnings', array('warning_id' => $__vars['comment']['warning_id'], ), ), true) . '" class="menu-linkRow" data-xf-click="overlay">' . 'View warning' . '</a>
													';
	}
	$__compilerTemp2 .= '
												';
	if (strlen(trim($__compilerTemp2)) > 0) {
		$__finalCompiled .= '
										<a class="actionBar-action actionBar-action--menuTrigger is-displayed"
											data-xf-click="menu"
											title="' . $__templater->filter('More options', array(array('for_attr', array()),), true) . '"
											role="button"
											tabindex="0"
											aria-expanded="false"
											aria-haspopup="true">&#8226;&#8226;&#8226;</a>
										<div class="menu" data-menu="menu">
											<div class="menu-content">
												<h4 class="menu-header">' . 'More options' . '</h4>
												' . $__compilerTemp2 . '
											</div>
										</div>
									';
	}
	$__finalCompiled .= '
								</div>
							</div>
							<div class="comment-reactions js-commentReactionsList ' . ($__vars['comment']['reactions'] ? 'is-active' : '') . '">
								' . $__templater->func('reactions', array($__vars['comment'], 'media/comments/reactions', array())) . '
							</div>
						</footer>

						<div class="js-historyTarget toggleTarget" data-href="trigger-href"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
';
	return $__finalCompiled;
}
),
'comment_deleted' => array(
'arguments' => function($__templater, array $__vars) { return array(
		'comment' => '!',
		'content' => '!',
		'linkPrefix' => '!',
	); },
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '
	';
	$__templater->includeCss('xfmg_comment.less');
	$__finalCompiled .= '

	<section class="message message--simple message--deleted message--comment' . ($__templater->method($__vars['comment'], 'isIgnored', array()) ? ' is-ignored' : '') . ' js-comment js-inlineModContainer"
		data-author="' . ($__templater->escape($__vars['comment']['User']['username']) ?: $__templater->escape($__vars['comment']['username'])) . '"
		data-content="xfmg-comment-' . $__templater->escape($__vars['comment']['comment_id']) . '">

		<span class="u-anchorTarget" id="xfmg-comment-' . $__templater->escape($__vars['comment']['comment_id']) . '"></span>
		<div class="message-inner">
			<div class="message-cell message-cell--user">
				' . $__templater->callMacro(null, 'message_macros::user_info_simple', array(
		'user' => $__vars['comment']['User'],
		'fallbackName' => $__vars['comment']['username'],
	), $__vars) . '
			</div>
			<div class="message-cell message-cell--main">
				<header class="message-attribution message-attribution--plain">
					<ul class="listInline listInline--bullet">
						<li class="message-attribution-user">
							' . $__templater->func('avatar', array($__vars['comment']['User'], 'xxs', false, array(
	))) . '
							<h4 class="attribution">' . $__templater->func('username_link', array($__vars['comment']['User'], true, array(
		'defaultname' => $__vars['comment']['username'],
	))) . '</h4>
						</li>
						<li>' . $__templater->func('date_dynamic', array($__vars['comment']['comment_date'], array(
	))) . '</li>
					</ul>
				</header>

				<div class="messageNotice messageNotice--deleted">
					' . $__templater->callMacro(null, 'deletion_macros::notice', array(
		'log' => $__vars['comment']['DeletionLog'],
	), $__vars) . '

					<a href="' . $__templater->func('link', array('media/comments/show', $__vars['comment'], ), true) . '" class="u-jsOnly" data-xf-click="inserter" data-replace="[data-content=xfmg-comment-' . $__templater->escape($__vars['comment']['comment_id']) . ']">' . 'Show' . $__vars['xf']['language']['ellipsis'] . '</a>
				</div>
			</div>
		</div>
	</section>
';
	return $__finalCompiled;
}
),
'comment_deleted_lightbox' => array(
'arguments' => function($__templater, array $__vars) { return array(
		'comment' => '!',
		'content' => '!',
		'linkPrefix' => '!',
	); },
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '
	<div class="message-responseRow">
		<div class="comment' . ($__templater->method($__vars['comment'], 'isIgnored', array()) ? ' is-ignored' : '') . '"
			data-author="' . ($__templater->escape($__vars['comment']['User']['username']) ?: $__templater->escape($__vars['comment']['username'])) . '"
			data-content="xfmg-comment-' . $__templater->escape($__vars['comment']['comment_id']) . '">

			<div class="comment-inner">
				<span class="comment-avatar">
					' . $__templater->func('avatar', array($__vars['comment']['User'], 'xxs', false, array(
		'defaultname' => $__vars['comment']['username'],
	))) . '
				</span>
				<div class="comment-main">
					<div class="comment-content">
						<div class="messageNotice messageNotice--deleted">
							' . $__templater->callMacro(null, 'deletion_macros::notice', array(
		'log' => $__vars['comment']['DeletionLog'],
	), $__vars) . '

							<a href="' . $__templater->func('link', array('media/comments/show', $__vars['comment'], array('lightbox' => 1, ), ), true) . '" class="u-jsOnly" data-xf-click="inserter" data-replace="[data-content=xfmg-comment-' . $__templater->escape($__vars['comment']['comment_id']) . ']">' . 'Show' . $__vars['xf']['language']['ellipsis'] . '</a>
						</div>
					</div>
					<div class="comment-actionBar actionBar">
						<div class="actionBar-set actionBar-set--internal">
							<span class="actionBar-action">
								' . $__templater->func('date_dynamic', array($__vars['comment']['comment_date'], array(
	))) . '
								<span role="presentation" aria-hidden="true">&middot;</span>
								' . $__templater->func('username_link', array($__vars['comment']['User'], false, array(
		'defaultname' => $__vars['comment']['username'],
		'class' => 'u-concealed',
	))) . '
							</span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
';
	return $__finalCompiled;
}
),
'comment_add' => array(
'arguments' => function($__templater, array $__vars) { return array(
		'comments' => '!',
		'content' => '!',
		'linkPrefix' => '!',
	); },
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '
	';
	$__vars['isPreRegComment'] = $__templater->method($__vars['content'], 'canAddCommentPreReg', array());
	$__finalCompiled .= '
	';
	if ($__templater->method($__vars['content'], 'canAddComment', array()) OR $__vars['isPreRegComment']) {
		$__finalCompiled .= '
		';
		$__templater->includeJs(array(
			'src' => 'xf/action.js, xf/message.js',
			'min' => '1',
		));
		$__templater->includeJs(array(
			'src' => 'xf/captcha.js',
			'min' => '1',
		));
		$__vars['lastPost'] = $__templater->filter($__vars['comments'], array(array('last', array()),), false);
		$__finalCompiled .= $__templater->form('

			' . '' . '
			' . '' . '

			' . '' . '

			<div class="block-container">
				<div class="block-body">
					' . $__templater->callMacro(null, 'quick_reply_macros::body', array(
			'message' => $__vars['content']['draft_comment']['message'],
			'messageSelector' => '.js-comment',
			'supportsMultiQuote' => $__vars['xf']['options']['multiQuote'],
			'multiQuoteHref' => $__templater->func('link', array($__vars['linkPrefix'] . '/multi-quote', $__vars['content'], ), false),
			'multiQuoteStorageKey' => 'multiQuoteMediaItem',
			'simple' => true,
			'submitText' => 'Post comment',
			'lastDate' => $__vars['lastPost']['comment_date'],
			'showGuestControls' => (!$__vars['isPreRegComment']),
			'previewUrl' => $__templater->func('link', array($__vars['linkPrefix'] . '/preview', $__vars['content'], ), false),
		), $__vars) . '
				</div>
			</div>
		', array(
			'action' => $__templater->func('link', array($__vars['linkPrefix'] . '/add-comment', $__vars['content'], ), false),
			'ajax' => 'true',
			'draft' => $__templater->func('link', array($__vars['linkPrefix'] . '/draft', $__vars['content'], ), false),
			'class' => 'block js-quickReply',
			'data-xf-init' => 'quick-reply' . ((($__templater->method($__vars['xf']['visitor'], 'isShownCaptcha', array()) AND (!$__vars['isPreRegComment']))) ? ' guest-captcha' : ''),
			'data-captcha-context' => 'xfmg_guest_comment_quick_reply',
			'data-message-container' => '< :prev | .js-replyNewCommentContainer',
		)) . '
	';
	}
	$__finalCompiled .= '
';
	return $__finalCompiled;
}
)),
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '

' . '

' . '

' . '

' . '

' . '

';
	return $__finalCompiled;
}
);