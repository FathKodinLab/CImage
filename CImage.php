<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
* Image Manipulation Class
*
* @author   Fathan kodin['lab'], nanoDEV
* @category Custom Image Library
* @link     -
* @version  1.0.0
*/

class CImage{

    /**
     * Path or URL to original image
     *
     * @var string
     */
    public $source = '';

    /**
     * Temp path to image has cropped
     *
     * @var array
     */
    public $new_source = [];

    /**
     * Path to destination image will be cropped or resized
     *
     * @var string
     */
    public $path_destination = '';

    /**
     * Original image width value
     *
     * @var int
     */
    public $width_source = 0;

    /**
     * Orignial image height value
     *
     * @var int
     */
    public $height_source = 0;

    /**
     * Image width value to modified
     *
     * @var int
     */
    public $width_destination = 0;

    /**
     * Image width value to modified
     *
     * @var int
     */
    public $height_destination = 0;

    /**
     * Ratio or gradient value to every image
     *
     * @var int
     */
    public $ratio = 0;

    /**
     * Image quality modified
     * Default image quality is 85 (jpeg level quality)
     *
     * @var int
     */
    public $image_quality = 85;

    /**
     * Image sizes to modified (in pixel)
     * example format: 60x62 (single size)
     *                 60x62|75x75|135x125 ..etc (multi size)
     * @var string
     */
    public $size_source = '';

    /**
     * Image sizes have extracted from variabel size_source
     *
     * @var array
     */
    public $size_fixed = [];

    /**
     * Original or random image name
     *
     * @var string
     */
    public $image_name = '';

    /**
     * Extension image
     *
     * @var string
     */
    public $image_ext = '';

    /**
     * Type image index such as 1 => gif, 2 => jpeg, or 3 => png
     * Default type image is jpeg
     *
     * @var int
     */
    public $image_type = 2;

    /**
     * Temp to error message
     *
     * @var array
     */
    public $message_error = [];

    /**
     * Whether to create random name for new image (have modified)
     * 
     * @var bool
     */
    public $rename_random = FALSE;

    /**
     * Base coordinate as reference to start cropped
     *
     * @var string
     */
    public $base_coordinate = '';

    /**
     * Temporary output data
     *
     * @var array
     */
    public $temp_output = array();

    /**
     * Coordinate x start of original image
     * Coordinate x is represent the image width
     *
     * @var int
     */
    public $x1 = 0;

    /**
     * Coordinate y start of original image
     * Coordinate y is represent the image height
     *
     * @var int
     */
    public $y1 = 0;

    /**
     * Coordinate x2 is the coordinate x end (destination) of original image
     * This coordinate depend the width destination image
     * So, we can say that coordinate x2 is the value of coordinate x1 added with width destination image,
     * but they are still engage by computing the ratio or gradient
     *
     * @var int
     */
    public $x2 = 0;

    /**
     * Coordinate y2 is same with about coordinate x end (x1).
     * It's like coordinate y1 added with height destination.
     *
     * @var int
     */
    public $y2 = 0;

    /**
     * String function name library to create image from PATH or URL
     * The function is imagecreatefromgif, imagecreatefromjpeg, or imagecreatefrompng
     *
     * @var string
     */
    public $func_image_create = '';

    /**
     * String function name library to output image into browser or file
     * The function is imagegif, imagejpeg, or imagepng
     *
     * @var string
     */
    public $func_image_final = '';

    /**
     * Setting the content of header to send a raw HTTP header
     * It's contain Identities of image
     *
     * @var string
     */
    public $header = '';

    /**
     * Whether to display the image has modified
     * 
     *
     * @var bool
     */
    public $image_view = FALSE;

    /**
     * Constant value for image type
     *
     * @var array
     */
    protected const EXTENSION_SUPPORTED = array(1 => 'gif', 2 => 'jpg', 3 => 'png');

    /**
     * Constant value for base coordinate
     *
     * @var array
     */
    protected const COORDINATE_BASE = ['top_left', 'top_center', 'top_right', 'left_center', 'center', 'right_center', 'bottom_left', 'bottom_center', 'bottom_right'];


    public function __construct()
    {
        ini_set('gd.jpeg_ignore_warning', 1);

        /** 
        * @crop() method, there are a loop process to cropping images
        * It's sometime needs a execution long term,
        * you can change the max time execute by set_time_limit library function.
        * ex: set_time_limit(300), so it's give 300 seconds as the max time to executed.
        */

        set_time_limit(0);
    }

