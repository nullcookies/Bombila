<?php

// phpinfo();
// exit(0);

header("Content-Type: application/json; charset=UTF-8");

// include "database.php";

// $mysqlnd = function_exists('mysqli_query'); /* mysqli_fetch_all */

// if ($mysqlnd) {
//     echo 'mysqlnd enabled!';
//     error_log( 'mysqlnd enabled!' );
// }
// else {
//     error_log( 'mysqlnd disabled!' );
// }

// exit(0);

$SERVER = "localhost";
$DATABASE = "bombila_main";
$LOGIN = "bombila_main";
$PASSWORD = "MQivz6tnKI";

$connection = "";

$route = key($_GET);

openDbConnection();

if( $route == 'petrolStations/list' )
	petrolStationsList();

else if( $route == 'users/register' ) {

  $json = file_get_contents('php://input');
  $user = json_decode($json,true);

  if( $json === FALSE || $user === NULL ) {
      http_response_code(400);    
      exit(0);  
  }

  // error_log( "!!!".$json );
  // error_log( "!!!".print_r($user,true) );

  registerUser($user);
}

else if( $route == 'users/setCertificationPhoto' ) {

  $temp = $_FILES['image_file']['tmp_name'];
  $file = getcwd().'/../../files/'.$_POST['phone'].'/cert.jpg';

  $result = copy( $temp, $file );
  if( !$result ) {
    http_response_code(500);    
    echo "Failed to save file: " . mysqli_connect_error();
    exit(0);    
  }
}

else if( $route == 'users/testImage' ) {

  $imageBytes = file_get_contents( '/var/www/vhosts/bombila/~2.jpg' ); 

  header("Content-type: image/jpeg");
  // header("Content-Disposition: attachment; filename=\"image_file\"");
  echo $imageBytes;  

}

else if( $route == 'users/getCertificationPhoto' )
  getCertificationPhoto( $_GET['phone'] );

else if( $route == 'users/deleteCertificationPhoto' )
  deleteCertificationPhoto( $_GET['phone'] );


else if( $route == 'users/setAutoPhoto' ) {

  $temp = $_FILES['image_file']['tmp_name'];
  $file = getcwd().'/../../files/'.$_POST['phone'].'/auto.jpg';

  $result = copy( $temp, $file );
  if( !$result ) {
    http_response_code(500);    
    echo "Failed to save file: " . mysqli_connect_error();
    exit(0);    
  }
}

else if( $route == 'users/getAutoPhoto' )
  getAutoPhoto( $_GET['phone'] );

else if( $route == 'users/deleteAutoPhoto' )
  deleteAutoPhoto( $_GET['phone'] );


else if( $route == 'users/enter' )
  enter2( $_GET['phone'] );

else if( $route == 'users/getOneByPhone' ) {

	// echo( $_GET['phone'] );
	// exit(0);

	getOneUserByPhone( $_GET['phone'] );
}

else if( $route == 'users/getBombilaStatus' )
  getBombilaStatus( $_GET['phone'] );

else if( $route == 'messages/list' )
  getMessages( $_GET['phone'] );

else if( $route == 'chats/getMessages' )
  getChatMessages( $_GET['phone1'], $_GET['phone2'] );

else if( $route == 'hotels/getNearest' )
  getNearestHotels( $_GET['latitude'], $_GET['longitude'] );

else if( $route == 'hotels/getOne' )
  getOneHotel( $_GET['id'] );

else if( $route == 'orders/new' ) {

  $json = file_get_contents('php://input');
  $order = json_decode($json,true);

  if( $json === FALSE || $order === NULL ) {
      http_response_code(400);    
      exit(0);  
  }

  error_log( "!!!".$json );
  error_log( "!!!".print_r($order,true) );

  newOrder( $order );  
}

else if( $route == 'orders/accept' )
  acceptOrder( $_GET['phone'], $_GET['order_id'] );

else if( $route == 'orders/go' )
  goOrder( $_GET['phone'], $_GET['order_id'] );

