<?php
// FROM HASH: 2b69dbc6ba9734ee5b01e2df1d7e378b
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->pageParams['pageTitle'] = $__templater->preEscaped('Change forum type' . ': ' . $__templater->escape($__vars['node']['title']));
	$__finalCompiled .= '

';
	$__compilerTemp1 = '';
	if ($__vars['isResourceForum'] AND ($__vars['currentForumTypeId'] == 'discussion')) {
		$__compilerTemp1 .= '
	<div class="blockMessage blockMessage--important">
		' . 'This forum is assigned to a resource category and may contain resource threads. Changing the type to anything other than "General discussion" may cause the existing resource threads to lose their type association with resource items.' . '
	</div>
';
	}
	$__compilerTemp2 = '';
	if ($__vars['newForumTypeId']) {
		$__compilerTemp2 .= '
				' . $__templater->formRow('
					' . $__templater->escape($__vars['newForumTypeTitle']) . '
				', array(
			'label' => 'New forum type',
			'explain' => $__templater->escape($__vars['newForumTypeDesc']),
		)) . '
				';
		if ($__vars['typeConfigTemplate']) {
			$__compilerTemp2 .= '
					' . $__templater->includeTemplate($__vars['typeConfigTemplate'], $__vars) . '
				';
		}
		$__compilerTemp2 .= '
				' . $__templater->formHiddenVal('new_forum_type_id', $__vars['newForumTypeId'], array(
		)) . '
				' . $__templater->formHiddenVal('confirm', '1', array(
		)) . '
			';
	} else {
		$__compilerTemp2 .= '
				';
		$__compilerTemp3 = array();
		if ($__templater->isTraversable($__vars['forumTypesInfo'])) {
			foreach ($__vars['forumTypesInfo'] AS $__vars['forumTypeId'] => $__vars['forumTypeInfo']) {
				$__compilerTemp3[] = array(
					'value' => $__vars['forumTypeId'],
					'label' => $__templater->escape($__vars['forumTypeInfo']['title']),
					'hint' => $__templater->escape($__vars['forumTypeInfo']['description']),
					'_type' => 'option',
				);
			}
		}
		$__compilerTemp2 .= $__templater->formRadioRow(array(
			'name' => 'new_forum_type_id',
			'value' => $__vars['currentForumTypeId'],
		), $__compilerTemp3, array(
			'label' => 'New forum type',
		)) . '
			';
	}
	$__compilerTemp4 = '';
	if ($__vars['newForumTypeId']) {
		$__compilerTemp4 .= '
			' . $__templater->formSubmitRow(array(
			'submit' => 'Change type',
		), array(
		)) . '
		';
	} else {
		$__compilerTemp4 .= '
			' . $__templater->formSubmitRow(array(
			'submit' => 'Continue' . $__vars['xf']['language']['ellipsis'],
		), array(
		)) . '
		';
	}
	$__finalCompiled .= $__templater->form('
	<div class="block-container">
		<div class="block-body">
			' . $__templater->formInfoRow('
				' . $__compilerTemp1 . '
<div class="blockMessage blockMessage--warning">
					' . 'Changing a forum\'s type will change the type of all threads within the forum to a type that is allowed in the forum. This may cause some data loss. Please be sure that you select the correct options before proceeding.' . '
				</div>
			', array(
	)) . '

			' . $__templater->formRow('
				' . $__templater->escape($__vars['currentForumTypeTitle']) . '
			', array(
		'label' => 'Current forum type',
		'explain' => $__templater->escape($__vars['currentForumTypeDesc']),
	)) . '

			' . $__compilerTemp2 . '
		</div>
		' . $__compilerTemp4 . '
	</div>
', array(
		'action' => $__templater->func('link', array('forums/change-type', $__vars['node'], ), false),
		'ajax' => 'true',
	));
	return $__finalCompiled;
}
);