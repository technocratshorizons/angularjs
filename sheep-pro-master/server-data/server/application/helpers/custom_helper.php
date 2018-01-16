<?php
function send_email($To, $Subject, $Content)
{
    $ci = &get_instance();
    $ci->load->library('email');
    $config['mailtype'] = 'html';
    $ci->email->initialize($config);
    $ci->email->from(FROM_EMAIL, FROM_NAME);
    $ci->email->to($To);
    $ci->email->subject($Subject);
    $ci->email->message($Content);
    if ($ci->email->send())
    {
        return true;
    }
    else
    {
        log_message('error', $ci->email->print_debugger());
        return false;
    }
}
function randomPassword()
{
    $alphabet    = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    $pass        = array();               //remember to declare $pass as an array
    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
    for ($i = 0; $i < 6; $i++)
    {
        $n      = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass); //turn the array into a string
}

function random($id)
{
    $alphabet    = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    $pass        = array();               //remember to declare $pass as an array
    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
    for ($i = 0; $i < $id; $i++)
    {
        $n      = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass); //turn the array into a string
}


function verificationcode()
{
    $string           = "1234567890";
    $string_shufffled = str_shuffle($string);
    $code             = substr($string_shufffled, 1, 5);
    return $code;
}

function get_role_id($usertype)
{
    if ($usertype == 'technician')
    {
        return '1';
    }
    else if ($usertype == 'shopowner')
    {
        return '2';
    }
    else if ($usertype == 'truckowner')
    {
        return '3';
    }
    else
    {
        return '0';
    }
}

function get_role_name($id)
{
    if ($id == '1')
    {
        return 'Technician';
    }
    else if ($id == '2')
    {
        return 'Shop owner';
    }
    else if ($id == '3')
    {
        return 'Truck owner';
    }
    else
    {
        return 'Admin';
    }
}

function get_role_type($id)
{
    if ($id == '1')
    {
        return 'technician';
    }
    else if ($id == '2')
    {
        return 'shopowner';
    }
    else if ($id == '3')
    {
        return 'truckowner';
    }
    else
    {
        return 'admin';
    }
}
function date_visible($date)
{
    if ($date == '0000-00-00 00:00:00')
    {
        return "N/A";
    }
    else
    {
        $myDateTime           = DateTime::createFromFormat('Y-m-d H:i:s', $date);
        return $newDateString = $myDateTime->format('M d, Y h:i A');
    }
}

function date_visible_without_time($date)
{
    if ($date == '0000-00-00')
    {
        return "N/A";
    }
    else
    {
        $myDateTime           = DateTime::createFromFormat('Y-m-d', $date);
        return $newDateString = $myDateTime->format('M d, Y');
    }
}

function date_visible_without_time2($date)
{
    if ($date == '0000-00-00 00:00:00')
    {
        return "N/A";
    }
    else
    {
        $myDateTime           = DateTime::createFromFormat('Y-m-d H:i:s', $date);
        return $newDateString = $myDateTime->format('M d, Y');
    }
}

function date_set($date)
{
    if (empty($date))
    {
        return "N/A";
    }
    else
    {
        $myDateTime           = DateTime::createFromFormat('M d, Y', $date);
        return $newDateString = $myDateTime->format('Y-m-d');
    }
}

function date_set_api($date)
{
    if (empty($date))
    {
        return "N/A";
    }
    else
    {
        $myDateTime = DateTime::createFromFormat('m-d-Y', $date);

        return $newDateString = $myDateTime->format('Y-m-d');
    }
}

function image_exist($value = '')
{
    return false;
}

function convert($value)
{
    if ($value == 1)
    {
        return 0;
    }
    else
    {
        return 1;
    }
}
