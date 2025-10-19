<?php

namespace XenAddons\AMS\ControllerPlugin;

use XF\ControllerPlugin\AbstractPlugin;
use XenAddons\AMS\Entity\SeriesItem;

class SeriesIcon extends AbstractPlugin
{
	public function actionUpload(SeriesItem $series)
	{
		/** @var \XenAddons\AMS\Service\SeriesItem\Icon $iconService */
		$iconService = $this->service('XenAddons\AMS:Series\Icon', $series);

		$action = $this->filter('icon_action', 'str');

		if ($action == 'delete')
		{
			$iconService->deleteIcon();
		}
		else if ($action == 'custom')
		{
			$upload = $this->request->getFile('upload', false, false);
			if ($upload)
			{
				if (!$iconService->setImageFromUpload($upload))
				{
					throw $this->exception($this->error($iconService->getError()));
				}

				if (!$iconService->updateIcon())
				{
					throw $this->exception($this->error(\XF::phrase('xa_ams_new_icon_could_not_be_applied_try_later')));
				}
			}
		}
	}
}