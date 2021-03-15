<?php
require(dirname(__FILE__) . '/../config/db.php');
include_once(dirname(__FILE__) . '/../utils/helper.php');
class VenueQuery extends DB
{
    private $table = 'venue_query';
    function addVenueQuery($event, $date, $time_slot, $city, $max_guests, $min_price, $max_price, $user_name, $email, $phone_number)
    {
        $otp = mt_rand(1000, 9999);
        $stmt = $this->conn->prepare('INSERT INTO ' . $this->table . ' SET
        event=?,
        date=?,
        time_slot=?, 
        city=?, 
        max_guests=?,
        min_price=?,
        max_price=?,
        user_name=?,
        email=?,
        phone_number=?,
        otp=?,
        created_at=NOW()
        ');
        if (
            $stmt &&
            $stmt->bind_param('isiiiiisssi', $event, $date, $time_slot, $city, $max_guests, $min_price, $max_price, $user_name, $email, $phone_number, $otp) &&
            $stmt->execute()
        ) {
            $user_details = $this->getDetailsById($this->conn->insert_id);
            $message = 'Your bookmymerry mobile verification code is ' . $otp;
            send_text($phone_number, $message);
            return array("status" => "success", "data" => $user_details['data']);
        } else {
            return array("status" => "fail", "error" => $this->conn->error);
        }
    }

    function verifyNumber($id, $otp)
    {
        $query = 'UPDATE ' . $this->table . ' SET ';
        $query .= 'otp=NULL,
        number_verified=1 
        ';
        $query .= ' WHERE id=? AND otp=?';
        $stmt = $this->conn->prepare($query);
        if (
            $stmt &&
            $stmt->bind_param('ii', $id, $otp) &&
            $stmt->execute() &&
            $stmt->affected_rows === 1
        ) {
            $user_details = $this->getDetailsById($id);
            return array("status" => "success", "data" => $user_details['data']);
        } else {
            if ($this->conn->error) {
                return array("status" => "fail", "error" => $this->conn->error);
            }
            if ($stmt->error) {
                return array("status" => "fail", "error" => $stmt->error);
            }

            return array("status" => "fail", "error" => "Invalid otp");
        }
    }


    function updateVenueQuery($data, $id)
    {

        $fields = $data;
        $query = ' UPDATE ' . $this->table . ' SET ';
        foreach ($fields as $key => $value) {
            if ($key !== 'id') {
                if ($key !== 'user_name' && $key !== 'phone_number' && $key !== 'email') {
                    $query .= $key . '=' . $value . ',';
                } else {
                    $query .= $key . '="' . $value . '",';
                }
            }
        };
        $query = substr($query, 0, -1);

        $query .= ' WHERE id=' . $id;
        // echo $query;
        $stmt = $this->conn->prepare($query);
        if (
            $stmt &&
            $stmt->execute() &&
            ($stmt->affected_rows === 1 || $stmt->affected_rows === 0)
        ) {
            $user_details = $this->getDetailsById($id);
            return array("status" => "success", "data" => $user_details['data']);
        } else {
            if ($this->conn->error) {
                return array("status" => "fail", "error" => $this->conn->error);
            }
            if ($stmt->error) {
                return array("status" => "fail", "error" => $stmt->error);
            }
        }
    }



    function getDetailsById($id)
    {
        $details = array();
        $stmt = $this->conn->prepare('SELECT 
        vq.id,
        (SELECT name FROM event WHERE id=vq.event) as event,
        (SELECT slot_name FROM time_slot WHERE id=vq.time_slot) as time_slot,
        (SELECT city_name FROM city WHERE id=vq.city) as city_name,
        (SELECT id FROM city WHERE id=vq.city) as city_id,
        vq.date,
        vq.max_guests,
        vq.min_price,
        vq.max_price,
        vq.user_name,
        vq.email,
        vq.phone_number,
        vq.created_at,
        vq.status 
        from ' . $this->table . ' vq WHERE id=?');
        if (
            $stmt &&
            $stmt->bind_param('i', $id) &&
            $stmt->execute() &&
            $stmt->store_result() &&
            $stmt->bind_result($id, $event, $time_slot, $city_name, $city_id, $date, $max_guests, $min_price, $max_price, $user_name, $email, $phone_number, $created_at, $status)
        ) {
            if ($stmt->fetch()) {
                $details["id"] = $id;
                $details["event"] = $event;
                $details["time_slot"] = $time_slot;
                $details["city_name"] = $city_name;
                $details["city_id"] = $city_id;
                $details["date"] = $date;
                $details["max_guests"] = $max_guests;
                $details["min_price"] = $min_price;
                $details["max_price"] = $max_price;
                $details["user_name"] = $user_name;
                $details["email"] = $email;
                $details["phone_number"] = $phone_number;
                $details["created_at"] = $created_at;
                $details["status"] = $status ? true : false;
                return array("status" => "success", "data" => $details);
            } else {
                return array("status" => "fail", "error" => "event not found");
            }
        } else {
            return array("status" => "fail", "error" => $this->conn->error);
        }
    }





    function getVenueQueries($count, $page, $search, $all = false)
    {
        $limit = $this->parse($count);
        $offset = ($this->parse($page) - 1) * $limit;
        $events = [];
        $onlyActive = !$all ? 'WHERE status=1' : '';
        $onlyActive .= !$all ? ($search ? ' AND full_name LIKE "%' . $search . '%"' : '') : ($search ? ' WHERE full_name LIKE "%' . $search . '%"' : '');
        $query = 'SELECT 
        vq.id,
        (SELECT name FROM event WHERE id=vq.event) as event,
        (SELECT slot_name FROM time_slot WHERE id=vq.time_slot) as time_slot,
        (SELECT city_name FROM city WHERE id=vq.city) as city_name,
        (SELECT id FROM city WHERE id=vq.city) as city_id,
        vq.date,
        vq.max_guests,
        vq.min_price,
        vq.max_price,
        vq.user_name,
        vq.email,
        vq.phone_number,
        vq.created_at,
        vq.status FROM ' . $this->table . ' vq ' . $onlyActive . ' ORDER BY id DESC LIMIT ' . $limit . ' OFFSET ' . $offset . '';
        // echo $query;
        $stmt = $this->conn->prepare($query);
        if (
            $stmt &&
            $stmt->execute() &&
            $stmt->store_result() &&
            $stmt->bind_result($id, $event, $time_slot, $city_name, $city_id, $date, $max_guests, $min_price, $max_price, $user_name, $email, $phone_number, $created_at, $status)

        ) {
            while ($stmt->fetch()) {
                array_push($events, array(
                    "id" => $id,
                    "event" => $event,
                    "time_slot" => $time_slot,
                    "city_name" => $city_name,
                    "city_id" => $city_id,
                    "date" => $date,
                    "max_guests" => $max_guests,
                    "min_price" => $min_price,
                    "max_price" => $max_price,
                    "user_name" => $user_name,
                    "email" => $email,
                    "phone_number" => $phone_number,
                    "created_at" => $created_at,
                    "status" => $status  ? true : false
                ));
            }

            $totalCounts = $this->rowCounts($search);
            return array("status" => "success", "data" => array("queries" => $events, "totalCount" => $totalCounts, "totalPages" => ceil($totalCounts / $limit)));
        } else {
            if ($this->conn->error) {
                return array("status" => "fail", "error" => $this->conn->error);
            }
            if ($stmt->error) {
                return array("status" => "fail", "error" => $stmt->error);
            }
        }
    }


    function rowCounts($search)
    {

        $searchQuery = $search ? ' WHERE full_name LIKE "%' . $search . '%"' : '';

        $query = 'SELECT COUNT(*) as totalDocs from ' . $this->table . $searchQuery;
        $stmt = $this->conn->prepare($query);

        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($totalDocs);
        $stmt->fetch();
        return $totalDocs;
    }
}
