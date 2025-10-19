<?php

namespace XenAddons\AMS\XF\Service\Message;

class Preparer extends XFCP_Preparer
{
	protected $amsEmbeds = [];

	public function prepare($message, $checkValidity = true)
	{
		$message = parent::prepare($message, $checkValidity);

		/** @var \XenAddons\AMS\XF\BbCode\ProcessorAction\AnalyzeUsage $usage */
		$usage = $this->bbCodeProcessor->getAnalyzer('usage');
		$this->amsEmbeds = $usage->getAmsEmbeds();

		return $message;
	}

	public function getEmbeddedAmsItems()  // TODO check to see if this should be AmsArticles insteald of AmsItems
	{
		return $this->amsEmbeds;
	}

	public function getEmbedMetadata()
	{
		$metadata = parent::getEmbedMetadata();
		if ($this->amsEmbeds)
		{
			$metadata['amsEmbeds'] = $this->amsEmbeds;
		}

		return $metadata;
	}

	public function checkValidity($message)
	{
		$isValid = parent::checkValidity($message);

		/** @var \XF\BbCode\ProcessorAction\AnalyzeUsage $usage */
		$usage = $this->bbCodeProcessor->getAnalyzer('usage');

		if ($this->isValid)
		{
			$maxImages = $this->constraints['maxImages'];
			if ($maxImages && $usage->getTagCount('img') + $usage->getTagCount('ams') > $maxImages)
			{
				$this->errors[] = \XF::phraseDeferred(
					'please_enter_message_with_no_more_than_x_images',
					['count' => $maxImages]
				);
				$this->isValid = false;
			}
		}

		return $this->isValid;
	}
}