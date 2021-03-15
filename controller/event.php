<?php
require(dirname(__FILE__) . '/../config/db.php');
class Event extends DB
{
    private $table = 'event';

    function addEvent($name, $status)
    {
        $stmt = $this->conn->prepare('INSERT INTO ' . $this->table . ' SET
        name=?,
        status=?,
        created_at=NOW()
        ');
        if (
            $stmt &&
            $stmt->bind_param('si', $name, $status) &&
            $stmt->execute()
        ) {
            $user_details = $this->getDetailsById($this->conn->insert_id);
            return array("status" => "success", "data" => $user_details['data']);
        } else {
            return array("status" => "fail", "error" => $this->conn->error);
        }
    }



    function updateEvent($data, $id)
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
        $query .= 'updated_at=NOW()';

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
        $stmt = $this->conn->prepare('SELECT id,name,created_at,updated_at,status from ' . $this->table . ' WHERE id=?');
        if (
            $stmt &&
            $stmt->bind_param('i', $id) &&
            $stmt->execute() &&
            $stmt->store_result() &&
            $stmt->bind_result($id, $name, $created_at, $updated_at, $status)
        ) {
            if ($stmt->fetch()) {
                $details["id"] = $id;
                $details["name"] = $name;
                $details["created_at"] = $created_at;
                $details["updated_at"] = $updated_at;
                $details["status"] = $status ? true : false;
                return array("status" => "success", "data" => $details);
            } else {
                return array("status" => "fail", "error" => "event not found");
            }
        } else {
            return array("status" => "fail", "error" => $this->conn->error);
        }
    }





    function getEvents($count, $page, $search, $all = false)
    {
        $limit = $this->parse($count);
        $offset = ($this->parse($page) - 1) * $limit;
        $events = [];
        $onlyActive = !$all ? 'WHERE status=1' : '';
        $onlyActive .= !$all ? ($search ? ' AND name LIKE "%' . $search . '%"' : '') : ($search ? ' WHERE name LIKE "%' . $search . '%"' : '');
        $query = 'SELECT  id,name,created_at,updated_at,status from ' . $this->table . ' ' . $onlyActive . ' ORDER BY id DESC LIMIT ' . $limit . ' OFFSET ' . $offset . '';
        // echo $query;
        $stmt = $this->conn->prepare($query);
        if (
            $stmt &&
            $stmt->execute() &&
            $stmt->store_result() &&
            $stmt->bind_result($id, $name, $created_at, $updated_at, $status)

        ) {
            while ($stmt->fetch()) {
                array_push($events, array("id" => $id, "name" => $name,  "created_at" => $created_at, "updated_at" => $updated_at, "status" => $status  ? true : false));
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
