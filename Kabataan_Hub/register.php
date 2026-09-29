<?php
require 'config.php'; $e='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 $name=trim($_POST['name']);$email=trim($_POST['email']);$pw=$_POST['password'];
 if(strlen($pw)<6)$e='Password must be at least 6 characters.';
 else{
  $hash=password_hash($pw,PASSWORD_DEFAULT);
  $s=$conn->prepare("INSERT INTO users(name,email,password,role) VALUES(?,?,?,'youth')");
  $s->bind_param('sss',$name,$email,$hash);
  if($s->execute()) redirect('login.php?registered=1');
  $e='Email already exists.';
 }
}
?><!doctype html><html><head><meta name="viewport" content="width=device-width,initial-scale=1"><title>Register</title><link rel="stylesheet" href="assets/style.css"></head><body class="auth"><form class="box" method="post"><h1>Kabataan <i>Hub</i></h1><h2>Youth Registration</h2><?php if($e):?><div class="error"><?=e($e)?></div><?php endif;?><input name="name" placeholder="Full name" required><input name="email" type="email" placeholder="Email" required><input name="password" type="password" placeholder="Password (6+ characters)" minlength="6" required><button class="btn">Create Account</button><p>Already registered? <a href="login.php">Login</a></p></form></body></html>