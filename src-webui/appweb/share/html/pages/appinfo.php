<?
   if ( empty($_GET['app']) )
      die("Missing app=");

   // Get the current work queue status of the dispatcher
   $dStatus = getDispatcherStatus();

   $pbiorigin = $_GET['app'];

   // Load the PBI details page
   $cmd="pbi app $pbiorigin";
   exec("$sc ". escapeshellarg("$cmd name") . " " . escapeshellarg("pkg #system local $pbiorigin version") . " " . escapeshellarg("$cmd author") . " " . escapeshellarg("$cmd website") . " " . escapeshellarg("$cmd comment") . " " . escapeshellarg("$cmd confdir") . " " . escapeshellarg("$cmd description") . " " . escapeshellarg("$cmd screenshots"), $pbiarray);

  $pbiname = $pbiarray[0];
  $pbiver = $pbiarray[1];
  $pbiauth = $pbiarray[2];
  $pbiweb = $pbiarray[3];
  $pbicomment = $pbiarray[4];
  $pbicdir = $pbiarray[5];
  $pbidesc = $pbiarray[6];
  $pbiss = $pbiarray[7];

  if ( empty($pbiname) )
  {
    exec("$sc " . escapeshellarg("pkg #system local $pbiorigin name"), $pkgarray);
    $pbiname = $pkgarray[0];
  }

  if ( empty($pbiname) )
    die("No such app: $pbi");

  // Check if this app is installed
  $pkgoutput = syscache_ins_pkg_list();
  $pkglist = explode(", ", $pkgoutput[0]);
  if ( array_search($pbiorigin, $pkglist) !== false)
     $pbiinstalled=true;
  else
     $pbiinstalled=false;

  // If the application is not installed, we need to fetch some stuff from rquery
  if ( ! $pbiinstalled )
  {
    exec("$sc " . escapeshellarg("pkg #system remote $pbiorigin version"), $pkgarray);
    $pbiver = $pkgarray[0];
  }
?>
   
<br>
<table class="jaillist" style="width:100%">
  <tr>
    <th colspan=2><? echo "$pbiname - $pbiver"; ?></th>
  </tr>
  <tr>
    <td align=center>
      <img align="center" height=64 width=64 src="images/pbiicon.php?i=<? echo "$pbicdir"; ?>/icon.png"><br><br>
      <?
	 if ( array_search("pbi $pbiorigin install system", $dStatus) !== false ) {
	   print("    Installing...");
         } else if ( array_search("pbi $pbiorigin delete system", $dStatus) !== false ) {
	   print("    Deleting...");
         } else if( $pbiinstalled ) {
	    print("    <button onclick=\"delConfirm('" . $pbiname ."','".$pbiorigin."','pbi','system')\">-Remove</button>");
         } else {
	    print("    <button onclick=\"addConfirm('" . $pbiname ."','".$pbiorigin."','pbi','system')\">+Install</button>");
	 }
      ?>
    </td>
    <td>
       <a href="<? echo "$pbiweb"; ?>" target="_new"><? echo "$pbiauth"; ?></a><br>
       Version: <b><? echo "$pbiver"; ?></b><br>
    </td>
  </tr>
  <tr>
    <td colspan="2" width=400>
      <? echo "$pbidesc"; ?>
    </td>
  </tr>
  <tr>
    <td colspan="2">
      <?  // Do we have screenshots to display?
	 if ( ! empty($pbiss) ) {
	    echo "Screenshots: <br>";
            $sslist = explode(" ", $pbiss);
            foreach($sslist as $screenshot)
            {
              echo "<a href=\"$screenshot\" target=\"_new\"><img border=0 src=\"$screenshot\" height=50 width=50></a>&nbsp;";
            }
         }
      ?>
    </td>
  </tr>
</table>