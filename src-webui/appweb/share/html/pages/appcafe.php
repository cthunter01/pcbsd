<?
defined('DS') OR die('No direct access allowed.');

 if ( empty($_GET['jail']) or ! empty($_GET['changeappcafejail']))
 {
    display_jail_appcafeselection();
 } else {

   if ( ! empty($_GET['cat']) )
     $header="Browsing Category: ". $_GET['cat'];
   else
     $header="Recommended Applications";
?>

<h1><? echo $header; ?></h1>
<br>
<?

       if ( $deviceType == "computer" ) { 
       $totalCols = 3;
?>
<table class="jaillist" style="width:100%">
<tr>
   <th></th>
   <th></th>
   <th></th>
</tr>
<?
       } else {
         $totalCols = 2;
?>
<table class="jaillist" style="width:100%">
<tr>
   <th></th>
   <th></th>
</tr>
<?
       }


     if ( ! empty($_GET['cat']) )
     {
         if ( $allPBI == "true" )
           exec("$sc ". escapeshellarg("pbi list allapps"), $pbiarray);
         elseif ( $jail == "#system" && $sysType == "DESKTOP" )
           exec("$sc ". escapeshellarg("pbi list graphicalapps"), $pbiarray);
         else
           exec("$sc ". escapeshellarg("pbi list serverapps"), $pbiarray);

       $fulllist = explode(", ", $pbiarray[0]);
       $catsearch = $_GET['cat'] . "/";
       $pbilist = array_filter($fulllist, function($var) use ($catsearch) { return preg_match("|^$catsearch|", $var); });

     } else {
       exec("$sc ". escapeshellarg("pbi list recommended")." ". escapeshellarg("pbi list new"), $pbiarray);
       $pbilist = explode(", ", $pbiarray[0]);
       $newlist = explode(", ", $pbiarray[1]);
     }

     // Now loop through pbi origins
     $col=1;
     foreach ($pbilist as $pbiorigin) {
       if ( parse_details($pbiorigin, $jail, $col) == 0 ) {
         if ( $col == $totalCols )
           $col = 1;
         else
           $col++;
       }
     } 

     // Close off the <tr>
     if ( $col != $totalCols )
        echo "</tr>";

     echo "</table>";
   }
?>

