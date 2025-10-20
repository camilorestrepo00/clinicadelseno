<?php    function connection() {
        $host = "localhost";
        $user = "root";
        $password = "";
        $database = "clinicaseno";
        $connect = mysqli_connect($host, $user, $password, $database);
        if (!$connect) {
            die("Connection failed: " . mysqli_connect_error());
        }
        return $connect;
    }
    // mysqli_query($connect, "SET NAMES 'utf8'");
    // mysqli_query($connect, "SET CHARACTER SET utf8");
    // mysqli_query($connect, "SET COLLATION_CONNECTION = 'utf8_general_ci'");
    



?>