<?php

namespace PunchCMS;

use PunchCMS\Client\Client;
use Bili\FileIO;

/**
 *
 * Values of an image type field.
 * @author felix
 * @version 0.1.0
 *
 */
class ImageValue
{
    private $settings;
    private $src;
    private $path;
    private $original;
    private $alt;

    public function __construct($arrSettings)
    {
        $this->settings = $arrSettings;
    }

    public function setPath($strValue)
    {
        $this->path = $strValue;
    }

    public function getPath($strValue)
    {
        return $this->path;
    }

    public function setSrc($strValue)
    {
        $this->src = $strValue;
    }

    public function setOriginal($strValue)
    {
        $this->original = $strValue;
    }

    public function setAlt($strValue)
    {
        $this->alt = $strValue;
    }

    public function getSrc($strApiName = "")
    {
        $strReturn = "";

        if (empty($strApiName) || count($this->settings) < 2) {
            $strReturn = $this->path . $this->src;
        } else {
            foreach ($this->settings as $arrSetting) {
                if ($arrSetting['api'] == $strApiName) {
                    $strReturn = $this->path . FileIO::add2Base($this->src, $arrSetting['key']);
                    break;
                }
            }
        }

        return $strReturn;
    }

    public function getSize($strApiName = "")
    {
        //*** Return the width and height of an image field as an array.
        $arrReturn = array('width' => 0, 'height' => 0);

        $objCms = Client::getInstance();
        $strFile = $objCms->getBasePath() . $this->getSrc($strApiName);
        if (is_file($strFile)) {
            $arrTemp = getimagesize($strFile);
            $arrReturn['width'] = $arrTemp[0];
            $arrReturn['height'] = $arrTemp[1];
        }

        return $arrReturn;
    }

    public function getWidth($strApiName = "")
    {
        //*** Return the width of an image field as an integer.
        $arrSize = $this->getSize($strApiName);

        return $arrSize['width'];
    }

    public function getHeight($strApiName = "")
    {
        //*** Return the height of an image field as an integer.
        $arrSize = $this->getSize($strApiName);

        return $arrSize['height'];
    }

    public function getHtmlSize($strApiName = "")
    {
        //*** Return the width and height of an image field as an URL string.
        $arrSize = $this->getSize($strApiName);

        return "width=\"{$arrSize['width']}\" height=\"{$arrSize['height']}\"";
    }

    public function getOriginal()
    {
        return $this->original;
    }

    public function getAlt()
    {
        return $this->alt;
    }
}
