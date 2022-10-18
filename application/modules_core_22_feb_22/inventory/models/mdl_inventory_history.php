<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

class Mdl_Inventory_History extends MY_Model {
      const LOW_INVENTORY = 5;
      public function __construct() {

        parent::__construct();

        $this->table_name = 'mcb_inventory_history';

        $this->primary_key = 'mcb_inventory_history.inventory_history_id';

        //$this->order_by = 'mcb_orders.order_date_entered DESC, mcb_orders.order_id';
        $this->order_by = 'mcb_inventory_history.inventory_history_id DESC';


        $this->select_fields = "
        SQL_CALC_FOUND_ROWS
        mcb_inventory_history.*";


        $this->joins = array( );


        $this->limit = $this->mdl_mcb_data->setting('results_per_page');

    }

    public function get($params = NULL) {

        //$params['group_by'] = 'mcb_orders.order_id';

        //$params['debug'] = TRUE;
        $inventory = parent::get($params);
        return $inventory;

    }

    public function validate() {
        $this->form_validation->set_rules('history_qty', $this->lang->line('inventory_history_qty'));
        $this->form_validation->set_rules('notes', $this->lang->line('inventory_history_notes'));
        $this->form_validation->set_rules('inventory_id', $this->lang->line('inventory_id'));
         return parent::validate($this);

    }
    public function get_history_by_iventoryId($id){
            $select = "
            SQL_CALC_FOUND_ROWS
            ih.inventory_history_id id,
            ih.history_qty,
            ih.notes,
            ih.user_id,
            ih.created_at ";

        $this->db->select($select, FALSE);
        $this->db->order_by('ih.inventory_history_id DESC');
        $id= $id==NULL?'NULL':$id;

        $this->db->where('ih.inventory_id', $id );
        $this->db->where('ih.inventory_id !=', '' );
        $this->db->where('ih.history_qty !=', '0.00' );
//        if ($limit) {
//            $this->db->limit($limit, $offset);
//        }
        $query = $this->db->get('mcb_inventory_history AS ih');
        return $query->result();
//            echo '<pre>';
//            print_r($id);
//            die;
    }
    
    public function ajax_insert($id, $qty) {
        $this->load->model('mdl_inventory_item');
        $old_qty = $this->mdl_inventory_item->get_qty_by_id($id)[0]->qty;
        $new_qty = floatval($qty) - floatval($old_qty);
        if ($new_qty == 0)
            return;
        $this->mdl_inventory_item->update_qty($id,$new_qty);

        $array = array('history_qty' => $new_qty,
            'inventory_id'=> $id, 'user_id'=> $this->session->userdata('user_id'));
        
        if(uri_assoc('inventory_history_id') != NULL){
            parent::save($array, uri_assoc('inventory_history_id'));
        }
    }
    public function save() {
        $this->load->model('mdl_inventory_item');
        $db_array = parent::db_array();
        $inventory_id = $this->mdl_inventory_item->get_id_by_name($db_array['name'])[0]->inventory_id;
        if ($db_array['history_qty'] == '' && $db_array['notes'] =='' )
            return;
         $array = array('history_qty' => $db_array['history_qty'], 'notes' =>  $db_array['notes'],
            'inventory_id'=> $inventory_id, 'user_id'=> $this->session->userdata('user_id'));
         
         if(uri_assoc('inventory_history_id') != NULL){
            parent::save($array, uri_assoc('inventory_history_id'));
         }
//         $this->mdl_inventory_item->update_qty($inventory_id,$db_array['history_qty']);
    }

    public function inventory_qty_order_deduct($id, $qty, $notes,$update_source,$field,$field_id = null){
      
        $this->load->model('inventory/mdl_inventory_item');

        
        $array = array('history_qty' => $qty, 'notes' =>  $notes,
            'inventory_id'=> $id, 'user_id'=> $this->session->userdata('user_id'));
        
        //this step is not going to update the qty of the inventory so obviously we wont put history here
        if(uri_assoc('inventory_history_id') != NULL){
            parent::save($array, uri_assoc('inventory_history_id'));
        }
        $current_qty = $this->mdl_inventory_item->get_qty_by_id($id)[0]->qty;
        $name = $this->mdl_inventory_item->get_by_id($id)[0]->name;
        
     
        //-------update pending quantity-------
    
       /* $stock_itm = $this->get_Row('mcb_item_stock', array('inventory_id' => $id),'stock_id','desc','1');
        $this->insert('mcb_item_stock', array(
                                        'qty'=>$stock_itm->qty,
                                        'qty_pending' => ($stock_itm->qty_pending + $qty),
                                        'qty_update_source'=>$update_source,
                                        'relevent_item_field'=>$field,
                                        'relevent_item_id'=>$field_id,
                                        'inventory_id' =>$id
                                         ));*/
        
      
        //updating history
        $history_inventory = array(
          'history_qty' => '',
            'inventory_id' => $id,
            'notes' => $qty . ' ' . $notes,
            'created_at' => date('Y-m-d H:i:s'),
        );

        parent::save($history_inventory, uri_assoc('inventory_history_id'));

        if ((floatval($current_qty) - floatval($qty)) < 0){
            return 'Negative';
        } else if ((floatval($current_qty) - floatval($qty)) < $this::LOW_INVENTORY) {
            return 'Low';
        }

    }
    public function update($tbl_name, $data, $condition) {
        
        $this->db->where($condition);
        $this->db->update($tbl_name, $data);
    }
    public function insert($table,$data){
       
        $this->db->insert($table,$data);
    }
    
    public function addHistoryItem($data){
        return $this->db->insert('mcb_inventory_history',$data);
    }
    public function get_Row($tbl_name, $condition,$orderby,$order,$limit) {
        
        $this->db->where($condition);
        $this->db->order_by($orderby,$order);
        $this->db->limit($limit);
        $q = $this->db->get($tbl_name);
        $Res = $q->row();
        return $Res;
    }

}
?>
