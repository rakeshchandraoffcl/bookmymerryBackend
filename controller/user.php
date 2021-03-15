<?php
require(dirname(__FILE__) . '/../config/db.php');
class User extends DB
{
    private $table = 'user';

    function signUp($fullName, $email, $phoneNumber, $password)
    {
        $stmt = $this->conn->prepare('INSERT INTO ' . $this->table . ' SET
        full_name=?, 
        email=?,
        password=?,
        phone_number=?,
        created_at=NOW()
        ');
        if (
            $stmt &&
            $stmt->bind_param('ssss', $fullName, $email, $password, $phoneNumber) &&
            $stmt->execute()
        ) {
            $user_details = $this->getDetailsById($this->conn->insert_id);
            return array("status" => "success", "data" => $user_details['data']);
        } else {
            return array("status" => "fail", "error" => $this->conn->error);
        }
    }
    function signUpByAdmin($fullName, $email, $phoneNumber, $password, $status, $number_verified)
    {
        $stmt = $this->conn->prepare('INSERT INTO ' . $this->table . ' SET
        full_name=?, 
        email=?,
        password=?,
        phone_number=?,
        status=?,
        number_verified=?,
        created_at=NOW(),
        created_by="admin"
        ');
        if (
            $stmt &&
            $stmt->bind_param('ssssii', $fullName, $email, $password, $phoneNumber, $status, $number_verified) &&
            $stmt->execute()
        ) {
            $user_details = $this->getDetailsById($this->conn->insert_id);
            return array("status" => "success", "data" => $user_details['data']);
        } else {
            return array("status" => "fail", "error" => $this->conn->error);
        }
    }


    function updateUser($data)
    {
        $id = $data['id'];
        $fields = $data;
        $query = ' UPDATE ' . $this->table . ' SET ';
        $validFields = ['full_name', 'email', 'phone_number', 'password', 'status', 'number_verified', 'otp', 'otp_expires_at'];

        foreach ($data as $key => $value) {
            if (!in_array($key, $validFields)) {
                unset($fields[$key]);
            }
        };
        foreach ($fields as $key => $value) {
            $query .= $key . '="' . $value . '",';
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
            return array("status" => "success", "data" => $user_details);
        } else {
            if ($this->conn->error) {
                return array("status" => "fail", "error" => $this->conn->error);
            }
            if ($stmt->error) {
                return array("status" => "fail", "error" => $stmt->error);
            }
        }
    }

    function setOtp($otp, $id)
    {

        $dataType = '';
        $query = ' UPDATE ' . $this->table . ' SET otp=' . $otp . ',otp_expires_at=DATE_ADD(NOW(), INTERVAL 10 MINUTE),number_verified=0';
        $query .= ' WHERE id=' . $id;
        // echo $query;
        $stmt = $this->conn->prepare($query);
        if (
            $stmt &&
            $stmt->execute() &&
            ($stmt->affected_rows === 1 || $stmt->affected_rows === 0)
        ) {
            $user_details = $this->getDetailsById($id);
            return array("status" => "success", "data" => $user_details);
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
        $stmt = $this->conn->prepare('SELECT id,full_name,email,phone_number,created_at,number_verified,status from ' . $this->table . ' WHERE id=?');
        if (
            $stmt &&
            $stmt->bind_param('i', $id) &&
            $stmt->execute() &&
            $stmt->store_result() &&
            $stmt->bind_result($id, $full_name, $email, $phone_number, $created_at, $number_verified, $status)
        ) {
            if ($stmt->fetch()) {
                $details["id"] = $id;
                $details["full_name"] = $full_name;
                $details["email"] = $email;
                $details["phone_number"] = $phone_number;
                $details["number_verified"] = $number_verified;
                $details["created_at"] = $created_at;
                $details["status"] = $status;
                return array("status" => "success", "data" => $details);
            } else {
                return array("status" => "fail", "error" => "user not found");
            }
        } else {
            return array("status" => "fail", "error" => $this->conn->error);
        }
    }

    function getDetailsByEmail($email)
    {
        $details = array();
        $stmt = $this->conn->prepare('SELECT id,password,full_name,email,phone_number,created_at,number_verified,status from ' . $this->table . ' WHERE email=?');
        if (
            $stmt &&
            $stmt->bind_param('s', $email) &&
            $stmt->execute() &&
            $stmt->store_result() &&
            $stmt->bind_result($id, $password, $full_name, $email, $phone_number, $created_at, $number_verified, $status)
        ) {
            if ($stmt->num_rows() > 0) {
                if ($stmt->fetch()) {
                    $details["id"] = $id;
                    $details["full_name"] = $full_name;
                    $details["password"] = $password;
                    $details["email"] = $email;
                    $details["phone_number"] = $phone_number;
                    $details["number_verified"] = $number_verified;
                    $details["created_at"] = $created_at;
                    $details["status"] = $status;
                    return array("status" => "success", "data" => $details);
                } else {
                    return array("status" => "fail", "error" => "user not found");
                }
            } else {
                return array("status" => "fail", "error" => "No user found");
            }
        } else {
            return array("status" => "fail", "error" => $this->conn->error);
        }
    }
    function getDetailsByPhone($phone_number)
    {
        $details = array();
        $stmt = $this->conn->prepare('SELECT id,password,full_name,email,phone_number,created_at,number_verified,status from ' . $this->table . ' WHERE phone_number=?');
        if (
            $stmt &&
            $stmt->bind_param('s', $phone_number) &&
            $stmt->execute() &&
            $stmt->store_result() &&
            $stmt->bind_result($id, $password, $full_name, $email, $phone_number, $created_at, $number_verified, $status)
        ) {
            if ($stmt->num_rows() > 0) {
                if ($stmt->fetch()) {
                    $details["id"] = $id;
                    $details["full_name"] = $full_name;
                    $details["password"] = $password;
                    $details["email"] = $email;
                    $details["phone_number"] = $phone_number;
                    $details["number_verified"] = $number_verified;
                    $details["created_at"] = $created_at;
                    $details["status"] = $status;
                    return array("status" => "success", "data" => $details);
                } else {
                    return array("status" => "fail", "error" => "user not found");
                }
            } else {
                return array("status" => "fail", "error" => "No user found");
            }
        } else {
            return array("status" => "fail", "error" => $this->conn->error);
        }
    }

    function otpVerify($phone_number, $otp)
    {
        $stmt = $this->conn->prepare('SELECT id,otp_expires_at from ' . $this->table . ' WHERE phone_number=? AND otp = ?');
        if (
            $stmt &&
            $stmt->bind_param('si', $phone_number, $otp) &&
            $stmt->execute() &&
            $stmt->store_result() &&
            $stmt->bind_result($id, $otp_expires_at)
        ) {
            if ($stmt->num_rows() > 0) {
                if ($stmt->fetch()) {
                    date_default_timezone_set("Asia/Calcutta");
                    $date = date('Y-m-d H:i:s');
                    // echo $date;
                    if ($date < $otp_expires_at) {
                        $this->updateUser(array("id" => $id, "otp" => null, "number_verified" => 1, "otp_expires_at" => null));
                        $user_details = $this->getDetailsById($id);
                        return array("status" => "success", "data" => $user_details['data']);
                    } else {
                        return array("status" => "fail", "error" => "OTP expired");
                    }
                } else {
                    return array("status" => "fail", "error" => "Incorrect otp");
                }
            } else {
                return array("status" => "fail", "error" => "Incorrect otp");
            }
        } else {
            return array("status" => "fail", "error" => $this->conn->error);
        }
    }

    function getUsers($count, $page)
    {
        $limit = $this->parse($count);
        $offset = ($this->parse($page) - 1) * $limit;
        $users = [];
        $query = 'SELECT id,full_name,email,phone_number,created_at,updated_at,status,number_verified from ' . $this->table . ' ORDER BY created_at DESC LIMIT ' . $limit . ' OFFSET ' . $offset . '';
        $stmt = $this->conn->prepare($query);
        if (
            $stmt &&
            $stmt->execute() &&
            $stmt->store_result() &&
            $stmt->bind_result($id, $full_name, $email, $phone_number, $created_at, $updated_at, $status, $number_verified)

        ) {
            while ($stmt->fetch()) {
                array_push($users, array("id" => $id, "full_name" => $full_name, "email" => $email, "phone_number" => $phone_number, "created_at" => $created_at, "updated_at" => $updated_at, "status" => $status ? true : false, "number_verified" => $number_verified ? true : false));
            }

            $totalCounts = $this->rowCounts();
            return array("status" => "success", "data" => array("users" => $users, "totalCount" => $totalCounts, "totalPages" => ceil($totalCounts / $limit)));
        } else {
            if ($this->conn->error) {
                return array("status" => "fail", "error" => $this->conn->error);
            }
            if ($stmt->error) {
                return array("status" => "fail", "error" => $stmt->error);
            }
        }
    }


    function rowCounts()
    {

        $query = 'SELECT COUNT(*) as totalDocs from ' . $this->table;
        $stmt = $this->conn->prepare($query);

        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($totalDocs);
        $stmt->fetch();
        return $totalDocs;
    }
}
