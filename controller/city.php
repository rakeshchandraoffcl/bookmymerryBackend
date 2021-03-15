<?php
require(dirname(__FILE__) . '/../config/db.php');
class City extends DB
{
    private $table = 'city';
    function getCityImage($img)
    {

        return $img ? $this->host . "/images/city_images/" . $img : "";
    }

    function addCity($city_name, $city_img, $city_slug, $top_city, $is_active)
    {
        $stmt = $this->conn->prepare('INSERT INTO ' . $this->table . ' SET
        city_name=?, 
        city_img=?,
        city_slug=?,
        top_city=?,
        is_active=?
        ');
        if (
            $stmt &&
            $stmt->bind_param('sssii', $city_name, $city_img, $city_slug, $top_city, $is_active) &&
            $stmt->execute()
        ) {
            $cityDetails = $this->getDetailsById($this->conn->insert_id);
            return array("status" => "success", "data" => $cityDetails['data']);
        } else {
            return array("status" => "fail", "error" => $this->conn->error);
        }
    }



    function updateCity($data, $id)
    {

        $fields = $data;
        $query = ' UPDATE ' . $this->table . ' SET ';


        if (isset($fields['city_img'])) {
            $details = $this->getDetailsById($id);
            if ($details['status'] === 'success') {
                if ($details['data']['city_img']) {

                    unlink('../images/city_images/' . $details['data']['city_img']);
                }
            } else {
                return array("status" => "fail", "error" => $details['error']);
            }
        }
        foreach ($fields as $key => $value) {
            if ($key == 'is_active' || $key == 'top_city') {
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
        $stmt = $this->conn->prepare('SELECT id,city_name,city_img,city_slug,top_city,is_active from ' . $this->table . ' WHERE id=?');
        if (
            $stmt &&
            $stmt->bind_param('i', $id) &&
            $stmt->execute() &&
            $stmt->store_result() &&
            $stmt->bind_result($id, $city_name, $city_img, $city_slug, $top_city, $is_active)
        ) {
            if ($stmt->fetch()) {
                $details["id"] = $id;
                $details["city_name"] = $city_name;
                $details["img_url"] = $this->getCityImage($city_img);
                $details["city_img"] = $city_img;
                $details["city_slug"] = $city_slug;
                $details["top_city"] = $top_city ? true : false;
                $details["is_active"] = $is_active ? true : false;
                return array("status" => "success", "data" => $details);
            } else {
                return array("status" => "fail", "error" => "user not found");
            }
        } else {
            return array("status" => "fail", "error" => $this->conn->error);
        }
    }



    function getCities($count, $page, $search, $all = false)
    {
        $limit = $this->parse($count);
        $offset = ($this->parse($page) - 1) * $limit;
        $cities = [];
        $onlyActive = !$all ? 'WHERE is_active=1' : '';
        $onlyActive .= !$all ? ($search ? ' AND city_name LIKE "%' . $search . '%"' : '') : ($search ? ' WHERE city_name LIKE "%' . $search . '%"' : '');
        $query = 'SELECT id,city_name,city_img,city_slug,top_city,is_active from ' . $this->table . ' ' . $onlyActive . ' ORDER BY top_city DESC LIMIT ' . $limit . ' OFFSET ' . $offset . '';
        // echo $query;
        $stmt = $this->conn->prepare($query);
        if (
            $stmt &&
            $stmt->execute() &&
            $stmt->store_result() &&
            $stmt->bind_result($id, $city_name, $city_img, $city_slug, $top_city, $is_active)

        ) {
            while ($stmt->fetch()) {
                array_push($cities, array("id" => $id, "city_name" => $city_name, "city_img" => $this->getCityImage($city_img), "city_slug" => $city_slug, "top_city" => $top_city ? true : false, "is_active" => $is_active  ? true : false));
            }

            $totalCounts = $this->rowCounts($search);
            return array("status" => "success", "data" => array("cities" => $cities, "totalCount" => $totalCounts, "totalPages" => ceil($totalCounts / $limit)));
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

        $searchQuery = $search ? ' WHERE city_name LIKE "%' . $search . '%"' : '';

        $query = 'SELECT COUNT(*) as totalDocs from ' . $this->table . $searchQuery;
        $stmt = $this->conn->prepare($query);

        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($totalDocs);
        $stmt->fetch();
        return $totalDocs;
    }

    function getCityResources()
    {
        $details = [];
        $query = 'SELECT id,city_name,city_img,(SELECT COUNT(*) FROM venue v WHERE c.id = v.city) as venueCount,(SELECT COUNT(*) FROM vendor vo WHERE c.id = vo.city) as vendorCount FROM ' . $this->table . ' c WHERE c.is_active = 1 AND c.top_city=1';
        $stmt = $this->conn->prepare($query);
        if (
            $stmt &&
            $stmt->execute() &&
            $stmt->store_result() &&
            $stmt->bind_result($id, $city_name, $city_img, $venueCount, $vendorCount)

        ) {
            while ($stmt->fetch()) {
                array_push($details, array("id" => $id, "city_name" => $city_name, "city_img" => $this->getCityImage($city_img), "venue_count" => $venueCount, "vendor_count" => $vendorCount));
            }

            return array("status" => "success", "data" => $details);
        } else {
            if ($this->conn->error) {
                return array("status" => "fail", "error" => $this->conn->error);
            }
            if ($stmt->error) {
                return array("status" => "fail", "error" => $stmt->error);
            }
        }
    }
}
