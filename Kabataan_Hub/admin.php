<?php
require 'config.php'; require_admin();
$msg='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 $a=$_POST['action']??'';$id=(int)($_POST['id']??0);
 if($a==='save_user'){
  $name=trim($_POST['name']);$email=trim($_POST['email']);$role=$_POST['role'];
  if($id){$s=$conn->prepare('UPDATE users SET name=?,email=?,role=? WHERE id=?');$s->bind_param('sssi',$name,$email,$role,$id);$s->execute();}
  else{$h=password_hash($_POST['password']?:'changeme',PASSWORD_DEFAULT);$s=$conn->prepare('INSERT INTO users(name,email,password,role) VALUES(?,?,?,?)');$s->bind_param('ssss',$name,$email,$h,$role);$s->execute();}
 } elseif($a==='delete_user'&&$id!=$_SESSION['id']){$s=$conn->prepare('DELETE FROM users WHERE id=?');$s->bind_param('i',$id);$s->execute();}
 elseif(in_array($a,['save_ann','save_event','save_program','save_project','save_opp'])){
  if($a==='save_ann'){$t='announcements';$cols='title,content';$vals=[$_POST['title'],$_POST['content']];}
  if($a==='save_event'){$t='events';$cols='title,event_date,location,description';$vals=[$_POST['title'],$_POST['event_date'],$_POST['location'],$_POST['description']];}
  if($a==='save_program'){$t='programs';$cols='title,description,status';$vals=[$_POST['title'],$_POST['description'],$_POST['status']];}
  if($a==='save_project'){$t='projects';$cols='title,description,budget,status';$vals=[$_POST['title'],$_POST['description'],$_POST['budget'],$_POST['status']];}
  if($a==='save_opp'){$t='opportunities';$cols='title,description,link,status';$vals=[$_POST['title'],$_POST['description'],$_POST['link'],$_POST['status']];}
  if($id){$sets=implode(',',array_map(fn($c)=>"$c=?",explode(',',$cols)));$types=str_repeat('s',count($vals)).'i';$vals[]=$id;$s=$conn->prepare("UPDATE $t SET $sets WHERE id=?");$s->bind_param($types,...$vals);$s->execute();}
  else{$ph=implode(',',array_fill(0,count($vals),'?'));$types=str_repeat('s',count($vals));if($t==='projects')$types='ssd s';$s=$conn->prepare("INSERT INTO $t($cols) VALUES($ph)");if($t==='projects')$s->bind_param('ssds',...$vals);else$s->bind_param($types,...$vals);$s->execute();}
 }
 elseif($a==='delete'){ $map=['announcements','events','programs','projects','opportunities'];$t=$_POST['table']??'';if(in_array($t,$map)){$s=$conn->prepare("DELETE FROM $t WHERE id=?");$s->bind_param('i',$id);$s->execute();}}
 elseif($a==='status_feedback'){
  $status=$_POST['status'];$reply=trim($_POST['admin_reply']??'');$q=$conn->prepare('SELECT user_id FROM feedback WHERE id=?');$q->bind_param('i',$id);$q->execute();$uid=$q->get_result()->fetch_assoc()['user_id']??0;$s=$conn->prepare('UPDATE feedback SET status=?,admin_reply=? WHERE id=?');$s->bind_param('ssi',$status,$reply,$id);$s->execute();if($uid)notify_user($uid,'Feedback Update',"Your feedback status is now $status." . ($reply?" Admin reply: $reply":''));}
 elseif($a==='status_service'){
  $status=$_POST['status'];$notes=trim($_POST['admin_notes']??'');$q=$conn->prepare('SELECT user_id FROM service_requests WHERE id=?');$q->bind_param('i',$id);$q->execute();$uid=$q->get_result()->fetch_assoc()['user_id']??0;$s=$conn->prepare('UPDATE service_requests SET status=?,admin_notes=? WHERE id=?');$s->bind_param('ssi',$status,$notes,$id);$s->execute();if($uid)notify_user($uid,'Service Request Update',"Your request status is now $status." . ($notes?" Note: $notes":''));}
 elseif($a==='reg_status'){
  $status=$_POST['status'];$q=$conn->prepare('SELECT user_id,event_id FROM registrations WHERE id=?');$q->bind_param('i',$id);$q->execute();$r=$q->get_result()->fetch_assoc();$s=$conn->prepare('UPDATE registrations SET status=? WHERE id=?');$s->bind_param('si',$status,$id);$s->execute();if($r)notify_user($r['user_id'],'Event Registration Update',"Your event registration is now $status.");}
 elseif($a==='notify'){ $uid=(int)$_POST['user_id'];notify_user($uid,$_POST['title'],$_POST['message']);}
 header('Location: admin.php');exit;
}
$search=trim($_GET['q']??'');$like="%$search%";
$users=$conn->query("SELECT * FROM users ORDER BY created_at DESC");
$ann=$conn->query("SELECT * FROM announcements ORDER BY created_at DESC");$events=$conn->query("SELECT * FROM events ORDER BY event_date");
$programs=$conn->query("SELECT * FROM programs ORDER BY created_at DESC");$opps=$conn->query("SELECT * FROM opportunities ORDER BY created_at DESC");$projects=$conn->query("SELECT * FROM projects ORDER BY created_at DESC");
$feedback=$conn->query("SELECT f.*,u.name,u.email FROM feedback f JOIN users u ON u.id=f.user_id ORDER BY f.created_at DESC");
$services=$conn->query("SELECT s.*,u.name,u.email FROM service_requests s JOIN users u ON u.id=s.user_id ORDER BY s.created_at DESC");
$regs=$conn->query("SELECT r.*,u.name,u.email,e.title,event_date FROM registrations r JOIN users u ON u.id=r.user_id JOIN events e ON e.id=r.event_id ORDER BY r.created_at DESC");
$editType='';$editRow=null;
foreach(['announcements','events','programs','opportunities','projects'] as $et){
 if(isset($_GET['edit_'.$et])){$editType=$et;$eid=(int)$_GET['edit_'.$et];$q=$conn->query("SELECT * FROM $et WHERE id=$eid");$editRow=$q->fetch_assoc();}
}
?>

