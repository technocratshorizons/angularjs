<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Booking extends CI_Controller
{
    public function __construct()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: X-Requested-With');
        header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
        parent::__construct();
        $this->load->library('sms');
    }

    public function add()
    {
        $res   = file_get_contents("php://input");
        $_POST = json_decode($res, true);
        if (isset($_POST) && !empty($_POST)) {
            $postdata = $_POST;
            $this->form_validation->set_error_delimiters('', '');
            $this->form_validation->set_rules('client_details', 'Client', 'required');
            if ($this->form_validation->run() == false) {
                $response['status'] = false;
                $response['msg']    = validation_errors();
            } else {

                if (!empty($postdata['unavailable_dates'])) {
                    $all_unavailables = explode(',', $postdata['unavailable_dates']);
                    $errors           = 0;

                    foreach ($postdata['joinings_array'] as $join) {

                        //Convert Dates to readable format
                        $begin = date('Y-m-d', strtotime($join['booking_date'][0]));
                        $end   = date('Y-m-d', strtotime($join['booking_date'][1]));

                        //Fetch All dates from Start date and end date
                        $date_ranges       = $this->date_range($begin, $end);

                        //Loop for check
                        foreach ($date_ranges as $date_r) {
                            if (in_array($date_r, $all_unavailables)) {
                                $errors++;
                            }
                        }
                    }
                    if ($errors > 0) {
                        $response['status'] = false;
                        $response['msg']    = 'Your Appointment date is clash with unavailable date. Please select another appointment date.';
                        exit(json_encode($response));
                    }
                }
                $InsertData = array(
                    'client_id'                 => $postdata['client_details'],
                    'unavailable_dates'         => @$postdata['unavailable_dates'],
                    'client_confirmation_email' => (!empty($postdata['client_confirmation_email'])) ? '1' : '0',
                    'client_confirmation_sms'   => (!empty($postdata['client_confirmation_phone'])) ? '1' : '0',
                    'notes'                     => @$postdata['notes'],
                );
                if ($this->common_model->insert_data('bookings', $InsertData)) {
                    $insert_id = $this->db->insert_id();
                    $this->load->library('google');

                    $clientInfo              = $this->common_model->get_data('users', array('user_id' => $postdata['client_details']), 'single');
                    $k                       = 1;
                    $__SCANNING_DATES_MODULE = "";
                    //Joining Insertion start here
                    foreach ($postdata['joinings_array'] as $join) 
                    {
                        $joinInsert = array();
                        $joinInsert = array(
                            'booking_id'       => $insert_id,
                            // 'joining_title'=> @$join['joining_title'],
                            'number_of_sheep'  => @$join['number_of_sheep'],
                            'scan_type'        => @$join['scan_type'],
                            'booking_date'     => @$join['booking_date'][0],
                            'booking_date_end' => @$join['booking_date'][1],
                            // 'time'=> @$join['time'],
                            'scanner'          => implode(',', $join['scanner']),
                            'room_in'          => @$join['room_in'][0],
                            'room_out'         => @$join['room_in'][1],
                            'introduced_days'  => @$join['introduced_days'],
                            'joining_duration' => @$join['joining_duration'],
                            'room_free_days'   => @$join['room_free_days'],
                            'date_to_scan'     => @$join['date_to_scan'],
                        );
                        if ($this->common_model->insert_data('joinings', $joinInsert)) {

                            if(!empty($postdata['client_confirmation_phone'])){
                                $sms_start             = date('d-m-Y', strtotime($join['booking_date'][0]));
                                $sms_end               = date('d-m-Y', strtotime($join['booking_date'][1]));
                                if($sms_start!=$sms_end){
                                    $send_date = $sms_start.' - '.$sms_end;
                                } else{
                                    $send_date = $sms_start;
                                }
                                // $sms_messgae = 'Sheep Pro booking confirmed. We are scanning '.$join['number_of_sheep'].' sheeps on '.$send_date.'. Call Mark on 0428386924 for any queries.';
                                // $response = $this->sms->send('61'.$clientInfo['mobile_phone'],$sms_messgae);
                                // if($response['data']['status']!='OK'){
                                //     log_message('error',$response['message']);
                                // }
                            }

                            // $__SCANNING_DATES_MODULE .= 'scanning date ' . $k++ . ': <b> ' . date('d-m-Y', strtotime($join['date_to_scan'])) . ' </b><br/>Approx. number: <b>' . $join['number_of_sheep'] . '</b><br/><br/>';
                            $last_join_id = $this->db->insert_id();
                            $events_ids   = array();
                            foreach ($join['scanner'] as $scanner) {
                                $scannerInfo = $this->common_model->get_data('users', array('user_id' => $scanner), 'single');

                                $args = array(
                                    'share_with'  => $clientInfo['email'],
                                    'permission'  => 'reader',
                                    'calendar_id' => $scannerInfo['calendar_id'],
                                );
                                $this->google->shareCal($args);
                                $summary   = array();
                                $summary[] = 'Status: Pending';
                                if (!empty($postdata['notes'])) {
                                    $summary[] = 'Name: ' . ucfirst(substr($clientInfo['first_name'], 0, 1)) . ' ' . ucfirst($clientInfo['last_name']) . " **";

                                } else {
                                    $summary[] = 'Name: ' . ucfirst(substr($clientInfo['first_name'], 0, 1)) . ' ' . ucfirst($clientInfo['last_name']);
                                }
                                $summary[] = 'Booking ID: ' . $last_join_id;
                                $summary[] = 'Location: ' . $clientInfo['suburb'] . " " . $clientInfo['state'];
                                $summary[] = 'Sheep: ' . $join['number_of_sheep'];
                                if (strtolower($join['scan_type']) == 'single') {
                                    $summary[] = 'Scan Type: WET/DRY';
                                } else {
                                    $summary[] = 'Scan Type: MULTIPLES';
                                }
                                $summary[]         = 'Intro: ' . $join['introduced_days'];
                                $summary[]         = 'Free: ' . $join['room_free_days'];
                                $summary[]         = 'Phone: ' . $clientInfo['mobile_phone'];
                                $summary[]         = 'Landline: ' . $clientInfo['landline_phone'];
                                $new_sum           = implode(', ', $summary);
                                $start             = date('Y-m-d', strtotime($join['booking_date'][0])) . " 09:00:00";
                                $end               = date('Y-m-d', strtotime($join['booking_date'][1])) . " 13:00:00";
                                $start             = date(DATE_ATOM, strtotime($start));
                                $end               = date(DATE_ATOM, strtotime($end));
                                $description       = array();
                                $description[]     = "Notes: " . $postdata['notes'];
                                $description[]     = "Link: staging.technocratshorizons.com/sheeptimer/booking/view/" . $last_join_id;
                                $description[]     = "Unavailable Days: ";
                                $unavailable_dates = explode(',', $postdata['unavailable_dates']);
                                foreach ($unavailable_dates as $datesc) {
                                    $description[] = $datesc;
                                }
                                $description2 = implode("\n", $description);
                                $array        = array(
                                    'summary'     => $new_sum,
                                    'location'    => $clientInfo['address_1'] . ' ' . $clientInfo['address_2'] . ' ' . $clientInfo['post_code'] . ' ' . $clientInfo['state'],
                                    'description' => $description2,
                                    'startDate'   => $start,
                                    'endDate'     => $end,
                                    'calendarId'  => $scannerInfo['calendar_id'],
                                );
                                $res = $this->google->addEvent($array);
                                if ($res['status']) {
                                    $events_ids[] = $res['data'];
                                } else {
                                    $events_ids[] = null;
                                }
                            }
                            if (!empty($events_ids)) {
                                $update_data = array(
                                    'event_ids'  => implode(',', $events_ids),
                                    'extra_date' => $start,
                                );
                                $this->common_model->update_data('joinings', $update_data, array('join_id' => $last_join_id));
                            }
                        }
                    }

                    // if ($postdata['client_confirmation_email']) {
                    //     $message         = $this->lang->line('Booking_mail_body');
                    //     $replaceto       = array("__FIRST_NAME", "__SCANNING_DATES_MODULE");
                    //     $replacewith     = array($clientInfo['first_name'] . " " . $clientInfo['last_name'], $__SCANNING_DATES_MODULE);
                    //     $data['content'] = str_replace($replaceto, $replacewith, $message);
                    //     $subject         = $this->lang->line('Booking_mail_subject');
                    //     $body            = $this->load->view('email/simple_content', $data, true);
                    //     send_email($clientInfo['email'], $subject, $body);
                    // }

                    $response['status'] = true;
                    $response['msg']    = 'Booking added successfully.';
                } else {
                    $response['status'] = false;
                    $response['msg']    = 'data could not be inserted.';
                }
            }
        } else {
            $response['status'] = false;
            $response['msg']    = 'Please send data first';
        }
        exit(json_encode($response));
    }

    public function edit()
    {
        $res   = file_get_contents("php://input");
        $_POST = json_decode($res, true);
        if (isset($_POST) && !empty($_POST)) {
            $postdata = $_POST;
            $this->form_validation->set_error_delimiters('', '');
            $this->form_validation->set_rules('join_id', 'Join', 'required');
            $this->form_validation->set_rules('number_of_sheep', 'number of sheep', 'required');
            $this->form_validation->set_rules('scan_type', 'scan type', 'required');
            // $this->form_validation->set_rules('booking_date', 'booking date', 'required');
            $this->form_validation->set_rules('introduced_days', 'introduced days', 'required');
            $this->form_validation->set_rules('joining_duration', 'joining duration', 'required');
            $this->form_validation->set_rules('date_to_scan', 'date to scan', 'required');
            if ($this->form_validation->run() == false) {
                $response['status'] = false;
                $response['msg']    = validation_errors();
            } else {

                if (!empty($postdata['unavailable_dates'])) {
                    $all_unavailables = explode(',', $postdata['unavailable_dates']);
                    $errors           = 0;

                    // foreach ($postdata['joinings_array'] as $join) {
                        
                        //Convert Dates to readable format
                        $begin = date('Y-m-d', strtotime($postdata['booking_date'][0]));
                        $end   = date('Y-m-d', strtotime($postdata['booking_date'][1]));

                        //Fetch All dates from Start date and end date
                        $date_ranges       = $this->date_range($begin, $end);

                        //Loop for check
                        foreach ($date_ranges as $date_r) {
                            if (in_array($date_r, $all_unavailables)) {
                                $errors++;
                            }
                        }
                    // }
                    if ($errors > 0) {
                        $response['status'] = false;
                        $response['msg']    = 'Your Appointment date is clash with unavailable date. Please select another appointment date.';
                        exit(json_encode($response));
                    }
                }

                $joinInfo    = $this->common_model->get_data('joinings', array('join_id' => $postdata['join_id']), 'single');
                $bookingInfo = $this->common_model->get_data('bookings', array('booking_id' => $joinInfo['booking_id']), 'single');
                $InsertData  = array(
                    'unavailable_dates'         => @$postdata['unavailable_dates'],
                    'client_confirmation_email' => (!empty($_POST['client_confirmation_email'])) ? '1' : '0',
                    'client_confirmation_sms'   => (!empty($_POST['client_confirmation_phone'])) ? '1' : '0',
                    'notes'                     => @$postdata['notes'],
                );
                if ($this->common_model->update_data('bookings', $InsertData, array('booking_id' => $joinInfo['booking_id']))) {
                    $this->load->library('google');

                    $clientInfo = $this->common_model->get_data('users', array('user_id' => $bookingInfo['client_id']), 'single');
                    // echo $postdata['booking_date'][0]."<br>";
                    // echo $start      = date('Y-m-d', strtotime($postdata['booking_date'][0])) . " 09:00:00";
                    // echo $end        = date('Y-m-d', strtotime($postdata['booking_date'][1])) . " 13:00:00";
                    $start      = $postdata['booking_date'][0];
                    $end        = $postdata['booking_date'][1];

                    // echo "Start".$start;
                    // echo "End".$end;
                    // die;

                    //Joining Insertion start here
                    $joinInsert = array(
                        'number_of_sheep'  => @$postdata['number_of_sheep'],
                        'scan_type'        => @$postdata['scan_type'],
                        'booking_date'     => @$postdata['booking_date'][0],
                        'booking_date_end'     => @$postdata['booking_date'][1],
                        'room_in'          => @$postdata['room_in'][0],
                        'room_out'         => @$postdata['room_in'][1],
                        'introduced_days'  => @$postdata['introduced_days'],
                        'joining_duration' => @$postdata['joining_duration'],
                        'room_free_days'   => @$postdata['room_free_days'],
                        'date_to_scan'     => @$postdata['date_to_scan'],
                        'extra_date'       => $start,
                        'status'           => 'Pending'
                    );

                    if($postdata['status']=='booking-pending')
                    {
                        $joinInsert['scanner'] = implode(',', $postdata['scanner']);
                    }
                    if ($this->common_model->update_data('joinings', $joinInsert, array('join_id' => $postdata['join_id']))) {
                        $joinInfo    = $this->common_model->get_data('joinings', array('join_id' => $postdata['join_id']), 'single');
                        $bookingInfo = $this->common_model->get_data('bookings', array('booking_id' => $joinInfo['booking_id']), 'single');
                        $last_join_id       = $postdata['join_id'];
                        $events_ids         = array();
                        $join['scanner']    = explode(',', $joinInfo['scanner']);
                        $join['events_ids'] = explode(',', $joinInfo['event_ids']);
                        if($postdata['status']!='booking-pending')
                        { 
                            // print_r($join['scanner']);
                            foreach ($join['scanner'] as $key => $scanner) {
                                $scannerInfo = $this->common_model->get_data('users', array('user_id' => $scanner), 'single');
                                $summary     = array();
                                $summary[]   = 'Status: ' . $joinInfo['status'];
                                if (!empty($postdata['notes'])) {
                                    $summary[] = 'Name: ' . ucfirst(substr($clientInfo['first_name'], 0, 1)) . ' ' . ucfirst($clientInfo['last_name']) . " **";

                                } else {
                                    $summary[] = 'Name: ' . ucfirst(substr($clientInfo['first_name'], 0, 1)) . ' ' . ucfirst($clientInfo['last_name']);
                                }
                                $summary[] = 'Booking ID: ' . $last_join_id;
                                $summary[] = 'Location: ' . $clientInfo['suburb'] . " " . $clientInfo['state'];
                                $summary[] = 'Sheep: ' . $postdata['number_of_sheep'];
                                if (strtolower($postdata['scan_type']) == 'single') {
                                    $summary[] = 'Scan Type: WET/DRY';
                                } else {
                                    $summary[] = 'Scan Type: MULTIPLES';
                                }
                                $summary[]         = 'Intro: ' . $postdata['introduced_days'];
                                $summary[]         = 'Free: ' . $postdata['room_free_days'];
                                $summary[]         = 'Phone: ' . $clientInfo['mobile_phone'];
                                $summary[]         = 'Landline: ' . $clientInfo['landline_phone'];
                                $new_sum           = implode(', ', $summary);
                                $description       = array();
                                $description[]     = "Notes: " . $postdata['notes'];
                                $description[]     = "Link: staging.technocratshorizons.com/sheeptimer/booking/view/" . $last_join_id;
                                $description[]     = "Unavailable Days:";
                                $unavailable_dates = explode(',', $postdata['unavailable_dates']);
                                foreach ($unavailable_dates as $datesc) {
                                    $description[] = $datesc;
                                }
                                $description2 = implode("\n", $description);
                                $array        = array(
                                    'summary'     => $new_sum,
                                    'location'    => $clientInfo['address_1'] . ' ' . $clientInfo['address_2'] . ' ' . $clientInfo['post_code'] . ' ' . $clientInfo['state'],
                                    'description' => $description2,
                                    'startDate'   => $start,
                                    'endDate'     => $end,
                                    'calendarId'  => $scannerInfo['calendar_id'],
                                    'eventId'     => @$join['events_ids'][$key],
                                );
                                $res = $this->google->editEvent($array);
                            }
                        }
                        else
                        {
                             // print_r($join['scanner']);
                            foreach ($join['scanner'] as $key => $scanner) {
                                $scannerInfo = $this->common_model->get_data('users', array('user_id' => $scanner), 'single');
                                $summary     = array();
                                $summary[]   = 'Status: ' . $joinInfo['status'];
                                if (!empty($postdata['notes'])) {
                                    $summary[] = 'Name: ' . ucfirst(substr($clientInfo['first_name'], 0, 1)) . ' ' . ucfirst($clientInfo['last_name']) . " **";

                                } else {
                                    $summary[] = 'Name: ' . ucfirst(substr($clientInfo['first_name'], 0, 1)) . ' ' . ucfirst($clientInfo['last_name']);
                                }
                                $summary[] = 'Booking ID: ' . $last_join_id;
                                $summary[] = 'Location: ' . $clientInfo['suburb'] . " " . $clientInfo['state'];
                                $summary[] = 'Sheep: ' . $postdata['number_of_sheep'];
                                if (strtolower($postdata['scan_type']) == 'single') {
                                    $summary[] = 'Scan Type: WET/DRY';
                                } else {
                                    $summary[] = 'Scan Type: MULTIPLES';
                                }
                                $summary[]         = 'Intro: ' . $postdata['introduced_days'];
                                $summary[]         = 'Free: ' . $postdata['room_free_days'];
                                $summary[]         = 'Phone: ' . $clientInfo['mobile_phone'];
                                $summary[]         = 'Landline: ' . $clientInfo['landline_phone'];
                                $new_sum           = implode(', ', $summary);
                                $description       = array();
                                $description[]     = "Notes: " . $postdata['notes'];
                                $description[]     = "Link: staging.technocratshorizons.com/sheeptimer/booking/view/" . $last_join_id;
                                $description[]     = "Unavailable Days:";
                                $unavailable_dates = explode(',', $postdata['unavailable_dates']);
                                foreach ($unavailable_dates as $datesc) {
                                    $description[] = $datesc;
                                }
                                $description2 = implode("\n", $description);
                                $array        = array(
                                    'summary'     => $new_sum,
                                    'location'    => $clientInfo['address_1'] . ' ' . $clientInfo['address_2'] . ' ' . $clientInfo['post_code'] . ' ' . $clientInfo['state'],
                                    'description' => $description2,
                                    'startDate'   => $start,
                                    'endDate'     => $end,
                                    'calendarId'  => $scannerInfo['calendar_id'],
                                );
                                $res = $this->google->addEvent($array);
                                if ($res['status']) {
                                    $events_ids[] = $res['data'];
                                } else {
                                    $events_ids[] = null;
                                }
                            }
                            $this->common_model->update_data('joinings',array('event_ids'=>implode(',',$events_ids)),array('join_id' => $postdata['join_id']));
                        }
                    }
                    $response['status'] = true;
                    $response['msg']    = 'Booking updated successfully.';
                } else {
                    $response['status'] = false;
                    $response['msg']    = 'data could not be inserted.';
                }
            }
        } else {
            $response['status'] = false;
            $response['msg']    = 'Please send data first';
        }
        exit(json_encode($response));
    }

    public function fetchBookings()
    {
        $where = array(
            'join_id!=' => '0',
        );
        $this->load->model("datatables/Booking_list");
        $fetch_data = $this->Booking_list->make_datatables($where);
        $data       = array();
        $i          = 1;
        foreach ($fetch_data as $row) {
            $sub_array   = array();
            $sub_array[] = $row->date_to_scan;
            $sub_array[] = $row->scan_type;
            $sub_array[] = $row->client_name;
            $sub_array[] = $row->number_of_sheep;
            $sub_array[] = "pending";
            $sub_array[] = $row->join_id;
            $data[]      = $sub_array;
        }

        $output = array(
            "draw"            => intval($_REQUEST["draw"]),
            "recordsTotal"    => $this->Booking_list->get_all_data($where),
            "recordsFiltered" => $this->Booking_list->get_filtered_data($where),
            "data"            => $data,
        );
        echo json_encode($output);
    }

    public function all_bookings()
    {
        $data               = $this->db->select('j.*,b.unavailable_dates,b.client_confirmation_email,b.client_confirmation_sms,b.notes,u.first_name as client_first_name,u.last_name as client_last_name')->from('joinings j')->join('bookings b', 'b.booking_id=j.booking_id', 'left')->join('users u', 'u.user_id=b.client_id', 'left')->order_by('join_id', 'desc')->get()->result_array();
        $response['status'] = true;
        $response['data']   = $data;
        foreach ($response['data'] as $key => $value) {
            $scanners_name          = "";
            $response['data'][$key] = $value;
            $response['data'][$key]['isDisable'] = false;
            $response['data'][$key]['action'] = 'Action';
            $scanners               = explode(',', $value['scanner']);
            foreach ($scanners as $scan) {
                $res = $this->db->select('first_name,last_name')->where(array('user_id' => $scan))->get('users')->row_array();
                $scanners_name .= $res['first_name'] . ' ' . $res['last_name'] . ', ';
            }
            $scanners_name                      = rtrim($scanners_name, ', ');
            $response['data'][$key]['scanners'] = $scanners_name;

        }
        exit(json_encode($response));
    }

    //fetch single booking info here
    public function bookinginfo()
    {
        $res      = file_get_contents("php://input");
        $_POST    = json_decode($res, true);
        $response = array();
        if (isset($_POST) && !empty($_POST)) {
            $postdata = $_POST;
            $this->form_validation->set_error_delimiters('', '');
            $this->form_validation->set_rules('id', 'Booking', 'required');
            if ($this->form_validation->run() == false) {
                $response['status'] = false;
                $response['msg']    = validation_errors();
            } else {
                $response['status']          = true;
                $response['data']            = $this->db->where('j.join_id', $postdata['id'])->from('joinings j')->join('bookings b', 'b.booking_id=j.booking_id', 'left')->get()->row_array();
                $response['data']['scanner'] = explode(',', $response['data']['scanner']);
            }
        } else {
            $response['status'] = false;
            $response['msg']    = 'Something went wrong';
        }
        exit(json_encode($response));
    }

    public function view_bookings($id)
    {
        $data               = $this->db->select('j.*,b.unavailable_dates,b.client_confirmation_email,b.client_confirmation_sms,b.notes,u.first_name as client_first_name,u.last_name as client_last_name')->from('joinings j')->join('bookings b', 'b.booking_id=j.booking_id', 'left')->join('users u', 'u.user_id=b.client_id', 'left')->where('j.join_id', $id)->order_by('join_id', 'desc')->get()->row_array();
        $response['status'] = true;
        $response['data']   = $data;
        $scanners           = explode(',', $data['scanner']);
        if (!empty($data)) {
            foreach ($scanners as $key => $scanner) {
                $response['data']['scanners'][$key] = $this->db->select('first_name,last_name')->where('user_id', $scanner)->get('users')->row_array();
            }
        } else {
            $response['status'] = false;
            $response['msg']    = "Booking not found";
        }
        exit(json_encode($response));
    }

    public function index()
    {
        $this->load->library('google');
        $calendarId = 'db1o71p9ofli7vufarjqclcfr4@group.calendar.google.com';
        $optParams  = array(
            'maxResults'   => 10,
            'orderBy'      => 'startTime',
            'singleEvents' => true,
            'timeMin'      => date('c'),
        );
        $res = $this->google->listEvents($calendarId, $optParams);

        echo "<pre>";
        print_r($res);

    }

    public function index2()
    {
        $this->load->library('google');
        $res = $this->google->getCalenderList();
        echo "<pre>";
        print_r($res);
    }

    public function addcalc()
    {
        $this->load->library('google');
        $array = array('title' => 'Manpreet Dummy Calendar', 'timeZone' => 'America/Los_Angeles');
        $res   = $this->google->addCalendar($array);
        echo "<pre>";
        print_r($res);
    }

    public function getColor()
    {
        $this->load->library('google');
        $res = $this->google->getColor();
    }

    public function addevent()
    {
        $this->load->library('google');
        $array = array(
            'summary'     => ' Demo event For Testing',
            'location'    => '800 Howard St San Francisco, CA 94103',
            'description' => 'A chance to hear more about Googles developer products.',
            'startDate'   => '2017-11-11T09:00:00-07:00',
            'endDate'     => '2017-11-11T09:00:00-07:00',
            'calendarId'  => 'db1o71p9ofli7vufarjqclcfr4@group.calendar.google.com',
        );
        $res = $this->google->addEvent($array);
    }

    public function editevent()
    {
        $this->load->library('google');
        $array = array(
            'summary'     => 'Demo event For Testing',
            'description' => 'A chance to hear more about Googles developer products.',
            'startDate'   => '2017-11-10T09:00:00-07:00',
            'endDate'     => '2017-11-10T09:00:00-07:00',
            'calendarId'  => '95vpasb7o6dljtnmhf0m2mg6kc@group.calendar.google.com',
            'eventId'     => '57nfgb3i3htakf2bhcv6a3dbts',
        );
        $res = $this->google->editEvent($array);
    }

    public function sharecal()
    {
        $this->load->library('google');
        $res = $this->google->shareCal('db1o71p9ofli7vufarjqclcfr4@group.calendar.google.com');
        echo "<pre>";
        print_r($res);
    }

    //Booking Set up Push notification
    public function somefunction()
    {
        $this->load->library('google');
        $res = $this->google->listEvents('95vpasb7o6dljtnmhf0m2mg6kc@group.calendar.google.com');
        echo "<pre>";
        print_r($res);
    }

    public function notification()
    {
        $postdata    = $_POST;
        $channel_id  = trim($postdata['X-Goog-Channel-Id']);
        $scannerInfo = $this->common_model->get_data('users', array('channel_id' => $channel_id), 'single');
        if (!empty($scannerInfo)) {
            $this->load->library('google');
            if (!empty($scannerInfo['sync_token'])) {
                $res = $this->google->listEvents($scannerInfo['calendar_id'], array('syncToken' => $scannerInfo['sync_token']));
            } else {
                $res = $this->google->listEvents($scannerInfo['calendar_id']);
            }
            if (!empty($res['status'])) {
                foreach ($res['data'] as $key => $value) {
                    if (!empty($value['id'])) {
                        $joiningInfo = $this->db->where('FIND_IN_SET("' . $value['id'] . '",event_ids)>', '0')->get('joinings')->row_array();

                        //If Event Deleted from google Calendar start here
                        if(empty($value['start']) && empty($value['end'])){
                            $this->common_model->update_data('joinings',array('google_delete'=>'Yes'),array('join_id'=>$joiningInfo['join_id']));
                        }   
                        else
                        {
                            //If Event Deleted From google calendar end here
                            $start = date('Y-m-d', strtotime($value['start'])) . " 09:00:00";
                            $end   = date('Y-m-d', strtotime($value['end'])) . " 13:00:00";
                            $start = date(DATE_ATOM, strtotime($start));
                            $end   = date(DATE_ATOM, strtotime($end));

                            $booking_date_start   = date('Y-m-d', strtotime($joiningInfo['booking_date']));
                            $booking_date_end   = date('Y-m-d', strtotime($joiningInfo['booking_date_end']));
                            $new_date_start       = date('Y-m-d', strtotime($value['start']));
                            $new_date_end       = date('Y-m-d', strtotime($value['end']));
                            $rooms_in_date  = date('Y-m-d', strtotime($joiningInfo['room_in']));
                            $rooms_out_date = date('Y-m-d', strtotime($joiningInfo['room_out']));

                            $introduced_days = $this->get_diff_date($new_date_start, $rooms_in_date);
                            $room_free_days  = $this->get_diff_date($new_date_start, $rooms_out_date);

                            if (($new_date_start != $booking_date_start) || $new_date_end != $booking_date_end) {
                                $updateData = array(
                                    'booking_date'     => $value['start'],
                                    'booking_date_end' => $value['end'],
                                    'introduced_days'  => $introduced_days,
                                    'room_free_days'   => $room_free_days,
                                );
                                $this->db->where('join_id', $joiningInfo['join_id'])->update('joinings', $updateData);
                                $allEvents   = explode(',', $joiningInfo['event_ids']);
                                $allScanners = explode(',', $joiningInfo['scanner']);

                                $bookingInfo2 = $this->db->select('*')->where('booking_id', $joiningInfo['booking_id'])->get('bookings')->row_array();

                                $all_bookings = explode(',', $bookingInfo2['unavailable_dates']);
                                


                                $errors = 0;
                                //Convert Dates to readable format
                                $begin = $value['start'];
                                $end   = $value['end'];

                                //Fetch All dates from Start date and end date
                                $date_ranges       = $this->date_range($begin, $end);

                                 foreach ($date_ranges as $date_r) {
                                    if (in_array($date_r, $all_bookings)) {
                                        $errors++;
                                    }
                                    log_message('error', 'Date_ranges' . $date_r);
                                }
                                if ($errors > 0) {
                                    log_message('error', 'Hook Called IF not found');
                                    $this->send_mail_to_admin($joiningInfo['join_id']);
                                }


                                foreach ($allEvents as $key => $eve) {
                                    if (!empty($eve)) {
                                        if ($eve == $value['id']) {
                                            $userInfo    = $this->db->select('*')->where('user_id', $allScanners[$key])->get('users')->row_array();
                                            $bookingInfo = $this->db->select('*')->where('booking_id', $joiningInfo['booking_id'])->get('bookings')->row_array();
                                            $clientInfo  = $this->db->select('*')->where('user_id', $bookingInfo['client_id'])->get('users')->row_array();
                                            if (!empty($userInfo['calendar_id'])) {
                                                $summary = array();
                                                //Edit Event Call here
                                                $summary[] = 'Status: ' . $joiningInfo['status'];

                                                if (!empty($postdata['notes'])) {
                                                    $summary[] = 'Name: ' . ucfirst(substr($clientInfo['first_name'], 0, 1)) . ' ' . ucfirst($clientInfo['last_name']) . " **";

                                                } else {
                                                    $summary[] = 'Name: ' . ucfirst(substr($clientInfo['first_name'], 0, 1)) . ' ' . ucfirst($clientInfo['last_name']);
                                                }
                                                $summary[] = 'Booking ID: ' . $joiningInfo['join_id'];
                                                $summary[] = 'Location: ' . $clientInfo['suburb'] . " " . $clientInfo['state'];
                                                $summary[] = 'Sheep: ' . $joiningInfo['number_of_sheep'];

                                                if (strtolower($joiningInfo['scan_type']) == 'single') {
                                                    $summary[] = 'Scan Type: WET/DRY';
                                                } else {
                                                    $summary[] = 'Scan Type: MULTIPLES';
                                                }
                                                $summary[]         = 'Intro: ' . $introduced_days;
                                                $summary[]         = 'Free: ' . $room_free_days;
                                                $summary[]         = 'Phone: ' . $clientInfo['mobile_phone'];
                                                $summary[]         = 'Landline: ' . $clientInfo['landline_phone'];
                                                $new_sum           = implode(', ', $summary);
                                                $description       = array();
                                                $description[]     = "Notes: " . $bookingInfo['notes'];
                                                $description[]     = "Link: staging.technocratshorizons.com/sheeptimer/booking/view/" . $joiningInfo['join_id'];
                                                $description[]     = "Unavailable Days: ";
                                                $unavailable_dates = explode(',', $bookingInfo['unavailable_dates']);
                                                foreach ($unavailable_dates as $datesc) {
                                                    $description[] = $datesc;
                                                }
                                                $description2 = implode("\n", $description);
                                                $array        = array(
                                                    'summary'     => $new_sum,
                                                    'location'    => $clientInfo['address_1'] . ' ' . $clientInfo['address_2'] . ' ' . $clientInfo['post_code'] . ' ' . $clientInfo['state'],
                                                    'description' => $description2,
                                                    'startDate'   => $start,
                                                    'endDate'     => $end,
                                                    'calendarId'  => $userInfo['calendar_id'],
                                                    'eventId'     => $eve,
                                                );
                                                $this->google->editEvent($array);}
                                        } else {
                                            $userInfo    = $this->db->select('*')->where('user_id', $allScanners[$key])->get('users')->row_array();
                                            $bookingInfo = $this->db->select('*')->where('booking_id', $joiningInfo['booking_id'])->get('bookings')->row_array();
                                            $clientInfo  = $this->db->select('*')->where('user_id', $bookingInfo['client_id'])->get('users')->row_array();
                                            if (!empty($userInfo['calendar_id'])) {
                                                $summary = array();
                                                //Edit Event Call here
                                                $summary[] = 'Status: ' . $joiningInfo['status'];

                                                if (!empty($postdata['notes'])) {
                                                    $summary[] = 'Name: ' . ucfirst(substr($clientInfo['first_name'], 0, 1)) . ' ' . ucfirst($clientInfo['last_name']) . " **";

                                                } else {
                                                    $summary[] = 'Name: ' . ucfirst(substr($clientInfo['first_name'], 0, 1)) . ' ' . ucfirst($clientInfo['last_name']);
                                                }
                                                $summary[] = 'Booking ID: ' . $joiningInfo['join_id'];
                                                $summary[] = 'Location: ' . $clientInfo['suburb'] . " " . $clientInfo['state'];
                                                $summary[] = 'Sheep: ' . $joiningInfo['number_of_sheep'];
                                                if (strtolower($joiningInfo['scan_type']) == 'single') {
                                                    $summary[] = 'Scan Type: WET/DRY';
                                                } else {
                                                    $summary[] = 'Scan Type: MULTIPLES';
                                                }
                                                $summary[]         = 'Intro: ' . $introduced_days;
                                                $summary[]         = 'Free: ' . $room_free_days;
                                                $summary[]         = 'Phone: ' . $clientInfo['mobile_phone'];
                                                $summary[]         = 'Landline: ' . $clientInfo['landline_phone'];
                                                $new_sum           = implode(', ', $summary);
                                                $description       = array();
                                                $description[]     = "Notes: " . $bookingInfo['notes'];
                                                $description[]     = "Link: staging.technocratshorizons.com/sheeptimer/booking/view/" . $joiningInfo['join_id'];
                                                $description[]     = "Unavailable Days: ";
                                                $unavailable_dates = explode(',', $bookingInfo['unavailable_dates']);
                                                foreach ($unavailable_dates as $datesc) {
                                                    $description[] = $datesc;
                                                }
                                                $description2 = implode("\n", $description);
                                                $array        = array(
                                                    'summary'     => $new_sum,
                                                    'location'    => $clientInfo['address_1'] . ' ' . $clientInfo['address_2'] . ' ' . $clientInfo['post_code'] . ' ' . $clientInfo['state'],
                                                    'description' => $description2,
                                                    'startDate'   => $start,
                                                    'endDate'     => $end,
                                                    'calendarId'  => $userInfo['calendar_id'],
                                                    'eventId'     => $eve,
                                                );
                                                $this->google->editEvent($array);}
                                        }
                                    }
                                }
                            }    
                        }
                    }
                }
                $this->common_model->update_data('users', array('sync_token' => $res['syncToken']), array('user_id' => $scannerInfo['user_id']));
            }
        } else {
            log_message('error', 'Webhook calls but channel id mismatch with database');
        }
    }

    public function get_diff_date($from, $to)
    {
        $date11       = strtotime($from);
        $date22       = strtotime($to);
        $diff         = $date11 - $date22;
        $diff_in_days = floor($diff / (60 * 60 * 24));
        if ($diff_in_days < 0) {
            $diff_in_days = $diff_in_days;
        } else {
            $diff_in_days = $diff_in_days + 1;
        }
        return $diff_in_days;
    }

    public function send_sms($mobile=null,$text=null)
    {
        // $this->load->library('sms');
        // $res = $this->sms->send('9780698969','fdsfdsfsfs');
        // echo "<pre>";
        // print_r($res);
    }

    //send mail to admin
    public function send_mail_to_admin($join_id)
    {
        log_message('error', "function called");
        if (!empty($join_id)) {
            $joiningInfo = $this->common_model->get_data('joinings', array('join_id' => $join_id), 'single');
            // print_r($joiningInfo);
            if (!empty($joiningInfo)) {
                $bookingInfo = $this->db->select('*')->where('booking_id', $joiningInfo['booking_id'])->get('bookings')->row_array();
                $clientInfo  = $this->db->select('*')->where('user_id', $bookingInfo['client_id'])->get('users')->row_array();

                $summary = array();
                //Edit Event Call here
                $summary[] = 'Status: ' . $joiningInfo['status'];

                if (!empty($postdata['notes'])) {
                    $summary[] = 'Name: ' . ucfirst(substr($clientInfo['first_name'], 0, 1)) . ' ' . ucfirst($clientInfo['last_name']) . " **";
                } else {
                    $summary[] = 'Name: ' . ucfirst(substr($clientInfo['first_name'], 0, 1)) . ' ' . ucfirst($clientInfo['last_name']);
                }
                $summary[] = 'Booking ID: ' . $joiningInfo['join_id'];
                $summary[] = 'Location: ' . $clientInfo['suburb'] . " " . $clientInfo['state'];
                $summary[] = 'Sheep: ' . $joiningInfo['number_of_sheep'];
                if (strtolower($joiningInfo['scan_type']) == 'single') {
                    $summary[] = 'Scan Type: WET/DRY';
                } else {
                    $summary[] = 'Scan Type: MULTIPLES';
                }
                $summary[] = 'Intro: ' . $joiningInfo['introduced_days'];
                $summary[] = 'Free: ' . $joiningInfo['joining_duration'];
                $summary[] = 'Phone: ' . $clientInfo['mobile_phone'];
                $summary[] = 'Landline: ' . $clientInfo['landline_phone'];

                $description       = array();
                $description[]     = "Notes: " . $bookingInfo['notes'];
                $description[]     = "Link: staging.technocratshorizons.com/sheeptimer/booking/view/" . $joiningInfo['join_id'];
                $description[]     = "Unavailable Days: ";
                $unavailable_dates = explode(',', $bookingInfo['unavailable_dates']);
                foreach ($unavailable_dates as $datesc) {
                    $description[] = $datesc;
                }
                $description2 = implode("<br/>", $description);
                $new_sum      = implode("<br/>", $summary);

                $details = $new_sum . "<br/>" . $description2;
                $link    = '<a href="http://staging.technocratshorizons.com/sheeptimer/booking/edit/' . $joiningInfo['join_id'] . '">Edit Booking</a>';

                $message         = $this->lang->line('Booking_mail_date_body');
                $replaceto       = array("__LINK", "__BOOKING_DETAILS");
                $replacewith     = array($link, $details);
                $data['content'] = str_replace($replaceto, $replacewith, $message);
                $subject         = $this->lang->line('Booking_mail_date_subject');
                $body            = $this->load->view('email/simple_content', $data, true);
                if (send_email(CAL_ADMIN_EMAIL, $subject, $body)) {
                    echo "success";
                } else {
                    echo "no";
                }
            }
        }
    }

    public function change_status($booking_id, $status)
    {
        if (!empty($booking_id) && !empty($status)) {
            $joiningInfo = $this->common_model->get_data('joinings', array('join_id' => $booking_id), 'single');
            if (!empty($joiningInfo)) {
                if ($status == $joiningInfo['status']) {
                    $notification['status'] = false;
                    $notification['msg']    = "Booking is already in same status";
                } else {
                    $this->update_event_calc($joiningInfo, $status);
                    $this->common_model->update_data('joinings', array('status' => $status), array('join_id' => $booking_id));
                    $notification['status'] = true;
                    $notification['msg']    = "Booking updated successfully";
                    if($status=='scheduled'){   
                        $this->send_confirmation($booking_id);
                    }
                }
            } else {
                $notification['status'] = false;
                $notification['msg']    = "No data found for this booking.Please refesh page try again";
            }
        }
        exit(json_encode($notification));
    }

    public function update_event_calc($joiningInfo = array(), $status = null)
    {
        $this->load->library('google');
        if (!empty($joiningInfo)) {
            $start          = date('Y-m-d', strtotime($joiningInfo['booking_date'])) . " 09:00:00";
            $end            = date('Y-m-d', strtotime($joiningInfo['booking_date_end'])) . " 13:00:00";
            $start          = date(DATE_ATOM, strtotime($start));
            $end            = date(DATE_ATOM, strtotime($end));
            $allEvents      = explode(',', $joiningInfo['event_ids']);
            $allScanners    = explode(',', $joiningInfo['scanner']);
            $bookingInfo2   = $this->db->select('*')->where('booking_id', $joiningInfo['booking_id'])->get('bookings')->row_array();
            $all_bookings   = explode(',', $bookingInfo2['unavailable_dates']);
            // $date_to_scan   = date('Y-m-d', strtotime($joiningInfo['booking_date']));
            $new_date       = date('Y-m-d', strtotime($joiningInfo['booking_date'][0]));
            $rooms_in_date  = date('Y-m-d', strtotime($joiningInfo['room_in']));
            $rooms_out_date = date('Y-m-d', strtotime($joiningInfo['room_out']));

            $introduced_days = $this->get_diff_date($new_date, $rooms_in_date);
            $room_free_days  = $this->get_diff_date($new_date, $rooms_out_date);
            foreach ($allEvents as $key => $eve) {
                if (!empty($eve)) {
                    $userInfo    = $this->db->select('*')->where('user_id', $allScanners[$key])->get('users')->row_array();
                    $bookingInfo = $this->db->select('*')->where('booking_id', $joiningInfo['booking_id'])->get('bookings')->row_array();
                    $clientInfo  = $this->db->select('*')->where('user_id', $bookingInfo['client_id'])->get('users')->row_array();
                    if (!empty($userInfo['calendar_id'])) {
                        $summary = array();
                        //Edit Event Call here
                        $summary[] = 'Status: ' . $status;

                        if (!empty($postdata['notes'])) {
                            $summary[] = 'Name: ' . ucfirst(substr($clientInfo['first_name'], 0, 1)) . ' ' . ucfirst($clientInfo['last_name']) . " **";
                        } else {
                            $summary[] = 'Name: ' . ucfirst(substr($clientInfo['first_name'], 0, 1)) . ' ' . ucfirst($clientInfo['last_name']);
                        }
                        $summary[] = 'Booking ID: ' . $joiningInfo['join_id'];
                        $summary[] = 'Location: ' . $clientInfo['suburb'] . " " . $clientInfo['state'];
                        $summary[] = 'Sheep: ' . $joiningInfo['number_of_sheep'];
                        if (strtolower($joiningInfo['scan_type']) == 'single') {
                            $summary[] = 'Scan Type: WET/DRY';
                        } else {
                            $summary[] = 'Scan Type: MULTIPLES';
                        }
                        $summary[]         = 'Intro: ' . $introduced_days;
                        $summary[]         = 'Free: ' . $room_free_days;
                        $summary[]         = 'Phone: ' . $clientInfo['mobile_phone'];
                        $summary[]         = 'Landline: ' . $clientInfo['landline_phone'];
                        $new_sum           = implode(', ', $summary);
                        $description       = array();
                        $description[]     = "Notes: " . $bookingInfo['notes'];
                        $description[]     = "Link: staging.technocratshorizons.com/sheeptimer/booking/view/" . $joiningInfo['join_id'];
                        $description[]     = "Unavailable Days: ";
                        $unavailable_dates = explode(',', $bookingInfo['unavailable_dates']);
                        foreach ($unavailable_dates as $datesc) {
                            $description[] = $datesc;
                        }
                        $description2 = implode("\n", $description);
                        $array        = array(
                            'summary'     => $new_sum,
                            'location'    => $clientInfo['address_1'] . ' ' . $clientInfo['address_2'] . ' ' . $clientInfo['post_code'] . ' ' . $clientInfo['state'],
                            'description' => $description2,
                            'startDate'   => $start,
                            'endDate'     => $end,
                            'calendarId'  => $userInfo['calendar_id'],
                            'eventId'     => $eve,
                        );
                        $this->google->editEvent($array);
                    }
                }
            }
        }
    }

    //Get date ranges from dates
    public function date_range($start_date, $end_date)
    {
        $start = strtotime($start_date);
        $end   = strtotime($end_date);
        $dates = array();
        while ($start <= $end) {
            array_push(
                $dates,
                date(
                    'd-m-Y',
                    $start
                )
            );
            $start += 86400;
        }
        return $dates;
    }



    public  function send_confirmation($Booking_Id=null)
    {
        $BookingInfo = $this->db->where(array('join_id'=>$Booking_Id))->select('j.*,b.client_id,b.unavailable_dates,b.client_confirmation_email,b.client_confirmation_sms,b.notes')->from('joinings     j')->join('bookings b','b.booking_id=j.booking_id','left')->get()->row_array();
        if(!empty($BookingInfo)){
            $clientInfo = $this->common_model->get_data('users',array('user_id'=>$BookingInfo['client_id']),'single');

            $scanners = explode(',',$BookingInfo['scanner']);
            $scannerInfo = $this->common_model->get_data('users',array('user_id'=>$scanners[0]),'single');

            $__SCANNING_DATES_MODULE = 'scanning date : <b> ' . date('d-m-Y', strtotime($BookingInfo['date_to_scan'])) . ' </b><br/>Approx. number: <b>' . $BookingInfo['number_of_sheep'] . '</b><br/><br/>
                '.$scannerInfo['first_name'].' '.$scannerInfo['last_name'].' will be scanning your sheep on the above date. He can be contacted on +61'.$scannerInfo['mobile_phone'].'.';

            if ($BookingInfo['client_confirmation_email']=='1') {
                $message         = $this->lang->line('Booking_mail_body');
                $replaceto       = array("__FIRST_NAME", "__SCANNING_DATES_MODULE");
                $replacewith     = array($clientInfo['first_name'] . " " . $clientInfo['last_name'], $__SCANNING_DATES_MODULE);
                $data['content'] = str_replace($replaceto, $replacewith, $message);
                $subject         = $this->lang->line('Booking_mail_subject');
                $body            = $this->load->view('email/simple_content', $data, true);
                send_email($clientInfo['email'], $subject, $body);
            }
        }


        if ($BookingInfo['client_confirmation_sms']=='1') {
            $sms_messgae = 'Sheep Pro booking confirmed. We are scanning '.$BookingInfo['number_of_sheep'].' sheeps on '.date('d-m-Y', strtotime($BookingInfo['date_to_scan'])).'. Call Mark on 0428386924 for any queries.';
            $response = $this->sms->send('61'.$clientInfo['mobile_phone'],$sms_messgae);
            if($response['data']['status']!='OK'){
                log_message('error',$response['message']);
            }
        }
    }


    public function regenrateBooking()
    {
        $response = array();
        $res   = file_get_contents("php://input");
        $_POST = json_decode($res, true);
        if (isset($_POST) && !empty($_POST))
        {
            $Booking_Id = $_POST['Booking_Id'];
            $this->load->library('google');
            $BookingInfo = $this->db->where(array('join_id'=>$Booking_Id,''))->select('j.*,b.client_id,b.unavailable_dates,b.client_confirmation_email,b.client_confirmation_sms,b.notes')->from('joinings     j')->join('bookings b','b.booking_id=j.booking_id','left')->get()->row_array();
            if(!empty($BookingInfo))
            {
                $clientInfo = $this->common_model->get_data('users',array('user_id'=>$BookingInfo['client_id']),'single');

                $notFound = array();
                $oldEvents = explode(",",$BookingInfo['event_ids']);
                $scanners = explode(',',$BookingInfo['scanner']);
                foreach($oldEvents as $key => $event)
                {
                    $userInfo = $this->common_model->get_data('users',array('user_id'=>$scanners[$key]),'single');
                    $this->google->deleteEvent($userInfo['calendar_id'],$event);
                }

                //Events insertion start here
                $events_ids   = array();
                foreach ($scanners as $scanner)
                {
                    $scannerInfo = $this->common_model->get_data('users', array('user_id' => $scanner), 'single');

                    $args = array(
                        'share_with'  => $clientInfo['email'],
                        'permission'  => 'reader',
                        'calendar_id' => $scannerInfo['calendar_id'],
                    );
                    $this->google->shareCal($args);
                    $summary   = array();
                    $summary[] = 'Status: '.$BookingInfo['status'];
                    if (!empty($postdata['notes'])) {
                        $summary[] = 'Name: ' . ucfirst(substr($clientInfo['first_name'], 0, 1)) . ' ' . ucfirst($clientInfo['last_name']) . " **";

                    } else {
                        $summary[] = 'Name: ' . ucfirst(substr($clientInfo['first_name'], 0, 1)) . ' ' . ucfirst($clientInfo['last_name']);
                    }
                    $summary[] = 'Booking ID: ' . $BookingInfo['join_id'];
                    $summary[] = 'Location: ' . $clientInfo['suburb'] . " " . $clientInfo['state'];
                    $summary[] = 'Sheep: ' . $BookingInfo['number_of_sheep'];
                    if (strtolower($BookingInfo['scan_type']) == 'single') {
                        $summary[] = 'Scan Type: WET/DRY';
                    } else {
                        $summary[] = 'Scan Type: MULTIPLES';
                    }
                    $summary[]         = 'Intro: ' . $BookingInfo['introduced_days'];
                    $summary[]         = 'Free: ' . $BookingInfo['room_free_days'];
                    $summary[]         = 'Phone: ' . $clientInfo['mobile_phone'];
                    $summary[]         = 'Landline: ' . $clientInfo['landline_phone'];
                    $new_sum           = implode(', ', $summary);
                    $start             = date('Y-m-d', strtotime($BookingInfo['booking_date'])) . " 09:00:00";
                    $end               = date('Y-m-d', strtotime($BookingInfo['booking_date_end'])) . " 13:00:00";
                    $start             = date(DATE_ATOM, strtotime($start));
                    $end               = date(DATE_ATOM, strtotime($end));
                    $description       = array();
                    $description[]     = "Notes: " . $BookingInfo['notes'];
                    $description[]     = "Link: staging.technocratshorizons.com/sheeptimer/booking/view/" . $BookingInfo['join_id'];
                    $description[]     = "Unavailable Days: ";
                    $unavailable_dates = explode(',', $BookingInfo['unavailable_dates']);
                    foreach ($unavailable_dates as $datesc) {
                        $description[] = $datesc;
                    }
                    $description2 = implode("\n", $description);
                    $array        = array(
                        'summary'     => $new_sum,
                        'location'    => $clientInfo['address_1'] . ' ' . $clientInfo['address_2'] . ' ' . $clientInfo['post_code'] . ' ' . $clientInfo['state'],
                        'description' => $description2,
                        'startDate'   => $start,
                        'endDate'     => $end,
                        'calendarId'  => $scannerInfo['calendar_id'],
                    );
                    $res = $this->google->addEvent($array);
                    if ($res['status']) {
                        $events_ids[] = $res['data'];
                    } else {
                        $events_ids[] = null;
                    }
                }
                if (!empty($events_ids)) {
                    $update_data = array(
                        'event_ids'  => implode(',', $events_ids),
                        'extra_date' => $start,
                        'google_delete' => 'Not'
                    );
                    $this->common_model->update_data('joinings', $update_data, array('join_id' => $BookingInfo['join_id']));
                }
                $response['status'] = true;
                $response['msg'] = 'Events regenrated successfully';
                //Event insertion end here
            }
            else
            {
                $response['status'] = false;
                $response['msg'] = "Booking not found";
            }
        } 
        else
        {
            $response['status'] = false;
            $response['msg']    = 'Please send data first';
        }
        exit(json_encode($response));
    }


    //Save Empty Booking Function start here
    public function saveEmptyBooking() {
        $response['status'] = false;
        $response['msg'] = "Booking not found";
        if($this->input->post()) {
            $input = $this->input->post();
            $this->form_validation->set_error_delimiters('', '');
            $this->form_validation->set_rules('first_name', 'first name', 'trim|required');
            $this->form_validation->set_rules('last_name', 'last name', 'trim|required');
            $this->form_validation->set_rules('company', 'company', 'trim|required');
            $this->form_validation->set_rules('email', 'email', 'trim|valid_email|required|is_unique[users.email]');
            $this->form_validation->set_rules('mobile_phone', 'mobile_phone', 'trim|required');
             $this->form_validation->set_rules('address_1', 'address_1', 'trim|required');
             $this->form_validation->set_rules('suburb', 'suburb', 'trim|required');
             $this->form_validation->set_rules('post_code', 'post_code', 'trim|required');
             $this->form_validation->set_rules('state', 'state', 'trim|required');
            if ($this->form_validation->run() == false) {
                $response['status'] = false;
                $response['msg']    = validation_errors();
            }
            else
            {
                $InsertClient = array(
                    'first_name' => $input['first_name'],
                    'last_name' => $input['last_name'],
                    'company' => $input['company'],
                    'email' => $input['email'],
                    'secondary_email' => @$input['secondary_email'],
                    'mobile_phone' => $input['mobile_phone'],
                    'landline_phone' => @$input['landline_phone'],
                    'address_1' => $input['address_1'],
                    'address_2' => $input['address_2'],
                    'suburb' => $input['suburb'],
                    'post_code' => $input['post_code'],
                    'state' => $input['state'],
                    'user_type' => 'Client'
                );
                if($this->common_model->insert_data('users',$InsertClient)){
                    $client_id = $this->db->insert_id();
                    $insertBooking = array(
                        'client_id' => $client_id,
                        'unavailable_dates' => @$input['unavailable_dates'],
                        // 'client_confirmation_email' => '1',
                        // 'client_confirmation_sms' =>'1',
                        'notes'=> $input['notes']
                    );
                    if(isset($input['Notifications']) && $input['Notifications']=='yes'){
                        $insertBooking['client_confirmation_email'] ='1';
                        $insertBooking['client_confirmation_sms'] ='1';
                    }else {
                        $insertBooking['client_confirmation_email'] ='0';
                        $insertBooking['client_confirmation_sms'] ='0';
                    }
                    if($this->common_model->insert_data('bookings',$insertBooking)){
                        $bookingId = $this->db->insert_id();
                        foreach($input['number_of_sheep'] as $key => $number_of_sheep){
                            $dateRange = explode(' - ',$input['daterange'][$key]);
                            $new_date_start = date('Y-m-d H:i:s');
                            $new_date_end   = date('Y-m-d H:i:s');
                            $rooms_in_date  = date('Y-m-d', strtotime($dateRange['0']));
                            $rooms_in_date_temop  = date('Y-m-d', strtotime($dateRange['0'])).' '.date('H:i:s');

                            $rooms_out_date = date('Y-m-d', strtotime($dateRange['1']));
                            $rooms_out_date_temp = date('Y-m-d', strtotime($dateRange['1'])).' '.date('H:i:s');;

                            $introduced_days = $this->get_diff_date($new_date_start, $rooms_in_date);
                            $room_free_days  = $this->get_diff_date($new_date_start, $rooms_out_date);
                            $joining_duration  = $this->get_diff_date($rooms_in_date, $rooms_out_date);
                            $insertJoining = array(
                                'booking_id' => $bookingId,
                                'number_of_sheep' => $number_of_sheep,
                                'scan_type' =>'Single',
                                'booking_date' => date(DATE_ATOM, strtotime($new_date_start)),
                                'scanner' =>'',
                                'room_in' => date(DATE_ATOM, strtotime($rooms_in_date_temop)),
                                'room_out' => date(DATE_ATOM, strtotime($rooms_out_date_temp)),
                                'introduced_days'=> $introduced_days,
                                'joining_duration' =>$joining_duration,
                                'room_free_days' =>$room_free_days,
                                'date_to_scan' => '',
                                'event_ids' => '',
                                'status' => 'booking-pending',
                                'booking_date_end' =>date(DATE_ATOM, strtotime($new_date_start))
                            );
                            $this->common_model->insert_data('joinings',$insertJoining); 
                            $last_join_id = $this->db->insert_id();
                            //Send Mail start here
                            $message         = $this->lang->line('Booking_form_mail_date_admin_body');
                            $replaceto       = array("__CLINET_NAME","__LINK");
                            $link = "Link: staging.technocratshorizons.com/sheeptimer/booking/edit/" . $last_join_id;
                            $replacewith     = array($input['first_name'].' '.$input['last_name'], $link);
                            $data['content'] = str_replace($replaceto, $replacewith, $message);
                            $subject         = $this->lang->line('Booking_form_mail_date_admin_subject');
                            $body            = $this->load->view('email/simple_content', $data, true);
                            if (send_email(CAL_ADMIN_EMAIL, $subject, $body)) {
                                // echo "success";
                            }
                            //end here
                        }
                        $response['status'] = true;
                        $response['msg'] = "Booking Submitted. We will notify you soon once it will be approved";
                    }
                }
                else {
                    $response['msg'] = "Unable to create client";
                }
            }
        }
        exit(json_encode($response));
    }
}