else if( $route == 'orders/getNearest' )
  getNearestOrders( $_GET['latitude'], $_GET['longitude'] );

else {
  http_response_code(404);
  echo '';
}


// print_r($_GET);
// echo $value;
// echo $p;
// echo $_GET['var1'];

// echo 'hello';

closeDbConnection();

function openDbConnection() {
  global $connection;
  global $DATABASE;
  global $LOGIN;
  global $PASSWORD;
  $connection = mysqli_connect("localhost", $LOGIN, $PASSWORD, $DATABASE);
  if (mysqli_connect_errno()) {
		http_response_code(500);  	
    // echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit(0);
  }
  mysqli_set_charset($connection, "utf8mb4");
}

function closeDbConnection() {
  global $connection;
  mysqli_close($connection);
}


function petrolStationsList() {
  global $connection;

  $query = "SELECT * FROM petrol_stations";
  $result = mysqli_query($connection, $query);

  $array = array();

  while($row = mysqli_fetch_assoc($result)) {
    $array[] = $row;
  }

  echo json_encode($array);
}

function getOneUserByPhone($phone) {
  global $connection;

  $query = sprintf("SELECT * FROM users where phone='%s' limit 1", $phone);  

  $result = mysqli_query($connection, $query);

	if( mysqli_num_rows($result) == 0  ) {
		http_response_code(404);
		echo '';
	}
	else {
	  $row = mysqli_fetch_assoc($result);
	  echo json_encode($row);	
	}
}

function registerUser($user) {
  global $connection;

  $stmt = mysqli_prepare($connection, "SELECT * FROM users where phone=? limit 1");
  mysqli_stmt_bind_param($stmt, "s", $user['phone']);
  $result = mysqli_stmt_execute($stmt);
  mysqli_stmt_store_result($stmt);

  if( !$result ) {
    http_response_code(500);
    error_log( "error:".mysqli_error( $connection ) );     
    echo '';
    exit(0);
  }
  if( mysqli_stmt_num_rows($stmt) == 1 ) {
    http_response_code(406); // User already registered
    error_log("error: user with phone=".$user['phone']." already registered");         
    echo '';
    exit(0);    
  }

  $stmt = mysqli_prepare($connection, "INSERT INTO users(phone, firstname, lastname) VALUES(?, ?, ?)");
  mysqli_stmt_bind_param($stmt, "sss", $user['phone'], $user['firstname'], $user['lastname']);
  $result = mysqli_stmt_execute($stmt);

  if( !$result ) {
    http_response_code(500);
    error_log("error: user with phone=".$user['phone']." insert error");  
    echo '';
    exit(0);     
  }

    /* проверить */

    // $stmt = mysqli_prepare($connection, "INSERT INTO bombilas_certifications_photos(phone, photo, extension) VALUES(?, ?, ?)");
    // $photo = null;
    // $extension = '';
    // mysqli_stmt_bind_param($stmt, "sbs", $user['phone'], $photo, $extension);
    // $result = mysqli_stmt_execute($stmt);    

    // if( !$result ) {
    //   http_response_code(500);
    //   error_log("error: bombilas_certifications_photos record insert error");  
    //   echo '';
    //   exit(0);     
    // }        

    // $stmt = mysqli_prepare($connection, "INSERT INTO bombilas_autos_photos(phone, photo, extension) VALUES(?, ?, ?)");
    // $photo = null;
    // $extension = '';    
    // mysqli_stmt_bind_param($stmt, "sbs", $user['phone'], $photo, $extension);
    // $result = mysqli_stmt_execute($stmt);    

    // if( !$result ) {
    //   http_response_code(500);
    //   error_log("error: bombilas_autos_photos record insert error");  
    //   echo '';
    //   exit(0);     
    // }    

  $currentdir = getcwd();

  mkdir( $currentdir.'/../../files/'.$user['phone'] );

  echo '';
}






