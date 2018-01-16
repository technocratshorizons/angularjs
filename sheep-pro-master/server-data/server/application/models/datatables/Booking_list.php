<?php
class Booking_list extends CI_Model
{
    public $table = "joinings";
    public $select_column = array(
        "joinings.join_id",
        "joinings.date_to_scan",
        "joinings.scan_type",
        "joinings.number_of_sheep",
        "CONCAT(u.first_name,' ',u.last_name) as client_name",
        "joinings.status"
    );
    public $order_column = array(
        "joinings.date_to_scan",
        "joinings.scan_type",
        null,
        "joinings.number_of_sheep",
    );

    public $search_column = array(
        "joinings.date_to_scan",
        "joinings.scan_type",
        null,
        "joinings.number_of_sheep",
    );
    public function make_query()
    {
        $this->db->select($this->select_column);
        $this->db->from($this->table)->join('bookings b', 'b.booking_id=joinings.booking_id', 'left')->join('users u', 'u.user_id=b.client_id', 'left');
        if (isset($_REQUEST["search"]["value"]))
        {
            $wh = " (";
            foreach ($this->search_column as $column)
            {
                if (!empty($column))
                {
                    $wh .= " " . $column . " LIKE '%" . $_REQUEST["search"]["value"] . "%' OR";
                }
            }

            $wh = rtrim($wh, "OR");
            $wh .= " )";
            $this->db->where($wh);
        }
		
		if (isset($_REQUEST["order"]))
        {
            $this->db->order_by($this->order_column[$_REQUEST['order']['0']['column']], $_REQUEST['order']['0']['dir']);
        }
        else
        {
            $this->db->order_by($this->select_column[0], 'DESC');
        }
    }

    public function make_datatables($where)
    {
        $this->make_query();
        if ($_REQUEST["length"] != -1)
        {
            $this->db->limit($_REQUEST['length'], $_REQUEST['start']);
        }

        $query = $this->db->where($where)->get();
        return $query->result();
    }

    public function get_filtered_data($where)
    {
        $this->make_query();
        $query = $this->db->where($where)->get();
        return $query->num_rows();
    }

    public function get_all_data($where)
    {
        $this->db->select("*");
        $this->db->from($this->table)->join('bookings b', 'b.booking_id=joinings.booking_id', 'left')->join('users u', 'u.user_id=b.client_id', 'left')->where($where);
        return $this->db->count_all_results();
    }
}
