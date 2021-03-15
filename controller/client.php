<?php
require(dirname(__FILE__) . '/../config/db.php');
class Client extends DB
{
    private $table = 'client';

    function addClient($data)
    {
        $stmt = $this->conn->prepare('INSERT INTO ' . $this->table . ' SET
        name=?,
        business=?,
        city=?,
        business_type=?,
        client_name=?,
        mobile=?,
        email=?,
        password=?,
        comments=?,
        object=?,
        created_at=NOW()
        ');
        if (
            $stmt &&
            $stmt->bind_param('ssisssssss', $data['name'], $data['business'], $data['city'], $data['business_type'], $data['client_name'], $data['mobile'], $data['email'], $data['password'], $data['comments'], $data['object']) &&
            $stmt->execute()
        ) {
            $user_details = $this->getDetailsById($this->conn->insert_id);
            return array("status" => "success", "data" => $user_details['data']);
        } else {
            return array("status" => "fail", "error" => $this->conn->error);
        }
    }



    function updateAmenity($data, $id)
    {

        $fields = $data;
        $query = ' UPDATE ' . $this->table . ' SET ';
        foreach ($fields as $key => $value) {
            if ($key !== 'id') {
                if ($key == 'status') {
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
        $stmt = $this->conn->prepare('SELECT id,business,city,business_type,client_name,mobile,email,password,comments,object,created_at,updated_at,status ' . $this->table . ' WHERE id=?');
        if (
            $stmt &&
            $stmt->bind_param('i', $id) &&
            $stmt->execute() &&
            $stmt->store_result() &&
            $stmt->bind_result($id, $business, $city, $business_type, $client_name, $mobile, $email, $password, $comments, $object, $created_at, $updated_at, $status)
        ) {
            if ($stmt->fetch()) {
                $details["id"] = $id;
                $details["business"] = $business;
                $details["city"] = $city;
                $details["business_type"] = $business_type;
                $details["client_name"] = $client_name;
                $details["mobile"] = $mobile;
                $details["email"] = $email;
                $details["comments"] = $comments;
                $details["object"] = $object;
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





    function getAmenities($count, $page, $search, $all = false)
    {
        $limit = $this->parse($count);
        $offset = ($this->parse($page) - 1) * $limit;
        $events = [];
        $onlyActive = !$all ? 'WHERE status=1' : '';
        $onlyActive .= !$all ? ($search ? ' AND name LIKE "%' . $search . '%"' : '') : ($search ? ' WHERE name LIKE "%' . $search . '%"' : '');
        $query = 'SELECT  id,name,status from ' . $this->table . ' ' . $onlyActive . ' ORDER BY id DESC LIMIT ' . $limit . ' OFFSET ' . $offset . '';
        // echo $query;
        $stmt = $this->conn->prepare($query);
        if (
            $stmt &&
            $stmt->execute() &&
            $stmt->store_result() &&
            $stmt->bind_result($id, $name, $status)

        ) {
            while ($stmt->fetch()) {
                array_push($events, array("id" => $id, "name" => $name, "status" => $status  ? true : false));
            }

            $totalCounts = $this->rowCounts($search);
            return array("status" => "success", "data" => array("events" => $events, "totalCount" => $totalCounts, "totalPages" => ceil($totalCounts / $limit)));
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

        $searchQuery = $search ? ' WHERE name LIKE "%' . $search . '%"' : '';

        $query = 'SELECT COUNT(*) as totalDocs from ' . $this->table . $searchQuery;
        $stmt = $this->conn->prepare($query);

        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($totalDocs);
        $stmt->fetch();
        return $totalDocs;
    }
}