function enter($phone) {
  global $connection;

  $stmt = mysqli_prepare($connection, "SELECT * FROM users where phone=? limit 1");
  mysqli_stmt_bind_param($stmt, "s", $phone);
  $result = mysqli_stmt_execute($stmt);
  mysqli_stmt_store_result($stmt);

  if( !$result ) {
    http_response_code(500);
    error_log( "error:".mysqli_error( $connection ) );     
    echo '';
    mysqli_stmt_close($stmt);    
    exit(0);
  }
  if( mysqli_stmt_num_rows($stmt) == 1 ) {

    // mysqli_stmt_bind_result($stmt, $row);
    // $row = mysqli_stmt_fetch_assoc($stmt);

    // $row = mysqli_stmt_fetch_assoc($stmt);

    $row = mysqli_fetch_assoc( mysqli_stmt_get_result( $stmt ) );

    error_log("!!! user:".$row);
    echo json_encode($row);     
  }
  else {
    http_response_code(404);
    error_log("error: user with phone=".$phone." not registered");         
    echo '';
  }   

   mysqli_stmt_close($stmt);
}

function enter2($phone) {
  global $connection;

  $query = sprintf("SELECT * FROM users where phone='%s' limit 1", $phone);  

  $result = mysqli_query($connection, $query);

  if( mysqli_num_rows($result) == 0  ) {
    http_response_code(404);
    echo '';
  }
  else {
    $row = mysqli_fetch_assoc($result);
    echo json_encode($row); 
  }  
}

function getBombilaStatus( $phone ) {
  global $connection;

  $query = sprintf("SELECT bs.phone,u.firstname,u.lastname,bs.rating,bs.accepted_orders,bs.fullfilled_orders FROM bombilas_statuses as bs INNER JOIN users as u on bs.phone = u.phone where bs.phone='%s' limit 1", $phone);

  $result = mysqli_query($connection, $query);

  if( mysqli_num_rows($result) == 0  ) {
    http_response_code(404);
    echo '';
  }
  else {
    $row = mysqli_fetch_assoc($result);
    echo json_encode($row); 
  }  
}

function getMessages( $phone ) {

  global $connection;

  $query = "SELECT header,text FROM messages where phone='".$phone."'";
  $result = mysqli_query($connection, $query);

  $array = array();

  while($row = mysqli_fetch_assoc($result)) {
    $array[] = $row;
  }

  echo json_encode($array);  
}

function getChatMessages( $phone1, $phone2 ) {
  global $connection;

  $query = "SELECT c.from_user,c.to_user,concat(u1.firstname,' ',u1.lastname) as 'from_fullname',c.message FROM chats as c inner join users as u1 on c.from_user = u1.phone inner join users as u2 on c.to_user = u2.phone where ( from_user='".$phone1."' and to_user='".$phone2."' ) or ( from_user='".$phone2."' and to_user='".$phone1."')";
  $result = mysqli_query($connection, $query);

  $array = array();

  while($row = mysqli_fetch_assoc($result)) {
    $array[] = $row;
  }

  echo json_encode($array);    
}

function setUserCertificationPhoto( $phone, $extension, $imageBytes ) {
  global $connection;

  // $imageBytes = mysqli_real_escape_string( $connection, $imageBytes );

  $stmt = mysqli_prepare($connection, "UPDATE bombilas_certifications_photos set photo=?,extension=? where phone=?");
  mysqli_stmt_bind_param($stmt, "bss", $imageBytes, $extension, $phone );
  $result = mysqli_stmt_execute($stmt);
  mysqli_stmt_store_result($stmt);  

    if( !$result ) {
      http_response_code(500);
      error_log("error: bombilas_certification_photos record update error");
      echo '';
      exit(0);     
    }

    echo '';

  // $query = "UPDATE bombilas_certifications_photos set photo='$imageBytes',extension='$extension' where phone='$phone'";
  // $result = mysqli_query($connection, $query);  

  // if( !$result ) {
  //   http_response_code(500);
  //   error_log("error: bombilas_certification_photos record update error");
  //   echo '';
  //   exit(0);     
  // }

  // echo '';  
}

