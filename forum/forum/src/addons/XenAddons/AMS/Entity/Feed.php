<?php

namespace XenAddons\AMS\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

/**
 * COLUMNS
 * @property int|null feed_id
 * @property string title
 * @property string url
 * @property int frequency
 * @property int category_id
 * @property int user_id
 * @property int prefix_id
 * @property string title_template
 * @property string message_template
 * @property bool article_visible
 * @property int last_fetch
 * @property bool active
 *
 * RELATIONS
 * @property \XF\Entity\User User
 * @property \XenAddons\AMS\Entity\Category Category
 */
class Feed extends Entity
{
	public function getEntryTitle(array $entry)
	{
		if (!$this->title_template)
		{
			$title = $entry['title'];
		}
		else
		{
			$title = $this->replaceTokens($this->title_template, $entry);
		}

		return $title;
	}

	public function getEntryMessage(array $entry)
	{
		if (!$this->message_template)
		{
			$message = $entry['content'];
		}
		else
		{
			$message = $this->replaceTokens($this->message_template, $entry);
		}

		$message = trim($message);
		if ($message === '')
		{
			$message = '[url]' . $entry['link'] . '[/url]';
		}

		return $message;
	}

	/**
	 * Searches the given template string for {token} and replaces it with $entry[token]
	 *
	 * @param string $template
	 * @param array $entry
	 */
	protected function replaceTokens($template, array $entry)
	{
		if (preg_match_all('/\{([a-z0-9_]+)\}/i', $template, $matches))
		{
			foreach ($matches[1] AS $token)
			{
				if (isset($entry[$token]))
				{
					$template = str_replace('{' . $token . '}', $entry[$token], $template);
				}
			}
		}

		return $template;
	}

	protected function verifyCategoryId(&$categoryId)
	{
		$category = $this->_em->find('XenAddons\AMS:Category', $categoryId);
		if (!$category)
		{
			$this->error(\XF::phrase('please_select_valid_category'), 'category_id');
			return false;
		}

		return true;
	}

	protected function _preSave()
	{
		if ($this->url
			&& (!$this->title
				|| ($this->isChanged('url') && !$this->isChanged('title'))
			)
		)
		{
			/** @var \XenAddons\AMS\Service\Feed\Reader $reader */
			$reader = $this->app()->service('XenAddons\AMS:Feed\Reader', $this->url);
			$title = $reader->getTitle();

			$this->title = $title ?: $this->url;
		}
	}

	protected function _postDelete()
	{
		$this->db()->delete('xf_xa_ams_feed_log', 'feed_id = ?', $this->feed_id);
	}

	protected function _setupDefaults()
	{
		$this->frequency = 1800;
		$this->message_template = '{content}' . "\n\n" . '[url="{link}"]' . \XF::phrase('continue_reading') . '[/url]';
	}

	public static function getStructure(Structure $structure)
	{
		$structure->table = 'xf_xa_ams_feed';
		$structure->shortName = 'XenAddons\AMS:Feed';
		$structure->primaryKey = 'feed_id';
		$structure->columns = [
			'feed_id' => ['type' => self::UINT, 'autoIncrement' => true, 'nullable' => true],
			'title' => ['type' => self::STR, 'maxLength' => 250, 'default' => ''],
			'url' => ['type' => self::STR, 'maxLength' => 2083, 'required' => true,
				'match' => 'url'
			],
			'frequency' => ['type' => self::UINT, 'required' => true],
			'category_id' => ['type' => self::UINT, 'required' => true],
			'user_id' => ['type' => self::UINT, 'default' => 0],
			'prefix_id' => ['type' => self::UINT, 'default' => 0],
			'title_template' => ['type' => self::STR, 'maxLength' => 250, 'default' => ''],
			'message_template' => ['type' => self::STR,
				'required' => 'please_enter_message_template'
			],
			'article_visible' => ['type' => self::BOOL, 'default' => true],
			'last_fetch' => ['type' => self::UINT, 'default' => 0],
			'active' => ['type' => self::BOOL, 'default' => true]
		];
		$structure->getters = [];
		$structure->relations = [
			'User' => [
				'entity' => 'XF:User',
				'type' => self::TO_ONE,
				'conditions' => 'user_id',
				'primary' => true
			],
			'Category' => [
				'entity' => 'XenAddons\AMS:Category',
				'type' => self::TO_ONE,
				'conditions' => 'category_id',
				'primary' => true
			]
		];

		return $structure;
	}
}