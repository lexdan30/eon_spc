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

    $allowance = array();

    $Qry=new Query();
    $Qry->table="vw_data_wageincrease";
    $Qry->selected="id,requestor,newbasepay,refferenceno";
    $Qry->fields="idstatus=1 AND effectivedate='".$date."' AND 201update=0";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>0){
        while($row=mysqli_fetch_array($rs)){
           
            $Qrye 			= new Query();	
            $Qrye->table 	= "tblaccountjob";
            $Qrye->selected = "salary =	'".$row['newbasepay']."'";
            $Qrye->fields 	= "idacct='".$row['requestor']."'";
            $update 	= $Qrye->exe_UPDATE($con);
            if($update){

                $Qry1 			= new Query();	
				$Qry1->table     = "tblformsallowance";
				$Qry1->selected  = "*";
				$Qry1->fields    = "refno='".$row['refferenceno']."'";
				$rs1 			= $Qry1->exe_SELECT($con);
				if(mysqli_num_rows($rs1)>= 1){
					while($row1=mysqli_fetch_array($rs1)){
						$allowance[] = array(
							"id"			=>	$row1['id'],
							"idacct"		=>	$row1['idacct'],
							"description"	=>	$row1['type'],
							"idallowance"	=>  $row1['idallowance'],
							"new_amt"		=>  $row1['new_amt'],
							"current_amt"	=>  $row1['current_amt'],
						);
					}
                }
                
                foreach($allowance as $key => $value){
                    // Update table
                    $Qry2            = new Query();
                    $Qry2->table     = "tblacctallowance";
                    $Qry2->selected  = "amt='".$allowance[$key]['new_amt']."'";
                    $Qry2->fields 	 = "idallowance = '".$allowance[$key]['idallowance']."'";
                    $rs2             = $Qry2->exe_UPDATE($con);
                    if(!$rs2){
                        $return = json_encode(array('status'=>mysqli_error($con)));
                        print $return;
                        mysqli_close($con);
                        return;
                    }
                }

                $counter++;
                $proceed = 1;
                
                $Qryf 			= new Query();	
                $Qryf->table 	= "tblforms02";
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
			echo "*** AUTO UPDATE 201 WAGE INCREASE ***\r\n";
			echo "DATE:".' '.$date.' '."TIME:".' '.$time."\r\n";
			echo $counter.' '."Rows data updated successfully.\r\n";
			echo "**************************** END OF EXECUTION ****************************\r\n";
        }else{
            echo "*** AUTO UPDATE 201 WAGE INCREASE ***\r\n";
			echo "DATE:".' '.$date.' '."TIME:".' '.$time."\r\n";
			echo $counter.' '."rows unaffected.\r\n";
			echo "**************************** END OF EXECUTION ****************************\r\n";
        }
       
    }else{
        echo "*** AUTO UPDATE 201 WAGE INCREASE ***\r\n";
        echo "DATE:".' '.$date.' '."TIME:".' '.$time."\r\n";
        echo "No wage increase was made.\r\n";
        echo "**************************** END OF EXECUTION ****************************\r\n";
        
    }


mysqli_close($con);
?>