    //++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    /** 
    * Filtering data input
    * 
    * 
    * @param array
    * @return bool
    */
    protected function filter_data_input($data)
    {
        $status = array();

        if(!is_array($data))
        {
            $this->set_error('data_input', NULL);
            return FALSE;
        }
        else
        {
            $index = ['source', 'path_destination', 'size_source', 'image_quality', 'rename_random', 'base_coordinate', 'image_view'];

            $imagesize = function($source)
            {
                return getimagesize($source);
            };

            foreach($data as $key => $val)
            {
                if(!in_array($key, $index))
                {
                    $this->set_error('unknown_index', $key);
                    return FALSE;
                }

                switch($key)
                {
                    case 'source':

                        //is none? -___-
                        if(empty($val))
                        {
                            $this->set_error('source_empty', NULL);
                            return FALSE;
                        }

                        /**
                        * We should know the image source from where?
                        *               ---START---
                        * The first:: If image source exists or "is_file" TRUE, it's source a PATH.
                        * But if source is detected not exists, maybe it's a URL. Right?
                        * The second::
                        *   ### PATH.   Check the extension and check the image type
                        *   ### URL.    Check valid URL, check the extension,
                        *               check connection or request HTTP via cURL then
                        *               identify the status connection and content page.
                        *               Check the image type if before have passed.
                        *               ----END---
                        */
                        if(!is_file($val))
                        {
                            //filter valid URL
                            if(filter_var($val, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED) === FALSE)
                            {
                                $this->set_error('image_source', NULL);
                                return FALSE;
                            }

                            $ext = explode('.', $val);
                            $end = end($ext);

                            if(!in_array($end, CImage::EXTENSION_SUPPORTED))
                            {
                                $this->set_error('extension_unsupported', $end);
                                return FALSE;
                            }

                            //Test connection to URL
                            $curl = curl_init();
                            
                            curl_setopt($curl, CURLOPT_URL, $val);
                            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

                            //execute response
                            $exec   = curl_exec($curl);

                            //maybe some characters can found if access the URL is error
                            $error  = ['Error', 'error', 'errors', '404', 'error 404', 'err', 'ERR', 'ERROR', 'error not found', '404 not found', 'not found', 'forbidden', 'server error', 'cannot be displayed', 'it contains errors', 'halaman tidak ada', 'halaman hilang', 'halaman tidak ditemukan'];
                            $status = TRUE;
                            
                            foreach($error as $err)
                            {
                                //if the chracters is found, feedback the position is bigger than zero
                                if(strpos($exec, $err) > 0)
                                {
                                    $status = FALSE;
                                    break;
                                }
                            }

                            //if the URL can not to access, return FALSE
                            if($status === FALSE)
                            {
                                $this->set_error('url_cannot_access', NULL);
                                return FALSE;
                            }
                            else
                            {
                                //if true URL, we test again the content.
                                $data = file_get_contents($val, true);

                                //If length of string/content less than 1000, maybe it's too small image.
                                //So, return false when the string length less than 1000.
                                if(strlen($data) < 1000)
                                {
                                    $this->set_error('content_size_too_small', NULL);
                                    return FALSE;
                                }
                            }
                            
                            // PASS ANYWAY? O...really? +_+
                            // Test again, is it valid image or not?
                            // Check it by getimagesize
                            if(!in_array($imagesize($data['source'])[2] , array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG)))
                            {
                                $this->set_error('image_unsupported', NULL);
                                return FALSE;
                            }
                        }
                        else
                        {
                            //Filter PATH
                            $ext = explode('.', $val);
                            $end = end($ext);

                            //Check extension is supported
                            if(!in_array($end, CImage::EXTENSION_SUPPORTED))
                            {
                                $this->set_error('extension_unsupported', $end);
                                return FALSE;
                            }
                            
                            //Is valid image?
                            if(!in_array($imagesize($data['source'])[2] , array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG)))
                            {
                                $this->set_error('image_unsupported', NULL);
                                return FALSE;
                            }
                        }
                    break;
                    case 'path_destination':

                        //Is directory?
                        if(!is_dir($val))
                        {
                            $this->set_error('path_destination', $val);
                            return FALSE;
                        }
                    break;
                    case 'size_source':

                        /**
                        * Format size_source is "width x height", where width and height value is integer,
                        * and for multiple size use the delimiter "|", it's like this: 
                        * "60x62|75x75|768x600"
                        */
                        if(!empty($val))
                        {
                            $split = str_split($val);
                            $char = '';
                            $obj = ['0','1','2','3','4','5','6','7','8','9','x','|'];

                            
                            foreach($split as $s)
                            {
                                if(in_array($s, $obj) == FALSE)
                                {
                                    if(strlen($char) > 0)
                                    {
                                        $char .= ','.$s;
                                    }
                                    else
                                    {
                                        $char = $s;
                                    }
                                }
                            }

                            //if character outside of the rules
                            if($char != '')
                            {
                                $this->set_error('size_format', $char);
                                return FALSE;
                            }
                            else
                            {
                                $new_value = '';

                                //check delimiter position. if true, it's possible multiple size
                                if(strpos($val, '|') > 2)
                                {
                                    $explode    = explode('|', $val);
                                    $length     = sizeof($explode);
                                    
                                    if(empty(end($explode)) || strrpos($val, '|') == strlen($val)+1)
                                    {
                                        $this->set_error('size_format_delimiter', substr($val, -10));
                                        return FALSE;
                                    }
                                    else
                                    {
                                        $new_value = $explode;
                                    }
                                }
                                else
                                {
                                    $new_value = [$val];
                                }

                                //If a size and check the format again
                                if(strrpos($val, '|') != strlen($val)+1)
                                {
                                    foreach($new_value as $nv)
                                    {
                                        $_explode   = explode('x', $nv);
                                        $_length    = sizeof($_explode);

                                        if($_length != 2)
                                        {
                                            $this->set_error('size_format_value', $nv);
                                            return FALSE;
                                        }
                                    }
                                }
                            }
                        }
                    break;
                    case 'image_quality':

                        //PNG has quality level from 0 up to 9
                        if($imagesize($data['source'])[2] === 3 && $val > 9)
                        {
                            $this->set_error('image_quality', 'PNG image type have compression level 0 through 9');
                            return FALSE;
                        }

                        //Otherwise JPEG and GIF have quality level value from 0 up to 100
                        if(!is_int($val) || $val < 0 || $val > 100)
                        {
                            $this->set_error('image_quality', NULL);
                            return FALSE;
                        }
                    break;
                    case 'rename_random':

                        //Nothing to explain, you have knew without tell you. LOL :D
                        if(!is_bool($val))
                        {
                            $this->set_error('rename_random', NULL);
                            return FALSE;
                        }
                    break;
                    case 'base_coordinate':

                        //Check exists the base coordinate
                        if(!in_array($val, CImage::COORDINATE_BASE))
                        {
                            //If use custom base coordinate
                            //It's format looked same with the size source format
                            preg_match('/([0-9]+)x([0-9]+)/', $val, $match);
                            
                            if(sizeof($match) != 3)
                            {
                                $this->set_error('base_coordinate', NULL);
                                return FALSE;
                            }

                            list($w, $h) = $imagesize($data['source']);
                         
                            if($w <= $match[1] || $h <= $match[2])
                            {
                                $this->set_error('custom_base_coordinate', '. Image source (width: '.$w.', height: '.$h.') | Base coordinate inputed (x: '.$match[1].', y: '.$match[2].')');
                                return FALSE;
                            }
                        }
                    break;
                    case 'image_view':

                        //Want to display image after cropped without saved it?
                        //You must put true
                        if(!is_bool($val))
                        {
                            $this->set_error('image_view_value');
                        }
                    break;
                }
            }
        }

        //Initialize the ALL
        foreach($data as $key => $val)
        {
            $this->$key = $val;
        }

        return TRUE;
    }

    //++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    /** 
    * Image Crop Method
    *  _____________________________    ____________________________
    * |                             |  |        |          |        |
    * |  C_________                 |  |        |top_center|        |
    * |  |         |                |  |        |__________|        |
    * |  |         |                |  |                            |
    * |  |_________|                |  |                            |
    * |_____________________________|  |____________________________|
    *
    * >| On the illustration above, "C" is the custom base coordinate,
    *       but If you use the base coordinate is "center" (top_center, left_center, center, etc),
    *       it's with the view of put object on the center position of original image source.
    *       Ex: "top_center", the position on top and center. "center", on center or diagonal coordinate.
    * >| Two steps cropping image (by size_source):
    *       ### Cropping under the base coordinate and ratio
    *       ### Resize (go to resize_image()) the image cropped according dimension destination (size source image)
    *           If the value of display image to browser is TRUE, view image via header HTTP and
    *           unlink image resized. If the display value is FALSE, save it as file image.
    *           How many images to do crop? At any quantity, do it at will!
    * >| In this case, Image Crop Method can crop an image become some images which you want.
    * 
    * @param array
    * @return bool
    */
    public function crop($data_input = [])
    {
        //call filter_data_input for filtering data input
        $data_filter = $this->filter_data_input($data_input);

        if($data_filter === TRUE)
        {
            //Call Extract_size method. So cool? :D
            //What's is it? Scroll down and find it.
            $this->extract_size();

            $explode    = explode('/', str_replace('\\', '/', $this->source));
            $_explode   = explode('.', end($explode));

            //Create random name
            if($this->rename_random === TRUE)
            {
                $this->image_name = $this->random_name();
            }
            else
            {
                $this->image_name = $_explode[0];
            }
            
            list($this->width_source, $this->height_source, $this->image_type) = getimagesize($this->source);

            //If custom base coordinate
            //Size image source is less the custom base coordinate value
            if(!in_array($this->base_coordinate, CImage::COORDINATE_BASE))
            {
                preg_match('/([0-9]+)x([0-9]+)/', $this->base_coordinate, $match);
                
                $this->width_source     -= $match[1];
                $this->height_source    -= $match[2];
            }

            $this->image_ext = CImage::EXTENSION_SUPPORTED[$this->image_type];
            
            //Some GD2 function initialized into initialize_func method
            $this->initialize_func();

            //Image will be cropped as much as contain of size_fixed property
            $i = 0;
            foreach($this->size_fixed as $sf)
            {

                if($this->image_view === TRUE && $i >= 1)
                {
                    break;
                }

                //Ratio or gradient is calculated and initialized as property global
                $this->calculate_ratio($sf);

                //Crop image tobe new iamge, but not yet resized
                $new_image = $this->create_new_image($sf);

                $i++;
                
                if($new_image === FALSE)
                {
                    return FALSE;
                }
            }

            //After cropping end and none FALSE return, image resized
            $image_resize = $this->resize_image();

            if($image_resize === FALSE)
            {
                return FALSE;
            }

            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }

    //++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    /**
    * Reinitialized property to default
    * All of dynamic property
    *
    * @return void
    */
    public function resets()
    {
        $this->source = '';
        $this->new_source = '';
        $this->path_destination = '';
        $this->width_source = 0;
        $this->height_source = 0;
        $this->width_destination = 0;
        $this->height_destination = 0;
        $this->ratio = 0;
        $this->image_quality = 85;
        $this->size_source = '';
        $this->size_fixed = [];
        $this->image_name = '';
        $this->image_ext = '';
        $this->image_type = 2;
        //$this->message_error = array();
        $this->rename_random = FALSE;
        $this->base_coordinate = '';
        $this->x1 = 0;
        $this->y1 = 0;
        $this->x2 = 0;
        $this->y2 = 0;
    }

    //++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    /**
     * Initialize Some function
     *
     * @return  void
     */
    private function initialize_func()
    {
        switch($this->image_type)
        {
            case 1:
                $this->func_image_create    = "imagecreatefromgif";
                $this->func_image_final     = "imagegif";
                $this->header               = "Content-type: image/gif";
            break;
            case 2:
                $this->func_image_create    = "imagecreatefromjpeg";
                $this->func_image_final     = "imagejpeg";
                $this->header               = "Content-type: image/jpeg";
            break;
            case 3:
                $this->func_image_create    = "imagecreatefrompng";
                $this->func_image_final     = "imagepng";
                $this->header               = "Content-type: image/png";
            break;
        }
    }

    //++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    /**
     * Create new image method
     * Use this method to crop image
     *
     * @param   array
     * @return  bool
     */
    protected function create_new_image($size)
    {
        $this->calculate_coordinate();
        
        if (extension_loaded('gd') === TRUE && function_exists('imagecreatetruecolor'))
        {
            $truecolor      = 'imagecreatetruecolor';
            $imagecopy      = 'imagecopyresampled';
        }
        else
        {
            $truecolor      = 'imagecreate';
            $imagecopy      = 'imagecopyresized';
        }
        
        $image_source = $this->create_func_image($this->source, $this->func_image_create);
        
        if($image_source === FALSE)
        {
            return FALSE;
        }

        $image_destination = $this->create_base_image($this->width_destination, $this->height_destination, $truecolor);

        if($image_destination === FALSE)
        {
            return FALSE;
        }

        $rename = $this->path_destination.'/'.$this->image_name.'-'.$size[0].'x'.$size[1].'.'.$this->image_ext;

        if($this->image_type === 3 || $this->image_type === 1)
        {
            imagecolortransparent($image_destination, imagecolorallocatealpha($image_destination, 0, 0, 0, 127));
            imagealphablending($image_destination, FALSE);
            imagesavealpha($image_destination, TRUE);
        }

        $imagecopy($image_destination, $image_source, 0, 0, $this->x1, $this->y1, $this->x2, $this->y2, $this->x2, $this->y2);

        $final_image = $this->create_final_image($image_destination, $rename, $this->func_image_final, FALSE);

        if($final_image === FALSE)
        {
            return FALSE;
        }

        imagedestroy($image_destination);
        imagedestroy($image_source);
        
        return TRUE;
    }

    //++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    /**
     * Create new blank image method
     * Use this method to create new image with gif, jpeg, or png
     * This method returns an image identifier representing 
     * the image obtained from the given filename
     *
     * @param   string
     * @param   string
     * @return  bool (FALSE) if failure
     * @return  resource (type of GD (image identifier)) if success
     */
    private function create_func_image($path, $func)
    {
        if(!function_exists($func))
        {
            $this->set_error('gd2_not_installed', NULL);
            return FALSE;
        }

        return $func($path);
    }

    //++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    /**
     * Create new new true color method
     * Use this method to create new black image
     * This method returns an image identifier representing 
     * a black image of the specified size
     *
     * @param   int
     * @param   int
     * @param   string
     * @return  bool (FALSE) if failure
     * @return  resource (type of GD (Image identifier)) if success
     */
    private function create_base_image($width, $height, $func)
    {
        if(!function_exists('imagecreatetruecolor'))
        {
            $this->set_error('gd2_not_installed', NULL);
            return FALSE;
        }

        return $func($width, $height);
    }

    //++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    /**
     * Output an image to either browser or file
     * If you want view image on browser,
     *  use header to identify an image and don't put path
     * If you save image as file,
     *  put path
     *
     * @param   resource (type of GD (Image identifier))
     * @param   resource (type of GD (Image identifier))
     * @param   string
     * @param   string
     * @param   bool
     * @return  bool
     */
    private function create_final_image($destination, $path, $func, $view)
    {
        if(!function_exists($func))
        {
            $this->set_error('gd2_not_installed', NULL);
            return FALSE;
        }

        if($this->image_type === 3 && $this->image_quality > 9)
        {
            $this->image_quality = 9;
        }
        
        if($this->image_view === TRUE)
        {
            if($view === TRUE)
            {
                header($this->header);
                $func($destination, NULL, $this->image_quality);
                unlink($path);
            }
            else
            {
                $func($destination, $path, $this->image_quality);
            }
        }
        else
        {
            $func($destination, $path, $this->image_quality);
        }

        //path to create new file and it's reuse in processing resize image
        $this->new_source[] = $path;
        return TRUE;
    }

    //++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    /**
     * Resize Image Method
     * 
     * @return  bool
     */
    protected function resize_image()
    {
        if(!empty($this->new_source))
        {
            $this->extract_size($this->new_source);
            
            if (extension_loaded('gd') && function_exists('imagecreatetruecolor'))
            {
                $truecolor      = 'imagecreatetruecolor';
                $imagecopy      = 'imagecopyresampled';
            }
            else
            {
                $truecolor      = 'imagecreate';
                $imagecopy      = 'imagecopyresized';
            }
            
            $i = 0;
            foreach($this->new_source as $ns)
            {
                list($width_new, $height_new) = getimagesize($ns);

                $image_source = $this->create_func_image($ns, $this->func_image_create);

                if($image_source === FALSE)
                {
                    return FALSE;
                }

                $image_destination = $this->create_base_image($this->size_fixed[$i][0], $this->size_fixed[$i][1], $truecolor);

                if($image_destination === FALSE)
                {
                    return FALSE;
                }

                $image_destination_name = $this->image_name.'-'.$this->size_fixed[$i][0].'x'.$this->size_fixed[$i][1].'.'.$this->image_ext;
                $path                   = $this->path_destination.'/'.$image_destination_name;

                //Transparancy enabled
                if($this->image_type === 3 || $this->image_type === 1)
                {
                    imagecolortransparent($image_destination, imagecolorallocatealpha($image_destination, 0, 0, 0, 127));
                    imagealphablending($image_destination, FALSE);
                    imagesavealpha($image_destination, TRUE);
                }

                $imagecopy($image_destination, $image_source, 0, 0, 0, 0, $this->size_fixed[$i][0], $this->size_fixed[$i][1], $width_new, $height_new);

                $final = $this->create_final_image($image_destination, $path, $this->func_image_final, $this->image_view);

                if($final === FALSE)
                {
                    return FALSE;
                }

                $this->temp_output[$this->size_fixed[$i][0].'x'.$this->size_fixed[$i][1]] = $image_destination_name;
                $i++;
                
                imagedestroy($image_destination);
                imagedestroy($image_source);
            }

            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }

    //++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    /**
     * Calculate Coordinate Method
     * 
     *  
     * @return  void
     */
    protected function calculate_coordinate()
    {
        switch($this->base_coordinate)
        {
            case 'top_left':
                $this->x1 = 0;
                $this->y1 = 0;
                $this->x2 = $this->width_destination;
                $this->y2 = $this->height_destination;
            break;
            case 'top_center':
                $this->x1 = ($this->width_source - $this->width_destination) / 2;
                $this->y1 = 0;
                $this->x2 = $this->width_destination + $this->x1;
                $this->y2 = $this->height_destination;
            break;
            case 'top_right':
                $this->x1 = $this->width_source - $this->width_destination;
                $this->y1 = 0;
                $this->x2 = $this->width_source;
                $this->y2 = $this->height_destination;
            break;
            case 'left_center':
                $this->x1 = 0;
                $this->y1 = ($this->height_source - $this->height_destination) / 2;
                $this->x2 = $this->width_destination;
                $this->y2 = $this->height_destination + $this->y1;
            break;
            case 'center':
                $this->x1 = ($this->width_source - $this->width_destination) / 2;
                $this->y1 = ($this->height_source - $this->height_destination) / 2;
                $this->x2 = $this->width_destination + $this->x1;
                $this->y2 = $this->height_destination + $this->y1;
            break;
            case 'right_center':
                $this->x1 = $this->width_source - $this->width_destination;
                $this->y1 = ($this->height_source - $this->height_destination) / 2;
                $this->x2 = $this->width_source;
                $this->y2 = $this->height_destination + $this->y1;
            break;
            case 'bottom_left':
                $this->x1 = 0;
                $this->y1 = $this->height_source - $this->height_destination;
                $this->x2 = $this->width_destination;
                $this->y2 = $this->height_destination;
            break;
            case 'bottom_center':
                $this->x1 = ($this->width_source - $this->width_destination) / 2;
                $this->y1 = $this->height_source - $this->height_destination;
                $this->x2 = $this->width_destination + $this->x1;
                $this->y2 = $this->height_destination + $this->y1;
            break;
            case 'bottom_right':
                $this->x1 = ($this->width_source - $this->width_destination) / 2;
                $this->y1 = $this->height_source - $this->height_destination;
                $this->x2 = $this->width_destination + $this->x1;
                $this->y2 = $this->height_destination + $this->y1;
            break;
            default:
                preg_match('/([0-9]+)x([0-9]+)/', $this->base_coordinate, $match);
                $this->x1 = $match[1];
                $this->y1 = $match[2];
                $this->x2 = $this->width_source;
                $this->y2 = $this->height_source;
            break;
        }
    }

    //++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    /**
     * Extract Size Method
     * 
     *  
     * @return  void
     */
    protected function extract_size()
    {
        $position   = strpos($this->size_source, '|');
        $free_space = str_replace(' ', '', $this->size_source);
        
        if($position > 0)
        {
            $explode    = explode('|', $free_space);
            $length     = sizeof($explode);

            foreach($explode as $expl)
            {
                preg_match('/([0-9]+)x([0-9]+)/', $expl, $match);
                $this->size_fixed[] = array($match[1], $match[2]);
            }
        }
        else
        {
            preg_match('/([0-9]+)x([0-9]+)/', $this->size_source, $match);

            $this->size_fixed[] = array($match[1], $match[2]);
        }
    }

    //++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    /**
     * Calculate Ratio Method
     * 
     * @param   array
     * @return  void
     */
    protected function calculate_ratio($size)
    {
        $this->width_destination    = $size[0];
        $this->height_destination   = $size[1];

        if($this->width_destination/$this->height_destination < $this->width_source/$this->height_source)
        {
            $this->ratio = $this->height_source / $this->height_destination;
        }
        else
        {
            $this->ratio = $this->width_source / $this->width_destination;
        }

        $this->width_destination    = $this->width_destination * $this->ratio;
        $this->height_destination   = $this->height_destination * $this->ratio;
    }

    //++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    /**
     * Random Name Method
     * 
     *  
     * @return  string
     */
    private function random_name()
    {
        $length_destination = 45;
        $source             = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $str_random         = '';
        $length_source      = strlen($source)-1;
                
        for($i = 0; $i<$length_destination; ++$i)
        {
            $str_random .= $source[mt_rand(0,$length_source)];
        }

        return $str_random;
    }

    //++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    /**
     * Data Method
     * 
     * @param   string
     * @return  string (if parameter is not empty)
     * @return  array (if parameter is empty)
     */
    public function data($index = '')
    {
        if($index == '')
        {
            return $this->temp_output;
        }
        else
        {
            return $this->temp_output[$index];
        }
    }

    //++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    /**
     * Setting Error Method
     * 
     *  
     * @return  void
     */
    public function set_error($msg = NULL, $field = NULL)
    {
        //*
        //error message language start

        $lang['source_not_exist'] = 'Source image is not exists';
        $lang['not_image'] = 'Source is not image';
        $lang['source_empty'] = 'Path or URL is required';
        $lang['url_cannot_access'] = 'URL can not accessed';
        $lang['image_unsupported'] = 'Image source is unsupported';
        $lang['extension_unsupported'] = 'Extension "{field}" is unsupported';
        $lang['content_size_too_small'] = 'Content size too small';
        $lang['rename_random'] = 'Rename random value is not correct';
        $lang['image_quality'] = 'Quality value is not correct. {field}';
        $lang['base_coordinate'] = 'Base coordinate value is not correct';
        $lang['custom_base_coordinate'] = 'Base coordinate is as or exeeded the image size {field}';
        $lang['data_input'] = 'You must input data by array';
        $lang['unknown_index'] = 'Index {field} is unknown';
        $lang['image_source'] = 'Image source is not found';
        $lang['path_destination'] = 'Path destination "{field}" is not exists';
        $lang['size_format'] = 'Size Source Format: character "{field}" can not be applied';
        $lang['size_format_delimiter'] = 'Size Source Format: delete the delimiter "|" of the end string: " ... {field}"';
        $lang['size_format_value'] = 'Size Source Format: size of {field} is not correct';
        $lang['gd2_not_installed'] = 'Library GD2 is not installed or not actived, or image library is using unsupported';

        //error message language end
        //*/

        /**
        * If you want to add the error message language to CI Framework,
        * copy/cut and paste the error message code above into imglib_lang.php file.
        * It's path likely "system/language/english/imglib_lang.php".
        * Or you can also create your own language library file into application/language directory.
        * It's path likely "application/language/english/yourlib_lang.php".
        */

        if(function_exists('get_instance'))
        {
            // This code just work on Codeigniter Framework
            $ci =& get_instance();

            //cimage_lang.php in directory indonesian
            $ci->lang->load('cimage','indonesian');

            if (is_array($msg))
            {
                foreach ($msg as $val)
                {
                    $msg = ($ci->lang->line($val) === FALSE) ? $val : $ci->lang->line($val);
                    $this->message_error[] = str_replace('{field}', $field, $msg);
                    log_message('error', $msg);
                }
            }
            else
            {
                $msg = ($ci->lang->line($msg) === FALSE) ? $msg : $ci->lang->line($msg);
                $this->message_error[] = str_replace('{field}', $field, $msg);
                log_message('error', $msg);
            }
        }
        else
        {
            //If you use CImage for native PHP
            $this->message_error[] = str_replace('{field}', $field, $lang[$msg]);
        }
    }

    //++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    /**
     * Display Error Message Method
     * 
     * @param   string
     * @param   string
     * @return  string
     */
    public function view_error($tag_start = '<p>', $tag_end = '</p>')
    {
        return (count($this->message_error) > 0) ? $tag_start.implode($tag_end.$tag_start, $this->message_error).$tag_end : '';
    }
}
?>