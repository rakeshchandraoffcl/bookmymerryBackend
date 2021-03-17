<?php
require(dirname(__FILE__) . '/../config/db.php');
include_once(dirname(__FILE__) . '/../utils/helper.php');
class Venue extends DB
{
    private $table = 'venue';

    function getImage($img)
    {
        return $img ? $this->host . "/images/venue_images/" . $img : "";
    }

    function addVenue($data, $otherFields, $file)
    {
        $venue = $this->createVenueWithBasicDetails($data);
        if ($venue['status'] === 'success') {
            $this->addEvent($otherFields['event'], $venue['data']);
            $this->addType($otherFields['type'], $venue['data']);
            $this->addUsp($otherFields['usp'], $venue['data']);
            $this->addAmenity($otherFields['amenity'], $venue['data']);
            $this->addTimeSLot($otherFields['time_slot'], $venue['data']);
            $this->addPolicy($otherFields['policy'], $venue['data']);
            $this->addDecoration($otherFields['decoration'], $venue['data']);
            $this->addMenu($otherFields['menu'], $venue['data']);
            $i = 0;
            foreach ($file['img']['name'] as $ts) {

                $target_dir = dirname(__FILE__) . "/../images/venue_images/";
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

    function createVenueWithBasicDetails($data)
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

    function getVenuesOfACity($city_id)
    {
        $ids = [];
        $query = 'SELECT DISTINCT id FROM ' . $this->table . ' WHERE city = ' . $city_id;
        $stmt = $this->conn->prepare($query);
        if (
            $stmt &&
            $stmt->execute() &&
            $stmt->store_result() &&
            $stmt->bind_result($id)

        ) {
            while ($stmt->fetch()) {
                array_push($ids, $id);
            }

            return $ids;
        } else {
            return $ids;
        }
    }
    function getVenuesByCapacity($capacity, $venueIds)
    {
        $ids = [];
        $query = 'SELECT DISTINCT id FROM ' . $this->table . ' WHERE (GREATEST(max_guest_hall,max_guest_lawn,max_seat_guest_hall,max_seat_guest_lawn)) >=' . $capacity;
        // echo $query;
        if (count($venueIds) > 0) {
            $query .= ' AND id IN (';
            foreach ($venueIds as $id) {
                $query .= $id . ',';
            }
            $query = substr($query, 0, -1);
            $query .= ')';
        }
        $stmt = $this->conn->prepare($query);
        if (
            $stmt &&
            $stmt->execute() &&
            $stmt->store_result() &&
            $stmt->bind_result($id)

        ) {
            while ($stmt->fetch()) {
                array_push($ids, $id);
            }

            return $ids;
        } else {
            return $ids;
        }
    }
    function getVenuesByPricing($price, $venueIds)
    {
        $ids = [];
        $query = 'SELECT DISTINCT venue FROM menu WHERE price <=' . $price;
        if (count($venueIds) > 0) {
            $query .= ' AND venue IN (';
            foreach ($venueIds as $id) {
                $query .= $id . ',';
            }
            $query = substr($query, 0, -1);
            $query .= ')';
        }
        // echo $query;
        $stmt = $this->conn->prepare($query);
        if (
            $stmt &&
            $stmt->execute() &&
            $stmt->store_result() &&
            $stmt->bind_result($venue)

        ) {
            while ($stmt->fetch()) {
                array_push($ids, $venue);
            }

            return $ids;
        } else {
            return $ids;
        }
    }
    function getVenuesByEvents($events, $venueIds)
    {
        $ids = [];
        $query = 'SELECT  venue,COUNT(event) as event_count FROM venue_event WHERE event IN (';
        if (count($events) > 0) {

            foreach ($events as $id) {
                $query .= $id . ',';
            }
            $query = substr($query, 0, -1);
            $query .= ')';
        }
        if (count($venueIds) > 0) {
            $query .= ' AND venue IN (';
            foreach ($venueIds as $id) {
                $query .= $id . ',';
            }
            $query = substr($query, 0, -1);
            $query .= ')';
        }
        $query .= ' GROUP BY venue having COUNT(venue) >= ' . count($events);
        $stmt = $this->conn->prepare($query);
        if (
            $stmt &&
            $stmt->execute() &&
            $stmt->store_result() &&
            $stmt->bind_result($venue, $event_count)

        ) {
            while ($stmt->fetch()) {
                array_push($ids, $venue);
            }

            return $ids;
        } else {
            return $ids;
        }
    }
    function getVenuesByTypes($types, $venueIds)
    {
        $ids = [];
        $query = 'SELECT  venue,COUNT(type) as type_count FROM venue_type WHERE type IN (';
        if (count($types) > 0) {

            foreach ($types as $id) {
                $query .= $id . ',';
            }
            $query = substr($query, 0, -1);
            $query .= ')';
        }
        if (count($venueIds) > 0) {
            $query .= ' AND venue IN (';
            foreach ($venueIds as $id) {
                $query .= $id . ',';
            }
            $query = substr($query, 0, -1);
            $query .= ')';
        }
        $query .= ' GROUP BY venue having COUNT(venue) >= ' . count($types);
        // echo $query;
        $stmt = $this->conn->prepare($query);
        if (
            $stmt &&
            $stmt->execute() &&
            $stmt->store_result() &&
            $stmt->bind_result($venue, $type_count)

        ) {
            while ($stmt->fetch()) {
                array_push($ids, $venue);
            }

            return $ids;
        } else {
            return $ids;
        }
    }
    function getVenuesByAmenities($amenities, $venueIds)
    {
        $ids = [];
        $query = 'SELECT  venue,COUNT(amenity) as amenity_count FROM venue_amenity WHERE amenity IN (';
        if (count($amenities) > 0) {

            foreach ($amenities as $id) {
                $query .= $id . ',';
            }
            $query = substr($query, 0, -1);
            $query .= ')';
        }
        if (count($venueIds) > 0) {
            $query .= ' AND venue IN (';
            foreach ($venueIds as $id) {
                $query .= $id . ',';
            }
            $query = substr($query, 0, -1);
            $query .= ')';
        }
        $query .= ' GROUP BY venue having COUNT(venue) >= ' . count($amenities);
        $stmt = $this->conn->prepare($query);
        if (
            $stmt &&
            $stmt->execute() &&
            $stmt->store_result() &&
            $stmt->bind_result($venue)

        ) {
            while ($stmt->fetch()) {
                array_push($ids, $venue);
            }

            return $ids;
        } else {
            return $ids;
        }
    }

    function filterVenueIds($city = null, $capacity = null, $price = null, $events = [], $types = [], $amenities = [])
    {
        $ids = [];
        if ($city) {
            $ids = $this->getVenuesOfACity($city);
            if (count($ids) === 0) {
                return $ids;
            }
        }
        if ($capacity) {
            $ids = $this->getVenuesByCapacity($capacity, $ids);
            if (count($ids) === 0) {
                return $ids;
            }
        }
        if ($price) {
            $ids = $this->getVenuesByPricing($price, $ids);
            if (count($ids) === 0) {
                return $ids;
            }
        }
        if (count($events) > 0) {
            $ids = $this->getVenuesByEvents($events, $ids);
            if (count($ids) === 0) {
                return $ids;
            }
        }
        if (count($types) > 0) {

            $ids = $this->getVenuesByTypes($types, $ids);
            if (count($ids) === 0) {
                return $ids;
            }
        }
        if (count($amenities) > 0) {
            $ids = $this->getVenuesByAmenities($amenities, $ids);
            if (count($ids) === 0) {
                return $ids;
            }
        }

        return $ids;
    }

    function getVenuesByIds($ids)
    {
        $venues = array();
        if (count($ids) === 0) {
            return array("status" => "success", "data" => $venues);
        }
        $query = 'SELECT DISTINCT v.id,v.name,(SELECT city_name  from city c WHERE c.id = v.city) as city,(SELECT MIN(price) from menu m WHERE v.id=m.venue) as min_price,(SELECT MAX(price) from menu m WHERE v.id=m.venue) as max_price,v.location,v.about,v.phone_number,v.opening_time,v.landmark,v.view,v.created_at,status from ' . $this->table . ' v WHERE v.status =1   ';
        if (count($ids) > 0) {
            $query .= ' AND v.id IN (';
            foreach ($ids as $id) {
                $query .= $id . ',';
            }
            $query = substr($query, 0, -1);
            $query .= ')';
        }
        $query .= 'ORDER BY v.created_at DESC';
        // echo $query;
        $stmt = $this->conn->prepare($query);
        if (
            $stmt &&
            $stmt->execute() &&
            $stmt->store_result() &&
            $stmt->bind_result($id, $name, $city, $min_price, $max_price, $location, $about, $phone_number, $opening_time, $landmark, $view, $created_at, $status)

        ) {
            while ($stmt->fetch()) {
                $usps = $this->getUSPs($id)['data'];
                $images = $this->getImages($id)['data'];
                array_push($venues, array(
                    "id" => $id,
                    "name" => $name,
                    "city" => $city,
                    "min_price" => $min_price,
                    "max_price" => $max_price,
                    "location" => $location,
                    "about" => $about,
                    "phone_number" => $phone_number,
                    "opening_time" => $opening_time,
                    "landmark" => $landmark,
                    "view" => $view,
                    "created_at" => $created_at,
                    "status" => $status,
                    "usps" => $usps,
                    "images" => $images,
                ));
            }

            return array("status" => "success", "data" => $venues);
        } else {
            if ($this->conn->error) {
                return array("status" => "fail", "error" => $this->conn->error);
            }
            if ($stmt->error) {
                return array("status" => "fail", "error" => $stmt->error);
            }
        }
    }

    function addEvent($data, $id)
    {
        $query = 'INSERT INTO venue_event (event,venue) VALUES ';
        foreach ($data as $key => $value) {
            $query .= '(' . $value . ',' . $id . '),';
        };
        $query = substr($query, 0, -1);
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
        $query = 'INSERT INTO venue_image (name,venue) VALUES ("' . $name . '",' . $id . ')';

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
    function addPolicy($data, $id)
    {
        $query = 'INSERT INTO venue_policy (name,type,availability,venue) VALUES ';
        foreach ($data as $key => $value) {
            $query .= '("' . $value['name'] . '","' . $value['type'] . '",' . $value['availability'] . ',' . $id . '),';
        };
        $query = substr($query, 0, -1);
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
    function addType($data, $id)
    {
        $query = 'INSERT INTO venue_type (type,venue) VALUES ';
        foreach ($data as $key => $value) {
            $query .= '(' . $value . ',' . $id . '),';
        };
        $query = substr($query, 0, -1);
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
    function addUsp($data, $id)
    {
        $query = 'INSERT INTO venue_usp (name,venue) VALUES ';
        foreach ($data as $key => $value) {
            $query .= '("' . $value . '",' . $id . '),';
        };
        $query = substr($query, 0, -1);
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
    function addAmenity($data, $id)
    {
        $query = 'INSERT INTO venue_amenity (amenity,venue) VALUES ';
        foreach ($data as $key => $value) {
            $query .= '(' . $value . ',' . $id . '),';
        };
        $query = substr($query, 0, -1);
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
    function addTimeSLot($data, $id)
    {
        $query = 'INSERT INTO venue_time_slot (slot,venue) VALUES ';
        foreach ($data as $key => $value) {
            $query .= '(' . $value . ',' . $id . '),';
        };
        $query = substr($query, 0, -1);
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

    function addDecoration($data, $id)
    {
        // print_r($data);
        $test =  $id;
        foreach ($data as $key => $value) {

            $query = 'INSERT INTO decoration SET name="' . $value['name'] . '",price=' . $value['price'] . ',venue=' . $test . '';
            // $query = substr($query, 0, -1);
            // echo $query;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $decorationId = $this->conn->insert_id;
            $this->addDecorationItem($value['item'], $decorationId);
        };
    }

    function addDecorationItem($data, $id)
    {
        $query = 'INSERT INTO decoration_item (name,decoration) VALUES ';
        foreach ($data as $key => $value) {
            $query .= '("' . $value . '",' . $id . '),';
        };
        $query = substr($query, 0, -1);
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

    function addMenu($data, $id)
    {
        // print_r($data);
        $test =  $id;
        foreach ($data as $key => $value) {

            $query = 'INSERT INTO menu SET type="' . $key . '",price=' . $value['price'] . ',venue=' . $test . ',package_name="' . $value['package_name'] . '"';
            // $query = substr($query, 0, -1);
            // echo $query;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $menuId = $this->conn->insert_id;
            $this->addMenuItem($value['item'], $menuId);
        };
    }

    function addMenuItem($data, $id)
    {
        $query = 'INSERT INTO menu_item (name,quantity,menu) VALUES ';
        foreach ($data as $key => $value) {
            $query .= '("' . $value['name'] . '",' . $value['quantity'] . ',' . $id . '),';
        };
        $query = substr($query, 0, -1);
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

    function getVenues($count, $page, $search, $all = false)
    {
        $limit = $this->parse($count);
        $offset = ($this->parse($page) - 1) * $limit;
        $cities = [];
        $onlyActive = !$all ? 'WHERE status=1' : '';
        $onlyActive .= !$all ? ($search ? ' AND name LIKE "%' . $search . '%"' : '') : ($search ? ' WHERE name LIKE "%' . $search . '%"' : '');
        $query = 'SELECT id,name,(SELECT city_name  from city c WHERE c.id = v.city) as city,location,about,phone_number,opening_time,landmark,view,created_at,status from ' . $this->table . '  v ' . $onlyActive . ' ORDER BY created_at DESC LIMIT ' . $limit . ' OFFSET ' . $offset . '';
        // echo $query;
        $stmt = $this->conn->prepare($query);
        if (
            $stmt &&
            $stmt->execute() &&
            $stmt->store_result() &&
            $stmt->bind_result($id, $name, $city, $location, $about, $phone_number, $opening_time, $landmark, $view, $created_at, $status)

        ) {
            while ($stmt->fetch()) {
                array_push($cities, array(
                    "id" => $id,
                    "name" => $name,
                    "city" => $city,
                    "location" => $location,
                    "about" => $about,
                    "phone_number" => $phone_number,
                    "opening_time" => $opening_time,
                    "landmark" => $landmark,
                    "view" => $view,
                    "created_at" => $created_at,
                    "status" => $status,
                ));
            }

            $totalCounts = $this->rowCounts($search);
            return array("status" => "success", "data" => array("venues" => $cities, "totalCount" => $totalCounts, "totalPages" => ceil($totalCounts / $limit)));
        } else {
            if ($this->conn->error) {
                return array("status" => "fail", "error" => $this->conn->error);
            }
            if ($stmt->error) {
                return array("status" => "fail", "error" => $stmt->error);
            }
        }
    }
    function searchVenues($events = null, $type = null, $amenity = null, $city = null)
    {
        $cities = [];
        if ($events) {
            $eventQuery = 'INNER JOIN venue_event ve ON v.id=ve.venue';
            $eventWhereQuery = 'AND ve.event IN (';
            foreach ($events as $value) {
                $eventWhereQuery .= $value . ',';
            }
            $eventWhereQuery = substr($eventWhereQuery, 0, -1);
            $eventWhereQuery .= ')';
        } else {
            $eventQuery = '';
            $eventWhereQuery = '';
        }
        if ($type) {
            $typeQuery = 'INNER JOIN venue_type vt ON v.id=vt.venue';
            $typeWhereQuery = 'AND vt.type IN (';
            foreach ($type as $value) {
                $typeWhereQuery .= $value . ',';
            }
            $typeWhereQuery = substr($typeWhereQuery, 0, -1);
            $typeWhereQuery .= ')';
        } else {
            $typeQuery = '';
            $typeWhereQuery = '';
        }
        if ($amenity) {
            $amenityQuery = 'INNER JOIN venue_amenity va ON v.id=va.venue';
            $amenityWhereQuery = 'AND va.amenity IN (';
            foreach ($amenity as $value) {
                $amenityWhereQuery .= $value . ',';
            }
            $amenityWhereQuery = substr($amenityWhereQuery, 0, -1);
            $amenityWhereQuery .= ')';
        } else {
            $amenityQuery = '';
            $amenityWhereQuery = '';
        }
        if ($city) {
            $cityWhereQuery = 'AND v.city = ' . $city . '';
        } else {
            $cityWhereQuery = '';
        }


        $combineQuery = $eventQuery . ' ' . $typeQuery . ' ' . $amenityQuery;
        $combineWhereQuery = $eventWhereQuery . ' ' . $typeWhereQuery . ' ' . $amenityWhereQuery . ' ' . $cityWhereQuery;
        $query = 'SELECT DISTINCT v.id,v.name,(SELECT city_name  from city c WHERE c.id = v.city) as city,(SELECT MIN(price) from menu m WHERE v.id=m.venue) as min_price,(SELECT MAX(price) from menu m WHERE v.id=m.venue) as max_price,v.location,v.about,v.phone_number,v.opening_time,v.landmark,v.view,v.created_at,status from ' . $this->table . ' v ' . $combineQuery . ' WHERE 1 ' . $combineWhereQuery . ' ORDER BY v.created_at DESC';
        // echo $query;
        $stmt = $this->conn->prepare($query);
        if (
            $stmt &&
            $stmt->execute() &&
            $stmt->store_result() &&
            $stmt->bind_result($id, $name, $city, $min_price, $max_price, $location, $about, $phone_number, $opening_time, $landmark, $view, $created_at, $status)

        ) {
            while ($stmt->fetch()) {
                $usps = $this->getUSPs($id)['data'];
                $images = $this->getImages($id)['data'];
                array_push($cities, array(
                    "id" => $id,
                    "name" => $name,
                    "city" => $city,
                    "min_price" => $min_price,
                    "max_price" => $max_price,
                    "location" => $location,
                    "about" => $about,
                    "phone_number" => $phone_number,
                    "opening_time" => $opening_time,
                    "landmark" => $landmark,
                    "view" => $view,
                    "created_at" => $created_at,
                    "status" => $status,
                    "usps" => $usps,
                    "images" => $images,
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

    function getVenueDetails($id)
    {
        $details = [];
        $query = 'SELECT id,name,(SELECT city_name  from city c WHERE c.id = v.city) as city_name,(SELECT id  from city c WHERE c.id = v.city) as city_id,location,about,phone_number,opening_time,landmark,view,created_at,status,max_guest_hall,max_guest_lawn,max_seat_guest_hall,max_seat_guest_lawn,changing_room FROM ' . $this->table . ' v WHERE id =' . $id;
        // echo $query;
        $stmt = $this->conn->prepare($query);
        if (
            $stmt &&
            $stmt->execute() &&
            $stmt->store_result() &&
            $stmt->bind_result($id, $name, $city_name, $city_id, $location, $about, $phone_number, $opening_time, $landmark, $view, $created_at, $status, $max_guest_hall, $max_guest_lawn, $max_seat_guest_hall, $max_seat_guest_lawn, $changing_room)


        ) {

            if ($stmt->num_rows() > 0) {
                if ($stmt->fetch()) {
                    $details["id"] = $id;
                    $details["name"] = $name;
                    $details["city_name"] = $city_name;
                    $details["city_id"] = $city_id;
                    $details["location"] = $location;
                    $details["about"] = $about;
                    $details["phone_number"] = $phone_number;
                    $details["opening_time"] = $opening_time;
                    $details["landmark"] = $landmark;
                    $details["view"] = $view;
                    $details["created_at"] = $created_at;
                    $details["max_guest_hall"] = $max_guest_hall;
                    $details["max_guest_lawn"] = $max_guest_lawn;
                    $details["max_seat_guest_hall"] = $max_seat_guest_hall;
                    $details["max_seat_guest_lawn"] = $max_seat_guest_lawn;
                    $details["changing_room"] = $changing_room;
                    $details["amenities"] = $this->getAmenities($id)['data'];
                    $details["events"] = $this->getEvents($id)['data'];
                    $details["types"] = $this->getTypes($id)['data'];
                    $details["usps"] = $this->getUSPs($id)['data'];
                    $details["time_slots"] = $this->getTimeSlots($id)['data'];
                    $details["decorations"] = $this->getDecorations($id)['data'];
                    $details["policies"] = $this->getPolicy($id)['data'];
                    $details["images"] = $this->getImages($id)['data'];
                    $details["menu"] = $this->getMenus($id)['data'];
                    $details["ratings"] = $this->getVenueRatings($id);
                    $details["status"] = $status;
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

    function getVenueRatings($venueId)
    {
        $query = 'SELECT ROUND(AVG(rating),1) as avg_rating,COUNT(*) as rating_count FROM venue_rating WHERE status=1 AND verified = 1 AND venue=' . $venueId;
        $stmt = $this->conn->prepare($query);
        if (
            $stmt &&
            $stmt->execute() &&
            $stmt->store_result() &&
            $stmt->bind_result($avg_rating, $rating_count) &&
            $stmt->fetch()

        ) {


            return array("rating" => $avg_rating, "count" => $rating_count);
        } else {
            if ($this->conn->error) {
                return array("status" => "fail", "error" => $this->conn->error);
            }
            if ($stmt->error) {
                return array("status" => "fail", "error" => $stmt->error);
            }
        }
    }

    function getAmenities($id)
    {
        $amenities = [];
        $query = 'SELECT a.id,a.name from  venue_amenity va INNER JOIN amenity a ON va.amenity = a.id WHERE va.venue =' . $id . ' AND a.status=1';
        // echo $query;
        $stmt = $this->conn->prepare($query);
        if (
            $stmt &&
            $stmt->execute() &&
            $stmt->store_result() &&
            $stmt->bind_result($id, $name)

        ) {
            while ($stmt->fetch()) {
                array_push($amenities, array(
                    "id" => $id,
                    "name" => $name
                ));
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

    function getEvents($id)
    {
        $events = [];
        $query = 'SELECT a.id,a.name from  venue_event va INNER JOIN event a ON va.event = a.id WHERE va.venue =' . $id . ' AND a.status=1';
        // echo $query;
        $stmt = $this->conn->prepare($query);
        if (
            $stmt &&
            $stmt->execute() &&
            $stmt->store_result() &&
            $stmt->bind_result($id, $name)

        ) {
            while ($stmt->fetch()) {
                array_push($events, array(
                    "id" => $id,
                    "name" => $name
                ));
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

    function getTypes($id)
    {
        $types = [];
        $query = 'SELECT a.id,a.name from  venue_type va INNER JOIN venue_type_name a ON va.type = a.id WHERE va.venue =' . $id . ' AND a.status=1';
        // echo $query;
        $stmt = $this->conn->prepare($query);
        if (
            $stmt &&
            $stmt->execute() &&
            $stmt->store_result() &&
            $stmt->bind_result($id, $name)

        ) {
            while ($stmt->fetch()) {
                array_push($types, array(
                    "id" => $id,
                    "name" => $name
                ));
            }

            return array("status" => "success", "data" => $types);
        } else {
            if ($this->conn->error) {
                return array("status" => "fail", "error" => $this->conn->error);
            }
            if ($stmt->error) {
                return array("status" => "fail", "error" => $stmt->error);
            }
        }
    }

    function getTimeSlots($id)
    {
        $time_slots = [];
        $query = 'SELECT a.id,a.slot,a.slot_name from  venue_time_slot va INNER JOIN time_slot a ON va.slot = a.id WHERE va.venue =' . $id . ' AND a.status=1';
        // echo $query;
        $stmt = $this->conn->prepare($query);
        if (
            $stmt &&
            $stmt->execute() &&
            $stmt->store_result() &&
            $stmt->bind_result($id, $slot, $slot_name)

        ) {
            while ($stmt->fetch()) {
                array_push($time_slots, array(
                    "id" => $id,
                    "slot" => $slot,
                    "slot_name" => $slot_name,
                ));
            }

            return array("status" => "success", "data" => $time_slots);
        } else {
            if ($this->conn->error) {
                return array("status" => "fail", "error" => $this->conn->error);
            }
            if ($stmt->error) {
                return array("status" => "fail", "error" => $stmt->error);
            }
        }
    }


    function getUSPs($id)
    {
        $events = [];
        $query = 'SELECT id,name from  venue_usp WHERE venue =' . $id;
        // echo $query;
        $stmt = $this->conn->prepare($query);
        if (
            $stmt &&
            $stmt->execute() &&
            $stmt->store_result() &&
            $stmt->bind_result($id, $name)

        ) {
            while ($stmt->fetch()) {
                array_push($events, array(
                    "id" => $id,
                    "name" => $name
                ));
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

    function getDecorations($id)
    {
        $decorations = [];
        $query = 'SELECT d.id,d.name,d.price,di.name as decoration_item,di.id as decoration_item_id FROM `decoration` d LEFT JOIN decoration_item di ON d.id=di.decoration WHERE d.venue=' . $id . ' AND d.status=1';
        // echo $query;
        $stmt = $this->conn->prepare($query);
        if (
            $stmt &&
            $stmt->execute() &&
            $stmt->store_result() &&
            $stmt->bind_result($id, $name, $price, $decoration_item, $decoration_item_id)

        ) {
            while ($stmt->fetch()) {
                array_push($decorations, array(
                    "decoration_id" => $id,
                    "decoration_name" => $name,
                    "decoration_price" => $price,
                    "decoration_item_id" => $decoration_item_id,
                    "decoration_item_name" => $decoration_item,
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

    function getMenus($id)
    {
        $decorations = [];
        $query = 'SELECT d.id,d.package_name,d.price,d.type,di.quantity,di.name  FROM `menu` d LEFT JOIN menu_item di ON d.id=di.menu WHERE d.venue=' . $id . ' AND di.status=1';
        // echo $query;
        $stmt = $this->conn->prepare($query);
        if (
            $stmt &&
            $stmt->execute() &&
            $stmt->store_result() &&
            $stmt->bind_result($id, $package_name, $price, $type, $quantity, $name)

        ) {
            while ($stmt->fetch()) {
                array_push($decorations, array(
                    "id" => $id,
                    "package_name" => $package_name,
                    "decoration_price" => $price,
                    "price" => $price,
                    "type" => $type,
                    "quantity" => $quantity,
                    "name" => $name,
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
        $query = 'SELECT id,name FROM venue_image WHERE venue=' . $id;
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

    function checkAvailableTimeSlots($id, $date)
    {
        $timeSlots = [];
        $query = "SELECT ts.id,ts.slot,ts.slot_name FROM venue_time_slot vt INNER JOIN time_slot ts ON vt.slot=ts.id WHERE vt.venue =" . $id . " AND vt.slot NOT IN (SELECT time_slot  FROM venue_booking WHERE venue=" . $id . " AND date='" . $date . "')";

        // echo $query;
        $stmt = $this->conn->prepare($query);
        if (
            $stmt &&
            $stmt->execute() &&
            $stmt->store_result() &&
            $stmt->bind_result($id, $slot, $slot_name)

        ) {
            while ($stmt->fetch()) {
                array_push($timeSlots, array(
                    "id" => $id,
                    "slot" => $slot,
                    "slot_name" => $slot_name,
                ));
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
}
