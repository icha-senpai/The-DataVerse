<?php
// FROM HASH: 7b4392819b7ed7009c0a325a4085054b
return array(
'macros' => array('news_feed_profile_post' => array(
'extends' => 'audfeeds_newsFeedItem_macros::newsFeedItem',
'arguments' => function($__templater, array $__vars) { return array(
		'attachData' => null,
	); },
'extensions' => array('attribution' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	
	return $__finalCompiled;
},
'after_content' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
		$__finalCompiled .= '
		';
	if ($__vars['content']['attach_count']) {
		$__finalCompiled .= '
			' . $__templater->callMacro(null, 'message_macros::attachments', array(
			'attachments' => $__vars['content']['Attachments'],
			'message' => $__vars['content'],
			'canView' => $__templater->method($__vars['xf']['visitor'], 'hasPermission', array('profilePost', 'viewAttachment', )),
		), $__vars) . '
		';
	}
	$__finalCompiled .= '
		<div class="reactionsBar js-reactionsList ' . ($__vars['content']['reactions'] ? 'is-active' : '') . '">';
	if ($__vars['content']['reactions']) {
		$__finalCompiled .= '
			' . $__templater->func('reactions', array($__vars['content'], 'profile-posts/reactions', array())) . '
			';
	}
	$__finalCompiled .= '</div>
	';
	return $__finalCompiled;
},
'after_footer' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
		$__finalCompiled .= '
		<section class="message-responses js-messageResponses">

			';
	if (!$__templater->test($__vars['content']['LatestComments'], 'empty', array())) {
		$__finalCompiled .= '
				';
		if ($__templater->method($__vars['content'], 'hasMoreComments', array())) {
			$__finalCompiled .= '
					<div class="message-responseRow u-jsOnly js-commentLoader">
						<a href="' . $__templater->func('link', array('profile-posts/load-previous', $__vars['content'], array('before' => $__templater->arrayKey($__templater->method($__vars['content']['LatestComments'], 'first', array()), 'comment_date'), ), ), true) . '"
						   data-xf-click="comment-loader"
						   data-container=".js-commentLoader"
						   rel="nofollow">' . 'View previous comments' . $__vars['xf']['language']['ellipsis'] . '</a>
					</div>
				';
		}
		$__finalCompiled .= '
				<div class="js-replyNewMessageContainer">
					';
		if ($__templater->isTraversable($__vars['content']['LatestComments'])) {
			foreach ($__vars['content']['LatestComments'] AS $__vars['comment']) {
				$__finalCompiled .= '
						' . $__templater->callMacro('profile_post_macros', (($__vars['comment']['message_state'] == 'deleted') ? 'comment_deleted' : 'comment'), array(
					'comment' => $__vars['comment'],
					'profilePost' => $__vars['content'],
				), $__vars) . '
					';
			}
		}
		$__finalCompiled .= '
				</div>
				';
	} else {
		$__finalCompiled .= '
				<div class="js-replyNewMessageContainer"></div>
			';
	}
	$__finalCompiled .= '


			';
	if ($__templater->method($__vars['content'], 'canComment', array())) {
		$__finalCompiled .= '
				';
		$__templater->includeJs(array(
			'src' => 'xf/action.js, xf/message.js',
			'min' => '1',
		));
		$__finalCompiled .= '
				<div class="message-responseRow js-commentsTarget-' . $__templater->escape($__vars['content']['profile_post_id']) . ' ' . ($__templater->func('property', array('profilePostCommentToggle', ), false) ? 'toggleTarget' : '') . '">
					';
		$__vars['lastProfilePostComment'] = $__templater->filter($__vars['content']['LatestComments'], array(array('last', array()),), false);
		$__finalCompiled .= $__templater->form('
						<div class="comment-inner">
							<span class="comment-avatar">
								' . $__templater->func('avatar', array($__vars['xf']['visitor'], 'xxs', false, array(
		))) . '
							</span>
							<div class="comment-main">
								<div class="editorPlaceholder" data-xf-click="editor-placeholder">
									<div class="editorPlaceholder-editor is-hidden">
										' . $__templater->callMacro(null, 'quick_reply_macros::editor', array(
			'attachmentData' => $__vars['attachData'],
			'minHeight' => '40',
			'placeholder' => 'Write a comment' . $__vars['xf']['language']['ellipsis'],
			'submitText' => 'Post comment',
			'deferred' => true,
			'simpleSubmit' => true,
		), $__vars) . '
									</div>
									<div class="editorPlaceholder-placeholder">
										<div class="input"><span class="u-muted"> ' . 'Write a comment' . $__vars['xf']['language']['ellipsis'] . '</span></div>
									</div>
								</div>
							</div>
						</div>
						' . '' . '
						' . $__templater->formHiddenVal('last_date', $__vars['lastProfilePostComment']['comment_date'], array(
		)) . '
					', array(
			'action' => $__templater->func('link', array('profile-posts/add-comment', $__vars['content'], ), false),
			'ajax' => 'true',
			'class' => 'comment',
			'data-xf-init' => 'attachment-manager quick-reply',
			'data-message-container' => '< .js-messageResponses | .js-replyNewMessageContainer',
		)) . '
				</div>
			';
	}
	$__finalCompiled .= '
		</section>
	';
	return $__finalCompiled;
},
'footer' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
		$__finalCompiled .= '
		<footer class="message-footer">
			<div class="message-actionBar actionBar">
				';
	$__compilerTemp1 = '';
	$__compilerTemp1 .= '
							' . $__templater->func('react', array(array(
		'content' => $__vars['content'],
		'link' => 'profile-posts/react',
		'list' => '< .message | .js-reactionsList',
	))) . '

							';
	if ($__templater->method($__vars['content'], 'canComment', array()) AND $__templater->func('property', array('profilePostCommentToggle', ), false)) {
		$__compilerTemp1 .= '
								<a class="actionBar-action actionBar-action--reply"
								   data-xf-click="toggle"
								   data-target=".js-commentsTarget-' . $__templater->escape($__vars['content']['profile_post_id']) . '"
								   data-scroll-to="true"
								   role="button"
								   tabindex="0">' . 'Comment' . '</a>
							';
	}
	$__compilerTemp1 .= '
						';
	if (strlen(trim($__compilerTemp1)) > 0) {
		$__finalCompiled .= '
					<div class="actionBar-set actionBar-set--external">
						' . $__compilerTemp1 . '
					</div>
				';
	}
	$__finalCompiled .= '

				';
	$__compilerTemp2 = '';
	$__compilerTemp2 .= '
							';
	if ($__templater->method($__vars['content'], 'canUseInlineModeration', array())) {
		$__compilerTemp2 .= '
								<span class="actionBar-action actionBar-action--inlineMod">
									' . $__templater->formCheckBox(array(
			'standalone' => 'true',
		), array(array(
			'value' => $__vars['content']['profile_post_id'],
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
	$__compilerTemp2 .= '
							';
	if ($__templater->method($__vars['content'], 'canReport', array())) {
		$__compilerTemp2 .= '
								<a href="' . $__templater->func('link', array('profile-posts/report', $__vars['content'], ), true) . '" class="actionBar-action actionBar-action--report" data-xf-click="overlay">' . 'Report' . '</a>
							';
	}
	$__compilerTemp2 .= '

							';
	$__vars['hasActionBarMenu'] = false;
	$__compilerTemp2 .= '
							';
	if ($__templater->method($__vars['content'], 'canEdit', array())) {
		$__compilerTemp2 .= '
								';
		$__templater->includeJs(array(
			'src' => 'xf/action.js, xf/message.js',
			'min' => '1',
		));
		$__compilerTemp2 .= '
								<a href="' . $__templater->func('link', array('profile-posts/edit', $__vars['content'], ), true) . '"
								   class="actionBar-action actionBar-action--edit actionBar-action--menuItem"
								   data-xf-click="quick-edit"
								   data-editor-target="#js-profilePost-' . $__templater->escape($__vars['content']['profile_post_id']) . ' .js-quickEditTarget"
								   data-no-inline-mod="' . '"
								   data-menu-closer="true">' . 'Edit' . '</a>
								';
		$__vars['hasActionBarMenu'] = true;
		$__compilerTemp2 .= '
							';
	}
	$__compilerTemp2 .= '
							';
	if ($__templater->method($__vars['content'], 'canDelete', array('soft', ))) {
		$__compilerTemp2 .= '
								<a href="' . $__templater->func('link', array('profile-posts/delete', $__vars['content'], ), true) . '"
								   class="actionBar-action actionBar-action--delete actionBar-action--menuItem"
								   data-xf-click="overlay">' . 'Delete' . '</a>
								';
		$__vars['hasActionBarMenu'] = true;
		$__compilerTemp2 .= '
							';
	}
	$__compilerTemp2 .= '
							';
	if (($__vars['content']['message_state'] == 'deleted') AND $__templater->method($__vars['content'], 'canUndelete', array())) {
		$__compilerTemp2 .= '
								<a href="' . $__templater->func('link', array('profile-posts/undelete', $__vars['content'], ), true) . '" data-xf-click="overlay"
								   class="actionBar-action actionBar-action--undelete actionBar-action--menuItem">' . 'Undelete' . '</a>
								';
		$__vars['hasActionBarMenu'] = true;
		$__compilerTemp2 .= '
							';
	}
	$__compilerTemp2 .= '
							';
	if ($__templater->method($__vars['content'], 'canAudfeedsPromoteDemote', array())) {
		$__compilerTemp2 .= '
								<a href="' . $__templater->func('link', array('profile-posts/audfeeds-promote', $__vars['content'], ), true) . '"
								   data-xf-click="overlay"
								   class="actionBar-action actionBar-action--promote actionBar-action--menuItem">
									';
		if ($__vars['content']['audfeeds_promote_date']) {
			$__compilerTemp2 .= '
										' . 'audfeeds_demote' . '
										';
		} else {
			$__compilerTemp2 .= '
										' . 'audfeeds_promote' . '
									';
		}
		$__compilerTemp2 .= '
								</a>
								';
		$__vars['hasActionBarMenu'] = true;
		$__compilerTemp2 .= '
							';
	}
	$__compilerTemp2 .= '
							';
	if ($__templater->method($__vars['content'], 'canAudfeedsStickUnstick', array())) {
		$__compilerTemp2 .= '
								<a href="' . $__templater->func('link', array('profile-posts/audfeeds-stick', $__vars['content'], ), true) . '"
								   data-xf-click="overlay"
								   class="actionBar-action actionBar-action--stick actionBar-action--menuItem">
									';
		if ($__vars['content']['audfeeds_pin_date']) {
			$__compilerTemp2 .= '
										' . 'audfeeds_unstick' . '
										';
		} else {
			$__compilerTemp2 .= '
										' . 'audfeeds_stick' . '
									';
		}
		$__compilerTemp2 .= '
								</a>
								';
		$__vars['hasActionBarMenu'] = true;
		$__compilerTemp2 .= '
							';
	}
	$__compilerTemp2 .= '
							';
	if ($__templater->method($__vars['content'], 'canCleanSpam', array())) {
		$__compilerTemp2 .= '
								<a href="' . $__templater->func('link', array('spam-cleaner', $__vars['content'], ), true) . '"
								   class="actionBar-action actionBar-action--spam actionBar-action--menuItem"
								   data-xf-click="overlay">' . 'Spam' . '</a>
								';
		$__vars['hasActionBarMenu'] = true;
		$__compilerTemp2 .= '
							';
	}
	$__compilerTemp2 .= '
							';
	if ($__templater->method($__vars['xf']['visitor'], 'canViewIps', array()) AND $__vars['content']['ip_id']) {
		$__compilerTemp2 .= '
								<a href="' . $__templater->func('link', array('profile-posts/ip', $__vars['content'], ), true) . '"
								   class="actionBar-action actionBar-action--ip actionBar-action--menuItem"
								   data-xf-click="overlay">' . 'IP' . '</a>
								';
		$__vars['hasActionBarMenu'] = true;
		$__compilerTemp2 .= '
							';
	}
	$__compilerTemp2 .= '
							';
	if ($__templater->method($__vars['content'], 'canWarn', array())) {
		$__compilerTemp2 .= '
								<a href="' . $__templater->func('link', array('profile-posts/warn', $__vars['content'], ), true) . '"
								   class="actionBar-action actionBar-action--warn actionBar-action--menuItem">' . 'Warn' . '</a>
								';
		$__vars['hasActionBarMenu'] = true;
		$__compilerTemp2 .= '
								';
	} else if ($__vars['content']['warning_id'] AND $__templater->method($__vars['xf']['visitor'], 'canViewWarnings', array())) {
		$__compilerTemp2 .= '
								<a href="' . $__templater->func('link', array('warnings', array('warning_id' => $__vars['content']['warning_id'], ), ), true) . '"
								   class="actionBar-action actionBar-action--warn actionBar-action--menuItem"
								   data-xf-click="overlay">' . 'View warning' . '</a>
								';
		$__vars['hasActionBarMenu'] = true;
		$__compilerTemp2 .= '
							';
	}
	$__compilerTemp2 .= '

							';
	if ($__vars['hasActionBarMenu']) {
		$__compilerTemp2 .= '
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
	$__compilerTemp2 .= '
						';
	if (strlen(trim($__compilerTemp2)) > 0) {
		$__finalCompiled .= '
					<div class="actionBar-set actionBar-set--internal">
						' . $__compilerTemp2 . '
					</div>
				';
	}
	$__finalCompiled .= '

			</div>
		</footer>
	';
	return $__finalCompiled;
}),
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '
	';
	$__templater->includeJs(array(
		'src' => 'xf/comment.js',
		'min' => '1',
	));
	$__finalCompiled .= '

	' . $__templater->renderExtension('attribution', $__vars, $__extensions) . '
	
	' . $__templater->renderExtension('after_content', $__vars, $__extensions) . '
	
	' . $__templater->renderExtension('after_footer', $__vars, $__extensions) . '

	' . $__templater->renderExtension('footer', $__vars, $__extensions) . '
';
	return $__finalCompiled;
}
)),
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__compilerTemp1 = '';
	if ($__vars['user']['user_id'] == $__vars['content']['ProfileUser']['user_id']) {
		$__compilerTemp1 .= '
		' . '' . $__templater->func('username_link', array($__vars['user'], false, array('defaultname' => $__vars['newsFeed']['username'], ), ), true) . ' updated their <a href="' . $__templater->func('link', array('profile-posts', $__vars['content'], ), true) . '">status</a>.' . '
		';
	} else {
		$__compilerTemp1 .= '
		' . '' . $__templater->func('username_link', array($__vars['user'], false, array('defaultname' => $__vars['newsFeed']['username'], ), ), true) . ' left a message on ' . (((('<a href="' . $__templater->func('link', array('profile-posts', $__vars['content'], ), true)) . '">') . $__templater->escape($__vars['content']['ProfileUser']['username'])) . '</a>') . '\'s profile.' . '
	';
	}
	$__vars['feedTitle'] = $__templater->preEscaped('
	' . $__compilerTemp1 . '
');
	$__finalCompiled .= '

';
	$__compilerTemp2 = '';
	if ($__vars['user']['user_id'] != $__vars['content']['ProfileUser']['user_id']) {
		$__compilerTemp2 .= '
						' . 'audfeeds_posted_on_x' . '
					';
	}
	$__vars['titleHtml'] = $__templater->preEscaped('
	<div class="contentRow">
		<div class="contentRow-figure">
			' . $__templater->func('avatar', array($__vars['content']['User'], 'xs', false, array(
	))) . '
		</div>
		<div class="contentRow-main contentRow-main--close">
			<div><b>' . $__templater->func('username_link', array($__vars['content']['User'], false, array(
		'defaultname' => $__vars['content']['User']['username'],
	))) . '</b></div>
			<div class="contentRow-minor">
				<ul class="listInline listInline--bullet">
					' . $__compilerTemp2 . '
					<li><a class="contentRow-minor" href="' . $__templater->func('link', array('profile-posts', $__vars['content'], ), true) . '"  rel="nofollow">' . $__templater->func('date_dynamic', array($__vars['content']['post_date'], array(
	))) . '</a></li>
				</ul>
			</div>
		</div>
	</div>
');
	$__finalCompiled .= '

' . $__templater->callMacro(null, 'news_feed_profile_post', array(
		'titleHtml' => $__vars['titleHtml'],
		'contentType' => 'profile_post',
		'attachData' => $__vars['attachData'],
		'feedTitle' => $__vars['feedTitle'],
		'content' => $__vars['content'],
		'date' => $__vars['content']['post_date'],
	), $__vars) . '

';
	return $__finalCompiled;
}
);