function setUserAutoPhoto( $phone, $imageBytes, $ext ) {

}

function getCertificationPhoto( $phone ) {
  $imageBytes = file_get_contents( getcwd().'/../../files/'.$phone.'/cert.jpg' );

  header("Content-type: image/jpeg");
  echo $imageBytes;  
}

function deleteCertificationPhoto( $phone ) {

  $result = unlink( getcwd().'/../../files/'.$phone.'/cert.jpg' );

  if( !$result ) {
      http_response_code(500);
      error_log("error: could not delete file");
      echo '';
      exit(0);       
  }

  echo '';
}

function getAutoPhoto( $phone ) {
  $imageBytes = file_get_contents( getcwd().'/../../files/'.$phone.'/auto.jpg' );

  header("Content-type: image/jpeg");
  echo $imageBytes;  
}

function deleteAutoPhoto( $phone ) {

  $result = unlink( getcwd().'/../../files/'.$phone.'/auto.jpg' );

  if( !$result ) {
      http_response_code(500);
      error_log("error: could not delete file");
      echo '';
      exit(0);       
  }

  echo '';
}

function getNearestHotels( $latitude, $longitude ) {
  global $connection;

  $query = 
"SELECT 
   h.id, h.name, h.description_short, h.latitude, h.longitude, 
   111.11 *
    ROUND(DEGREES(ACOS(LEAST(COS(RADIANS(h.latitude))
         * COS(RADIANS({$latitude}))
         * COS(RADIANS(h.longitude - {$longitude}))
         + SIN(RADIANS(h.latitude))
         * SIN(RADIANS({$latitude})), 1.0))), 2) AS distance_in_km
FROM hotels as h
ORDER BY distance_in_km
LIMIT 100
";

  //$query = "select * from hotels";
  $result = mysqli_query($connection, $query);

  $array = array();

  while($row = mysqli_fetch_assoc($result)) {
    $array[] = $row;
  }

  echo json_encode($array);
}

function getOneHotel( $id ) {
  global $connection;

  $query = "SELECT * from hotels where id='{$id}' limit 1";
  $result = mysqli_query($connection, $query);

  if( mysqli_num_rows($result) == 0  ) {
    http_response_code(404);
    echo '';
    exit(0);
  }

  $hotel = mysqli_fetch_assoc($result);

  // отзывы

  $query = "SELECT reviewer_name,text,mark from hotels_reviews where hotel_id='{$id}'";
  $result = mysqli_query($connection, $query);

  $hotel['reviews'] = array();

  while($row = mysqli_fetch_assoc($result)) {
    $hotel['reviews'][] = $row;
  }  

  // изображения

  $files = scandir( getcwd().'/../../files/hotels/'.$hotel['id']);

  error_log( "!!! files: ".print_r($files,true) );

  $files = array_diff($files, array('.', '..'));

  error_log( "!!! files: ".print_r($files,true) );  

  foreach( $files as $file ) {
    $image['href'] = 'http://'.$_SERVER['HTTP_HOST'].'/files/hotels/'.$hotel['id'].'/'.$file;
    $hotel['images'][] = $image;
  }

  // $hotel['images'][0]['href'] = 'http://'.$_SERVER['HTTP_HOST'].'/cert.jpg';
  // $hotel['images'][1]['href'] = 'http://'.$_SERVER['HTTP_HOST'].'/cert.jpg';
  // $hotel['images'][2]['href'] = 'http://'.$_SERVER['HTTP_HOST'].'/cert.jpg';

  echo json_encode($hotel);
}

