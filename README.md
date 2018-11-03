# CImage
Codeigniter custom library. CImage is like CI upload/crop/resize image. CImage focus on cropping all of position.
### 
    Image Manipulation Class
    @author   Fathan kodin['lab'], nanoDEV
    @category Custom Image Library
    @link     -
    @version  1.0.0
### 
# How to use?
1. You must declare the class using $this->load->library('cimage');
2. Inisializing, as example:
    $config['source'] = 'your/source/path/in/here'; //or your URL
    $config['path_destination']= 'your/destination/path/in/here';
    $config['size_source'] = '60x62|75x75|150x150|268x273|300x225|500x233|730x340|1024x768'; //size you want
    $config['image_quality'] = 90; //image qualitiy
    $config['base_coordinate'] = 'center'; //start position image cropping
    $config['rename_random'] = FALSE; //if you are not use random to rename put the FALSE
3. executing: $this->cimage->crop($config);
4. finish.

# Index inisialization:
    'source': source the image of computer or URL
    'path_destination': destination path image has cropped or resized
    'size_source': output size image in pixel. example: width 34px and height 70px, become 34x70. If the output image is more than 1 use "|".
    'image_quality': output image quality. The value is 0 until 100;
    'rename_random': create 45 characters for rename the output image. Data type is boolean, TRUE for rename or FALSE to not rename. Default value is FALSE.
    'base_coordinate': Start position image cropping. Base coordinate: top_left, top_center, top_right, left_center, center, right_center, bottom_left, bottom_center, and bottom_right. Base coordinate can also use the coordinat X and y in pixel such as 70x46. So, It is cropping at start width 70px and height 46px.
    'image_view': Display output image on the browser. Data type is boolean, TRUE for display on to browser and not created as file image, and FALSE for save output image without display on to browser. Default value is FALSE.
