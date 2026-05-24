<?php

$conn = mysqli_connect("localhost", "root", "", "education_cms");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

?>