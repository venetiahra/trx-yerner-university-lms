
<?php require 'config.php';
$q=$_GET['q'] ?? '';
$res=[];
if($q){ $stmt=$conn->prepare("SELECT * FROM students WHERE full_name LIKE ?");
$stmt->execute(['%'.$q.'%']); $res=$stmt->fetchAll(); }
?>
<h2>Search</h2>
<form>
<input name='q' placeholder='Search'>
<button>Go</button>
</form>
<?php foreach($res as $r){ echo "<p>$r[full_name]</p>";} ?>
