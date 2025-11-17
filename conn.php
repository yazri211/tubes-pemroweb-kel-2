<?php
$conn = mysqli_connect("localhost","root", "","tubes");
if (!$conn){
    die("koneksi gagal". mysqli_connect_error());
}
?>