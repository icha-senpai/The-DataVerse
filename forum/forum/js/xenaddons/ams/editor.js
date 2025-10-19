var XAAMS = window.XAAMS || {};

!function($, window, document, _undefined)
{
	"use strict";

	XF.Inserter = XF.extend(XF.Inserter, {

		__backup: {
			'_amsApplyAppend': '__amsApplyAppend'
		},

		_amsApplyAppend: function(selectorOld, $old, $new)
		{
			var validSelectors = ['.js-yourArticlesList', '.js-yourPagesList', '.js-yourSeriesList', '.js-browseArticlesList', '.js-browsePagesList', '.js-browseSeriesList'];
			if (validSelectors.indexOf(selectorOld) < 0)
			{
				this.__amsApplyAppend(selectorOld, $old, $new);
				return;
			}

			var $placeholders = $old.find('.amsArticleList-item--placeholder'),
				$children = $new.children();

			if (!$placeholders.length)
			{
				this.__amsApplyAppend(selectorOld, $old, $new);
				return;
			}

			$children.addClass('amsArticleList-item--placeholder--temp');

			this.__amsApplyAppend(selectorOld, $old, $new);

			setTimeout(function()
			{
				$children.removeClass('amsArticleList-item--placeholder--temp');
				$placeholders.remove();

				XF.layoutChange();
			}, 10);
		}
	});

	XAAMS.editorButton = {
		init: function()
		{
			XAAMS.editorButton.initializeDialog();
			XF.EditorHelpers.dialogs.ams = new XAAMS.EditorDialogAms('ams');

			if ($.FE.COMMANDS.xfCustom_ams)
			{
				$.FE.COMMANDS.xfCustom_ams.callback = XAAMS.editorButton.callback;
			}
		},

		initializeDialog: function()
		{
			XAAMS.EditorDialogAms = XF.extend(XF.EditorDialog, {
				cache: false,
				$container: null,

				_init: function(overlay)
				{
					var $container = overlay.$container;
					$container.on('change', '.js-articlesPicker', XF.proxy(this, 'pick'));
					this.$container = $container;

					$('#xa_ams_editor_dialog_form').submit(XF.proxy(this, 'submit'));
				},

				_afterShow: function(overlay)
				{
					this.tabCounts = {
						yourArticles: 0,
						yourPages: 0,
						yourSeries: 0,
						browseArticles: 0,
						browsePages: 0,
						browseSeries: 0
					};
				},

				pick: function(e)
				{
					var $checkbox = this.$container.find(e.currentTarget),
						checked = $checkbox.is(':checked'),
						$item = $checkbox.parent();

					if (checked)
					{
						this.checked($item);
					}
					else
					{
						this.unchecked($item);
					}
				},

				checked: function($item, $checkbox)
				{
					$item.addClass('is-selected');

					var $pane = $item.closest('ul > li.is-active'),
						$tab = this.$container.find($pane.data('tab')),
						tabType = $tab.attr('id');

					if (!$tab.hasClass('has-selected'))
					{
						$tab.addClass('has-selected');
					}

					var $valueEl = this.$container.find('.js-embedValue'),
						value = JSON.parse($valueEl.val()),
						type = $item.data('type'), id = $item.data('id'),
						itemId = type + '-' + id;

					if (value.hasOwnProperty(itemId))
					{
						return;
					}

					value[itemId] = 1;

					var $countEl = $tab.find('.js-tabCounter');

					this.tabCounts[tabType] += 1;
					$countEl.text(this.tabCounts[tabType]);

					$valueEl.val(JSON.stringify(value));
				},

				unchecked: function($item, $checkbox)
				{
					$item.removeClass('is-selected');

					var $pane = $item.closest('ul > li.is-active'),
						$tab = this.$container.find($pane.data('tab')),
						tabType = $tab.attr('id');

					var $valueEl = this.$container.find('.js-embedValue'),
						value = JSON.parse($valueEl.val()),
						type = $item.data('type'), id = $item.data('id'),
						itemId = type + '-' + id;

					if (!value.hasOwnProperty(itemId))
					{
						return;
					}

					delete value[itemId];

					var $countEl = $tab.find('.js-tabCounter');

					this.tabCounts[tabType] -= 1;

					if (this.tabCounts[tabType])
					{
						$countEl.text(this.tabCounts[tabType]);
					}
					else
					{
						$countEl.text(0);
						$tab.removeClass('has-selected');
					}

					$valueEl.val(JSON.stringify(value));
				},

				submit: function(e)
				{
					e.preventDefault();

					var ed = this.ed,
						overlay = this.overlay,
						$valueEl = this.$container.find('.js-embedValue'),
						value = JSON.parse($valueEl.val()),
						output = '';

					for (var key in value)
					{
						if (!value.hasOwnProperty(key))
						{
							continue;
						}

						var parts = key.split('-'),
							type = parts[0], id = parts[1];

						output += XF.htmlspecialchars('[AMS=' + type + ', ' + parseInt(id) + '][/AMS]');
						output += '<p><br></p>';
					}

					ed.selection.restore();
					ed.html.insert(output);
					overlay.hide();
				}
			});
		},

		callback: function()
		{
			XF.EditorHelpers.loadDialog(this, 'ams');
		}
	};

	$(document).on('editor:first-start', XAAMS.editorButton.init);
}
(jQuery, window, document);
