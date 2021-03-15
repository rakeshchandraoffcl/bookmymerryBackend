<?php
require(dirname(__FILE__) . '/../config/db.php');
include_once(dirname(__FILE__) . '/../utils/helper.php');
class Vendor extends DB
{
    private $table = 'vendor';

    function getImage($img)
    {
        return $img ? $this->host . "/images/vendor_images/" . $img : "";
    }

    function addVendor($data, $otherFields, $file)
    {
        // print_r($otherFields);
        // print_r($file['gallery']);
        // return;
        $venue = $this->createVendorWithBasicDetails($data);
        if ($venue['status'] === 'success') {
            $this->addGallery($otherFields['gallery'], $venue['data'], $file);
            $this->addFaq($otherFields['faq'], $venue['data']);
            $this->addPackage($otherFields['package'], $venue['data']);
            // $this->addEvent($otherFields['event'], $venue['data']);
            // $this->addType($otherFields['type'], $venue['data']);
            // $this->addUsp($otherFields['usp'], $venue['data']);
            // $this->addAmenity($otherFields['amenity'], $venue['data']);
            // $this->addTimeSLot($otherFields['time_slot'], $venue['data']);
            // $this->addPolicy($otherFields['policy'], $venue['data']);
            // $this->addMenu($otherFields['menu'], $venue['data']);
            $i = 0;
            foreach ($file['img']['name'] as $ts) {
                $target_dir = dirname(__FILE__) . "/../images/vendor_images/";
                $t = time();
                $rand = rand();
                $name = $target_dir . $rand . '_' . basename($file["img"]["name"][$i]);
                $size = $file["img"]["size"][$i];
                $tmp_name = $file["img"]["tmp_name"][$i];
                $image_upload_status = image_upload($name, $tmp_name, $size);
                // print_r($image_upload_status);
                if ($image_upload_status["status"] === "success") {
                    $imageName = $rand . '_' . basename($file["img"]["name"][$i]);
                    $this->addImage($imageName, $venue['data']);
                }
                //File Loading Successfully
                $i++;
            }
            return array("status" => "success", "data" => $venue['data']);
        } else {
            return array("status" => "fail", "error" => $venue['error']);
        }
    }

    function createVendorWithBasicDetails($data)
    {
        $query = 'INSERT INTO ' . $this->table . ' SET ';
        foreach ($data as $key => $value) {
            $query .= $key . '="' . $value . '",';
        };
        $query .= 'created_at=NOW()';
        // echo $query;
        $stmt = $this->conn->prepare($query);
        if (
            $stmt &&
            $stmt->execute()
        ) {
            return array("status" => "success", "data" => $this->conn->insert_id);
        } else {
            return array("status" => "fail", "error" => $this->conn->error);
        }
    }

    function addImage($name, $id)
    {
        $query = 'INSERT INTO vendor_image (name,vendor) VALUES ("' . $name . '",' . $id . ')';
        $stmt = $this->conn->prepare($query);
        if (
            $stmt &&
            $stmt->execute()
        ) {
            return array("status" => "success", "data" => $this->conn->insert_id);
        } else {
            return array("status" => "fail", "error" => $this->conn->error);
        }
    }

    function addPackage($packages, $id)
    {
        $query = 'INSERT INTO vendor_package_items (vendor,package,package_item,package_value) VALUES ';
        $addon_query = 'INSERT INTO vendor_package_addon (vendor,package,add_on) VALUES ';
        foreach ($packages as $x => $package) {
            foreach ($package['item'] as $y => $val) {
                $query .= '(' . $id . ',' . $package['package'] . ',"' . $val['name'] . '","' . $val['value'] . '"),';
            }
            foreach ($package['add_on'] as $y => $val) {
                $addon_query .= '(' . $id . ',' . $package['package'] . ',"' . $val . '"),';
            }
        }
        $query = substr($query, 0, -1);
        $addon_query = substr($addon_query, 0, -1);
        $this->addQuery($query);
        $this->addQuery($addon_query);
    }

