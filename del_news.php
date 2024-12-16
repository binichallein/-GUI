<?php include "conn.php" ?>
<?php
$id=$_GET["id"]; 
$sql = "delete from news where id=$id"; 
$res = @mysqli_query($con,$sql);
if($res)
{
?>
<script language=javascript>window.alert('delete successfully!');history.back(1);</script>
<?php
} 
?>
