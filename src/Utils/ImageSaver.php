<?php

namespace App\Utils;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageSaver
{
	public static function save($imageFile, $directory, $sortie)
	{
		if ($imageFile) {
			$newFileName = uniqid().'.'.$imageFile->guessExtension();

			$imageFile->move(
				$directory,
				$newFileName
			);
			$sortie->setFilename($newFileName);
		}
	}
}