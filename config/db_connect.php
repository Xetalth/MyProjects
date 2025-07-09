<?php   

    //connect to database
    $conn = mysqli_connect('localhost', 'admin', 'admin1234', 'cozyshare');
    
    //check connection
    if(!$conn){
        echo 'Connection error: ' . mysqli_connect_error();
    }
    
    
?>