    function addQuery($query)
    {

        $stmt = $this->conn->prepare($query);
        if (
            $stmt &&
            $stmt->execute()
        ) {
            return array("status" => "success", "data" => $this->conn->insert_id);
        } else {
            return array("status" => "fail", "error" => $this->conn->error);
        }
    }



    function addFaq($data, $id)
    {
        // print_r($data);
        $test =  $id;
        foreach ($data as $key => $value) {

            $query = 'INSERT INTO vendor_faq SET question="' . $value['question'] . '",answer="' . $value['answer'] . '",vendor=' . $test . '';
            // $query = substr($query, 0, -1);
            // echo $query;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
        };
    }


    function addGallery($data, $id, $file)
    {
        $galleryInfo = array();

        foreach ($data as $key => $value) {
            if ($value['type'] == 'image') {
                $target_dir = dirname(__FILE__) . "/../images/vendor_images/";
                $t = time();
                $rand = rand();
                $name = $target_dir . $rand . '_' . basename($file["gallery"]["name"][$key]['media']);
                $size = $file["gallery"]["size"][$key]['media'];
                $tmp_name = $file["gallery"]["tmp_name"][$key]['media'];
                $image_upload_status = image_upload($name, $tmp_name, $size);
                // print_r($image_upload_status);
                if ($image_upload_status["status"] === "success") {
                    $url = $rand . '_' . basename($file["gallery"]["name"][$key]['media']);
                } else {
                    $url = '';
                }

                //File Loading Successfully
            } else {
                $url = $value['media'];
            }

            $t = array(
                "vendor" => $id,
                "type" => $value['type'],
                "title" => $value['title'],
                "description" => $value['description'],
                "url" => $url,
            );
            array_push($galleryInfo, $t);
            // $query .= '("' . $value . '",' . $id . '),';

        };
        foreach ($galleryInfo as $key => $value) {

            $query = 'INSERT INTO vendor_gallery SET title="' . $value['title'] . '",description="' . $value['description'] . '",type="' . $value['type'] . '",url="' . $value['url'] . '",vendor=' . $id;
            // $query = substr($query, 0, -1);
            // echo $query;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $decorationId = $this->conn->insert_id;
        };
    }



