<?php

function send_text($number, $message)
{
     $message = urlencode($message);
     $url = 'https://api.smscountry.com/SMSCwebservice_bulk.aspx?User=homesearch&Passwd=lvadda@413&Sid=GROVIS&Mobilenumber=' . $number . '&message=' . $message . '&Mtype=N&DR=Y';
     //step1
     $cSession = curl_init();
     //step2
     curl_setopt($cSession, CURLOPT_URL, $url);
     curl_setopt($cSession, CURLOPT_RETURNTRANSFER, true);
     curl_setopt($cSession, CURLOPT_HEADER, false);
     //step3
     $result = curl_exec($cSession);
     //step4
     curl_close($cSession);
     //step5
     return $result;
}



function image_upload($name, $tmp_name, $size)
{
     $target_file = $name;
     $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

     // Check if image file is a actual image or fake image
     $check = getimagesize($tmp_name);
     if ($check == false) {
          return array("status" => "fail", "error" => "Not an image");
     }

     // Check if file already exists
     if (file_exists($target_file)) {
          return array("status" => "fail", "error" => "Already exists");
     }
     // Check file size
     if ($size > 800000) {
          return array("status" => "fail", "error" => "File is too large");
     }
     // Allow certain file formats
     if (
          $imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
          && $imageFileType != "gif"
     ) {
          return array("status" => "fail", "error" => "only JPG, JPEG, PNG & GIF files are allowed");
     }
     if (move_uploaded_file($tmp_name, $target_file)) {
          return array("status" => "success", "error" => "Image uploaded successfully");
     } else {
          return array("status" => "fail", "error" => "there was an error uploading your file");
     }
}

function slugify($text)
{
     // replace non letter or digits by -
     $text = preg_replace('~[^\pL\d]+~u', '-', $text);

     // transliterate
     $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

     // remove unwanted characters
     $text = preg_replace('~[^-\w]+~', '', $text);

     // trim
     $text = trim($text, '-');

     // remove duplicate -
     $text = preg_replace('~-+~', '-', $text);

     // lowercase
     $text = strtolower($text);

     if (empty($text)) {
          return 'n-a';
     }

     return $text;
}

function imageSaver($image_data, $path)
{

     list($type, $data) = explode(';', $image_data); // exploding data for later checking and validating 

     if (preg_match('/^data:image\/(\w+);base64,/', $image_data, $list)) {
          $data = substr($data, strpos($data, ',') + 1);
          $type = strtolower($list[1]); // jpg, png, gif
          echo $type;
          if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
               throw new \Exception('invalid image type');
          }

          $data = base64_decode($data);

          if ($data === false) {
               throw new \Exception('base64_decode failed');
          }
     } else {
          throw new \Exception('did not match data URI with image data');
     }

     $imageName = time() . "." . $type;

     $fullname = $path . $imageName;

     if (file_put_contents($fullname, $data)) {
          $result = $imageName;
     } else {
          $result =  "";
     }
     /* it will return image name if image is saved successfully 
    or it will return error on failing to save image. */
     return $result;
}
