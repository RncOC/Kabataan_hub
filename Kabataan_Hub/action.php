<?php
require 'config.php'; require_login();
$id=(int)$_SESSION['id']; $a=$_POST['action']??'';
if($a==='register'){
 $event=(int)$_POST['event_id'];$s=$conn->prepare('INSERT IGNORE INTO registrations(user_id,event_id) VALUES(?,?)');$s->bind_param('ii',$id,$event);$s->execute();
 notify_user($id,'Event Registration','Your event registration was submitted.');
}
elseif($a==='feedback'){
 $s=$conn->prepare('INSERT INTO feedback(user_id,subject,message) VALUES(?,?,?)');$s->bind_param('iss',$id,$_POST['subject'],$_POST['message']);$s->execute();notify_user($id,'Feedback Submitted','Your feedback has been received.');
}
elseif($a==='service'){
 $s=$conn->prepare('INSERT INTO service_requests(user_id,service_type,details) VALUES(?,?,?)');$s->bind_param('iss',$id,$_POST['service_type'],$_POST['details']);$s->execute();notify_user($id,'Service Request Submitted','Your service request has been received.');
}
elseif($a==='profile'){
 $name=trim($_POST['name']);$email=trim($_POST['email']);$phone=trim($_POST['phone']);$address=trim($_POST['address']);
 $s=$conn->prepare('UPDATE users SET name=?,email=?,phone=?,address=? WHERE id=?');$s->bind_param('ssssi',$name,$email,$phone,$address,$id);$s->execute();$_SESSION['name']=$name;$_SESSION['email']=$email;$_SESSION['phone']=$phone;$_SESSION['address']=$address;
 if(!empty($_POST['new_password'])){if(strlen($_POST['new_password'])<6)$_SESSION['msg']='Password must be at least 6 characters.';else{$h=password_hash($_POST['new_password'],PASSWORD_DEFAULT);$q=$conn->prepare('UPDATE users SET password=? WHERE id=?');$q->bind_param('si',$h,$id);$q->execute();$_SESSION['password']=$h;$_SESSION['msg']='Profile and password updated.';}}
 else $_SESSION['msg']='Profile updated.';
}
elseif($a==='read_notifications'){ $s=$conn->prepare('UPDATE notifications SET is_read=1 WHERE user_id=?');$s->bind_param('i',$id);$s->execute(); }
redirect('dashboard.php');
?>