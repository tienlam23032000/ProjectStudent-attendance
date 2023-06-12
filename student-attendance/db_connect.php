<?php 
// $conn= new mysqli('localhost:3306','root','','student_attendance_db')
// or
// die("Could not connect to mysql".mysqli_error($con));
$conn = mysqli_connect("localhost:3306","root","","student_attendance_db");
if(!$conn) die("kết nối thất bại:".mysqli_connect_error());
?>
