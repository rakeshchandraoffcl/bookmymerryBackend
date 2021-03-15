<?php
require(dirname(__FILE__) . '/../config/db.php');
class Resources extends DB
{


    function getAmenities()
    {

        $amenities = [];
        $query = 'SELECT id,name from amenity WHERE status = 1';
        $stmt = $this->conn->prepare($query);
        if (
            $stmt &&
            $stmt->execute() &&
            $stmt->store_result() &&
            $stmt->bind_result($id, $name)

        ) {
            while ($stmt->fetch()) {
                array_push($amenities, array("id" => $id, "name" => $name));
            }

            return array("status" => "success", "data" => $amenities);
        } else {
            if ($this->conn->error) {
                return array("status" => "fail", "error" => $this->conn->error);
            }
            if ($stmt->error) {
                return array("status" => "fail", "error" => $stmt->error);
            }
        }
    }

    function getEvents()
    {

        $events = [];
        $query = 'SELECT id,name from event WHERE status = 1';
        $stmt = $this->conn->prepare($query);
        if (
            $stmt &&
            $stmt->execute() &&
            $stmt->store_result() &&
            $stmt->bind_result($id, $name)

        ) {
            while ($stmt->fetch()) {
                array_push($events, array("id" => $id, "name" => $name));
            }

            return array("status" => "success", "data" => $events);
        } else {
            if ($this->conn->error) {
                return array("status" => "fail", "error" => $this->conn->error);
            }
            if ($stmt->error) {
                return array("status" => "fail", "error" => $stmt->error);
            }
        }
    }

    function getVenueTypes()
    {

        $eventTypes = [];
        $query = 'SELECT id,name,image from venue_type_name WHERE status = 1';
        $stmt = $this->conn->prepare($query);
        if (
            $stmt &&
            $stmt->execute() &&
            $stmt->store_result() &&
            $stmt->bind_result($id, $name, $image)

        ) {
            while ($stmt->fetch()) {
                array_push($eventTypes, array("id" => $id, "name" => $name, "image" => $image ? $this->host . "/images/venueType_images/" . $image : ""));
            }

            return array("status" => "success", "data" => $eventTypes);
        } else {
            if ($this->conn->error) {
                return array("status" => "fail", "error" => $this->conn->error);
            }
            if ($stmt->error) {
                return array("status" => "fail", "error" => $stmt->error);
            }
        }
    }
    function getVendorTypes()
    {

        $eventTypes = [];
        $query = 'SELECT id,name from vendor_type WHERE status = 1';
        $stmt = $this->conn->prepare($query);
        if (
            $stmt &&
            $stmt->execute() &&
            $stmt->store_result() &&
            $stmt->bind_result($id, $name)

        ) {
            while ($stmt->fetch()) {
                array_push($eventTypes, array("id" => $id, "name" => $name));
            }

            return array("status" => "success", "data" => $eventTypes);
        } else {
            if ($this->conn->error) {
                return array("status" => "fail", "error" => $this->conn->error);
            }
            if ($stmt->error) {
                return array("status" => "fail", "error" => $stmt->error);
            }
        }
    }

    function getTimeSlots()
    {

        $timeSlots = [];
        $query = 'SELECT id,slot,slot_name FROM  time_slot WHERE status = 1';
        $stmt = $this->conn->prepare($query);
        if (
            $stmt &&
            $stmt->execute() &&
            $stmt->store_result() &&
            $stmt->bind_result($id, $slot, $slot_name)

        ) {
            while ($stmt->fetch()) {
                array_push($timeSlots, array("id" => $id, "slot" => $slot, "slot_name" => $slot_name));
            }

            return array("status" => "success", "data" => $timeSlots);
        } else {
            if ($this->conn->error) {
                return array("status" => "fail", "error" => $this->conn->error);
            }
            if ($stmt->error) {
                return array("status" => "fail", "error" => $stmt->error);
            }
        }
    }

    function getCities()
    {

        $timeSlots = [];
        $query = 'SELECT id,city_name FROM  city WHERE is_active = 1 ORDER BY top_city DESC,city_name';
        $stmt = $this->conn->prepare($query);
        if (
            $stmt &&
            $stmt->execute() &&
            $stmt->store_result() &&
            $stmt->bind_result($id, $city_name)

        ) {
            while ($stmt->fetch()) {
                array_push($timeSlots, array("id" => $id, "name" => $city_name));
            }

            return array("status" => "success", "data" => $timeSlots);
        } else {
            if ($this->conn->error) {
                return array("status" => "fail", "error" => $this->conn->error);
            }
            if ($stmt->error) {
                return array("status" => "fail", "error" => $stmt->error);
            }
        }
    }




    function getResources()
    {

        $resources = array(
            "events" => $this->getEvents()['data'],
            "amenities" => $this->getAmenities()['data'],
            "types" => $this->getVenueTypes()['data'],
            "timeSlots" => $this->getTimeSlots()['data'],
            "cities" => $this->getCities()['data'],
            "vendorTypes" => $this->getVendorTypes()['data']
        );

        return array("status" => "success", "data" => $resources);
    }
}
