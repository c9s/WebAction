<?php
namespace WebAction\Param;

use WebAction\Param\Param;

/**
 * Preprocess image data fields
 *
 * This preprocessor takes image file columns,
 * copy these uploaded file to destination directory and
 * update the original file hash, So in the run method of
 * action class, user can simply take the hash arguments,
 * and no need to move files or validate size by themselfs.
 *
 * To define a Image Param column in Action schema:
 *
 *
 *  public function schema()
 *  {
 *     $this->param('image','Image')
 *          ->validExtensions('jpg','png');
 *  }
 *
 */

class ImageResizer
{
    public static $classes = array(
        'max_width'      => 'WebAction\\Param\\Image\\MaxWidthResize',
        'max_height'     => 'WebAction\\Param\\Image\\MaxHeightResize',
        'scale'          => 'WebAction\\Param\\Image\\ScaleResize',
        'crop_and_scale' => 'WebAction\\Param\\Image\\CropAndScaleResize',
    );

    public static function create($type, Param $param)
    {
        if (!isset(self::$classes[$type])) {
            throw new Exception("Image Resize Type '$type' is undefined.");
        }
        $c = self::$classes[$type];
        return new $c($param);
    }
}
