<?php
// FROM HASH: ee394b7dcb131a4dc4fcef89aa845397
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->pageParams['pageTitle'] = $__templater->preEscaped($__templater->escape($__vars['campaign']['title']));
	$__finalCompiled .= '

';
	$__compilerTemp1 = $__vars;
	$__compilerTemp1['pageSelected'] = 'overview';
	$__templater->wrapTemplate('thdonate_campaign_wrapper', $__compilerTemp1);
	$__finalCompiled .= '

<div class="block js-CampaignInfoBlock">
	<div class="block-container">
		<div class="block-body">
			<div class="block-row">
				<div class="campaign-description">
					';
	if ($__vars['campaign']['description']) {
		$__finalCompiled .= '
						' . $__templater->func('bb_code', array($__vars['campaign']['description'], 'html', $__vars['campaign'], ), true) . '
						';
	} else {
		$__finalCompiled .= '
						' . 'thdonate_no_campaign_description' . '
					';
	}
	$__finalCompiled .= '
				</div>

				<div class="reactionsBar js-reactionsList ' . ($__vars['campaign']['reactions'] ? 'is-active' : '') . '">
					' . $__templater->func('reactions', array($__vars['campaign'], 'donate/reactions', array())) . '
				</div>

				';
	if ($__templater->func('is_addon_active', array('ThemeHouse/Reactions', ), false) AND $__vars['xf']['options']['th_reactHandlers_reactions']['thdonate_campaign']) {
		$__finalCompiled .= '

					' . $__templater->callMacro(null, 'th_display_reactions::reactions_bar', array(
			'type' => 'thdonate_campaign',
			'id' => 'campaign_id',
			'content' => $__vars['campaign'],
		), $__vars) . '

					';
	} else {
		$__finalCompiled .= '

					';
		$__compilerTemp2 = '';
		$__compilerTemp2 .= '
								';
		$__compilerTemp3 = '';
		$__compilerTemp3 .= '
											';
		if ($__templater->method($__vars['campaign'], 'isValidRelation', array('Bookmarks', ))) {
			$__compilerTemp3 .= '
											' . $__templater->callMacro(null, 'bookmark_macros::link', array(
				'content' => $__vars['campaign'],
				'class' => 'message-attribution-gadget bookmarkLink--highlightable',
				'confirmUrl' => $__templater->func('link', array('donate/bookmark', $__vars['campaign'], ), false),
				'showText' => false,
			), $__vars) . '
											';
		}
		$__compilerTemp3 .= '
											' . $__templater->func('react', array(array(
			'content' => $__vars['campaign'],
			'link' => 'donate/react',
			'list' => '< .js-CampaignInfoBlock | .js-reactionsList',
		))) . '
										';
		if (strlen(trim($__compilerTemp3)) > 0) {
			$__compilerTemp2 .= '
									<div class="actionBar-set actionBar-set--external">
										' . $__compilerTemp3 . '
									</div>
								';
		}
		$__compilerTemp2 .= '
							';
		if (strlen(trim($__compilerTemp2)) > 0) {
			$__finalCompiled .= '
						<div class="actionBar">
							' . $__compilerTemp2 . '
						</div>
					';
		}
		$__finalCompiled .= '
				';
	}
	$__finalCompiled .= '
			</div>
		</div>
	</div>
</div>';
	return $__finalCompiled;
}
);