?><!doctype html><html><head><meta name="viewport" content="width=device-width,initial-scale=1"><title>Admin Panel</title><link rel="stylesheet" href="assets/style.css"></head><body>
<nav><b>Kabataan <i>Hub</i></b><div><a href="dashboard.php">Dashboard</a><a href="logout.php">Logout</a></div></nav><main>
<h1>Admin Management</h1>
<?php if($editType): ?>
<section><h2>✏️ Edit <?=e(ucwords(str_replace('_',' ',$editType)))?></h2>
<form class="inline-form" method="post">
<input type="hidden" name="id" value="<?=e($editRow['id'])?>">
<input type="hidden" name="action" value="<?=e($editType==='announcements'?'save_ann':($editType==='events'?'save_event':($editType==='programs'?'save_program':($editType==='opportunities'?'save_opp':'save_project'))))?>">
<input name="title" value="<?=e($editRow['title'])?>" placeholder="Title" required>
<?php if($editType==='events'): ?><input name="event_date" type="date" value="<?=e($editRow['event_date'])?>" required><input name="location" value="<?=e($editRow['location'])?>" placeholder="Location" required><?php endif;?>
<?php if($editType==='projects'): ?><input name="budget" type="number" step="0.01" value="<?=e($editRow['budget'])?>" placeholder="Budget"><?php endif;?>
<?php if($editType==='opportunities'): ?><input name="link" value="<?=e($editRow['link'])?>" placeholder="Link"><?php endif;?>
<textarea name="<?=($editType==='announcements'?'content':'description')?>" placeholder="Description" required><?=e($editRow[$editType==='announcements'?'content':'description'])?></textarea>
<?php if(in_array($editType,['programs','opportunities','projects'])):?><select name="status"><option <?=($editRow['status']==='Active'?'selected':'')?>>Active</option><option <?=($editRow['status']==='Ongoing'?'selected':'')?>>Ongoing</option><option <?=($editRow['status']==='Completed'?'selected':'')?>>Completed</option><option <?=($editRow['status']==='Inactive'?'selected':'')?>>Inactive</option></select><?php endif;?>
<button class="btn">Save Changes</button><a class="btn secondary" href="admin.php">Cancel</a></form></section>
<?php endif; ?>
<section><h2>➕ Add Content</h2><div class="grid">
<?php foreach([['save_ann','Announcement','content'],['save_event','Event','description'],['save_program','Program','description'],['save_opp','Opportunity','description'],['save_project','Project','description']] as $f):?>
<form class="box" method="post"><input type="hidden" name="action" value="<?=$f[0]?>"><h3><?=e($f[1])?></h3>
<input name="title" placeholder="Title" required>
<?php if($f[0]==='save_event'):?><input name="event_date" type="date" required><input name="location" placeholder="Location" required><?php endif;?>
<?php if($f[0]==='save_opp'):?><input name="link" placeholder="Link"><?php endif;?>
<?php if($f[0]==='save_project'):?><input name="budget" type="number" step="0.01" placeholder="Budget"><select name="status"><option>Ongoing</option><option>Completed</option><option>Active</option></select><?php endif;?>
<?php if($f[0]==='save_program'):?><select name="status"><option>Active</option><option>Inactive</option></select><?php endif;?>
<?php if($f[0]==='save_opp'):?><select name="status"><option>Active</option><option>Inactive</option></select><?php endif;?>
<textarea name="<?=$f[2]?>" placeholder="Description" required></textarea><button class="btn">Add</button></form>
<?php endforeach;?></div></section>
<form class="search"><input name="q" value="<?=e($search)?>" placeholder="Search users, feedback, requests, opportunities..."><button class="btn">Search / Filter</button></form>
<section><h2>👥 User Management</h2><form class="inline-form" method="post"><input type="hidden" name="action" value="save_user"><input name="name" placeholder="Name" required><input name="email" type="email" placeholder="Email" required><input name="password" placeholder="Password"><select name="role"><option value="youth">Youth</option><option value="admin">Admin</option></select><button class="btn">Add User</button></form><div class="table-wrap"><table><tr><th>Name</th><th>Email</th><th>Role</th><th>Actions</th></tr><?php while($u=$users->fetch_assoc()):?><tr><td><?=e($u['name'])?></td><td><?=e($u['email'])?></td><td><?=e($u['role'])?></td><td><a class="btn small" href="?edit_user=<?=$u['id']?>">Edit</a><?php if($u['id']!=$_SESSION['id']):?><form class="inline" method="post"><input type="hidden" name="action" value="delete_user"><input type="hidden" name="id" value="<?=$u['id']?>"><button class="danger small">Delete</button></form><?php endif;?></td></tr><?php endwhile;?></table></div></section>
<?php
function manage_table($title,$icon,$rows,$table,$fields){
 echo "<section><h2>$icon ".e($title)."</h2><div class='grid'>";
 while($r=$rows->fetch_assoc()){echo "<article><h3>".e($r['title'])."</h3><p>".e($r['description']??$r['content']??'')."</p>";if(isset($r['status']))echo "<span class='badge'>".e($r['status'])."</span>";echo "<div class='actions'><a class='btn small' href='?edit_".$table."=".$r['id']."'>Edit</a><form class='inline' method='post'><input type='hidden' name='action' value='delete'><input type='hidden' name='table' value='$table'><input type='hidden' name='id' value='".$r['id']."'><button class='danger small'>Delete</button></form></div></article>";}
 echo "</div></section>";
}
manage_table('Announcements','📢',$ann,'announcements',[]);
manage_table('Events','📅',$events,'events',[]);
manage_table('Programs','📋',$programs,'programs',[]);
manage_table('Opportunities','🎓',$opps,'opportunities',[]);
manage_table('Projects','💰',$projects,'projects',[]);
?>
<section><h2>💬 Feedback Processing</h2><?php while($f=$feedback->fetch_assoc()):?><article class="record"><b><?=e($f['subject'])?></b> — <?=e($f['name'])?><p><?=nl2br(e($f['message']))?></p><form class="inline-form" method="post"><input type="hidden" name="action" value="status_feedback"><input type="hidden" name="id" value="<?=$f['id']?>"><select name="status"><option <?=($f['status']=='Pending'?'selected':'')?>>Pending</option><option <?=($f['status']=='Reviewed'?'selected':'')?>>Reviewed</option><option <?=($f['status']=='Resolved'?'selected':'')?>>Resolved</option></select><input name="admin_reply" value="<?=e($f['admin_reply'])?>" placeholder="Admin reply"><button class="btn">Update</button></form></article><?php endwhile;?></section>
<section><h2>📝 Service Requests Processing</h2><?php while($s=$services->fetch_assoc()):?><article class="record"><b><?=e($s['service_type'])?></b> — <?=e($s['name'])?><p><?=nl2br(e($s['details']))?></p><form class="inline-form" method="post"><input type="hidden" name="action" value="status_service"><input type="hidden" name="id" value="<?=$s['id']?>"><select name="status"><option>Pending</option><option>Processing</option><option>Approved</option><option>Rejected</option><option>Completed</option></select><input name="admin_notes" value="<?=e($s['admin_notes'])?>" placeholder="Admin notes"><button class="btn">Update</button></form></article><?php endwhile;?></section>
<section><h2>📅 Event Registrations</h2><div class="table-wrap"><table><tr><th>Youth</th><th>Event</th><th>Date</th><th>Status</th><th>Action</th></tr><?php while($r=$regs->fetch_assoc()):?><tr><td><?=e($r['name'])?></td><td><?=e($r['title'])?></td><td><?=$r['event_date']?></td><td><?=e($r['status'])?></td><td><form method="post" class="inline-form"><input type="hidden" name="action" value="reg_status"><input type="hidden" name="id" value="<?=$r['id']?>"><select name="status"><option>registered</option><option>approved</option><option>attended</option><option>cancelled</option></select><button class="btn small">Update</button></form></td></tr><?php endwhile;?></table></div></section>
<section><h2>🔔 Send Notification</h2><form class="inline-form" method="post"><input type="hidden" name="action" value="notify"><select name="user_id" required><option value="">Choose user</option><?php $nu=$conn->query("SELECT id,name FROM users WHERE role='youth' ORDER BY name");while($u=$nu->fetch_assoc()):?><option value="<?=$u['id']?>"><?=e($u['name'])?></option><?php endwhile;?></select><input name="title" placeholder="Notification title" required><input name="message" placeholder="Message" required><button class="btn">Send</button></form></section>
</main></body></html>