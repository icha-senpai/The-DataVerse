<?php

namespace XenAddons\AMS\Service\Series;

use XenAddons\AMS\Entity\SeriesItem;

class Icon extends \XF\Service\AbstractService
{
	/**
	 * @var \XenAddons\AMS\Entity\SeriesItem
	 */
	protected $series;

	protected $logIp = true;

	protected $fileName;

	protected $width;

	protected $height;

	protected $type;

	protected $error = null;

	protected $allowedTypes = [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG];


	public function __construct(\XF\App $app, SeriesItem $series)
	{
		parent::__construct($app);
		$this->series = $series;
	}

	public function getSeries()
	{
		return $this->series;
	}

	public function logIp($logIp)
	{
		$this->logIp = $logIp;
	}

	public function getError()
	{
		return $this->error;
	}

	public function setImage($fileName)
	{
		if (!$this->validateImageAsIcon($fileName, $error))
		{
			$this->error = $error;
			$this->fileName = null;
			return false;
		}

		$this->fileName = $fileName;
		return true;
	}

	public function setImageFromUpload(\XF\Http\Upload $upload)
	{
		$upload->requireImage();

		if (!$upload->isValid($errors))
		{
			$this->error = reset($errors);
			return false;
		}

		return $this->setImage($upload->getTempFile());
	}

	public function validateImageAsIcon($fileName, &$error = null)
	{
		$error = null;

		if (!file_exists($fileName))
		{
			throw new \InvalidArgumentException("Invalid file '$fileName' passed to icon service");
		}
		if (!is_readable($fileName))
		{
			throw new \InvalidArgumentException("'$fileName' passed to icon service is not readable");
		}

		$imageInfo = filesize($fileName) ? getimagesize($fileName) : false;
		if (!$imageInfo)
		{
			$error = \XF::phrase('provided_file_is_not_valid_image');
			return false;
		}

		$type = $imageInfo[2];
		if (!in_array($type, $this->allowedTypes))
		{
			$error = \XF::phrase('provided_file_is_not_valid_image');
			return false;
		}

		$width = $imageInfo[0];
		$height = $imageInfo[1];

		if (!$this->app->imageManager()->canResize($width, $height))
		{
			$error = \XF::phrase('uploaded_image_is_too_big');
			return false;
		}

		// require 2:1 aspect ratio or squarer
		if ($width > 2 * $height || $height > 2 * $width)
		{
			$error = \XF::phrase('please_provide_an_image_whose_longer_side_is_no_more_than_twice_length');
			return false;
		}

		$this->width = $width;
		$this->height = $height;
		$this->type = $type;

		return true;
	}

	public function updateIcon()
	{
		if (!$this->fileName)
		{
			throw new \LogicException("No source file for icon set");
		}

		$imageManager = $this->app->imageManager();
		$targetSize = $this->app->container('avatarSizeMap')['m'];
		$outputFile = null;

		if ($this->width != $targetSize || $this->height != $targetSize)
		{
			$image = $imageManager->imageFromFile($this->fileName);
			if (!$image)
			{
				return false;
			}

			$image->resizeAndCrop($targetSize);

			$newTempFile = \XF\Util\File::getTempFile();
			if ($newTempFile && $image->save($newTempFile))
			{
				$outputFile = $newTempFile;
			}
		}
		else
		{
			$outputFile = $this->fileName;
		}

		if (!$outputFile)
		{
			throw new \RuntimeException("Failed to save image to temporary file; check internal_data/data permissions");
		}

		$dataFile = $this->series->getAbstractedIconPath();
		\XF\Util\File::copyFileToAbstractedPath($outputFile, $dataFile);

		$this->series->icon_date = \XF::$time;
		$this->series->save();

		if ($this->logIp)
		{
			$ip = ($this->logIp === true ? $this->app->request()->getIp() : $this->logIp);
			$this->writeIpLog('update', $ip);
		}

		return true;
	}

	public function deleteIcon()
	{
		$this->deleteIconFiles();

		$this->series->icon_date = 0;
		$this->series->save();

		if ($this->logIp)
		{
			$ip = ($this->logIp === true ? $this->app->request()->getIp() : $this->logIp);
			$this->writeIpLog('delete', $ip);
		}

		return true;
	}

	public function deleteIconForSeriesDelete()
	{
		$this->deleteIconFiles();

		return true;
	}

	protected function deleteIconFiles()
	{
		if ($this->series->icon_date)
		{
			\XF\Util\File::deleteFromAbstractedPath($this->series->getAbstractedIconPath());
		}
	}

	protected function writeIpLog($action, $ip)
	{
		$series = $this->series;

		/** @var \XF\Repository\Ip $ipRepo */
		$ipRepo = $this->repository('XF:Ip');
		$ipRepo->logIp(\XF::visitor()->user_id, $ip, 'ams_series', $series->series_id, 'avatar_' . $action);
	}
}