<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{
		$this->load->view('welcome_message');
	}

	public function gate()
	{
		$this->load->view('gate');
	}
	public function gate_logs()
	{
		$this->load->view('gate-logs');
	}

	public function client_index()
	{
		$this->load->model('client_model');
		$data = $this->client_model->get();
		echo json_encode($data);
	}

	public function client_add()
	{
		$this->load->model('client_model');
		
		$color = $this->input->post('color');
		$plate_number = $this->input->post('plate_number');
		$model = $this->input->post('model');
		$brand = $this->input->post('brand');
		$data = [
			'color' => $color,
			'plate_number' => $plate_number,
			'model' => $model,
			'brand' => $brand,
		];



		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('', '');
		$this->form_validation->set_rules('color', 'color', 'required');
		$this->form_validation->set_rules('plate_number', 'plate_number', 'required');
		$this->form_validation->set_rules('model', 'model', 'required');
		$this->form_validation->set_rules('brand', 'brand', 'required');

		if ($this->form_validation->run() == FALSE)
		{
			$errors = [
				'color' => form_error('color'),
				'plate_number' => form_error('plate_number'),
				'model' => form_error('model'),
				'brand' => form_error('brand'),
			];
			return $this->output
			->set_status_header('422')
			->set_content_type('application/json')
			->set_output(json_encode($errors));
		}
		else
		{
			$this->client_model->insert($data);
		}		

	}

	public function client_delete($id)
	{
		$this->load->model('client_model');
		$data = $this->client_model->delete($id);
	}

	public function client_update($id)
	{
		$this->load->model('client_model');
		
		$color = $this->input->post('color');
		$plate_number = $this->input->post('plate_number');
		$model = $this->input->post('model');
		$brand = $this->input->post('brand');
		$rfid = $this->input->post('rfid');
		$type = $this->input->post('type');
		$rfid_expiry = $this->input->post('rfid_expiry');
		$data = [
			'color' => $color,
			'plate_number' => $plate_number,
			'model' => $model,
			'brand' => $brand,
			'rfid' => $rfid,
			'rfid_expiry' => $rfid_expiry,
		];



		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('', '');
		$this->form_validation->set_rules('color', 'color', 'required');
		$this->form_validation->set_rules('plate_number', 'plate_number', 'required');
		$this->form_validation->set_rules('model', 'model', 'required');
		$this->form_validation->set_rules('brand', 'brand', 'required');
		if($type == "rfid"){
			$this->form_validation->set_rules('rfid', 'rfid', 'required|is_unique[clients.rfid]');
			$this->form_validation->set_rules('rfid_expiry', 'rfid_expiry', 'required');
		}

		if ($this->form_validation->run() == FALSE)
		{
			$errors = [
				'color' => form_error('color'),
				'plate_number' => form_error('plate_number'),
				'model' => form_error('model'),
				'brand' => form_error('brand'),
				'rfid' => form_error('rfid'),
				'rfid_expiry' => form_error('rfid_expiry'),
			];
			return $this->output
			->set_status_header('422')
			->set_content_type('application/json')
			->set_output(json_encode($errors));
		}
		else
		{
			$this->client_model->update($data, $id);
		}	
	}

	public function scan_rfid()
	{
		$rfid = $this->input->post('rfid');
		$this->load->model('client_model');
		$data = $this->client_model->findByRfid($rfid);
		if($data){
			$data = $this->client_model->passedGate($data->id);
			echo json_encode($data);
		}else{
			return $this->output
			->set_status_header('422')
			->set_content_type('application/json')
			->set_output(json_encode(["error"=>"no rfid	"]));
		}
	}

	public function remove_rfid($client_id)
	{
		$this->load->model('client_model');
		$data = [
			'rfid' => null,
			'rfid_expiry' => null
		];
		$this->client_model->update($data, $client_id);
	}


	public function gate_logs_index()
	{
		$this->load->model('client_model');	
		$searchstring = $this->input->get('searchstring');
		$date_passed = $this->input->get('date_passed');
		$data = [
			'searchstring' => $searchstring,
			'date_passed' => $date_passed
		];
		$data = $this->client_model->getGatelogs($data);
		echo json_encode($data);
	}

	public function gate_logs_download(Type $var = null)
	{
		$this->load->model('client_model');	
		$data = $this->client_model->getGatelogs();

		$file = fopen("gatelogs.csv","w");
		
		fputcsv($file, [
			"Type",
			"Date Time",
			"Color",
			"Plate number",
			"Model",
			"Brand",
		]);


		foreach ($data as $line) {
			fputcsv($file, [
				$line->type,
				$line->created_at,
				$line->color,
				$line->plate_number,
				$line->model,
				$line->brand,
			]);
		}
		
		fclose($file);

	}
}
