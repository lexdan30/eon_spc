<?php

    class connector{
        public $host = "localhost";					
        public $dbname = "2hrisdbic";			
        public $name = "root";						
        public $pass = "waterfront07";
        function connect(){
            $conn = mysqli_connect("$this->host", "$this->name", "$this->pass","$this->dbname");
            if (!$conn)
            {
                die('Could not connect: ' . mysqli_connect_error());
            }
            $conn->set_charset("utf8");
            return $conn;
        }
        
        function connect2(){
            $conn = mysqli_connect("localhost", "root", "waterfront07","2hrisdbic");
            if (!$conn)
            {
                die('Could not connect: ' . mysqli_connect_error());
            }
            $conn->set_charset("utf8");
            return $conn;
        }
    }

    class Query{
		
		public $table;	
		public $fields;
		public $values;
		public $selected;
		public $delimeter;

/**************************************************************************************
***************************************************************************************
*********************************INSERT QUERY STATEMENT*******************************/	
			function exe_INSERT($con){
				$quer_y = "INSERT INTO ".$this->table." (".$this->selected.") VALUES (".$this->fields.")";
				return mysqli_query($con, $quer_y);
			}
/**************************************************************************************
***************************************************************************************
*********************************SELECT QUERY STATEMENT*******************************/	
			function exe_SELECT($con){
				$quer_y = "SELECT ".$this->selected." FROM ".$this->table." WHERE ".$this->fields;
				return mysqli_query($con, $quer_y);
			}
/**************************************************************************************
***************************************************************************************
*********************************UPDATE QUERY STATEMENT*******************************/	
			function exe_UPDATE($con){
				$quer_y = "UPDATE ".$this->table." SET ".$this->selected." WHERE ".$this->fields;
				return mysqli_query($con, $quer_y);
			}
/**************************************************************************************
***************************************************************************************
*********************************DELETE QUERY STATEMENT*******************************/	
			function exe_DELETE($con){
				$quer_y = "DELETE FROM ".$this->table." WHERE ".$this->fields;
				return mysqli_query($con, $quer_y);
			}
/**************************************************************************************
***************************************************************************************
*********************************TRUNCATE QUERY STATEMENT*******************************/	
			function exe_TRUNCATE($con){
				$quer_y = "TRUNCATE TABLE ".$this->table;
				return mysqli_query($con, $quer_y);
			}			
    }

    function SysDate(){
        date_default_timezone_set('Asia/Manila');
        $info = getdate();
        $date = $info['mday'];
        $month = $info['mon'];
        $year = $info['year'];
        $dat_e = $year."-".$month."-".$date;
        $date_2 = date_create($dat_e);
        if(!empty($year) && !empty($month) && !empty($date)){
            return date_format($date_2,"Y-m-d");
        }else{
            return date_format($date_2,"Y-m-d");
        }
    }

    function SysTime(){

        date_default_timezone_set('Asia/Manila');
        $info = getdate();
        $hour = $info['hours'];
        $min = $info['minutes'];
        $sec = $info['seconds'];
        $time = $hour.":".$min.":00";
        if(!empty($hour) && !empty($min) && !empty($sec)){
            return $time;
        }else{
            return $time;
        }
    }
    
    $conn = new connector();
    $con = $conn->connect();

    $date = SysDate();
    $time = SysTime();
    $proceed = 0;
    $counter = 0;

    $Qry=new Query();
    $Qry->table="vw_data_lateraltransfer LEFT JOIN tblposition ON vw_data_lateraltransfer.newpositiontitle = tblposition.name LEFT JOIN tbljoblvl ON vw_data_lateraltransfer.newjoblevel = tbljoblvl.lvl LEFT JOIN tbllabortype ON vw_data_lateraltransfer.newlabortype = tbllabortype.type LEFT JOIN tblempstatus ON vw_data_lateraltransfer.newempstatus = tblempstatus.stat LEFT JOIN tblpaygrp ON vw_data_lateraltransfer.newpaygroup = tblpaygrp.group";
    $Qry->selected="vw_data_lateraltransfer.id,vw_data_lateraltransfer.idstatus,vw_data_lateraltransfer.effectivedate,vw_data_lateraltransfer.requestor,tblposition.id AS newpositiontitle,tbljoblvl.id AS newjoblevel,(CASE WHEN (vw_data_lateraltransfer.newidsection = '') THEN vw_data_lateraltransfer.newiddept ELSE vw_data_lateraltransfer.newidsection END) AS idunit,tbllabortype.id AS newlabortype,vw_data_lateraltransfer.newidsuperior, tblempstatus.id AS newempstatus,tblpaygrp.id AS newpaygroup";
    $Qry->fields="vw_data_lateraltransfer.idstatus=1 AND vw_data_lateraltransfer.effectivedate='".$date."' AND vw_data_lateraltransfer.201update=0";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>0){
        while($row=mysqli_fetch_array($rs)){
           
            $Qrye 			= new Query();	
            $Qrye->table 	= "tblaccountjob";
            $Qrye->selected = " idpos		=	'".$row['newpositiontitle']."',						
                                idlvl		=	'".$row['newjoblevel']."',
                                idunit		=	'".$row['idunit']."',
                                idlabor		=	'".$row['newlabortype']."',
                                idsuperior	=	'".$row['newidsuperior']."',
                                empstat		=	'".$row['newempstatus']."',
                                idpaygrp	=	'".$row['newpaygroup']."'";
            $Qrye->fields 	= "idacct='".$row['requestor']."'";
            $update 	= $Qrye->exe_UPDATE($con);
            if($update){
                $counter++;
                $proceed = 1;

                $Qryf 			= new Query();	
                $Qryf->table 	= "tblforms01";
                $Qryf->selected = "201update	=	'1'";
                $Qryf->fields 	= "id='".$row['id']."'";
                $Qryf->exe_UPDATE($con);
            }else{
                $counter++;
                $proceed = 0;
            }
        }

        //Inserted output text
        if($proceed==1){
			echo "*** AUTO UPDATE 201 LATERAL TRANSFER ***\r\n";
			echo "DATE:".' '.$date.' '."TIME:".' '.$time."\r\n";
			echo $counter.' '."Rows data updated successfully.\r\n";
			echo "**************************** END OF EXECUTION ****************************\r\n";
        }else{
            echo "*** AUTO UPDATE 201 LATERAL TRANSFER ***\r\n";
			echo "DATE:".' '.$date.' '."TIME:".' '.$time."\r\n";
			echo $counter.' '."rows unaffected.\r\n";
			echo "**************************** END OF EXECUTION ****************************\r\n";
        }
       
    }else{
        echo "*** AUTO UPDATE 201 LATERAL TRANSFER ***\r\n";
        echo "DATE:".' '.$date.' '."TIME:".' '.$time."\r\n";
        echo "No lateral transfer was made.\r\n";
        echo "**************************** END OF EXECUTION ****************************\r\n";
        
    }


mysqli_close($con);
?>