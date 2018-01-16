<?php
class Common_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    //Insert Row
    public function insert_data($table, $data)
    {
        if ($this->db->insert($table, $data))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function coulmn_name($coulmn_name, $table, $where)
    {
        return @$this->db->select($coulmn_name)->where($where)->get($table)->row()->$coulmn_name;
    }

	
    public function select_data($coulmn_names, $table, $where, $option = "", $order_by = "", $or_where)
    {
        $this->db->select($coulmn_names);
        if (!empty($where))
        {
            $this->db->where($where);
        }
        if (!empty($or_where))
        {
            $this->db->or_where($or_where);
        }
        if (!empty($order_by))
        {
            $this->db->order_by($order_by, 'asc');
        }
        $sql = $this->db->get($table);
        if ($sql->num_rows() > 0)
        {
            if ($option == 'single')
            {
                return $sql->row_array();
            }
            else
            {
                return $sql->result_array();
            }
        }
        else
        {
            return false;
        }
    }

    //Get User Info here
    public function get_data($table, $where = '', $option = "", $order_by = "", $limit = "", $offset = "", $order = "", $keyword = '')
    {
        if (!empty($where))
        {
            $this->db->where($where);
        }

        if (!empty($order_by))
        {
            if (!empty($order))
            {
                $this->db->order_by($order_by, $order);
            }
            else
            {
                $this->db->order_by($order_by, 'asc');
            }
        }
        if (!empty($limit))
        {
            if (!empty($offset))
            {
                $this->db->limit($limit, $offset);
            }
            else
            {
                $this->db->limit($limit);
            }
        }
        $sql = $this->db->get($table);

        if ($sql->num_rows() > 0)
        {
            if ($option == 'single')
            {
                return $sql->row_array();
            }
            else
            {
                return $sql->result_array();
            }
        }
        else
        {
            return false;
        }
    }

    //GEt Update data
    public function update_data($table, $data, $where)
    {
        if ($this->db->where($where)->update($table, $data))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function check_data($table, $where)
    {
        if ($this->db->where($where)->get($table)->num_rows() <= 0)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function delete_data($table, $where)
    {
        $this->db->where($where)->delete($table);
    }

	public function exist_data($table, $data)
    {
		$query = $this->db->get_where($table, $data);
        return $query->row_array();
    }
   
}
