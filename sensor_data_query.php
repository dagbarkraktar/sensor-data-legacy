<?php header('Content-type: text/html; charset=utf-8'); ?>

<?php
    
    $MYSQL_HOST = "192.168.10.210"
    $MYSQL_DB = "log_data_db"
    $MYSQL_USER = "..."
    $MYSQL_PASS = "..."
    

    // query
    $query_str = "select (sensors_log.timestamp_utc + INTERVAL 3 HOUR) as datetime_local, sensors_log.sensor_value_string from sensors_log where sensors_log.timestamp_utc > (NOW() - INTERVAL 24 HOUR)";

    // db connect
    $link = mysqli_connect($MYSQL_HOST, $MYSQL_USER, $MYSQL_PASS, $MYSQL_DB);
    if (mysqli_connect_errno()) printf("Connect failed: %s\n", mysqli_connect_error());
    mysqli_query($link, 'SET NAMES utf8');

    $ResultArr = array();

    // get query result
    if($result = mysqli_query($link, $query_str)){
        // copy data to array
        while ($line = mysqli_fetch_row($result)){
            $ResultArr[] = $line;
        }
    }
    else print "Query failed : " . mysqli_error( $f_link );

    // row counter
    //$row_cnt = mysqli_num_rows($result);

    // free result memory
    if($result) mysqli_free_result($result);
    // close db
    mysqli_close($link);

    // encode array to json
    echo json_encode($ResultArr);
?>
