<?php
session_start();
$conn = new mysqli('localhost','root','','kabataan_hub');
if ($conn->connect_error) die('Database connection failed');
$conn->set_charset('utf8mb4');

function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function require_login(){ if(!isset($_SESSION['id'])){ header('Location: login.php'); exit; } }
function require_admin(){ require_login(); if(($_SESSION['role']??'')!=='admin'){ http_response_code(403); die('Admin access only.'); } }
function notify_user($user_id,$title,$message){
    global $conn;
    $s=$conn->prepare("INSERT INTO notifications(user_id,title,message) VALUES(?,?,?)");
    $s->bind_param('iss',$user_id,$title,$message); $s->execute();
}
function redirect($url){ header("Location: $url"); exit; }
?>