    function getVendors($count, $page, $search, $all = false, $type = null, $city = null)
    {
        $limit = $this->parse($count);
        $offset = ($this->parse($page) - 1) * $limit;
        $cities = [];
        $onlyActive = !$all ? 'WHERE status=1' : '';
        $onlyActive .= !$all ? ($search ? ' AND name LIKE "%' . $search . '%"' : '') : ($search ? ' WHERE name LIKE "%' . $search . '%"' : '');
        if ($type) {
            $onlyActive .= ' AND type=' . $type;
        }
        if ($city) {
            $onlyActive .= ' AND city=' . $city;
        }
        $query = 'SELECT id,name,(SELECT city_name  from city c WHERE c.id = v.city) as cityName,phone_number,view,created_at,status,description,address,email,wh_number,main_service,booking_policy,cancellation_policy,terms,(SELECT name  from vendor_type vt WHERE vt.id = v.type) as vendor_type,city,type from ' . $this->table . '  v ' . $onlyActive . ' ORDER BY created_at DESC LIMIT ' . $limit . ' OFFSET ' . $offset . '';
        // echo $query;
        $stmt = $this->conn->prepare($query);
        if (
            $stmt &&
            $stmt->execute() &&
            $stmt->store_result() &&
            $stmt->bind_result($id, $name, $cityName, $phone_number, $view, $created_at, $status, $description, $address, $email, $wh_number, $main_service, $booking_policy, $cancellation_policy, $terms, $vendor_type, $city, $type)

        ) {
            while ($stmt->fetch()) {
                $images = $this->getImages($id)['data'];
                array_push($cities, array(
                    "id" => $id,
                    "name" => $name,
                    "city" => $city,
                    "city_name" => $cityName,
                    "phone_number" => $phone_number,
                    "description" => $description,
                    "address" => $address,
                    "email" => $email,
                    "wh_number" => $wh_number,
                    "main_service" => $main_service,
                    "booking_policy" => $booking_policy,
                    "cancellation_policy" => $cancellation_policy,
                    "terms" => $terms,
                    "vendor_type" => $vendor_type,
                    "view" => $view,
                    "created_at" => $created_at,
                    "images" => $images,
                    "status" => $status
                ));
            }

            $totalCounts = $this->rowCounts($search);
            return array("status" => "success", "data" => array("vendors" => $cities, "totalCount" => $totalCounts, "totalPages" => ceil($totalCounts / $limit)));
        } else {
            if ($this->conn->error) {
                return array("status" => "fail", "error" => $this->conn->error);
            }
            if ($stmt->error) {
                return array("status" => "fail", "error" => $stmt->error);
            }
        }
    }
    function getSimilarVendors($excludeId, $type, $city)
    {
        $cities = [];
        $query = 'SELECT id,name,(SELECT city_name  from city c WHERE c.id = v.city) as city,phone_number,view,created_at,status,description,address,email,wh_number,main_service,booking_policy,cancellation_policy,terms,(SELECT name  from vendor_type vt WHERE vt.id = v.type) as vendor_type from ' . $this->table . '  v WHERE city=' . $city . ' AND type=' . $type . ' AND status=1  ORDER BY created_at DESC LIMIT 3 ';
        // echo $query;
        $stmt = $this->conn->prepare($query);
        if (
            $stmt &&
            $stmt->execute() &&
            $stmt->store_result() &&
            $stmt->bind_result($id, $name, $city, $phone_number, $view, $created_at, $status, $description, $address, $email, $wh_number, $main_service, $booking_policy, $cancellation_policy, $terms, $vendor_type)

        ) {
            while ($stmt->fetch()) {
                $images = $this->getImages($id)['data'];
                array_push($cities, array(
                    "id" => $id,
                    "name" => $name,
                    "city" => $city,
                    "phone_number" => $phone_number,
                    "description" => $description,
                    "address" => $address,
                    "email" => $email,
                    "wh_number" => $wh_number,
                    "main_service" => $main_service,
                    "booking_policy" => $booking_policy,
                    "cancellation_policy" => $cancellation_policy,
                    "terms" => $terms,
                    "vendor_type" => $vendor_type,
                    "view" => $view,
                    "created_at" => $created_at,
                    "images" => $images,
                    "status" => $status,
                ));
            }

            return array("status" => "success", "data" => $cities);
        } else {
            if ($this->conn->error) {
                return array("status" => "fail", "error" => $this->conn->error);
            }
            if ($stmt->error) {
                return array("status" => "fail", "error" => $stmt->error);
            }
        }
    }


    function getVendorDetails($id)
    {
        $details = [];
        $query = 'SELECT id,name,(SELECT city_name  from city c WHERE c.id = v.city) as city_name,phone_number,view,created_at,status,description,address,email,wh_number,main_service,booking_policy,cancellation_policy,terms,(SELECT name  from vendor_type vt WHERE vt.id = v.type) as vendor_type,city,type FROM  ' . $this->table . ' v WHERE id =' . $id;
        // echo $query;
        $stmt = $this->conn->prepare($query);
        if (
            $stmt &&
            $stmt->execute() &&
            $stmt->store_result() &&
            $stmt->bind_result($id, $name, $city_name, $phone_number, $view, $created_at, $status, $description, $address, $email, $wh_number, $main_service, $booking_policy, $cancellation_policy, $terms, $vendor_type, $city, $type)


        ) {

            if ($stmt->num_rows() > 0) {
                if ($stmt->fetch()) {
                    $images = $this->getImages($id)['data'];
                    $faqs = $this->getFaqs($id)['data'];
                    $gallery = $this->getGalleries($id)['data'];
                    $packages = $this->getPackages($id)['data'];
                    $details["id"] = $id;
                    $details["name"] = $name;
                    $details["city"] = $city_name;
                    $details["phone_number"] = $phone_number;
                    $details["created_at"] = $created_at;
                    $details["description"] = $description;
                    $details["address"] = $address;
                    $details["email"] = $email;
                    $details["wh_number"] = $wh_number;
                    $details["main_service"] = $main_service;
                    $details["booking_policy"] = $booking_policy;
                    $details["cancellation_policy"] = $cancellation_policy;
                    $details["terms"] = $terms;
                    $details["vendor_type"] = $vendor_type;
                    $details["images"] = $images;
                    $details["faqs"] = $faqs;
                    $details["gallery"] = $gallery;
                    $details["status"] = $status;
                    $details["view"] = $view;
                    $details["packages"] = $packages;
                    $details["city_id"] = $city;
                    $details["type_id"] = $type;
                    $details["rating"] = $this->getVendorRatings($id)['data'];
                    return array("status" => "success", "data" => $details);
                } else {
                    if ($stmt->error) {
                        return array("status" => "fail", "error" => $stmt->error);
                    }
                }
            } else {
                return array("status" => "fail", "error" => "No venue found");
            }
        } else {
            if ($this->conn->error) {
                return array("status" => "fail", "error" => $this->conn->error);
            }
            if ($stmt->error) {
                return array("status" => "fail", "error" => $stmt->error);
            }
        }
    }


