<?php
require 'vendor/autoload.php';
use Aws\S3\S3Client;
// Instantiate an Amazon S3 client.
$s3Client = new S3Client([
'version' => 'latest',
'region'  => 'ap-south-1',                //Add your bucket region here
'credentials' => [
'key'    => 'AKIAR7MIJYJCCHJKKPXO',     //Add your access key here
'secret' => 'dFBNdJhfH1QKq8xDldJRK5KRAUHhiykAovU7+oRG'  //Add your secret key here
]
]);
// Check if the form was submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
// Check if file was uploaded without errors
if(isset($_FILES["anyfile"]) && $_FILES["anyfile"]["error"] == 0){
$allowed = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png");
$filename = $_FILES["anyfile"]["name"];
$filetype = $_FILES["anyfile"]["type"];
$filesize = $_FILES["anyfile"]["size"];
// Validate file extension
$ext = pathinfo($filename, PATHINFO_EXTENSION);
if(!array_key_exists($ext, $allowed)) die("Error: Please select a valid file format.");
// Validate file size - 10MB maximum
$maxsize = 10 * 1024 * 1024;
if($filesize > $maxsize) die("Error: File size is larger than the allowed limit.");
// Validate type of the file
if(in_array($filetype, $allowed)){
// Check whether file exists before uploading it
if(file_exists("uploads/" . $filename)){
echo $filename . " is already exists.";
} else{
if(move_uploaded_file($_FILES["anyfile"]["tmp_name"], "uploads/" . $filename)){
$bucket = '3tier-architecture';                        //Add your bucket name here
$file_Path = __DIR__ . '/uploads/'. $filename;    // create uploads folder in html path
$key = basename($file_Path);
try {
$result = $s3Client->putObject([
'Bucket' => $bucket,
'Key'    => $key,
'Body'   => fopen($file_Path, 'r'),
'ACL'    => 'public-read', // make file 'public'
]);
echo " Image uploaded successfully. Image path is: ". $result->get('ObjectURL');
} catch (Aws\S3\Exception\S3Exception $e) {
echo " There was an error uploading the file.\n";
echo $e->getMessage();
}
echo " Your file was uploaded successfully.";
}else{
echo "File is not uploaded";
}
}
} else{
echo " Error: There was a problem uploading your file. Please try again.";
}
} else{
echo " Error: " . $_FILES["anyfile"]["error"];
}
}
$name=$_POST["name"];
$s3url=$result->get("ObjectURL");

$servername = "mysql-container"; // Enter your database endpoint here
$username = "root";       // Enter your database username here
$password = "Pass@123";  // Enter your database password here
$dbname = "3tier";      // Enter your database name here

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);
// Check connection
if (!$conn) {
  die("Connection failed: " . mysqli_connect_error());
}
$sql = "INSERT INTO user(name,urls3) VALUES('$name','$s3url')";
if (mysqli_query($conn, $sql)) {
  echo " New record created successfully";
} else {
  echo "Error: " . $sql . "<br>" . mysqli_error($conn);
}
mysqli_close($conn);
?>
