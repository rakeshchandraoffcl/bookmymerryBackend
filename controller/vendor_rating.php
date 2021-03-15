<?php
require(dirname(__FILE__) . '/../config/db.php');
class VendorRating extends DB
{
    private $table = 'vendor_rating';

    function addRating($venue, $user, $rating, $comment)
    {
        $stmt = $this->conn->prepare('INSERT INTO ' . $this->table . ' SET
        vendor =?,
        user=?,
        rating=?,
        comment=?,
        created_at=NOW()
        ');
        if (
            $stmt &&
            $stmt->bind_param('iids', $venue, $user, $rating, $comment) &&
            $stmt->execute()
        ) {
            $user_details = $this->getDetailsById($this->conn->insert_id);
            return array("status" => "success", "data" => $user_details['data']);
        } else {
            return array("status" => "fail", "error" => $this->conn->error);
        }
    }







    function getDetailsById($id)
    {
        $details = array();
        $stmt = $this->conn->prepare('SELECT id,vendor,user,rating,comment,created_at,updated_at,status from ' . $this->table . ' WHERE id=?');
        if (
            $stmt &&
            $stmt->bind_param('i', $id) &&
            $stmt->execute() &&
            $stmt->store_result() &&
            $stmt->bind_result($id, $vendor, $user, $rating, $comment, $created_at, $updated_at, $status)
        ) {
            if ($stmt->fetch()) {
                $details["id"] = $id;
                $details["vendor"] = $vendor;
                $details["user"] = $user;
                $details["rating"] = $rating;
                $details["comment"] = $comment;
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





    function getRatings($count, $page, $search, $all = false)
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