    function getVendorRatings($id)
    {
        $query = 'SELECT ROUND(AVG(rating),1) as rating from vendor_rating WHERE vendor=' . $id . ' AND status=1';
        $stmt = $this->conn->prepare($query);
        if (
            $stmt &&
            $stmt->execute() &&
            $stmt->store_result() &&
            $stmt->bind_result($rating) &&
            $stmt->fetch()

        ) {
            return array("status" => "success", "data" => $rating);
        } else {
            if ($this->conn->error) {
                return array("status" => "fail", "error" => $this->conn->error);
            }
            if ($stmt->error) {
                return array("status" => "fail", "error" => $stmt->error);
            }
        }
    }







    function getPolicy($id)
    {
        $decorations = [];
        $query = 'SELECT id,name,type,availability FROM venue_policy WHERE venue=' . $id . ' AND status=1';
        // echo $query;
        $stmt = $this->conn->prepare($query);
        if (
            $stmt &&
            $stmt->execute() &&
            $stmt->store_result() &&
            $stmt->bind_result($id, $name, $type, $availability)

        ) {
            while ($stmt->fetch()) {
                array_push($decorations, array(
                    "id" => $id,
                    "name" => $name,
                    "type" => $type,
                    "availability" => $availability,
                ));
            }

            return array("status" => "success", "data" => $decorations);
        } else {
            if ($this->conn->error) {
                return array("status" => "fail", "error" => $this->conn->error);
            }
            if ($stmt->error) {
                return array("status" => "fail", "error" => $stmt->error);
            }
        }
    }


