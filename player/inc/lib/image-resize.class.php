<?php

	/*************************************************************************************************************
	* Image Handling Class
	* @author 	Jaka Prasnikar - https://prahec.com/
	* @version 	2.1 (29.08.2015)
	************************************************************************************************************ */
	class image {

		// *** Class variables
		protected $image, $width, $height, $imageResized;
		public $opts;


		## ------------------------ Initial construct ---------------------------
		function __construct($fileName, $opts = array() ) {

			$this->options = array ( // Defaults
				'jpegoptim'		=>	false,
				'pngquant'		=>	false
			) + $opts;	// Replace defaults with opts

			// *** Open up the file
			if ($this->image = $this->openImage($fileName)) {

				// *** Get width and height
				$this->width  = imagesx($this->image);
				$this->height = imagesy($this->image);
				return true;

			}  else {

				$this->image == false;
				return false;

			}

		}

		## --------------------------------------------------------
		private function openImage($file) {

			// *** Get extension
			$extension = strtolower(strrchr($file, '.'));

			switch($extension)
			{
				case '.jpg':
				case '.jpeg':
					$img = @imagecreatefromjpeg($file);
					break;
				case '.gif':
					$img = @imagecreatefromgif($file);
					break;
				case '.png':
					$img = @imagecreatefrompng($file);
					break;
				default:
					$img = false;
					break;
			}
			return $img;
		}

		## --------------------------------------------------------
		public function resize ($size, $option = "auto", $opts = array()) {

			// Exit on error!
			if ($this->image == false) { return false; }

			// Size takes (width)x(height) or 50% or auto arguments
			if ($size == "auto") {

				// Auto, usually to just resample image
				$newWidth = $this->width;
				$newHeight = $this->height;

			} else if (strstr($size, ':') !== false) {

				// Crop by ratio (16:9, 4:3, 1:1, etc...)
				$ratio = explode (':', $size);
				$divide = $ratio[0] / $ratio[1];

				$newWidth = $this->width;
				$newHeight = $this->width / $divide;

			} else if (strstr($size, 'x') === false) {

				// Resize by %
				$newWidth = round(($this->width / 100) * $size);
				$newHeight = round(($this->height / 100) * $size);

			} else {

				// Resize by numXnum pix
				$size = explode('x', $size);
				$newWidth = $size[0];
				$newHeight = $size[1];

			}

			// *** Get optimal width and height - based on $option
			$optionArray = $this->getDimensions($newWidth, $newHeight, $option);

			$optimalWidth  = $optionArray['optimalWidth'];
			$optimalHeight = $optionArray['optimalHeight'];

			// *** Resample - create image canvas of x, y size
			$this->imageResized = imagecreatetruecolor($optimalWidth, $optimalHeight);

			// *** Alpha Fix
			imagealphablending($this->imageResized, false);
			imagesavealpha($this->imageResized, true); 

			// *** Resample image
			imagecopyresampled($this->imageResized, $this->image, 0, 0, 0, 0, $optimalWidth, $optimalHeight, $this->width, $this->height);

			// *** if option is 'crop', then crop too
			if ($option == 'crop') {
				$this->crop( $optimalWidth, $optimalHeight, $newWidth, $newHeight, $opts );
			}

		}

		## --------------------------------------------------------
		private function getDimensions($newWidth, $newHeight, $option) {

			switch ($option) {
				case 'exact':
					$optimalWidth = $newWidth;
					$optimalHeight= $newHeight;
					break;
				case 'portrait':
					$optimalWidth = $this->getSizeByFixedHeight($newHeight);
					$optimalHeight= $newHeight;
					break;
				case 'landscape':
					$optimalWidth = $newWidth;
					$optimalHeight= $this->getSizeByFixedWidth($newWidth);
					break;
				case 'auto':
					$optionArray = $this->getSizeByAuto($newWidth, $newHeight);
					$optimalWidth = $optionArray['optimalWidth'];
					$optimalHeight = $optionArray['optimalHeight'];
					break;
				case 'crop':
					$optionArray = $this->getOptimalCrop($newWidth, $newHeight);
					$optimalWidth = $optionArray['optimalWidth'];
					$optimalHeight = $optionArray['optimalHeight'];
					break;
			}

			return array('optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight);

		}

		## --------------------------------------------------------
		private function getSizeByFixedHeight($newHeight) {
			$ratio = $this->width / $this->height;
			$newWidth = $newHeight * $ratio;
			return $newWidth;
		}

		## --------------------------------------------------------
		private function getSizeByFixedWidth($newWidth)	{
			$ratio = $this->height / $this->width;
			$newHeight = $newWidth * $ratio;
			return $newHeight;
		}

		## --------------------------------------------------------
		private function getSizeByAuto($newWidth, $newHeight) {

			if ($this->height < $this->width) {

				// *** Image to be resized is wider (landscape)
				$optimalWidth = $newWidth;
				$optimalHeight= $this->getSizeByFixedWidth($newWidth);

			} elseif ($this->height > $this->width) {

				// *** Image to be resized is taller (portrait)
				$optimalWidth = $this->getSizeByFixedHeight($newHeight);
				$optimalHeight= $newHeight;

			} else {

				// *** Image to be resizerd is a square
				if ($newHeight < $newWidth) {

					$optimalWidth = $newWidth;
					$optimalHeight= $this->getSizeByFixedWidth($newWidth);

				} else if ($newHeight > $newWidth) {

					$optimalWidth = $this->getSizeByFixedHeight($newHeight);
					$optimalHeight= $newHeight;

				} else {

					// *** Sqaure being resized to a square
					$optimalWidth = $newWidth;
					$optimalHeight= $newHeight;

				}
			}

			return array('optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight);
		}

		## --------------------------------------------------------
		private function getOptimalCrop($newWidth, $newHeight) {

			$heightRatio = $this->height / $newHeight;
			$widthRatio  = $this->width /  $newWidth;

			if ($heightRatio < $widthRatio) {
				$optimalRatio = $heightRatio;
			} else {
				$optimalRatio = $widthRatio;
			}

			$optimalHeight = $this->height / $optimalRatio;
			$optimalWidth  = $this->width  / $optimalRatio;

			return array('optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight);
		}

		## --------------------------------------------------------
		private function crop( $optimalWidth, $optimalHeight, $newWidth, $newHeight, $opt = array() ) {

			// *** Find center - this will be used for the crop
			if ( $opt['cropY'] < 0 AND $opt['cropX'] < 0 ) {

				$opt['cropX'] = ($optimalWidth / 2) - ($newWidth / 2);
				$opt['cropY'] = ($optimalHeight / 2) - ($newHeight / 2);

			} else { ## Fix image position problems

				$opt['cropX'] = $opt['cropX'] * 2;
				$opt['cropY'] = $opt['cropY'] * 2;

			}

			$crop = $this->imageResized;

			// *** Create back
			$this->imageResized = imagecreatetruecolor($newWidth, $newHeight);

			// *** Preserve Alpha
			imagealphablending($this->imageResized, false);
			imagesavealpha($this->imageResized, true);

			// *** Now resample image
			imagecopyresampled($this->imageResized, $crop , 0, 0, $opt['cropX'], $opt['cropY'], $newWidth, $newHeight , $newWidth, $newHeight);

		}

		## --------------------------------------------------------
		public function save ( $savePath, $imageQuality = 100 ) {

			// Exit on error
			if ($this->image == false) { return false; }

			// *** Get extension
			$extension = strtolower(strrchr($savePath, '.'));

			switch($extension) {

				case '.jpg':
				case '.jpeg':

					if ( imagetypes() & IMG_JPG ) {
						$return = imagejpeg($this->imageResized, $savePath, $imageQuality);
					}

					break;

				case '.gif':

					if ( imagetypes() & IMG_GIF ) {
						$return = imagegif($this->imageResized, $savePath);
					}

					break;

				case '.png':

					// *** Scale quality from 0-100 to 0-9
					$scaleQuality = round(($imageQuality/100) * 9);

					// *** Invert quality setting as 0 is best, not 9
					$invertScaleQuality = 9 - $scaleQuality;

					if ( imagetypes() & IMG_PNG ) {
						$return = imagepng($this->imageResized, $savePath, $invertScaleQuality);
					}

					break;
					// ... etc

				default:
					// *** No extension - No save.
					break;
			}

			$this->compress($savePath);
			imagedestroy($this->imageResized);
			return $return;

		}


		// *** Static function to make resize easier and faster
		public static function handle ( $image, $size, $mode = null, $newfilename = null, $opt = null ) {

			if ( !is_file($image) ) return false;

			$object = new self($image);
			$object->resize($size, $mode, $opt);
			$object->save((!empty($newfilename)) ? $newfilename : $image);

		}


		// Optimize image via JPEGOPTIM OR PNGQUANT (image::compress($path);)
		public function compress ( $file ) {

			$extension = strtolower(strrchr($file, '.'));

			// Match JPEG/JPG Extension
			if ( $extension == '.jpeg' || $extension == '.jpg' ) {

				// Try to use JPEG Optim to loselessly compress image
				if ( $this->opts['jpegoptim'] !== false && is_file( $this->opts['jpegoptim'] ) && is_file($file) ) {

					return	exec ( "{$this->opts['jpegoptim']} --strip-all --all-normal -o -q -p " . realpath(getcwd()) . "/{$file}" );

				}

				// Match PNG
			} else if ( $extension == '.png' ) {

				// Try to use PNGQuant to loselessly compress image
				if ( $this->opts['pngquant'] !== false && is_file( $this->opts ) && is_file($file) ) {

					return exec ( "{$this->opts['pngquant']} -f --speed 1 " . realpath(getcwd()) . "/{$file} --output " . realpath(getcwd()) . "/{$file}" );

				}

			}

			return false;

		}

	}
?>