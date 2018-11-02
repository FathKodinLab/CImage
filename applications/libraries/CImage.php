<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH.'controllers/AGallery.php';

class AImage extends AGallery{

	public function __construct()
	{
		parent::__construct();
		$this->load->model('image_model');
	}

	public function view_images()
	{
		$this->load->helper('source_helper');

		$data['title'] 		= 'Images List | SMA Negeri 1 Selong';
		$data['css_source'] = [['bootstrap','bootstrap.progressbar','nprogress','font.awesome/css','custom']];
		$data['js_source'] 	= [['bootstrap','bootstrap.progressbar','nprogress','custom']];
		$data['image_data'] = json_encode($this->image_model->get_all_image());
		$data['content'] 	= 'cpanel/media/all_images';
		$data['container'] 	= 'cpanel/media';

		$this->load->view('cpanel/templ', $data);
	}

	public function view_upload_image()
	{
		$this->load->helper('source_helper');
		$data['title'] 		= 'Images Upload | SMA Negeri 1 Selong';
		$data['css_source'] = [['bootstrap','bootstrap.progressbar','nprogress','font.awesome/css','custom','dropzone']];
		$data['js_source'] 	= [['bootstrap','bootstrap.progressbar','nprogress','custom','dropzone']];
		$data['content'] 	= 'cpanel/media/image_upload';
		$data['container'] 	= 'cpanel/media';

		$this->load->view('cpanel/templ', $data);
	}

	public function do_upload_image()
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
		$uploaded = $this->upload->do_upload('file');
		
		if(!$uploaded)
		{
			echo '{"status":false,"message":"'.$this->upload->display_errors().'"}';
		}
		else
		{
			$this->load->library('cimage');
		
			$_config['source'] 			= $this->upload->data('full_path');
			$_config['path_destination']= $this->gallery_path.'/'.date('Y/m');
			$_config['size_source'] 	= '60x62|75x75|150x150|268x273|300x225|500x233|730x340|1024x768';
			$_config['image_quality'] 	= 90;
			$_config['base_coordinate'] = 'center';
			$_config['rename_random'] 	= FALSE;
			
			if(!$this->cimage->crop($_config))
			{
				echo '{"status":false,"ext":"'.$this->upload->data('file_ext').'","name":"'.$this->upload->data('raw_name').'","message":"'.$this->cimage->view_error().'. If you want to remove the image, click: "}';
			}
			else
			{
				$stored = $this->image_model->add_multi_images();
				//$stored = TRUE;
				if(!$stored)
				{
					echo '{"status":false,"ext":"'.$this->upload->data('file_ext').'","name":"'.$this->upload->data('raw_name').'","message":"Image has uploaded but can not stored in database. If you want to remove the image, click: "}';
				}
				else
				{
					echo '{"status":true,"ext":"'.$this->upload->data('file_ext').'","name":"'.$this->upload->data('raw_name').'","message":"Image has uploaded and stored in database. If you want to remove the image, click: "}';
				}
			}
		}
	}

	public function do_change_image_indentity()
	{
		$this->load->library('form_validation');
		$this->form_validation->set_rules('id_image','Image Id','required|number');
		$this->form_validation->set_rules('alt','Image Alt','max_length[255]');
		$this->form_validation->set_rules('gallery','Image Gallery','min_length[1]');
		
		if($this->form_validation->run() === TRUE)
		{
			if(!$this->image_model->update_identity_image())
			{
				echo '{"status":false}';
			}
			else
			{
				echo '{"status":true}';
			}
		}
		else
		{
			echo '{"status":false,"message":"'.$form_error('id_image').$form_error('alt').$form_error('gallery').'"}';
		}
	}

	public function delete_image()
	{
		$this->form_validation->set_rules('id_image', 'Image Id', 'required|number');
		
		if($this->form_validation->run() === TRUE)
		{
			if(!$this->image_model->delete_image())
			{
				echo '{"status":false}';
			}
			else
			{
				echo '{"status":true}';
			}
		}
		else
		{
			echo '{"status":false,"message":"'.$form_error('id_image').'"}';
		}
	}

	public function move_image()
	{
		$this->form_validation->set_rules('id_image', 'Image Id', 'required|number');
		$this->form_validation->set_rules('recycle', 'Recycle Status', 'required|number');
		
		if($this->form_validation->run() === TRUE)
		{
			if(!$this->image_model->move_image())
			{
				echo '';
			}
			else
			{
				echo '';
			}
		}
		else
		{
			echo '';
		}
	}
}
?>

