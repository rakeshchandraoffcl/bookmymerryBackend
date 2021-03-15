<?php
require(dirname(__FILE__) . '/../config/db.php');
class VenueType extends DB
{
    private $table = 'venue_type_name';
    function getVenueTypeImage($img)
    {

        return $img ? $this->host .  "/images/venueType_images/" . $img : "";
    }

    function addVenueType($name, $status, $image)
    {
        $stmt = $this->conn->prepare('INSERT INTO ' . $this->table . ' SET
        name=?,
        status=?,
        image=?
        ');
        if (
            $stmt &&
            $stmt->bind_param('sis', $name, $status, $image) &&
            $stmt->execute()
        ) {
            $user_details = $this->getDetailsById($this->conn->insert_id);
            return array("status" => "success", "data" => $user_details['data']);
        } else {
            return array("status" => "fail", "error" => $this->conn->error);
        }
    }

    function updateVenueType($data, $id)
    {

        $fields = $data;
        $query = ' UPDATE ' . $this->table . ' SET ';


        if (isset($fields['image'])) {
            $details = $this->getDetailsById($id);
            if ($details['status'] === 'success') {
                if ($details['data']['image']) {

                    unlink('../images/venueType_images/' . $details['data']['image']);
                }
            } else {
                return array("status" => "fail", "error" => $details['error']);
            }
        }
        foreach ($fields as $key => $value) {
            if ($key == 'status') {
                $query .= $key . '=' . $value . ',';
            } else {

                $query .= $key . '="' . $value . '",';
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
        $stmt = $this->conn->prepare('SELECT id,name,status,image from ' . $this->table . ' WHERE id=?');
        if (
            $stmt &&
            $stmt->bind_param('i', $id) &&
            $stmt->execute() &&
            $stmt->store_result() &&
            $stmt->bind_result($id, $name, $status, $image)
        ) {
            if ($stmt->fetch()) {
                $details["id"] = $id;
                $details["name"] = $name;
                $details["image"] = $image;
                $details["status"] = $status ? true : false;
                return array("status" => "success", "data" => $details);
            } else {
                return array("status" => "fail", "error" => "event not found");
            }
        } else {
            return array("status" => "fail", "error" => $this->conn->error);
        }
    }





    function getVenueTypes($count, $page, $search, $all = false)
    {
        $limit = $this->parse($count);
        $offset = ($this->parse($page) - 1) * $limit;
        $venueTypes = [];
        $onlyActive = !$all ? 'WHERE status=1' : '';
        $onlyActive .= !$all ? ($search ? ' AND name LIKE "%' . $search . '%"' : '') : ($search ? ' WHERE name LIKE "%' . $search . '%"' : '');
        $query = 'SELECT  id,name,status,image from ' . $this->table . ' ' . $onlyActive . ' ORDER BY id DESC LIMIT ' . $limit . ' OFFSET ' . $offset . '';
        // echo $query;
        $stmt = $this->conn->prepare($query);
        if (
            $stmt &&
            $stmt->execute() &&
            $stmt->store_result() &&
            $stmt->bind_result($id, $name, $status, $image)

        ) {
            while ($stmt->fetch()) {
                array_push($venueTypes, array("id" => $id, "name" => $name, "status" => $status  ? true : false, "image" => $this->getVenueTypeImage($image)));
            }

            $totalCounts = $this->rowCounts($search);
            return array("status" => "success", "data" => array("venueTypes" => $venueTypes, "totalCount" => $totalCounts, "totalPages" => ceil($totalCounts / $limit)));
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
