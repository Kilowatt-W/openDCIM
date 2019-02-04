<?php


        require_once('db.inc.php');
        require_once('facilities.inc.php');

if(!$person->ReadAccess){
    // No soup for you.
    header('Location: '.redirect());
    exit;
}

        $subheader=__("Inventory Reporting By ColorCodes and Cabeling");

        $tagsList=Tags::FindAll();
        $body="";
        if(isset($_REQUEST['tagid'])){
                $tag=isset($_POST['tagid'])?$_POST['tagid']:$_GET['tagid'];
                if($tag!=''){
                        $tag=intval($tag);
                        if($tag==0){
                                $sql="select FD1.Label as Device_1, FD1.DeviceType as DeviceType_1, FD2.Label as Device_2, FD2.DeviceType as DeviceType_2, fac_ColorCoding.Name as Color, '1' as Count from fac_Device as FD1, fac_Device as FD2, fac_ColorCoding, fac_Ports where fac_Ports.DeviceID = FD1.DeviceID and fac_Ports.ConnectedDeviceID = FD2.DeviceID and fac_ColorCoding.ColorID = fac_Ports.ColorID order by Device_1 ASC;";
                                   }else{
                                $sql="select FD1.Label as Device_1, FD1.DeviceType as DeviceType_1, FD2.Label as Device_2, FD2.DeviceType as DeviceType_2, fac_ColorCoding.Name as Color, '1' as Count from fac_Device as FD1, fac_Device as FD2, fac_ColorCoding, fac_Ports where fac_Ports.DeviceID = FD1.DeviceID and fac_Ports.ConnectedDeviceID = FD2.DeviceID and fac_ColorCoding.ColorID = fac_Ports.ColorID and ((FD1.DeviceType <> 'Switch') or (FD1.DeviceType = 'Switch' and FD2.DeviceType = 'Switch')) order by Device_1 ASC;";
                                   }
                $result=$dbh->query($sql);

                // Left these expanded in case we need to add or remove columns.  Otherwise I would have just collapsed entirely.
                $body="<table id=\"export\" class=\"display\">\n\t<thead>\n\t\t<tr>\n
                        \t<th>".__("Device 1")."</th>
                        \t<th>".__("DeviceType 1")."</th>
                        \t<th>".__("Device 2")."</th>
                        \t<th>".__("DeviceType 2")."</th>
                        \t<th>".__("Color,Cabeling")."</th>
                        \t<th>".__("Count")."</th>
                        </tr>\n\t</thead>\n\t<tbody>\n";

                // suppressing errors for when there is a fake data set in place
                foreach($result as $row){
                        $body.="\t\t<tr>
                        \t<td>{$row["Device_1"]}</td>
                        \t<td>{$row["DeviceType_1"]}</td>
                        \t<td>{$row["Device_2"]}</td>
                        \t<td>{$row["DeviceType_2"]}</td>
                        \t<td>{$row["Color"]}</td>
                        \t<td>{$row["Count"]}</td>
                        \t\t</tr>\n";
                }
                $body.="\t\t</tbody>\n\t</table>\n";

// Add group Count for Colors
                $body.="<br>Summary for Colors and Cabeling Types\n<br><br><br>";


                $sql="select fac_ColorCoding.Name as Color, count(*) as Count from fac_Device as FD1, fac_Device as FD2, fac_ColorCoding, fac_Ports where fac_Ports.DeviceID = FD1.DeviceID and fac_Ports.ConnectedDeviceID = FD2.DeviceID and fac_ColorCoding.ColorID = fac_Ports.ColorID and FD1.Label not like 'SW%' group by Color order by Color ASC;";
                $result=$dbh->query($sql);

                // Left these expanded in case we need to add or remove columns.  Otherwise I would have just collapsed entirely.
                $body.="<table id=\"export\" class=\"display\">\n\t<thead>\n\t\t<tr>\n
                        \t<th>".__("Color")."</th>
                        \t<th>".__("Count")."</th>
                        </tr>\n\t</thead>\n\t<tbody>\n";

                // suppressing errors for when there is a fake data set in place
                foreach($result as $row){
                        $body.="\t\t<tr>
                        \t<td>{$row["Color"]}</td>
                        \t<td>{$row["Count"]}</td>
                        \t\t</tr>\n";
                }
                $body.="\t\t</tbody>\n\t</table>\n";


                if(isset($_REQUEST['ajax'])){
                        echo $body;
                        exit;
                }
        }} //IF TAG
?>
<!doctype html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=Edge">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <link rel="stylesheet" href="css/inventory.php" type="text/css">
  <link rel="stylesheet" href="css/print.css" type="text/css" media="print">
  <link rel="stylesheet" href="css/jquery-ui.css" type="text/css">
  <link rel="stylesheet" href="css/jquery.dataTables.min.css" type="text/css">
  <style type="text/css"></style>
  <!--[if lt IE 9]>
  <link rel="stylesheet"  href="css/ie.css" type="text/css" />
  <![endif]-->
  <script type="text/javascript" src="scripts/jquery.min.js"></script>
  <script type="text/javascript" src="scripts/jquery-ui.min.js"></script>
  <script type="text/javascript" src="scripts/jquery.dataTables.min.js"></script>
  <script type="text/javascript" src="scripts/pdfmake.min.js"></script>
  <script type="text/javascript" src="scripts/vfs_fonts.js"></script>

  <script type="text/javascript">
        $(document).ready(function(){
                var rows;
                function dt(){
                        $('#export').dataTable({
                                dom: 'B<"clear">lfrtip',
                                buttons:{
                                        buttons: [
                                                'copy', 'excel', 'pdf', 'csv', 'colvis', 'print'
                                        ]
                                }
                        });
                        redraw();
                }
                function redraw(){
                        if(($('#export').outerWidth()+$('#sidebar').outerWidth()+10)<$('.page').innerWidth()){
                                $('.main').width($('#header').innerWidth()-$('#sidebar').outerWidth()-16);
                        }else{
                                $('.main').width($('#export').outerWidth()+40);
                        }
                        $('.page').width($('.main').outerWidth()+$('#sidebar').outerWidth()+10);
                }
                dt();
                $('#tagid').change(function(){
                        $.post('', {tagid: $(this).val(), ajax: ''}, function(data){
                                $('#tablecontainer').html(data);
                                dt();
                        });
                });
        });
  </script>
</head>
<body>
<?php include( 'header.inc.php' ); ?>
        <div class="page">
<?php
        include('sidebar.inc.php');
echo '          <div class="main">
                        <label for="tagid">',__("Mode:"),'</label>
                        <select name="tagid" id="tagid">
                                <option value="_">',__("Select Mode"),'</option>
                                <option value="0">',__("All Cablelinks"),'</option>
                                <option value="1">',__("Deduplicate Cablelinks"),'</option>'
                        //foreach($tagsList as $tagid => $tagname){print "\t\t\t\t<option value=\"$tagid\">$tagname</option>\n";}
?>
                        </select>
                        <br><br>
                        <div class="center">
                                <div id="tablecontainer">
                                        <?php echo $body; ?>
                                </div>
                        </div>
                </div> // <!-- END div.main -->
        </div>  // <!-- END div.page -->
</body>
</html>