    function getImages($id)
    {
        $images = [];
        $query = 'SELECT id,name FROM vendor_image WHERE vendor=' . $id;
        // echo $query;
        $stmt = $this->conn->prepare($query);
        if (
            $stmt &&
            $stmt->execute() &&
            $stmt->store_result() &&
            $stmt->bind_result($id, $name)

        ) {
            while ($stmt->fetch()) {
                array_push($images, array(
                    "id" => $id,
                    "name" => $this->getImage($name),

                ));
            }

            return array("status" => "success", "data" => $images);
        } else {
            if ($this->conn->error) {
                return array("status" => "fail", "error" => $this->conn->error);
            }
            if ($stmt->error) {
                return array("status" => "fail", "error" => $stmt->error);
            }
        }
    }
    function getAddons($id, $package_id)
    {
        $images = [];
        $query = 'SELECT add_on FROM vendor_package_addon WHERE vendor=' . $id . ' AND package=' . $package_id;
        // echo $query;
        $stmt = $this->conn->prepare($query);
        if (
            $stmt &&
            $stmt->execute() &&
            $stmt->store_result() &&
            $stmt->bind_result($add_on)

        ) {
            while ($stmt->fetch()) {
                array_push($images, $add_on);
            }
            return array("status" => "success", "data" => $images);
        } else {
            if ($this->conn->error) {
                return array("status" => "fail", "error" => $this->conn->error);
            }
            if ($stmt->error) {
                return array("status" => "fail", "error" => $stmt->error);
            }
        }
    }
    function getPackages($id)
    {
        $images = [];
        $query = 'SELECT vp.id as package_id,vp.name,vp.price,GROUP_CONCAT(CONCAT(vi.package_item,"-",vi.package_value)) as item FROM vendor_package_items vi LEFT JOIN vendor_package vp ON vi.package=vp.id   WHERE vi.vendor=' . $id . ' GROUP BY vi.package';
        // echo $query;
        $stmt = $this->conn->prepare($query);
        if (
            $stmt &&
            $stmt->execute() &&
            $stmt->store_result() &&
            $stmt->bind_result($package_id, $name, $price, $item)

        ) {
            while ($stmt->fetch()) {
                $addOns = $this->getAddons($id, $package_id)['data'];
                $t = array();
                $d = explode(",", $item);
                foreach ($d as $value) {
                    $a = explode("-", $value);
                    array_push($t, array(
                        "name" => $a[0],
                        "value" => $a[1],
                    ));
                }
                array_push($images, array(
                    "package_id" => $package_id,
                    "package_name" => $name,
                    "package_price" => $price,
                    "items" => $t,
                    "addons" => $addOns,

                ));
            }

            return array("status" => "success", "data" => $images);
        } else {
            if ($this->conn->error) {
                return array("status" => "fail", "error" => $this->conn->error);
            }
            if ($stmt->error) {
                return array("status" => "fail", "error" => $stmt->error);
            }
        }
    }
    function getGalleries($id)
    {
        $images = [];
        $query = 'SELECT id,type,url,title,description FROM vendor_gallery WHERE status =1 AND vendor=' . $id;
        // echo $query;
        $stmt = $this->conn->prepare($query);
        if (
            $stmt &&
            $stmt->execute() &&
            $stmt->store_result() &&
            $stmt->bind_result($id, $type, $url, $title, $description)

        ) {
            while ($stmt->fetch()) {
                array_push($images, array(
                    "id" => $id,
                    "type" => $type,
                    "title" => $title,
                    "description" => $description,
                    "url" => $type === 'image' ? $this->getImage($url) : $url,

                ));
            }

            return array("status" => "success", "data" => $images);
        } else {
            if ($this->conn->error) {
                return array("status" => "fail", "error" => $this->conn->error);
            }
            if ($stmt->error) {
                return array("status" => "fail", "error" => $stmt->error);
            }
        }
    }
    function getFaqs($id)
    {
        $images = [];
        $query = 'SELECT id,question,answer FROM vendor_faq WHERE status=1 AND vendor=' . $id;
        // echo $query;
        $stmt = $this->conn->prepare($query);
        if (
            $stmt &&
            $stmt->execute() &&
            $stmt->store_result() &&
            $stmt->bind_result($id, $question, $answer)

        ) {
            while ($stmt->fetch()) {
                array_push($images, array(
                    "id" => $id,
                    "question" => $question,
                    "answer" => $answer,

                ));
            }

            return array("status" => "success", "data" => $images);
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

    function updateView($id)
    {
        $query = 'UPDATE ' . $this->table . ' SET view = view + 1 WHERE id=' . $id;
        $stmt = $this->conn->prepare($query);
        if (
            $stmt &&
            $stmt->execute() &&
            ($stmt->affected_rows === 1)
        ) {
            return array("status" => "success", "data" => $id);
        } else {
            if ($this->conn->error) {
                return array("status" => "fail", "error" => $this->conn->error);
            }
            if ($stmt->error) {
                return array("status" => "fail", "error" => $stmt->error);
            }
        }
    }

    function updateVendor($data, $id)
    {

        $fields = $data;
        $query = ' UPDATE ' . $this->table . ' SET ';
        foreach ($fields as $key => $value) {
            if ($key !== 'id') {
                if ($key == 'status' || $key == 'city' || $key == 'type') {
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
            $user_details = $this->getVendorDetails($id);
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
}
