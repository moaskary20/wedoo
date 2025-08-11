<?php
function get_available_slots1($partner_id, $booking_date)
{
    $today = date('Y-m-d');
    if ($booking_date < $today) {
        $response['error'] = true;
        $response['message'] = "please select upcoming date!";
        return $response;
    }
    $day = date('l', strtotime($booking_date));
    $timings = getTimingOfDay($partner_id, $day);
    if (isset($timings) && !empty($timings)) {
        $opening_time = $timings['opening_time'];
        $closing_time =  $timings['closing_time'];
        $booked_slots = booked_timings($partner_id, $booking_date);
        $array_of_time = [];
        $interval  = 30 * 60;
        $start_time = strtotime($opening_time);
        $end_time = strtotime($closing_time);
        $count = count($booked_slots);
        $available_slots = [];
        $busy_slot = [];

        if (isset($booked_slots) && !empty($booked_slots)) {
            while ($start_time <= $end_time) {
                $array_of_time[] = date("H:i:s", $start_time);
                $start_time += $interval;
            }
            $count_suggestion_slots = count($array_of_time);
            for ($i = 0; $i < $count; $i++) {
                for ($j = 0; $j < $count_suggestion_slots; $j++) {
                    if ($array_of_time[$j] < $booked_slots[$i]['starting_time'] || $array_of_time[$j] > $booked_slots[$i]['ending_time']) {
                        if (!in_array($array_of_time[$j], $available_slots))
                            $available_slots[] = $array_of_time[$j];
                    } else {
                        $busy_slot[] = $array_of_time[$j];
                    }
                }
                $count_busy_slot = count($busy_slot);
                for ($k = 0; $k < $count_busy_slot; $k++) {
                    if (($key = array_search($busy_slot[$k], $available_slots)) !== false) {
                        unset($available_slots[$key]);
                    }
                }
            }
            $response['error'] = false;
            $response['available_slots'] = $available_slots;
            $response['busy_slot'] = $busy_slot;
            return $response;
        } else {
            while ($start_time <= $end_time) {
                $array_of_time[] = date("H:i:s", $start_time);
                $start_time += $interval;
            }
            $response['error'] = false;
            $response['available_slots'] = $array_of_time;
            $response['busy_slot'] = $busy_slot;
            return $response;
        }
    } else {
        $response['error'] = true;
        $response['message'] = "provider is closed on this day";
        return $response;
    }
}
