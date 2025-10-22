<?php
// FROM HASH: f75a3c4eb7ead8ada8a261e42c803cbe
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->pageParams['pageTitle'] = $__templater->preEscaped('Connected account providers');
	$__finalCompiled .= '

';
	$__templater->pageParams['pageAction'] = $__templater->preEscaped('
	' . $__templater->button('
		' . 'Sort' . '
	', array(
		'href' => $__templater->func('link', array('connected-accounts/th-cap-sort', ), false),
		'icon' => 'sort',
		'data-xf-click' => 'overlay',
	), '', array(
	)) . '
');
	$__finalCompiled .= '

<div class="block">
	<div class="block-outer">
		' . $__templater->callMacro(null, 'filter_macros::quick_filter', array(
		'key' => 'connected-accounts',
		'class' => 'block-outer-opposite',
	), $__vars) . '
	</div>
	<div class="block-container">
		';
	if (!$__templater->test($__vars['activeProviders'], 'empty', array())) {
		$__finalCompiled .= '
			<div class="block-body">
				';
		$__compilerTemp1 = '';
		if ($__templater->isTraversable($__vars['activeProviders'])) {
			foreach ($__vars['activeProviders'] AS $__vars['provider']) {
				$__compilerTemp1 .= '
						';
				$__compilerTemp2 = '';
				if ($__templater->method($__vars['provider'], 'isThCapProvider', array())) {
					$__compilerTemp2 .= '
								<span class="label label--primary">' . '[ThemeHouse]' . '</span>
							';
				}
				$__vars['providerTitle'] = $__templater->preEscaped('
							' . $__compilerTemp2 . '
							' . $__templater->escape($__vars['provider']['title']) . '
						');
				$__compilerTemp1 .= '
						';
				$__compilerTemp3 = array();
				if ($__templater->method($__vars['provider'], 'canBeTested', array())) {
					$__compilerTemp3[] = array(
						'href' => $__templater->func('link', array('connected-accounts/test', $__vars['provider'], ), false),
						'overlay' => 'true',
						'_type' => 'action',
						'html' => 'Test provider',
					);
				} else {
					$__compilerTemp3[] = array(
						'_type' => 'cell',
						'html' => '',
					);
				}
				$__compilerTemp3[] = array(
					'href' => $__templater->func('link', array('connected-accounts/deactivate', $__vars['provider'], ), false),
					'tooltip' => 'Deactivate',
					'_type' => 'delete',
					'html' => '',
				);
				$__compilerTemp1 .= $__templater->dataRow(array(
					'label' => $__templater->filter($__vars['providerTitle'], array(array('raw', array()),), true),
					'href' => $__templater->func('link', array('connected-accounts/edit', $__vars['provider'], ), false),
				), $__compilerTemp3) . '
					';
			}
		}
		$__finalCompiled .= $__templater->dataList('
					' . $__templater->dataRow(array(
			'rowtype' => 'subsection',
			'rowclass' => 'dataList-row--noHover',
		), array(array(
			'colspan' => '3',
			'_type' => 'cell',
			'html' => 'Active providers',
		))) . '
					' . $__compilerTemp1 . '
				', array(
		)) . '
			</div>
		';
	} else {
		$__finalCompiled .= '
			<!--<div class="block-body block-row">' . 'There are no active providers yet.' . '</div>-->
		';
	}
	$__finalCompiled .= '

		';
	if (!$__templater->test($__vars['inactiveProviders'], 'empty', array())) {
		$__finalCompiled .= '
			<div class="block-body">
				';
		$__compilerTemp4 = '';
		if ($__templater->isTraversable($__vars['inactiveProviders'])) {
			foreach ($__vars['inactiveProviders'] AS $__vars['provider']) {
				$__compilerTemp4 .= '
						';
				$__compilerTemp5 = '';
				if ($__templater->method($__vars['provider'], 'isThCapProvider', array())) {
					$__compilerTemp5 .= '
								<span class="label label--primary">' . '[ThemeHouse]' . '</span>
							';
				}
				$__vars['providerTitle'] = $__templater->preEscaped('
							' . $__compilerTemp5 . '
							' . $__templater->escape($__vars['provider']['title']) . '
						');
				$__compilerTemp4 .= '
						' . $__templater->dataRow(array(
					'label' => $__templater->filter($__vars['providerTitle'], array(array('raw', array()),), true),
					'href' => $__templater->func('link', array('connected-accounts/edit', $__vars['provider'], ), false),
				), array()) . '
					';
			}
		}
		$__finalCompiled .= $__templater->dataList('
					' . $__templater->dataRow(array(
			'rowtype' => 'subsection',
			'rowclass' => 'dataList-row--noHover',
		), array(array(
			'_type' => 'cell',
			'html' => 'Inactive providers',
		))) . '
					' . $__compilerTemp4 . '
				', array(
		)) . '
			</div>
		';
	}
	$__finalCompiled .= '
		<div class="block-footer">
			<span class="block-footer-counter">' . $__templater->func('display_totals', array($__templater->func('count', array($__vars['activeProviders'], ), false) + $__templater->func('count', array($__vars['inactiveProviders'], ), false), ), true) . '</span>
		</div>
	</div>
</div>';
	return $__finalCompiled;
}
);