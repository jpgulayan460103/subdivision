<?php

class Client_model extends CI_Model {

    function __construct(){
        // Call the Model constructor
        parent::__construct();
        $this->load->database();
    }

    public function insert($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert('clients', $data);
    }

    public function get($data = [])
    {
        return $this->db->get('clients')->result();
    }

    public function delete($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('clients');
    }

    public function update($data,$id)
    {
        $this->db->where('id', $id);
        $this->db->update('clients', $data);
    }

    public function findByRfid($rfid)
    {
        $this->db->where('rfid', $rfid);
        return $this->db->get('clients')->row();
    }

    public function findByid($id)
    {
        $this->db->where('id', $id);
        return $this->db->get('clients')->row();
    }

    public function passedGate($client_id)
    {
        $type = "entry";
        $last = $this->getLastGatelog($client_id);
        if($last == null){
            $type = "entry";
        }else{
            if($last->type == "entry"){
                $type = "exit";
            }else{
                $type = "entry";
            }
        }
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['date_passed'] = date('Y-m-d');
        $data['client_id'] = $client_id;
        $data['type'] = $type;
        $this->db->insert('gate_logs', $data);
        return [
            'type' => $type,
            'date_passed' => date('Y-m-d H:i:s'),
            'client' => $this->findByid($client_id)
        ];
    }

    public function getLastGatelog($client_id)
    {
        $this->db->where('client_id', $client_id);
        $this->db->where('date_passed', date('Y-m-d'));
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        return $this->db->get('gate_logs')->row();
    }

    public function getGatelogs($data = [])
    {
        $this->db->order_by('gate_logs.id', 'DESC');
        $this->db->select("clients.*,gate_logs.type,gate_logs.date_passed, gate_logs.created_at");
        if(isset($data['date_passed']) && $data['date_passed']){
            $this->db->where('gate_logs.date_passed', $data['date_passed']);
        }
        if(isset($data['searchstring']) && $data['searchstring']){
            $this->db->group_start();
            $this->db->like('clients.plate_number', $data['searchstring']);
            $this->db->or_like('clients.model', $data['searchstring']);
            $this->db->or_like('clients.brand', $data['searchstring']);
            $this->db->or_like('clients.color', $data['searchstring']);
            $this->db->group_end();
        }
        $this->db->join('clients', 'clients.id = gate_logs.client_id', 'left');
        $result =  $this->db->get('gate_logs')->result();
        return $result;
    }
    
}