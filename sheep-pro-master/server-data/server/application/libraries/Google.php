<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Google
{
    public function __construct()
    {
        $CI           = &get_instance();
        $this->client = new Google_Client();
        if (file_exists(CREDENTIALS_PATH)) {
            $this->client->setAuthConfig(CREDENTIALS_PATH);
        } elseif (getenv('GOOGLE_APPLICATION_CREDENTIALS')) {
            // use the application default credentials
            $this->client->useApplicationDefaultCredentials();
        } else {
            exit(json_encode(array('status' => false, 'msg' => missingServiceAccountDetailsWarning())));
        }
        $this->client->setApplicationName(APPLICATION_NAME);
        $this->client->addScope("https://www.googleapis.com/auth/calendar");
        $this->client->setAccessType('online');
    }

    //Event List API
    public function listEvents($calendarId = null, $optParams = array())
    {
        try
        {
            $service = new Google_Service_Calendar($this->client);
            $results = $service->events->listEvents($calendarId, $optParams);
            if (count($results->getItems()) == 0) {
                return array('status' => false, 'msg' => "No upcoming events found.");
            } else {
                $all_events = array();
                foreach ($results->getItems() as $key => $event) {
                    $start = $event->start->dateTime;
                    $end = $event->end->dateTime;
                    if (empty($start)) {
                        $start = $event->start->date;
                        $end = $event->end->date;
                    }
                    $all_events[$key]['id'] = $event->id;
                    $all_events[$key]['title'] = $event->getSummary();
                    $all_events[$key]['start']  = $start;
                    $all_events[$key]['end']  = $end;
                }
                $syncToken = $results->nextSyncToken;
                return array('status' => true, 'data' => $all_events,'syncToken'=>$syncToken);
            }
        } catch (Exception $e) {
            return array('status' => false, 'msg' => $e->getMessage());
        }
    }


    //Delete Events here
    public  function deleteEvent($Calender_Id=null,$Event_Id)
    {
        try {
            $service = new Google_Service_Calendar($this->client);
            $service->events->delete($Calender_Id, $Event_Id);
            return array('status'=> true, 'msg' => 'Event has been deleted successfully');
        } catch (Exception $e) {
            return array('status' => false, 'msg' => $e->getMessage());
        }
    }


    //Calendar List API
    public function getCalenderList($code = null)
    {
        try
        {
            $results      = array();
            $service      = new Google_Service_Calendar($this->client);
            $calendarList = $service->calendarList->listCalendarList();
            $k            = 0;
            while (true) {
                foreach ($calendarList->getItems() as $calendarListEntry) {
                    $results[$k]['title'] = $calendarListEntry->getSummary();
                    $results[$k]['id']    = $calendarListEntry->getId();
                    $k++;
                }
                $pageToken = $calendarList->getNextPageToken();
                if ($pageToken) {
                    $optParams    = array('pageToken' => $pageToken);
                    $calendarList = $service->calendarList->listCalendarList($optParams);
                } else {
                    break;
                }
            }
            return array('status' => true, 'data' => $results);
        } catch (Exception $e) {
            return array('status' => false, 'msg' => $e->getMessage());
        }
    }

    //Add Calendar Event
    public function addCalendar($args = array())
    {
        try
        {
            $service  = new Google_Service_Calendar($this->client);
            $calendar = new Google_Service_Calendar_Calendar();
            $calendar->setSummary($args['title']);
            $calendar->setTimeZone($args['timeZone']);
            $createdCalendar = $service->calendars->insert($calendar);
            return array('status' => true, 'data' => $createdCalendar->getId());

        } catch (Exception $e) {
            return array('status' => false, 'msg' => $e->getMessage());
        }
    }

    //Add Event to Calendar
    public function addEvent($args = array())
    {
        try
        {
            $event = new Google_Service_Calendar_Event(array(
                'summary'     => $args['summary'],
                'location'    => $args['location'],
                'description' => $args['description'],
                'start'       => array(
                    'dateTime' => $args['startDate'],
                    'timeZone' => 'America/Los_Angeles',
                ),
                'end'         => array(
                    'dateTime' => $args['endDate'],
                    'timeZone' => 'America/Los_Angeles',
                ),
            ));
            $service = new Google_Service_Calendar($this->client);
            $event   = $service->events->insert($args['calendarId'], $event);
            return array('status' => true, 'data' => $event->id);
        } catch (Exception $e) {
            return array('status' => false, 'msg' => $e->getMessage());
        }
    }

    //Edit Event Here
    public function editEvent($args = array())
    {
        try
        {
            $service = new Google_Service_Calendar($this->client);
            $event   = $service->events->get($args['calendarId'], $args['eventId']);
            $event->setSummary($args['summary']);
            $event->setDescription($args['description']);

            //Set Start date
            $start = new Google_Service_Calendar_EventDateTime();
            $start->setDateTime($args['startDate']);
            $event->setStart($start);

            //Set End date
            $end = new Google_Service_Calendar_EventDateTime();
            $end->setDateTime($args['endDate']);
            $event->setEnd($end);

            $updatedEvent = $service->events->update($args['calendarId'], $event->getId(), $event);

            // Print the updated date.
            return array('status' => true, 'data' => $updatedEvent->getUpdated());
        } catch (Exception $e) {
            return array('status' => false, 'msg' => $e->getMessage());
        }
    }


    //Edit Event Date only Here
    public function editEventDate($args = array())
    {
        try
        {
            $service = new Google_Service_Calendar($this->client);
            $event   = $service->events->get($args['calendarId'], $args['eventId']);

            //Set Start date
            $start = new Google_Service_Calendar_EventDateTime();
            $start->setDateTime($args['startDate']);
            $event->setStart($start);

            //Set End date
            $end = new Google_Service_Calendar_EventDateTime();
            $end->setDateTime($args['endDate']);
            $event->setEnd($end);

            $updatedEvent = $service->events->update($args['calendarId'], $event->getId(), $event);

            // Print the updated date.
            return array('status' => true, 'data' => $updatedEvent->getUpdated());
        } catch (Exception $e) {
            return array('status' => false, 'msg' => $e->getMessage());
        }
    }

    //Get Colors first
    public function getColor($value = '')
    {
        $service = new Google_Service_Calendar($this->client);
        $colors  = $service->colors->get();
        // Print available calendarListEntry colors.
        echo "<div>Calendar Events colors</div>";
        foreach ($colors->getCalendar() as $key => $color) {
            print " <ul><li>colorId : {$key}</li>";
            print " <li><div style='height:20px; width:20px;background:{$color->getBackground()}'></div>";
            print " <div style='height:20px; width:20px;background:{$color->getForeground()}'></div><li></ul>";
        }
        echo "<div>available Events colors</div>";
        // Print available event colors.
        foreach ($colors->getEvent() as $key => $color) {
            print " <ul><li>colorId : {$key}</li>";
            print " <li><div style='height:20px; width:20px;background:{$color->getBackground()}'></div>";
            print " <div style='height:20px; width:20px;background:{$color->getForeground()}'></div><li></ul>";
        }
    }

    public function shareCal($args = array())
    {
        try
        {
            $rule  = new Google_Service_Calendar_AclRule();
            $scope = new Google_Service_Calendar_AclRuleScope();
            $scope->setType("user");
            $scope->setValue($args['share_with']);
            $rule->setScope($scope);
            $rule->setRole($args['permission']);
            $service     = new Google_Service_Calendar($this->client);
            $createdRule = $service->acl->insert($args['calendar_id'], $rule);
            return $createdRule->getId();
        } catch (Exception $e) {
            return array('status' => false, 'msg' => $e->getMessage());
        }
        /*
        <-------------Permisson accepted parameters --------------->

        "freeBusyReader" - Provides read access to free/busy information.
        "reader" - Provides read access to the calendar. Private events will appear to users with reader access, but event details will be hidden.
        "writer" - Provides read and write access to the calendar. Private events will appear to users with writer access, and event details will be visible.
        "owner" - Provides ownership of the calendar. This role has all of the permissions of the writer role with the additional ability to see and manipulate ACLs.
         */
    }

    public function setHook($calendarId)
    {
        $channel_id = $this->random(8) . '-' . $this->random(4) . '-' . $this->random(4) . '-' . $this->random(4) . '-' . $this->random(12);
        try
        {
            $service = new Google_Service_Calendar($this->client);
            $channel = new Google_Service_Calendar_Channel($this->client);
            $channel->setId($channel_id);
            $channel->setType('web_hook');
            $channel->setAddress(NOTIFICATION_URL);
            $watchEvent = $service->events->watch($calendarId, $channel, array());
            return array('status' => true, 'data' => $channel_id);
        } catch (Exception $e) {
            return array('status' => false, 'msg' => $e->getMessage());
        }
    }

    public function random($id)
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

}
