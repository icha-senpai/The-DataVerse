<?php
// FROM HASH: 0ad690f5864b1916b83efe412915f891
return array(
'macros' => array('news_feed_post' => array(
'extends' => 'audfeeds_newsFeedItem_macros::newsFeedItem',
'arguments' => function($__templater, array $__vars) { return array(
		'thread' => '!',
		'poll' => false,
	); },
'extensions' => array('attribution_extras' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
		$__finalCompiled .= '
		<ul class="listInline listInline--bullet">
			<li><a href="' . $__templater->func('link', array('forums', $__vars['content']['Thread']['Forum']['Node'], ), true) . '">' . $__templater->escape($__vars['content']['Thread']['Forum']['Node']['title']) . '</a></li>
			<li>' . $__templater->escape($__vars['thread']['reply_count']) . ' ' . 'Replies' . '</li>
		</ul>
	';
	return $__finalCompiled;
},
'attribution_opposite' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
		$__finalCompiled .= '
		';
	if ($__templater->method($__vars['content'], 'isUnread', array())) {
		$__finalCompiled .= '
			<li><span class="message-newIndicator">' . 'New' . '</span></li>
			';
	} else if ($__templater->method($__vars['thread'], 'isUnread', array()) AND $__vars['showThreadUnreadIndicator']) {
		$__finalCompiled .= '
			<li><span class="message-newIndicator" title="' . $__templater->filter('New replies', array(array('for_attr', array()),), true) . '">' . 'New' . '</span></li>
		';
	}
	$__finalCompiled .= '
		<li>
			<a href="' . $__templater->func('link', array('threads/post', $__vars['thread'], array('post_id' => $__vars['content']['post_id'], ), ), true) . '"
			   class="message-attribution-gadget"
			   data-xf-init="share-tooltip"
			   data-href="' . $__templater->func('link', array('posts/share', $__vars['content'], ), true) . '"
			   rel="nofollow">
				' . $__templater->fontAwesome('fa-share-alt', array(
	)) . '
			</a>
		</li>
		';
	$__compilerTemp1 = '';
	$__compilerTemp1 .= '
					' . $__templater->callMacro(null, 'bookmark_macros::link', array(
		'content' => $__vars['content'],
		'class' => 'message-attribution-gadget bookmarkLink--highlightable',
		'confirmUrl' => $__templater->func('link', array('posts/bookmark', $__vars['content'], ), false),
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
	if (($__vars['thread']['discussion_type'] == 'poll') AND ($__vars['thread']['Poll'] AND $__vars['poll'])) {
		$__finalCompiled .= '
			' . $__templater->callMacro(null, 'poll_macros::poll_block', array(
			'poll' => $__vars['thread']['Poll'],
			'simpleDisplay' => true,
		), $__vars) . '
			<br />
		';
	}
	$__finalCompiled .= '
		' . $__templater->renderExtensionParent($__vars, null, $__extensions) . '
	';
	return $__finalCompiled;
},
'after_content' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
		$__finalCompiled .= '
		' . $__templater->callMacro('message_macros', 'attachments', array(
		'attachments' => $__vars['content']['Attachments'],
		'message' => $__vars['content'],
		'canView' => $__templater->method($__vars['thread'], 'canViewAttachments', array()),
	), $__vars) . '
		<div class="reactionsBar js-reactionsList ' . ($__vars['content']['reactions'] ? 'is-active' : '') . '">
			' . $__templater->func('reactions', array($__vars['content'], 'posts/reactions', array())) . '
		</div>
	';
	return $__finalCompiled;
},
'footer' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
		$__finalCompiled .= '
		' . $__templater->callMacro(null, 'post_macros::post_footer', array(
		'audfeedsThread' => true,
		'audfeedsView' => true,
		'post' => $__vars['content'],
		'thread' => $__vars['thread'],
	), $__vars) . '
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
	$__templater->inlineJs('
		var TH_Feeds = window.TH_Feeds || {};
		!function (window, document) {
		TH_Feeds.actionBarReply = XF.Event.newHandler({
			eventNameSpace: \'TH_FeedsActionBarReply\',
			init: function() {
				XF.on(this.target, \'click\', this.handleClick.bind(this));
			},
			click: function(e) {
				e.stopPropagation();
			},
			handleClick: function(e) {
				e.stopPropagation();
			}
		});
		XF.Event.register(\'click\', \'TH_Feeds_actionBar-action--reply\', \'TH_Feeds.actionBarReply\');
		}(window, document);
	', true);
	$__finalCompiled .= '
';
	return $__finalCompiled;
}
)),
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__compilerTemp1 = '';
	$__compilerTemp2 = '';
	$__compilerTemp2 .= '
				';
	$__vars['reactions'] = $__vars['content']['first_post_reactions'];
	$__compilerTemp2 .= '
				';
	if (($__templater->func('property', array('reactionSummaryOnLists', ), false) == 'status') AND $__vars['reactions']) {
		$__compilerTemp2 .= '
					<li>' . $__templater->func('reactions_summary', array($__vars['reactions'])) . '</li>
				';
	}
	$__compilerTemp2 .= '
				';
	if ($__vars['content']['discussion_state'] == 'moderated') {
		$__compilerTemp2 .= '
					<li>
						<i class="structItem-status structItem-status--moderated" aria-hidden="true" title="' . $__templater->filter('Awaiting approval', array(array('for_attr', array()),), true) . '"></i>
						<span class="u-srOnly">' . 'Awaiting approval' . '</span>
					</li>
				';
	}
	$__compilerTemp2 .= '
				';
	if ($__vars['content']['discussion_state'] == 'deleted') {
		$__compilerTemp2 .= '
					<li>
						<i class="structItem-status structItem-status--deleted" aria-hidden="true" title="' . $__templater->filter('Deleted', array(array('for_attr', array()),), true) . '"></i>
						<span class="u-srOnly">' . 'Deleted' . '</span>
					</li>
				';
	}
	$__compilerTemp2 .= '
				';
	if (!$__vars['content']['discussion_open']) {
		$__compilerTemp2 .= '
					<li>
						<i class="structItem-status structItem-status--locked" aria-hidden="true" title="' . $__templater->filter('Locked', array(array('for_attr', array()),), true) . '"></i>
						<span class="u-srOnly">' . 'Locked' . '</span>
					</li>
				';
	}
	$__compilerTemp2 .= '

				';
	if ($__vars['content']['sticky']) {
		$__compilerTemp2 .= '
					<li>
						<i class="structItem-status structItem-status--sticky" aria-hidden="true" title="' . $__templater->filter('Sticky', array(array('for_attr', array()),), true) . '"></i>
						<span class="u-srOnly">' . 'Sticky' . '</span>
					</li>
				';
	}
	$__compilerTemp2 .= '

				';
	if ($__vars['showWatched'] AND $__vars['xf']['visitor']['user_id']) {
		$__compilerTemp2 .= '
					';
		if ($__vars['content']['Watch'][$__vars['xf']['visitor']['user_id']]) {
			$__compilerTemp2 .= '
						<li>
							<i class="structItem-status structItem-status--watched" aria-hidden="true" title="' . $__templater->filter('Thread watched', array(array('for_attr', array()),), true) . '"></i>
							<span class="u-srOnly">' . 'Thread watched' . '</span>
						</li>
						';
		} else if ((!$__vars['forum']) AND $__vars['content']['Forum']['Watch'][$__vars['xf']['visitor']['user_id']]) {
			$__compilerTemp2 .= '
						<li>
							<i class="structItem-status structItem-status--watched" aria-hidden="true" title="' . $__templater->filter('Forum watched', array(array('for_attr', array()),), true) . '"></i>
							<span class="u-srOnly">' . 'Forum watched' . '</span>
						</li>
					';
		}
		$__compilerTemp2 .= '
				';
	}
	$__compilerTemp2 .= '

				';
	if ($__vars['content']['discussion_type'] == 'redirect') {
		$__compilerTemp2 .= '
					<li>
						<i class="structItem-status structItem-status--redirect" aria-hidden="true" title="' . $__templater->filter('Redirect', array(array('for_attr', array()),), true) . '"></i>
						<span class="u-srOnly">' . 'Redirect' . '</span>
					</li>
					';
	} else if (($__vars['content']['discussion_type'] == 'question') AND $__vars['content']['type_data']['solution_post_id']) {
		$__compilerTemp2 .= '
					<li>
						<i class="structItem-status structItem-status--solved" aria-hidden="true" title="' . $__templater->filter('Solved', array(array('for_attr', array()),), true) . '"></i>
						<span class="u-srOnly">' . 'Solved' . '</span>
					</li>
					';
	} else {
		$__compilerTemp2 .= '
					';
		if ($__vars['content']['discussion_type'] != 'discussion') {
			$__compilerTemp2 .= '
						';
			$__vars['threadTypeHandler'] = $__templater->method($__vars['content'], 'getTypeHandler', array());
			$__compilerTemp2 .= '
						';
			if ($__templater->method($__vars['threadTypeHandler'], 'getTypeIconClass', array())) {
				$__compilerTemp2 .= '
							<li>
								';
				$__vars['threadTypePhrase'] = $__templater->method($__vars['threadTypeHandler'], 'getTypeTitle', array());
				$__compilerTemp2 .= '
								' . $__templater->fontAwesome($__templater->escape($__templater->method($__vars['threadTypeHandler'], 'getTypeIconClass', array())), array(
					'class' => 'structItem-status',
					'title' => $__templater->filter($__vars['threadTypePhrase'], array(array('for_attr', array()),), false),
				)) . '
								<span class="u-srOnly">' . $__templater->escape($__vars['threadTypePhrase']) . '</span>
							</li>
						';
			}
			$__compilerTemp2 .= '
					';
		}
		$__compilerTemp2 .= '
				';
	}
	$__compilerTemp2 .= '
			';
	if (strlen(trim($__compilerTemp2)) > 0) {
		$__compilerTemp1 .= '
		<ul class="structItem-statuses">
			' . $__compilerTemp2 . '
		</ul>
	';
	}
	$__vars['feedTitle'] = $__templater->preEscaped('
	' . $__compilerTemp1 . '

	' . $__templater->func('prefix', array('thread', $__vars['content'], ), true) . '<a href="' . $__templater->func('link', array('threads', $__vars['content'], ), true) . '">' . $__templater->escape($__vars['content']['title']) . '</a>
');
	$__finalCompiled .= '

' . $__templater->callMacro(null, 'news_feed_post', array(
		'contentType' => 'post',
		'poll' => true,
		'feedTitle' => $__vars['feedTitle'],
		'content' => $__vars['content']['FirstPost'],
		'thread' => $__vars['content'],
		'date' => $__vars['content']['FirstPost']['post_date'],
	), $__vars) . '

';
	return $__finalCompiled;
}
);