function newOrder( $order ) {
  global $connection;

  $query = 
"INSERT INTO orders( 
  passenger, 
  from_latitude, 
  from_longitude, 
  from_address, 
  to_latitude,
  to_longitude,
  to_address,
  passengers_comment,
  payment_method,
  not_to_call,
  wait,
  not_to_smoke,
  childish_armchair,
  state
)
VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?)
";

  $state = 'new';

  $stmt = mysqli_prepare($connection, $query);
  mysqli_stmt_bind_param($stmt, "sddsddsssiiiis", 
    $order['passenger'], 
    $order['from_latitude'], 
    $order['from_longitude'],
    $order['from_address'],
    $order['to_latitude'],
    $order['to_longitude'],
    $order['to_address'],
    $order['passengers_comment'],
    $order['payment_method'],
    $order['not_to_call'],
    $order['wait'],
    $order['not_to_smoke'],
    $order['childish_armchair'],
    $state
  );  

  // error_log("!!! query:".);

  $result = mysqli_stmt_execute($stmt);

  if( !$result ) {
    http_response_code(500);
    error_log("error: order from user=".$order['passenger']." insert error");  
    echo '';
    exit(0);     
  }

  echo '';  
}


function acceptOrder( $phone, $order_id ) {
  global $connection;

  $query = sprintf("UPDATE orders set bombila='{$phone}', state='accepted' where id={$order_id}"); 
  $result = mysqli_query($connection, $query);

  if( !$result ) {
    http_response_code(500);
    error_log( "error: orders status was not updated : ".mysqli_error( $connection ) );
    echo '';
    exit(0);
  }

  echo '';
}


function goOrder( $phone, $order_id ) {
  global $connection;

  $query = sprintf("UPDATE orders set state='in_way' where id={$order_id} and bombila='{$phone}'"); 
  $result = mysqli_query($connection, $query);

  if( !$result ) {
    http_response_code(500);
    error_log( "error: orders status was not updated : ".mysqli_error( $connection ) );
    echo '';
    exit(0);
  }

  echo '';
}

function finishOrder( $phone, $order_id ) {
  global $connection;

  $query = sprintf("UPDATE orders set state='finished' where id={$order_id} and passenger='{$phone}'"); 
  $result = mysqli_query($connection, $query);

  if( !$result ) {
    http_response_code(500);
    error_log( "error: orders status was not updated : ".mysqli_error( $connection ) );
    echo '';
    exit(0);
  }

  $query = 
"INSERT into trips_history(
  passenger,
  from_latitude,
  from_longitude,
  from_address,
  to_latitude,
  to_longitude,
  to_address
)
SELECT
  passenger,
  from_latitude,
  from_longitude,
  from_address,
  to_latitude,
  to_longitude,
  to_address
FROM 
  orders
WHERE 
  id = {$order_id} and 
  passenger = '{$phone}'
";

  $result = mysqli_query($connection, $query);

  if( !$result ) {
    http_response_code(500);
    error_log( "error: trip history record can not be inserted : ".mysqli_error( $connection ) );
    echo '';
    exit(0);
  }

  echo '';  
}


function getNearestOrders( $latitude, $longitude ) {
  global $connection;

  $query = 
"SELECT 
   *, 
   111.11 *
    ROUND(DEGREES(ACOS(LEAST(COS(RADIANS(o.from_latitude))
         * COS(RADIANS({$latitude}))
         * COS(RADIANS(o.from_longitude - {$longitude}))
         + SIN(RADIANS(o.from_latitude))
         * SIN(RADIANS({$latitude})), 1.0))), 2) AS distance_in_km
FROM orders as o
WHERE o.state = 'new'
ORDER BY distance_in_km
LIMIT 100
";

  $result = mysqli_query($connection, $query);

  $array = array();

  while($row = mysqli_fetch_assoc($result)) {

    $row['not_to_call'] = boolval( $row['not_to_call'] );
    $row['wait'] = boolval( $row['wait'] );
    $row['not_to_smoke'] = boolval( $row['not_to_smoke'] );
    $row['childish_armchair'] = boolval( $row['childish_armchair'] );
    
    $array[] = $row;
  }

  echo json_encode($array);
}


?>
