<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Crop_Image extends CI_Controller{

	public $gallery_path = 'your/path/in/here';

	protected function create_directory($dir = '')
	{
		if($dir == '')
		{
			$umask = umask(0);
			mkdir($this->gallery_path.'/'.date('Y/m'), 0777);
			umask($umask);
			return TRUE;
		}
		else
		{
			if(!is_dir($this->gallery_path.'/'.substr_replace($dir, '', 0,1)))
			{
				$umask = umask(0);
				mkdir($this->gallery_path.'/'.substr_replace($dir, '', 0,1), 0777);
				umask($umask);
				return TRUE;
			}
			else
			{
				return TRUE;
			}
		}
	}
	}

	private function rand_string($length = 6, $str_rand = '0123456789')
	{
		for ($s = '', $cl = strlen($str_rand)-1, $i = 0; $i < $length; $s .= $str_rand[mt_rand(0, $cl)], ++$i);
     	return $s;
	}

	public function do_upload()
	{

		if(!is_dir($this->gallery_path.'/'.date('Y/m')))
		{
			$this->create_directory();
		}
		
		$config['upload_path']	= $this->gallery_path.'/'.date('Y/m');
		$config['allowed_types']= 'gif|jpeg|jpg|png';
		$config['max_filename']	= 100;
		$config['file_name']	= $this->rand_string(60,'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVXYZ0123456789');
		$config['max_size']   	= 10096;
		$config['max_width']   	= 10000;
		$config['max_height']  	= 10000;
		
		$this->load->library('upload', $config);
		$uploaded = $this->upload->do_upload('image');
		
		if(!$uploaded)
		{
			//If you use JSON as feedback
			echo '{"status":false,"message":"'.$this->upload->display_errors().'"}';

			//not use JSON, you can return a value;
			//return $this->upload->display();
		}
		else
		{
			//call class CImage
			$this->load->library('cimage');
		
			$_config['source'] 			= $this->upload->data('full_path');
			$_config['path_destination']= $this->gallery_path.'/'.date('Y/m');
			$_config['size_source'] 	= '60x62|75x75|150x150|268x273|300x225|500x233|730x340|1024x768';
			$_config['image_quality'] 	= 90;
			$_config['base_coordinate'] = 'center';
			$_config['rename_random'] 	= FALSE;
			
			if(!$this->cimage->crop($_config))
			{
				//You can return value if you are not use JSON
				echo '{"status":false,"ext":"'.$this->upload->data('file_ext').'","name":"'.$this->upload->data('raw_name').'","message":"'.$this->cimage->view_error().'. If you want to remove the image, click: "}';
			}
			else
			{
				/*
				* Call a method in model
				* This function to store the image indentity has uploaded
				*/

				$stored = $this->image_model->add_multi_images();
				//$stored = TRUE;
				if(!$stored)
				{
					//You can return value if you are not use JSON
					echo '{"status":false,"ext":"'.$this->upload->data('file_ext').'","name":"'.$this->upload->data('raw_name').'","message":"Image has uploaded but can not stored in database. If you want to remove the image, click: "}';
				}
				else
				{
					//You can return value if you are not use JSON
					echo '{"status":true,"ext":"'.$this->upload->data('file_ext').'","name":"'.$this->upload->data('raw_name').'","message":"Image has uploaded and stored in database. If you want to remove the image, click: "}';
				}
			}
		}
	}
}