<?php
// FROM HASH: c4e322c9d57806d6ce7847108caaaf85
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	if (!$__templater->test($__vars['thWatchedNodes'], 'empty', array())) {
		$__finalCompiled .= '
	<div class="block uix_nodeList block--category2 block--category block--categoryWatched ' . ($__templater->func('is_toggled', array('thuixsf-watched', ), false) ? 'category--collapsed' : '') . '">
		<span class="u-anchorTarget" id="thWatchedNodes"></span>
		';
		if ($__templater->func('property', array('uix_categoryStripOutsideWrapper', ), false)) {
			$__finalCompiled .= '
			<h2 class="block-header js-nodeMain' . ($__templater->func('property', array('uix_stickyCategoryStrips', ), false) ? ' uix_stickyCategoryStrips' : '') . '">
				';
			if ($__templater->func('property', array('uix_categoryStripIcons', ), false)) {
				$__finalCompiled .= '
					<div class="uix_categoryStrip__icon">
						' . $__templater->fontAwesome('fa-folder', array(
				)) . '
					</div>
				';
			}
			$__finalCompiled .= '
				<div class="uix_categoryStrip-content">
					' . '
					' . '
					';
			$__vars['uix_categoryDescriptionDisplay'] = $__templater->func('property', array('uix_categoryDescriptionDisplay', ), false);
			$__finalCompiled .= '
					<a href="' . $__templater->func('link', array('watched/forums', ), true) . '" class="uix_categoryTitle">
						' . 'th_flwf_watched_nodes' . '
					</a>
					';
			if (($__vars['uix_categoryDescriptionDisplay'] != 'none') AND $__vars['node']['description']) {
				$__finalCompiled .= '
						<div class="node-description ' . (($__vars['uix_categoryDescriptionDisplay'] == 'tooltip') ? 'node-description--tooltip js-nodeDescTooltip' : '') . '">' . $__templater->filter($__vars['node']['description'], array(array('raw', array()),), true) . '</div>
					';
			}
			$__finalCompiled .= '
				</div>
				';
			if (($__templater->func('property', array('uix_categoryCollapse', ), false) AND $__templater->method($__vars['xf']['visitor'], 'hasPermission', array('th_uix', 'collapseCategories', )))) {
				$__finalCompiled .= '
					<a class="u-ripple categoryCollapse--trigger"
					   data-xf-click="toggle"
					   data-target="< :up :next"
					   data-hide="true"
					   data-xf-init="toggle-storage"
					   data-storage-type="cookie"
					   data-storage-key="thuixsf-watched">
						' . $__templater->fontAwesome('fa-chevron-up', array(
				)) . '</a>
				';
			}
			$__finalCompiled .= '
			</h2>
		';
		}
		$__finalCompiled .= '
		<div class="block-container">
			';
		if (!$__templater->func('property', array('uix_categoryStripOutsideWrapper', ), false)) {
			$__finalCompiled .= '
				<h2 class="block-header js-nodeMain ' . ($__templater->func('property', array('uix_stickyCategoryStrips', ), false) ? ' uix_stickyCategoryStrips' : '') . '">
					';
			if ($__templater->func('property', array('uix_categoryStripIcons', ), false)) {
				$__finalCompiled .= '
						<div class="uix_categoryStrip__icon">
							' . $__templater->fontAwesome('fa-folder', array(
				)) . '
						</div>
					';
			}
			$__finalCompiled .= '
					<div class="uix_categoryStrip-content">
						' . '
						' . '
						';
			$__vars['uix_categoryDescriptionDisplay'] = $__templater->func('property', array('uix_categoryDescriptionDisplay', ), false);
			$__finalCompiled .= '
						<a href="' . $__templater->func('link', array('watched/forums', ), true) . '" class="uix_categoryTitle">
							' . 'th_flwf_watched_nodes' . '
						</a>
						';
			if (($__vars['uix_categoryDescriptionDisplay'] != 'none') AND $__vars['node']['description']) {
				$__finalCompiled .= '
							<div class="node-description ' . (($__vars['uix_categoryDescriptionDisplay'] == 'tooltip') ? 'node-description--tooltip js-nodeDescTooltip' : '') . '">' . $__templater->filter($__vars['node']['description'], array(array('raw', array()),), true) . '</div>
						';
			}
			$__finalCompiled .= '
					</div>
				';
			if (($__templater->func('property', array('uix_categoryCollapse', ), false) AND $__templater->method($__vars['xf']['visitor'], 'hasPermission', array('th_uix', 'collapseCategories', )))) {
				$__finalCompiled .= '
					<a class="u-ripple categoryCollapse--trigger"
					   data-xf-click="toggle"
					   data-target="< :up :next"
					   data-hide="true"
					   data-xf-init="toggle-storage"
					   data-storage-type="cookie"
					   data-storage-key="thuixsf-watched">
						' . $__templater->fontAwesome('fa-chevron-up', array(
				)) . '</a>
				';
			}
			$__finalCompiled .= '
				</h2>
			';
		}
		$__finalCompiled .= '
			<div class="uix_block-body--outer">
				<div class="block-body">
					';
		if ($__templater->isTraversable($__vars['thWatchedNodes'])) {
			foreach ($__vars['thWatchedNodes'] AS $__vars['id'] => $__vars['watchedNode']) {
				$__finalCompiled .= '
						' . $__templater->callMacro(null, 'forum_list::node_list_entry', array(
					'node' => $__vars['watchedNode']['record'],
					'extras' => $__vars['nodeExtras'][$__vars['id']],
					'children' => $__vars['watchedNode']['children'],
					'depth' => '2',
					'childExtras' => $__vars['nodeExtras'],
				), $__vars) . '
					';
			}
		}
		$__finalCompiled .= '
				</div>
			</div>
		</div>
	</div>
';
	}
	return $__finalCompiled;
}
);